
<?php
// =====================================================
// HELPER: Comentários e Feedbacks
// Arquivo: includes/comentarios.php
// =====================================================

/**
 * Busca todos os comentários de uma notícia
 */
function get_comentarios($pdo, $noticia_id) {
    $stmt = $pdo->prepare("
        SELECT c.*, u.nome, u.foto 
        FROM comentarios c
        INNER JOIN usuarios u ON c.usuario_id = u.id
        WHERE c.noticia_id = ?
        ORDER BY c.data DESC
    ");
    $stmt->execute([$noticia_id]);
    return $stmt->fetchAll();
}

/**
 * Conta os comentários de uma notícia
 */
function contar_comentarios($pdo, $noticia_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM comentarios WHERE noticia_id = ?");
    $stmt->execute([$noticia_id]);
    return $stmt->fetchColumn();
}

/**
 * Adiciona um comentário
 */
function adicionar_comentario($pdo, $noticia_id, $usuario_id, $conteudo) {
    $stmt = $pdo->prepare("
        INSERT INTO comentarios (noticia_id, usuario_id, conteudo) 
        VALUES (?, ?, ?)
    ");
    return $stmt->execute([$noticia_id, $usuario_id, $conteudo]);
}

/**
 * Exclui um comentário (apenas o autor ou admin)
 */
function excluir_comentario($pdo, $comentario_id, $usuario_id) {
    $stmt = $pdo->prepare("
        DELETE FROM comentarios 
        WHERE id = ? AND usuario_id = ?
    ");
    return $stmt->execute([$comentario_id, $usuario_id]);
}