<?php

session_start();

if (isset($_SESSION['logged_in'])) {
    header('Location: dashboard.php');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    

    $_SESSION['logged_in'] = true;
    $_SESSION['usuario'] = $_POST['usuario'] ?? 'Gestor';
    header('Location: dashboard.php');
    exit;


}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GPM Dashboard - Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <div class="logo">
                    <svg width="50" height="50" viewBox="0 0 50 50" fill="none">
                        <rect width="50" height="50" rx="10" fill="#4A90E2"/>
                        <path d="M15 35V25L25 15L35 25V35H28V28H22V35H15Z" fill="white"/>
                    </svg>
                </div>
                <h1>GPM Dashboard</h1>
                <p>Sistema de Gestão de Produtividade</p>
            </div>

            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <label for="usuario">Usuário</label>
                    <input 
                        type="text" 
                        id="usuario" 
                        name="usuario" 
                        placeholder="Digite seu usuário"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input 
                        type="password" 
                        id="senha" 
                        name="senha" 
                        placeholder="Digite sua senha"
                        required
                    >
                </div>

                <button type="submit" class="btn-primary">Entrar</button>

            </form>

            <div class="login-footer">
                <p>&copy; 2024 GPM Soluções - Gestão Operacional</p>
            </div>
        </div>
    </div>
</body>
</html>