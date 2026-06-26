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

$usuario_logado = null;
if (usuario_logado()) {
    $usuario_logado = [
        'nome' => get_usuario_nome(),
        'foto' => get_usuario_foto()
    ];
}

// Processar novo comentário
$erro_comentario = '';
$sucesso_comentario = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'comentar') {
    if (!usuario_logado()) {
        $erro_comentario = 'Você precisa estar logado para comentar.';
    } else {
        $conteudo = trim($_POST['conteudo'] ?? '');
        if (empty($conteudo)) {
            $erro_comentario = 'O comentário não pode estar vazio.';
        } elseif (strlen($conteudo) < 3) {
            $erro_comentario = 'O comentário deve ter no mínimo 3 caracteres.';
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

// Processar exclusão de comentário - CORRIGIDO
if (isset($_GET['excluir_comentario']) && usuario_logado()) {
    $comentario_id = intval($_GET['excluir_comentario']);
    
    // Verifica se o comentário existe e pertence ao usuário
    $stmt = $pdo->prepare("SELECT id FROM comentarios WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$comentario_id, get_usuario_id()]);
    $comentario_existe = $stmt->fetch();
    
    if ($comentario_existe) {
        $stmt = $pdo->prepare("DELETE FROM comentarios WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$comentario_id, get_usuario_id()]);
        set_mensagem('sucesso', 'Comentário excluído com sucesso.');
    } else {
        set_mensagem('erro', 'Você não tem permissão para excluir este comentário.');
    }
    
    // Redireciona para a mesma página
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
        .comentario-item-header {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .comentario-item-header .avatar-img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e8e2da;
            flex-shrink: 0;
        }

        .comentario-item-header .avatar-fallback {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ede9fe;
            color: #5b21b6;
            font-weight: 700;
            font-size: 0.8rem;
            border: 2px solid #e8e2da;
            flex-shrink: 0;
        }

        [data-theme="dark"] .comentario-item-header .avatar-fallback {
            background: #1c1831;
            color: #c4b5fd;
            border-color: #252535;
        }
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

    <div class="flex-1 md:ml-64 min-w-0">

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
                    <a href="../../index.php" class="flex items-center gap-2 md:hidden text-lg font-bold"
                       style="color:var(--text-primary); text-decoration:none;">
                        🎮 <span style="color:var(--accent)">GG</span>News
                    </a>
                </div>

                <nav class="flex items-center gap-3">
                    <?php if (usuario_logado()): ?>
                        <div class="flex items-center gap-2">
                            <?php 
                            $foto_usuario = get_usuario_foto();
                            if ($foto_usuario): ?>
                                <img src="../../uploads/<?= escape($foto_usuario) ?>" 
                                     class="avatar" 
                                     style="width:32px; height:32px; border-radius:50%; object-fit:cover; border:2px solid var(--border);">
                            <?php else: ?>
                                <div class="avatar-fallback" 
                                     style="width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; background:var(--accent-light); color:var(--accent-text); font-weight:700; font-size:0.8rem; border:2px solid var(--border);">
                                    <?= get_avatar_initials(get_usuario_nome()) ?>
                                </div>
                            <?php endif; ?>
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

        <main class="max-w-4xl mx-auto px-4 py-8">

            <a href="../../index.php"
               class="inline-flex items-center gap-1 text-sm mb-6 transition"
               style="color:var(--accent); text-decoration:none;">
                ← Voltar para as notícias
            </a>

            <?php if ($noticia['imagem']): ?>
                <div class="rounded-2xl overflow-hidden mb-8 h-[400px]">
                    <img src="<?= escape($noticia['imagem']) ?>"
                         alt="<?= escape($noticia['titulo']) ?>"
                         class="w-full h-full object-cover">
                </div>
            <?php endif; ?>

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

            <article class="text-lg leading-relaxed whitespace-pre-line"
                     style="color:var(--text-secondary)">
                <?= escape($noticia['noticia']) ?>
            </article>

            <!-- SEÇÃO DE COMENTÁRIOS -->
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
                            <?php 
                            $foto_usuario = get_usuario_foto();
                            if ($foto_usuario): ?>
                                <img src="../../uploads/<?= escape($foto_usuario) ?>" 
                                     style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid var(--border);">
                            <?php else: ?>
                                <div style="width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; background:var(--accent-light); color:var(--accent-text); font-weight:700; font-size:0.9rem; border:2px solid var(--border);">
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
                                      required></textarea>
                            <button type="submit"
                                    class="btn-primary mt-3 px-6 py-2 text-sm">
                                Enviar Comentário
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="text-center p-8 rounded-xl mb-8" style="background:var(--bg-elevated); border:1px solid var(--border);">
                        <p style="color:var(--text-secondary);">
                            🔒 Faça <a href="../../pages/auth/login.php" style="color:var(--accent); font-weight:600; text-decoration:none;">login</a>
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
                            <div class="p-4 rounded-xl" style="background:var(--bg-elevated); border:1px solid var(--border);">
                                <div class="comentario-item-header">
                                    <?php 
                                    if (!empty($comentario['foto'])): ?>
                                        <img src="../../uploads/<?= escape($comentario['foto']) ?>" 
                                             class="avatar-img">
                                    <?php else: ?>
                                        <div class="avatar-fallback">
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
                                               class="text-xs mt-1 inline-block transition"
                                               style="color:#ef4444; text-decoration:none;"
                                               onmouseover="this.style.color='#f87171'"
                                               onmouseout="this.style.color='#ef4444'">
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

            <div class="mt-12 pt-6 flex items-center justify-between flex-wrap gap-4"
                 style="border-top: 1px solid var(--border);">
                <a href="../../index.php"
                   class="inline-flex items-center gap-2 text-sm font-medium transition"
                   style="color:var(--accent); text-decoration:none;">
                    ← Todas as notícias
                </a>
                <?php if (usuario_logado()): ?>
                    <a href="editar_noticia.php?id=<?= $noticia['id'] ?>"
                       class="btn-primary text-sm px-4 py-2">
                        Editar notícia
                    </a>
                <?php endif; ?>
            </div>

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