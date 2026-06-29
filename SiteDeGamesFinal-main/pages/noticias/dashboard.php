<?php
// =====================================================
// DASHBOARD (PAINEL DO USUÁRIO) - CORRIGIDO E PADRONIZADO
// Arquivo: pages/noticias/dashboard.php
// =====================================================

// Inicia a sessão se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclui os arquivos necessários
require_once __DIR__ . '/../../includes/conexao.php';
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/verifica_login.php';
require_once __DIR__ . '/../../includes/avatar_helper.php';

// =====================================================
// BUSCA AS NOTÍCIAS DO USUÁRIO
// =====================================================
try {
    if (!isset($conn)) {
        throw new Exception("Conexão com o banco de dados não disponível.");
    }

    $stmt = $conn->prepare("
        SELECT n.*, c.nome AS categoria_nome, c.icone AS categoria_icone
        FROM noticias n
        LEFT JOIN categorias c ON n.categoria_id = c.id
        WHERE n.autor = ? 
        ORDER BY n.data DESC
    ");
    $stmt->execute([get_usuario_id()]);
    $minhas_noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erro ao buscar notícias do dashboard: " . $e->getMessage());
    $minhas_noticias = [];
    set_mensagem('erro', 'Erro ao carregar suas notícias. Tente novamente.');
} catch (Exception $e) {
    error_log("Erro no dashboard: " . $e->getMessage());
    $minhas_noticias = [];
}

// =====================================================
// DADOS DO USUÁRIO PARA O AVATAR
// =====================================================
$usuario_logado = [
    'id' => get_usuario_id(),
    'nome' => get_usuario_nome(),
    'foto' => get_usuario_foto(),
    'email' => get_usuario_email()
];

// =====================================================
// CONTAGEM DE NOTÍCIAS
// =====================================================
$total_noticias = count($minhas_noticias);
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - GGNews</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">

    <style>
        :root {
            --bg-body: #0c0c10;
            --bg-card: #1a1a2e;
            --bg-surface: #121218;
            --bg-header: #121218;
            --border: #252535;
            --accent: #7c3aed;
            --accent-light: #6d28d9;
            --accent-hover: #5b21b6;
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

        /* Header */
        .site-header {
            background: var(--bg-header);
            border-bottom: 1px solid var(--border);
            padding: 0.75rem 1rem;
            position: sticky;
            top: 0;
            z-index: 30;
            transition: background 0.3s ease, border-color 0.3s ease;
        }

        .header-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .header-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            text-decoration: none;
            flex-shrink: 0;
        }

        .header-logo span {
            color: var(--accent);
        }

        .header-nav {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .header-nav .nome {
            color: var(--text-secondary);
            font-size: 0.875rem;
            display: none;
        }

        @media (min-width: 640px) {
            .header-nav .nome {
                display: inline;
            }
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
            transition: background 0.2s ease;
        }

        .btn-primary:hover {
            background: var(--accent-light);
        }

        .btn-sair {
            color: var(--text-muted);
            font-size: 0.875rem;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .btn-sair:hover {
            color: var(--text-danger);
        }

        .btn-conta {
            color: var(--accent);
            font-size: 0.875rem;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .btn-conta:hover {
            color: var(--accent-light);
            text-decoration: underline;
        }

        /* Theme Toggle */
        .theme-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            padding: 0.25rem 0.75rem;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .theme-toggle:hover {
            background: var(--bg-card);
        }

        .toggle-track {
            width: 30px;
            height: 18px;
            background: var(--border);
            border-radius: 99px;
            position: relative;
            transition: background 0.25s ease;
        }

        [data-theme="dark"] .toggle-track {
            background: var(--accent);
        }

        .toggle-thumb {
            position: absolute;
            top: 3px;
            left: 3px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 1px 4px rgba(0,0,0,0.2);
            transition: transform 0.25s ease;
        }

        [data-theme="dark"] .toggle-thumb {
            transform: translateX(12px);
        }

        .toggle-icon {
            font-size: 0.9rem;
        }

        /* Dashboard */
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .dashboard-header {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        @media (min-width: 640px) {
            .dashboard-header {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }

        .dashboard-titulo {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }

        .dashboard-subtitulo {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin: 0.25rem 0 0 0;
        }

        .dashboard-stats {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .stat-item {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stat-number {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--accent);
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .btn-nova {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--accent);
            color: #fff;
            padding: 0.6rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            transition: background 0.2s ease, transform 0.2s ease;
            border: none;
            cursor: pointer;
            align-self: flex-start;
        }

        .btn-nova:hover {
            background: var(--accent-light);
            transform: translateY(-2px);
        }

        /* Messages */
        .msg-sucesso {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid var(--text-success);
            color: var(--text-success);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .msg-erro {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--text-danger);
            color: var(--text-danger);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .msg-info {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid #3b82f6;
            color: #3b82f6;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        /* Table */
        .tabela-container {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 0.875rem;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: background 0.3s ease, border-color 0.3s ease;
        }

        .tabela {
            width: 100%;
            border-collapse: collapse;
        }

        .tabela th {
            background: var(--bg-surface);
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.75rem 1.25rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .tabela td {
            padding: 0.75rem 1.25rem;
            border-bottom: 1px solid var(--border);
            color: var(--text-primary);
            font-size: 0.875rem;
            vertical-align: middle;
        }

        .tabela tr:last-child td {
            border-bottom: none;
        }

        .tabela tr:hover td {
            background: var(--bg-surface);
        }

        .tabela-titulo-link {
            color: var(--text-primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
            display: block;
        }

        .tabela-titulo-link:hover {
            color: var(--accent);
        }

        .tabela-categoria {
            display: inline-block;
            font-size: 0.7rem;
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
            background: var(--accent);
            color: #fff;
        }

        .tabela-data {
            color: var(--text-muted);
            font-size: 0.8rem;
            white-space: nowrap;
        }

        .tabela-acoes {
            text-align: right;
            white-space: nowrap;
        }

        .btn-editar {
            color: #3b82f6;
            text-decoration: none;
            font-size: 0.875rem;
            margin-right: 1rem;
            transition: color 0.2s ease;
        }

        .btn-editar:hover {
            color: #60a5fa;
            text-decoration: underline;
        }

        .btn-excluir {
            color: var(--text-danger);
            text-decoration: none;
            font-size: 0.875rem;
            transition: color 0.2s ease;
            background: none;
            border: none;
            cursor: pointer;
        }

        .btn-excluir:hover {
            color: #f87171;
            text-decoration: underline;
        }

        .btn-ver {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.875rem;
            margin-right: 0.75rem;
            transition: color 0.2s ease;
        }

        .btn-ver:hover {
            color: var(--text-primary);
            text-decoration: underline;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 0.875rem;
            transition: background 0.3s ease, border-color 0.3s ease;
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.6;
        }

        .empty-state-titulo {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .empty-state-texto {
            color: var(--text-muted);
            font-size: 0.95rem;
            max-width: 400px;
            margin: 0 auto;
        }

        .empty-state-link {
            display: inline-block;
            margin-top: 1.5rem;
            color: var(--accent);
            font-weight: 500;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .empty-state-link:hover {
            color: var(--accent-light);
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-titulo {
                font-size: 1.5rem;
            }

            .tabela th,
            .tabela td {
                padding: 0.5rem 0.75rem;
                font-size: 0.8rem;
            }

            .tabela-acoes {
                display: flex;
                flex-direction: column;
                gap: 0.25rem;
                align-items: flex-end;
            }

            .btn-editar {
                margin-right: 0;
            }

            .header-nav .nome {
                display: none;
            }

            .stat-item {
                padding: 0.4rem 0.75rem;
            }
        }

        @media (max-width: 480px) {
            .dashboard-container {
                padding: 1rem 0.5rem;
            }

            .tabela th:nth-child(2),
            .tabela td:nth-child(2) {
                display: none;
            }

            .tabela th,
            .tabela td {
                padding: 0.4rem 0.5rem;
                font-size: 0.75rem;
            }

            .dashboard-stats {
                flex-direction: column;
                gap: 0.5rem;
            }

            .header-nav {
                gap: 0.4rem;
            }

            .header-logo {
                font-size: 1rem;
            }

            .btn-nova {
                font-size: 0.8rem;
                padding: 0.4rem 1rem;
            }
        }

        /* Scrollbar style */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-body);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--text-muted);
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

    <header class="site-header">
        <div class="header-inner">
            <a href="<?= BASE_URL ?>" class="header-logo">
                🎮 <span>GG</span>News
            </a>

            <nav class="header-nav">
                <button id="theme-toggle" class="theme-toggle" aria-label="Alternar tema">
                    <div class="toggle-track">
                        <div class="toggle-thumb"></div>
                    </div>
                    <span class="toggle-icon" id="theme-icon">🌙</span>
                </button>

                <?php if ($usuario_logado['foto']): ?>
                    <img src="<?= BASE_URL ?>uploads/<?= escape($usuario_logado['foto']) ?>" 
                         class="avatar" 
                         alt="Avatar"
                         style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:2px solid var(--border);">
                <?php else: ?>
                    <div style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:var(--accent);color:#fff;font-weight:700;font-size:0.8rem;border:2px solid var(--border);">
                        <?= get_avatar_initials($usuario_logado['nome']) ?>
                    </div>
                <?php endif; ?>

                <span class="nome"><?= escape($usuario_logado['nome']) ?></span>
                <a href="<?= BASE_URL ?>pages/usuario/editar_usuario.php" class="btn-conta">Minha Conta</a>
                <a href="<?= BASE_URL ?>pages/auth/logout.php" class="btn-sair">Sair</a>
            </nav>
        </div>
    </header>

    <main class="dashboard-container">

        <!-- Mensagens Flash -->
        <?php $msg = get_mensagem(); if ($msg): ?>
            <div class="<?= $msg['tipo'] === 'sucesso' ? 'msg-sucesso' : ($msg['tipo'] === 'erro' ? 'msg-erro' : 'msg-info') ?>">
                <?= escape($msg['texto']) ?>
            </div>
        <?php endif; ?>

        <!-- Header do Dashboard -->
        <div class="dashboard-header">
            <div>
                <h1 class="dashboard-titulo">📋 Meu Painel</h1>
                <p class="dashboard-subtitulo">Gerencie suas notícias publicadas</p>
            </div>
            
            <div style="display:flex;gap:1rem;align-items:center;flex-wrap:wrap;">
                <div class="dashboard-stats">
                    <div class="stat-item">
                        <span class="stat-number"><?= $total_noticias ?></span>
                        <span class="stat-label">notícias</span>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>pages/noticias/nova_noticia.php" class="btn-nova">
                    + Nova Notícia
                </a>
            </div>
        </div>

        <!-- Lista de Notícias -->
        <?php if (empty($minhas_noticias)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📝</div>
                <h2 class="empty-state-titulo">Você ainda não publicou nenhuma notícia</h2>
                <p class="empty-state-texto">Comece agora mesmo compartilhando suas notícias com a comunidade!</p>
                <a href="<?= BASE_URL ?>pages/noticias/nova_noticia.php" class="empty-state-link">
                    Publicar minha primeira notícia →
                </a>
            </div>
        <?php else: ?>
            <div class="tabela-container">
                <table class="tabela">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Categoria</th>
                            <th>Data</th>
                            <th style="text-align:right;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($minhas_noticias as $noticia): ?>
                            <tr>
                                <td>
                                    <a href="<?= BASE_URL ?>pages/noticias/noticia.php?id=<?= $noticia['id'] ?>" 
                                       class="tabela-titulo-link">
                                        <?= escape($noticia['titulo']) ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if (!empty($noticia['categoria_nome'])): ?>
                                        <span class="tabela-categoria">
                                            <?= $noticia['categoria_icone'] ?? '📰' ?>
                                            <?= escape($noticia['categoria_nome']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color:var(--text-muted);font-size:0.75rem;">Sem categoria</span>
                                    <?php endif; ?>
                                </td>
                                <td class="tabela-data">
                                    <?= formatar_data($noticia['data']) ?>
                                </td>
                                <td class="tabela-acoes">
                                    <a href="<?= BASE_URL ?>pages/noticias/noticia.php?id=<?= $noticia['id'] ?>" 
                                       class="btn-ver" 
                                       title="Ver notícia">
                                        👁️
                                    </a>
                                    <a href="<?= BASE_URL ?>pages/noticias/editar_noticia.php?id=<?= $noticia['id'] ?>" 
                                       class="btn-editar">
                                        Editar
                                    </a>
                                    <a href="<?= BASE_URL ?>pages/noticias/excluir_noticia.php?id=<?= $noticia['id'] ?>" 
                                       class="btn-excluir"
                                       onclick="return confirm('Tem certeza que deseja excluir a notícia \'<?= escape($noticia['titulo']) ?>\'? Esta ação não pode ser desfeita.')">
                                        Excluir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Rodapé da tabela com informações -->
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:1rem;flex-wrap:wrap;gap:0.5rem;">
                <span style="color:var(--text-muted);font-size:0.8rem;">
                    Mostrando <?= $total_noticias ?> notícia<?= $total_noticias > 1 ? 's' : '' ?>
                </span>
                <a href="<?= BASE_URL ?>" style="color:var(--text-muted);font-size:0.8rem;text-decoration:none;">
                    ← Voltar para o portal
                </a>
            </div>
        <?php endif; ?>

    </main>

    <script>
    (function () {
        var html = document.documentElement;
        var btn = document.getElementById('theme-toggle');
        var icon = document.getElementById('theme-icon');

        function applyTheme(theme) {
            html.setAttribute('data-theme', theme);
            localStorage.setItem('gg-theme', theme);
            if (theme === 'dark') {
                if (icon) icon.textContent = '☀️';
            } else {
                if (icon) icon.textContent = '🌙';
            }
        }

        // Aplica o tema salvo
        var savedTheme = localStorage.getItem('gg-theme') || 'dark';
        applyTheme(savedTheme);

        // Alterna o tema
        if (btn) {
            btn.addEventListener('click', function () {
                var current = html.getAttribute('data-theme');
                applyTheme(current === 'dark' ? 'light' : 'dark');
            });
        }

        // Confirmação de exclusão com Sweet Alert (opcional)
        document.querySelectorAll('.btn-excluir').forEach(function(link) {
            link.addEventListener('click', function(e) {
                if (!confirm(this.getAttribute('data-confirm') || 'Tem certeza que deseja excluir esta notícia?')) {
                    e.preventDefault();
                }
            });
        });
    })();
    </script>

</body>
</html>