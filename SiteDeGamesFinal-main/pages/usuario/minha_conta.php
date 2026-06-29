<?php

require_once __DIR__ . '/../../includes/conexao.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se o usuário estiver logado, vai para editar perfil
if (isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id'])) {
    header("Location: editar_perfil.php");
    exit;
}

// Caso contrário, vai para a página de login
header("Location: " . BASE_URL . "login.php");
exit;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<a href="<?= BASE_URL ?>pages/usuario/minha_conta.php">
    Minha Conta
</a>
<body>
    
</body>
</html>