<?php
// =====================================================
// FUNÇÕES AUXILIARES
// =====================================================

// Inicia sessão apenas se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DEFINE A BASE_URL - VERSÃO CORRIGIDA
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $script_name = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $script_name = rtrim($script_name, '/') . '/';
    define('BASE_URL', $protocol . $host . $script_name);
}

// =====================================================
// FUNÇÕES DE USUÁRIO
// =====================================================

function usuario_logado(): bool {
    return isset($_SESSION['usuario_id']);
}

function get_usuario_id(): ?int {
    return $_SESSION['usuario_id'] ?? null;
}

function get_usuario_nome(): string {
    return $_SESSION['usuario_nome'] ?? '';
}

function get_usuario_foto(): ?string {
    return $_SESSION['usuario_foto'] ?? null;
}

// =====================================================
// FUNÇÕES DE REDIRECIONAMENTO
// =====================================================

function redirecionar($url) {
    // Verifica se a URL já tem o BASE_URL ou é absoluta
    if (strpos($url, 'http') !== 0 && strpos($url, '/') !== 0) {
        // Se for caminho relativo, adiciona BASE_URL
        $url = BASE_URL . $url;
    }
    header("Location: " . $url);
    exit;
}

// =====================================================
// FUNÇÕES DE TEXTO
// =====================================================

function resumo_texto(string $texto, int $limite = 150): string {
    $texto = strip_tags($texto);
    $texto = trim($texto);
    
    if (strlen($texto) <= $limite) {
        return $texto;
    }
    
    $resumo = substr($texto, 0, $limite);
    $ultimo_espaco = strrpos($resumo, ' ');
    
    if ($ultimo_espaco !== false) {
        $resumo = substr($resumo, 0, $ultimo_espaco);
    }
    
    return $resumo . '...';
}

function formatar_data(string $data, string $formato = 'd/m/Y \à\s H:i'): string {
    try {
        $timestamp = strtotime($data);
        if ($timestamp === false) {
            return $data;
        }
        return date($formato, $timestamp);
    } catch (Exception $e) {
        return $data;
    }
}

function escape(string $texto): string {
    return htmlspecialchars($texto, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// =====================================================
// FUNÇÕES DE MENSAGEM
// =====================================================

function set_mensagem(string $tipo, string $texto): void {
    $_SESSION['mensagem'] = [
        'tipo' => $tipo,
        'texto' => $texto
    ];
}

function get_mensagem(): ?array {
    if (isset($_SESSION['mensagem'])) {
        $mensagem = $_SESSION['mensagem'];
        unset($_SESSION['mensagem']);
        return $mensagem;
    }
    return null;
}

// =====================================================
// FUNÇÕES DE BANCO DE DADOS
// =====================================================

/**
 * Obtém todas as categorias do banco de dados
 * 
 * @param PDO|null $pdo Conexão PDO
 * @param bool $log_erros Se deve logar erros
 * @return array Lista de categorias ou array vazio
 */
function get_categorias(?PDO $pdo, bool $log_erros = false): array {
    // Verifica se o PDO é válido
    if ($pdo === null) {
        if ($log_erros) {
            error_log("get_categorias: PDO connection is null");
        }
        return [];
    }
    
    try {
        // Testa se a conexão está ativa
        $pdo->query('SELECT 1');
        
        $stmt = $pdo->query("SELECT * FROM categorias ORDER BY nome ASC");
        if ($stmt === false) {
            throw new PDOException("Query failed");
        }
        
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resultado !== false ? $resultado : [];
        
    } catch (PDOException $e) {
        if ($log_erros) {
            error_log("get_categorias: " . $e->getMessage());
        }
        return [];
    }
}

/**
 * Obtém categorias com cache (versão melhorada)
 */
function get_categorias_cache(?PDO $pdo, bool $forcar_atualizacao = false): array {
    static $cache = null;
    
    if ($cache !== null && !$forcar_atualizacao) {
        return $cache;
    }
    
    $cache = get_categorias($pdo);
    return $cache;
}

// =====================================================
// FUNÇÕES DE VALIDAÇÃO
// =====================================================

function validar_email(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validar_senha(string $senha, int $min_length = 6): bool {
    return strlen($senha) >= $min_length;
}

// =====================================================
// FUNÇÕES DE SEGURANÇA
// =====================================================

function gerar_token_csrf(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verificar_token_csrf(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// =====================================================
// FUNÇÃO DE DEBUG (APENAS PARA DESENVOLVIMENTO)
// =====================================================

function debug_var($var, string $label = ''): void {
    if (defined('DEBUG') && DEBUG === true) {
        echo '<pre style="background:#f4f4f4;border:1px solid #ddd;padding:10px;margin:10px 0;">';
        if ($label) {
            echo '<strong>' . escape($label) . ':</strong><br>';
        }
        var_dump($var);
        echo '</pre>';
    }
}