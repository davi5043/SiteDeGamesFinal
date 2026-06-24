<?php
// =====================================================
// FUNÇÕES AUXILIARES
// Arquivo: includes/funcoes.php
// Descrição: Funções reutilizáveis em todo o sistema
// =====================================================

// Inicia a sessão PHP se ainda não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define a constante BASE_URL para facilitar links entre pastas
// Detecta automaticamente o caminho base do projeto no servidor
if (!defined('BASE_URL')) {
    // Descobre o caminho do projeto baseado em onde está este arquivo (includes/)
    // __DIR__ = caminho absoluto do diretório deste arquivo
    // O projeto está um nível acima de /includes/
    $doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $project_root = str_replace('\\', '/', dirname(__DIR__));

    // Calcula o caminho relativo do projeto a partir da raiz do servidor
    $base = str_replace($doc_root, '', $project_root);
    $base = rtrim($base, '/') . '/';

    define('BASE_URL', $base);
}

/**
 * Verifica se o usuário está logado
 * Retorna true se houver uma sessão de usuário ativa
 */
function usuario_logado() {
    return isset($_SESSION['usuario_id']);
}

/**
 * Retorna o ID do usuário logado
 * Retorna null se não estiver logado
 */
function get_usuario_id() {
    return $_SESSION['usuario_id'] ?? null;
}

/**
 * Retorna o nome do usuário logado
 */
function get_usuario_nome() {
    return $_SESSION['usuario_nome'] ?? '';
}

/**
 * Redireciona para outra página usando a BASE_URL
 * Usa header Location para fazer o redirecionamento HTTP
 */
function redirecionar($url) {
    header("Location: " . BASE_URL . $url);
    exit; // Importante: encerra o script após redirecionar
}

/**
 * Cria um resumo do texto (para exibir nos cards da página inicial)
 * Corta o texto no número de caracteres especificado sem quebrar palavras
 */
function resumo_texto($texto, $limite = 150) {
    $texto = strip_tags($texto);
    if (strlen($texto) <= $limite) {
        return $texto;
    }
    $resumo = substr($texto, 0, $limite);
    $ultimo_espaco = strrpos($resumo, ' ');
    return substr($resumo, 0, $ultimo_espaco) . '...';
}

/**
 * Formata uma data do banco para formato brasileiro
 * Exemplo: 2026-06-20 10:00:00 -> 20/06/2026 às 10:00
 */
function formatar_data($data) {
    $timestamp = strtotime($data);
    return date('d/m/Y \à\s H:i', $timestamp);
}

/**
 * Define uma mensagem flash (exibida uma única vez)
 * Útil para mostrar mensagens de sucesso/erro após redirecionamentos
 */
function set_mensagem($tipo, $texto) {
    $_SESSION['mensagem'] = [
        'tipo' => $tipo,
        'texto' => $texto
    ];
}

/**
 * Recupera e remove a mensagem flash da sessão
 * Retorna null se não houver mensagem
 */
function get_mensagem() {
    if (isset($_SESSION['mensagem'])) {
        $mensagem = $_SESSION['mensagem'];
        unset($_SESSION['mensagem']);
        return $mensagem;
    }
    return null;
}

/**
 * Escapa texto para exibição segura em HTML
 * Previne ataques XSS (Cross-Site Scripting)
 */
function escape($texto) {
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}
