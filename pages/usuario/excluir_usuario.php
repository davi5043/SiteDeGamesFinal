<?php
// =====================================================
// EXCLUIR USUÁRIO
// Arquivo: pages/usuario/excluir_usuario.php
// Descrição: Exclui a conta do usuário logado
//            As notícias são excluídas automaticamente (CASCADE)
// =====================================================

require_once __DIR__ . '/../../includes/verifica_login.php';
require_once __DIR__ . '/../../includes/conexao.php';

// Exclui o usuário do banco (CASCADE remove suas notícias também)
$stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
$stmt->execute([get_usuario_id()]);

// Destroi a sessão
$_SESSION = [];
session_destroy();

// Redireciona para a página inicial
header("Location: " . BASE_URL);
exit;
