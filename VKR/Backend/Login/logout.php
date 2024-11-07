<?php
require __DIR__ . '/../vendor/autoload.php';
require 'optionsJWT.php';
use Firebase\JWT\JWT;
require __DIR__ . '/../database.php'; 

header("Access-Control-Allow-Origin: http://localhost:8080"); // Разрешаем доступ с вашего фронтенда
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Разрешаем необходимые методы
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Разрешаем необходимые заголовки
header("Access-Control-Allow-Credentials: true"); // Если вам нужно отправлять cookies или авторизационные заголовки



// Удаление токенов из cookies
setcookie("auth_token", "", time() - 3600, "/"); // Удаляем access token
setcookie("refresh_token", "", time() - 3600, "/"); // Удаляем refresh token

// Если нужно, можно удалить refresh token из базы данных
// Например, можно сделать запрос для обновления поля refresh_token на NULL или пустое значение
if (isset($_COOKIE['refresh_token'])) {
    $refreshToken = $_COOKIE['refresh_token'];
    
    // Обновляем запись в базе данных, удаляя refresh token
    $stmt = $pdo->prepare("UPDATE users SET refresh_token = NULL WHERE refresh_token = ?");
    $stmt->execute([$refreshToken]);
}

// Ответ клиенту, что пользователь успешно вышел
echo json_encode(['message' => 'Log Out BBB']);
?>
