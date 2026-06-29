<?php
// =====================================================
// PÁGINA DE CATEGORIA
// Arquivo: pages/categorias/categoria.php
// =====================================================

require_once __DIR__ . '/../../includes/conexao.php';
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/avatar_helper.php';

$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header("Location: ../../index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM categorias WHERE slug = ?");
$stmt->execute([$slug]);
$categoria = $stmt->fetch();

if (!$categoria) {
    set_mensagem('erro', 'Categoria não encontrada.');
    header("Location: ../../index.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT n.*, u.nome AS autor_nome, u.foto AS autor_foto,
           c.nome AS categoria_nome, c.icone AS categoria_icone
    FROM noticias n
    INNER JOIN usuarios u ON n.autor = u.id
    LEFT JOIN categorias c ON n.categoria_id = c.id
    WHERE n.categoria_id = ?
    ORDER BY n.data DESC
");
$stmt->execute([$categoria['id']]);
$noticias = $stmt->fetchAll();

$categorias = get_categorias($pdo);

$usuario_logado = null;
if (usuario_logado()) {
    $usuario_logado = [
        'nome' => get_usuario_nome(),
        'foto' => get_usuario_foto()
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape($categoria['nome']) ?> - GGNews</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../css/style.css">

    <style>
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

        /* ── AVATAR NO HEADER ───────────────────────────────────── */
        .header-nav .avatar-img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e8e2da;
        }

        [data-theme="dark"] .header-nav .avatar-img {
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

        .category-header {
            position: relative;
            border-radius: 1.5rem;
            overflow: hidden;
            padding: 2.5rem 3rem;
            margin-bottom: 2.5rem;
            background: linear-gradient(135deg, #ede9fe, #f8f4f0);
            border: 2px solid #e8e2da;
        }

        [data-theme="dark"] .category-header {
            background: linear-gradient(135deg, #1c1831, #1a1a22);
            border-color: #252535;
        }

        .category-header-content {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .category-icon-wrapper {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.8rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border: 2px solid #e8e2da;
            flex-shrink: 0;
        }

        [data-theme="dark"] .category-icon-wrapper {
            background: #121218;
            border-color: #252535;
        }

        .category-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1a1a1a;
            margin: 0;
            line-height: 1.2;
        }

        [data-theme="dark"] .category-title {
            color: #eeeaf8;
        }

        .category-subtitle {
            font-size: 1rem;
            color: #5f6378;
            margin: 0.25rem 0 0 0;
        }

        [data-theme="dark"] .category-subtitle {
            color: #918fac;
        }

        .category-count {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #9ca3af;
            background: #ffffff;
            padding: 0.3rem 1rem;
            border-radius: 99px;
            border: 1px solid #e8e2da;
        }

        [data-theme="dark"] .category-count {
            color: #5e5c76;
            background: #121218;
            border-color: #252535;
        }

        .category-news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .category-news-card {
            background: #ffffff;
            border: 1px solid #e8e2da;
            border-radius: 1rem;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            text-decoration: none;
            display: flex;
            flex-direction: column;
        }

        [data-theme="dark"] .category-news-card {
            background: #121218;
            border-color: #252535;
            box-shadow: 0 2px 10px rgba(0,0,0,0.35);
        }

        .category-news-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 8px 28px rgba(0,0,0,0.10);
            border-color: #ede9fe;
        }

        .category-news-image {
            height: 200px;
            overflow: hidden;
            background: #f8f4f0;
        }

        [data-theme="dark"] .category-news-image {
            background: #1a1a22;
        }

        .category-news-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .category-news-card:hover .category-news-image img {
            transform: scale(1.08);
        }

        .category-news-image-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            background: linear-gradient(135deg, #ede9fe, #f8f4f0);
        }

        [data-theme="dark"] .category-news-image-placeholder {
            background: linear-gradient(135deg, #1c1831, #1a1a22);
        }

        .category-news-body {
            padding: 1.25rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .category-news-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: #ede9fe;
            color: #5b21b6;
            padding: 0.2rem 0.75rem;
            border-radius: 99px;
            font-size: 0.7rem;
            font-weight: 600;
            align-self: flex-start;
            margin-bottom: 0.75rem;
        }

        [data-theme="dark"] .category-news-badge {
            background: #1c1831;
            color: #c4b5fd;
        }

        .category-news-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0 0 0.5rem 0;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        [data-theme="dark"] .category-news-title {
            color: #eeeaf8;
        }

        .category-news-card:hover .category-news-title {
            color: #7c3aed;
        }

        .category-news-excerpt {
            font-size: 0.875rem;
            color: #5f6378;
            line-height: 1.6;
            flex: 1;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        [data-theme="dark"] .category-news-excerpt {
            color: #918fac;
        }

        .category-news-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 1rem;
            padding-top: 0.75rem;
            border-top: 1px solid #e8e2da;
            font-size: 0.75rem;
            color: #9ca3af;
        }

        [data-theme="dark"] .category-news-footer {
            border-color: #252535;
            color: #5e5c76;
        }

        .category-news-author {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .category-news-author-avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #e8e2da;
        }

        [data-theme="dark"] .category-news-author-avatar {
            border-color: #252535;
        }

        .category-empty {
            text-align: center;
            padding: 4rem 2rem;
            background: #ffffff;
            border-radius: 1.5rem;
            border: 2px dashed #e8e2da;
        }

        [data-theme="dark"] .category-empty {
            background: #121218;
            border-color: #252535;
        }

        .category-empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.6;
        }

        .category-empty-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
        }

        [data-theme="dark"] .category-empty-title {
            color: #eeeaf8;
        }

        .category-empty-text {
            color: #9ca3af;
            font-size: 0.95rem;
            max-width: 400px;
            margin: 0 auto;
        }

        [data-theme="dark"] .category-empty-text {
            color: #5e5c76;
        }

        .category-empty-link {
            display: inline-block;
            margin-top: 1.25rem;
            color: #7c3aed;
            font-weight: 500;
            text-decoration: none;
        }

        .category-empty-link:hover {
            color: #6d28d9;
        }

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

        @media (max-width: 1024px) {
            .category-header {
                padding: 2rem;
            }
            .category-title {
                font-size: 2rem;
            }
            .category-icon-wrapper {
                width: 64px;
                height: 64px;
                font-size: 2rem;
            }
            .category-news-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .category-header {
                padding: 1.5rem;
                border-radius: 1rem;
            }
            .category-header-content {
                flex-direction: column;
                text-align: center;
            }
            .category-icon-wrapper {
                width: 72px;
                height: 72px;
                font-size: 2.4rem;
            }
            .category-title {
                font-size: 1.75rem;
            }
            .category-news-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            .category-news-image {
                height: 180px;
            }
        }

        @media (max-width: 480px) {
            .category-header {
                padding: 1.25rem;
            }
            .category-title {
                font-size: 1.5rem;
            }
            .category-icon-wrapper {
                width: 60px;
                height: 60px;
                font-size: 2rem;
            }
            .category-news-image {
                height: 160px;
            }
            .category-news-body {
                padding: 1rem;
            }
            .category-news-title {
                font-size: 0.95rem;
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
                <a href="categoria.php?slug=<?= $cat['slug'] ?>"
                   class="nav-link <?= $cat['id'] === $categoria['id'] ? 'active' : '' ?>">
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
                        <!-- AVATAR DO USUÁRIO LOGADO -->
                        <?php 
                        $foto_usuario = get_usuario_foto();
                        if ($foto_usuario): ?>
                            <img src="../../uploads/<?= escape($foto_usuario) ?>" class="avatar-img" alt="Avatar">
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

        <main style="max-width:1200px; margin:0 auto; padding:2rem 1rem;">

            <div class="category-header">
                <div class="category-header-content">
                    <div class="category-icon-wrapper">
                        <?= $categoria['icone'] ?>
                    </div>
                    <div>
                        <h1 class="category-title"><?= escape($categoria['nome']) ?></h1>
                        <p class="category-subtitle">Todas as notícias sobre <?= escape($categoria['nome']) ?></p>
                        <span class="category-count">
                            📰 <?= count($noticias) ?> notícia(s) publicada(s)
                        </span>
                    </div>
                </div>
            </div>

            <?php if (empty($noticias)): ?>
                <div class="category-empty">
                    <div class="category-empty-icon">📭</div>
                    <h2 class="category-empty-title">Nenhuma notícia encontrada</h2>
                    <p class="category-empty-text">
                        Ainda não há notícias publicadas nesta categoria. Volte em breve para novidades!
                    </p>
                    <a href="../../index.php" class="category-empty-link">← Voltar para o início</a>
                </div>
            <?php else: ?>
                <div class="category-news-grid">
                    <?php foreach ($noticias as $noticia): ?>
                        <a href="../../pages/noticias/noticia.php?id=<?= $noticia['id'] ?>" class="category-news-card">
                            <div class="category-news-image">
                                <?php if ($noticia['imagem']): ?>
                                    <img src="<?= escape($noticia['imagem']) ?>" alt="<?= escape($noticia['titulo']) ?>">
                                <?php else: ?>
                                    <div class="category-news-image-placeholder">
                                        <?= $categoria['icone'] ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="category-news-body">
                                <span class="category-news-badge">
                                    <?= $categoria['icone'] ?>
                                    <?= escape($categoria['nome']) ?>
                                </span>
                                <h3 class="category-news-title"><?= escape($noticia['titulo']) ?></h3>
                                <p class="category-news-excerpt"><?= escape(resumo_texto($noticia['noticia'], 120)) ?></p>
                                <div class="category-news-footer">
                                    <span class="category-news-author">
                                        <?php if ($noticia['autor_foto']): ?>
                                            <img src="../../uploads/<?= escape($noticia['autor_foto']) ?>"
                                                 alt="<?= escape($noticia['autor_nome']) ?>"
                                                 class="category-news-author-avatar">
                                        <?php endif; ?>
                                        <?= escape($noticia['autor_nome']) ?>
                                    </span>
                                    <span><?= formatar_data($noticia['data']) ?></span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </main>

        <footer class="site-footer">
            <p>🎮 <span>GG</span>News &copy; <?= date('Y') ?> — Portal de Notícias de Games e E-Sports</p>
        </footer>

    </div>

    <script>
    (function () {
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
    })();
    </script>

</body>
</html>