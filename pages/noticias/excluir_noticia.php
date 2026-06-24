<?php
// =====================================================
// EXCLUIR NOTÍCIA
// Arquivo: pages/noticias/excluir_noticia.php
// Descrição: Exclui uma notícia do banco de dados
//            Apenas o autor pode excluir sua própria notícia
// =====================================================

require_once __DIR__ . '/../../includes/verifica_login.php';
require_once __DIR__ . '/../../includes/conexao.php';

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    redirecionar('pages/noticias/dashboard.php');
}

// Exclui a notícia APENAS se pertence ao usuário logado
$stmt = $pdo->prepare("DELETE FROM noticias WHERE id = ? AND autor = ?");
$stmt->execute([$id, get_usuario_id()]);

if ($stmt->rowCount() > 0) {
    set_mensagem('sucesso', 'Notícia excluída com sucesso.');
} else {
    set_mensagem('erro', 'Não foi possível excluir a notícia.');
}

redirecionar('pages/noticias/dashboard.php');
