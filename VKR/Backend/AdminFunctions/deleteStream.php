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

    // Получаем данные из POST-запроса
    $data = json_decode(file_get_contents('php://input'), true);

    // Отправивший запрос пользователь не является администратором системы
    if ($role_id != 1) {
        http_response_code(402);
        echo json_encode(['error' => 'Нет прав доступа']);
        exit;
    }

    if (empty($data['stream_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Не передан идентификатор потока.']);
        exit;
    }

    // Преобразуем stream_id в int
    $stream_id = (int)$data['stream_id'];

    // Отключаем поток из групп
    $stmt = $pdo->prepare("
        UPDATE groups_catalog 
        SET stream_id = NULL 
        WHERE stream_id = :stream_id
    ");
    
    // Используем execute() с параметрами
    if (!$stmt->execute([':stream_id' => $stream_id])) {
        http_response_code(500);
        echo json_encode(['error' => 'Ошибка при обновлении таблицы groups_catalog']);
        exit;
    }

    // Удаляем сам поток из таблицы streams
    $stmt = $pdo->prepare("
        DELETE FROM streams 
        WHERE stream_id = :stream_id
    ");
    
    // Используем execute() с параметрами
    if ($stmt->execute([':stream_id' => $stream_id])) {
        echo json_encode(['success' => true, 'message' => 'Поток успешно удален']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Ошибка при удалении потока из таблицы streams']);
    }

} catch (Exception $e) {
    // Ошибка декодирования токена
    http_response_code(409);
    echo json_encode(['error' => 'Неверный или просроченный токен', 'text' => $e->getMessage()]);
}
?>
