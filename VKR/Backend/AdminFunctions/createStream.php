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

    // Проверка прав доступа (только администратор может создавать потоки)
    if ($role_id != 1) {
        http_response_code(402);
        echo json_encode(['error' => 'Нет прав доступа']);
        exit;
    }

    // Проверяем, переданы ли необходимые данные
    if (empty($data['stream_name']) || empty($data['groups']) || !is_array($data['groups'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Не переданы данные для создания потока или групп']);
        exit;
    }

    $streamName = $data['stream_name'];
    $groups = $data['groups']; // Массив с ID групп

    // Начинаем транзакцию
    $pdo->beginTransaction();

    // 1. Добавляем новый поток в таблицу `streams`
    $stmt = $pdo->prepare("
        INSERT INTO streams (stream_name)
        VALUES (:stream_name)
    ");
    $stmt->bindParam(':stream_name', $streamName, PDO::PARAM_STR);

    if ($stmt->execute()) {
        // Получаем ID только что вставленного потока
        $stream_id = $pdo->lastInsertId(); 

        // 2. Обновляем таблицу `groups_catalog` для всех выбранных групп
        if (count($groups) === 1) {
            // Если только одна группа, используем "=" вместо "IN"
            $stmt = $pdo->prepare("
                UPDATE groups_catalog
                SET stream_id = ?
                WHERE group_id = ?
            ");
            if (!$stmt->execute([$stream_id, $groups[0]])) {
                throw new Exception('Ошибка при обновлении группы');
            }
        } else {
            // Если несколько групп, используем "IN" с подготовленными выражениями
            $placeholders = implode(',', array_fill(0, count($groups), '?')); // для IN (?, ?, ?)
            $stmt = $pdo->prepare("
                UPDATE groups_catalog
                SET stream_id = ?
                WHERE group_id IN ($placeholders)
            ");
            
            // Выполняем запрос
            $params = array_merge([$stream_id], $groups); // Все параметры передаются позиционно
            if (!$stmt->execute($params)) {
                throw new Exception('Ошибка при обновлении групп');
            }
        }

        // Если оба запроса прошли успешно, подтверждаем изменения
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Поток успешно создан']);
    } else {
        throw new Exception('Ошибка при добавлении потока в таблицу streams');
    }

} catch (Exception $e) {
    // В случае ошибки откатываем транзакцию
    $pdo->rollBack();
    
    // Ошибка декодирования токена или выполнения запроса
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
