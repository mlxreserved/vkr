<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



// Обработка CORS для всех запросов
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Разрешаем запросы с любых источников (или с конкретного)
    header("Access-Control-Allow-Origin: http://localhost:8080");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Credentials: true");
    exit(0);  // Завершаем обработку для OPTIONS запроса
}

// Общая обработка для других методов
header("Access-Control-Allow-Origin: http://localhost:8080");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// Получаем URL и перенаправляем на соответствующие файлы
$request = $_SERVER['REQUEST_URI'];
switch ($request) {
    case '/login':
        include "Login/login.php";
        break;
    case '/logout':
        include "Login/logout.php";
        break;
    case '/updateToken':
        include "Login/refresh_token.php";
        break;
    case '/changePassword':
        include 'Login/change_password.php';
        break;
    case '/students':
        include "get_all_students_from_db.php";
        break;
    case '/update_state':
        include "update_state.php";
        break;
    default:
        http_response_code(445);
        echo json_encode(['error' => 'Not found']);
        break;
}
?>
