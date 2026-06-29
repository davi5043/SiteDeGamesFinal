<?php
// =====================================================
// PÁGINA INICIAL
// Arquivo: index.php
// =====================================================

require_once __DIR__ . '/includes/conexao.php';
require_once __DIR__ . '/includes/funcoes.php';
require_once __DIR__ . '/includes/avatar_helper.php';

$categorias = get_categorias($pdo);

$stmt = $pdo->query("
    SELECT n.*, u.nome AS autor_nome, c.nome AS categoria_nome, c.icone AS categoria_icone
    FROM noticias n
    INNER JOIN usuarios u ON n.autor = u.id
    LEFT JOIN categorias c ON n.categoria_id = c.id
    ORDER BY n.data DESC
");
$noticias = $stmt->fetchAll();

$destaque = $noticias[0] ?? null;
$demais   = array_slice($noticias, 1);

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
    <title>GGNews - Portal de Games & E-Sports</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/style.css">

    <style>
        /* =====================================================
           ESTILOS PARA PÁGINA INICIAL
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

        .hero-news {
            position: relative;
            border-radius: 1rem;
            overflow: hidden;
            height: 400px;
            display: flex;
            align-items: flex-end;
            margin-bottom: 2.5rem;
            background: linear-gradient(135deg, #ede9fe, #f8f4f0);
        }

        [data-theme="dark"] .hero-news {
            background: linear-gradient(135deg, #1c1831, #1a1a22);
        }

        .hero-news img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .hero-news:hover img {
            transform: scale(1.05);
        }

        .hero-gradient {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(255,255,255,0.93) 0%, rgba(255,255,255,0.4) 50%, transparent 100%);
        }

        [data-theme="dark"] .hero-gradient {
            background: linear-gradient(to top, rgba(12,12,16,0.95) 0%, rgba(12,12,16,0.4) 55%, transparent 100%);
        }

        .hero-content {
            position: relative;
            padding: 2rem;
            width: 100%;
        }

        .hero-content .badge-cat {
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

        [data-theme="dark"] .hero-content .badge-cat {
            background: #1c1831;
            color: #c4b5fd;
        }

        .hero-content h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-top: 0.75rem;
        }

        [data-theme="dark"] .hero-content h2 {
            color: #eeeaf8;
        }

        .hero-content h2:hover {
            color: #7c3aed;
        }

        .hero-content p {
            color: #5f6378;
            margin-top: 0.5rem;
            max-width: 600px;
            font-size: 1.05rem;
        }

        [data-theme="dark"] .hero-content p {
            color: #918fac;
        }

        .hero-content .meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 0.75rem;
            font-size: 0.875rem;
            color: #9ca3af;
        }

        [data-theme="dark"] .hero-content .meta {
            color: #5e5c76;
        }

        .hero-content a {
            text-decoration: none;
        }

        .noticias-section {
            margin-top: 2.5rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a1a1a;
            padding-left: 1rem;
            border-left: 4px solid #7c3aed;
            margin-bottom: 1.5rem;
        }

        [data-theme="dark"] .section-title {
            color: #eeeaf8;
        }

        .noticia-list-item {
            display: flex;
            gap: 1.5rem;
            background: #ffffff;
            border: 1px solid #e8e2da;
            border-radius: 1rem;
            padding: 1.25rem;
            margin-bottom: 1.25rem;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        [data-theme="dark"] .noticia-list-item {
            background: #121218;
            border-color: #252535;
            box-shadow: 0 2px 10px rgba(0,0,0,0.35);
        }

        .noticia-list-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 28px rgba(0,0,0,0.10);
            border-color: #ede9fe;
        }

        [data-theme="dark"] .noticia-list-item:hover {
            border-color: #1c1831;
        }

        .noticia-list-image {
            width: 220px;
            min-height: 140px;
            border-radius: 0.5rem;
            overflow: hidden;
            flex-shrink: 0;
            background: #f8f4f0;
        }

        [data-theme="dark"] .noticia-list-image {
            background: #1a1a22;
        }

        .noticia-list-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .noticia-list-item:hover .noticia-list-image img {
            transform: scale(1.05);
        }

        .noticia-list-image-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            background: linear-gradient(135deg, #ede9fe, #f8f4f0);
        }

        [data-theme="dark"] .noticia-list-image-placeholder {
            background: linear-gradient(135deg, #1c1831, #1a1a22);
        }

        .noticia-list-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .noticia-list-content .badge-cat {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: #ede9fe;
            color: #5b21b6;
            padding: 0.15rem 0.6rem;
            border-radius: 99px;
            font-size: 0.7rem;
            font-weight: 600;
            align-self: flex-start;
            margin-bottom: 0.5rem;
        }

        [data-theme="dark"] .noticia-list-content .badge-cat {
            background: #1c1831;
            color: #c4b5fd;
        }

        .noticia-list-content h3 {
            font-size: 1.15rem;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0 0 0.5rem 0;
            line-height: 1.4;
            transition: color 0.2s ease;
        }

        [data-theme="dark"] .noticia-list-content h3 {
            color: #eeeaf8;
        }

        .noticia-list-item:hover .noticia-list-content h3 {
            color: #7c3aed;
        }

        [data-theme="dark"] .noticia-list-item:hover .noticia-list-content h3 {
            color: #a78bfa;
        }

        .noticia-list-content p {
            color: #5f6378;
            font-size: 0.9rem;
            line-height: 1.6;
            flex: 1;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        [data-theme="dark"] .noticia-list-content p {
            color: #918fac;
        }

        .noticia-list-footer {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid #e8e2da;
            font-size: 0.8rem;
            color: #9ca3af;
        }

        [data-theme="dark"] .noticia-list-footer {
            border-color: #252535;
            color: #5e5c76;
        }

        .noticia-list-footer .autor {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sem-noticias {
            text-align: center;
            padding: 3rem 2rem;
            color: #9ca3af;
        }

        [data-theme="dark"] .sem-noticias {
            color: #5e5c76;
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

        @media (max-width: 768px) {
            .noticia-list-item {
                flex-direction: column;
                padding: 1rem;
            }

            .noticia-list-image {
                width: 100%;
                height: 180px;
            }

            .hero-news {
                height: 300px;
            }

            .hero-content h2 {
                font-size: 1.5rem;
            }

            .hero-content p {
                font-size: 0.9rem;
            }

            .hero-content {
                padding: 1.25rem;
            }

            .header-nav .nome {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .hero-news {
                height: 250px;
            }

            .hero-content h2 {
                font-size: 1.2rem;
            }

            .hero-content .meta {
                font-size: 0.75rem;
                flex-wrap: wrap;
            }

            .noticia-list-content h3 {
                font-size: 1rem;
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
            <a href="index.php" class="sidebar-logo">
                <div class="logo-icon">🎮</div>
                <div>
                    <div class="logo-text"><span>GG</span>News</div>
                    <span class="logo-tag">Games & E-Sports</span>
                </div>
            </a>
        </div>

        <div class="sidebar-section-label">Menu</div>
        <nav class="sidebar-nav">
            <a href="index.php" class="nav-link active">
                <span class="nav-icon">🏠</span>
                Início
            </a>
            <a href="pages/noticias/dashboard.php" class="nav-link">
                <span class="nav-icon">📋</span>
                Painel
            </a>

            <?php if (usuario_logado()): ?>
                <a href="pages/usuario/editar_usuario.php" class="nav-link">
                    <span class="nav-icon">👤</span>
                    Minha Conta
                </a>
                <a href="pages/auth/logout.php" class="nav-link nav-link-danger">
                    <span class="nav-icon">🚪</span>
                    Sair
                </a>
            <?php else: ?>
                <a href="pages/auth/login.php" class="nav-link">
                    <span class="nav-icon">🔐</span>
                    Login
                </a>
                <a href="pages/auth/cadastro.php" class="nav-link">
                    <span class="nav-icon">📝</span>
                    Cadastrar
                </a>
            <?php endif; ?>
        </nav>

        <?php if (!empty($categorias)): ?>
        <div class="sidebar-section-label">Categorias</div>
        <nav class="sidebar-categories">
            <?php foreach ($categorias as $cat): ?>
                <a href="pages/categorias/categoria.php?slug=<?= $cat['slug'] ?>" class="nav-link">
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
                    <a href="index.php" class="header-logo-mobile">
                        🎮 <span>GG</span>News
                    </a>
                </div>

                <nav class="header-nav">
                    <?php if (usuario_logado()): ?>
                        <!-- AVATAR DO USUÁRIO LOGADO -->
                        <?php 
                        $foto_usuario = get_usuario_foto();
                        if ($foto_usuario): ?>
                            <img src="uploads/<?= escape($foto_usuario) ?>" class="avatar-img" alt="Avatar">
                        <?php else: ?>
                            <div class="avatar-fallback"><?= get_avatar_initials(get_usuario_nome()) ?></div>
                        <?php endif; ?>
                        <span class="nome">Olá, <?= escape(get_usuario_nome()) ?></span>
                        <a href="pages/noticias/dashboard.php" class="btn-primary">Painel</a>
                        <a href="pages/auth/logout.php" class="btn-sair">Sair</a>
                    <?php else: ?>
                        <a href="pages/auth/login.php" class="btn-sair" style="color:#7c3aed;">Login</a>
                        <a href="pages/auth/cadastro.php" class="btn-primary">Cadastrar</a>
                    <?php endif; ?>
                </nav>
            </div>
        </header>

        <main style="max-width:1200px; margin:0 auto; padding:2rem 1rem;">

            <?php if ($destaque): ?>
                <a href="pages/noticias/noticia.php?id=<?= $destaque['id'] ?>" style="text-decoration:none;">
                    <div class="hero-news">
                        <?php if ($destaque['imagem']): ?>
                            <img src="<?= escape($destaque['imagem']) ?>" alt="<?= escape($destaque['titulo']) ?>">
                        <?php endif; ?>
                        <div class="hero-gradient"></div>
                        <div class="hero-content">
                            <span class="badge-cat">
                                <?= $destaque['categoria_icone'] ?? '📰' ?>
                                <?= escape($destaque['categoria_nome'] ?? 'Destaque') ?>
                            </span>
                            <h2><?= escape($destaque['titulo']) ?></h2>
                            <p><?= escape(resumo_texto($destaque['noticia'], 180)) ?></p>
                            <div class="meta">
                                <span>Por <?= escape($destaque['autor_nome']) ?></span>
                                <span>•</span>
                                <span><?= formatar_data($destaque['data']) ?></span>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endif; ?>

            <section class="noticias-section">
                <h2 class="section-title">Últimas Notícias</h2>

                <?php if (empty($demais) && !$destaque): ?>
                    <div class="sem-noticias">Nenhuma notícia publicada ainda.</div>
                <?php else: ?>
                    <?php foreach ($demais as $noticia): ?>
                        <a href="pages/noticias/noticia.php?id=<?= $noticia['id'] ?>" class="noticia-list-item">
                            <div class="noticia-list-image">
                                <?php if ($noticia['imagem']): ?>
                                    <img src="<?= escape($noticia['imagem']) ?>" alt="<?= escape($noticia['titulo']) ?>">
                                <?php else: ?>
                                    <div class="noticia-list-image-placeholder">
                                        <?= $noticia['categoria_icone'] ?? '🎮' ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="noticia-list-content">
                                <?php if ($noticia['categoria_nome']): ?>
                                    <span class="badge-cat">
                                        <?= $noticia['categoria_icone'] ?>
                                        <?= escape($noticia['categoria_nome']) ?>
                                    </span>
                                <?php endif; ?>
                                <h3><?= escape($noticia['titulo']) ?></h3>
                                <p><?= escape(resumo_texto($noticia['noticia'], 200)) ?></p>
                                <div class="noticia-list-footer">
                                    <span class="autor">
                                        <span>👤</span>
                                        <?= escape($noticia['autor_nome']) ?>
                                    </span>
                                    <span>•</span>
                                    <span><?= formatar_data($noticia['data']) ?></span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

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

        function isMobile() {
            return window.innerWidth < 768;
        }

        menuBtn && menuBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (sidebar.classList.contains('mobile-open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });

        overlay && overlay.addEventListener('click', closeSidebar);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && isMobile()) {
                closeSidebar();
            }
        });

        window.addEventListener('resize', function () {
            if (!isMobile()) {
                closeSidebar();
                document.body.style.overflow = '';
            }
        });

    })();
    </script>

</body>
</html>