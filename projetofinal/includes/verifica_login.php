<?php
// =====================================================
// VERIFICAÇÃO DE LOGIN
// =====================================================

require_once __DIR__ . '/funcoes.php';

if (!usuario_logado()) {
    set_mensagem('erro', 'Você precisa estar logado para acessar esta página.');
    redirecionar('pages/auth/login.php');
}