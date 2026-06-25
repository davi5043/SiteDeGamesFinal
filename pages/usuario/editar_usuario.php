<?php

require_once __DIR__ . '/../../includes/verifica_login.php';
require_once __DIR__ . '/../../includes/conexao.php';

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([get_usuario_id()]);
$usuario = $stmt->fetch();

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';

    // FOTO
    $foto = $usuario['foto'];

    if (!empty($_FILES['foto']['name'])) {

        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nomeArquivo = uniqid() . '.' . $ext;
        $destino = __DIR__ . '/../../uploads/' . $nomeArquivo;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
            $foto = $nomeArquivo;
        }
    }

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

            $stmt->execute([
                $nome,
                $username,
                $email,
                $foto,
                get_usuario_id()
            ]);

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
    $username = $usuario['username'] ?? '';
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
        <a href="<?= BASE_URL ?>pages/noticias/dashboard.php" class="text-gray-400 hover:text-white text-sm">← Voltar</a>
    </div>
</header>

<main class="max-w-xl mx-auto px-4 py-8">

<h1 class="text-3xl font-bold mb-8">Editar Perfil</h1>

<?php if ($erro): ?>
<div class="bg-red-900/50 border border-red-500 text-red-300 px-4 py-3 rounded-lg mb-6">
    <?= escape($erro) ?>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data"
      class="bg-[#1a1a2e] rounded-xl p-8 border border-gray-800">

    <!-- FOTO -->
    <div class="mb-6 text-center">
        <?php if (!empty($usuario['foto'])): ?>
            <img src="<?= BASE_URL ?>uploads/<?= $usuario['foto'] ?>"
                 class="w-24 h-24 rounded-full mx-auto object-cover border border-gray-700">
        <?php else: ?>
            <div class="w-24 h-24 rounded-full mx-auto bg-gray-700"></div>
        <?php endif; ?>

        <input type="file" name="foto"
               class="mt-4 text-sm text-gray-300">
    </div>

    <!-- NOME -->
    <div class="mb-5">
        <label class="text-gray-300 text-sm">Nome</label>
        <input type="text" name="nome" value="<?= escape($nome) ?>"
               class="w-full px-4 py-3 bg-[#0f0f0f] border border-gray-700 rounded-lg">
    </div>

    <!-- USERNAME -->
    <div class="mb-5">
        <label class="text-gray-300 text-sm">Username</label>
        <input type="text" name="username" value="<?= escape($username) ?>"
               class="w-full px-4 py-3 bg-[#0f0f0f] border border-gray-700 rounded-lg">
    </div>

    <!-- EMAIL -->
    <div class="mb-5">
        <label class="text-gray-300 text-sm">Email</label>
        <input type="email" name="email" value="<?= escape($email) ?>"
               class="w-full px-4 py-3 bg-[#0f0f0f] border border-gray-700 rounded-lg">
    </div>

    <hr class="border-gray-800 my-6">

    <p class="text-gray-400 text-sm mb-4">Alterar senha (opcional)</p>

    <!-- SENHA ATUAL -->
    <div class="mb-5">
        <label class="text-gray-300 text-sm">Senha atual</label>
        <input type="password" name="senha_atual"
               class="w-full px-4 py-3 bg-[#0f0f0f] border border-gray-700 rounded-lg">
    </div>

    <!-- NOVA SENHA -->
    <div class="mb-8">
        <label class="text-gray-300 text-sm">Nova senha</label>
        <input type="password" name="nova_senha"
               class="w-full px-4 py-3 bg-[#0f0f0f] border border-gray-700 rounded-lg">
    </div>

    <button class="w-full bg-purple-600 hover:bg-purple-700 py-3 rounded-lg font-bold">
        Salvar Alterações
    </button>

</form>

</main>

</body>
</html>