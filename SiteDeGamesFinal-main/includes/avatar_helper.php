<?php
// =====================================================
// HELPER: Avatar do usuário
// Arquivo: includes/avatar_helper.php
// =====================================================

/**
 * Retorna a URL da foto do usuário ou null se não tiver.
 */
function get_avatar_url(?array $usuario): ?string {
    if (!empty($usuario['foto'])) {
        return BASE_URL . 'uploads/' . $usuario['foto'];
    }
    return null;
}

/**
 * Retorna as iniciais do nome para o avatar fallback.
 */
function get_avatar_initials(string $nome): string {
    $palavras = explode(' ', trim($nome));
    $iniciais = strtoupper(substr($palavras[0], 0, 1));
    if (count($palavras) > 1) {
        $iniciais .= strtoupper(substr(end($palavras), 0, 1));
    }
    return $iniciais;
}

/**
 * Renderiza o avatar como HTML.
 *
 * @param array|null $usuario  Linha do banco com 'foto' e 'nome'
 * @param int        $size     Tamanho em px (ex: 32, 40, 96)
 * @param string     $class    Classes CSS extras
 */
function render_avatar(?array $usuario, int $size = 32, string $class = ''): string {
    $nome     = $usuario['nome'] ?? 'U';
    $url      = get_avatar_url($usuario);
    $initials = get_avatar_initials($nome);
    $px       = $size . 'px';

    $base_style = "width:{$px}; height:{$px}; border-radius:50%; object-fit:cover; flex-shrink:0;";

    if ($url) {
        return sprintf(
            '<img src="%s" alt="%s" title="%s" style="%s border:2px solid var(--border);" class="%s">',
            htmlspecialchars($url),
            htmlspecialchars($nome),
            htmlspecialchars($nome),
            $base_style,
            $class
        );
    }

    // Fallback com iniciais
    $font = max(10, intval($size * 0.38));
    return sprintf(
        '<div title="%s" style="%s background:var(--accent-light); color:var(--accent-text);
              font-size:%dpx; font-weight:700; display:flex; align-items:center;
              justify-content:center; border:2px solid var(--border); flex-shrink:0;" class="%s">%s</div>',
        htmlspecialchars($nome),
        $base_style,
        $font,
        $class,
        $initials
    );
}