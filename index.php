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

// Organização estilo telejornal
$manchete_principal = $noticias[0] ?? null;
$manchete_secundaria = $noticias[1] ?? null;
$manchetes_pequenas = array_slice($noticias, 2, 4);
$demais_noticias = array_slice($noticias, 6);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GGNews - Portal de Games & E-Sports</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">

    <style>
        /* =============================================
           CORREÇÃO DO TEMA CLARO/ESCURO
        ============================================= */
        :root {
            --bg-body: #0c0c10;
            --bg-card: #1a1a2e;
            --bg-card-hover: #252540;
            --bg-header: #121218;
            --bg-surface: #1a1a22;
            --text-primary: #eeeaf8;
            --text-secondary: #b8b5d0;
            --text-muted: #5e5c76;
            --border: #252535;
            --accent: #7c3aed;
            --accent-light: #6d28d9;
            --shadow: 0 4px 20px rgba(0,0,0,0.4);
        }

        [data-theme="light"] {
            --bg-body: #f5f3f0;
            --bg-card: #ffffff;
            --bg-card-hover: #f0edf0;
            --bg-header: #ffffff;
            --bg-surface: #f8f6f4;
            --text-primary: #1a1a1a;
            --text-secondary: #4a4a5a;
            --text-muted: #8888a0;
            --border: #e5e0db;
            --accent: #7c3aed;
            --accent-light: #6d28d9;
            --shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        body {
            background: var(--bg-body);
            color: var(--text-primary);
            transition: background 0.3s ease, color 0.3s ease;
        }

        .site-header {
            background: var(--bg-header);
            border-color: var(--border);
            transition: background 0.3s ease;
        }

        .sidebar {
            background: var(--bg-card);
            border-right: 1px solid var(--border);
            transition: background 0.3s ease;
        }

        .card-news {
            background: var(--bg-card);
            border: 1px solid var(--border);
            transition: background 0.3s ease, border-color 0.3s ease, transform 0.2s ease;
        }

        .card-news:hover {
            background: var(--bg-card-hover);
            transform: translateY(-4px);
            box-shadow: var(--shadow);
        }

        .badge-cat {
            display: inline-block;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            background: var(--accent);
            color: #fff;
        }

        .text-primary { color: var(--text-primary); }
        .text-secondary { color: var(--text-secondary); }
        .text-muted { color: var(--text-muted); }
        .border-custom { border-color: var(--border); }

        /* Sidebar estilos */
        .nav-link {
            color: var(--text-secondary);
            transition: color 0.2s ease, background 0.2s ease;
        }
        .nav-link:hover, .nav-link.active {
            color: var(--text-primary);
            background: rgba(124, 58, 237, 0.1);
        }
        .sidebar-section-label {
            color: var(--text-muted);
        }

        /* Tema toggle */
        .theme-toggle-btn {
            color: var(--text-secondary);
        }
        .theme-toggle-btn:hover {
            color: var(--text-primary);
        }
        .toggle-track {
            background: var(--border);
        }
        [data-theme="dark"] .toggle-track.is-dark {
            background: var(--accent);
        }
        .toggle-thumb {
            background: #fff;
        }

        .btn-primary {
            background: var(--accent);
            color: #fff;
        }
        .btn-primary:hover {
            background: var(--accent-light);
        }

        /* Gradiente overlay para imagens */
        .gradient-overlay {
            background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.3) 50%, transparent 100%);
        }
        [data-theme="light"] .gradient-overlay {
            background: linear-gradient(to top, rgba(255,255,255,0.92) 0%, rgba(255,255,255,0.4) 50%, transparent 100%);
        }

        /* Placeholder de imagem */
        .img-placeholder {
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            opacity: 0.3;
        }
    </style>

    <script>
        (function() {
            var t = localStorage.getItem('gg-theme') || 'dark';
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

        <div class="mb-7">
            <a href="<?= BASE_URL ?>" class="sidebar-logo flex items-center gap-2 no-underline">
                <div class="logo-icon text-3xl">🎮</div>
                <div>
                    <div class="logo-text text-xl font-bold" style="color:var(--text-primary)">
                        <span style="color:var(--accent)">GG</span>News
                    </div>
                    <span class="logo-tag text-xs" style="color:var(--text-muted)">Games & E-Sports</span>
                </div>
            </a>
        </div>

        <div class="sidebar-section-label text-xs uppercase tracking-wider mb-2" style="color:var(--text-muted)">Menu</div>
        <nav class="sidebar-nav mb-4">
            <a href="<?= BASE_URL ?>" class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg no-underline active">
                <span>🏠</span> Início
            </a>
            <a href="<?= BASE_URL ?>pages/noticias/dashboard.php" class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg no-underline">
                <span>📋</span> Painel
            </a>

            <?php if (usuario_logado()): ?>
                <a href="<?= BASE_URL ?>pages/usuario/editar_usuario.php" class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg no-underline">
                    <span>👤</span> Minha Conta
                </a>
                <a href="<?= BASE_URL ?>pages/auth/logout.php" class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg no-underline" style="color:#ef4444;">
                    <span>🚪</span> Sair
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>pages/auth/login.php" class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg no-underline">
                    <span>🔐</span> Login
                </a>
                <a href="<?= BASE_URL ?>pages/auth/cadastro.php" class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg no-underline">
                    <span>📝</span> Cadastrar
                </a>
            <?php endif; ?>
        </nav>

        <?php if (!empty($categorias)): ?>
        <div class="sidebar-section-label text-xs uppercase tracking-wider mb-2" style="color:var(--text-muted)">Categorias</div>
        <nav class="sidebar-categories mb-4">
            <?php foreach ($categorias as $cat): ?>
                <a href="<?= BASE_URL ?>pages/categorias/categoria.php?slug=<?= $cat['slug'] ?>" class="nav-link flex items-center gap-3 px-3 py-2 rounded-lg no-underline">
                    <span><?= $cat['icone'] ?></span>
                    <?= escape($cat['nome']) ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <?php endif; ?>

        <div class="theme-toggle-wrap mt-auto pt-4 border-t" style="border-color:var(--border)">
            <button id="theme-toggle" class="theme-toggle-btn flex items-center gap-3 w-full px-3 py-2 rounded-lg no-underline" aria-label="Alternar tema">
                <div class="toggle-track w-10 h-5 rounded-full relative flex-shrink-0">
                    <div class="toggle-thumb w-4 h-4 rounded-full absolute top-0.5 left-0.5 transition-transform"></div>
                </div>
                <span class="toggle-icons" id="theme-icon">🌙</span>
                <span class="toggle-label text-sm" id="theme-label">Modo Escuro</span>
            </button>
            <p class="sidebar-footer-tag text-xs mt-3" style="color:var(--text-muted)">GGNews &copy; <?= date('Y') ?></p>
        </div>

    </aside>

    <!-- ══════════════════════════════════════════════════════
         CONTEÚDO PRINCIPAL
    ══════════════════════════════════════════════════════ -->
    <div class="flex-1 md:ml-64 min-w-0">

        <!-- HEADER -->
        <header class="site-header sticky top-0 z-30 px-4 py-3 border-b" style="background:var(--bg-header); border-color:var(--border)">
            <div class="max-w-7xl mx-auto flex items-center justify-between">

                <div class="flex items-center gap-3">
                    <button id="menu-toggle"
                            class="md:hidden text-2xl p-1.5 rounded-lg transition"
                            style="color:var(--text-primary); background:transparent;"
                            aria-label="Abrir menu">
                        ☰
                    </button>
                    <a href="<?= BASE_URL ?>" class="flex items-center gap-2 md:hidden text-lg font-bold no-underline" style="color:var(--text-primary)">
                        🎮 <span style="color:var(--accent)">GG</span>News
                    </a>
                </div>

                <nav class="flex items-center gap-3">
                    <?php if (usuario_logado()): ?>
                        <span class="hidden sm:inline text-sm" style="color:var(--text-secondary)">
                            Olá, <?= escape(get_usuario_nome()) ?>
                        </span>
                        <a href="<?= BASE_URL ?>pages/noticias/dashboard.php" class="btn-primary text-sm px-4 py-2 rounded-lg no-underline">
                            Painel
                        </a>
                        <a href="<?= BASE_URL ?>pages/auth/logout.php"
                           class="text-sm no-underline" style="color:var(--text-muted)">Sair</a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>pages/auth/login.php"
                           class="text-sm no-underline" style="color:var(--text-secondary)">Login</a>
                        <a href="<?= BASE_URL ?>pages/auth/cadastro.php" class="btn-primary text-sm px-4 py-2 rounded-lg no-underline">
                            Cadastrar
                        </a>
                    <?php endif; ?>
                </nav>

            </div>
        </header>

        <!-- ══════════════════════════════════════════════════════
             MAIN - LAYOUT ESTILO TELEJORNAL
        ══════════════════════════════════════════════════════ -->
        <main class="max-w-7xl mx-auto px-4 py-8">

            <?php if ($manchete_principal): ?>
            <!-- =============================================
                 MANCHETE PRINCIPAL (GRANDE DESTAQUE)
            ============================================= -->
            <section class="mb-6">
                <a href="<?= BASE_URL ?>pages/noticias/noticia.php?id=<?= $manchete_principal['id'] ?>" class="block group no-underline">
                    <div class="card-news rounded-2xl overflow-hidden">
                        <div class="grid grid-cols-1 lg:grid-cols-5">
                            <!-- Imagem -->
                            <div class="lg:col-span-3 h-72 lg:h-[440px] relative overflow-hidden">
                                <?php if ($manchete_principal['imagem']): ?>
                                    <img src="<?= escape($manchete_principal['imagem']) ?>?w=1200&q=80"
                                         alt="<?= escape($manchete_principal['titulo']) ?>"
                                         class="w-full h-full object-cover group-hover:scale-105 transition duration-700">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center img-placeholder">
                                        <span class="text-7xl opacity-50">🎮</span>
                                    </div>
                                <?php endif; ?>
                                <div class="gradient-overlay absolute inset-0"></div>
                            </div>

                            <!-- Texto -->
                            <div class="lg:col-span-2 p-6 lg:p-8 flex flex-col justify-center">
                                <span class="badge-cat inline-block mb-3">
                                    <?= $manchete_principal['categoria_icone'] ?? '📰' ?>
                                    <?= escape($manchete_principal['categoria_nome'] ?? 'Destaque') ?>
                                </span>
                                <h2 class="text-2xl md:text-3xl lg:text-4xl font-bold leading-tight group-hover:text-purple-400 transition"
                                    style="color:var(--text-primary)">
                                    <?= escape($manchete_principal['titulo']) ?>
                                </h2>
                                <p class="mt-3 text-base lg:text-lg leading-relaxed" style="color:var(--text-secondary)">
                                    <?= escape(resumo_texto($manchete_principal['noticia'], 200)) ?>
                                </p>
                                <div class="flex items-center gap-3 mt-4 text-sm" style="color:var(--text-muted)">
                                    <span>Por <?= escape($manchete_principal['autor_nome']) ?></span>
                                    <span>•</span>
                                    <span><?= formatar_data($manchete_principal['data']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </section>
            <?php endif; ?>

            <!-- =============================================
                 BLOCO: MANCHETE SECUNDÁRIA + PEQUENAS
            ============================================= -->
            <?php if ($manchete_secundaria || !empty($manchetes_pequenas)): ?>
            <section class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

                <!-- Manchete Secundária (ocupa 2 colunas) -->
                <?php if ($manchete_secundaria): ?>
                <div class="lg:col-span-2">
                    <a href="<?= BASE_URL ?>pages/noticias/noticia.php?id=<?= $manchete_secundaria['id'] ?>" class="block group no-underline h-full">
                        <div class="card-news rounded-xl overflow-hidden h-full flex flex-col">
                            <div class="h-56 lg:h-64 overflow-hidden relative">
                                <?php if ($manchete_secundaria['imagem']): ?>
                                    <img src="<?= escape($manchete_secundaria['imagem']) ?>?w=800&q=80"
                                         alt="<?= escape($manchete_secundaria['titulo']) ?>"
                                         class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center img-placeholder">
                                        <span class="text-5xl opacity-50">🎮</span>
                                    </div>
                                <?php endif; ?>
                                <div class="gradient-overlay absolute inset-0"></div>
                            </div>
                            <div class="p-5 flex-1 flex flex-col">
                                <span class="badge-cat text-xs inline-block mb-2 self-start">
                                    <?= $manchete_secundaria['categoria_icone'] ?? '📰' ?>
                                    <?= escape($manchete_secundaria['categoria_nome'] ?? '') ?>
                                </span>
                                <h3 class="text-xl font-bold group-hover:text-purple-400 transition" style="color:var(--text-primary)">
                                    <?= escape($manchete_secundaria['titulo']) ?>
                                </h3>
                                <p class="text-sm mt-2 flex-1" style="color:var(--text-secondary)">
                                    <?= escape(resumo_texto($manchete_secundaria['noticia'], 140)) ?>
                                </p>
                                <div class="flex items-center justify-between mt-3 text-xs" style="color:var(--text-muted)">
                                    <span><?= escape($manchete_secundaria['autor_nome']) ?></span>
                                    <span><?= formatar_data($manchete_secundaria['data']) ?></span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endif; ?>

                <!-- Manchetes Pequenas (ocupa 1 coluna) -->
                <?php if (!empty($manchetes_pequenas)): ?>
                <div class="flex flex-col gap-4">
                    <?php foreach ($manchetes_pequenas as $noticia): ?>
                        <a href="<?= BASE_URL ?>pages/noticias/noticia.php?id=<?= $noticia['id'] ?>"
                           class="block group no-underline">
                            <div class="card-news rounded-xl overflow-hidden p-4 flex items-start gap-4 hover:bg-card-hover transition">
                                <!-- Mini imagem -->
                                <div class="w-24 h-20 flex-shrink-0 overflow-hidden rounded-lg">
                                    <?php if ($noticia['imagem']): ?>
                                        <img src="<?= escape($noticia['imagem']) ?>?w=200&q=80"
                                             alt="<?= escape($noticia['titulo']) ?>"
                                             class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center img-placeholder">
                                            <span class="text-2xl">🎮</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <!-- Texto -->
                                <div class="flex-1 min-w-0">
                                    <span class="badge-cat text-[0.6rem] inline-block mb-1">
                                        <?= $noticia['categoria_icone'] ?? '📰' ?>
                                        <?= escape($noticia['categoria_nome'] ?? '') ?>
                                    </span>
                                    <h4 class="text-sm font-semibold leading-snug group-hover:text-purple-400 transition line-clamp-2"
                                        style="color:var(--text-primary)">
                                        <?= escape($noticia['titulo']) ?>
                                    </h4>
                                    <span class="text-xs" style="color:var(--text-muted)"><?= formatar_data($noticia['data']) ?></span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

            </section>
            <?php endif; ?>

            <!-- =============================================
                 GRADE DE NOTÍCIAS (ÚLTIMAS)
            ============================================= -->
            <section>
                <div class="flex items-center gap-3 mb-6 pb-3 border-b" style="border-color:var(--border)">
                    <h2 class="text-2xl font-bold" style="color:var(--text-primary)">📰 Últimas Notícias</h2>
                    <span class="text-sm" style="color:var(--text-muted)">(<?= count($demais_noticias) ?> notícias)</span>
                </div>

                <?php if (empty($demais_noticias) && !$manchete_principal): ?>
                    <div class="text-center py-16" style="color:var(--text-muted)">
                        <span class="text-6xl block mb-4">📭</span>
                        <p class="text-lg">Nenhuma notícia publicada ainda.</p>
                        <p class="text-sm">Seja o primeiro a compartilhar uma notícia!</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($demais_noticias as $noticia): ?>
                            <a href="<?= BASE_URL ?>pages/noticias/noticia.php?id=<?= $noticia['id'] ?>"
                               class="block group no-underline">
                                <div class="card-news rounded-xl overflow-hidden hover:shadow-lg transition-all h-full flex flex-col">
                                    <!-- Imagem -->
                                    <div class="h-48 overflow-hidden relative">
                                        <?php if ($noticia['imagem']): ?>
                                            <img src="<?= escape($noticia['imagem']) ?>?w=600&q=80"
                                                 alt="<?= escape($noticia['titulo']) ?>"
                                                 class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                                        <?php else: ?>
                                            <div class="w-full h-full flex items-center justify-center img-placeholder">
                                                <span class="text-4xl opacity-50">🎮</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Texto -->
                                    <div class="p-5 flex-1 flex flex-col">
                                        <span class="badge-cat text-xs inline-block mb-2 self-start">
                                            <?= $noticia['categoria_icone'] ?? '📰' ?>
                                            <?= escape($noticia['categoria_nome'] ?? 'Geral') ?>
                                        </span>
                                        <h3 class="text-base font-bold leading-snug group-hover:text-purple-400 transition line-clamp-2"
                                            style="color:var(--text-primary)">
                                            <?= escape($noticia['titulo']) ?>
                                        </h3>
                                        <p class="text-sm mt-2 line-clamp-3 flex-1" style="color:var(--text-secondary)">
                                            <?= escape(resumo_texto($noticia['noticia'], 100)) ?>
                                        </p>
                                        <div class="flex items-center justify-between mt-4 text-xs" style="color:var(--text-muted)">
                                            <span>Por <?= escape($noticia['autor_nome']) ?></span>
                                            <span><?= formatar_data($noticia['data']) ?></span>
                                        </div>
                                    </div>
                                </div>
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
        var html = document.documentElement;
        var btn = document.getElementById('theme-toggle');
        var label = document.getElementById('theme-label');
        var icon = document.getElementById('theme-icon');
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('sidebar-overlay');
        var menuBtn = document.getElementById('menu-toggle');

        function applyTheme(theme) {
            html.setAttribute('data-theme', theme);
            localStorage.setItem('gg-theme', theme);

            if (theme === 'dark') {
                if (label) label.textContent = 'Modo Claro';
                if (icon) icon.textContent = '☀️';
            } else {
                if (label) label.textContent = 'Modo Escuro';
                if (icon) icon.textContent = '🌙';
            }
        }

        // Aplica o tema salvo
        applyTheme(html.getAttribute('data-theme') || 'dark');

        btn && btn.addEventListener('click', function () {
            var current = html.getAttribute('data-theme');
            applyTheme(current === 'dark' ? 'light' : 'dark');
        });

        // Sidebar mobile
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