<?php
// =====================================================
// NOVA NOTÍCIA
// Arquivo: pages/noticias/nova_noticia.php
// Descrição: Formulário para cadastro de nova notícia
// =====================================================

require_once __DIR__ . '/../../includes/verifica_login.php';
require_once __DIR__ . '/../../includes/conexao.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $conteudo = trim($_POST['noticia'] ?? '');
    $imagem = trim($_POST['imagem'] ?? '');

    if (empty($titulo) || empty($conteudo)) {
        $erro = 'Título e conteúdo são obrigatórios.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO noticias (titulo, noticia, autor, imagem) VALUES (?, ?, ?, ?)");
        $stmt->execute([$titulo, $conteudo, get_usuario_id(), $imagem ?: null]);

        set_mensagem('sucesso', 'Notícia publicada com sucesso!');
        redirecionar('pages/noticias/dashboard.php');
    }
    <?php
require_once __DIR__ . '/../../includes/verifica_login.php';
require_once __DIR__ . '/../../includes/conexao.php';

$categorias = get_categorias($pdo);

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $conteudo = trim($_POST['noticia'] ?? '');
    $imagem = trim($_POST['imagem'] ?? '');
    $
}
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Notícia - GGNews</title>
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

    <main class="max-w-3xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">Publicar Nova Notícia</h1>

        <?php if ($erro): ?>
            <div class="bg-red-900/50 border border-red-500 text-red-300 px-4 py-3 rounded-lg mb-6">
                <?= escape($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="bg-[#1a1a2e] rounded-xl p-8 border border-gray-800">
            <div class="mb-6">
                <label for="titulo" class="block text-gray-300 text-sm font-medium mb-2">Título da Notícia *</label>
                <input type="text" id="titulo" name="titulo"
                       value="<?= escape($titulo ?? '') ?>"
                       class="w-full px-4 py-3 bg-[#0f0f0f] border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition"
                       placeholder="Ex: LOUD vence campeonato mundial..." required>
            </div>

            <div class="mb-6">
                <label for="noticia" class="block text-gray-300 text-sm font-medium mb-2">Conteúdo *</label>
                <textarea id="noticia" name="noticia" rows="12"
                          class="w-full px-4 py-3 bg-[#0f0f0f] border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition resize-y"
                          placeholder="Escreva o conteúdo completo da notícia..." required><?= escape($conteudo ?? '') ?></textarea>
            </div>

            <div class="mb-8">
                <label for="imagem" class="block text-gray-300 text-sm font-medium mb-2">URL da Imagem (opcional)</label>
                <input type="url" id="imagem" name="imagem"
                       value="<?= escape($imagem ?? '') ?>"
                       class="w-full px-4 py-3 bg-[#0f0f0f] border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition"
                       placeholder="https://exemplo.com/imagem.jpg">
                <p class="text-gray-500 text-xs mt-2">Cole a URL de uma imagem da internet ou deixe em branco.</p>
            </div>

            <div class="flex gap-4">
                <button type="submit"
                        class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-8 rounded-lg transition duration-200">
                    Publicar Notícia
                </button>
                <a href="<?= BASE_URL ?>pages/noticias/dashboard.php" class="bg-gray-700 hover:bg-gray-600 text-white py-3 px-8 rounded-lg transition duration-200 inline-flex items-center">
                    Cancelar
                </a>
            </div>
        </form>
    </main>

</body>
</html>
