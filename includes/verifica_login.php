<?php
// =====================================================
// VERIFICAÇÃO DE LOGIN - CORRIGIDO E PADRONIZADO
// =====================================================

// Inicia a sessão se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclui as funções auxiliares
require_once __DIR__ . '/funcoes.php';

// Verifica se o usuário está logado
if (!usuario_logado()) {
    // Define a mensagem de erro
    set_mensagem('erro', 'Você precisa estar logado para acessar esta página.');
    
    // Redireciona para o login
    redirecionar('pages/auth/login.php');
    exit;
}

// =====================================================
// VERIFICAÇÕES ADICIONAIS (OPCIONAL)
// =====================================================

// Obtém a conexão com o banco de dados
require_once __DIR__ . '/conexao.php';

// Verifica se o usuário ainda existe no banco de dados
try {
    $stmt = $conn->prepare("SELECT id, nome, email, foto FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Se o usuário não existe mais, faz logout
    if (!$usuario) {
        // Limpa a sessão
        session_destroy();
        
        // Define mensagem de erro
        set_mensagem('erro', 'Sua conta foi removida ou não existe mais.');
        
        // Redireciona para o login
        redirecionar('pages/auth/login.php');
        exit;
    }
    
    // Atualiza os dados do usuário na sessão (caso tenha mudado)
    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_foto'] = $usuario['foto'];
    
} catch (PDOException $e) {
    // Em caso de erro no banco, loga o erro
    error_log("Erro ao verificar usuário: " . $e->getMessage());
    
    // Se o erro for grave, pode redirecionar para o login
    // set_mensagem('erro', 'Erro ao verificar sua conta. Tente novamente.');
    // redirecionar('pages/auth/login.php');
    // exit;
}

// =====================================================
// VERIFICA SE O USUÁRIO TEM PERMISSÕES ESPECÍFICAS
// =====================================================

/**
 * Verifica se o usuário é administrador
 * (Descomente se tiver um campo 'admin' na tabela usuarios)
 */
/*
function usuario_e_admin(): bool {
    return isset($_SESSION['usuario_admin']) && $_SESSION['usuario_admin'] == 1;
}

// Se precisar restringir acesso apenas para admins
if (isset($requer_admin) && $requer_admin && !usuario_e_admin()) {
    set_mensagem('erro', 'Você não tem permissão para acessar esta página.');
    redirecionar('dashboard.php');
    exit;
}
*/

// =====================================================
// VERIFICAÇÃO DE TEMPO DE SESSÃO (OPCIONAL)
// =====================================================

/**
 * Verifica se a sessão expirou após X minutos de inatividade
 */
/*
$tempo_sessao = 60 * 30; // 30 minutos

if (isset($_SESSION['ultima_atividade']) && (time() - $_SESSION['ultima_atividade'] > $tempo_sessao)) {
    // Sessão expirada
    session_unset();
    session_destroy();
    
    set_mensagem('erro', 'Sua sessão expirou. Faça login novamente.');
    redirecionar('pages/auth/login.php');
    exit;
}

// Atualiza o tempo da última atividade
$_SESSION['ultima_atividade'] = time();
*/

// =====================================================
// VERIFICAÇÃO DE IP OU USER AGENT (OPCIONAL)
// =====================================================

/**
 * Verifica se o IP ou User Agent mudou (previne roubo de sessão)
 */
/*
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

if (isset($_SESSION['ip']) && $_SESSION['ip'] !== $ip) {
    session_destroy();
    set_mensagem('erro', 'Sessão inválida. Faça login novamente.');
    redirecionar('pages/auth/login.php');
    exit;
}

if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $user_agent) {
    session_destroy();
    set_mensagem('erro', 'Sessão inválida. Faça login novamente.');
    redirecionar('pages/auth/login.php');
    exit;
}

$_SESSION['ip'] = $ip;
$_SESSION['user_agent'] = $user_agent;
*/

// =====================================================
// REDIRECIONAMENTO CASO O USUÁRIO JÁ ESTEJA LOGADO
// =====================================================

/**
 * Se o arquivo for incluído em páginas de login/cadastro,
 * redireciona o usuário já logado para o dashboard
 */
if (isset($redirecionar_se_logado) && $redirecionar_se_logado === true) {
    if (usuario_logado()) {
        redirecionar('pages/noticias/dashboard.php');
        exit;
    }
}

// =====================================================
// VARIAVEIS GLOBAIS PARA USO NAS PÁGINAS
// =====================================================

// Disponibiliza os dados do usuário para as páginas
$usuario_logado = [
    'id' => $_SESSION['usuario_id'] ?? null,
    'nome' => $_SESSION['usuario_nome'] ?? 'Usuário',
    'email' => $_SESSION['usuario_email'] ?? '',
    'foto' => $_SESSION['usuario_foto'] ?? null
];

// Debug (se ativado)
if (defined('DEBUG') && DEBUG === true) {
    // debug_var($usuario_logado, 'Usuário logado');
}
?>