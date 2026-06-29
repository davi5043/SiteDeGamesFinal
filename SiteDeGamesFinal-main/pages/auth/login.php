<?php
// =====================================================
// PÁGINA DE LOGIN - CORRIGIDO E PADRONIZADO
// Arquivo: pages/auth/login.php
// =====================================================

// Inicia a sessão se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclui os arquivos necessários
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/conexao.php';

// Se já estiver logado, redireciona
if (usuario_logado()) {
    redirecionar('/');
    exit;
}

$erro = '';
$email = '';

// Processa o formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica token CSRF
    if (!isset($_POST['csrf_token']) || !verificar_token_csrf($_POST['csrf_token'])) {
        $erro = 'Token de segurança inválido. Tente novamente.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        // Valida campos
        if (empty($email) || empty($senha)) {
            $erro = 'Preencha todos os campos.';
        } elseif (!validar_email($email)) {
            $erro = 'Digite um email válido.';
        } elseif (!validar_senha($senha)) {
            $erro = 'A senha deve ter pelo menos 6 caracteres.';
        } else {
            try {
                // Verifica se a conexão existe
                if (!isset($conn)) {
                    throw new Exception("Conexão com o banco de dados não disponível.");
                }

                // Busca o usuário pelo email
                $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
                $stmt->execute([$email]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

                // Verifica se o usuário existe e a senha está correta
                if ($usuario && password_verify($senha, $usuario['senha'])) {
                    // Regenera o ID da sessão por segurança
                    session_regenerate_id(true);
                    
                    // Armazena os dados do usuário na sessão
                    $_SESSION['usuario_id'] = (int) $usuario['id'];
                    $_SESSION['usuario_nome'] = $usuario['nome'];
                    $_SESSION['usuario_email'] = $usuario['email'];
                    $_SESSION['usuario_foto'] = $usuario['foto'] ?? null;
                    $_SESSION['usuario_admin'] = $usuario['admin'] ?? 0;
                    
                    // Registra o login
                    error_log("Login realizado: " . $usuario['email'] . " (" . $_SERVER['REMOTE_ADDR'] . ")");
                    
                    // Mensagem de boas-vindas
                    set_mensagem('sucesso', 'Bem-vindo de volta, ' . $usuario['nome'] . '!');
                    
                    // Redireciona para o dashboard ou página anterior
                    $redirect = $_POST['redirect'] ?? '/';
                    redirecionar($redirect);
                    exit;
                } else {
                    // Log de tentativa de login falha
                    error_log("Tentativa de login falha para: " . $email . " (" . $_SERVER['REMOTE_ADDR'] . ")");
                    $erro = 'Email ou senha incorretos.';
                    
                    // Delay para evitar brute force
                    sleep(1);
                }
                
            } catch (PDOException $e) {
                error_log("Erro no login (PDO): " . $e->getMessage());
                $erro = 'Erro ao fazer login. Tente novamente mais tarde.';
            } catch (Exception $e) {
                error_log("Erro no login: " . $e->getMessage());
                $erro = 'Erro ao processar o login. Tente novamente.';
            }
        }
    }
}

// Gera token CSRF para o formulário
$csrf_token = gerar_token_csrf();

// Pega a URL de redirecionamento (se houver)
$redirect_url = $_GET['redirect'] ?? '/';
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GGNews Portal de Games & E-Sports</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
    
    <style>
        :root {
            --bg-body: #0c0c10;
            --bg-card: #1a1a2e;
            --bg-surface: #121218;
            --border: #252535;
            --accent: #7c3aed;
            --accent-light: #6d28d9;
            --text-primary: #eeeaf8;
            --text-secondary: #b8b5d0;
            --text-muted: #5e5c76;
        }

        body {
            background: var(--bg-body);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 1rem;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
        }

        .login-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 1rem;
            padding: 2rem;
            width: 100%;
            max-width: 28rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        }

        .input-field {
            background: var(--bg-body);
            border: 1px solid var(--border);
            color: var(--text-primary);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            width: 100%;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .input-field:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.2);
        }

        .input-field::placeholder {
            color: var(--text-muted);
        }

        .btn-login {
            background: var(--accent);
            color: #fff;
            font-weight: 700;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            width: 100%;
            border: none;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.15s ease;
        }

        .btn-login:hover {
            background: var(--accent-light);
            transform: scale(1.02);
        }

        .btn-login:active {
            transform: scale(0.98);
        }

        .link-purple {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
        }

        .link-purple:hover {
            color: var(--accent-light);
            text-decoration: underline;
        }

        .text-secondary {
            color: var(--text-secondary);
        }

        .text-muted {
            color: var(--text-muted);
        }

        .error-box {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .success-box {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #34d399;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .logo-text {
            font-size: 2.25rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .logo-text span {
            color: var(--accent);
        }

        .logo-sub {
            color: var(--text-muted);
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <!-- Logo -->
        <div class="text-center mb-6">
            <a href="<?= BASE_URL ?>" class="no-underline">
                <div class="logo-text">
                    🎮 <span>GG</span>News
                </div>
                <p class="logo-sub text-sm">Portal de Games & E-Sports</p>
            </a>
            <h1 class="text-xl font-semibold mt-4" style="color:var(--text-primary)">Entrar na sua conta</h1>
        </div>

        <!-- Mensagens de erro -->
        <?php if ($erro): ?>
            <div class="error-box">
                <?= escape($erro) ?>
            </div>
        <?php endif; ?>

        <!-- Mensagens flash -->
        <?php $msg = get_mensagem(); if ($msg): ?>
            <div class="<?= $msg['tipo'] === 'sucesso' ? 'success-box' : 'error-box' ?>">
                <?= escape($msg['texto']) ?>
            </div>
        <?php endif; ?>

        <!-- Formulário de login -->
        <form method="POST" action="" autocomplete="off">
            <!-- Token CSRF -->
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            
            <!-- Redirect -->
            <input type="hidden" name="redirect" value="<?= escape($redirect_url) ?>">

            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium mb-1" style="color:var(--text-secondary)">
                    Email
                </label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="<?= escape($email) ?>" 
                       class="input-field" 
                       placeholder="seu@email.com" 
                       required 
                       autofocus>
            </div>

            <!-- Senha -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-1">
                    <label for="senha" class="block text-sm font-medium" style="color:var(--text-secondary)">
                        Senha
                    </label>
                    <a href="#" class="text-xs link-purple" onclick="alert('Funcionalidade em desenvolvimento. Entre em contato com o suporte.')">
                        Esqueceu a senha?
                    </a>
                </div>
                <input type="password" 
                       id="senha" 
                       name="senha" 
                       class="input-field" 
                       placeholder="••••••••" 
                       required 
                       minlength="6">
            </div>

            <!-- Botão -->
            <button type="submit" class="btn-login">
                Entrar
            </button>
        </form>

        <!-- Link para cadastro -->
        <p class="text-center mt-6" style="color:var(--text-secondary)">
            Não tem conta?
            <a href="<?= BASE_URL ?>pages/auth/cadastro.php" class="link-purple">
                Cadastre-se
            </a>
        </p>

        <!-- Voltar -->
        <div class="text-center mt-4">
            <a href="<?= BASE_URL ?>" class="text-sm" style="color:var(--text-muted); text-decoration:none;">
                ← Voltar para o portal
            </a>
        </div>
    </div>

    <!-- Script para prevenir submissão dupla -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const btn = form.querySelector('.btn-login');
            let submitted = false;

            form.addEventListener('submit', function(e) {
                if (submitted) {
                    e.preventDefault();
                    return;
                }
                submitted = true;
                btn.textContent = 'Entrando...';
                btn.disabled = true;
                btn.style.opacity = '0.7';
            });

            // Prevenir que o navegador salve senhas em modo inseguro
            if (window.location.protocol !== 'https:') {
                console.warn('⚠️ Conexão não é HTTPS. Considere usar SSL para segurança.');
            }
        });
    </script>

</body>
</html>