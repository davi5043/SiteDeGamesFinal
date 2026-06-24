<?php
// =====================================================
// LOGOUT
// Arquivo: pages/auth/logout.php
// Descrição: Encerra a sessão do usuário e redireciona
// =====================================================

require_once __DIR__ . '/../../includes/funcoes.php';

// Destroi todas as variáveis de sessão
$_SESSION = [];

// Destroi a sessão
session_destroy();

// Redireciona para a página inicial
header("Location: " . BASE_URL);
exit;
