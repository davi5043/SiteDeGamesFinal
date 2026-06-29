<?php
// =====================================================
// INDEX - CORRIGIDO E PADRONIZADO
// Arquivo: index.php (RAIZ DO PROJETO)
// =====================================================

// Inicia a sessão se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =====================================================
// DEFINIÇÃO DA BASE_URL
// =====================================================
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $script_name = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    
    if ($script_name == '/' || $script_name == '\\') {
        $base_path = '/';
    } else {
        $base_path = rtrim($script_name, '/') . '/';
    }
    
    define('BASE_URL', $protocol . $host . $base_path);
}

// =====================================================
// INCLUDES - CAMINHO CORRETO PARA A RAIZ
// =====================================================
// Como o index.php está na raiz, usamos 'includes/' diretamente
require_once __DIR__ . '/includes/funcoes.php';
require_once __DIR__ . '/includes/conexao.php';
require_once __DIR__ . '/includes/verifica_login.php';

// =====================================================
// VERIFICA SE A CONEXÃO EXISTE
// =====================================================
if (!isset($conn)) {
    // Tenta usar $pdo como fallback
    if (isset($pdo)) {
        $conn = $pdo;
    } else {
        die("Erro: Conexão com o banco de dados não disponível.");
    }
}

// =====================================================
// BUSCA CATEGORIAS
// =====================================================
$categorias = get_categorias($conn);

// =====================================================
// BUSCA NOTÍCIAS
// =====================================================
try {
    $stmt = $conn->prepare("
        SELECT n.*, u.nome AS autor_nome, u.foto AS autor_foto,
               c.nome AS categoria_nome, c.icone AS categoria_icone
        FROM noticias n
        INNER JOIN usuarios u ON n.autor = u.id
        LEFT JOIN categorias c ON n.categoria_id = c.id
        ORDER BY n.data DESC
    ");
    $stmt->execute();
    $noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $noticias = [];
    error_log("Erro ao buscar notícias: " . $e->getMessage());
}

// =====================================================
// ORGANIZAÇÃO ESTILO TELEJORNAL
// =====================================================
$manchete_principal = $noticias[0] ?? null;
$manchete_secundaria = $noticias[1] ?? null;
$manchetes_pequenas = array_slice($noticias, 2, 4);
$demais_noticias = array_slice($noticias, 6);

// =====================================================
// FUNÇÕES AUXILIARES (FALLBACK)
// =====================================================
if (!function_exists('resumo_texto')) {
    function resumo_texto($texto, $limite = 100) {
        $texto = strip_tags($texto);
        if (strlen($texto) > $limite) {
            $texto = substr($texto, 0, $limite) . '...';
        }
        return $texto;
    }
}

if (!function_exists('formatar_data')) {
    function formatar_data($data) {
        if (empty($data)) return '';
        return date('d/m/Y H:i', strtotime($data));
    }
}

if (!function_exists('escape')) {
    function escape($texto) {
        return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('usuario_logado')) {
    function usuario_logado() {
        return isset($_SESSION['usuario_id']);
    }
}

if (!function_exists('get_usuario_nome')) {
    function get_usuario_nome() {
        return $_SESSION['usuario_nome'] ?? 'Usuário';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
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
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
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
            transform: translateX(-100%);
        }

        .sidebar.mobile-open {
            transform: translateX(0);
        }

        @media (min-width: 768px) {
            .sidebar {
                transform: translateX(0);
            }
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            z-index: 40;
        }

        .sidebar-overlay.active {
            display: block;
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

        .nav-link {
            color: var(--text-secondary);
            transition: color 0.2s ease, background 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            text-decoration: none;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--text-primary);
            background: rgba(124, 58, 237, 0.1);
        }

        .sidebar-section-label {
            color: var(--text-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .theme-toggle-btn {
            color: var(--text-secondary);
            background: none;
            border: none;
            cursor: pointer;
            width: 100%;
        }

        .theme-toggle-btn:hover {
            color: var(--text-primary);
        }

        .toggle-track {
            background: var(--border);
            width: 2.5rem;
            height: 1.25rem;
            border-radius: 999px;
            position: relative;
            flex-shrink: 0;
        }

        [data-theme="dark"] .toggle-track {
            background: var(--accent);
        }

        .toggle-thumb {
            background: #fff;
            width: 1rem;
            height: 1rem;
            border-radius: 999px;
            position: absolute;
            top: 0.125rem;
            left: 0.125rem;
            transition: transform 0.2s ease;
        }

        [data-theme="light"] .toggle-thumb {
            transform: translateX(1.25rem);
        }

        .btn-primary {
            background: var(--accent);
            color: #fff;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            transition: background 0.2s ease;
            display: inline-block;
        }

        .btn-primary:hover {
            background: var(--accent-light);
            color: #fff;
        }

        .gradient-overlay {
            background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.3) 50%, transparent 100%);
            position: absolute;
            inset: 0;
        }

        [data-theme="light"] .gradient-overlay {
            background: linear-gradient(to top, rgba(255,255,255,0.92) 0%, rgba(255,255,255,0.4) 50%, transparent 100%);
        }

        .img-placeholder {
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            opacity: 0.3;
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .logo-text {
            color: var(--text-primary);
        }

        .sidebar-logo {
            text-decoration: none;
        }

        #menu-toggle {
            background: transparent;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-primary);
            padding: 0.375rem;
            border-radius: 0.5rem;
        }

        #menu-toggle:hover {
            background: rgba(124, 58, 237, 0.1);
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
    <aside id="sidebar" class="sidebar w-64 fixed h-full z-50 flex-col p-5 overflow-y-auto hidden md:flex" style="display: none;">

        <div class="mb-7">
            <a href="<?= BASE_URL ?>" class="sidebar-logo flex items-center gap-2">
                <div class="logo-icon text-3xl">🎮</div>
                <div>
                    <div class="logo-text text-xl font-bold">
                        <span style="color:var(--accent)">GG</span>News
                    </div>
                    <span class="logo-tag text-xs" style="color:var(--text-muted)">Games & E-Sports</span>
                </div>
            </a>
        </div>

        <div class="sidebar-section-label mb-2">Menu</div>
        <nav class="sidebar-nav mb-4">
            <a href="<?= BASE_URL ?>" class="nav-link active">
                <span>🏠</span> Início
            </a>
            <a href="<?= BASE_URL ?>pages/noticias/dashboard.php" class="nav-link">
                <span>📋</span> Painel
            </a>

            <?php if (usuario_logado()): ?>
                <a href="<?= BASE_URL ?>pages/usuario/editar_usuario.php" class="nav-link">
                    <span>👤</span> Minha Conta
                </a>
                <a href="<?= BASE_URL ?>pages/auth/logout.php" class="nav-link" style="color:#ef4444;">
                    <span>🚪</span> Sair
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>pages/auth/login.php" class="nav-link">
                    <span>🔐</span> Login
                </a>
                <a href="<?= BASE_URL ?>pages/auth/cadastro.php" class="nav-link">
                    <span>📝</span> Cadastrar
                </a>
            <?php endif; ?>
        </nav>

        <?php if (!empty($categorias)): ?>
        <div class="sidebar-section-label mb-2">Categorias</div>
        <nav class="sidebar-categories mb-4">
            <?php foreach ($categorias as $cat): ?>
                <a href="<?= BASE_URL ?>pages/categorias/categoria.php?slug=<?= $cat['slug'] ?? '' ?>" class="nav-link">
                    <span><?= $cat['icone'] ?? '📰' ?></span>
                    <?= escape($cat['nome'] ?? 'Sem categoria') ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <?php endif; ?>

        <div class="theme-toggle-wrap mt-auto pt-4 border-t" style="border-color:var(--border)">
            <button id="theme-toggle" class="theme-toggle-btn flex items-center gap-3 w-full px-3 py-2 rounded-lg" aria-label="Alternar tema">
                <div class="toggle-track">
                    <div class="toggle-thumb"></div>
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
                        <a href="<?= BASE_URL ?>pages/noticias/dashboard.php" class="btn-primary text-sm">
                            Painel
                        </a>
                        <a href="<?= BASE_URL ?>pages/auth/logout.php"
                           class="text-sm no-underline" style="color:var(--text-muted)">Sair</a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>pages/auth/login.php"
                           class="text-sm no-underline" style="color:var(--text-secondary)">Login</a>
                        <a href="<?= BASE_URL ?>pages/auth/cadastro.php" class="btn-primary text-sm">
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
            <!-- MANCHETE PRINCIPAL -->
            <section class="mb-6">
                <a href="<?= BASE_URL ?>pages/noticias/noticia.php?id=<?= $manchete_principal['id'] ?>" class="block group no-underline">
                    <div class="card-news rounded-2xl overflow-hidden">
                        <div class="grid grid-cols-1 lg:grid-cols-5">
                            <div class="lg:col-span-3 h-72 lg:h-[440px] relative overflow-hidden">
                                <?php if (!empty($manchete_principal['imagem'])): ?>
                                    <img src="<?= escape($manchete_principal['imagem']) ?>"
                                         alt="<?= escape($manchete_principal['titulo']) ?>"
                                         class="w-full h-full object-cover group-hover:scale-105 transition duration-700">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center img-placeholder">
                                        <span class="text-7xl opacity-50">🎮</span>
                                    </div>
                                <?php endif; ?>
                                <div class="gradient-overlay"></div>
                            </div>
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
                                    <?= escape(resumo_texto($manchete_principal['noticia'] ?? '', 200)) ?>
                                </p>
                                <div class="flex items-center gap-3 mt-4 text-sm" style="color:var(--text-muted)">
                                    <span>Por <?= escape($manchete_principal['autor_nome'] ?? 'Desconhecido') ?></span>
                                    <span>•</span>
                                    <span><?= formatar_data($manchete_principal['data'] ?? date('Y-m-d H:i:s')) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </section>
            <?php endif; ?>

            <!-- MANCHETE SECUNDÁRIA + PEQUENAS -->
            <?php if ($manchete_secundaria || !empty($manchetes_pequenas)): ?>
            <section class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

                <?php if ($manchete_secundaria): ?>
                <div class="lg:col-span-2">
                    <a href="<?= BASE_URL ?>pages/noticias/noticia.php?id=<?= $manchete_secundaria['id'] ?>" class="block group no-underline h-full">
                        <div class="card-news rounded-xl overflow-hidden h-full flex flex-col">
                            <div class="h-56 lg:h-64 overflow-hidden relative">
                                <?php if (!empty($manchete_secundaria['imagem'])): ?>
                                    <img src="<?= escape($manchete_secundaria['imagem']) ?>"
                                         alt="<?= escape($manchete_secundaria['titulo']) ?>"
                                         class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center img-placeholder">
                                        <span class="text-5xl opacity-50">🎮</span>
                                    </div>
                                <?php endif; ?>
                                <div class="gradient-overlay"></div>
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
                                    <?= escape(resumo_texto($manchete_secundaria['noticia'] ?? '', 140)) ?>
                                </p>
                                <div class="flex items-center justify-between mt-3 text-xs" style="color:var(--text-muted)">
                                    <span><?= escape($manchete_secundaria['autor_nome'] ?? 'Desconhecido') ?></span>
                                    <span><?= formatar_data($manchete_secundaria['data'] ?? date('Y-m-d H:i:s')) ?></span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endif; ?>

                <?php if (!empty($manchetes_pequenas)): ?>
                <div class="flex flex-col gap-4">
                    <?php foreach ($manchetes_pequenas as $noticia): ?>
                        <a href="<?= BASE_URL ?>pages/noticias/noticia.php?id=<?= $noticia['id'] ?>"
                           class="block group no-underline">
                            <div class="card-news rounded-xl overflow-hidden p-4 flex items-start gap-4 hover:bg-card-hover transition">
                                <div class="w-24 h-20 flex-shrink-0 overflow-hidden rounded-lg">
                                    <?php if (!empty($noticia['imagem'])): ?>
                                        <img src="<?= escape($noticia['imagem']) ?>"
                                             alt="<?= escape($noticia['titulo']) ?>"
                                             class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center img-placeholder">
                                            <span class="text-2xl">🎮</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <span class="badge-cat text-[0.6rem] inline-block mb-1">
                                        <?= $noticia['categoria_icone'] ?? '📰' ?>
                                        <?= escape($noticia['categoria_nome'] ?? '') ?>
                                    </span>
                                    <h4 class="text-sm font-semibold leading-snug group-hover:text-purple-400 transition line-clamp-2"
                                        style="color:var(--text-primary)">
                                        <?= escape($noticia['titulo']) ?>
                                    </h4>
                                    <span class="text-xs" style="color:var(--text-muted)"><?= formatar_data($noticia['data'] ?? date('Y-m-d H:i:s')) ?></span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

            </section>
            <?php endif; ?>

            <!-- ÚLTIMAS NOTÍCIAS -->
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
                        <a href="<?= BASE_URL ?>pages/noticias/nova_noticia.php" class="btn-primary mt-4 inline-block">
                            Publicar minha primeira notícia →
                        </a>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($demais_noticias as $noticia): ?>
                            <a href="<?= BASE_URL ?>pages/noticias/noticia.php?id=<?= $noticia['id'] ?>"
                               class="block group no-underline">
                                <div class="card-news rounded-xl overflow-hidden hover:shadow-lg transition-all h-full flex flex-col">
                                    <div class="h-48 overflow-hidden relative">
                                        <?php if (!empty($noticia['imagem'])): ?>
                                            <img src="<?= escape($noticia['imagem']) ?>"
                                                 alt="<?= escape($noticia['titulo']) ?>"
                                                 class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                                        <?php else: ?>
                                            <div class="w-full h-full flex items-center justify-center img-placeholder">
                                                <span class="text-4xl opacity-50">🎮</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
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
                                            <?= escape(resumo_texto($noticia['noticia'] ?? '', 100)) ?>
                                        </p>
                                        <div class="flex items-center justify-between mt-4 text-xs" style="color:var(--text-muted)">
                                            <span>Por <?= escape($noticia['autor_nome'] ?? 'Desconhecido') ?></span>
                                            <span><?= formatar_data($noticia['data'] ?? date('Y-m-d H:i:s')) ?></span>
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

    </div>

    <!-- SCRIPTS -->
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

        var savedTheme = localStorage.getItem('gg-theme') || 'dark';
        applyTheme(savedTheme);

        if (btn) {
            btn.addEventListener('click', function () {
                var current = html.getAttribute('data-theme');
                applyTheme(current === 'dark' ? 'light' : 'dark');
            });
        }

        function openSidebar() {
            if (sidebar) {
                sidebar.classList.add('mobile-open');
                sidebar.style.display = 'flex';
            }
            if (overlay) overlay.classList.add('active');
            if (menuBtn) menuBtn.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            if (sidebar) {
                sidebar.classList.remove('mobile-open');
                if (window.innerWidth < 768) {
                    sidebar.style.display = 'none';
                }
            }
            if (overlay) overlay.classList.remove('active');
            if (menuBtn) menuBtn.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }

        function isMobile() {
            return window.innerWidth < 768;
        }

        if (isMobile() && sidebar) {
            sidebar.style.display = 'none';
        }

        if (menuBtn) {
            menuBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                if (sidebar && sidebar.classList.contains('mobile-open')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });
        }

        if (overlay) {
            overlay.addEventListener('click', closeSidebar);
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && isMobile()) {
                closeSidebar();
            }
        });

        window.addEventListener('resize', function () {
            if (!isMobile() && sidebar) {
                closeSidebar();
                sidebar.style.display = 'flex';
                document.body.style.overflow = '';
            } else if (isMobile() && sidebar && !sidebar.classList.contains('mobile-open')) {
                sidebar.style.display = 'none';
            }
        });

    })();
    </script>

</body>
</html>