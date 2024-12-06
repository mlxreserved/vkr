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

    // Подготавливаем SQL запрос для получения групп без потока
    $stmt = $pdo->prepare("
        SELECT g.group_id, g.group_name
        FROM groups_catalog g
        WHERE g.stream_id IS NULL AND g.closed IS NOT TRUE
    ");
    $stmt->execute();

    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($groups) {
        // Если группы найдены, отправляем их в ответ
        echo json_encode(['groups' => $groups]);
    } else {
        // Если данных нет, отправляем ошибку
        http_response_code(404);
        echo json_encode(['error' => 'Группы без потока не найдены']);
    }

} catch (Exception $e) {
    // Ошибка декодирования токена
    http_response_code(409);
    echo json_encode(['error' => 'Неверный или просроченный токен', 'text' => $e]);
}
?>
