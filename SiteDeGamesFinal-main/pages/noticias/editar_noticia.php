<?php
// =====================================================
// EDITAR NOTÍCIA - CORRIGIDO E PADRONIZADO
// Arquivo: pages/noticias/editar_noticia.php
// =====================================================

// Inicia a sessão se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclui os arquivos necessários
require_once __DIR__ . '/../../includes/conexao.php';
require_once __DIR__ . '/../../includes/funcoes.php';
require_once __DIR__ . '/../../includes/verifica_login.php';

// =====================================================
// VERIFICA SE A CONEXÃO EXISTE
// =====================================================
if (!isset($conn)) {
    set_mensagem('erro', 'Erro de conexão com o banco de dados.');
    redirecionar('dashboard.php');
    exit;
}

// =====================================================
// BUSCA CATEGORIAS
// =====================================================
$categorias = get_categorias($conn);

// =====================================================
// OBTÉM O ID DA NOTÍCIA
// =====================================================
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    set_mensagem('erro', 'ID da notícia inválido.');
    redirecionar('dashboard.php');
    exit;
}

// =====================================================
// BUSCA A NOTÍCIA NO BANCO
// =====================================================
try {
    $stmt = $conn->prepare("SELECT * FROM noticias WHERE id = ?");
    $stmt->execute([$id]);
    $noticia = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$noticia) {
        set_mensagem('erro', 'Notícia não encontrada.');
        redirecionar('dashboard.php');
        exit;
    }

    // Verifica se o usuário é o autor
    if ($noticia['autor'] != get_usuario_id()) {
        set_mensagem('erro', 'Você não tem permissão para editar esta notícia.');
        redirecionar('dashboard.php');
        exit;
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar notícia para edição: " . $e->getMessage());
    set_mensagem('erro', 'Erro ao carregar a notícia. Tente novamente.');
    redirecionar('dashboard.php');
    exit;
}

// =====================================================
// PROCESSAR FORMULÁRIO
// =====================================================
$erro = '';
$titulo = $noticia['titulo'];
$conteudo = $noticia['noticia'];
$imagem = $noticia['imagem'];
$categoria_id = $noticia['categoria_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica token CSRF
    if (!isset($_POST['csrf_token']) || !verificar_token_csrf($_POST['csrf_token'])) {
        $erro = 'Token de segurança inválido. Tente novamente.';
    } else {
        $titulo = trim($_POST['titulo'] ?? '');
        $conteudo = trim($_POST['noticia'] ?? '');
        $imagem = trim($_POST['imagem'] ?? '');
        $categoria_id = intval($_POST['categoria_id'] ?? 0);

        // Validações
        if (empty($titulo)) {
            $erro = 'O título é obrigatório.';
        } elseif (strlen($titulo) < 3) {
            $erro = 'O título deve ter pelo menos 3 caracteres.';
        } elseif (empty($conteudo)) {
            $erro = 'O conteúdo é obrigatório.';
        } elseif (strlen($conteudo) < 10) {
            $erro = 'O conteúdo deve ter pelo menos 10 caracteres.';
        } elseif ($categoria_id <= 0) {
            $erro = 'Selecione uma categoria válida.';
        } else {
            try {
                // Verifica se a categoria existe
                $stmt = $conn->prepare("SELECT id FROM categorias WHERE id = ?");
                $stmt->execute([$categoria_id]);
                if (!$stmt->fetch()) {
                    $erro = 'Categoria selecionada não existe.';
                } else {
                    // Atualiza a notícia
                    $stmt = $conn->prepare("
                        UPDATE noticias 
                        SET titulo = ?, noticia = ?, imagem = ?, categoria_id = ?, data = NOW() 
                        WHERE id = ? AND autor = ?
                    ");
                    $stmt->execute([
                        $titulo,
                        $conteudo,
                        !empty($imagem) ? $imagem : null,
                        $categoria_id,
                        $id,
                        get_usuario_id()
                    ]);

                    error_log("Notícia editada: ID $id por " . get_usuario_nome() . " (" . $_SERVER['REMOTE_ADDR'] . ")");
                    set_mensagem('sucesso', 'Notícia atualizada com sucesso!');
                    redirecionar('dashboard.php');
                    exit;
                }
            } catch (PDOException $e) {
                error_log("Erro ao editar notícia: " . $e->getMessage());
                $erro = 'Erro ao salvar as alterações. Tente novamente.';
            }
        }
    }
}

// =====================================================
// GERA TOKEN CSRF
// =====================================================
$csrf_token = gerar_token_csrf();
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Notícia - GGNews</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">

    <style>
        :root {
            --bg-body: #0c0c10;
            --bg-card: #1a1a2e;
            --bg-surface: #121218;
            --bg-header: #121218;
            --border: #252535;
            --accent: #7c3aed;
            --accent-light: #6d28d9;
            --text-primary: #eeeaf8;
            --text-secondary: #b8b5d0;
            --text-muted: #5e5c76;
            --text-danger: #ef4444;
            --text-success: #10b981;
            --shadow: 0 4px 20px rgba(0,0,0,0.4);
        }

        [data-theme="light"] {
            --bg-body: #f5f3f0;
            --bg-card: #ffffff;
            --bg-surface: #f8f6f4;
            --bg-header: #ffffff;
            --border: #e5e0db;
            --text-primary: #1a1a1a;
            --text-secondary: #4a4a5a;
            --text-muted: #8888a0;
            --shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: var(--bg-body);
            color: var(--text-primary);
            min-height: 100vh;
            transition: background 0.3s ease, color 0.3s ease;
        }

        .site-header {
            background: var(--bg-header);
            border-bottom: 1px solid var(--border);
            padding: 0.75rem 1rem;
            position: sticky;
            top: 0;
            z-index: 30;
            transition: background 0.3s ease, border-color 0.3s ease;
        }

        .header-inner {
            max-width: 900px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            text-decoration: none;
        }

        .header-logo span {
            color: var(--accent);
        }

        .btn-voltar {
            color: var(--text-muted);
            font-size: 0.9rem;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .btn-voltar:hover {
            color: var(--text-primary);
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .titulo {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 2rem;
        }

        .titulo small {
            font-size: 1rem;
            font-weight: 400;
            color: var(--text-muted);
            display: block;
            margin-top: 0.25rem;
        }

        .form-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            padding: 2rem;
            transition: background 0.3s ease, border-color 0.3s ease;
            box-shadow: var(--shadow);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            color: var(--text-secondary);
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.375rem;
        }

        .form-label .required {
            color: var(--text-danger);
        }

        .form-label .optional {
            color: var(--text-muted);
            font-weight: 400;
            font-size: 0.8rem;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            background: var(--bg-body);
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            color: var(--text-primary);
            font-family: inherit;
            font-size: 0.9rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-input::placeholder {
            color: var(--text-muted);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.2);
        }

        .form-select {
            width: 100%;
            padding: 0.75rem 1rem;
            background: var(--bg-body);
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            color: var(--text-primary);
            font-family: inherit;
            font-size: 0.9rem;
            cursor: pointer;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%239ca3af' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
        }

        .form-select option {
            background: var(--bg-card);
            color: var(--text-primary);
        }

        .form-select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.2);
        }

        .form-textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            background: var(--bg-body);
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            color: var(--text-primary);
            font-family: inherit;
            font-size: 0.9rem;
            resize: vertical;
            min-height: 200px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-textarea::placeholder {
            color: var(--text-muted);
        }

        .form-textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.2);
        }

        .form-hint {
            color: var(--text-muted);
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }

        .btn-primary {
            background: var(--accent);
            color: #fff;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .btn-primary:hover {
            background: var(--accent-light);
            transform: translateY(-2px);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-secondary {
            background: var(--bg-surface);
            color: var(--text-secondary);
            padding: 0.75rem 2rem;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .btn-secondary:hover {
            background: var(--border);
            color: var(--text-primary);
        }

        .msg-erro {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--text-danger);
            color: var(--text-danger);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .msg-sucesso {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid var(--text-success);
            color: var(--text-success);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .preview-imagem {
            margin-top: 0.5rem;
            max-width: 300px;
            border-radius: 0.5rem;
            border: 1px solid var(--border);
            display: none;
        }

        .preview-imagem.visible {
            display: block;
        }

        @media (max-width: 768px) {
            .form-card {
                padding: 1rem;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn-primary,
            .btn-secondary {
                width: 100%;
                justify-content: center;
            }

            .titulo {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 1rem 0.5rem;
            }

            .header-logo {
                font-size: 1.2rem;
            }
        }

        /* Tema toggle no header */
        .theme-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            padding: 0.25rem 0.75rem;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .theme-toggle:hover {
            background: var(--bg-card);
        }

        .toggle-track {
            width: 30px;
            height: 18px;
            background: var(--border);
            border-radius: 99px;
            position: relative;
            transition: background 0.25s ease;
        }

        [data-theme="dark"] .toggle-track {
            background: var(--accent);
        }

        .toggle-thumb {
            position: absolute;
            top: 3px;
            left: 3px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 1px 4px rgba(0,0,0,0.2);
            transition: transform 0.25s ease;
        }

        [data-theme="dark"] .toggle-thumb {
            transform: translateX(12px);
        }

        .toggle-icon {
            font-size: 0.9rem;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
    </style>

    <script>
        (function() {
            var t = localStorage.getItem('gg-theme') || 'dark';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
</head>
<body>

    <header class="site-header">
        <div class="header-inner">
            <a href="<?= BASE_URL ?>" class="header-logo">
                🎮 <span>GG</span>News
            </a>

            <div class="header-right">
                <button id="theme-toggle" class="theme-toggle" aria-label="Alternar tema">
                    <div class="toggle-track">
                        <div class="toggle-thumb"></div>
                    </div>
                    <span class="toggle-icon" id="theme-icon">🌙</span>
                </button>
                <a href="dashboard.php" class="btn-voltar">← Voltar</a>
            </div>
        </div>
    </header>

    <main class="container">

        <h1 class="titulo">
            ✏️ Editar Notícia
            <small>ID: #<?= $id ?></small>
        </h1>

        <?php if ($erro): ?>
            <div class="msg-erro"><?= escape($erro) ?></div>
        <?php endif; ?>

        <?php $msg = get_mensagem(); if ($msg && $msg['tipo'] === 'sucesso'): ?>
            <div class="msg-sucesso"><?= escape($msg['texto']) ?></div>
        <?php endif; ?>

        <form method="POST" class="form-card" id="formEditar">
            <!-- Token CSRF -->
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

            <div class="form-group">
                <label for="titulo" class="form-label">
                    Título da Notícia <span class="required">*</span>
                </label>
                <input type="text" id="titulo" name="titulo"
                       value="<?= escape($titulo) ?>"
                       class="form-input"
                       placeholder="Ex: LOUD vence campeonato mundial..."
                       required
                       minlength="3">
                <p class="form-hint">Mínimo 3 caracteres.</p>
            </div>

            <div class="form-group">
                <label for="categoria_id" class="form-label">
                    Categoria <span class="required">*</span>
                </label>
                <select id="categoria_id" name="categoria_id" class="form-select" required>
                    <option value="">Selecione uma categoria...</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $categoria_id == $cat['id'] ? 'selected' : '' ?>>
                            <?= $cat['icone'] ?? '📰' ?> <?= escape($cat['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="noticia" class="form-label">
                    Conteúdo <span class="required">*</span>
                </label>
                <textarea id="noticia" name="noticia" rows="12"
                          class="form-textarea"
                          placeholder="Escreva o conteúdo completo da notícia..."
                          required
                          minlength="10"><?= escape($conteudo) ?></textarea>
                <p class="form-hint">Mínimo 10 caracteres.</p>
            </div>

            <div class="form-group">
                <label for="imagem" class="form-label">
                    URL da Imagem <span class="optional">(opcional)</span>
                </label>
                <input type="url" id="imagem" name="imagem"
                       value="<?= escape($imagem ?? '') ?>"
                       class="form-input"
                       placeholder="https://exemplo.com/imagem.jpg">
                <p class="form-hint">Cole a URL de uma imagem da internet ou deixe em branco.</p>
                <?php if (!empty($imagem)): ?>
                    <img id="preview" src="<?= escape($imagem) ?>" 
                         alt="Preview da imagem" 
                         class="preview-imagem visible"
                         onerror="this.style.display='none'">
                <?php else: ?>
                    <img id="preview" src="" alt="Preview da imagem" class="preview-imagem">
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary" id="btnSalvar">Salvar Alterações</button>
                <a href="dashboard.php" class="btn-secondary">Cancelar</a>
            </div>
        </form>

    </main>

    <script>
        (function () {
            // Tema
            var html = document.documentElement;
            var btn = document.getElementById('theme-toggle');
            var icon = document.getElementById('theme-icon');

            function applyTheme(theme) {
                html.setAttribute('data-theme', theme);
                localStorage.setItem('gg-theme', theme);
                if (theme === 'dark') {
                    if (icon) icon.textContent = '☀️';
                } else {
                    if (icon) icon.textContent = '🌙';
                }
            }

            var savedTheme = localStorage.getItem('gg-theme') || 'dark';
            applyTheme(savedTheme);

            if (btn) {
                btn.addEventListener('click', function () {
                    var current = html.getAttribute('data-theme');
                    applyTheme(current === 'dark' ? 'light' : 'dark');
                });
            }

            // Preview da imagem
            var imagemInput = document.getElementById('imagem');
            var preview = document.getElementById('preview');

            if (imagemInput && preview) {
                imagemInput.addEventListener('input', function() {
                    var url = this.value.trim();
                    if (url) {
                        preview.src = url;
                        preview.classList.add('visible');
                        preview.onerror = function() {
                            this.classList.remove('visible');
                        };
                    } else {
                        preview.classList.remove('visible');
                    }
                });
            }

            // Prevenir submissão dupla
            var form = document.getElementById('formEditar');
            var btnSalvar = document.getElementById('btnSalvar');
            var submitted = false;

            if (form && btnSalvar) {
                form.addEventListener('submit', function(e) {
                    if (submitted) {
                        e.preventDefault();
                        return;
                    }

                    // Validações finais
                    var titulo = document.getElementById('titulo').value.trim();
                    var conteudo = document.getElementById('noticia').value.trim();
                    var categoria = document.getElementById('categoria_id').value;

                    if (titulo.length < 3) {
                        e.preventDefault();
                        alert('O título deve ter pelo menos 3 caracteres.');
                        document.getElementById('titulo').focus();
                        return;
                    }

                    if (conteudo.length < 10) {
                        e.preventDefault();
                        alert('O conteúdo deve ter pelo menos 10 caracteres.');
                        document.getElementById('noticia').focus();
                        return;
                    }

                    if (!categoria || categoria === '') {
                        e.preventDefault();
                        alert('Selecione uma categoria.');
                        document.getElementById('categoria_id').focus();
                        return;
                    }

                    submitted = true;
                    btnSalvar.textContent = 'Salvando...';
                    btnSalvar.disabled = true;
                });
            }
        })();
    </script>

</body>
</html>