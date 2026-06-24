<?php
// =====================================================
// VERIFICAÇÃO DE LOGIN
// Arquivo: includes/verifica_login.php
// Descrição: Protege páginas que exigem autenticação
// Inclua este arquivo no topo de páginas restritas
// =====================================================

require_once __DIR__ . '/funcoes.php';

// Se o usuário NÃO está logado, redireciona para o login
if (!usuario_logado()) {
    set_mensagem('erro', 'Você precisa estar logado para acessar esta página.');
    redirecionar('pages/auth/login.php');
}
