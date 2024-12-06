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
    $confirmed_teacher = $data['confirmed_teacher'] ? true : false;
    $confirmed_student = $data['confirmed_student'];
    $sql = ("INSERT INTO 
    CHANGES_THEME (VKR_ID, THEME, CONFIRMED_ADMIN, CONFIRMED_TEACHER, CONFIRMED_STUDENT, DATE)
    VALUES (:vkr_id, :theme, :confirmed_admin, :confirmed_teacher, :confirmed_student, NOW())");
    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(':confirmed_student', $confirmed_student, PDO::PARAM_BOOL);
    $stmt->bindValue(':confirmed_teacher', $confirmed_teacher, PDO::PARAM_BOOL);
    $stmt->bindValue(':confirmed_admin', $confirmed_admin, PDO::PARAM_BOOL);
    $stmt->bindValue(':vkr_id', $vkr_id);
    $stmt->bindValue(':theme', $theme);

    $result = $stmt->execute();

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