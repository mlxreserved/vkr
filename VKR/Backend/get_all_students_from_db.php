<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/Login/optionsJWT.php';
use Firebase\JWT\JWT;
require 'database.php';
use Firebase\JWT\Key;

// Общая обработка для других методов
header("Access-Control-Allow-Origin: http://localhost:8080");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

if($_SERVER['REQUEST_METHOD'] === 'GET'){

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

    try {
        
        
        $sql = "SELECT 
                    S.STUDENT_ID,
                    U.NAME,
                    U.LASTNAME,
                    GC.GROUP_NAME,
                    CT.THEME,
                    CT.VKR_ID,
                    CT.CONFIRMED_STUDENT,
                    CT.CONFIRMED_TEACHER,
                    CT.CONFIRMED_ADMIN
                FROM
                    STUDENTS S
                JOIN 
                    USERS U ON S.USER_ID = U.USER_ID
                JOIN
                    GROUPS_CATALOG_STUDENTS GCS ON S.STUDENT_ID = GCS.STUDENTS_STUDENT_ID
                JOIN
                    GROUPS_CATALOG GC ON GCS.GROUPS_CATALOG_GROUP_ID = GC.GROUP_ID
                JOIN
                    VKR V ON S.STUDENT_ID = V.STUDENT_ID
                JOIN
                    CHANGES_THEME CT ON V.VKR_ID = CT.VKR_ID
                JOIN
                    REAL_TEACHER RT ON V.VKR_ID = RT.VKR_ID
                WHERE CT.DATE = (
                    SELECT MAX(DATE)
                    FROM CHANGES_THEME
                    WHERE VKR_ID=V.VKR_ID
                )";
                // "
                
                
        $stmt = $pdo->prepare($sql);
        // $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($results) {
            // Возвращаем данные в формате JSON
            echo json_encode($results);
        } else {
            echo json_encode(['error' => 'No data found']);
        }
    } catch (PDOException $e) {
        // Возвращаем ошибку в формате JSON
        echo json_encode(['error' => $e->getMessage()]);
        echo "Ошибка подключения к базе данных: " . $e->getMessage();
    }
}


?>