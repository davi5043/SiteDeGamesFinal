<?php
// =====================================================
// PÁGINA DE CADASTRO - CORRIGIDO E PADRONIZADO
// Arquivo: pages/auth/cadastro.php
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
$nome = '';
$email = '';

// Processa o formulário de cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica token CSRF
    if (!isset($_POST['csrf_token']) || !verificar_token_csrf($_POST['csrf_token'])) {
        $erro = 'Token de segurança inválido. Tente novamente.';
    } else {
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $confirmar_senha = $_POST['confirmar_senha'] ?? '';

        // Validações
        if (empty($nome) || empty($email) || empty($senha) || empty($confirmar_senha)) {
            $erro = 'Preencha todos os campos.';
        } elseif (strlen($nome) < 2) {
            $erro = 'O nome deve ter pelo menos 2 caracteres.';
        } elseif (!validar_email($email)) {
            $erro = 'Digite um email válido.';
        } elseif (!validar_senha($senha)) {
            $erro = 'A senha deve ter no mínimo 6 caracteres.';
        } elseif ($senha !== $confirmar_senha) {
            $erro = 'As senhas não coincidem.';
        } else {
            try {
                // Verifica se a conexão existe
                if (!isset($conn)) {
                    throw new Exception("Conexão com o banco de dados não disponível.");
                }

                // Verifica se o email já está cadastrado
                $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
                $stmt->execute([$email]);

                if ($stmt->fetch()) {
                    $erro = 'Este email já está cadastrado.';
                } else {
                    // Hash da senha
                    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                    
                    // Insere o usuário
                    $stmt = $conn->prepare("
                        INSERT INTO usuarios (nome, email, senha, data_cadastro) 
                        VALUES (?, ?, ?, NOW())
                    ");
                    $stmt->execute([$nome, $email, $senha_hash]);

                    // Registra o cadastro
                    error_log("Novo usuário cadastrado: " . $email . " (" . $_SERVER['REMOTE_ADDR'] . ")");

                    // Mensagem de sucesso
                    set_mensagem('sucesso', 'Conta criada com sucesso! Faça login para começar.');
                    
                    // Redireciona para o login
                    redirecionar('pages/auth/login.php');
                    exit;
                }
            } catch (PDOException $e) {
                error_log("Erro no cadastro (PDO): " . $e->getMessage());
                $erro = 'Erro ao cadastrar. Tente novamente mais tarde.';
            } catch (Exception $e) {
                error_log("Erro no cadastro: " . $e->getMessage());
                $erro = 'Erro ao processar o cadastro. Tente novamente.';
            }
        }
    }
}

// Gera token CSRF para o formulário
$csrf_token = gerar_token_csrf();
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - GGNews Portal de Games & E-Sports</title>
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
            padding: 1rem;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
        }

        .cadastro-card {
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

        .input-field.error {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);
        }

        .input-field.success {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
        }

        .btn-cadastrar {
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

        .btn-cadastrar:hover {
            background: var(--accent-light);
            transform: scale(1.02);
        }

        .btn-cadastrar:active {
            transform: scale(0.98);
        }

        .btn-cadastrar:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
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

        .senha-forca {
            height: 4px;
            border-radius: 2px;
            margin-top: 0.5rem;
            background: var(--border);
            transition: width 0.3s ease, background 0.3s ease;
            width: 0%;
        }

        .senha-forca.fraca { width: 33%; background: #ef4444; }
        .senha-forca.media { width: 66%; background: #f59e0b; }
        .senha-forca.forte { width: 100%; background: #10b981; }

        .senha-dica {
            font-size: 0.75rem;
            margin-top: 0.25rem;
            color: var(--text-muted);
        }

        .senha-dica.valida {
            color: #10b981;
        }

        .senha-dica.invalida {
            color: #ef4444;
        }
    </style>
</head>
<body>

    <div class="cadastro-card">
        <!-- Logo -->
        <div class="text-center mb-6">
            <a href="<?= BASE_URL ?>" class="no-underline">
                <div class="logo-text">
                    🎮 <span>GG</span>News
                </div>
                <p class="logo-sub text-sm">Portal de Games & E-Sports</p>
            </a>
            <h1 class="text-xl font-semibold mt-4" style="color:var(--text-primary)">Criar nova conta</h1>
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

        <!-- Formulário de cadastro -->
        <form method="POST" action="" id="formCadastro" autocomplete="off" novalidate>
            <!-- Token CSRF -->
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

            <!-- Nome -->
            <div class="mb-4">
                <label for="nome" class="block text-sm font-medium mb-1" style="color:var(--text-secondary)">
                    Nome completo
                </label>
                <input type="text" 
                       id="nome" 
                       name="nome" 
                       value="<?= escape($nome) ?>" 
                       class="input-field" 
                       placeholder="Seu nome completo" 
                       required 
                       minlength="2"
                       autofocus>
                <small class="senha-dica" id="nome-dica">Mínimo 2 caracteres</small>
            </div>

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
                       required>
                <small class="senha-dica" id="email-dica">Digite um email válido</small>
            </div>

            <!-- Senha -->
            <div class="mb-4">
                <label for="senha" class="block text-sm font-medium mb-1" style="color:var(--text-secondary)">
                    Senha
                </label>
                <input type="password" 
                       id="senha" 
                       name="senha" 
                       class="input-field" 
                       placeholder="Mínimo 6 caracteres" 
                       required 
                       minlength="6">
                <div class="senha-forca" id="senha-forca"></div>
                <div class="flex justify-between mt-1">
                    <small class="senha-dica" id="senha-dica">Mínimo 6 caracteres</small>
                    <small class="senha-dica" id="senha-forca-texto"></small>
                </div>
            </div>

            <!-- Confirmar Senha -->
            <div class="mb-6">
                <label for="confirmar_senha" class="block text-sm font-medium mb-1" style="color:var(--text-secondary)">
                    Confirmar senha
                </label>
                <input type="password" 
                       id="confirmar_senha" 
                       name="confirmar_senha" 
                       class="input-field" 
                       placeholder="Repita a senha" 
                       required 
                       minlength="6">
                <small class="senha-dica" id="confirmar-dica">As senhas devem coincidir</small>
            </div>

            <!-- Termos -->
            <div class="mb-6">
                <label class="flex items-start gap-2 text-sm" style="color:var(--text-secondary)">
                    <input type="checkbox" id="termos" name="termos" required>
                    <span>
                        Li e aceito os 
                        <a href="#" class="link-purple" onclick="alert('Termos de uso em desenvolvimento.')">termos de uso</a> 
                        e 
                        <a href="#" class="link-purple" onclick="alert('Política de privacidade em desenvolvimento.')">política de privacidade</a>
                    </span>
                </label>
            </div>

            <!-- Botão -->
            <button type="submit" class="btn-cadastrar" id="btnCadastrar">
                Criar Conta
            </button>
        </form>

        <!-- Link para login -->
        <p class="text-center mt-6" style="color:var(--text-secondary)">
            Já tem conta?
            <a href="<?= BASE_URL ?>pages/auth/login.php" class="link-purple">
                Faça login
            </a>
        </p>

        <!-- Voltar -->
        <div class="text-center mt-4">
            <a href="<?= BASE_URL ?>" class="text-sm" style="color:var(--text-muted); text-decoration:none;">
                ← Voltar para o portal
            </a>
        </div>
    </div>

    <!-- Script para validação em tempo real -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('formCadastro');
            const btn = document.getElementById('btnCadastrar');
            
            const nomeInput = document.getElementById('nome');
            const emailInput = document.getElementById('email');
            const senhaInput = document.getElementById('senha');
            const confirmarInput = document.getElementById('confirmar_senha');
            const termosCheck = document.getElementById('termos');
            
            const nomeDica = document.getElementById('nome-dica');
            const emailDica = document.getElementById('email-dica');
            const senhaDica = document.getElementById('senha-dica');
            const confirmarDica = document.getElementById('confirmar-dica');
            const forcaBarra = document.getElementById('senha-forca');
            const forcaTexto = document.getElementById('senha-forca-texto');

            // Validação do nome em tempo real
            nomeInput.addEventListener('input', function() {
                const val = this.value.trim();
                if (val.length >= 2) {
                    this.classList.remove('error');
                    this.classList.add('success');
                    nomeDica.textContent = '✅ Nome válido';
                    nomeDica.className = 'senha-dica valida';
                } else {
                    this.classList.remove('success');
                    this.classList.add('error');
                    nomeDica.textContent = '❌ Mínimo 2 caracteres';
                    nomeDica.className = 'senha-dica invalida';
                }
            });

            // Validação do email em tempo real
            emailInput.addEventListener('input', function() {
                const val = this.value.trim();
                const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (regex.test(val)) {
                    this.classList.remove('error');
                    this.classList.add('success');
                    emailDica.textContent = '✅ Email válido';
                    emailDica.className = 'senha-dica valida';
                } else if (val.length > 0) {
                    this.classList.remove('success');
                    this.classList.add('error');
                    emailDica.textContent = '❌ Email inválido';
                    emailDica.className = 'senha-dica invalida';
                } else {
                    this.classList.remove('success', 'error');
                    emailDica.textContent = 'Digite um email válido';
                    emailDica.className = 'senha-dica';
                }
            });

            // Força da senha
            senhaInput.addEventListener('input', function() {
                const val = this.value;
                const forca = calcularForcaSenha(val);
                
                // Atualiza barra
                forcaBarra.className = 'senha-forca';
                if (val.length > 0) {
                    if (forca < 40) {
                        forcaBarra.classList.add('fraca');
                        forcaTexto.textContent = '🔴 Fraca';
                        forcaTexto.style.color = '#ef4444';
                    } else if (forca < 70) {
                        forcaBarra.classList.add('media');
                        forcaTexto.textContent = '🟡 Média';
                        forcaTexto.style.color = '#f59e0b';
                    } else {
                        forcaBarra.classList.add('forte');
                        forcaTexto.textContent = '🟢 Forte';
                        forcaTexto.style.color = '#10b981';
                    }
                } else {
                    forcaTexto.textContent = '';
                    forcaBarra.style.width = '0%';
                }

                // Valida tamanho
                if (val.length >= 6) {
                    this.classList.remove('error');
                    this.classList.add('success');
                    senhaDica.textContent = '✅ Senha válida';
                    senhaDica.className = 'senha-dica valida';
                } else if (val.length > 0) {
                    this.classList.remove('success');
                    this.classList.add('error');
                    senhaDica.textContent = '❌ Mínimo 6 caracteres';
                    senhaDica.className = 'senha-dica invalida';
                } else {
                    this.classList.remove('success', 'error');
                    senhaDica.textContent = 'Mínimo 6 caracteres';
                    senhaDica.className = 'senha-dica';
                }

                // Verifica confirmar senha
                verificarConfirmarSenha();
            });

            // Confirmar senha
            confirmarInput.addEventListener('input', verificarConfirmarSenha);

            function verificarConfirmarSenha() {
                const senha = senhaInput.value;
                const confirmar = confirmarInput.value;

                if (confirmar.length === 0) {
                    confirmarInput.classList.remove('success', 'error');
                    confirmarDica.textContent = 'As senhas devem coincidir';
                    confirmarDica.className = 'senha-dica';
                    return;
                }

                if (senha === confirmar) {
                    confirmarInput.classList.remove('error');
                    confirmarInput.classList.add('success');
                    confirmarDica.textContent = '✅ Senhas coincidem';
                    confirmarDica.className = 'senha-dica valida';
                } else {
                    confirmarInput.classList.remove('success');
                    confirmarInput.classList.add('error');
                    confirmarDica.textContent = '❌ Senhas não coincidem';
                    confirmarDica.className = 'senha-dica invalida';
                }
            }

            // Função para calcular força da senha
            function calcularForcaSenha(senha) {
                let forca = 0;
                if (senha.length >= 6) forca += 20;
                if (senha.length >= 10) forca += 20;
                if (/[a-z]/.test(senha)) forca += 15;
                if (/[A-Z]/.test(senha)) forca += 15;
                if (/\d/.test(senha)) forca += 15;
                if (/[^a-zA-Z0-9]/.test(senha)) forca += 15;
                return Math.min(forca, 100);
            }

            // Prevenir submissão dupla
            let submitted = false;

            form.addEventListener('submit', function(e) {
                // Valida termos
                if (!termosCheck.checked) {
                    e.preventDefault();
                    alert('Você precisa aceitar os termos de uso e política de privacidade.');
                    termosCheck.focus();
                    return;
                }

                if (submitted) {
                    e.preventDefault();
                    return;
                }

                // Validação final antes de enviar
                const nomeOk = nomeInput.value.trim().length >= 2;
                const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value.trim());
                const senhaOk = senhaInput.value.length >= 6;
                const confirmarOk = senhaInput.value === confirmarInput.value;

                if (!nomeOk || !emailOk || !senhaOk || !confirmarOk) {
                    e.preventDefault();
                    alert('Por favor, corrija os campos destacados antes de continuar.');
                    return;
                }

                submitted = true;
                btn.textContent = 'Criando conta...';
                btn.disabled = true;
            });

            // Mostrar senha (opcional)
            // Adiciona toggle de visibilidade da senha se desejar
        });
    </script>

</body>
</html>