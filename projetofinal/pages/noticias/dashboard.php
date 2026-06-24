<?php
// =====================================================
// DASHBOARD (PAINEL DO USUÁRIO)
// Arquivo: pages/noticias/dashboard.php
// Descrição: Painel principal do usuário logado
// =====================================================

require_once __DIR__ . '/../../includes/verifica_login.php';
require_once __DIR__ . '/../../includes/conexao.php';

// Busca as notícias do usuário logado
$stmt = $pdo->prepare("SELECT * FROM noticias WHERE autor = ? ORDER BY data DESC");
$stmt->execute([get_usuario_id()]);
$minhas_noticias = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - GGNews</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
</head>
<body class="bg-[#0f0f0f] text-white min-h-screen">

    <header class="bg-[#1a1a2e] border-b border-gray-800">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="<?= BASE_URL ?>" class="text-2xl font-bold">🎮 <span class="text-purple-500">GG</span>News</a>
            <nav class="flex items-center gap-4">
                <span class="text-gray-300 hidden sm:inline"><?= escape(get_usuario_nome()) ?></span>
                <a href="<?= BASE_URL ?>pages/usuario/editar_usuario.php" class="text-gray-400 hover:text-white text-sm transition">Minha Conta</a>
                <a href="<?= BASE_URL ?>pages/auth/logout.php" class="text-gray-400 hover:text-white text-sm transition">Sair</a>
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-8">

        <?php $msg = get_mensagem(); if ($msg): ?>
            <div class="<?= $msg['tipo'] === 'sucesso' ? 'bg-green-900/50 border-green-500 text-green-300' : 'bg-red-900/50 border-red-500 text-red-300' ?> border px-4 py-3 rounded-lg mb-6">
                <?= escape($msg['texto']) ?>
            </div>
        <?php endif; ?>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold">Meu Painel</h1>
                <p class="text-gray-400 mt-1">Gerencie suas notícias publicadas</p>
            </div>
            <a href="<?= BASE_URL ?>pages/noticias/nova_noticia.php"
               class="mt-4 sm:mt-0 bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium transition inline-block text-center">
                + Nova Notícia
            </a>
        </div>

        <?php if (empty($minhas_noticias)): ?>
            <div class="bg-[#1a1a2e] rounded-xl p-12 text-center border border-gray-800">
                <p class="text-gray-400 text-lg">Você ainda não publicou nenhuma notícia.</p>
                <a href="<?= BASE_URL ?>pages/noticias/nova_noticia.php" class="text-purple-400 hover:text-purple-300 mt-2 inline-block">Publicar minha primeira notícia →</a>
            </div>
        <?php else: ?>
            <div class="bg-[#1a1a2e] rounded-xl border border-gray-800 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-[#0f0f0f]">
                        <tr>
                            <th class="text-left px-6 py-4 text-sm font-medium text-gray-400">Título</th>
                            <th class="text-left px-6 py-4 text-sm font-medium text-gray-400 hidden md:table-cell">Data</th>
                            <th class="text-right px-6 py-4 text-sm font-medium text-gray-400">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        <?php foreach ($minhas_noticias as $noticia): ?>
                            <tr class="hover:bg-[#0f0f0f]/50 transition">
                                <td class="px-6 py-4">
                                    <a href="<?= BASE_URL ?>pages/noticias/noticia.php?id=<?= $noticia['id'] ?>" class="text-white hover:text-purple-400 font-medium transition">
                                        <?= escape($noticia['titulo']) ?>
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-400 hidden md:table-cell">
                                    <?= formatar_data($noticia['data']) ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="<?= BASE_URL ?>pages/noticias/editar_noticia.php?id=<?= $noticia['id'] ?>" class="text-blue-400 hover:text-blue-300 text-sm mr-3">Editar</a>
                                    <a href="<?= BASE_URL ?>pages/noticias/excluir_noticia.php?id=<?= $noticia['id'] ?>" class="text-red-400 hover:text-red-300 text-sm"
                                       onclick="return confirm('Tem certeza que deseja excluir esta notícia?')">Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </main>

</body>
</html>
