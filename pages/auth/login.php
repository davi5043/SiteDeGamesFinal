<?php
// =====================================================
// PÁGINA DE LOGIN
// Arquivo: pages/auth/login.php
// =====================================================

require_once __DIR__ . '/../../includes/conexao.php';
require_once __DIR__ . '/../../includes/funcoes.php';

// Se já está logado, redireciona para o dashboard
if (usuario_logado()) {
    header("Location: ../../pages/noticias/dashboard.php");
    exit;
}

$erro = '';

// Processa o formulário quando enviado via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $erro = 'Preencha todos os campos.';
    } else {
        // Busca o usuário pelo email no banco de dados
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        // Verifica se o usuário existe e se a senha está correta
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_foto'] = $usuario['foto'];

            set_mensagem('sucesso', 'Bem-vindo de volta, ' . $usuario['nome'] . '!');
            header("Location: ../../pages/noticias/dashboard.php");
            exit;
        } else {
            $erro = 'Email ou senha incorretos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Portal Games & E-Sports</title>
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
            background: #0f0f0f;
            color: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .container {
            width: 100%;
            max-width: 420px;
        }

        .logo-area {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-area h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #ffffff;
        }

        .logo-area h1 span {
            color: #7c3aed;
        }

        .logo-area p {
            color: #9ca3af;
            margin-top: 0.25rem;
        }

        .card {
            background: #1a1a2e;
            border: 1px solid #2a2a3e;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        }

        .msg-erro {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid #ef4444;
            color: #f87171;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .msg-sucesso {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid #10b981;
            color: #34d399;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            color: #9ca3af;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.375rem;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            background: #0f0f0f;
            border: 1px solid #2a2a3e;
            border-radius: 0.5rem;
            color: #ffffff;
            font-family: inherit;
            font-size: 0.95rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-input::placeholder {
            color: #4a4a5e;
        }

        .form-input:focus {
            outline: none;
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.2);
        }

        .btn-login {
            width: 100%;
            background: #7c3aed;
            color: #ffffff;
            padding: 0.75rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 0.5rem;
        }

        .btn-login:hover {
            background: #6d28d9;
            transform: translateY(-2px);
            box-shadow: 0 4px 14px rgba(124, 58, 237, 0.3);
        }

        .footer-links {
            text-align: center;
            margin-top: 1.5rem;
        }

        .footer-links p {
            color: #9ca3af;
            font-size: 0.9rem;
        }

        .footer-links a {
            color: #7c3aed;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .footer-links a:hover {
            color: #a78bfa;
            text-decoration: underline;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: #4a4a5e;
            font-size: 0.875rem;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .back-link:hover {
            color: #9ca3af;
        }

        @media (max-width: 480px) {
            .card {
                padding: 1.5rem;
            }

            .logo-area h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>

    <div class="container">

        <!-- LOGO -->
        <div class="logo-area">
            <h1>🎮 <span>GG</span>News</h1>
            <p>Entre na sua conta</p>
        </div>

        <!-- CARD -->
        <div class="card">

            <?php if ($erro): ?>
                <div class="msg-erro"><?= escape($erro) ?></div>
            <?php endif; ?>

            <?php $msg = get_mensagem(); if ($msg): ?>
                <div class="<?= $msg['tipo'] === 'sucesso' ? 'msg-sucesso' : 'msg-erro' ?>">
                    <?= escape($msg['texto']) ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="form-group">
                    <label for="email" class="form-label">📧 E-mail</label>
                    <input type="email" id="email" name="email"
                           value="<?= escape($email ?? '') ?>"
                           class="form-input"
                           placeholder="seu@email.com"
                           required>
                </div>

                <div class="form-group">
                    <label for="senha" class="form-label">🔒 Senha</label>
                    <input type="password" id="senha" name="senha"
                           class="form-input"
                           placeholder="••••••••"
                           required>
                </div>

                <button type="submit" class="btn-login">Entrar</button>

            </form>

            <div class="footer-links">
                <p>Não tem conta? <a href="cadastro.php">Cadastre-se</a></p>
            </div>

        </div>

        <a href="../../index.php" class="back-link">← Voltar para o portal</a>

    </div>

</body>
</html>