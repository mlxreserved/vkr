<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../Login/optionsJWT.php'; // Для работы с секретным ключом
require __DIR__ . '/../database.php'; // Подключаем базу данных
use Firebase\JWT\JWT;
use Firebase\JWT\Key; // Подключаем класс Key для указания ключа и алгоритма

$accessToken = $_COOKIE['auth_token'] ?? null;

if (!$accessToken) {
    http_response_code(401);
    echo json_encode(['error' => 'Токен не найден.']);
    exit;
}

try {
    // Декодируем токен
    $decoded = JWT::decode($accessToken, new Key($secretKey, 'HS256'));
    $role_id = $decoded->role_id; // Получаем user_id из токена

    // Отправивший запрос пользователь не является администратором системы
    if ($role_id != 1) {
        http_response_code(402);
        echo json_encode(['error' => 'Нет прав доступа']);
        exit;
    }

    // Получаем id потока из URL или из запроса
    $streamId = $_GET['streamId'] ?? null; // Параметр streamId из URL

    if (!$streamId) {
        http_response_code(400);
        echo json_encode(['error' => 'Не указан id потока.']);
        exit;
    }

    // Подготавливаем SQL запрос для получения данных потока и групп, связанных с ним
    $stmt = $pdo->prepare("
        SELECT s.*, g.group_id, g.group_name
        FROM streams s
        LEFT JOIN groups_catalog g ON s.stream_id = g.stream_id
        WHERE s.stream_id = :streamId AND g.closed IS NOT TRUE
    ");
    
    // Подставляем параметр streamId
    $stmt->bindParam(':streamId', $streamId, PDO::PARAM_INT); // Убедитесь, что тип данных правильный для PostgreSQL
    $stmt->execute();
    
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($result) {
        // Если данные найдены, отправляем их в ответ
        echo json_encode($result);
    } else {
        // Если данных нет, отправляем ошибку
        http_response_code(404);
        echo json_encode(['error' => 'Поток или группы не найдены']);
    }

} catch (Exception $e) {
    // Ошибка декодирования токена
    http_response_code(409);
    echo json_encode(['error' => 'Неверный или просроченный токен', 'text' => $e]);
}
?>
