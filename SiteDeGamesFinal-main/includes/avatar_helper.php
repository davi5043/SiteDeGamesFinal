<?php
// =====================================================
// HELPER: Avatar do usuário - CORRIGIDO E PADRONIZADO
// =====================================================

// Inclui funções auxiliares para usar escape e outras funções
require_once __DIR__ . '/funcoes.php';

// =====================================================
// CONSTANTES DE CONFIGURAÇÃO
// =====================================================

// Diretório onde as fotos são armazenadas
if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', 'uploads/');
}

// Tamanho máximo do avatar em pixels
if (!defined('AVATAR_MAX_SIZE')) {
    define('AVATAR_MAX_SIZE', 200);
}

// Cores para avatares fallback
$AVATAR_COLORS = [
    '#7c3aed', // Roxo
    '#ef4444', // Vermelho
    '#3b82f6', // Azul
    '#10b981', // Verde
    '#f59e0b', // Amarelo
    '#ec4899', // Rosa
    '#8b5cf6', // Roxo claro
    '#14b8a6', // Teal
    '#f97316', // Laranja
    '#6366f1', // Índigo
];

// =====================================================
// FUNÇÕES PRINCIPAIS
// =====================================================

/**
 * Retorna a URL da foto do usuário ou null se não tiver.
 * 
 * @param array|null $usuario Dados do usuário (deve ter 'foto')
 * @param bool $absolute Se deve retornar URL absoluta ou relativa
 * @return string|null URL da foto ou null
 */
function get_avatar_url(?array $usuario, bool $absolute = true): ?string {
    if (empty($usuario) || empty($usuario['foto'])) {
        return null;
    }
    
    // Remove caracteres perigosos do nome do arquivo
    $foto = basename($usuario['foto']);
    
    if ($absolute) {
        return BASE_URL . UPLOAD_DIR . $foto;
    } else {
        return UPLOAD_DIR . $foto;
    }
}

/**
 * Retorna as iniciais do nome para o avatar fallback.
 * 
 * @param string $nome Nome completo do usuário
 * @param int $limite Número máximo de iniciais (1 ou 2)
 * @return string Iniciais do nome
 */
function get_avatar_initials(string $nome, int $limite = 2): string {
    $nome = trim($nome);
    
    if (empty($nome)) {
        return '?';
    }
    
    // Remove múltiplos espaços
    $nome = preg_replace('/\s+/', ' ', $nome);
    
    $palavras = explode(' ', $nome);
    $iniciais = '';
    
    // Pega primeira letra da primeira palavra
    if (!empty($palavras[0])) {
        $iniciais .= mb_strtoupper(mb_substr($palavras[0], 0, 1));
    }
    
    // Se tiver mais de uma palavra e limite for 2, pega última palavra
    if ($limite >= 2 && count($palavras) > 1) {
        $ultima = end($palavras);
        if (!empty($ultima) && $ultima !== $palavras[0]) {
            $iniciais .= mb_strtoupper(mb_substr($ultima, 0, 1));
        }
    }
    
    return $iniciais ?: '?';
}

/**
 * Gera uma cor de fundo para o avatar baseado no nome
 * 
 * @param string $nome Nome do usuário
 * @return string Cor em formato hexadecimal
 */
function get_avatar_color(string $nome): string {
    global $AVATAR_COLORS;
    
    if (empty($nome)) {
        return $AVATAR_COLORS[0] ?? '#7c3aed';
    }
    
    // Gera um hash baseado no nome
    $hash = crc32($nome);
    $index = abs($hash) % count($AVATAR_COLORS);
    
    return $AVATAR_COLORS[$index];
}

/**
 * Verifica se o usuário tem uma foto de avatar
 * 
 * @param array|null $usuario Dados do usuário
 * @return bool
 */
function usuario_tem_avatar(?array $usuario): bool {
    if (empty($usuario) || empty($usuario['foto'])) {
        return false;
    }
    
    // Verifica se o arquivo realmente existe no servidor
    $caminho = __DIR__ . '/../' . UPLOAD_DIR . basename($usuario['foto']);
    return file_exists($caminho);
}

/**
 * Renderiza o avatar como HTML.
 *
 * @param array|null $usuario Linha do banco com 'foto' e 'nome'
 * @param int $size Tamanho em px (ex: 32, 40, 96)
 * @param string $class Classes CSS extras
 * @param array $opts Opções adicionais
 * @return string HTML do avatar
 */
function render_avatar(
    ?array $usuario, 
    int $size = 32, 
    string $class = '',
    array $opts = []
): string {
    $nome = $usuario['nome'] ?? 'Usuário';
    $url = get_avatar_url($usuario);
    $initials = get_avatar_initials($nome);
    $color = get_avatar_color($nome);
    $px = $size . 'px';
    
    // Opções padrão
    $defaults = [
        'border' => true,
        'border_color' => 'var(--border)',
        'link' => null,
        'link_class' => '',
        'tooltip' => true,
        'rounded' => true,
        'shadow' => false,
    ];
    $opts = array_merge($defaults, $opts);
    
    // Classes adicionais
    $extra_class = $class;
    if ($opts['rounded']) {
        $extra_class .= ' avatar-rounded';
    }
    if ($opts['shadow']) {
        $extra_class .= ' avatar-shadow';
    }
    
    // Estilos base
    $base_style = "width:{$px}; height:{$px}; flex-shrink:0;";
    $base_style .= $opts['rounded'] ? " border-radius:50%;" : " border-radius:8px;";
    $base_style .= $opts['border'] ? " border:2px solid {$opts['border_color']};" : "";
    $base_style .= $opts['shadow'] ? " box-shadow: 0 4px 6px rgba(0,0,0,0.1);" : "";
    $base_style .= " object-fit:cover;";
    
    // Atributos de tooltip
    $title_attr = $opts['tooltip'] ? 'title="' . escape($nome) . '"' : '';
    
    // Conteúdo do avatar
    if ($url && usuario_tem_avatar($usuario)) {
        $avatar_html = sprintf(
            '<img src="%s" alt="%s" %s style="%s" class="%s" loading="lazy">',
            escape($url),
            escape($nome),
            $title_attr,
            $base_style,
            trim($extra_class)
        );
    } else {
        // Fallback com iniciais
        $font_size = max(10, intval($size * 0.38));
        $text_color = is_color_dark($color) ? '#ffffff' : '#1a1a1a';
        
        $avatar_html = sprintf(
            '<div %s style="%s background:%s; color:%s; font-size:%dpx; font-weight:700; display:flex; align-items:center; justify-content:center;" class="%s">%s</div>',
            $title_attr,
            $base_style,
            $color,
            $text_color,
            $font_size,
            trim($extra_class),
            escape($initials)
        );
    }
    
    // Se tiver link, envolve com <a>
    if ($opts['link']) {
        $link_class = $opts['link_class'] ? ' class="' . escape($opts['link_class']) . '"' : '';
        return sprintf(
            '<a href="%s"%s>%s</a>',
            escape($opts['link']),
            $link_class,
            $avatar_html
        );
    }
    
    return $avatar_html;
}

/**
 * Verifica se uma cor é escura (para escolher texto branco ou preto)
 * 
 * @param string $hex Cor em hexadecimal
 * @return bool True se a cor é escura
 */
function is_color_dark(string $hex): bool {
    $hex = ltrim($hex, '#');
    
    // Converte para RGB
    if (strlen($hex) == 3) {
        $r = hexdec($hex[0] . $hex[0]);
        $g = hexdec($hex[1] . $hex[1]);
        $b = hexdec($hex[2] . $hex[2]);
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    
    // Fórmula de luminosidade
    $luminosity = (0.299 * $r + 0.587 * $g + 0.114 * $b);
    
    return $luminosity < 128;
}

// =====================================================
// FUNÇÕES DE UPLOAD DE AVATAR
// =====================================================

/**
 * Faz upload da foto de avatar do usuário
 * 
 * @param array $file Arquivo $_FILES['avatar']
 * @param int $usuario_id ID do usuário
 * @param PDO $pdo Conexão com o banco
 * @return array Resultado com status e mensagem
 */
function upload_avatar($file, $usuario_id, $pdo) {
    $resultado = [
        'sucesso' => false,
        'mensagem' => '',
        'arquivo' => null
    ];
    
    // Verifica se houve erro no upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $resultado['mensagem'] = 'Erro no upload do arquivo.';
        return $resultado;
    }
    
    // Verifica o tamanho (máximo 2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        $resultado['mensagem'] = 'A imagem deve ter no máximo 2MB.';
        return $resultado;
    }
    
    // Verifica o tipo de arquivo
    $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $tipos_permitidos)) {
        $resultado['mensagem'] = 'Formato de imagem não permitido. Use JPG, PNG, GIF ou WEBP.';
        return $resultado;
    }
    
    // Gera nome único para o arquivo
    $extensao = pathinfo($file['name'], PATHINFO_EXTENSION);
    $nome_arquivo = 'user_' . $usuario_id . '_' . uniqid() . '.' . $extensao;
    
    // Cria o diretório se não existir
    $upload_dir = __DIR__ . '/../' . UPLOAD_DIR;
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Move o arquivo
    $caminho_completo = $upload_dir . $nome_arquivo;
    if (move_uploaded_file($file['tmp_name'], $caminho_completo)) {
        // Remove avatar antigo se existir
        $stmt = $pdo->prepare("SELECT foto FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario_id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario && !empty($usuario['foto'])) {
            $avatar_antigo = $upload_dir . basename($usuario['foto']);
            if (file_exists($avatar_antigo) && is_file($avatar_antigo)) {
                unlink($avatar_antigo);
            }
        }
        
        // Atualiza o banco de dados
        $stmt = $pdo->prepare("UPDATE usuarios SET foto = ? WHERE id = ?");
        if ($stmt->execute([$nome_arquivo, $usuario_id])) {
            $resultado['sucesso'] = true;
            $resultado['mensagem'] = 'Avatar atualizado com sucesso!';
            $resultado['arquivo'] = $nome_arquivo;
            
            // Atualiza a sessão
            $_SESSION['usuario_foto'] = $nome_arquivo;
        } else {
            // Remove o arquivo se falhou no banco
            unlink($caminho_completo);
            $resultado['mensagem'] = 'Erro ao salvar no banco de dados.';
        }
    } else {
        $resultado['mensagem'] = 'Erro ao mover o arquivo.';
    }
    
    return $resultado;
}

/**
 * Remove o avatar de um usuário
 * 
 * @param int $usuario_id ID do usuário
 * @param PDO $pdo Conexão com o banco
 * @return array Resultado com status e mensagem
 */
function remover_avatar($usuario_id, $pdo) {
    $resultado = [
        'sucesso' => false,
        'mensagem' => ''
    ];
    
    try {
        // Busca o nome do arquivo
        $stmt = $pdo->prepare("SELECT foto FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario_id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario && !empty($usuario['foto'])) {
            // Remove o arquivo
            $upload_dir = __DIR__ . '/../' . UPLOAD_DIR;
            $caminho = $upload_dir . basename($usuario['foto']);
            if (file_exists($caminho) && is_file($caminho)) {
                unlink($caminho);
            }
            
            // Atualiza o banco
            $stmt = $pdo->prepare("UPDATE usuarios SET foto = NULL WHERE id = ?");
            if ($stmt->execute([$usuario_id])) {
                $_SESSION['usuario_foto'] = null;
                $resultado['sucesso'] = true;
                $resultado['mensagem'] = 'Avatar removido com sucesso!';
            }
        } else {
            $resultado['mensagem'] = 'Usuário não tem avatar.';
        }
    } catch (PDOException $e) {
        error_log("remover_avatar: " . $e->getMessage());
        $resultado['mensagem'] = 'Erro ao remover avatar.';
    }
    
    return $resultado;
}

// =====================================================
// FUNÇÕES DE EXIBIÇÃO (HELPERS PARA VIEWS)
// =====================================================

/**
 * Exibe um avatar com tamanhos responsivos
 * 
 * @param array|null $usuario Dados do usuário
 * @param string $size Tamanho (sm, md, lg, xl)
 * @param string $class Classes extras
 * @return string HTML do avatar
 */
function render_avatar_responsive(?array $usuario, string $size = 'md', string $class = ''): string {
    $sizes = [
        'xs' => 24,
        'sm' => 32,
        'md' => 48,
        'lg' => 64,
        'xl' => 96,
        'xxl' => 128
    ];
    
    $px = $sizes[$size] ?? 48;
    return render_avatar($usuario, $px, $class);
}

/**
 * Exibe um avatar circular com badge de status (online/offline)
 * 
 * @param array|null $usuario Dados do usuário
 * @param bool $online Se o usuário está online
 * @param int $size Tamanho do avatar
 * @return string HTML do avatar com badge
 */
function render_avatar_com_status(?array $usuario, bool $online = false, int $size = 40): string {
    $avatar = render_avatar($usuario, $size);
    $badge_size = max(8, $size * 0.25);
    $badge_color = $online ? '#10b981' : '#ef4444';
    
    return sprintf(
        '<div style="position:relative;display:inline-block;">
            %s
            <span style="position:absolute;bottom:0;right:0;width:%dpx;height:%dpx;border-radius:50%;background:%s;border:2px solid var(--bg-body);"></span>
        </div>',
        $avatar,
        $badge_size,
        $badge_size,
        $badge_color
    );
}
?>