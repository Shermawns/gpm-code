<?php
session_start();
require_once 'config.php';
/// nao pode funcionar sem o banco de dados, por isso é require_once
/// se fosse so require, iria mostrar um erro fatal
/// include é opcional para funcionar

// Verifica se o usuário está logado
if (!isset($_SESSION['logged_in'])) {
    jsonResponse(['success' => false, 'message' => 'Usuário não autenticado'], 401);
}
// credencial logged_in está presente na variavel de sessão, caso o sucess for false, ou seja nao estiver logado na sessao, vai emitir um erro 401


// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Método não permitido'], 405);
}
// 1. $_SERVER['REQUEST_METHOD']:
//    Esta é uma "superglobal" do PHP (uma variável que está sempre
//    disponível). Ela armazena qual método HTTP foi usado pelo
//    navegador/cliente para acessar esta página.
//    Os mais comuns são 'GET' (acessar uma URL) e 'POST' (enviar um formulário).

// 2. !== 'POST':
//    O 'fetch' do JavaScript no 'dashboard.php' foi configurado para
//    usar 'method: 'POST''.
//    Este 'if' verifica se o método NÃO É (!==) 'POST'.

// 3. Por que isso?
//    Isso impede que um usuário curioso (ou mal-intencionado) acesse
//    este arquivo diretamente pelo navegador (o que seria um método 'GET').
//    Este script foi feito APENAS para RECEBER dados de formulário,
//    não para ser "visitado".


// Valida os dados recebidos
$equipe_id = filter_input(INPUT_POST, 'equipe_id', FILTER_VALIDATE_INT);
$quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);
// FILTER_SANITIZE_STRING está obsoleto, use htmlspecialchars ou FILTER_UNSAFE_RAW (mas validamos a data depois)
$data_registro = filter_input(INPUT_POST, 'data_registro', FILTER_UNSAFE_RAW); 
$observacao = filter_input(INPUT_POST, 'observacao', FILTER_UNSAFE_RAW);
//- O que isso faz? Ele VALIDA se o dado é um número inteiro.
// - Se o usuário enviar "5", a variável $equipe_id vira o número 5.
// - Se o usuário enviar "abc" ou "1.5" ou algo malicioso, a variável
//   $equipe_id vira 'false'.

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