<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/Login/optionsJWT.php';
use Firebase\JWT\JWT;
require 'database.php';

// Общая обработка для других методов
header("Access-Control-Allow-Origin: http://localhost:8080");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

if($_SERVER['REQUEST_METHOD'] === 'GET'){
    try {
        $sql = "SELECT 
                    S.STUDENT_ID,
                    U.NAME,
                    U.LASTNAME,
                    GC.GROUP_NAME,
                    CT.PRETHEME,
                    CT.THEME
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
                WHERE RT.TEACHER_ID = 1";
                
        $stmt = $pdo->prepare($sql);
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