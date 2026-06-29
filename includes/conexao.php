<?php
// =====================================================
// CONEXÃO COM O BANCO DE DADOS
// =====================================================

$host = 'localhost';
$dbname = 'portal_games';
$usuario = 'root';
$senha_db = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $usuario,
        $senha_db,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Erro ao conectar com o banco de dados: " . $e->getMessage());
}

// VERIFICA SE O PDO FOI CRIADO
if (!isset($pdo)) {
    die("Erro: PDO não foi inicializado corretamente.");
}