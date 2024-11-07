<?php
require 'vendor/autoload.php';
require 'Login/optionsJWT.php';
require 'database.php';
use Firebase\JWT\JWT;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $data = json_decode(file_get_contents('php://input'), true);
    $vkr_id = $data['vkr_id'];
    $pretheme = $data['pretheme'];
    $theme = $data['theme'];
    $confirmed_admin = $data['confirmed_admin'];
    $confirmed_teacher = $data['confirmed_teacher'];
    $confirmed_student = $data['confirmed_student'];

    $sql = ("INSERT INTO 
    CHANGES_THEME (VKR_ID, PRETHEME, THEME, CONFIRMED_ADMIN, CONFIRMED_TEACHER, CONFIRMED_STUDENT, DATE)
    VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$vkr_id, $pretheme, $theme, $confirmed_admin, $confirmed_teacher, $confirmed_student]);

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