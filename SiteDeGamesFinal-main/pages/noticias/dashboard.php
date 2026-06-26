<?php
// =====================================================
// DASHBOARD (PAINEL DO USUÁRIO)
// Arquivo: pages/noticias/dashboard.php
// =====================================================

require_once __DIR__ . '/../../includes/conexao.php';
require_once __DIR__ . '/../../includes/verifica_login.php';
require_once __DIR__ . '/../../includes/avatar_helper.php';

$stmt = $pdo->prepare("SELECT * FROM noticias WHERE autor = ? ORDER BY data DESC");
$stmt->execute([get_usuario_id()]);
$minhas_noticias = $stmt->fetchAll();

$usuario_logado = [
    'nome' => get_usuario_nome(),
    'foto' => get_usuario_foto()
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - GGNews</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../css/style.css">

    <style>
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
        }

        [data-theme="dark"] body {
            background: #0c0c10;
            color: #eeeaf8;
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

        .header-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.25rem;
            font-weight: 700;
            color: #1a1a1a;
            text-decoration: none;
        }

        [data-theme="dark"] .header-logo {
            color: #eeeaf8;
        }

        .header-logo span {
            color: #7c3aed;
        }

        .header-nav {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-nav .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e8e2da;
        }

        [data-theme="dark"] .header-nav .avatar {
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

        .theme-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: #f8f4f0;
            border: 1px solid #e8e2da;
            border-radius: 0.5rem;
            padding: 0.25rem 0.75rem;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        [data-theme="dark"] .theme-toggle {
            background: #1a1a22;
            border-color: #252535;
        }

        .theme-toggle:hover {
            background: #ede9fe;
        }

        [data-theme="dark"] .theme-toggle:hover {
            background: #1c1831;
        }

        .toggle-track {
            width: 30px;
            height: 18px;
            background: #e8e2da;
            border-radius: 99px;
            position: relative;
            transition: background 0.25s ease;
        }

        [data-theme="dark"] .toggle-track {
            background: #7c3aed;
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
            color: #1a1a1a;
            margin: 0;
        }

        [data-theme="dark"] .dashboard-titulo {
            color: #eeeaf8;
        }

        .dashboard-subtitulo {
            color: #9ca3af;
            font-size: 0.9rem;
            margin: 0.25rem 0 0 0;
        }

        [data-theme="dark"] .dashboard-subtitulo {
            color: #5e5c76;
        }

        .btn-nova {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #7c3aed;
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
            background: #6d28d9;
            transform: translateY(-2px);
        }

        .msg-sucesso {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid #10b981;
            color: #059669;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .msg-erro {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid #ef4444;
            color: #dc2626;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .tabela-container {
            background: #ffffff;
            border: 1px solid #e8e2da;
            border-radius: 0.875rem;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        [data-theme="dark"] .tabela-container {
            background: #121218;
            border-color: #252535;
            box-shadow: 0 2px 10px rgba(0,0,0,0.35);
        }

        .tabela {
            width: 100%;
            border-collapse: collapse;
        }

        .tabela th {
            background: #f8f4f0;
            color: #5f6378;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.75rem 1.25rem;
            text-align: left;
            border-bottom: 1px solid #e8e2da;
        }

        [data-theme="dark"] .tabela th {
            background: #1a1a22;
            color: #918fac;
            border-color: #252535;
        }

        .tabela td {
            padding: 0.75rem 1.25rem;
            border-bottom: 1px solid #e8e2da;
            color: #1a1a1a;
            font-size: 0.875rem;
        }

        [data-theme="dark"] .tabela td {
            color: #eeeaf8;
            border-color: #252535;
        }

        .tabela tr:last-child td {
            border-bottom: none;
        }

        .tabela tr:hover td {
            background: #f8f4f0;
        }

        [data-theme="dark"] .tabela tr:hover td {
            background: #1a1a22;
        }

        .tabela-titulo-link {
            color: #1a1a1a;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .tabela-titulo-link:hover {
            color: #7c3aed;
        }

        [data-theme="dark"] .tabela-titulo-link {
            color: #eeeaf8;
        }

        [data-theme="dark"] .tabela-titulo-link:hover {
            color: #a78bfa;
        }

        .tabela-data {
            color: #9ca3af;
            font-size: 0.875rem;
        }

        [data-theme="dark"] .tabela-data {
            color: #5e5c76;
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
            color: #ef4444;
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

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            background: #ffffff;
            border: 1px solid #e8e2da;
            border-radius: 0.875rem;
        }

        [data-theme="dark"] .empty-state {
            background: #121218;
            border-color: #252535;
        }

        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.6;
        }

        .empty-state-titulo {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
        }

        [data-theme="dark"] .empty-state-titulo {
            color: #eeeaf8;
        }

        .empty-state-texto {
            color: #9ca3af;
            font-size: 0.9rem;
        }

        [data-theme="dark"] .empty-state-texto {
            color: #5e5c76;
        }

        .empty-state-link {
            display: inline-block;
            margin-top: 1rem;
            color: #7c3aed;
            font-weight: 500;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .empty-state-link:hover {
            color: #6d28d9;
            text-decoration: underline;
        }

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

    <header class="site-header">
        <div class="header-inner">
            <a href="../../index.php" class="header-logo">
                🎮 <span>GG</span>News
            </a>

            <nav class="header-nav">
                <button id="theme-toggle" class="theme-toggle" aria-label="Alternar tema">
                    <div class="toggle-track" id="toggle-track">
                        <div class="toggle-thumb" id="toggle-thumb"></div>
                    </div>
                    <span class="toggle-icon" id="theme-icon">🌙</span>
                </button>

                <?php 
                $foto_usuario = get_usuario_foto();
                if ($foto_usuario): ?>
                    <img src="../../uploads/<?= escape($foto_usuario) ?>" class="avatar" alt="Avatar">
                <?php else: ?>
                    <div class="avatar-fallback"><?= get_avatar_initials(get_usuario_nome()) ?></div>
                <?php endif; ?>

                <span class="nome"><?= escape(get_usuario_nome()) ?></span>
                <a href="../usuario/editar_usuario.php" class="btn-sair" style="color:#7c3aed;">Minha Conta</a>
                <a href="../auth/logout.php" class="btn-sair">Sair</a>
            </nav>
        </div>
    </header>

    <main class="dashboard-container">

        <?php $msg = get_mensagem(); if ($msg): ?>
            <div class="<?= $msg['tipo'] === 'sucesso' ? 'msg-sucesso' : 'msg-erro' ?>">
                <?= escape($msg['texto']) ?>
            </div>
        <?php endif; ?>

        <div class="dashboard-header">
            <div>
                <h1 class="dashboard-titulo">📋 Meu Painel</h1>
                <p class="dashboard-subtitulo">Gerencie suas notícias publicadas</p>
            </div>
            <a href="nova_noticia.php" class="btn-nova">
                + Nova Notícia
            </a>
        </div>

        <?php if (empty($minhas_noticias)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📝</div>
                <h2 class="empty-state-titulo">Você ainda não publicou nenhuma notícia</h2>
                <p class="empty-state-texto">Comece agora mesmo compartilhando suas notícias!</p>
                <a href="nova_noticia.php" class="empty-state-link">Publicar minha primeira notícia →</a>
            </div>
        <?php else: ?>
            <div class="tabela-container">
                <table class="tabela">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th style="display:none;">Data</th>
                            <th style="text-align:right;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($minhas_noticias as $noticia): ?>
                            <tr>
                                <td>
                                    <a href="noticia.php?id=<?= $noticia['id'] ?>" class="tabela-titulo-link">
                                        <?= escape($noticia['titulo']) ?>
                                    </a>
                                </td>
                                <td style="display:none;" class="tabela-data">
                                    <?= formatar_data($noticia['data']) ?>
                                </td>
                                <td class="tabela-acoes">
                                    <a href="editar_noticia.php?id=<?= $noticia['id'] ?>" class="btn-editar">
                                        Editar
                                    </a>
                                    <a href="excluir_noticia.php?id=<?= $noticia['id'] ?>"
                                       class="btn-excluir"
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

        applyTheme(html.getAttribute('data-theme') || 'light');

        btn && btn.addEventListener('click', function () {
            applyTheme(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
        });
    })();
    </script>

</body>
</html>