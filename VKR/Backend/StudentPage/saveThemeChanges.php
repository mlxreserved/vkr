<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../Login/optionsJWT.php'; // Для работы с секретным ключом
use Firebase\JWT\JWT;
use Firebase\JWT\Key; // Подключаем класс Key для указания ключа и алгоритма
require __DIR__ . '/../database.php'; // Подключаем базу данных

// Получаем токен из куки
$accessToken = $_COOKIE['auth_token'] ?? null;

if (!$accessToken) {
    // Если токена нет, возвращаем ошибку
    http_response_code(401);
    echo json_encode(['error' => 'Токен не найден.']);
    exit;
}

try {
    // Декодируем токен
    $decoded = JWT::decode($accessToken, new Key($secretKey, 'HS256'));
    $user_id = $decoded->user_id; // Получаем user_id из токена

    // Получаем данные из POST-запроса
    $data = json_decode(file_get_contents('php://input'), true);

    // Проверяем, что все необходимые параметры присутствуют
    if (!isset($data['vkr_id'], $data['theme'], $data['pretheme'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Отсутствуют необходимые данные.']);
        exit;
    }

    // Извлекаем параметры
    $vkr_id = $data['vkr_id'];
    $theme = $data['theme'];
    $pretheme = $data['pretheme'];

    // Проверяем, что студент действительно связан с этой ВКР
    $stmt = $pdo->prepare("
        SELECT student_id
        FROM vkr
        WHERE vkr_id = :vkr_id
    ");
    $stmt->execute(['vkr_id' => $vkr_id]);
    $vkr = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$vkr) {
        // ВКР не найдена
        http_response_code(404);
        echo json_encode(['error' => 'Выпускная работа не найдена.']);
        exit;
    }

    // Проверяем, что студент может редактировать тему (например, если она не подтверждена)
    $stmt = $pdo->prepare("
        SELECT confirmed_admin, confirmed_teacher, confirmed_student
        FROM changes_theme
        WHERE vkr_id = :vkr_id
        ORDER BY date DESC
        LIMIT 1
    ");
    $stmt->execute(['vkr_id' => $vkr_id]);
    $changes = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($changes && $changes['confirmed_admin'] !== null) {
        // Если тема уже подтверждена администратором, нельзя редактировать
        http_response_code(403);
        echo json_encode(['error' => 'Тема уже подтверждена и не может быть изменена.']);
        exit;
    }

    // Обновляем данные темы в базе данных
    $stmt = $pdo->prepare("
        INSERT INTO changes_theme VALUES
        (:vkr_id, :theme, null, null, true, NOW())
    ");
    $stmt->execute([
        'vkr_id' => $vkr_id,
        'theme' => $theme,
    ]);

    $stmt = $pdo->prepare("
        UPDATE vkr SET pretheme = :pretheme where vkr_id = :vkr_id
    ");
    $stmt->execute([
        'vkr_id' => $vkr_id,
        'pretheme' => $pretheme,
    ]);

    // Возвращаем успешный ответ
    echo json_encode(['success' => 'Изменения сохранены.']);

} catch (Exception $e) {
    // Ошибка декодирования токена
    http_response_code(409);
    echo json_encode(['error' => 'Неверный или просроченный токен', 'text' => $e]);
}
?>
