<?php
// =====================================================
// LOGOUT
// Arquivo: pages/auth/logout.php
// =====================================================

require_once __DIR__ . '/../../includes/funcoes.php';

$_SESSION = [];
session_destroy();

header("Location: " . BASE_URL);
exit;