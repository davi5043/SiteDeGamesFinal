<?php
// =====================================================
// CONEXÃO COM O BANCO DE DADOS
// Arquivo: includes/conexao.php
// Descrição: Estabelece a conexão PDO com o MySQL
// =====================================================

// Configurações do banco de dados
// Altere estas variáveis conforme seu ambiente (XAMPP, WAMP, etc.)
$host = 'localhost';       // Endereço do servidor MySQL
$dbname = 'portal_games';  // Nome do banco de dados
$usuario = 'root';         // Usuário do MySQL (padrão XAMPP: root)
$senha_db = '';             // Senha do MySQL (padrão XAMPP: vazia)

try {
    // Cria a conexão usando PDO (PHP Data Objects)
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
