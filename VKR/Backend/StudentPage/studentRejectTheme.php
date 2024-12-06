<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../Login/optionsJWT.php';  // Для работы с секретным ключом
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

    // Получаем данные из тела запроса (JSON)
    $data = json_decode(file_get_contents('php://input'), true);

    // Проверяем, что vkr_id передан в запросе
    $vkr_id = $data['vkr_id'] ?? null;

    if (!$vkr_id) {
        // Если vkr_id не передан, возвращаем ошибку
        http_response_code(400);
        echo json_encode(['error' => 'Не указан vkr_id']);
        exit;
    }

    $stmt = $pdo->prepare("
        select user_id from vkr v, students s
        where v.student_id = s.student_id and vkr_id = :vkr_id
    ");
    $stmt->execute([
        'vkr_id' => $vkr_id,
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Проверяем, существует ли запись и совпадает ли user_id
    if (!$row || $row['user_id'] != $user_id) {
        http_response_code(400);
        echo json_encode(['error' => 'vkr_id не привязан к вашему аккаунту']);
        exit;
    }

    // Здесь можно добавить запрос, который будет обновлять подтверждение темы в базе данных
    // Например, обновляем статус confirmed_student в таблице changes_theme

    // Ваш SQL запрос для обновления статуса подтверждения
    $stmt = $pdo->prepare("
        UPDATE changes_theme SET confirmed_student = false, date = NOW()
        where vkr_id = :vkr_id and
        date = (select max(date) from changes_theme where vkr_id = :vkr_id)
    ");

    // Выполняем запрос с параметрами
    $stmt->execute([
        'vkr_id' => $vkr_id,
    ]);

    // Проверка на успешное обновление
    if ($stmt->rowCount() > 0) {
        // Если запись обновлена, отправляем успешный ответ
        echo json_encode(['message' => 'Тема отклонена']);
    } else {
        // Если запись не была обновлена (например, уже подтверждена или неверный vkr_id)
        http_response_code(400);
        echo json_encode(['error' => 'Не удалось отклонить тему']);
    }

} catch (Exception $e) {
    // Ошибка декодирования токена
    http_response_code(480);
    echo json_encode(['error' => 'Неверный или просроченный токен', 'text' => $e]);
}
?>
