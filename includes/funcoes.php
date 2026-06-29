<?php
// =====================================================
// FUNÇÕES AUXILIARES - CORRIGIDO E PADRONIZADO
// =====================================================

// Inicia sessão apenas se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =====================================================
// DEFINIÇÃO DA BASE_URL - CORRIGIDA
// =====================================================
if (!defined('BASE_URL')) {
    // Detecta o protocolo (HTTP ou HTTPS)
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    
    // Obtém o caminho base do projeto
    $script_name = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    
    // Se estiver na raiz, usa '/'
    if ($script_name == '/' || $script_name == '\\') {
        $base_path = '/';
    } else {
        $base_path = rtrim($script_name, '/') . '/';
    }
    
    // Define a BASE_URL
    define('BASE_URL', $protocol . $host . $base_path);
}

// =====================================================
// FUNÇÕES DE USUÁRIO
// =====================================================

/**
 * Verifica se o usuário está logado
 */
function usuario_logado(): bool {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

/**
 * Retorna o ID do usuário logado
 */
function get_usuario_id(): ?int {
    return $_SESSION['usuario_id'] ?? null;
}

/**
 * Retorna o nome do usuário logado
 */
function get_usuario_nome(): string {
    return $_SESSION['usuario_nome'] ?? 'Usuário';
}

/**
 * Retorna a foto do usuário logado
 */
function get_usuario_foto(): ?string {
    return $_SESSION['usuario_foto'] ?? null;
}

/**
 * Retorna o email do usuário logado
 */
function get_usuario_email(): string {
    return $_SESSION['usuario_email'] ?? '';
}

// =====================================================
// FUNÇÕES DE REDIRECIONAMENTO
// =====================================================

/**
 * Redireciona para uma URL
 */
function redirecionar($url) {
    // Se a URL já começa com http ou https, é absoluta
    if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
        header("Location: " . $url);
        exit;
    }
    
    // Se começa com /, é caminho absoluto a partir da raiz do site
    if (strpos($url, '/') === 0) {
        header("Location: " . $url);
        exit;
    }
    
    // Caso contrário, é relativo ao BASE_URL
    header("Location: " . BASE_URL . $url);
    exit;
}

/**
 * Redireciona de volta para a página anterior
 */
function redirecionar_voltar() {
    if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
        header("Location: " . $_SERVER['HTTP_REFERER']);
    } else {
        redirecionar('/');
    }
    exit;
}

// =====================================================
// FUNÇÕES DE TEXTO E FORMATAÇÃO
// =====================================================

/**
 * Cria um resumo do texto com limite de caracteres
 */
function resumo_texto(string $texto, int $limite = 150): string {
    // Remove tags HTML
    $texto = strip_tags($texto);
    $texto = trim($texto);
    
    // Se o texto já é menor que o limite, retorna ele completo
    if (strlen($texto) <= $limite) {
        return $texto;
    }
    
    // Corta o texto no limite
    $resumo = substr($texto, 0, $limite);
    
    // Encontra o último espaço para não cortar palavras no meio
    $ultimo_espaco = strrpos($resumo, ' ');
    
    if ($ultimo_espaco !== false) {
        $resumo = substr($resumo, 0, $ultimo_espaco);
    }
    
    return $resumo . '...';
}

/**
 * Formata uma data para exibição
 */
function formatar_data(string $data, string $formato = 'd/m/Y H:i'): string {
    if (empty($data)) {
        return '';
    }
    
    try {
        $timestamp = strtotime($data);
        if ($timestamp === false || $timestamp === -1) {
            return $data;
        }
        return date($formato, $timestamp);
    } catch (Exception $e) {
        return $data;
    }
}

/**
 * Formata data para formato relativo (ex: "há 2 dias")
 */
function formatar_data_relativa(string $data): string {
    $timestamp = strtotime($data);
    $diferenca = time() - $timestamp;
    
    if ($diferenca < 60) {
        return 'agora mesmo';
    } elseif ($diferenca < 3600) {
        $minutos = floor($diferenca / 60);
        return "há {$minutos} minuto" . ($minutos > 1 ? 's' : '');
    } elseif ($diferenca < 86400) {
        $horas = floor($diferenca / 3600);
        return "há {$horas} hora" . ($horas > 1 ? 's' : '');
    } elseif ($diferenca < 604800) {
        $dias = floor($diferenca / 86400);
        return "há {$dias} dia" . ($dias > 1 ? 's' : '');
    } else {
        return formatar_data($data);
    }
}

/**
 * Escapa caracteres especiais HTML
 */
function escape(string $texto): string {
    return htmlspecialchars($texto ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// =====================================================
// FUNÇÕES DE MENSAGEM (FLASH MESSAGES)
// =====================================================

/**
 * Define uma mensagem para ser exibida na próxima requisição
 */
function set_mensagem(string $tipo, string $texto): void {
    $tipos_validos = ['sucesso', 'erro', 'info', 'alerta'];
    if (!in_array($tipo, $tipos_validos)) {
        $tipo = 'info';
    }
    
    $_SESSION['mensagem'] = [
        'tipo' => $tipo,
        'texto' => $texto
    ];
}

/**
 * Obtém e remove a mensagem da sessão
 */
function get_mensagem(): ?array {
    if (isset($_SESSION['mensagem'])) {
        $mensagem = $_SESSION['mensagem'];
        unset($_SESSION['mensagem']);
        return $mensagem;
    }
    return null;
}

/**
 * Exibe a mensagem (se existir)
 */
function exibir_mensagem(): void {
    $mensagem = get_mensagem();
    if ($mensagem) {
        $classes = [
            'sucesso' => 'bg-green-100 text-green-800 border-green-300',
            'erro' => 'bg-red-100 text-red-800 border-red-300',
            'info' => 'bg-blue-100 text-blue-800 border-blue-300',
            'alerta' => 'bg-yellow-100 text-yellow-800 border-yellow-300'
        ];
        
        $classe = $classes[$mensagem['tipo']] ?? $classes['info'];
        echo '<div class="p-4 mb-4 border rounded-lg ' . $classe . '">';
        echo escape($mensagem['texto']);
        echo '</div>';
    }
}

// =====================================================
// FUNÇÕES DE BANCO DE DADOS
// =====================================================

/**
 * Obtém todas as categorias do banco de dados
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
 * Obtém categorias com cache (melhor performance)
 */
function get_categorias_cache(?PDO $pdo, bool $forcar_atualizacao = false): array {
    static $cache = null;
    
    if ($cache !== null && !$forcar_atualizacao) {
        return $cache;
    }
    
    $cache = get_categorias($pdo);
    return $cache;
}

/**
 * Obtém uma notícia específica pelo ID
 */
function get_noticia(PDO $pdo, int $id): ?array {
    try {
        $stmt = $pdo->prepare("
            SELECT n.*, u.nome AS autor_nome, c.nome AS categoria_nome 
            FROM noticias n
            LEFT JOIN usuarios u ON n.autor = u.id
            LEFT JOIN categorias c ON n.categoria_id = c.id
            WHERE n.id = ?
        ");
        $stmt->execute([$id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    } catch (PDOException $e) {
        error_log("get_noticia: " . $e->getMessage());
        return null;
    }
}

/**
 * Obtém notícias de uma categoria específica
 */
function get_noticias_categoria(PDO $pdo, int $categoria_id, int $limite = 10): array {
    try {
        $stmt = $pdo->prepare("
            SELECT n.*, u.nome AS autor_nome 
            FROM noticias n
            INNER JOIN usuarios u ON n.autor = u.id
            WHERE n.categoria_id = ?
            ORDER BY n.data DESC
            LIMIT ?
        ");
        $stmt->execute([$categoria_id, $limite]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("get_noticias_categoria: " . $e->getMessage());
        return [];
    }
}

// =====================================================
// FUNÇÕES DE VALIDAÇÃO
// =====================================================

/**
 * Valida um email
 */
function validar_email(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida uma senha (mínimo de caracteres)
 */
function validar_senha(string $senha, int $min_length = 6): bool {
    return strlen($senha) >= $min_length;
}

/**
 * Valida se uma string não está vazia
 */
function validar_obrigatorio(string $valor): bool {
    return trim($valor) !== '';
}

/**
 * Sanitiza uma string para evitar XSS
 */
function sanitizar(string $texto): string {
    return strip_tags(trim($texto));
}

// =====================================================
// FUNÇÕES DE SEGURANÇA
// =====================================================

/**
 * Gera um token CSRF para proteção de formulários
 */
function gerar_token_csrf(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifica se o token CSRF é válido
 */
function verificar_token_csrf(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Gera um token de campo CSRF para formulários
 */
function csrf_field(): string {
    $token = gerar_token_csrf();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

// =====================================================
// FUNÇÕES DE UPLOAD DE ARQUIVOS
// =====================================================

/**
 * Faz upload de uma imagem
 */
function upload_imagem(array $file, string $destino, array $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp']): ?string {
    // Verifica se houve erro no upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    // Verifica o tamanho (máximo 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        return null;
    }
    
    // Verifica a extensão
    $extensao = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extensao, $extensoes_permitidas)) {
        return null;
    }
    
    // Gera nome único para o arquivo
    $nome_arquivo = uniqid() . '.' . $extensao;
    $caminho_completo = $destino . '/' . $nome_arquivo;
    
    // Move o arquivo
    if (move_uploaded_file($file['tmp_name'], $caminho_completo)) {
        return $nome_arquivo;
    }
    
    return null;
}

// =====================================================
// FUNÇÕES DE DEBUG (APENAS PARA DESENVOLVIMENTO)
// =====================================================

/**
 * Função de debug para desenvolvimento
 */
function debug_var($var, string $label = ''): void {
    if (defined('DEBUG') && DEBUG === true) {
        echo '<pre style="background:#f4f4f4;border:1px solid #ddd;padding:10px;margin:10px 0;border-radius:5px;overflow:auto;">';
        if ($label) {
            echo '<strong style="color:#333;">' . escape($label) . ':</strong><br>';
        }
        var_dump($var);
        echo '</pre>';
    }
}

/**
 * Função para log rápido
 */
function log_msg(string $mensagem, string $nivel = 'INFO'): void {
    $log = date('Y-m-d H:i:s') . " [$nivel] " . $mensagem . PHP_EOL;
    error_log($log);
}

// =====================================================
// FUNÇÕES DE NAVEGAÇÃO E MENU
// =====================================================

/**
 * Verifica se a página atual é a página ativa para o menu
 */
function is_active(string $pagina): string {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    return strpos($uri, $pagina) !== false ? 'active' : '';
}

// =====================================================
// FUNÇÃO DE VERIFICAÇÃO DE PERMISSÃO (AJUSTE)
// =====================================================

/**
 * Verifica se o usuário tem permissão para editar/excluir notícia
 */
function tem_permissao_noticia(PDO $pdo, int $noticia_id, int $usuario_id): bool {
    try {
        $stmt = $pdo->prepare("SELECT autor FROM noticias WHERE id = ?");
        $stmt->execute([$noticia_id]);
        $noticia = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$noticia) {
            return false;
        }
        
        return $noticia['autor'] == $usuario_id;
    } catch (PDOException $e) {
        error_log("tem_permissao_noticia: " . $e->getMessage());
        return false;
    }
}
?>