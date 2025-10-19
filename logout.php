<?php
/**
 * GPM Dashboard - Logout
 * Encerra a sessão do usuário e redireciona para login
 */
session_start();

// Destrói todas as variáveis de sessão
$_SESSION = array();

// Destrói a sessão
session_destroy();

// Redireciona para a tela de login
header('Location: index.php');
exit;
?>