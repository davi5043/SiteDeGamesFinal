<?php
// =====================================================
// PÁGINA INDIVIDUAL DA NOTÍCIA - CORRIGIDO E PADRONIZADO
// Arquivo: pages/noticias/noticia.php
// =====================================================

// Inicia a sessão se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclui os arquivos necessários
require_once __DIR__ . '/../../includes/conexao.php';
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/verifica_login.php';
require_once __DIR__ . '/../../includes/comentarios.php';
require_once __DIR__ . '/../../includes/avatar_helper.php';

// =====================================================
// VERIFICA SE A CONEXÃO EXISTE
// =====================================================
if (!isset($conn)) {
    set_mensagem('erro', 'Erro de conexão com o banco de dados.');
    redirecionar('/');
    exit;
}

// =====================================================
// OBTÉM O ID DA NOTÍCIA
// =====================================================
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    set_mensagem('erro', 'ID da notícia inválido.');
    redirecionar('/');
    exit;
}

// =====================================================
// BUSCA A NOTÍCIA NO BANCO
// =====================================================
try {
    $stmt = $conn->prepare("
        SELECT n.*, u.nome AS autor_nome, u.foto AS autor_foto, 
               c.nome AS categoria_nome, c.icone AS categoria_icone
        FROM noticias n
        INNER JOIN usuarios u ON n.autor = u.id
        LEFT JOIN categorias c ON n.categoria_id = c.id
        WHERE n.id = ?
    ");
    $stmt->execute([$id]);
    $noticia = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$noticia) {
        set_mensagem('erro', 'Notícia não encontrada.');
        redirecionar('/');
        exit;
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar notícia: " . $e->getMessage());
    set_mensagem('erro', 'Erro ao carregar a notícia. Tente novamente.');
    redirecionar('/');
    exit;
}

// =====================================================
// BUSCA CATEGORIAS PARA SIDEBAR
// =====================================================
$categorias = get_categorias($conn);

// =====================================================
// BUSCA COMENTÁRIOS
// =====================================================
try {
    $comentarios = get_comentarios($conn, $id);
    $total_comentarios = contar_comentarios($conn, $id);
} catch (PDOException $e) {
    error_log("Erro ao buscar comentários: " . $e->getMessage());
    $comentarios = [];
    $total_comentarios = 0;
}

// =====================================================
// DADOS DO USUÁRIO LOGADO
// =====================================================
$usuario_logado = null;
if (usuario_logado()) {
    $usuario_logado = [
        'id' => get_usuario_id(),
        'nome' => get_usuario_nome(),
        'foto' => get_usuario_foto()
    ];
}

// =====================================================
// PROCESSAR NOVO COMENTÁRIO
// =====================================================
$erro_comentario = '';
$sucesso_comentario = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'comentar') {
    // Verifica token CSRF
    if (!isset($_POST['csrf_token']) || !verificar_token_csrf($_POST['csrf_token'])) {
        $erro_comentario = 'Token de segurança inválido. Tente novamente.';
    } elseif (!usuario_logado()) {
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
            try {
                $comentario_id = adicionar_comentario($conn, $id, get_usuario_id(), $conteudo);
                if ($comentario_id) {
                    $sucesso_comentario = 'Comentário adicionado com sucesso!';
                    // Recarrega os comentários
                    $comentarios = get_comentarios($conn, $id);
                    $total_comentarios = contar_comentarios($conn, $id);
                    
                    // Log da ação
                    error_log("Novo comentário: ID $comentario_id na notícia $id por " . get_usuario_nome());
                } else {
                    $erro_comentario = 'Erro ao adicionar comentário. Tente novamente.';
                }
            } catch (PDOException $e) {
                error_log("Erro ao adicionar comentário: " . $e->getMessage());
                $erro_comentario = 'Erro ao adicionar comentário. Tente novamente.';
            }
        }
    }
}

// =====================================================
// PROCESSAR EXCLUSÃO DE COMENTÁRIO
// =====================================================
if (isset($_GET['excluir_comentario']) && usuario_logado()) {
    $comentario_id = intval($_GET['excluir_comentario']);
    
    if ($comentario_id > 0) {
        try {
            // Verifica se o comentário existe e pertence ao usuário
            $stmt = $conn->prepare("SELECT usuario_id FROM comentarios WHERE id = ?");
            $stmt->execute([$comentario_id]);
            $comentario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($comentario && $comentario['usuario_id'] == get_usuario_id()) {
                $deletado = excluir_comentario($conn, $comentario_id, get_usuario_id());
                if ($deletado) {
                    set_mensagem('sucesso', 'Comentário excluído com sucesso.');
                    // Recarrega os comentários
                    $comentarios = get_comentarios($conn, $id);
                    $total_comentarios = contar_comentarios($conn, $id);
                } else {
                    set_mensagem('erro', 'Erro ao excluir comentário.');
                }
            } else {
                set_mensagem('erro', 'Você não tem permissão para excluir este comentário.');
            }
        } catch (PDOException $e) {
            error_log("Erro ao excluir comentário: " . $e->getMessage());
            set_mensagem('erro', 'Erro ao excluir comentário.');
        }
        
        // Redireciona para a mesma página
        redirecionar('pages/noticias/noticia.php?id=' . $id);
        exit;
    }
}

// =====================================================
// GERA TOKEN CSRF
// =====================================================
$csrf_token = gerar_token_csrf();

// =====================================================
// VERIFICA SE O USUÁRIO É O AUTOR DA NOTÍCIA
// =====================================================
$pode_editar = usuario_logado() && $noticia['autor'] == get_usuario_id();
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape($noticia['titulo']) ?> - GGNews</title>
    <meta name="description" content="<?= escape(resumo_texto($noticia['noticia'], 150)) ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">

    <style>
        :root {
            --bg-body: #0c0c10;
            --bg-card: #1a1a2e;
            --bg-surface: #121218;
            --bg-elevated: #1a1a22;
            --bg-header: #121218;
            --border: #252535;
            --accent: #7c3aed;
            --accent-light: #6d28d9;
            --accent-text: #c4b5fd;
            --text-primary: #eeeaf8;
            --text-secondary: #b8b5d0;
            --text-muted: #5e5c76;
            --text-danger: #ef4444;
            --text-success: #10b981;
            --shadow: 0 4px 20px rgba(0,0,0,0.4);
        }

        [data-theme="light"] {
            --bg-body: #f5f3f0;
            --bg-card: #ffffff;
            --bg-surface: #f8f6f4;
            --bg-elevated: #f0edf0;
            --bg-header: #ffffff;
            --border: #e5e0db;
            --text-primary: #1a1a1a;
            --text-secondary: #4a4a5a;
            --text-muted: #8888a0;
            --shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: var(--bg-body);
            color: var(--text-primary);
            min-height: 100vh;
            transition: background 0.3s ease, color 0.3s ease;
        }

        /* Sidebar */
        .sidebar {
            background: var(--bg-card);
            border-right: 1px solid var(--border);
            transition: background 0.3s ease, transform 0.3s ease;
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

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .logo-icon {
            font-size: 1.5rem;
        }

        .logo-text {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .logo-text span {
            color: var(--accent);
        }

        .logo-tag {
            font-size: 0.65rem;
            color: var(--text-muted);
        }

        .sidebar-section-label {
            color: var(--text-muted);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.2s ease, background 0.2s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--text-primary);
            background: rgba(124, 58, 237, 0.1);
        }

        .nav-link-danger {
            color: var(--text-danger);
        }

        .nav-link-danger:hover {
            background: rgba(239, 68, 68, 0.1);
        }

        .nav-icon {
            font-size: 1.1rem;
        }

        .cat-icon {
            font-size: 1rem;
        }

        .theme-toggle-wrap {
            margin-top: auto;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
        }

        .theme-toggle-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            width: 100%;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            transition: color 0.2s ease, background 0.2s ease;
        }

        .theme-toggle-btn:hover {
            color: var(--text-primary);
            background: rgba(124, 58, 237, 0.1);
        }

        .toggle-track {
            width: 2.5rem;
            height: 1.25rem;
            background: var(--border);
            border-radius: 999px;
            position: relative;
            flex-shrink: 0;
            transition: background 0.25s ease;
        }

        [data-theme="dark"] .toggle-track {
            background: var(--accent);
        }

        .toggle-thumb {
            width: 1rem;
            height: 1rem;
            background: #fff;
            border-radius: 999px;
            position: absolute;
            top: 0.125rem;
            left: 0.125rem;
            transition: transform 0.25s ease;
        }

        [data-theme="light"] .toggle-thumb {
            transform: translateX(1.25rem);
        }

        .toggle-label {
            font-size: 0.8rem;
        }

        .sidebar-footer-tag {
            font-size: 0.65rem;
            color: var(--text-muted);
            margin-top: 0.5rem;
        }

        /* Site Header */
        .site-header {
            background: var(--bg-header);
            border-bottom: 1px solid var(--border);
            padding: 0.75rem 1rem;
            position: sticky;
            top: 0;
            z-index: 30;
            transition: background 0.3s ease, border-color 0.3s ease;
        }

        .btn-primary {
            background: var(--accent);
            color: #fff;
            padding: 0.4rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.15s ease;
            display: inline-block;
        }

        .btn-primary:hover {
            background: var(--accent-light);
            transform: translateY(-1px);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
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

        .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--border);
        }

        .avatar-fallback {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--accent);
            color: #fff;
            font-weight: 700;
            font-size: 0.8rem;
            border: 2px solid var(--border);
            flex-shrink: 0;
        }

        .comentario-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--border);
            flex-shrink: 0;
        }

        .comentario-avatar-fallback {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--accent);
            color: #fff;
            font-weight: 700;
            font-size: 0.9rem;
            border: 2px solid var(--border);
            flex-shrink: 0;
        }

        .msg-erro {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--text-danger);
            color: var(--text-danger);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .msg-sucesso {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid var(--text-success);
            color: var(--text-success);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }

            .sidebar.mobile-open {
                display: flex;
            }
        }

        @media (min-width: 768px) {
            .sidebar {
                display: flex !important;
            }
        }
    </style>

    <script>
        (function() {
            var t = localStorage.getItem('gg-theme') || 'dark';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
</head>
<body>

    <!-- Overlay mobile -->
    <div id="sidebar-overlay" class="sidebar-overlay"></div>

    <!-- ══════════════════════════════════════════════════════
         SIDEBAR
    ══════════════════════════════════════════════════════ -->
    <aside id="sidebar" class="sidebar w-64 fixed h-full z-50 flex-col p-5 overflow-y-auto hidden md:flex">

        <div class="mb-7">
            <a href="<?= BASE_URL ?>" class="sidebar-logo">
                <div class="logo-icon">🎮</div>
                <div>
                    <div class="logo-text"><span>GG</span>News</div>
                    <span class="logo-tag">Games & E-Sports</span>
                </div>
            </a>
        </div>

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

        <?php if (!empty($categorias)): ?>
        <div class="sidebar-section-label">Categorias</div>
        <nav class="sidebar-categories mb-4">
            <?php foreach ($categorias as $cat): ?>
                <a href="<?= BASE_URL ?>pages/categorias/categoria.php?slug=<?= $cat['slug'] ?? '' ?>"
                   class="nav-link <?= (isset($noticia['categoria_nome']) && $noticia['categoria_nome'] === $cat['nome']) ? 'active' : '' ?>">
                    <span class="cat-icon"><?= $cat['icone'] ?? '📰' ?></span>
                    <?= escape($cat['nome']) ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <?php endif; ?>

        <div class="theme-toggle-wrap">
            <button id="theme-toggle" class="theme-toggle-btn" aria-label="Alternar tema">
                <div class="toggle-track">
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
                        <div class="flex items-center gap-2">
                            <?php 
                            $foto_usuario = get_usuario_foto();
                            if ($foto_usuario): ?>
                                <img src="<?= BASE_URL ?>uploads/<?= escape($foto_usuario) ?>" 
                                     class="avatar">
                            <?php else: ?>
                                <div class="avatar-fallback">
                                    <?= get_avatar_initials(get_usuario_nome()) ?>
                                </div>
                            <?php endif; ?>
                            <span class="hidden sm:inline text-sm" style="color:var(--text-secondary)">
                                Olá, <?= escape(get_usuario_nome()) ?>
                            </span>
                        </div>
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

        <!-- MAIN -->
        <main class="max-w-4xl mx-auto px-4 py-8">

            <!-- Voltar -->
            <a href="<?= BASE_URL ?>"
               class="inline-flex items-center gap-1 text-sm mb-6 transition"
               style="color:var(--accent); text-decoration:none;">
                ← Voltar para as notícias
            </a>

            <!-- Imagem -->
            <?php if (!empty($noticia['imagem'])): ?>
                <div class="rounded-2xl overflow-hidden mb-8 h-[400px]">
                    <img src="<?= escape($noticia['imagem']) ?>"
                         alt="<?= escape($noticia['titulo']) ?>"
                         class="w-full h-full object-cover">
                </div>
            <?php endif; ?>

            <!-- Título -->
            <h1 class="text-3xl md:text-4xl font-bold leading-tight"
                style="color:var(--text-primary)">
                <?= escape($noticia['titulo']) ?>
            </h1>

            <!-- Meta -->
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
                <span style="color:var(--text-muted)">•</span>
                <span style="color:var(--text-muted)">💬 <?= $total_comentarios ?> comentário<?= $total_comentarios != 1 ? 's' : '' ?></span>
            </div>

            <!-- Conteúdo -->
            <article class="text-lg leading-relaxed whitespace-pre-line"
                     style="color:var(--text-secondary)">
                <?= escape($noticia['noticia']) ?>
            </article>

            <!-- ══════════════════════════════════════════════════════
                 SEÇÃO DE COMENTÁRIOS
            ══════════════════════════════════════════════════════ -->
            <section class="mt-12 pt-8" style="border-top: 1px solid var(--border);">

                <h3 class="text-2xl font-bold mb-6" style="color:var(--text-primary)">
                    💬 Comentários (<?= $total_comentarios ?>)
                </h3>

                <!-- Mensagens -->
                <?php if ($erro_comentario): ?>
                    <div class="msg-erro"><?= escape($erro_comentario) ?></div>
                <?php endif; ?>

                <?php if ($sucesso_comentario): ?>
                    <div class="msg-sucesso"><?= escape($sucesso_comentario) ?></div>
                <?php endif; ?>

                <?php $msg = get_mensagem(); if ($msg): ?>
                    <div class="<?= $msg['tipo'] === 'sucesso' ? 'msg-sucesso' : 'msg-erro' ?>">
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
                                <img src="<?= BASE_URL ?>uploads/<?= escape($foto_usuario) ?>" 
                                     class="comentario-avatar">
                            <?php else: ?>
                                <div class="comentario-avatar-fallback">
                                    <?= get_avatar_initials(get_usuario_nome()) ?>
                                </div>
                            <?php endif; ?>
                            <span class="font-medium" style="color:var(--text-primary)">
                                <?= escape(get_usuario_nome()) ?>
                            </span>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="acao" value="comentar">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <textarea name="conteudo" rows="3"
                                      placeholder="Escreva seu comentário..."
                                      class="w-full px-4 py-3 rounded-lg resize-y"
                                      style="background:var(--bg-surface); border:1px solid var(--border);
                                             color:var(--text-primary); font-family:inherit; font-size:0.9rem;"
                                      required
                                      maxlength="1000"></textarea>
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:0.5rem;">
                                <span style="color:var(--text-muted);font-size:0.75rem;">Máximo 1000 caracteres</span>
                                <button type="submit" class="btn-primary px-6 py-2 text-sm">
                                    Enviar Comentário
                                </button>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="text-center p-8 rounded-xl mb-8" style="background:var(--bg-elevated); border:1px solid var(--border);">
                        <p style="color:var(--text-secondary);">
                            🔒 Faça <a href="<?= BASE_URL ?>pages/auth/login.php" style="color:var(--accent); font-weight:600; text-decoration:none;">login</a>
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
                                <div class="flex items-start gap-3">
                                    <?php if (!empty($comentario['foto'])): ?>
                                        <img src="<?= BASE_URL ?>uploads/<?= escape($comentario['foto']) ?>" 
                                             class="comentario-avatar">
                                    <?php else: ?>
                                        <div class="comentario-avatar-fallback">
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
                                            <a href="?id=<?= $id ?>&excluir_comentario=<?= $comentario['id'] ?>"
                                               onclick="return confirm('Tem certeza que deseja excluir este comentário?')"
                                               class="text-xs mt-1 inline-block transition"
                                               style="color:var(--text-danger); text-decoration:none;"
                                               onmouseover="this.style.color='#f87171'"
                                               onmouseout="this.style.color='var(--text-danger)'">
                                                🗑️ Excluir
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </section>

            <!-- Rodapé da página -->
            <div class="mt-12 pt-6 flex items-center justify-between flex-wrap gap-4"
                 style="border-top: 1px solid var(--border);">
                <a href="<?= BASE_URL ?>"
                   class="inline-flex items-center gap-2 text-sm font-medium transition"
                   style="color:var(--accent); text-decoration:none;">
                    ← Todas as notícias
                </a>
                <?php if ($pode_editar): ?>
                    <a href="<?= BASE_URL ?>pages/noticias/editar_noticia.php?id=<?= $noticia['id'] ?>"
                       class="btn-primary text-sm px-4 py-2">
                        ✏️ Editar notícia
                    </a>
                <?php endif; ?>
            </div>

        </main>

        <!-- Footer -->
        <footer class="mt-16 border-t" style="background:var(--bg-surface); border-color:var(--border)">
            <div class="max-w-7xl mx-auto px-4 py-8 text-center">
                <p class="text-sm" style="color:var(--text-muted)">
                    🎮 <span style="color:var(--accent); font-weight:700">GG</span>News
                    &copy; <?= date('Y') ?> — Portal de Notícias de Games e E-Sports
                </p>
            </div>
        </footer>

    </div>

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

        applyTheme(html.getAttribute('data-theme') || 'dark');

        if (btn) {
            btn.addEventListener('click', function () {
                applyTheme(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
            });
        }

        // Sidebar mobile
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