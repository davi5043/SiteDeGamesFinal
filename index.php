<?php
require_once __DIR__ . '/includes/funcoes.php';
require_once __DIR__ . '/includes/conexao.php';

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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GGNews - Portal de Games & E-Sports</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">

    <!-- Aplica o tema ANTES de renderizar para evitar flash -->
    <script>
        (function() {
            var t = localStorage.getItem('gg-theme') || 'light';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
</head>
<body class="min-h-screen flex">

    <!-- Overlay mobile (clica fora para fechar a sidebar) -->
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
            <a href="<?= BASE_URL ?>" class="nav-link active">
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
                <a href="<?= BASE_URL ?>pages/categorias/categoria.php?slug=<?= $cat['slug'] ?>" class="nav-link">
                    <span class="cat-icon"><?= $cat['icone'] ?></span>
                    <?= escape($cat['nome']) ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <?php endif; ?>

        <!-- Toggle de tema -->
        <div class="theme-toggle-wrap">
            <button id="theme-toggle" class="theme-toggle-btn" aria-label="Alternar tema" title="Alternar tema claro/escuro">
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
            <div class="max-w-7xl mx-auto flex items-center justify-between">

                <div class="flex items-center gap-3">
                    <!-- Botão hambúrguer — visível só no mobile -->
                    <button id="menu-toggle"
                            class="md:hidden text-2xl p-1.5 rounded-lg transition"
                            style="color:var(--text-primary); background:transparent;"
                            aria-label="Abrir menu"
                            aria-expanded="false"
                            aria-controls="sidebar">
                        ☰
                    </button>
                    <!-- Logo mobile -->
                    <a href="<?= BASE_URL ?>" class="flex items-center gap-2 md:hidden text-lg font-bold" style="color:var(--text-primary); text-decoration:none;">
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

        <main class="max-w-7xl mx-auto px-4 py-8">

            <!-- HERO / DESTAQUE -->
            <?php if ($destaque): ?>
            <section class="mb-12">
                <a href="<?= BASE_URL ?>pages/noticias/noticia.php?id=<?= $destaque['id'] ?>" class="block group">
                    <div class="hero-news relative rounded-2xl overflow-hidden h-[400px] flex items-end">

                        <?php if ($destaque['imagem']): ?>
                            <img src="<?= escape($destaque['imagem']) ?>?w=1400&q=80"
                                 alt="<?= escape($destaque['titulo']) ?>"
                                 class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        <?php else: ?>
                            <div class="absolute inset-0" style="background: linear-gradient(135deg, var(--accent-light), var(--bg-elevated));"></div>
                        <?php endif; ?>

                        <!-- Gradiente sobre a imagem -->
                        <div class="absolute inset-0"
                             style="background: linear-gradient(to top, rgba(255,255,255,0.93) 0%, rgba(255,255,255,0.4) 50%, transparent 100%);">
                        </div>

                        <div class="relative p-8 w-full">
                            <span class="badge-cat">
                                <?= $destaque['categoria_icone'] ?? '📰' ?>
                                <?= escape($destaque['categoria_nome'] ?? 'Destaque') ?>
                            </span>
                            <h2 class="text-3xl md:text-4xl font-bold mt-4 group-hover:text-purple-600 transition"
                                style="color:var(--text-primary)">
                                <?= escape($destaque['titulo']) ?>
                            </h2>
                            <p class="mt-3 text-lg max-w-3xl" style="color:var(--text-secondary)">
                                <?= escape(resumo_texto($destaque['noticia'], 200)) ?>
                            </p>
                            <div class="flex items-center gap-4 mt-4 text-sm" style="color:var(--text-muted)">
                                <span>Por <?= escape($destaque['autor_nome']) ?></span>
                                <span>•</span>
                                <span><?= formatar_data($destaque['data']) ?></span>
                            </div>
                        </div>

                    </div>
                </a>
            </section>
            <?php endif; ?>

            <!-- GRID DE NOTÍCIAS -->
            <section>
                <h2 class="text-2xl font-bold mb-6 pl-4"
                    style="color:var(--text-primary); border-left: 4px solid var(--accent);">
                    Últimas Notícias
                </h2>

                <?php if (empty($demais) && !$destaque): ?>
                    <p class="text-center py-12" style="color:var(--text-muted)">
                        Nenhuma notícia publicada ainda.
                    </p>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($demais as $noticia): ?>
                            <a href="<?= BASE_URL ?>pages/noticias/noticia.php?id=<?= $noticia['id'] ?>"
                               class="group block" style="text-decoration:none;">
                                <article class="card-news h-full flex flex-col">

                                    <!-- Imagem -->
                                    <div class="h-48 overflow-hidden">
                                        <?php if ($noticia['imagem']): ?>
                                            <img src="<?= escape($noticia['imagem']) ?>?w=800&q=80"
                                                 alt="<?= escape($noticia['titulo']) ?>"
                                                 class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                                        <?php else: ?>
                                            <div class="w-full h-full flex items-center justify-center"
                                                 style="background: linear-gradient(135deg, var(--accent-light), var(--bg-elevated));">
                                                <span class="text-4xl">🎮</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Conteúdo -->
                                    <div class="card-body flex flex-col flex-1">
                                        <?php if ($noticia['categoria_nome']): ?>
                                            <span class="badge-cat mb-2 self-start">
                                                <?= $noticia['categoria_icone'] ?>
                                                <?= escape($noticia['categoria_nome']) ?>
                                            </span>
                                        <?php endif; ?>

                                        <h3 class="text-base font-bold line-clamp-2 group-hover:text-purple-500 transition"
                                            style="color:var(--text-primary)">
                                            <?= escape($noticia['titulo']) ?>
                                        </h3>

                                        <p class="text-sm mt-2 line-clamp-3 flex-1"
                                           style="color:var(--text-secondary)">
                                            <?= escape(resumo_texto($noticia['noticia'], 120)) ?>
                                        </p>

                                        <div class="flex items-center justify-between mt-4 text-xs"
                                             style="color:var(--text-muted)">
                                            <span><?= escape($noticia['autor_nome']) ?></span>
                                            <span><?= formatar_data($noticia['data']) ?></span>
                                        </div>
                                    </div>

                                </article>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

        </main>

        <!-- FOOTER -->
        <footer class="mt-16 border-t" style="background:var(--bg-surface); border-color:var(--border)">
            <div class="max-w-7xl mx-auto px-4 py-8 text-center">
                <p class="text-sm" style="color:var(--text-muted)">
                    🎮 <span style="color:var(--accent); font-weight:700">GG</span>News
                    &copy; <?= date('Y') ?> — Portal de Notícias de Games e E-Sports
                </p>
                <p class="text-xs mt-2" style="color:var(--text-muted)">
                    Projeto acadêmico desenvolvido com PHP, MySQL e Tailwind CSS
                </p>
            </div>
        </footer>

    </div><!-- /flex-1 -->

    <!-- ══════════════════════════════════════════════════════
         SCRIPTS
    ══════════════════════════════════════════════════════ -->
    <script>
    (function () {

        /* ── Referências ─────────────────────────────────── */
        var html     = document.documentElement;
        var btn      = document.getElementById('theme-toggle');
        var track    = document.getElementById('toggle-track');
        var label    = document.getElementById('theme-label');
        var icon     = document.getElementById('theme-icon');
        var sidebar  = document.getElementById('sidebar');
        var overlay  = document.getElementById('sidebar-overlay');
        var menuBtn  = document.getElementById('menu-toggle');

        /* ── Toggle de tema ──────────────────────────────── */
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

        /* Sincroniza com o tema já aplicado no <head> */
        applyTheme(html.getAttribute('data-theme') || 'light');

        btn && btn.addEventListener('click', function () {
            var current = html.getAttribute('data-theme');
            applyTheme(current === 'dark' ? 'light' : 'dark');
        });

        /* ── Sidebar mobile ──────────────────────────────── */
        function openSidebar() {
            sidebar.classList.add('mobile-open');
            overlay.classList.add('active');
            menuBtn && menuBtn.setAttribute('aria-expanded', 'true');
            /* Impede o scroll do body enquanto a sidebar está aberta */
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

        /* Botão hambúrguer */
        menuBtn && menuBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (sidebar.classList.contains('mobile-open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });

        /* Overlay (clique fora fecha) */
        overlay && overlay.addEventListener('click', closeSidebar);

        /* Tecla Escape fecha a sidebar */
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && isMobile()) {
                closeSidebar();
            }
        });

        /* Ao redimensionar para desktop, limpa estado mobile */
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