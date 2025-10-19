<?php
/**
 * GPM Dashboard - Adicionar Serviços
 * Processa o formulário de registro de serviços via AJAX (Versão PDO)
 */
session_start();
require_once 'config.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['logged_in'])) {
    jsonResponse(['success' => false, 'message' => 'Usuário não autenticado'], 401);
}

// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Método não permitido'], 405);
}

// Valida os dados recebidos
$equipe_id = filter_input(INPUT_POST, 'equipe_id', FILTER_VALIDATE_INT);
$quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);
// FILTER_SANITIZE_STRING está obsoleto, use htmlspecialchars ou FILTER_UNSAFE_RAW (mas validamos a data depois)
$data_registro = filter_input(INPUT_POST, 'data_registro', FILTER_UNSAFE_RAW); 
$observacao = filter_input(INPUT_POST, 'observacao', FILTER_UNSAFE_RAW);

// Verifica se os campos obrigatórios foram preenchidos
if (!$equipe_id || !$quantidade || !$data_registro) {
    jsonResponse([
        'success' => false, 
        'message' => 'Campos obrigatórios não preenchidos corretamente'
    ], 400);
}

// Valida quantidade mínima
if ($quantidade < 1) {
    jsonResponse([
        'success' => false, 
        'message' => 'A quantidade deve ser maior que zero'
    ], 400);
}

// Valida formato da data
$date = DateTime::createFromFormat('Y-m-d', $data_registro);
if (!$date || $date->format('Y-m-d') !== $data_registro) {
    jsonResponse([
        'success' => false, 
        'message' => 'Data inválida'
    ], 400);
}

// --- INÍCIO DA CORREÇÃO (SINTAXE PDO COM TRY...CATCH) ---

try {
    // 1. Conecta ao banco de dados
    $conn = getConnection(); // $conn é um objeto PDO

    // 2. Prepara a query para inserção (placeholders são '?')
    $sql = "INSERT INTO servicos (equipe_id, quantidade, data_registro, observacao) 
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // 3. Executa a query passando os parâmetros em um array
    // Isso substitui o bind_param() e já protege contra SQL Injection
    $stmt->execute([$equipe_id, $quantidade, $data_registro, $observacao]);

    // 4. Pega o ID da linha recém-inserida (substitui insert_id)
    $lastInsertId = $conn->lastInsertId();

    // 5. Busca o nome da equipe para confirmação
    $stmtEquipe = $conn->prepare("SELECT nome FROM equipes WHERE id = ?");
    $stmtEquipe->execute([$equipe_id]);
    
    // 6. Busca o resultado (substitui get_result e fetch_assoc)
    $equipe = $stmtEquipe->fetch(PDO::FETCH_ASSOC);
    
    $equipeNome = $equipe ? $equipe['nome'] : 'Equipe Desconhecida';

    // 7. Envia a resposta de sucesso
    jsonResponse([
        'success' => true, 
        'message' => "✅ {$quantidade} serviço(s) registrado(s) para {$equipeNome}!",
        'id' => $lastInsertId
    ]);

} catch (PDOException $e) {
    // 8. Captura qualquer erro do banco de dados
    // (substitui as verificações de $stmt->error e $conn->error)
    jsonResponse([
        'success' => false, 
        'message' => 'Erro no banco de dados: ' . $e->getMessage()
    ], 500);
}

// 9. Não é necessário $stmt->close() ou $conn->close()
// jsonResponse() chama exit() e o PDO fecha as conexões automaticamente.
?>