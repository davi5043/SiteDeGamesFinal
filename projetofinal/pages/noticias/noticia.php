<?php
// =====================================================
// PÁGINA INDIVIDUAL DA NOTÍCIA
// Arquivo: pages/noticias/noticia.php
// =====================================================

require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/conexao.php';

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    redirecionar('index.php');
}

$stmt = $pdo->prepare("
    SELECT n.*, u.nome AS autor_nome, c.nome AS categoria_nome, c.icone AS categoria_icone
    FROM noticias n
    INNER JOIN usuarios u ON n.autor = u.id
    LEFT JOIN categorias c ON n.categoria_id = c.id
    WHERE n.id = ?
");
$stmt->execute([$id]);
$noticia = $stmt->fetch();

if (!$noticia) {
    set_mensagem('erro', 'Notícia não encontrada.');
    redirecionar('index.php');
}

$categorias = get_categorias($pdo);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape($noticia['titulo']) ?> - GGNews</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">

    <!-- Aplica o tema antes de renderizar para evitar flash -->
    <script>
        (function() {
            var t = localStorage.getItem('gg-theme') || 'light';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
</head>
<body class="min-h-screen flex">

    <!-- Overlay mobile -->
    <div id="sidebar-overlay" class="sidebar-overlay"></div>

    <!-- ══════════════════════════════════════════════════════
         SIDEBAR
    ══════════════════════════════════════════════════════ -->
    <aside id="sidebar" class="sidebar w-64 fixed h-full z-50 flex-col p-5 overflow-y-auto hidden md:flex">

        <!-- Logo -->
        <div class="mb-7">
            <a href="<?= BASE_URL ?>" class="sidebar-logo">
                <div class="logo-icon">🎮</div>
                <div>
                    <div class="logo-text"><span>GG</span>News</div>
                    <span class="logo-tag">Games & E-Sports</span>
                </div>
            </a>
        </div>

        <!-- Nav principal -->
        <div class="sidebar-section-label">Menu</div>
        <nav class="sidebar-nav mb-4">
            <a href="<?= BASE_URL ?>" class="nav-link">
                <span class="nav-icon">🏠</span>
                Início
            </a>
            <a href="<?= BASE_URL ?>pages/noticias/dashboard.php" class="nav-link">
                <span class="nav-icon">📋</span>
                Painel
            </a>

            <?php if (usuario_logado()): ?>
                <a href="<?= BASE_URL ?>pages/usuario/editar_usuario.php" class="nav-link">
                    <span class="nav-icon">👤</span>
                    Minha Conta
                </a>
                <a href="<?= BASE_URL ?>pages/auth/logout.php" class="nav-link nav-link-danger">
                    <span class="nav-icon">🚪</span>
                    Sair
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>pages/auth/login.php" class="nav-link">
                    <span class="nav-icon">🔐</span>
                    Login
                </a>
                <a href="<?= BASE_URL ?>pages/auth/cadastro.php" class="nav-link">
                    <span class="nav-icon">📝</span>
                    Cadastrar
                </a>
            <?php endif; ?>
        </nav>

        <!-- Categorias -->
        <?php if (!empty($categorias)): ?>
        <div class="sidebar-section-label">Categorias</div>
        <nav class="sidebar-categories mb-4">
            <?php foreach ($categorias as $cat): ?>
                <a href="<?= BASE_URL ?>pages/categorias/categoria.php?slug=<?= $cat['slug'] ?>"
                   class="nav-link <?= ($noticia['categoria_nome'] ?? '') === $cat['nome'] ? 'active' : '' ?>">
                    <span class="cat-icon"><?= $cat['icone'] ?></span>
                    <?= escape($cat['nome']) ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <?php endif; ?>

        <!-- Toggle de tema -->
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
    <div class="flex-1 md:ml-64 min-w-0">

        <!-- HEADER -->
        <header class="site-header sticky top-0 z-30 px-4 py-3">
            <div class="max-w-4xl mx-auto flex items-center justify-between">

                <div class="flex items-center gap-3">
                    <button id="menu-toggle"
                            class="md:hidden text-2xl p-1.5 rounded-lg transition"
                            style="color:var(--text-primary); background:transparent;"
                            aria-label="Abrir menu"
                            aria-expanded="false"
                            aria-controls="sidebar">
                        ☰
                    </button>
                    <a href="<?= BASE_URL ?>" class="flex items-center gap-2 md:hidden text-lg font-bold"
                       style="color:var(--text-primary); text-decoration:none;">
                        🎮 <span style="color:var(--accent)">GG</span>News
                    </a>
                </div>

                <nav class="flex items-center gap-3">
                    <?php if (usuario_logado()): ?>
                        <span class="hidden sm:inline text-sm" style="color:var(--text-secondary)">
                            Olá, <?= escape(get_usuario_nome()) ?>
                        </span>
                        <a href="<?= BASE_URL ?>pages/noticias/dashboard.php" class="btn-primary text-sm px-4 py-2">
                            Painel
                        </a>
                        <a href="<?= BASE_URL ?>pages/auth/logout.php"
                           style="color:var(--text-muted); font-size:.875rem; text-decoration:none;">Sair</a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>pages/auth/login.php"
                           style="color:var(--text-secondary); font-size:.875rem; text-decoration:none;">Login</a>
                        <a href="<?= BASE_URL ?>pages/auth/cadastro.php" class="btn-primary text-sm px-4 py-2">
                            Cadastrar
                        </a>
                    <?php endif; ?>
                </nav>

            </div>
        </header>

        <!-- CONTEÚDO DA NOTÍCIA -->
        <main class="max-w-4xl mx-auto px-4 py-8">

            <!-- Breadcrumb -->
            <a href="<?= BASE_URL ?>"
               class="inline-flex items-center gap-1 text-sm mb-6 transition"
               style="color:var(--accent); text-decoration:none;">
                ← Voltar para as notícias
            </a>

            <!-- Imagem de capa -->
            <?php if ($noticia['imagem']): ?>
                <div class="rounded-2xl overflow-hidden mb-8 h-[400px]">
                    <img src="<?= escape($noticia['imagem']) ?>"
                         alt="<?= escape($noticia['titulo']) ?>"
                         class="w-full h-full object-cover">
                </div>
            <?php endif; ?>

            <!-- Cabeçalho da notícia -->
            <h1 class="text-3xl md:text-4xl font-bold leading-tight"
                style="color:var(--text-primary)">
                <?= escape($noticia['titulo']) ?>
            </h1>

            <div class="flex flex-wrap items-center gap-4 mt-4 text-sm pb-6 mb-8"
                 style="border-bottom: 1px solid var(--border);">
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

            <!-- Corpo do artigo -->
            <article class="text-lg leading-relaxed whitespace-pre-line"
                     style="color:var(--text-secondary)">
                <?= escape($noticia['noticia']) ?>
            </article>

            <!-- Rodapé do artigo -->
            <div class="mt-12 pt-6 flex items-center justify-between flex-wrap gap-4"
                 style="border-top: 1px solid var(--border);">
                <a href="<?= BASE_URL ?>"
                   class="inline-flex items-center gap-2 text-sm font-medium transition"
                   style="color:var(--accent); text-decoration:none;">
                    ← Todas as notícias
                </a>
                <?php if (usuario_logado()): ?>
                    <a href="<?= BASE_URL ?>pages/noticias/editar_noticia.php?id=<?= $noticia['id'] ?>"
                       class="btn-primary text-sm px-4 py-2">
                        Editar notícia
                    </a>
                <?php endif; ?>
            </div>

        </main>

        <!-- FOOTER -->
        <footer class="mt-16 border-t" style="background:var(--bg-surface); border-color:var(--border)">
            <div class="max-w-7xl mx-auto px-4 py-8 text-center">
                <p class="text-sm" style="color:var(--text-muted)">
                    🎮 <span style="color:var(--accent); font-weight:700">GG</span>News
                    &copy; <?= date('Y') ?> — Portal de Notícias de Games e E-Sports
                </p>
            </div>
        </footer>

    </div><!-- /flex-1 -->

    <!-- ══════════════════════════════════════════════════════
         SCRIPTS
    ══════════════════════════════════════════════════════ -->
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

        /* ── Toggle de tema ── */
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

        /* ── Sidebar mobile ── */
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