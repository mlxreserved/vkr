<?php
require 'vendor/autoload.php';
require 'optionsJWT.php';
require 'database.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Authorization, Content-Type");

// Обработка метода OPTIONS для CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Content-Type: application/json");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, OPTIONS");
    header("Access-Control-Allow-Headers: Authorization, Content-Type");
    exit(0);
}

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Проверка, что токена нет
$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'Authorization header missing']);
    exit;
}

// Получение токена
$authHeader = $headers['Authorization'];
$jwt = str_replace('Bearer ', '', $authHeader);

try {

    // Декодируем JWT-токен, в нем есть id и role
    $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
    $userId = $decoded->id;
    $userRole = $decoded->role;

    // Проверяем роль пользователя
    switch ($userRole) {
        case 'admin':
            http_response_code(403); // Forbidden
            echo json_encode(['error' => 'Admins are not allowed to access this resource']);
            break;

        case 'teacher':
            $stmt = $pdo->prepare("SELECT name, lastname, surname FROM users WHERE user_id = :id");
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($teacher) {
                echo json_encode(['name' => $teacher['name'], 
                                'lastname' => $teacher['lastname'],
                                'surname' => $teacher['surname']
                            ]);
            } 
            else {
                http_response_code(404); // Not Found
                echo json_encode(['error' => 'Teacher not found']);
            }
            break;

        case 'student':
            $stmt = $pdo->prepare("SELECT name, lastname, surname FROM users WHERE user_id = :id");
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $studentData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($studentData) {
                echo json_encode($studentData);
            } else {
                http_response_code(404); // Not Found
                echo json_encode(['error' => 'No records found for the student']);
            }
            break;

        default:
            http_response_code(400); // Bad Request
            echo json_encode(['error' => 'Invalid role']);
    }

} catch (Exception $e) {
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'Invalid or expired token', 'details' => $e->getMessage()]);
}
