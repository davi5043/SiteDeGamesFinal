<?php
// =====================================================
// PÁGINA INDIVIDUAL DA NOTÍCIA
// Arquivo: pages/noticias/noticia.php
// Descrição: Exibe o conteúdo completo de uma notícia
// =====================================================

require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/conexao.php';

// Pega o ID da notícia pela URL (ex: noticia.php?id=1)
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    redirecionar('index.php');
}

// Busca a notícia no banco com o nome do autor
$stmt = $pdo->prepare("
    SELECT n.*, u.nome AS autor_nome
    FROM noticias n
    INNER JOIN usuarios u ON n.autor = u.id
    WHERE n.id = ?
");
$stmt->execute([$id]);
$noticia = $stmt->fetch();

if (!$noticia) {
    set_mensagem('erro', 'Notícia não encontrada.');
    redirecionar('index.php');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape($noticia['titulo']) ?> - GGNews</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
</head>
<body class="bg-[#0f0f0f] text-white min-h-screen">

    <!-- Header -->
    <header class="bg-[#1a1a2e] border-b border-gray-800 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="<?= BASE_URL ?>" class="text-2xl font-bold">🎮 <span class="text-purple-500">GG</span>News</a>
            <nav class="flex items-center gap-4">
                <?php if (usuario_logado()): ?>
                    <a href="<?= BASE_URL ?>pages/noticias/dashboard.php" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">Painel</a>
                    <a href="<?= BASE_URL ?>pages/auth/logout.php" class="text-gray-400 hover:text-white text-sm transition">Sair</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>pages/auth/login.php" class="text-gray-300 hover:text-white text-sm transition">Login</a>
                    <a href="<?= BASE_URL ?>pages/auth/cadastro.php" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">Cadastrar</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-8">

        <a href="<?= BASE_URL ?>" class="text-purple-400 hover:text-purple-300 text-sm mb-6 inline-block">← Voltar para as notícias</a>

        <?php if ($noticia['imagem']): ?>
            <div class="rounded-xl overflow-hidden mb-8 h-[400px]">
                <img src="<?= escape($noticia['imagem']) ?>" alt="<?= escape($noticia['titulo']) ?>"
                     class="w-full h-full object-cover">
            </div>
        <?php endif; ?>

        <h1 class="text-3xl md:text-4xl font-bold leading-tight"><?= escape($noticia['titulo']) ?></h1>

        <div class="flex items-center gap-4 mt-4 text-sm text-gray-400 border-b border-gray-800 pb-6 mb-8">
            <span class="bg-purple-600/20 text-purple-400 px-3 py-1 rounded-full text-xs font-medium">Games & E-Sports</span>
            <span>Por <strong class="text-gray-300"><?= escape($noticia['autor_nome']) ?></strong></span>
            <span>•</span>
            <span><?= formatar_data($noticia['data']) ?></span>
        </div>

        <article class="prose prose-invert max-w-none text-gray-300 leading-relaxed text-lg whitespace-pre-line">
<?= escape($noticia['noticia']) ?>
        </article>

    </main>

    <footer class="bg-[#1a1a2e] border-t border-gray-800 mt-16">
        <div class="max-w-7xl mx-auto px-4 py-8 text-center">
            <p class="text-gray-400 text-sm">🎮 <span class="text-purple-400 font-bold">GG</span>News &copy; <?= date('Y') ?> - Portal de Notícias de Games e E-Sports</p>
        </div>
    </footer>

</body>
</html>
