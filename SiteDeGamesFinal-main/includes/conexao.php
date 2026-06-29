<?php
// =====================================================
// CONEXÃO COM O BANCO DE DADOS
// =====================================================

$host = 'localhost';
$dbname = 'portal_games';
$usuario = 'root';
$senha_db = '';

try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $usuario,
        $senha_db,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    // Adiciona também um alias $pdo para compatibilidade com código antigo
    $pdo = $conn;
    
} catch (PDOException $e) {
    // Em vez de die(), podemos mostrar uma mensagem mais amigável
    // e registrar o erro para debug
    error_log("Erro ao conectar com o banco de dados: " . $e->getMessage());
    die("Erro ao conectar com o banco de dados. Por favor, tente novamente mais tarde.");
}

// VERIFICA SE A CONEXÃO FOI CRIADA
if (!isset($conn)) {
    die("Erro: Conexão com o banco de dados não foi inicializada corretamente.");
}

// Função auxiliar para obter a conexão (opcional)
function getConexao() {
    global $conn;
    return $conn;
}

// Função auxiliar para compatibilidade com PDO (se precisar)
function getPDO() {
    global $pdo;
    return $pdo;
}

?>