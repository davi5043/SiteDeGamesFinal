<?php
// =====================================================
// PÁGINA INICIAL
// Arquivo: index.php (raiz do projeto)
// Descrição: Exibe todas as notícias públicas com
//            hero section e grid de cards
// =====================================================

require_once __DIR__ . '/includes/funcoes.php';
require_once __DIR__ . '/includes/conexao.php';

// Busca todas as notícias ordenadas por data (mais recentes primeiro)
// JOIN com usuarios para pegar o nome do autor
$stmt = $pdo->query("
    SELECT n.*, u.nome AS autor_nome
    FROM noticias n
    INNER JOIN usuarios u ON n.autor = u.id
    ORDER BY n.data DESC
");
$noticias = $stmt->fetchAll();

// Separa a primeira notícia (destaque) das demais
$destaque = $noticias[0] ?? null;
$demais = array_slice($noticias, 1);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GGNews - Portal de Games & E-Sports</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
</head>
<body class="bg-[#0f0f0f] text-white min-h-screen">

    <!-- ============ HEADER / NAVEGAÇÃO ============ -->
    <header class="bg-[#1a1a2e] border-b border-gray-800 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <!-- Logo -->
            <a href="<?= BASE_URL ?>" class="text-2xl font-bold">
                🎮 <span class="text-purple-500">GG</span>News
            </a>

            <!-- Navegação -->
            <nav class="flex items-center gap-4">
                <?php if (usuario_logado()): ?>
                    <span class="text-gray-300 hidden sm:inline">Olá, <?= escape(get_usuario_nome()) ?></span>
                    <a href="<?= BASE_URL ?>pages/noticias/dashboard.php" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        Painel
                    </a>
                    <a href="<?= BASE_URL ?>pages/auth/logout.php" class="text-gray-400 hover:text-white text-sm transition">Sair</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>pages/auth/login.php" class="text-gray-300 hover:text-white text-sm transition">Login</a>
                    <a href="<?= BASE_URL ?>pages/auth/cadastro.php" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        Cadastrar
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-8">

        <!-- ============ HERO SECTION (Notícia Destaque) ============ -->
        <?php if ($destaque): ?>
        <section class="mb-12">
            <a href="<?= BASE_URL ?>pages/noticias/noticia.php?id=<?= $destaque['id'] ?>" class="block group">
                <div class="relative rounded-2xl overflow-hidden bg-[#1a1a2e] h-[400px] flex items-end">
                    <?php if ($destaque['imagem']): ?>
                        <img src="<?= escape($destaque['imagem']) ?>" alt=""
                             class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    <?php else: ?>
                        <div class="absolute inset-0 bg-gradient-to-br from-purple-900 to-[#1a1a2e]"></div>
                    <?php endif; ?>

                    <!-- Overlay escuro para legibilidade -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>

                    <!-- Conteúdo sobre a imagem -->
                    <div class="relative p-8 w-full">
                        <span class="bg-purple-600 text-white text-xs font-bold px-3 py-1 rounded-full uppercase">Destaque</span>
                        <h2 class="text-3xl md:text-4xl font-bold mt-4 group-hover:text-purple-400 transition">
                            <?= escape($destaque['titulo']) ?>
                        </h2>
                        <p class="text-gray-300 mt-3 text-lg max-w-3xl">
                            <?= escape(resumo_texto($destaque['noticia'], 200)) ?>
                        </p>
                        <div class="flex items-center gap-4 mt-4 text-sm text-gray-400">
                            <span>Por <?= escape($destaque['autor_nome']) ?></span>
                            <span>•</span>
                            <span><?= formatar_data($destaque['data']) ?></span>
                        </div>
                    </div>
                </div>
            </a>
        </section>
        <?php endif; ?>

        <!-- ============ GRID DE NOTÍCIAS ============ -->
        <section>
            <h2 class="text-2xl font-bold mb-6 border-l-4 border-purple-500 pl-4">Últimas Notícias</h2>

            <?php if (empty($demais) && !$destaque): ?>
                <p class="text-gray-400 text-center py-12">Nenhuma notícia publicada ainda.</p>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($demais as $noticia): ?>
                        <a href="<?= BASE_URL ?>pages/noticias/noticia.php?id=<?= $noticia['id'] ?>" class="group">
                            <article class="bg-[#1a1a2e] rounded-xl overflow-hidden border border-gray-800 hover:border-purple-500/50 transition duration-300 hover:transform hover:scale-[1.02]">
                                <!-- Imagem do card -->
                                <div class="h-48 overflow-hidden">
                                    <?php if ($noticia['imagem']): ?>
                                        <img src="<?= escape($noticia['imagem']) ?>" alt=""
                                             class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-gradient-to-br from-purple-900/50 to-[#0f0f0f] flex items-center justify-center">
                                            <span class="text-4xl">🎮</span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Conteúdo do card -->
                                <div class="p-5">
                                    <h3 class="text-lg font-bold text-white group-hover:text-purple-400 transition line-clamp-2">
                                        <?= escape($noticia['titulo']) ?>
                                    </h3>
                                    <p class="text-gray-400 text-sm mt-2 line-clamp-3">
                                        <?= escape(resumo_texto($noticia['noticia'], 120)) ?>
                                    </p>
                                    <div class="flex items-center justify-between mt-4 text-xs text-gray-500">
                                        <span><?= escape($noticia['autor_nome']) ?></span>
                                        <span><?= formatar_data($noticia['data']) ?></span>
                                    </div>
                                </div>
                            </article>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </main>

    <!-- ============ FOOTER ============ -->
    <footer class="bg-[#1a1a2e] border-t border-gray-800 mt-16">
        <div class="max-w-7xl mx-auto px-4 py-8 text-center">
            <p class="text-gray-400 text-sm">
                🎮 <span class="text-purple-400 font-bold">GG</span>News &copy; <?= date('Y') ?> - Portal de Notícias de Games e E-Sports
            </p>
            <p class="text-gray-600 text-xs mt-2">Projeto acadêmico desenvolvido com PHP, MySQL e Tailwind CSS</p>
        </div>
    </footer>

</body>
</html>
