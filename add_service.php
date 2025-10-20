<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['logged_in'])) {
    jsonResponse(['success' => false, 'message' => 'Usuário não autenticado'], 401);
}




if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Método não permitido'], 405);
}


// validacao dos dados
$equipe_id = filter_input(INPUT_POST, 'equipe_id', FILTER_VALIDATE_INT);
$quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);
$data_registro = filter_input(INPUT_POST, 'data_registro', FILTER_UNSAFE_RAW); 
$observacao = filter_input(INPUT_POST, 'observacao', FILTER_UNSAFE_RAW);


if (!$equipe_id || !$quantidade || !$data_registro) {
    jsonResponse([
        'success' => false, 
        'message' => 'Campos obrigatórios não preenchidos corretamente'
    ], 400);
}


if ($quantidade < 1) {
    jsonResponse([
        'success' => false, 
        'message' => 'A quantidade deve ser maior que zero'
    ], 400);
}


$date = DateTime::createFromFormat('Y-m-d', $data_registro);
if (!$date || $date->format('Y-m-d') !== $data_registro) {
    jsonResponse([
        'success' => false, 
        'message' => 'Data inválida'
    ], 400);
}



try {

    $conn = getConnection();

    $sql = "INSERT INTO servicos (equipe_id, quantidade, data_registro, observacao) 
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    $stmt->execute([$equipe_id, $quantidade, $data_registro, $observacao]);


    $lastInsertId = $conn->lastInsertId();

    $stmtEquipe = $conn->prepare("SELECT nome FROM equipes WHERE id = ?");
    $stmtEquipe->execute([$equipe_id]);
    
    $equipe = $stmtEquipe->fetch(PDO::FETCH_ASSOC);
    
    $equipeNome = $equipe ? $equipe['nome'] : 'Equipe Desconhecida';
    jsonResponse([
        'success' => true, 
        'message' => "✅ {$quantidade} serviço(s) registrado(s) para {$equipeNome}!",
        'id' => $lastInsertId
    ]);

} catch (PDOException $e) {
    jsonResponse([
        'success' => false, 
        'message' => 'Erro no banco de dados: ' . $e->getMessage()
    ], 500);
}

?>