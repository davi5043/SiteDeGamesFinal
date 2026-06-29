<?php
// =====================================================
// LOGOUT - CORRIGIDO E PADRONIZADO
// Arquivo: pages/auth/logout.php
// =====================================================

// Inicia a sessão se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclui as funções auxiliares
require_once __DIR__ . '/../../includes/funcoes.php';

// =====================================================
// VERIFICA SE O USUÁRIO ESTÁ LOGADO
// =====================================================
if (!usuario_logado()) {
    set_mensagem('erro', 'Você não está logado.');
    redirecionar('/');
    exit;
}

// =====================================================
// REGISTRA O LOGOUT (OPCIONAL - PARA AUDITORIA)
// =====================================================
$usuario_nome = $_SESSION['usuario_nome'] ?? 'Usuário';
$usuario_email = $_SESSION['usuario_email'] ?? 'Email não disponível';
error_log("Logout realizado: " . $usuario_email . " (" . $_SERVER['REMOTE_ADDR'] . ")");

// =====================================================
// LIMPA A SESSÃO COMPLETAMENTE
// =====================================================

// 1. Remove todas as variáveis da sessão
$_SESSION = [];

// 2. Remove o cookie de sessão (se existir)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    
    // Tenta remover o cookie com os parâmetros originais
    setcookie(
        session_name(),          // Nome do cookie de sessão
        '',                      // Valor vazio
        time() - 42000,          // Data no passado (expira)
        $params["path"],         // Caminho
        $params["domain"],       // Domínio
        $params["secure"],       // HTTPS?
        $params["httponly"]      // Acesso apenas via HTTP
    );
    
    // Tenta remover também com path raiz (para garantir)
    setcookie(
        session_name(),
        '',
        time() - 42000,
        '/',
        '',
        false,
        true
    );
}

// 3. Destroi a sessão
session_destroy();

// 4. Garante que a sessão foi destruída
if (session_status() === PHP_SESSION_ACTIVE) {
    session_unset();
    session_write_close();
}

// =====================================================
// PREVINE CACHE DA PÁGINA DE LOGOUT
// =====================================================
header("Cache-Control: no-cache, no-store, must-revalidate, private");
header("Pragma: no-cache");
header("Expires: 0");

// =====================================================
// MENSAGEM E REDIRECIONAMENTO
// =====================================================

// Define mensagem de sucesso
set_mensagem('sucesso', 'Você foi desconectado com sucesso!');

// Redireciona para a página inicial
redirecionar('/');
exit;
?>