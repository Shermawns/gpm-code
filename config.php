<?php
/**
 * GPM Dashboard - Arquivo de Configuração
 * Gerencia a conexão com o banco de dados PostgreSQL via Docker
 */

// Configurações do banco de dados (baseadas no seu docker-compose.yml)
$db_host = 'db';           // <-- CORRETO: O nome do serviço 'db' no Docker
$db_port = '5432';         // <-- CORRETO: A porta INTERNA do contêiner
$db_name = 'gpm';          // <-- SEU BANCO
$db_user = 'postgres';     // <-- SEU USUÁRIO (ATUALIZADO)
$db_pass = 'admin';        // <-- SUA SENHA (ATUALIZADA)

/**
 * Função para estabelecer conexão com o banco de dados PostgreSQL usando PDO
 * Retorna o objeto PDO ou exibe erro em caso de falha
 */
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

/**
 * Função auxiliar para retornar dados em formato JSON
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}
?>