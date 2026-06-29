<?php
// =====================================================
// LOGOUT
// Arquivo: pages/auth/logout.php
// =====================================================

require_once __DIR__ . '/../../includes/funcoes.php';

// Verifica se o usuário está logado
if (!usuario_logado()) {
    set_mensagem('erro', 'Você não está logado.');
    header("Location: " . BASE_URL);
    exit;
}

// Destroi a sessão
$_SESSION = [];

// Remove cookie de sessão
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroi a sessão completamente
session_destroy();

// Previne cache
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Mensagem e redirecionamento para a RAIZ
set_mensagem('sucesso', 'Você foi desconectado com sucesso!');
header("Location: " . BASE_URL);
exit;