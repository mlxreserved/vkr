<?php
$host = '77.221.138.236';
$port = '5432';
$dbname = 'vkr_project'; 
$user = 'vkr';
$password = 'sasha'; 


try {
    
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'connection error']);
    exit;
}
?>