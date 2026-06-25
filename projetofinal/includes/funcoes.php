<?php
// =====================================================
// FUNÇÕES AUXILIARES
// =====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('BASE_URL')) {
    $doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $project_root = str_replace('\\', '/', dirname(__DIR__));
    $base = str_replace($doc_root, '', $project_root);
    $base = rtrim($base, '/') . '/';
    define('BASE_URL', $base);
}

function usuario_logado() {
    return isset($_SESSION['usuario_id']);
}

function get_usuario_id() {
    return $_SESSION['usuario_id'] ?? null;
}

function get_usuario_nome() {
    return $_SESSION['usuario_nome'] ?? '';
}

function redirecionar($url) {
    header("Location: " . BASE_URL . $url);
    exit;
}

function resumo_texto($texto, $limite = 150) {
    $texto = strip_tags($texto);
    if (strlen($texto) <= $limite) {
        return $texto;
    }
    $resumo = substr($texto, 0, $limite);
    $ultimo_espaco = strrpos($resumo, ' ');
    return substr($resumo, 0, $ultimo_espaco) . '...';
}

function formatar_data($data) {
    $timestamp = strtotime($data);
    return date('d/m/Y \à\s H:i', $timestamp);
}

function set_mensagem($tipo, $texto) {
    $_SESSION['mensagem'] = [
        'tipo' => $tipo,
        'texto' => $texto
    ];
}

function get_mensagem() {
    if (isset($_SESSION['mensagem'])) {
        $mensagem = $_SESSION['mensagem'];
        unset($_SESSION['mensagem']);
        return $mensagem;
    }
    return null;
}

function escape($texto) {
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

// ===== NOVA FUNÇÃO: Busca todas as categorias =====
function get_categorias($pdo) {
    $stmt = $pdo->query("SELECT * FROM categorias ORDER BY nome ASC");
    return $stmt->fetchAll();
}