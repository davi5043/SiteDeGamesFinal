<?php
// =====================================================
// FUNÇÕES AUXILIARES
// =====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DEFINE A BASE_URL
if (!defined('BASE_URL')) {
    // Pega o caminho do projeto
    $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    if ($script_dir != '/') {
        $script_dir = rtrim($script_dir, '/') . '/';
    }
    define('BASE_URL', $script_dir);
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

function get_usuario_foto() {
    return $_SESSION['usuario_foto'] ?? null;
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

function get_categorias($pdo) {
    if ($pdo === null) {
        return [];
    }
    try {
        $stmt = $pdo->query("SELECT * FROM categorias ORDER BY nome ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}