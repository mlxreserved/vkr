<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../Login/optionsJWT.php'; // Для работы с секретным ключом
require __DIR__ . '/../database.php'; // Подключаем базу данных
use Firebase\JWT\JWT;
use Firebase\JWT\Key; // Подключаем класс Key для указания ключа и алгоритма

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
        $role_id = $decoded->role_id;

    } catch (Exception $e) {
        http_response_code(402);
        echo json_encode(["error" => "Неверный или просроченный токен."]);
        exit();
    }

    try {
        if($role_id == 2) {
            $sql = "SELECT 
                        S.STUDENT_ID,
                        U.NAME,
                        U.LASTNAME,
                        GC.GROUP_NAME,
                        CT.THEME,
                        CT.VKR_ID,
                        CT.CONFIRMED_STUDENT,
                        CT.CONFIRMED_TEACHER,
                        CT.CONFIRMED_ADMIN,
                        STR.STREAM_NAME
                    FROM
                        STUDENTS S
                    JOIN 
                        USERS U ON S.USER_ID = U.USER_ID
                    JOIN
                        GROUPS_CATALOG_STUDENTS GCS ON S.STUDENT_ID = GCS.STUDENTS_STUDENT_ID
                    JOIN
                        GROUPS_CATALOG GC ON GCS.GROUPS_CATALOG_GROUP_ID = GC.GROUP_ID
                    JOIN 
                        STREAMS STR ON GC.STREAM_ID = STR.STREAM_ID
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
                    ) 
                    AND RT.TEACHER_ID = (SELECT TEACHER_ID FROM TEACHERS WHERE USER_ID = ?)
                    AND GC.CLOSED IS NOT TRUE";
                    
                    
                    
            $stmt = $pdo->prepare($sql);
            // $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->execute([$user_id]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($results) {
                // Возвращаем данные в формате JSON
                echo json_encode($results);
            } else {
                echo json_encode(['error' => 'No data found']);
            }
        } else {
            echo json_encode(['error' => 'You don\'t have permission']);
        }
    } catch (PDOException $e) {
        // Возвращаем ошибку в формате JSON
        echo json_encode(['error' => $e->getMessage()]);
        echo "Ошибка подключения к базе данных: " . $e->getMessage();
    }
}


?>