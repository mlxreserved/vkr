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

    // Получаем group_id из POST-запроса
    $data = json_decode(file_get_contents('php://input'), true);
    $groupId = $data['groupId'] ?? null;

    if (!$groupId) {
        http_response_code(400);
        echo json_encode(['error' => 'Не указан group_id.']);
        exit;
    }

    // Обновляем запись в базе данных, устанавливаем stream_id = NULL для указанной группы
    $stmt = $pdo->prepare("
        UPDATE groups_catalog
        SET stream_id = NULL
        WHERE group_id = :groupId
    ");

    // Подставляем параметр group_id
    $stmt->bindParam(':groupId', $groupId, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Если успешно обновили, отправляем ответ
        echo json_encode(['success' => 'Группа успешно удалена из потока.']);
    } else {
        // Если группа не найдена или не изменена
        http_response_code(404);
        echo json_encode(['error' => 'Группа не найдена или уже не привязана к потоку.']);
    }

} catch (Exception $e) {
    // Ошибка декодирования токена
    http_response_code(409);
    echo json_encode(['error' => 'Неверный или просроченный токен', 'text' => $e]);
}
?>
