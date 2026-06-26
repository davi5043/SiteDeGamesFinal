<?php
// =====================================================
// EDITAR NOTÍCIA
// Arquivo: pages/noticias/editar_noticia.php
// =====================================================

require_once __DIR__ . '/../../includes/conexao.php';
require_once __DIR__ . '/../../includes/verifica_login.php';

$categorias = get_categorias($pdo);
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    redirecionar('pages/noticias/dashboard.php');
}

$stmt = $pdo->prepare("SELECT * FROM noticias WHERE id = ?");
$stmt->execute([$id]);
$noticia = $stmt->fetch();

if (!$noticia || $noticia['autor'] !== get_usuario_id()) {
    set_mensagem('erro', 'Você não tem permissão para editar esta notícia.');
    redirecionar('pages/noticias/dashboard.php');
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $conteudo = trim($_POST['noticia'] ?? '');
    $imagem = trim($_POST['imagem'] ?? '');
    $categoria_id = intval($_POST['categoria_id'] ?? 0);

    if (empty($titulo) || empty($conteudo)) {
        $erro = 'Título e conteúdo são obrigatórios.';
    } elseif ($categoria_id <= 0) {
        $erro = 'Selecione uma categoria.';
    } else {
        $stmt = $pdo->prepare("UPDATE noticias SET titulo = ?, noticia = ?, imagem = ?, categoria_id = ? WHERE id = ? AND autor = ?");
        $stmt->execute([$titulo, $conteudo, $imagem ?: null, $categoria_id, $id, get_usuario_id()]);

        set_mensagem('sucesso', 'Notícia atualizada com sucesso!');
        redirecionar('pages/noticias/dashboard.php');
    }
} else {
    $titulo = $noticia['titulo'];
    $conteudo = $noticia['noticia'];
    $imagem = $noticia['imagem'];
    $categoria_id = $noticia['categoria_id'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Notícia - GGNews</title>
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
        <h1 class="text-3xl font-bold mb-8">Editar Notícia</h1>

        <?php if ($erro): ?>
            <div class="bg-red-900/50 border border-red-500 text-red-300 px-4 py-3 rounded-lg mb-6">
                <?= escape($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="bg-[#1a1a2e] rounded-xl p-8 border border-gray-800">
            <div class="mb-6">
                <label for="titulo" class="block text-gray-300 text-sm font-medium mb-2">Título da Notícia *</label>
                <input type="text" id="titulo" name="titulo" value="<?= escape($titulo) ?>"
                       class="w-full px-4 py-3 bg-[#0f0f0f] border border-gray-700 rounded-lg text-white focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition" required>
            </div>

            <div class="mb-6">
                <label for="categoria_id" class="block text-gray-300 text-sm font-medium mb-2">Categoria *</label>
                <select id="categoria_id" name="categoria_id"
                        class="w-full px-4 py-3 bg-[#0f0f0f] border border-gray-700 rounded-lg text-white focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition" required>
                    <option value="">Selecione uma categoria...</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $categoria_id == $cat['id'] ? 'selected' : '' ?>>
                            <?= $cat['icone'] ?> <?= escape($cat['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-6">
                <label for="noticia" class="block text-gray-300 text-sm font-medium mb-2">Conteúdo *</label>
                <textarea id="noticia" name="noticia" rows="12"
                          class="w-full px-4 py-3 bg-[#0f0f0f] border border-gray-700 rounded-lg text-white focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition resize-y" required><?= escape($conteudo) ?></textarea>
            </div>

            <div class="mb-8">
                <label for="imagem" class="block text-gray-300 text-sm font-medium mb-2">URL da Imagem (opcional)</label>
                <input type="url" id="imagem" name="imagem" value="<?= escape($imagem ?? '') ?>"
                       class="w-full px-4 py-3 bg-[#0f0f0f] border border-gray-700 rounded-lg text-white focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition">
            </div>

            <div class="flex gap-4">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-8 rounded-lg transition">
                    Salvar Alterações
                </button>
                <a href="<?= BASE_URL ?>pages/noticias/dashboard.php" class="bg-gray-700 hover:bg-gray-600 text-white py-3 px-8 rounded-lg transition inline-flex items-center">
                    Cancelar
                </a>
            </div>
        </form>
    </main>

</body>
</html>