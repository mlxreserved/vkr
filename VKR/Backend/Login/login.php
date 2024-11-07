<?php
require __DIR__ . '/../vendor/autoload.php';
require 'optionsJWT.php';
use Firebase\JWT\JWT;
require __DIR__ . '/../database.php'; 


if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $data = json_decode(file_get_contents('php://input'), true);
    $login = $data['login'];
    $password = $data['password'];

    // Получаем данные пользователя из базы
    $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch();

    // Проверяем пароль
    if ($user && ($password == $user['password'])) {

        // Устанавливаем время жизни токенов
        $issuedAt = time();
        $accessTokenExpiration = $issuedAt + 3600; // Токен доступа действует 1 час
        $refreshTokenExpiration = $issuedAt + 3600 * 24 * 30; // Refresh token действует 30 дней

        // Создаем access token
        $accessPayload = [
            'iat' => $issuedAt,
            'exp' => $accessTokenExpiration,
            'role_id' => $user['role_id'],
            'user_id' => $user['user_id'],
        ];
        $accessToken = JWT::encode($accessPayload, $secretKey, 'HS256');

        // Создаем refresh token
        $refreshPayload = [
            'iat' => $issuedAt,
            'exp' => $refreshTokenExpiration,
            'user_id' => $user['user_id'],
        ];
        $refreshToken = JWT::encode($refreshPayload, $secretKey, 'HS256');

        // Сохраняем refresh token в базе данных
        $stmt = $pdo->prepare("UPDATE users SET refresh_token = ? WHERE user_id = ?");
        $stmt->execute([$refreshToken, $user['user_id']]);

        // Устанавливаем access token как HttpOnly и Secure куку
        setcookie("auth_token", $accessToken, [
            'expires' => $accessTokenExpiration,
            'path' => '/',
            'secure' => true, 
            'httponly' => true, 
            'samesite' => 'Strict'
        ]);

        // Устанавливаем refresh token как HttpOnly и Secure куку
        setcookie("refresh_token", $refreshToken, [
            'expires' => $refreshTokenExpiration,
            'path' => '/',
            'secure' => true, 
            'httponly' => true, 
            'samesite' => 'Strict'
        ]);

        // Ответ клиенту (можно отправить любые данные, которые хотите)
        http_response_code(200);
        echo json_encode(['role_id' => $user['role_id'], 'login' => $user['login']]);

    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Неверное имя пользователя или пароль']);
    }
}
?>
