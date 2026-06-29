<?php
// =====================================================
// PÁGINA INDIVIDUAL DA NOTÍCIA
// Arquivo: pages/noticias/noticia.php
// =====================================================

require_once __DIR__ . '/../../includes/conexao.php';
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/comentarios.php';
require_once __DIR__ . '/../../includes/avatar_helper.php';

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: ../../index.php");
    exit;
}

// ══════════════════════════════════════════════════════
// PROCESSAR LIKE (responde JSON — sem reload)
// ══════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'like') {
    header('Content-Type: application/json');

    if (!usuario_logado()) {
        echo json_encode(['erro' => 'Login necessário']);
        exit;
    }

    $uid = get_usuario_id();

    // Verifica se já curtiu
    $chk = $pdo->prepare("SELECT id FROM likes WHERE noticia_id = ? AND usuario_id = ?");
    $chk->execute([$id, $uid]);
    $ja_curtiu = $chk->fetch();

    if ($ja_curtiu) {
        // Remove o like
        $pdo->prepare("DELETE FROM likes WHERE noticia_id = ? AND usuario_id = ?")->execute([$id, $uid]);
        $liked = false;
    } else {
        // Adiciona o like
        $pdo->prepare("INSERT IGNORE INTO likes (noticia_id, usuario_id) VALUES (?, ?)")->execute([$id, $uid]);
        $liked = true;
    }

    // Conta total atualizado
    $cnt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE noticia_id = ?");
    $cnt->execute([$id]);
    $total = (int) $cnt->fetchColumn();

    echo json_encode(['liked' => $liked, 'total' => $total]);
    exit;
}

// ══════════════════════════════════════════════════════
// BUSCA NOTÍCIA
// ══════════════════════════════════════════════════════
$stmt = $pdo->prepare("
    SELECT n.*, u.nome AS autor_nome, u.foto AS autor_foto,
           c.nome AS categoria_nome, c.icone AS categoria_icone
    FROM noticias n
    INNER JOIN usuarios u ON n.autor = u.id
    LEFT JOIN categorias c ON n.categoria_id = c.id
    WHERE n.id = ?
");
$stmt->execute([$id]);
$noticia = $stmt->fetch();

if (!$noticia) {
    set_mensagem('erro', 'Notícia não encontrada.');
    header("Location: ../../index.php");
    exit;
}

$categorias = get_categorias($pdo);
$comentarios = get_comentarios($pdo, $id);
$total_comentarios = contar_comentarios($pdo, $id);

// ── Dados de like ──────────────────────────────────────
$stmt_likes = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE noticia_id = ?");
$stmt_likes->execute([$id]);
$total_likes = (int) $stmt_likes->fetchColumn();

$usuario_ja_curtiu = false;
if (usuario_logado()) {
    $stmt_meu = $pdo->prepare("SELECT id FROM likes WHERE noticia_id = ? AND usuario_id = ?");
    $stmt_meu->execute([$id, get_usuario_id()]);
    $usuario_ja_curtiu = (bool) $stmt_meu->fetch();
}

$usuario_logado = null;
if (usuario_logado()) {
    $usuario_logado = [
        'nome' => get_usuario_nome(),
        'foto' => get_usuario_foto()
    ];
}

// ══════════════════════════════════════════════════════
// PROCESSAR COMENTÁRIO
// ══════════════════════════════════════════════════════
$erro_comentario = '';
$sucesso_comentario = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'comentar') {
    if (!usuario_logado()) {
        $erro_comentario = 'Você precisa estar logado para comentar.';
    } else {
        $conteudo = trim($_POST['conteudo'] ?? '');
        if (empty($conteudo)) {
            $erro_comentario = 'O comentário não pode estar vazio.';
        } elseif (strlen($conteudo) < 3) {
            $erro_comentario = 'O comentário deve ter no mínimo 3 caracteres.';
        } elseif (strlen($conteudo) > 1000) {
            $erro_comentario = 'O comentário deve ter no máximo 1000 caracteres.';
        } else {
            if (adicionar_comentario($pdo, $id, get_usuario_id(), $conteudo)) {
                $sucesso_comentario = 'Comentário adicionado com sucesso!';
                $comentarios = get_comentarios($pdo, $id);
                $total_comentarios = contar_comentarios($pdo, $id);
            } else {
                $erro_comentario = 'Erro ao adicionar comentário. Tente novamente.';
            }
        }
    }
}

// ── Excluir comentário ──────────────────────────────────
if (isset($_GET['excluir_comentario']) && usuario_logado()) {
    $comentario_id = intval($_GET['excluir_comentario']);

    $stmt = $pdo->prepare("SELECT id FROM comentarios WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$comentario_id, get_usuario_id()]);
    if ($stmt->fetch()) {
        $pdo->prepare("DELETE FROM comentarios WHERE id = ? AND usuario_id = ?")->execute([$comentario_id, get_usuario_id()]);
        set_mensagem('sucesso', 'Comentário excluído com sucesso.');
    } else {
        set_mensagem('erro', 'Você não tem permissão para excluir este comentário.');
    }

    header("Location: noticia.php?id=" . $id);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape($noticia['titulo']) ?> - GGNews</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../css/style.css">

    <style>
        /* =====================================================
           ESTILOS DA PÁGINA DE NOTÍCIA
           ===================================================== */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8f4f0;
            color: #1a1a1a;
            min-height: 100vh;
            display: flex;
        }

        [data-theme="dark"] body {
            background: #0c0c10;
            color: #eeeaf8;
        }

        /* ── SIDEBAR ─────────────────────────────────────────────── */
        .sidebar {
            width: 260px;
            background: #ffffff;
            border-right: 1px solid #e8e2da;
            padding: 1.25rem;
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            overflow-y: auto;
            z-index: 50;
            display: none;
            flex-direction: column;
        }

        [data-theme="dark"] .sidebar {
            background: #101015;
            border-color: #252535;
        }

        @media (min-width: 768px) {
            .sidebar {
                display: flex !important;
            }
        }

        .sidebar.mobile-open {
            display: flex !important;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 49;
            backdrop-filter: blur(2px);
        }

        .sidebar-overlay.active {
            display: block;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            margin-bottom: 1.75rem;
        }

        .logo-icon {
            width: 38px;
            height: 38px;
            background: #ede9fe;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        [data-theme="dark"] .logo-icon {
            background: #1c1831;
        }

        .logo-text {
            font-family: 'Syne', sans-serif;
            font-size: 1.15rem;
            font-weight: 800;
            color: #1a1a1a;
            line-height: 1.1;
        }

        [data-theme="dark"] .logo-text {
            color: #eeeaf8;
        }

        .logo-text span {
            color: #7c3aed;
        }

        .logo-tag {
            font-size: 0.68rem;
            font-weight: 500;
            color: #9ca3af;
            letter-spacing: 0.02em;
        }

        [data-theme="dark"] .logo-tag {
            color: #5e5c76;
        }

        .sidebar-section-label {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #9ca3af;
            padding: 0.75rem 0.75rem 0.35rem;
        }

        [data-theme="dark"] .sidebar-section-label {
            color: #5e5c76;
        }

        .sidebar-nav,
        .sidebar-categories {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.55rem 0.75rem;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #5f6378;
            text-decoration: none;
            position: relative;
            transition: all 0.18s ease;
        }

        [data-theme="dark"] .nav-link {
            color: #918fac;
        }

        .nav-link:hover {
            background: #f8f4f0;
            color: #1a1a1a;
        }

        [data-theme="dark"] .nav-link:hover {
            background: #1a1a22;
            color: #eeeaf8;
        }

        .nav-link.active {
            background: #ede9fe;
            color: #7c3aed;
            font-weight: 600;
        }

        [data-theme="dark"] .nav-link.active {
            background: #1c1831;
            color: #a78bfa;
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 18%;
            height: 64%;
            width: 3px;
            background: #7c3aed;
            border-radius: 0 3px 3px 0;
        }

        .nav-icon {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            background: #f8f4f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        [data-theme="dark"] .nav-icon {
            background: #1a1a22;
        }

        .cat-icon {
            width: 22px;
            text-align: center;
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        .nav-link-danger {
            color: #ef4444 !important;
        }

        .nav-link-danger:hover {
            background: #fef2f2 !important;
            color: #dc2626 !important;
        }

        [data-theme="dark"] .nav-link-danger:hover {
            background: #2d0a0a !important;
        }

        .theme-toggle-wrap {
            padding-top: 0.75rem;
            border-top: 1px solid #e8e2da;
            margin-top: auto;
        }

        [data-theme="dark"] .theme-toggle-wrap {
            border-color: #252535;
        }

        .theme-toggle-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            width: 100%;
            padding: 0.55rem 0.75rem;
            border-radius: 0.75rem;
            background: transparent;
            border: none;
            cursor: pointer;
            transition: background 0.18s ease;
            text-align: left;
        }

        .theme-toggle-btn:hover {
            background: #f8f4f0;
        }

        [data-theme="dark"] .theme-toggle-btn:hover {
            background: #1a1a22;
        }

        .toggle-track {
            position: relative;
            width: 38px;
            height: 22px;
            background: #e8e2da;
            border-radius: 99px;
            flex-shrink: 0;
            transition: background 0.25s ease;
        }

        [data-theme="dark"] .toggle-track {
            background: #7c3aed;
        }

        .toggle-thumb {
            position: absolute;
            top: 3px;
            left: 3px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 1px 4px rgba(0,0,0,0.2);
            transition: transform 0.25s ease;
        }

        [data-theme="dark"] .toggle-thumb {
            transform: translateX(16px);
        }

        .toggle-icons {
            font-size: 1rem;
            line-height: 1;
        }

        .toggle-label {
            font-size: 0.82rem;
            font-weight: 500;
            color: #5f6378;
        }

        [data-theme="dark"] .toggle-label {
            color: #918fac;
        }

        .sidebar-footer-tag {
            font-size: 0.7rem;
            color: #9ca3af;
            text-align: center;
            margin: 0.5rem 0 0;
        }

        [data-theme="dark"] .sidebar-footer-tag {
            color: #5e5c76;
        }

        /* ── MAIN CONTENT ────────────────────────────────────────── */
        .main-content {
            flex: 1;
            margin-left: 0;
            min-width: 0;
        }

        @media (min-width: 768px) {
            .main-content {
                margin-left: 260px;
            }
        }

        /* ── HEADER ────────────────────────────────────────────────── */
        .site-header {
            background: #ffffff;
            border-bottom: 1px solid #e8e2da;
            padding: 0.75rem 1rem;
            position: sticky;
            top: 0;
            z-index: 30;
        }

        [data-theme="dark"] .site-header {
            background: #121218;
            border-color: #252535;
        }

        .header-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .menu-toggle {
            font-size: 1.5rem;
            background: transparent;
            border: none;
            color: #1a1a1a;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 0.5rem;
            display: block;
        }

        [data-theme="dark"] .menu-toggle {
            color: #eeeaf8;
        }

        @media (min-width: 768px) {
            .menu-toggle {
                display: none;
            }
        }

        .header-logo-mobile {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.1rem;
            font-weight: 700;
            color: #1a1a1a;
            text-decoration: none;
        }

        [data-theme="dark"] .header-logo-mobile {
            color: #eeeaf8;
        }

        .header-logo-mobile span {
            color: #7c3aed;
        }

        @media (min-width: 768px) {
            .header-logo-mobile {
                display: none;
            }
        }

        .header-nav {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .header-nav .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e8e2da;
        }

        [data-theme="dark"] .header-nav .avatar {
            border-color: #252535;
        }

        .header-nav .avatar-fallback {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ede9fe;
            color: #5b21b6;
            font-weight: 700;
            font-size: 0.8rem;
            border: 2px solid #e8e2da;
        }

        [data-theme="dark"] .header-nav .avatar-fallback {
            background: #1c1831;
            color: #c4b5fd;
            border-color: #252535;
        }

        .header-nav .nome {
            color: #5f6378;
            font-size: 0.875rem;
            display: none;
        }

        @media (min-width: 640px) {
            .header-nav .nome {
                display: inline;
            }
        }

        [data-theme="dark"] .header-nav .nome {
            color: #918fac;
        }

        .btn-primary {
            background: #7c3aed;
            color: #fff;
            padding: 0.4rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .btn-primary:hover {
            background: #6d28d9;
        }

        .btn-sair {
            color: #9ca3af;
            font-size: 0.875rem;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .btn-sair:hover {
            color: #ef4444;
        }

        [data-theme="dark"] .btn-sair {
            color: #5e5c76;
        }

        /* ── BADGE ────────────────────────────────────────────────── */
        .badge-cat {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: #ede9fe;
            color: #5b21b6;
            padding: 0.2rem 0.75rem;
            border-radius: 99px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        [data-theme="dark"] .badge-cat {
            background: #1c1831;
            color: #c4b5fd;
        }

        /* ── BOTÃO DE LIKE ───────────────────────────────────────── */
        .btn-like {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 1.25rem;
            border-radius: 99px;
            border: 2px solid #e8e2da;
            background: #ffffff;
            color: #5f6378;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            user-select: none;
        }

        [data-theme="dark"] .btn-like {
            background: #121218;
            border-color: #252535;
            color: #918fac;
        }

        .btn-like:hover {
            border-color: #ef4444;
            color: #ef4444;
            background: rgba(239, 68, 68, 0.06);
            transform: scale(1.04);
        }

        [data-theme="dark"] .btn-like:hover {
            background: rgba(239, 68, 68, 0.12);
        }

        .btn-like.curtido {
            border-color: #ef4444;
            background: rgba(239, 68, 68, 0.10);
            color: #ef4444;
        }

        [data-theme="dark"] .btn-like.curtido {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border-color: #f87171;
        }

        .btn-like .heart-icon {
            display: inline-block;
            transition: transform 0.1s;
        }

        .btn-like.animating .heart-icon {
            animation: heartBeat 0.4s ease;
        }

        @keyframes heartBeat {
            0%   { transform: scale(1); }
            30%  { transform: scale(1.35); }
            60%  { transform: scale(0.9); }
            100% { transform: scale(1); }
        }

        .like-wrap {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
        }

        .like-tooltip {
            position: absolute;
            bottom: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%);
            background: #1a1a1a;
            color: #ffffff;
            font-size: 0.72rem;
            font-weight: 500;
            padding: 0.35rem 0.75rem;
            border-radius: 0.4rem;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        [data-theme="dark"] .like-tooltip {
            background: #eeeaf8;
            color: #1a1a1a;
        }

        .like-tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 5px solid transparent;
            border-top-color: #1a1a1a;
        }

        [data-theme="dark"] .like-tooltip::after {
            border-top-color: #eeeaf8;
        }

        .btn-like:focus + .like-tooltip,
        .btn-like:hover + .like-tooltip {
            opacity: 1;
        }

        /* ── COMENTÁRIOS ──────────────────────────────────────────── */
        .comentario-item-header {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .comentario-item-excluir {
            color: #ef4444;
            text-decoration: none;
            font-size: 0.75rem;
            transition: color 0.2s ease;
        }

        .comentario-item-excluir:hover {
            color: #dc2626;
            text-decoration: underline;
        }

        /* ── FOOTER ────────────────────────────────────────────────── */
        .site-footer {
            margin-top: 4rem;
            border-top: 1px solid #e8e2da;
            background: #ffffff;
            padding: 2rem 1rem;
            text-align: center;
        }

        [data-theme="dark"] .site-footer {
            background: #121218;
            border-color: #252535;
        }

        .site-footer p {
            color: #9ca3af;
            font-size: 0.875rem;
        }

        [data-theme="dark"] .site-footer p {
            color: #5e5c76;
        }

        .site-footer span {
            color: #7c3aed;
            font-weight: 700;
        }

        /* ── RESPONSIVIDADE ───────────────────────────────────────── */
        @media (max-width: 768px) {
            .btn-like {
                padding: 0.4rem 1rem;
                font-size: 0.8rem;
            }

            .like-wrap {
                gap: 0.5rem;
            }
        }

        @media (max-width: 480px) {
            .btn-like {
                padding: 0.35rem 0.8rem;
                font-size: 0.75rem;
            }

            .like-tooltip {
                font-size: 0.65rem;
                padding: 0.25rem 0.5rem;
            }
        }
    </style>

    <script>
        (function() {
            var t = localStorage.getItem('gg-theme') || 'light';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
</head>
<body>

    <div id="sidebar-overlay" class="sidebar-overlay"></div>

    <!-- ══════════════════════════════════════════════════════
         SIDEBAR
    ══════════════════════════════════════════════════════ -->
    <aside id="sidebar" class="sidebar">
        <div>
            <a href="../../index.php" class="sidebar-logo">
                <div class="logo-icon">🎮</div>
                <div>
                    <div class="logo-text"><span>GG</span>News</div>
                    <span class="logo-tag">Games & E-Sports</span>
                </div>
            </a>
        </div>

        <div class="sidebar-section-label">Menu</div>
        <nav class="sidebar-nav">
            <a href="../../index.php" class="nav-link">
                <span class="nav-icon">🏠</span>
                Início
            </a>
            <a href="../../pages/noticias/dashboard.php" class="nav-link">
                <span class="nav-icon">📋</span>
                Painel
            </a>
            <?php if (usuario_logado()): ?>
                <a href="../../pages/usuario/editar_usuario.php" class="nav-link">
                    <span class="nav-icon">👤</span>
                    Minha Conta
                </a>
                <a href="../../pages/auth/logout.php" class="nav-link nav-link-danger">
                    <span class="nav-icon">🚪</span>
                    Sair
                </a>
            <?php else: ?>
                <a href="../../pages/auth/login.php" class="nav-link">
                    <span class="nav-icon">🔐</span>
                    Login
                </a>
                <a href="../../pages/auth/cadastro.php" class="nav-link">
                    <span class="nav-icon">📝</span>
                    Cadastrar
                </a>
            <?php endif; ?>
        </nav>

        <?php if (!empty($categorias)): ?>
        <div class="sidebar-section-label">Categorias</div>
        <nav class="sidebar-categories">
            <?php foreach ($categorias as $cat): ?>
                <a href="../../pages/categorias/categoria.php?slug=<?= $cat['slug'] ?>"
                   class="nav-link <?= ($noticia['categoria_nome'] ?? '') === $cat['nome'] ? 'active' : '' ?>">
                    <span class="cat-icon"><?= $cat['icone'] ?></span>
                    <?= escape($cat['nome']) ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <?php endif; ?>

        <div class="theme-toggle-wrap">
            <button id="theme-toggle" class="theme-toggle-btn" aria-label="Alternar tema">
                <div class="toggle-track" id="toggle-track">
                    <div class="toggle-thumb"></div>
                </div>
                <span class="toggle-icons" id="theme-icon">🌙</span>
                <span class="toggle-label" id="theme-label">Modo Escuro</span>
            </button>
            <p class="sidebar-footer-tag">GGNews &copy; <?= date('Y') ?></p>
        </div>
    </aside>

    <!-- ══════════════════════════════════════════════════════
         CONTEÚDO PRINCIPAL
    ══════════════════════════════════════════════════════ -->
    <div class="main-content">

        <header class="site-header">
            <div class="header-inner">
                <div class="header-left">
                    <button id="menu-toggle" class="menu-toggle" aria-label="Abrir menu">☰</button>
                    <a href="../../index.php" class="header-logo-mobile">
                        🎮 <span>GG</span>News
                    </a>
                </div>
                <nav class="header-nav">
                    <?php if (usuario_logado()): ?>
                        <?php $foto_usuario = get_usuario_foto(); ?>
                        <?php if ($foto_usuario): ?>
                            <img src="../../uploads/<?= escape($foto_usuario) ?>" class="avatar" alt="Avatar">
                        <?php else: ?>
                            <div class="avatar-fallback"><?= get_avatar_initials(get_usuario_nome()) ?></div>
                        <?php endif; ?>
                        <span class="nome">Olá, <?= escape(get_usuario_nome()) ?></span>
                        <a href="../../pages/noticias/dashboard.php" class="btn-primary">Painel</a>
                        <a href="../../pages/auth/logout.php" class="btn-sair">Sair</a>
                    <?php else: ?>
                        <a href="../../pages/auth/login.php" class="btn-sair" style="color:#7c3aed;">Login</a>
                        <a href="../../pages/auth/cadastro.php" class="btn-primary">Cadastrar</a>
                    <?php endif; ?>
                </nav>
            </div>
        </header>

        <main class="max-w-4xl mx-auto px-4 py-8">

            <!-- VOLTAR -->
            <a href="../../index.php"
               class="inline-flex items-center gap-1 text-sm mb-6 transition"
               style="color:var(--accent); text-decoration:none;">
                ← Voltar para as notícias
            </a>

            <!-- IMAGEM DESTAQUE -->
            <?php if ($noticia['imagem']): ?>
                <div class="rounded-2xl overflow-hidden mb-8 h-[400px]">
                    <img src="<?= escape($noticia['imagem']) ?>"
                         alt="<?= escape($noticia['titulo']) ?>"
                         class="w-full h-full object-cover">
                </div>
            <?php endif; ?>

            <!-- TÍTULO -->
            <h1 class="text-3xl md:text-4xl font-bold leading-tight"
                style="color:var(--text-primary)">
                <?= escape($noticia['titulo']) ?>
            </h1>

            <!-- META + LIKE -->
            <div class="flex flex-wrap items-center justify-between gap-4 mt-4 pb-6 mb-8"
                 style="border-bottom: 1px solid var(--border);">

                <!-- Categoria / Autor / Data -->
                <div class="flex flex-wrap items-center gap-3 text-sm">
                    <span class="badge-cat">
                        <?= $noticia['categoria_icone'] ?? '📰' ?>
                        <?= escape($noticia['categoria_nome'] ?? 'Games & E-Sports') ?>
                    </span>
                    <span style="color:var(--text-secondary)">
                        Por <strong style="color:var(--text-primary)"><?= escape($noticia['autor_nome']) ?></strong>
                    </span>
                    <span style="color:var(--text-muted)">•</span>
                    <span style="color:var(--text-muted)"><?= formatar_data($noticia['data']) ?></span>
                </div>

                <!-- BOTÃO DE LIKE -->
                <div class="like-wrap">
                    <button id="btn-like"
                            class="btn-like <?= $usuario_ja_curtiu ? 'curtido' : '' ?>"
                            data-noticia="<?= $id ?>"
                            data-logado="<?= usuario_logado() ? '1' : '0' ?>"
                            aria-label="Curtir notícia"
                            aria-pressed="<?= $usuario_ja_curtiu ? 'true' : 'false' ?>">
                        <span class="heart-icon"><?= $usuario_ja_curtiu ? '❤️' : '🤍' ?></span>
                        <span id="like-count"><?= $total_likes ?></span>
                        <span id="like-label"><?= $usuario_ja_curtiu ? 'Curtido' : 'Curtir' ?></span>
                    </button>
                    <?php if (!usuario_logado()): ?>
                        <span class="like-tooltip">Faça login para curtir</span>
                    <?php endif; ?>
                </div>

            </div>

            <!-- CORPO DO ARTIGO -->
            <article class="text-lg leading-relaxed whitespace-pre-line"
                     style="color:var(--text-secondary)">
                <?= escape($noticia['noticia']) ?>
            </article>

            <!-- LIKE EMBAIXO DO ARTIGO -->
            <div class="flex items-center gap-3 mt-10 pt-6" style="border-top:1px solid var(--border);">
                <span style="font-size:0.9rem; color:var(--text-muted);">Gostou desta notícia?</span>
                <button class="btn-like-mirror btn-like <?= $usuario_ja_curtiu ? 'curtido' : '' ?>"
                        data-noticia="<?= $id ?>"
                        data-logado="<?= usuario_logado() ? '1' : '0' ?>"
                        aria-label="Curtir notícia">
                    <span class="heart-icon"><?= $usuario_ja_curtiu ? '❤️' : '🤍' ?></span>
                    <span class="mirror-count"><?= $total_likes ?></span>
                    <span class="mirror-label"><?= $usuario_ja_curtiu ? 'Curtido' : 'Curtir' ?></span>
                </button>
            </div>

            <!-- ══════════════════════════════════════════════════════
                 SEÇÃO DE COMENTÁRIOS
            ══════════════════════════════════════════════════════ -->
            <section class="mt-12 pt-8" style="border-top: 1px solid var(--border);">

                <h3 class="text-2xl font-bold mb-6" style="color:var(--text-primary)">
                    💬 Comentários (<?= $total_comentarios ?>)
                </h3>

                <?php if ($erro_comentario): ?>
                    <div class="px-4 py-3 rounded-lg mb-4 border text-sm"
                         style="background:rgba(239,68,68,0.1); border-color:#ef4444; color:#dc2626;">
                        <?= escape($erro_comentario) ?>
                    </div>
                <?php endif; ?>

                <?php if ($sucesso_comentario): ?>
                    <div class="px-4 py-3 rounded-lg mb-4 border text-sm"
                         style="background:rgba(16,185,129,0.1); border-color:#10b981; color:#059669;">
                        <?= escape($sucesso_comentario) ?>
                    </div>
                <?php endif; ?>

                <?php $msg = get_mensagem(); if ($msg): ?>
                    <div class="px-4 py-3 rounded-lg mb-4 border text-sm"
                         style="<?= $msg['tipo'] === 'sucesso'
                            ? 'background:rgba(16,185,129,0.1); border-color:#10b981; color:#059669;'
                            : 'background:rgba(239,68,68,0.1); border-color:#ef4444; color:#dc2626;' ?>">
                        <?= escape($msg['texto']) ?>
                    </div>
                <?php endif; ?>

                <!-- Formulário de comentário -->
                <?php if (usuario_logado()): ?>
                    <div class="mb-8 p-6 rounded-xl" style="background:var(--bg-elevated); border:1px solid var(--border);">
                        <div class="flex items-center gap-3 mb-3">
                            <?php $foto_usuario = get_usuario_foto(); if ($foto_usuario): ?>
                                <img src="../../uploads/<?= escape($foto_usuario) ?>"
                                     style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid var(--border);">
                            <?php else: ?>
                                <div style="width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:var(--accent-light);color:var(--accent-text);font-weight:700;font-size:0.9rem;border:2px solid var(--border);">
                                    <?= get_avatar_initials(get_usuario_nome()) ?>
                                </div>
                            <?php endif; ?>
                            <span class="font-medium" style="color:var(--text-primary)">
                                <?= escape(get_usuario_nome()) ?>
                            </span>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="acao" value="comentar">
                            <textarea name="conteudo" rows="3"
                                      placeholder="Escreva seu comentário..."
                                      class="w-full px-4 py-3 rounded-lg resize-y"
                                      style="background:var(--bg-surface); border:1px solid var(--border);
                                             color:var(--text-primary); font-family:inherit; font-size:0.9rem;"
                                      maxlength="1000"
                                      required></textarea>
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:0.25rem;">
                                <span style="font-size:0.7rem; color:var(--text-muted);">Máximo 1000 caracteres</span>
                            </div>
                            <button type="submit" class="btn-primary mt-3 px-6 py-2 text-sm">
                                Enviar Comentário
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="text-center p-8 rounded-xl mb-8"
                         style="background:var(--bg-elevated); border:1px solid var(--border);">
                        <p style="color:var(--text-secondary);">
                            🔒 Faça <a href="../../pages/auth/login.php"
                                       style="color:var(--accent); font-weight:600; text-decoration:none;">login</a>
                            para comentar e interagir com a comunidade.
                        </p>
                    </div>
                <?php endif; ?>

                <!-- Lista de comentários -->
                <?php if (empty($comentarios)): ?>
                    <div class="text-center py-8" style="color:var(--text-muted);">
                        <p>Nenhum comentário ainda. Seja o primeiro a comentar!</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($comentarios as $comentario): ?>
                            <div class="p-4 rounded-xl"
                                 style="background:var(--bg-elevated); border:1px solid var(--border);">
                                <div class="comentario-item-header">
                                    <?php if (!empty($comentario['foto'])): ?>
                                        <img src="../../uploads/<?= escape($comentario['foto']) ?>"
                                             style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid var(--border);flex-shrink:0;">
                                    <?php else: ?>
                                        <div style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:var(--accent-light);color:var(--accent-text);font-weight:700;font-size:0.8rem;border:2px solid var(--border);flex-shrink:0;">
                                            <?= get_avatar_initials($comentario['nome']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between flex-wrap gap-2">
                                            <span class="font-semibold" style="color:var(--text-primary)">
                                                <?= escape($comentario['nome']) ?>
                                            </span>
                                            <span class="text-xs" style="color:var(--text-muted)">
                                                <?= formatar_data($comentario['data']) ?>
                                            </span>
                                        </div>
                                        <p class="mt-1 text-sm" style="color:var(--text-secondary); word-wrap:break-word;">
                                            <?= nl2br(escape($comentario['conteudo'])) ?>
                                        </p>
                                        <?php if (usuario_logado() && get_usuario_id() == $comentario['usuario_id']): ?>
                                            <a href="noticia.php?id=<?= $id ?>&excluir_comentario=<?= $comentario['id'] ?>"
                                               onclick="return confirm('Tem certeza que deseja excluir este comentário?')"
                                               class="comentario-item-excluir">
                                                Excluir
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </section>

            <!-- RODAPÉ DO ARTIGO -->
            <div class="mt-12 pt-6 flex items-center justify-between flex-wrap gap-4"
                 style="border-top: 1px solid var(--border);">
                <a href="../../index.php"
                   class="inline-flex items-center gap-2 text-sm font-medium transition"
                   style="color:var(--accent); text-decoration:none;">
                    ← Todas as notícias
                </a>
                <?php if (usuario_logado() && $noticia['autor'] == get_usuario_id()): ?>
                    <a href="editar_noticia.php?id=<?= $noticia['id'] ?>" class="btn-primary text-sm px-4 py-2">
                        Editar notícia
                    </a>
                <?php endif; ?>
            </div>

        </main>

        <footer class="site-footer">
            <p>🎮 <span>GG</span>News &copy; <?= date('Y') ?> — Portal de Notícias de Games e E-Sports</p>
        </footer>

    </div>

    <!-- ══════════════════════════════════════════════════════
         SCRIPTS
    ══════════════════════════════════════════════════════ -->
    <script>
    (function () {
        // ── Tema ────────────────────────────────────────────────
        var html    = document.documentElement;
        var btn     = document.getElementById('theme-toggle');
        var track   = document.getElementById('toggle-track');
        var label   = document.getElementById('theme-label');
        var icon    = document.getElementById('theme-icon');
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('sidebar-overlay');
        var menuBtn = document.getElementById('menu-toggle');

        function applyTheme(theme) {
            html.setAttribute('data-theme', theme);
            localStorage.setItem('gg-theme', theme);
            if (theme === 'dark') {
                track  && track.classList.add('is-dark');
                if (label) label.textContent = 'Modo Claro';
                if (icon)  icon.textContent  = '☀️';
            } else {
                track  && track.classList.remove('is-dark');
                if (label) label.textContent = 'Modo Escuro';
                if (icon)  icon.textContent  = '🌙';
            }
        }

        applyTheme(html.getAttribute('data-theme') || 'light');
        btn && btn.addEventListener('click', function () {
            applyTheme(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
        });

        // ── Sidebar mobile ───────────────────────────────────────
        function openSidebar() {
            sidebar.classList.add('mobile-open');
            overlay.classList.add('active');
            menuBtn && menuBtn.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('active');
            menuBtn && menuBtn.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }

        menuBtn && menuBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            sidebar.classList.contains('mobile-open') ? closeSidebar() : openSidebar();
        });

        overlay && overlay.addEventListener('click', closeSidebar);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && window.innerWidth < 768) closeSidebar();
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 768) {
                closeSidebar();
                document.body.style.overflow = '';
            }
        });

        // ── LIKES ────────────────────────────────────────────────
        var btnLike   = document.getElementById('btn-like');
        var mirrors   = document.querySelectorAll('.btn-like-mirror');
        var likeCount = document.getElementById('like-count');
        var likeLabel = document.getElementById('like-label');

        function sincronizarMirrors(liked, total) {
            // Atualiza o botão principal
            if (likeCount) likeCount.textContent = total;
            if (likeLabel) likeLabel.textContent = liked ? 'Curtido' : 'Curtir';

            // Atualiza todos os mirrors
            mirrors.forEach(function(m) {
                m.classList.toggle('curtido', liked);
                m.setAttribute('aria-pressed', liked ? 'true' : 'false');
                var hIcon  = m.querySelector('.heart-icon');
                var mCount = m.querySelector('.mirror-count');
                var mLabel = m.querySelector('.mirror-label');
                if (hIcon)  hIcon.textContent  = liked ? '❤️' : '🤍';
                if (mCount) mCount.textContent  = total;
                if (mLabel) mLabel.textContent  = liked ? 'Curtido' : 'Curtir';
            });
        }

        function handleLike(botao) {
            var noticiaId = botao.getAttribute('data-noticia');
            var logado    = botao.getAttribute('data-logado') === '1';

            if (!logado) {
                window.location.href = '../../pages/auth/login.php';
                return;
            }

            // Desabilita durante o request para evitar duplo clique
            botao.disabled = true;

            // Animação do coração
            botao.classList.add('animating');
            setTimeout(function() { botao.classList.remove('animating'); }, 400);

            var formData = new FormData();
            formData.append('acao', 'like');

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.erro) {
                    alert(data.erro);
                    return;
                }

                var liked = data.liked;
                var total = data.total;

                // Atualiza botão principal
                if (btnLike) {
                    btnLike.classList.toggle('curtido', liked);
                    btnLike.setAttribute('aria-pressed', liked ? 'true' : 'false');
                    var hIcon = btnLike.querySelector('.heart-icon');
                    if (hIcon) hIcon.textContent = liked ? '❤️' : '🤍';
                }

                sincronizarMirrors(liked, total);
            })
            .catch(function(e) {
                console.error('Erro no like:', e);
            })
            .finally(function() {
                botao.disabled = false;
            });
        }

        // Botão principal
        if (btnLike) {
            btnLike.addEventListener('click', function() {
                handleLike(this);
            });
        }

        // Botões mirror (embaixo do artigo)
        mirrors.forEach(function(m) {
            m.addEventListener('click', function() {
                handleLike(this);
            });
        });

    })();
    </script>

</body>
</html>