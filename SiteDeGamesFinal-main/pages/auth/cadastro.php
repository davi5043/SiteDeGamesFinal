<?php
// =====================================================
// PÁGINA DE CADASTRO
// Arquivo: pages/auth/cadastro.php
// =====================================================

require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/conexao.php';

if (usuario_logado()) {
    redirecionar('pages/noticias/dashboard.php');
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
            redirecionar('pages/auth/login.php');
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
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
</head>
<body class="bg-[#0f0f0f] min-h-screen flex items-center justify-center px-4">

    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <a href="<?= BASE_URL ?>" class="text-3xl font-bold text-white">
                🎮 <span class="text-purple-500">GG</span>News
            </a>
            <p class="text-gray-400 mt-2">Crie sua conta</p>
        </div>

        <div class="bg-[#1a1a2e] rounded-xl p-8 shadow-2xl border border-gray-800">

            <?php if ($erro): ?>
                <div class="bg-red-900/50 border border-red-500 text-red-300 px-4 py-3 rounded-lg mb-6">
                    <?= escape($erro) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-5">
                    <label for="nome" class="block text-gray-300 text-sm font-medium mb-2">Nome completo</label>
                    <input type="text" id="nome" name="nome"
                           value="<?= escape($nome ?? '') ?>"
                           class="w-full px-4 py-3 bg-[#0f0f0f] border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition"
                           placeholder="Seu nome" required>
                </div>

                <div class="mb-5">
                    <label for="email" class="block text-gray-300 text-sm font-medium mb-2">Email</label>
                    <input type="email" id="email" name="email"
                           value="<?= escape($email ?? '') ?>"
                           class="w-full px-4 py-3 bg-[#0f0f0f] border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition"
                           placeholder="seu@email.com" required>
                </div>

                <div class="mb-5">
                    <label for="senha" class="block text-gray-300 text-sm font-medium mb-2">Senha</label>
                    <input type="password" id="senha" name="senha"
                           class="w-full px-4 py-3 bg-[#0f0f0f] border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition"
                           placeholder="Mínimo 6 caracteres" required>
                </div>

                <div class="mb-6">
                    <label for="confirmar_senha" class="block text-gray-300 text-sm font-medium mb-2">Confirmar senha</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha"
                           class="w-full px-4 py-3 bg-[#0f0f0f] border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition"
                           placeholder="Repita a senha" required>
                </div>

                <button type="submit"
                        class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 transform hover:scale-[1.02]">
                    Criar Conta
                </button>
            </form>

            <p class="text-center text-gray-400 mt-6">
                Já tem conta?
                <a href="<?= BASE_URL ?>pages/auth/login.php" class="text-purple-400 hover:text-purple-300 font-medium">Faça login</a>
            </p>
        </div>

        <p class="text-center mt-4">
            <a href="<?= BASE_URL ?>" class="text-gray-500 hover:text-gray-300 text-sm">← Voltar para o portal</a>
        </p>
    </div>

</body>
</html>