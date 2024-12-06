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

    // Получаем stream_id и список group_id из POST-запроса
    $data = json_decode(file_get_contents('php://input'), true);
    $streamId = $data['streamId'] ?? null;
    $groupIds = $data['groupIds'] ?? null;

    if (!$streamId || !$groupIds || !is_array($groupIds)) {
        http_response_code(400);
        echo json_encode(['error' => 'Не указан stream_id или group_ids.', 'groups' => $groupIds, 'stream' => $streamId]);
        exit;
    }

    // Генерируем список для подготовленного выражения
    if (count($groupIds) > 1) {
        // Если групп несколько, используем IN
        $placeholders = implode(',', array_fill(0, count($groupIds), '?'));
        $query = "
            UPDATE groups_catalog
            SET stream_id = ?
            WHERE group_id IN ($placeholders)
        ";
    } else {
        // Если только одна группа, используем простое сравнение
        $query = "
            UPDATE groups_catalog
            SET stream_id = ?
            WHERE group_id = ?
        ";
    }
    
    // Подготавливаем SQL запрос
    $stmt = $pdo->prepare($query);
    
    // Объединяем streamId и все group_ids в массив для подстановки
    $params = array_merge([$streamId], $groupIds);
    
    // Выполняем запрос
    $stmt->execute($params);

    if ($stmt->rowCount() > 0) {
        // Если хотя бы одна строка была обновлена, отправляем успешный ответ
        echo json_encode(['success' => 'Группы успешно добавлены в поток.']);
    } else {
        // Если ничего не было обновлено
        http_response_code(404);
        echo json_encode(['error' => 'Группы не найдены или уже привязаны к потоку.']);
    }

} catch (Exception $e) {
    // Ошибка декодирования токена
    http_response_code(409);
    echo json_encode(['error' => 'Неверный или просроченный токен', 'text' => $e]);
}
?>
