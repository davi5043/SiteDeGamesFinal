<?php
// =====================================================
// EXCLUIR NOTÍCIA
// Arquivo: pages/noticias/excluir_noticia.php
// =====================================================

require_once __DIR__ . '/../../includes/conexao.php';
require_once __DIR__ . '/../../includes/verifica_login.php';

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: dashboard.php");
    exit;
}

// Verifica se a notícia existe e pertence ao usuário
$stmt = $pdo->prepare("SELECT id FROM noticias WHERE id = ? AND autor = ?");
$stmt->execute([$id, get_usuario_id()]);
$noticia = $stmt->fetch();

if (!$noticia) {
    set_mensagem('erro', 'Notícia não encontrada ou você não tem permissão para excluí-la.');
    header("Location: dashboard.php");
    exit;
}

// Exclui a notícia
$stmt = $pdo->prepare("DELETE FROM noticias WHERE id = ? AND autor = ?");
$stmt->execute([$id, get_usuario_id()]);

if ($stmt->rowCount() > 0) {
    set_mensagem('sucesso', 'Notícia excluída com sucesso.');
} else {
    set_mensagem('erro', 'Não foi possível excluir a notícia.');
}

header("Location: dashboard.php");
exit;