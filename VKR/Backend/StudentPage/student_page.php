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

    // Выполняем SQL запрос с подставленным user_id
    $stmt = $pdo->prepare("
        SELECT v.vkr_id, v.student_id, v.mark, v.pretheme, ct.theme, tw.type_name, 
               ct.confirmed_admin, ct.confirmed_teacher, ct.confirmed_student, ct.date
        FROM vkr v
        JOIN types_work tw ON v.type_work_id = tw.type_work_id
        JOIN changes_theme ct ON v.vkr_id = ct.vkr_id
        WHERE v.student_id = (
            SELECT student_id
            FROM students
            WHERE user_id = :user_id
        )
        AND ct.date = (
            SELECT MAX(date)
            FROM changes_theme
            WHERE vkr_id = v.vkr_id
        )
    ");
    
    // Выполняем запрос с параметром user_id
    $stmt->execute(['user_id' => $user_id]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($result) {
        // Если данные найдены, отправляем их в ответ
        echo json_encode($result);
    } else {
        // Если данных нет, отправляем ошибку
        http_response_code(404);
        echo json_encode(['error' => 'Данные не найдены']);
    }

} catch (Exception $e) {
    // Ошибка декодирования токена
    http_response_code(401);
    echo json_encode(['error' => 'Неверный или просроченный токен']);
}
?>
