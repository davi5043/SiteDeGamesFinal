<?php
require_once __DIR__ . '/includes/funcoes.php';
require_once __DIR__ . '/includes/conexao.php';

// Busca categorias para o menu lateral
$categorias = get_categorias($pdo);

// Busca notícias com categoria
$stmt = $pdo->query("
    SELECT n.*, u.nome AS autor_nome, c.nome AS categoria_nome, c.icone AS categoria_icone
    FROM noticias n
    INNER JOIN usuarios u ON n.autor = u.id
    LEFT JOIN categorias c ON n.categoria_id = c.id
    ORDER BY n.data DESC
");
$noticias = $stmt->fetchAll();

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
<body class="bg-[#f8f4f0] text-[#1a1a1a] min-h-screen flex">

    <!-- ============ SIDEBAR (MENU LATERAL) ============ -->
    <aside class="sidebar w-64 fixed h-full z-40 hidden md:block p-6 overflow-y-auto">
        <div class="mb-8">
            <a href="<?= BASE_URL ?>" class="text-2xl font-bold logo">
                🎮 <span>GG</span>News
            </a>
            <p class="text-sm text-gray-500 mt-1">Portal de Games & E-Sports</p>
        </div>

        <nav class="space-y-2">
            <a href="<?= BASE_URL ?>" class="active flex items-center gap-2">
                <span>🏠</span> Início
            </a>
            <a href="<?= BASE_URL ?>pages/noticias/dashboard.php" class="flex items-center gap-2">
                <span>📋</span> Painel
            </a>
            <?php if (usuario_logado()): ?>
                <a href="<?= BASE_URL ?>pages/usuario/editar_usuario.php" class="flex items-center gap-2">
                    <span>👤</span> Minha Conta
                </a>
                <a href="<?= BASE_URL ?>pages/auth/logout.php" class="flex items-center gap-2 text-red-500 hover:text-red-600">
                    <span>🚪</span> Sair
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>pages/auth/login.php" class="flex items-center gap-2">
                    <span>🔐</span> Login
                </a>
                <a href="<?= BASE_URL ?>pages/auth/cadastro.php" class="flex items-center gap-2">
                    <span>📝</span> Cadastrar
                </a>
            <?php endif; ?>
        </nav>

        <div class="mt-8">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Categorias</h3>
            <nav class="space-y-1">
                <?php foreach ($categorias as $cat): ?>
                    <a href="<?= BASE_URL ?>pages/categorias/categoria.php?slug=<?= $cat['slug'] ?>" 
                       class="flex items-center gap-2 text-sm px-3 py-2 rounded-lg hover:bg-[#f3f0eb] transition">
                        <span><?= $cat['icone'] ?></span>
                        <?= escape($cat['nome']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
    </aside>

    <!-- ============ MAIN CONTENT ============ -->
    <div class="flex-1 md:ml-64">
        
        <!-- ============ HEADER ============ -->
        <header class="site-header sticky top-0 z-30 px-4 py-4">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <!-- Botão hamburguer (mobile) -->
                    <button id="menu-toggle" class="md:hidden text-2xl">
                        ☰
                    </button>
                    <a href="<?= BASE_URL ?>" class="text-2xl font-bold logo md:hidden">
                        🎮 <span>GG</span>News
                    </a>
                </div>

                <nav class="flex items-center gap-4">
                    <?php if (usuario_logado()): ?>
                        <span class="text-gray-600 hidden sm:inline">Olá, <?= escape(get_usuario_nome()) ?></span>
                        <a href="<?= BASE_URL ?>pages/noticias/dashboard.php" class="btn-primary text-sm px-4 py-2">
                            Painel
                        </a>
                        <a href="<?= BASE_URL ?>pages/auth/logout.php" class="text-gray-500 hover:text-gray-700 text-sm">Sair</a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>pages/auth/login.php" class="text-gray-600 hover:text-gray-900 text-sm">Login</a>
                        <a href="<?= BASE_URL ?>pages/auth/cadastro.php" class="btn-primary text-sm px-4 py-2">
                            Cadastrar
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 py-8">

            <!-- ============ HERO SECTION ============ -->
            <?php if ($destaque): ?>
            <section class="mb-12">
                <a href="<?= BASE_URL ?>pages/noticias/noticia.php?id=<?= $destaque['id'] ?>" class="block group">
                    <div class="hero-news relative rounded-2xl overflow-hidden h-[400px] flex items-end">
                        <?php if ($destaque['imagem']): ?>
                            <img src="<?= escape($destaque['imagem']) ?>" alt=""
                                 class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        <?php else: ?>
                            <div class="absolute inset-0 bg-gradient-to-br from-purple-100 to-[#e8e0d8]"></div>
                        <?php endif; ?>

                        <div class="absolute inset-0 bg-gradient-to-t from-white/90 via-white/40 to-transparent"></div>

                        <div class="relative p-8 w-full">
                            <span class="badge-cat"><?= $destaque['categoria_icone'] ?? '📰' ?> <?= escape($destaque['categoria_nome'] ?? 'Destaque') ?></span>
                            <h2 class="text-3xl md:text-4xl font-bold mt-4 group-hover:text-purple-600 transition text-[#1a1a1a]">
                                <?= escape($destaque['titulo']) ?>
                            </h2>
                            <p class="text-gray-700 mt-3 text-lg max-w-3xl">
                                <?= escape(resumo_texto($destaque['noticia'], 200)) ?>
                            </p>
                            <div class="flex items-center gap-4 mt-4 text-sm text-gray-600">
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
                <h2 class="text-2xl font-bold mb-6 border-l-4 border-purple-500 pl-4 text-[#1a1a1a]">Últimas Notícias</h2>

                <?php if (empty($demais) && !$destaque): ?>
                    <p class="text-gray-500 text-center py-12">Nenhuma notícia publicada ainda.</p>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($demais as $noticia): ?>
                            <a href="<?= BASE_URL ?>pages/noticias/noticia.php?id=<?= $noticia['id'] ?>" class="group">
                                <article class="card-news">
                                    <div class="h-48 overflow-hidden">
                                        <?php if ($noticia['imagem']): ?>
                                            <img src="<?= escape($noticia['imagem']) ?>" alt=""
                                                 class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                                        <?php else: ?>
                                            <div class="w-full h-full bg-gradient-to-br from-purple-100 to-[#e8e0d8] flex items-center justify-center">
                                                <span class="text-4xl">🎮</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="p-5">
                                        <div class="flex items-center gap-2 mb-2">
                                            <?php if ($noticia['categoria_icone']): ?>
                                                <span class="badge-cat"><?= $noticia['categoria_icone'] ?> <?= escape($noticia['categoria_nome'] ?? 'Geral') ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <h3 class="text-lg font-bold text-[#1a1a1a] group-hover:text-purple-600 transition line-clamp-2">
                                            <?= escape($noticia['titulo']) ?>
                                        </h3>
                                        <p class="text-gray-600 text-sm mt-2 line-clamp-3">
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
        <footer class="bg-white border-t border-[#ece8e2] mt-16">
            <div class="max-w-7xl mx-auto px-4 py-8 text-center">
                <p class="text-gray-500 text-sm">
                    🎮 <span class="text-purple-600 font-bold">GG</span>News &copy; <?= date('Y') ?> - Portal de Notícias de Games e E-Sports
                </p>
                <p class="text-gray-400 text-xs mt-2">Projeto acadêmico desenvolvido com PHP, MySQL e Tailwind CSS</p>
            </div>
        </footer>

    </div>

    <!-- ============ SCRIPT MOBILE MENU ============ -->
    <script>
        document.getElementById('menu-toggle')?.addEventListener('click', function() {
            const sidebar = document.querySelector('aside');
            sidebar.classList.toggle('hidden');
            sidebar.classList.toggle('fixed');
            sidebar.classList.toggle('w-full');
            sidebar.classList.toggle('md:w-64');
            sidebar.classList.toggle('z-50');
        });
    </script>

</body>
</html>