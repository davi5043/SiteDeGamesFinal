<?php
// =====================================================
// PÁGINA DE LOGIN
// Arquivo: pages/auth/login.php
// =====================================================

require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/conexao.php';

if (usuario_logado()) {
    header("Location: " . BASE_URL);
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $erro = 'Preencha todos os campos.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($senha, $usuario['senha'])) {
                session_regenerate_id(true);
                
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_foto'] = $usuario['foto'] ?? null;

                set_mensagem('sucesso', 'Bem-vindo de volta, ' . $usuario['nome'] . '!');
                header("Location: " . BASE_URL);
                exit;
            } else {
                $erro = 'Email ou senha incorretos.';
            }
        } catch (PDOException $e) {
            $erro = 'Erro ao fazer login. Tente novamente.';
            error_log("Erro no login: " . $e->getMessage());
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
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
</head>
<body class="bg-[#0f0f0f] min-h-screen flex items-center justify-center px-4">

    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <a href="<?= BASE_URL ?>" class="text-3xl font-bold text-white">
                🎮 <span class="text-purple-500">GG</span>News
            </a>
            <p class="text-gray-400 mt-2">Entre na sua conta</p>
        </div>

        <div class="bg-[#1a1a2e] rounded-xl p-8 shadow-2xl border border-gray-800">

            <?php if ($erro): ?>
                <div class="bg-red-900/50 border border-red-500 text-red-300 px-4 py-3 rounded-lg mb-6">
                    <?= escape($erro) ?>
                </div>
            <?php endif; ?>

            <?php $msg = get_mensagem(); if ($msg): ?>
                <div class="<?= $msg['tipo'] === 'sucesso' ? 'bg-green-900/50 border-green-500 text-green-300' : 'bg-red-900/50 border-red-500 text-red-300' ?> border px-4 py-3 rounded-lg mb-6">
                    <?= escape($msg['texto']) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-5">
                    <label for="email" class="block text-gray-300 text-sm font-medium mb-2">Email</label>
                    <input type="email" id="email" name="email"
                           value="<?= escape($email ?? '') ?>"
                           class="w-full px-4 py-3 bg-[#0f0f0f] border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition"
                           placeholder="seu@email.com" required>
                </div>

                <div class="mb-6">
                    <label for="senha" class="block text-gray-300 text-sm font-medium mb-2">Senha</label>
                    <input type="password" id="senha" name="senha"
                           class="w-full px-4 py-3 bg-[#0f0f0f] border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition"
                           placeholder="••••••••" required>
                </div>

                <button type="submit"
                        class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 transform hover:scale-[1.02]">
                    Entrar
                </button>
            </form>

            <p class="text-center text-gray-400 mt-6">
                Não tem conta?
                <a href="<?= BASE_URL ?>pages/auth/cadastro.php" class="text-purple-400 hover:text-purple-300 font-medium">Cadastre-se</a>
            </p>
        </div>

        <p class="text-center mt-4">
            <a href="<?= BASE_URL ?>" class="text-gray-500 hover:text-gray-300 text-sm">← Voltar para o portal</a>
        </p>
    </div>

</body>
</html>