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
    redirecionar('index.php');
}

$stmt = $pdo->prepare("SELECT * FROM categorias WHERE slug = ?");
$stmt->execute([$slug]);
$categoria = $stmt->fetch();

if (!$categoria) {
    set_mensagem('erro', 'Categoria não encontrada.');
    redirecionar('index.php');
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
        /* =====================================================
           ESTILOS PARA PÁGINAS DE CATEGORIA (INLINE)
           ===================================================== */

        /* ── HEADER DA CATEGORIA ─────────────────────────────── */
        .category-header {
            position: relative;
            border-radius: 1.5rem;
            overflow: hidden;
            padding: 2.5rem 3rem;
            margin-bottom: 2.5rem;
            background: linear-gradient(135deg, #ede9fe, #f3f0eb);
            border: 2px solid #e8e2da;
            transition: all 0.3s ease;
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
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        [data-theme="dark"] .category-icon-wrapper {
            background: #121218;
            border-color: #252535;
        }

        .category-header:hover .category-icon-wrapper {
            transform: scale(1.05) rotate(-5deg);
            box-shadow: 0 8px 28px rgba(0,0,0,0.10);
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

        /* ── GRID DE NOTÍCIAS ──────────────────────────────────── */
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
            transition: all 0.3s cubic-bezier(.4, 0, .2, 1);
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

        [data-theme="dark"] .category-news-card:hover {
            border-color: #1c1831;
        }

        .category-news-image {
            height: 200px;
            overflow: hidden;
            background: #f3f0eb;
            position: relative;
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
            background: linear-gradient(135deg, #ede9fe, #f3f0eb);
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
            transition: color 0.2s ease;
        }

        [data-theme="dark"] .category-news-title {
            color: #eeeaf8;
        }

        .category-news-card:hover .category-news-title {
            color: #7c3aed;
        }

        [data-theme="dark"] .category-news-card:hover .category-news-title {
            color: #a78bfa;
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

        /* ── ESTADO VAZIO ──────────────────────────────────────── */
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
            transition: color 0.2s ease;
        }

        .category-empty-link:hover {
            color: #6d28d9;
        }

        /* ── RESPONSIVIDADE ────────────────────────────────────── */
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

        /* ── ANIMAÇÃO DE ENTRADA ──────────────────────────────── */
        @keyframes categoryFadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .category-news-card {
            animation: categoryFadeIn 0.4s ease-out both;
        }

        .category-news-card:nth-child(1) { animation-delay: 0.05s; }
        .category-news-card:nth-child(2) { animation-delay: 0.10s; }
        .category-news-card:nth-child(3) { animation-delay: 0.15s; }
        .category-news-card:nth-child(4) { animation-delay: 0.20s; }
        .category-news-card:nth-child(5) { animation-delay: 0.25s; }
        .category-news-card:nth-child(6) { animation-delay: 0.30s; }
        .category-news-card:nth-child(7) { animation-delay: 0.35s; }
        .category-news-card:nth-child(8) { animation-delay: 0.40s; }
        .category-news-card:nth-child(9) { animation-delay: 0.45s; }
        .category-news-card:nth-child(10) { animation-delay: 0.50s; }
        .category-news-card:nth-child(11) { animation-delay: 0.55s; }
        .category-news-card:nth-child(12) { animation-delay: 0.60s; }
    </style>

    <script>
        (function() {
            var t = localStorage.getItem('gg-theme') || 'light';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
</head>
<body class="min-h-screen flex">

    <div id="sidebar-overlay" class="sidebar-overlay"></div>

    <aside id="sidebar" class="sidebar w-64 fixed h-full z-50 flex-col p-5 overflow-y-auto hidden md:flex">

        <div class="mb-7">
            <a href="../../index.php" class="sidebar-logo">
                <div class="logo-icon">🎮</div>
                <div>
                    <div class="logo-text"><span>GG</span>News</div>
                    <span class="logo-tag">Games & E-Sports</span>
                </div>
            </a>
        </div>

        <div class="sidebar-section-label">Menu</div>
        <nav class="sidebar-nav mb-4">
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
        <nav class="sidebar-categories mb-4">
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

    <div class="flex-1 md:ml-64 min-w-0">

        <header class="site-header sticky top-0 z-30 px-4 py-3">
            <div class="max-w-7xl mx-auto flex items-center justify-between">

                <div class="flex items-center gap-3">
                    <button id="menu-toggle"
                            class="md:hidden text-2xl p-1.5 rounded-lg transition"
                            style="color:var(--text-primary); background:transparent;"
                            aria-label="Abrir menu"
                            aria-expanded="false"
                            aria-controls="sidebar">
                        ☰
                    </button>
                    <a href="../../index.php" class="flex items-center gap-2 md:hidden text-lg font-bold"
                       style="color:var(--text-primary); text-decoration:none;">
                        🎮 <span style="color:var(--accent)">GG</span>News
                    </a>
                </div>

                <nav class="flex items-center gap-3">
                    <?php if (usuario_logado()): ?>
                        <div class="flex items-center gap-2">
                            <?= render_avatar($usuario_logado, 32) ?>
                            <span class="hidden sm:inline text-sm" style="color:var(--text-secondary)">
                                Olá, <?= escape(get_usuario_nome()) ?>
                            </span>
                        </div>
                        <a href="../../pages/noticias/dashboard.php" class="btn-primary text-sm px-4 py-2">
                            Painel
                        </a>
                        <a href="../../pages/auth/logout.php"
                           style="color:var(--text-muted); font-size:.875rem; text-decoration:none;">Sair</a>
                    <?php else: ?>
                        <a href="../../pages/auth/login.php"
                           style="color:var(--text-secondary); font-size:.875rem; text-decoration:none;">Login</a>
                        <a href="../../pages/auth/cadastro.php" class="btn-primary text-sm px-4 py-2">
                            Cadastrar
                        </a>
                    <?php endif; ?>
                </nav>

            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 py-8">

            <!-- HEADER DA CATEGORIA -->
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

            <!-- GRID DE NOTÍCIAS -->
            <?php if (empty($noticias)): ?>
                <div class="category-empty">
                    <div class="category-empty-icon">📭</div>
                    <h2 class="category-empty-title">Nenhuma notícia encontrada</h2>
                    <p class="category-empty-text">
                        Ainda não há notícias publicadas nesta categoria. 
                        Volte em breve para novidades!
                    </p>
                    <a href="../../index.php" class="category-empty-link">
                        ← Voltar para o início
                    </a>
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
                                <h3 class="category-news-title">
                                    <?= escape($noticia['titulo']) ?>
                                </h3>
                                <p class="category-news-excerpt">
                                    <?= escape(resumo_texto($noticia['noticia'], 120)) ?>
                                </p>
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

        <footer class="mt-16 border-t" style="background:var(--bg-surface); border-color:var(--border)">
            <div class="max-w-7xl mx-auto px-4 py-8 text-center">
                <p class="text-sm" style="color:var(--text-muted)">
                    🎮 <span style="color:var(--accent); font-weight:700">GG</span>News
                    &copy; <?= date('Y') ?> — Portal de Notícias de Games e E-Sports
                </p>
            </div>
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