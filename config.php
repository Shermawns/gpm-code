<?php

$db_host = 'db';           
$db_port = '5432';     
$db_name = 'gpm';       
$db_user = 'postgres';    
$db_pass = 'admin';       


function getConnection() {
    global $db_host, $db_port, $db_name, $db_user, $db_pass;

    $dsn = "pgsql:host=$db_host;port=$db_port;dbname=$db_name";

    try {
        $pdo = new PDO($dsn, $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;

    } catch (PDOException $e) {
        die("Erro na conexão com o banco de dados: " . $e->getMessage());
    }
}


function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}
?>