<?php
// =====================================================
// PÁGINA DE CADASTRO
// Arquivo: pages/auth/cadastro.php
// =====================================================

require_once __DIR__ . '/../../includes/conexao.php';
require_once __DIR__ . '/../../includes/funcoes.php';

if (usuario_logado()) {
    header("Location: ../../pages/noticias/dashboard.php");
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';

    if (empty($nome) || empty($email) || empty($senha) || empty($confirmar_senha)) {
        $erro = 'Preencha todos os campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Email inválido.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter no mínimo 6 caracteres.';
    } elseif ($senha !== $confirmar_senha) {
        $erro = 'As senhas não coincidem.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $erro = 'Este email já está cadastrado.';
        } else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
            $stmt->execute([$nome, $email, $senha_hash]);

            set_mensagem('sucesso', 'Conta criada com sucesso! Faça login.');
            header("Location: login.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Portal Games & E-Sports</title>
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

        .btn-cadastro {
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

        .btn-cadastro:hover {
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

        <div class="logo-area">
            <h1>🎮 <span>GG</span>News</h1>
            <p>Crie sua conta</p>
        </div>

        <div class="card">

            <?php if ($erro): ?>
                <div class="msg-erro"><?= escape($erro) ?></div>
            <?php endif; ?>

            <form method="POST">

                <div class="form-group">
                    <label for="nome" class="form-label">👤 Nome completo</label>
                    <input type="text" id="nome" name="nome"
                           value="<?= escape($nome ?? '') ?>"
                           class="form-input"
                           placeholder="Seu nome"
                           required>
                </div>

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
                           placeholder="Mínimo 6 caracteres"
                           required>
                </div>

                <div class="form-group">
                    <label for="confirmar_senha" class="form-label">🔒 Confirmar senha</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha"
                           class="form-input"
                           placeholder="Repita a senha"
                           required>
                </div>

                <button type="submit" class="btn-cadastro">Criar Conta</button>

            </form>

            <div class="footer-links">
                <p>Já tem conta? <a href="login.php">Faça login</a></p>
            </div>

        </div>

        <a href="../../index.php" class="back-link">← Voltar para o portal</a>

    </div>

</body>
</html>