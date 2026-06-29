<?php
// =====================================================
// EDITAR PERFIL DO USUÁRIO
// Arquivo: pages/usuario/editar_usuario.php
// =====================================================

require_once __DIR__ . '/../../includes/conexao.php';
require_once __DIR__ . '/../../includes/verifica_login.php';
require_once __DIR__ . '/../../includes/avatar_helper.php';

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([get_usuario_id()]);
$usuario = $stmt->fetch();

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome       = trim($_POST['nome']       ?? '');
    $username   = trim($_POST['username']   ?? '');
    $email      = trim($_POST['email']      ?? '');
    $senha_atual = $_POST['senha_atual']    ?? '';
    $nova_senha  = $_POST['nova_senha']     ?? '';

    $foto = $usuario['foto'];

    if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $_FILES['foto']['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed_types)) {
            $erro = 'Formato de imagem inválido. Use JPG, PNG, GIF ou WEBP.';
        } elseif ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
            $erro = 'A imagem não pode ultrapassar 2 MB.';
        } else {
            $ext          = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $nomeArquivo  = 'user_' . get_usuario_id() . '_' . uniqid() . '.' . strtolower($ext);
            $dir_uploads  = __DIR__ . '/../../uploads/';
            $destino      = $dir_uploads . $nomeArquivo;

            if (!is_dir($dir_uploads)) {
                mkdir($dir_uploads, 0775, true);
            }

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
                if (!empty($usuario['foto'])) {
                    $antiga = $dir_uploads . $usuario['foto'];
                    if (file_exists($antiga)) {
                        unlink($antiga);
                    }
                }
                $foto = $nomeArquivo;
            } else {
                $erro = 'Não foi possível salvar a imagem. Verifique as permissões da pasta /uploads/.';
            }
        }
    }

    if (empty($erro)) {
        if (empty($nome) || empty($email)) {
            $erro = 'Nome e email são obrigatórios.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erro = 'Email inválido.';
        } else {
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $stmt->execute([$email, get_usuario_id()]);

            if ($stmt->fetch()) {
                $erro = 'Este email já está em uso por outro usuário.';
            } else {
                $stmt = $pdo->prepare("
                    UPDATE usuarios
                    SET nome = ?, username = ?, email = ?, foto = ?
                    WHERE id = ?
                ");
                $stmt->execute([$nome, $username, $email, $foto, get_usuario_id()]);

                $usuario['foto'] = $foto;

                if (!empty($nova_senha)) {
                    if (empty($senha_atual)) {
                        $erro = 'Informe a senha atual para alterar a senha.';
                    } elseif (!password_verify($senha_atual, $usuario['senha'])) {
                        $erro = 'Senha atual incorreta.';
                    } elseif (strlen($nova_senha) < 6) {
                        $erro = 'A nova senha deve ter no mínimo 6 caracteres.';
                    } else {
                        $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
                        $stmt->execute([$hash, get_usuario_id()]);
                    }
                }

                if (empty($erro)) {
                    $_SESSION['usuario_nome'] = $nome;
                    $_SESSION['usuario_foto'] = $foto;
                    set_mensagem('sucesso', 'Dados atualizados com sucesso!');
                    redirecionar('../noticias/dashboard.php');
                }
            }
        }
    }

} else {
    $nome     = $usuario['nome'];
    $username = $usuario['username'] ?? '';
    $email    = $usuario['email'];
}

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
    <title>Minha Conta - GGNews</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../css/style.css">

    <style>
        /* =====================================================
           CSS EDITAR PERFIL - HEADER ORGANIZADO
           ===================================================== */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f3f0eb;
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
            max-width: 700px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.5rem;
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

        /* ── LADO DIREITO: Toggle + Voltar ──────────────────────── */
        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
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

        /* ── BOTÃO VOLTAR ───────────────────────────────────────── */
        .btn-voltar {
            color: #9ca3af;
            font-size: 0.875rem;
            text-decoration: none;
            transition: color 0.2s ease;
            padding: 0.3rem 0.6rem;
            border-radius: 0.3rem;
            white-space: nowrap;
        }

        .btn-voltar:hover {
            color: #7c3aed;
            background: rgba(124, 58, 237, 0.05);
        }

        [data-theme="dark"] .btn-voltar {
            color: #5e5c76;
        }

        /* ── CONTEÚDO PRINCIPAL ─────────────────────────────────── */
        .container {
            max-width: 700px;
            margin: 0 auto;
            padding: 2.5rem 1.5rem;
        }

        .titulo {
            font-size: 2rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 2rem;
            padding-bottom: 0.75rem;
            border-bottom: 3px solid #7c3aed;
            display: inline-block;
        }

        [data-theme="dark"] .titulo {
            color: #eeeaf8;
        }

        /* ── FORMULÁRIO ──────────────────────────────────────────── */
        .form-card {
            background: #ffffff;
            border: 1px solid #e0d8d0;
            border-radius: 1rem;
            padding: 2.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        [data-theme="dark"] .form-card {
            background: #121218;
            border-color: #252535;
            box-shadow: 0 2px 10px rgba(0,0,0,0.35);
        }

        /* ── AVATAR UPLOAD ───────────────────────────────────────── */
        .avatar-upload {
            text-align: center;
            margin-bottom: 2.5rem;
            padding-bottom: 2rem;
            border-bottom: 2px solid #e0d8d0;
        }

        [data-theme="dark"] .avatar-upload {
            border-color: #252535;
        }

        .avatar-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 1rem;
            border: 3px solid #e0d8d0;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        [data-theme="dark"] .avatar-preview {
            border-color: #252535;
        }

        .avatar-preview:hover {
            border-color: #7c3aed;
            transform: scale(1.02);
        }

        .avatar-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-preview .fallback {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ede9fe;
            color: #5b21b6;
            font-weight: 700;
            font-size: 3rem;
        }

        [data-theme="dark"] .avatar-preview .fallback {
            background: #1c1831;
            color: #c4b5fd;
        }

        .btn-upload-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-upload {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #f3f0eb;
            color: #5f6378;
            padding: 0.7rem 2rem;
            border-radius: 0.5rem;
            border: 1px solid #e0d8d0;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        [data-theme="dark"] .btn-upload {
            background: #1a1a22;
            color: #918fac;
            border-color: #252535;
        }

        .btn-upload:hover {
            background: #ede9fe;
            border-color: #7c3aed;
            transform: translateY(-2px);
        }

        [data-theme="dark"] .btn-upload:hover {
            background: #1c1831;
            border-color: #7c3aed;
        }

        .upload-hint {
            font-size: 0.75rem;
            color: #9ca3af;
        }

        [data-theme="dark"] .upload-hint {
            color: #5e5c76;
        }

        /* ── CAMPOS DO FORMULÁRIO ────────────────────────────────── */
        .form-group {
            margin-bottom: 1.75rem;
        }

        .form-group:last-of-type {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: #5f6378;
            margin-bottom: 0.5rem;
            letter-spacing: 0.02em;
        }

        [data-theme="dark"] .form-label {
            color: #918fac;
        }

        .form-input {
            width: 100%;
            padding: 0.9rem 1rem;
            border: 1.5px solid #e0d8d0;
            border-radius: 0.5rem;
            background: #f8f4f0;
            color: #1a1a1a;
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.2s ease;
        }

        [data-theme="dark"] .form-input {
            background: #1a1a22;
            border-color: #252535;
            color: #eeeaf8;
        }

        .form-input::placeholder {
            color: #b0a8a0;
        }

        [data-theme="dark"] .form-input::placeholder {
            color: #5e5c76;
        }

        .form-input:focus {
            outline: none;
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15);
            background: #ffffff;
        }

        [data-theme="dark"] .form-input:focus {
            background: #121218;
        }

        /* ── DIVISOR ──────────────────────────────────────────────── */
        .divisor {
            border: none;
            border-top: 2px solid #e0d8d0;
            margin: 2rem 0;
        }

        [data-theme="dark"] .divisor {
            border-color: #252535;
        }

        .divisor-texto {
            font-size: 1rem;
            color: #9ca3af;
            margin-bottom: 1rem;
            font-weight: 500;
        }

        [data-theme="dark"] .divisor-texto {
            color: #5e5c76;
        }

        .divisor-texto span {
            font-style: italic;
            color: #b0a8a0;
        }

        [data-theme="dark"] .divisor-texto span {
            color: #5e5c76;
        }

        /* ── BOTÕES ──────────────────────────────────────────────── */
        .btn-salvar {
            width: 100%;
            background: #7c3aed;
            color: #fff;
            padding: 1rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 1.05rem;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 0.5rem;
        }

        .btn-salvar:hover {
            background: #6d28d9;
            transform: translateY(-2px);
            box-shadow: 0 4px 14px rgba(124, 58, 237, 0.3);
        }

        .btn-excluir-conta {
            font-size: 0.875rem;
            color: #ef4444;
            text-decoration: none;
            display: inline-block;
            margin-top: 0.5rem;
            padding: 0.5rem 0.8rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }

        .btn-excluir-conta:hover {
            color: #dc2626;
            background: rgba(239, 68, 68, 0.1);
            text-decoration: underline;
        }

        [data-theme="dark"] .btn-excluir-conta:hover {
            background: rgba(239, 68, 68, 0.2);
        }

        /* ── MENSAGEM DE ERRO ────────────────────────────────────── */
        .msg-erro {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid #ef4444;
            color: #dc2626;
            padding: 1rem 1.25rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }

        [data-theme="dark"] .msg-erro {
            color: #f87171;
            background: rgba(239, 68, 68, 0.15);
        }

        /* ── RESPONSIVIDADE ────────────────────────────────────── */
        @media (max-width: 768px) {
            .container {
                padding: 2rem 1rem;
            }

            .form-card {
                padding: 1.5rem;
            }

            .titulo {
                font-size: 1.6rem;
            }

            .avatar-preview {
                width: 100px;
                height: 100px;
            }

            .header-inner {
                gap: 0.75rem;
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
        }

        @media (max-width: 480px) {
            .container {
                padding: 1rem 0.75rem;
            }

            .form-card {
                padding: 1.25rem;
            }

            .titulo {
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
                gap: 0.75rem;
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

            .btn-voltar {
                font-size: 0.8rem;
                padding: 0.2rem 0.5rem;
            }

            .form-input {
                padding: 0.7rem 0.8rem;
                font-size: 0.9rem;
            }

            .btn-salvar {
                padding: 0.8rem;
                font-size: 0.95rem;
            }

            .avatar-preview {
                width: 80px;
                height: 80px;
            }

            .avatar-preview .fallback {
                font-size: 2rem;
            }

            .btn-upload {
                padding: 0.5rem 1.5rem;
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

    <!-- HEADER ORGANIZADO -->
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

            <!-- LADO DIREITO: Toggle + Voltar -->
            <div class="header-right">
                <button id="theme-toggle" class="theme-toggle" aria-label="Alternar tema">
                    <div class="toggle-track" id="toggle-track">
                        <div class="toggle-thumb" id="toggle-thumb"></div>
                    </div>
                    <span class="toggle-icon" id="theme-icon">🌙</span>
                    <span class="toggle-label" id="theme-label">Modo Escuro</span>
                </button>
                <a href="../noticias/dashboard.php" class="btn-voltar">← Voltar</a>
            </div>

        </div>
    </header>

    <!-- CONTEÚDO PRINCIPAL -->
    <main class="container">

        <h1 class="titulo">👤 Editar Perfil</h1>

        <?php if ($erro): ?>
            <div class="msg-erro"><?= escape($erro) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="form-card">

            <!-- FOTO DE PERFIL -->
            <div class="avatar-upload">
                <div class="avatar-preview" id="avatar-preview">
                    <?php if ($usuario['foto']): ?>
                        <img src="../../uploads/<?= escape($usuario['foto']) ?>" alt="Avatar">
                    <?php else: ?>
                        <div class="fallback"><?= get_avatar_initials($usuario['nome']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="btn-upload-wrapper">
                    <label for="foto" class="btn-upload">
                        📷 Alterar foto
                    </label>
                    <input type="file" name="foto" id="foto" accept="image/*" style="display:none;">
                    <span class="upload-hint">Formatos: JPG, PNG, WEBP — Tamanho máximo: 2 MB</span>
                </div>
            </div>

            <!-- NOME -->
            <div class="form-group">
                <label for="nome" class="form-label">📛 Nome completo</label>
                <input type="text" name="nome" id="nome"
                       value="<?= escape($nome) ?>"
                       class="form-input"
                       placeholder="Digite seu nome completo">
            </div>

            <!-- USERNAME -->
            <div class="form-group">
                <label for="username" class="form-label">🔖 Username</label>
                <input type="text" name="username" id="username"
                       value="<?= escape($username) ?>"
                       class="form-input"
                       placeholder="Digite seu @username">
            </div>

            <!-- EMAIL -->
            <div class="form-group">
                <label for="email" class="form-label">📧 E-mail</label>
                <input type="email" name="email" id="email"
                       value="<?= escape($email) ?>"
                       class="form-input"
                       placeholder="Digite seu e-mail">
            </div>

            <!-- DIVISOR -->
            <hr class="divisor">
            <p class="divisor-texto">🔒 Alterar senha <span>(opcional)</span></p>

            <!-- SENHA ATUAL -->
            <div class="form-group">
                <label for="senha_atual" class="form-label">🔑 Senha atual</label>
                <input type="password" name="senha_atual" id="senha_atual"
                       class="form-input"
                       placeholder="Digite sua senha atual">
            </div>

            <!-- NOVA SENHA -->
            <div class="form-group">
                <label for="nova_senha" class="form-label">🔐 Nova senha</label>
                <input type="password" name="nova_senha" id="nova_senha"
                       class="form-input"
                       placeholder="Mínimo 6 caracteres">
            </div>

            <!-- EXCLUIR CONTA -->
            <div style="margin: 0.5rem 0 1.5rem 0;">
                <a href="excluir_usuario.php"
                   onclick="return confirm('⚠️ Tem certeza que deseja excluir sua conta permanentemente? Todas as suas notícias serão removidas.')"
                   class="btn-excluir-conta">
                    🗑️ Excluir minha conta
                </a>
            </div>

            <!-- BOTÃO SALVAR -->
            <button type="submit" class="btn-salvar">💾 Salvar Alterações</button>

        </form>

    </main>

    <!-- SCRIPTS -->
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

        /* PREVIEW DA FOTO */
        var inputFoto   = document.getElementById('foto');
        var previewWrap = document.getElementById('avatar-preview');

        inputFoto && inputFoto.addEventListener('change', function () {
            var file = this.files[0];
            if (!file) return;

            if (file.size > 2 * 1024 * 1024) {
                alert('A imagem não pode ultrapassar 2 MB.');
                this.value = '';
                return;
            }

            var reader = new FileReader();
            reader.onload = function (e) {
                previewWrap.innerHTML =
                    '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;">';
            };
            reader.readAsDataURL(file);
        });
    })();
    </script>

</body>
</html>