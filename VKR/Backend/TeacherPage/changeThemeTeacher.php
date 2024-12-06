<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../Login/optionsJWT.php'; // Для работы с секретным ключом
require __DIR__ . '/../database.php'; // Подключаем базу данных
use Firebase\JWT\JWT;
use Firebase\JWT\Key; // Подключаем класс Key для указания ключа и алгоритма


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($role_id != 2) {
        http_response_code(402);
        echo json_encode(['error' => 'Нет прав доступа']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $vkr_id = $data['vkr_id'];
    $theme = $data['theme'];
    $confirmed_admin = $data['confirmed_admin'];
    $confirmed_teacher = $data['confirmed_teacher'];
    $confirmed_student = $data['confirmed_student'];

    $sql = ("INSERT INTO 
    CHANGES_THEME (VKR_ID, THEME, CONFIRMED_ADMIN, CONFIRMED_TEACHER, CONFIRMED_STUDENT, DATE)
    VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$vkr_id, $theme, $confirmed_admin, $confirmed_teacher, $confirmed_student]);

    if ($result) {
        http_response_code(201); // Успешно создано
        echo json_encode(["message" => "Пользователь успешно добавлен"]);
    } else {
        http_response_code(500); // Ошибка сервера
        echo json_encode(["error" => "Ошибка при добавлении пользователя"]);
    }
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Неверный метод']);
}
?>