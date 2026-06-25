<?php
// =====================================================
// DASHBOARD (PAINEL DO USUÁRIO)
// Arquivo: pages/noticias/dashboard.php
// =====================================================

require_once __DIR__ . '/../../includes/verifica_login.php';
require_once __DIR__ . '/../../includes/conexao.php';

$stmt = $pdo->prepare("SELECT * FROM noticias WHERE autor = ? ORDER BY data DESC");
$stmt->execute([get_usuario_id()]);
$minhas_noticias = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - GGNews</title>
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
<body class="min-h-screen" style="background:var(--bg-base); color:var(--text-primary)">

    <!-- HEADER -->
    <header class="site-header sticky top-0 z-30 px-4 py-3">
        <div class="max-w-7xl mx-auto flex items-center justify-between">

            <!-- Logo -->
            <a href="<?= BASE_URL ?>" class="flex items-center gap-2 text-xl font-bold"
               style="color:var(--text-primary); text-decoration:none;">
                🎮 <span style="color:var(--accent)">GG</span>News
            </a>

            <!-- Nav direita -->
            <nav class="flex items-center gap-4">

                <!-- Toggle de tema compacto -->
                <button id="theme-toggle"
                        class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium transition"
                        style="background:var(--bg-elevated); color:var(--text-secondary); border:1px solid var(--border); cursor:pointer;"
                        aria-label="Alternar tema">
                    <div class="toggle-track" id="toggle-track" style="width:30px; height:18px;">
                        <div class="toggle-thumb" style="width:12px; height:12px; top:3px; left:3px;"></div>
                    </div>
                    <span id="theme-icon">🌙</span>
                </button>

                <span class="hidden sm:inline text-sm" style="color:var(--text-secondary)">
                    <?= escape(get_usuario_nome()) ?>
                </span>
                <a href="<?= BASE_URL ?>pages/usuario/editar_usuario.php"
                   class="text-sm transition"
                   style="color:var(--text-muted); text-decoration:none;">Minha Conta</a>
                <a href="<?= BASE_URL ?>pages/auth/logout.php"
                   class="text-sm transition"
                   style="color:var(--text-muted); text-decoration:none;">Sair</a>
            </nav>

        </div>
    </header>

    <!-- MAIN -->
    <main class="max-w-7xl mx-auto px-4 py-8">

        <!-- Mensagem de feedback -->
        <?php $msg = get_mensagem(); if ($msg): ?>
            <div class="px-4 py-3 rounded-lg mb-6 border text-sm"
                 style="<?= $msg['tipo'] === 'sucesso'
                    ? 'background:rgba(16,185,129,0.1); border-color:#10b981; color:#059669;'
                    : 'background:rgba(239,68,68,0.1); border-color:#ef4444; color:#dc2626;' ?>">
                <?= escape($msg['texto']) ?>
            </div>
        <?php endif; ?>

        <!-- Cabeçalho do painel -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold" style="color:var(--text-primary)">Meu Painel</h1>
                <p class="mt-1 text-sm" style="color:var(--text-muted)">Gerencie suas notícias publicadas</p>
            </div>
            <a href="<?= BASE_URL ?>pages/noticias/nova_noticia.php"
               class="btn-primary mt-4 sm:mt-0 px-6 py-3">
                + Nova Notícia
            </a>
        </div>

        <!-- Tabela de notícias ou estado vazio -->
        <?php if (empty($minhas_noticias)): ?>
            <div class="rounded-xl p-12 text-center"
                 style="background:var(--bg-surface); border:1px solid var(--border);">
                <p class="text-lg" style="color:var(--text-muted)">
                    Você ainda não publicou nenhuma notícia.
                </p>
                <a href="<?= BASE_URL ?>pages/noticias/nova_noticia.php"
                   class="inline-block mt-3 text-sm font-medium transition"
                   style="color:var(--accent); text-decoration:none;">
                    Publicar minha primeira notícia →
                </a>
            </div>
        <?php else: ?>
            <div class="table-news">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="text-left">Título</th>
                            <th class="text-left hidden md:table-cell">Data</th>
                            <th class="text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($minhas_noticias as $noticia): ?>
                            <tr>
                                <td>
                                    <a href="<?= BASE_URL ?>pages/noticias/noticia.php?id=<?= $noticia['id'] ?>"
                                       class="font-medium transition"
                                       style="color:var(--text-primary); text-decoration:none;"
                                       onmouseover="this.style.color='var(--accent)'"
                                       onmouseout="this.style.color='var(--text-primary)'">
                                        <?= escape($noticia['titulo']) ?>
                                    </a>
                                </td>
                                <td class="hidden md:table-cell" style="color:var(--text-muted); font-size:.875rem;">
                                    <?= formatar_data($noticia['data']) ?>
                                </td>
                                <td class="text-right">
                                    <a href="<?= BASE_URL ?>pages/noticias/editar_noticia.php?id=<?= $noticia['id'] ?>"
                                       class="text-sm mr-4 transition"
                                       style="color:#3b82f6; text-decoration:none;"
                                       onmouseover="this.style.color='#60a5fa'"
                                       onmouseout="this.style.color='#3b82f6'">
                                        Editar
                                    </a>
                                    <a href="<?= BASE_URL ?>pages/noticias/excluir_noticia.php?id=<?= $noticia['id'] ?>"
                                       class="text-sm transition"
                                       style="color:#ef4444; text-decoration:none;"
                                       onmouseover="this.style.color='#f87171'"
                                       onmouseout="this.style.color='#ef4444'"
                                       onclick="return confirm('Tem certeza que deseja excluir esta notícia?')">
                                        Excluir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </main>

    <!-- SCRIPTS -->
    <script>
    (function () {
        var html  = document.documentElement;
        var btn   = document.getElementById('theme-toggle');
        var track = document.getElementById('toggle-track');
        var icon  = document.getElementById('theme-icon');

        function applyTheme(theme) {
            html.setAttribute('data-theme', theme);
            localStorage.setItem('gg-theme', theme);
            if (theme === 'dark') {
                track && track.classList.add('is-dark');
                if (icon) icon.textContent = '☀️';
            } else {
                track && track.classList.remove('is-dark');
                if (icon) icon.textContent = '🌙';
            }
        }

        /* Sincroniza com o tema já aplicado no <head> */
        applyTheme(html.getAttribute('data-theme') || 'light');

        btn && btn.addEventListener('click', function () {
            applyTheme(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
        });
    })();
    </script>

</body>
</html>