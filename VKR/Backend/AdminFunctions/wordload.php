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
        http_response_code(403); // Ошибка доступа
        echo json_encode(['error' => 'Нет прав доступа']);
        exit;
    }

    // Выполняем SQL-запрос
    $stmt = $pdo->prepare("
        SELECT 
            s.stream_name, s.stream_id, t.teacher_id, u.name, u.lastname, u.surname, v.vkr_id 
        FROM 
            teachers t
            JOIN real_teacher rt ON rt.teacher_id = t.teacher_id
            JOIN nominal_teacher nt ON nt.teacher_id = t.teacher_id
            JOIN vkr v ON v.vkr_id = rt.vkr_id AND v.vkr_id = nt.vkr_id
            JOIN users u ON t.user_id = u.user_id
            JOIN students st ON v.student_id = st.student_id
            JOIN groups_catalog_students gct ON st.student_id = gct.students_student_id
            JOIN groups_catalog gc ON gc.group_id = gct.groups_catalog_group_id
            JOIN streams s ON gc.stream_id = s.stream_id
    ");
    
    $stmt->execute();

    // Получаем все результаты запроса
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($result) {
        // Отправляем успешный ответ с данными
        echo json_encode($result);
    } else {
        // Если данных нет
        http_response_code(404);
        echo json_encode(['error' => 'Данные не найдены.']);
    }

} catch (Exception $e) {
    // Ошибка декодирования токена
    http_response_code(409);
    echo json_encode(['error' => 'Неверный или просроченный токен', 'text' => $e->getMessage()]);
}
?>
