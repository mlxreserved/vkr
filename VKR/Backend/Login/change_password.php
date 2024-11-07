<?php
require __DIR__ . '/../vendor/autoload.php';
require 'optionsJWT.php'; // Для работы с секретным ключом
use Firebase\JWT\JWT;
use Firebase\JWT\Key; // Подключаем класс Key для указания ключа и алгоритма
require __DIR__ . '/../database.php'; // Подключаем базу данных

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Получаем данные из запроса
    $data = json_decode(file_get_contents('php://input'), true);
    $oldPassword = $data['oldPassword'];
    $newPassword = $data['newPassword'];

    // Получаем JWT токен из cookie
    if (isset($_COOKIE['auth_token'])) {
        $jwt = $_COOKIE['auth_token']; // Получаем токен из cookie
    } else {
        http_response_code(401);
        echo json_encode(["error" => "Токен не предоставлен."]);
        exit();
    }

    try {
        // Декодируем JWT с использованием нового синтаксиса
        $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256')); // Указываем ключ и алгоритм через объект Key
        $user_id = $decoded->user_id; // Извлекаем user_id из декодированного токена

    } catch (Exception $e) {
        http_response_code(402);
        echo json_encode(["error" => "Неверный или просроченный токен."]);
        exit();
    }

    // Получаем данные пользователя из базы данных
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(404);
        echo json_encode(["error" => "Пользователь не найден."]);
        exit();
    }

    // Проверка старого пароля
    if (hash('sha256', $oldPassword) !== $user['password']) {
        http_response_code(400);
        echo json_encode(["error" => "Неверный старый пароль."]);
        exit();
    }

    // Хешируем новый пароль
    $hashedNewPassword = hash('sha256', $newPassword); // Используем SHA-256 для хэширования нового пароля

    // Обновляем пароль в базе данных
    $updateQuery = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $updateQuery->execute([$hashedNewPassword, $user_id]);

    if ($updateQuery) {
        echo json_encode(["success" => "Пароль успешно изменен."]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Ошибка при обновлении пароля."]);
    }
}
?>
