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

    $stmt = $pdo->prepare("
        select group_id, group_name from groups_catalog
        where closed is not TRUE and stream_id is null
    ");
    $stmt->execute();
    $groups= $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        select * from streams s, groups_catalog g 
        where s.stream_id = g.stream_id and closed is not true
    ");
    $stmt->execute();
    $streams= $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Создаем результативный массив содержащий свободные группы и потоки
    $result = ["groups" => $groups, "streams" => $streams];

    if ($groups and $streams) {
        // Если данные найдены, отправляем их в ответ
        echo json_encode($result);

    } 
    else {
        // Если данных нет, отправляем ошибку
        http_response_code(404);
        echo json_encode(['error' => 'Данные не найдены']);
    }

    

} catch (Exception $e) {
    // Ошибка декодирования токена
    http_response_code(409);
    echo json_encode(['error' => 'Неверный или просроченный токен', 'text' => $e]);
}
?>