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
        /* =====================================================
           CSS DASHBOARD - HEADER ORGANIZADO
           ===================================================== */

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

        /* ── HEADER ORGANIZADO ──────────────────────────────────── */
        .site-header {
            background: #ffffff;
            border-bottom: 2px solid #e0d8d0;
            padding: 0.75rem 1.5rem;
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
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        /* ── LADO ESQUERDO: Avatar + Logo ───────────────────────── */
        .header-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .header-left .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e0d8d0;
        }

        [data-theme="dark"] .header-left .avatar {
            border-color: #252535;
        }

        .header-left .avatar-fallback {
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
            border: 2px solid #e0d8d0;
        }

        [data-theme="dark"] .header-left .avatar-fallback {
            background: #1c1831;
            color: #c4b5fd;
            border-color: #252535;
        }

        .header-logo {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1a1a1a;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        [data-theme="dark"] .header-logo {
            color: #eeeaf8;
        }

        .header-logo span {
            color: #7c3aed;
        }

        .header-logo:hover {
            color: #7c3aed;
        }

        /* ── LADO DIREITO: Toggle + Links ───────────────────────── */
        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        /* ── TOGGLE DE TEMA ─────────────────────────────────────── */
        .theme-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: #f3f0eb;
            border: 1px solid #e0d8d0;
            border-radius: 0.5rem;
            padding: 0.4rem 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        [data-theme="dark"] .theme-toggle {
            background: #1a1a22;
            border-color: #252535;
        }

        .theme-toggle:hover {
            background: #ede9fe;
            border-color: #7c3aed;
        }

        [data-theme="dark"] .theme-toggle:hover {
            background: #1c1831;
            border-color: #7c3aed;
        }

        .toggle-track {
            width: 30px;
            height: 18px;
            background: #d0c8c0;
            border-radius: 99px;
            position: relative;
            transition: background 0.25s ease;
            flex-shrink: 0;
        }

        [data-theme="dark"] .toggle-track {
            background: #7c3aed;
        }

        .toggle-thumb {
            position: absolute;
            top: 2px;
            left: 2px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 1px 4px rgba(0,0,0,0.2);
            transition: transform 0.25s ease;
        }

        [data-theme="dark"] .toggle-thumb {
            transform: translateX(12px);
        }

        .toggle-icon {
            font-size: 0.8rem;
            line-height: 1;
            color: #5f6378;
        }

        [data-theme="dark"] .toggle-icon {
            color: #918fac;
        }

        .toggle-label {
            font-size: 0.7rem;
            font-weight: 500;
            color: #5f6378;
            white-space: nowrap;
        }

        [data-theme="dark"] .toggle-label {
            color: #918fac;
        }

        /* ── LINKS: Minha Conta e Sair ─────────────────────────── */
        .header-links {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .header-link {
            color: #5f6378;
            font-size: 0.875rem;
            text-decoration: none;
            padding: 0.3rem 0.6rem;
            border-radius: 0.3rem;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        [data-theme="dark"] .header-link {
            color: #918fac;
        }

        .header-link:hover {
            color: #7c3aed;
            background: rgba(124, 58, 237, 0.05);
        }

        [data-theme="dark"] .header-link:hover {
            color: #a78bfa;
            background: rgba(124, 58, 237, 0.1);
        }

        .header-link-sair {
            color: #9ca3af;
            font-size: 0.875rem;
            text-decoration: none;
            padding: 0.3rem 0.6rem;
            border-radius: 0.3rem;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        [data-theme="dark"] .header-link-sair {
            color: #5e5c76;
        }

        .header-link-sair:hover {
            color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
        }

        /* ── NOME DO USUÁRIO ────────────────────────────────────── */
        .header-nome {
            color: #5f6378;
            font-size: 0.875rem;
            display: none;
        }

        [data-theme="dark"] .header-nome {
            color: #918fac;
        }

        @media (min-width: 640px) {
            .header-nome {
                display: inline;
            }
        }

        /* ── DIVISOR ────────────────────────────────────────────── */
        .divisor-header {
            width: 1px;
            height: 28px;
            background: #e0d8d0;
        }

        [data-theme="dark"] .divisor-header {
            background: #252535;
        }

        /* ── CONTEÚDO PRINCIPAL ─────────────────────────────────── */
        .dashboard-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2.5rem 1.5rem;
        }

        .dashboard-header {
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #e0d8d0;
        }

        [data-theme="dark"] .dashboard-header {
            border-color: #252535;
        }

        .dashboard-titulo {
            font-size: 2.2rem;
            font-weight: 800;
            color: #1a1a1a;
            margin: 0 0 0.25rem 0;
        }

        [data-theme="dark"] .dashboard-titulo {
            color: #eeeaf8;
        }

        .dashboard-subtitulo {
            color: #9ca3af;
            font-size: 1rem;
            margin: 0;
        }

        [data-theme="dark"] .dashboard-subtitulo {
            color: #5e5c76;
        }

        .dashboard-actions {
            margin-top: 1.25rem;
        }

        .btn-nova {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #7c3aed;
            color: #fff;
            padding: 0.7rem 2rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
        }

        .btn-nova:hover {
            background: #6d28d9;
            transform: translateY(-2px);
            box-shadow: 0 4px 14px rgba(124, 58, 237, 0.3);
        }

        /* ── MENSAGENS ───────────────────────────────────────────── */
        .msg-sucesso {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid #10b981;
            color: #059669;
            padding: 1rem 1.25rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }

        .msg-erro {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid #ef4444;
            color: #dc2626;
            padding: 1rem 1.25rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }

        /* ── ESTADO VAZIO ────────────────────────────────────────── */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: #ffffff;
            border: 2px dashed #e0d8d0;
            border-radius: 1rem;
            margin-top: 1rem;
        }

        [data-theme="dark"] .empty-state {
            background: #121218;
            border-color: #252535;
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state-titulo {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
        }

        [data-theme="dark"] .empty-state-titulo {
            color: #eeeaf8;
        }

        .empty-state-texto {
            color: #9ca3af;
            font-size: 1rem;
            max-width: 400px;
            margin: 0 auto;
        }

        [data-theme="dark"] .empty-state-texto {
            color: #5e5c76;
        }

        .empty-state-link {
            display: inline-block;
            margin-top: 1.5rem;
            color: #7c3aed;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            padding: 0.6rem 1.5rem;
            border: 2px solid #7c3aed;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }

        .empty-state-link:hover {
            background: #7c3aed;
            color: #fff;
            transform: translateY(-2px);
        }

        /* ── TABELA ───────────────────────────────────────────────── */
        .tabela-container {
            background: #ffffff;
            border: 1px solid #e0d8d0;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-top: 1rem;
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
            letter-spacing: 0.08em;
            padding: 1rem 1.25rem;
            text-align: left;
            border-bottom: 2px solid #e0d8d0;
        }

        [data-theme="dark"] .tabela th {
            background: #1a1a22;
            color: #918fac;
            border-color: #252535;
        }

        .tabela td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #e0d8d0;
            color: #1a1a1a;
            font-size: 0.95rem;
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

        .tabela-acoes {
            text-align: right;
            white-space: nowrap;
        }

        .btn-editar {
            color: #3b82f6;
            text-decoration: none;
            font-size: 0.875rem;
            margin-right: 1.25rem;
            font-weight: 500;
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
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .btn-excluir:hover {
            color: #f87171;
            text-decoration: underline;
        }

        /* ── RESPONSIVIDADE ────────────────────────────────────── */
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1.5rem 1rem;
            }

            .dashboard-titulo {
                font-size: 1.6rem;
            }

            .header-inner {
                gap: 0.5rem;
            }

            .header-right {
                gap: 0.5rem;
            }

            .theme-toggle {
                padding: 0.3rem 0.5rem;
            }

            .toggle-label {
                font-size: 0.6rem;
            }

            .tabela th,
            .tabela td {
                padding: 0.75rem 0.75rem;
                font-size: 0.85rem;
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

            .empty-state {
                padding: 2.5rem 1rem;
            }

            .empty-state-icon {
                font-size: 3rem;
            }

            .header-nome {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .dashboard-container {
                padding: 1rem 0.75rem;
            }

            .dashboard-titulo {
                font-size: 1.3rem;
            }

            .header-inner {
                flex-direction: column;
                align-items: center;
                gap: 0.5rem;
                padding: 0.25rem 0;
            }

            .header-left {
                gap: 0.5rem;
            }

            .header-logo {
                font-size: 1rem;
            }

            .header-right {
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .theme-toggle {
                padding: 0.25rem 0.5rem;
            }

            .toggle-track {
                width: 24px;
                height: 14px;
            }

            .toggle-thumb {
                width: 10px;
                height: 10px;
                top: 2px;
                left: 2px;
            }

            [data-theme="dark"] .toggle-thumb {
                transform: translateX(10px);
            }

            .toggle-label {
                font-size: 0.55rem;
            }

            .toggle-icon {
                font-size: 0.7rem;
            }

            .header-link,
            .header-link-sair {
                font-size: 0.8rem;
                padding: 0.2rem 0.4rem;
            }

            .divisor-header {
                height: 20px;
            }

            .tabela th,
            .tabela td {
                padding: 0.5rem 0.5rem;
                font-size: 0.75rem;
            }

            .btn-nova {
                padding: 0.5rem 1rem;
                font-size: 0.8rem;
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

    <!-- ════════════════════════════════════════════════════
         HEADER ORGANIZADO
    ════════════════════════════════════════════════════ -->
    <header class="site-header">
        <div class="header-inner">

            <!-- LADO ESQUERDO: Avatar + GGNews -->
            <div class="header-left">
                <?php 
                if ($usuario_logado && $usuario_logado['foto']): ?>
                    <img src="../../uploads/<?= escape($usuario_logado['foto']) ?>" class="avatar" alt="Avatar">
                <?php else: ?>
                    <div class="avatar-fallback"><?= get_avatar_initials(get_usuario_nome()) ?></div>
                <?php endif; ?>
                <a href="../../index.php" class="header-logo">
                    🎮 <span>GG</span>News
                </a>
            </div>

            <!-- LADO DIREITO: Toggle + Minha Conta + Sair -->
            <div class="header-right">
                <!-- Toggle de tema -->
                <button id="theme-toggle" class="theme-toggle" aria-label="Alternar tema">
                    <div class="toggle-track" id="toggle-track">
                        <div class="toggle-thumb" id="toggle-thumb"></div>
                    </div>
                    <span class="toggle-icon" id="theme-icon">🌙</span>
                    <span class="toggle-label" id="theme-label">Modo Escuro</span>
                </button>

                <!-- Divisor -->
                <span class="divisor-header"></span>

                <!-- Links -->
                <div class="header-links">
                    <span class="header-nome"><?= escape(get_usuario_nome()) ?></span>
                    <a href="../usuario/editar_usuario.php" class="header-link">👤 Minha Conta</a>
                    <a href="../auth/logout.php" class="header-link-sair">🚪 Sair</a>
                </div>
            </div>

        </div>
    </header>

    <!-- ════════════════════════════════════════════════════
         CONTEÚDO PRINCIPAL
    ════════════════════════════════════════════════════ -->
    <main class="dashboard-container">

        <?php $msg = get_mensagem(); if ($msg): ?>
            <div class="<?= $msg['tipo'] === 'sucesso' ? 'msg-sucesso' : 'msg-erro' ?>">
                <?= escape($msg['texto']) ?>
            </div>
        <?php endif; ?>

        <div class="dashboard-header">
            <h1 class="dashboard-titulo">📋 Meu Painel</h1>
            <p class="dashboard-subtitulo">Gerencie suas notícias publicadas</p>
            <div class="dashboard-actions">
                <a href="nova_noticia.php" class="btn-nova">
                    ➕ Nova Notícia
                </a>
            </div>
        </div>

        <?php if (empty($minhas_noticias)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📝</div>
                <h2 class="empty-state-titulo">Você ainda não publicou nenhuma notícia</h2>
                <p class="empty-state-texto">Comece agora mesmo compartilhando suas notícias com a comunidade!</p>
                <a href="nova_noticia.php" class="empty-state-link">🚀 Publicar minha primeira notícia →</a>
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
                                        ✏️ Editar
                                    </a>
                                    <a href="excluir_noticia.php?id=<?= $noticia['id'] ?>"
                                       class="btn-excluir"
                                       onclick="return confirm('Tem certeza que deseja excluir esta notícia?')">
                                        🗑️ Excluir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </main>

    <!-- ════════════════════════════════════════════════════
         SCRIPTS
    ════════════════════════════════════════════════════ -->
    <script>
    (function () {
        var html  = document.documentElement;
        var btn   = document.getElementById('theme-toggle');
        var track = document.getElementById('toggle-track');
        var icon  = document.getElementById('theme-icon');
        var label = document.getElementById('theme-label');

        function applyTheme(theme) {
            html.setAttribute('data-theme', theme);
            localStorage.setItem('gg-theme', theme);
            if (theme === 'dark') {
                track && track.classList.add('is-dark');
                if (icon) icon.textContent = '☀️';
                if (label) label.textContent = 'Modo Claro';
            } else {
                track && track.classList.remove('is-dark');
                if (icon) icon.textContent = '🌙';
                if (label) label.textContent = 'Modo Escuro';
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