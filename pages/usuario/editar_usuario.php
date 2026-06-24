<?php
// =====================================================
// EDITAR USUÁRIO
// Arquivo: pages/usuario/editar_usuario.php
// Descrição: Permite ao usuário editar seus dados
// =====================================================

require_once __DIR__ . '/../../includes/verifica_login.php';
require_once __DIR__ . '/../../includes/conexao.php';

// Busca os dados atuais do usuário
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([get_usuario_id()]);
$usuario = $stmt->fetch();

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';

    if (empty($nome) || empty($email)) {
        $erro = 'Nome e email são obrigatórios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Email inválido.';
    } else {
        // Verifica se o email já é usado por outro usuário
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $stmt->execute([$email, get_usuario_id()]);
        if ($stmt->fetch()) {
            $erro = 'Este email já está em uso por outro usuário.';
        } else {
            // Atualiza nome e email
            $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ? WHERE id = ?");
            $stmt->execute([$nome, $email, get_usuario_id()]);

            // Se preencheu a nova senha, atualiza também
            if (!empty($nova_senha)) {
                if (empty($senha_atual)) {
                    $erro = 'Informe a senha atual para alterar a senha.';
                } elseif (!password_verify($senha_atual, $usuario['senha'])) {
                    $erro = 'Senha atual incorreta.';
                } elseif (strlen($nova_senha) < 6) {
                    $erro = 'A nova senha deve ter no mínimo 6 caracteres.';
                } else {
                    $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
                    $stmt->execute([$senha_hash, get_usuario_id()]);
                }
            }

            if (empty($erro)) {
                $_SESSION['usuario_nome'] = $nome;
                set_mensagem('sucesso', 'Dados atualizados com sucesso!');
                redirecionar('pages/noticias/dashboard.php');
            }
        }
    }
} else {
    $nome = $usuario['nome'];
    $email = $usuario['email'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Conta - GGNews</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
</head>
<body class="bg-[#0f0f0f] text-white min-h-screen">

    <header class="bg-[#1a1a2e] border-b border-gray-800">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="<?= BASE_URL ?>" class="text-2xl font-bold">🎮 <span class="text-purple-500">GG</span>News</a>
            <a href="<?= BASE_URL ?>pages/noticias/dashboard.php" class="text-gray-400 hover:text-white text-sm transition">← Voltar ao Painel</a>
        </div>
    </header>

    <main class="max-w-xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">Minha Conta</h1>

        <?php if ($erro): ?>
            <div class="bg-red-900/50 border border-red-500 text-red-300 px-4 py-3 rounded-lg mb-6">
                <?= escape($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="bg-[#1a1a2e] rounded-xl p-8 border border-gray-800">
            <div class="mb-5">
                <label for="nome" class="block text-gray-300 text-sm font-medium mb-2">Nome</label>
                <input type="text" id="nome" name="nome" value="<?= escape($nome) ?>"
                       class="w-full px-4 py-3 bg-[#0f0f0f] border border-gray-700 rounded-lg text-white focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition" required>
            </div>

            <div class="mb-5">
                <label for="email" class="block text-gray-300 text-sm font-medium mb-2">Email</label>
                <input type="email" id="email" name="email" value="<?= escape($email) ?>"
                       class="w-full px-4 py-3 bg-[#0f0f0f] border border-gray-700 rounded-lg text-white focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition" required>
            </div>

            <hr class="border-gray-800 my-6">
            <p class="text-gray-400 text-sm mb-4">Preencha abaixo apenas se quiser alterar a senha:</p>

            <div class="mb-5">
                <label for="senha_atual" class="block text-gray-300 text-sm font-medium mb-2">Senha atual</label>
                <input type="password" id="senha_atual" name="senha_atual"
                       class="w-full px-4 py-3 bg-[#0f0f0f] border border-gray-700 rounded-lg text-white focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition">
            </div>

            <div class="mb-8">
                <label for="nova_senha" class="block text-gray-300 text-sm font-medium mb-2">Nova senha</label>
                <input type="password" id="nova_senha" name="nova_senha"
                       class="w-full px-4 py-3 bg-[#0f0f0f] border border-gray-700 rounded-lg text-white focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition"
                       placeholder="Mínimo 6 caracteres">
            </div>

            <button type="submit"
                    class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-lg transition">
                Salvar Alterações
            </button>
        </form>

        <!-- Excluir conta -->
        <div class="mt-8 bg-red-900/20 border border-red-800 rounded-xl p-6">
            <h3 class="text-red-400 font-bold mb-2">Zona de Perigo</h3>
            <p class="text-gray-400 text-sm mb-4">Ao excluir sua conta, todas as suas notícias também serão removidas. Esta ação não pode ser desfeita.</p>
            <a href="<?= BASE_URL ?>pages/usuario/excluir_usuario.php"
               class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition"
               onclick="return confirm('TEM CERTEZA? Esta ação excluirá sua conta e todas as suas notícias permanentemente!')">
                Excluir Minha Conta
            </a>
        </div>
    </main>

</body>
</html>
