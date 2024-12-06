<?php
require __DIR__ . '/../vendor/autoload.php';
require 'optionsJWT.php';
use Firebase\JWT\JWT;
require __DIR__ . '/../database.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем refresh token из куки
    if (!isset($_COOKIE['refresh_token'])) {
        echo json_encode(['error' => 'Refresh token not provided']);
        exit;
    }

    $refreshToken = $_COOKIE['refresh_token'];

    // Проверка refresh token в базе данных
    $stmt = $pdo->prepare("SELECT * FROM users WHERE refresh_token = :refreshToken");
    $stmt->execute($refreshToken);
    $user = $stmt->fetch();

    if ($user) {
        // Если refresh token найден в базе, генерируем новый access token

        // Устанавливаем время жизни нового access token
        $issuedAt = time();
        $accessTokenExpiration = $issuedAt + 3600; // Токен доступа действует 1 час

        // Создаем новый access token
        $accessPayload = [
            'iat' => $issuedAt,
            'exp' => $accessTokenExpiration,
            'role_id' => $user['role_id'],
            'user_id' => $user['user_id'],
        ];
        $accessToken = JWT::encode($accessPayload, $secretKey, 'HS256');

        // Устанавливаем новый access token в куки
        setcookie("auth_token", $accessToken, [
            'expires' => $accessTokenExpiration,
            'path' => '/',
            'secure' => true, 
            'httponly' => true, 
            'samesite' => 'Strict'
        ]);

        // Ответ клиенту: никаких данных, кроме куки с access_token, не нужно отправлять
        echo json_encode(['message' => 'Access token refreshed successfully']);
    } else {
        echo json_encode(['error' => 'Invalid refresh token']);
    }
}
?>
