<?php
// =====================================================
// LOGOUT
// Arquivo: pages/auth/logout.php
// =====================================================

require_once __DIR__ . '/../../includes/funcoes.php';

// Destroi todas as variáveis de sessão
$_SESSION = [];

// Destroi a sessão
session_destroy();

// Redireciona para a página inicial (fora da pasta pages)
header("Location: ../../index.php");
exit;