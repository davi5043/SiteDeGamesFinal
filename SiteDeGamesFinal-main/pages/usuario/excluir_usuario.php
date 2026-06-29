<?php
// =====================================================
// EXCLUIR USUÁRIO
// Arquivo: pages/usuario/excluir_usuario.php
// =====================================================

require_once __DIR__ . '/../../includes/conexao.php';
require_once __DIR__ . '/../../includes/verifica_login.php';

$stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
$stmt->execute([get_usuario_id()]);

$_SESSION = [];
session_destroy();

header("Location: " . BASE_URL);
exit;