<?php
// =====================================================
// EDITAR NOTÍCIA
// Arquivo: pages/noticias/editar_noticia.php
// =====================================================

require_once __DIR__ . '/../../includes/conexao.php';
require_once __DIR__ . '/../../includes/verifica_login.php';

$categorias = get_categorias($pdo);
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: dashboard.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM noticias WHERE id = ?");
$stmt->execute([$id]);
$noticia = $stmt->fetch();

if (!$noticia || $noticia['autor'] !== get_usuario_id()) {
    set_mensagem('erro', 'Você não tem permissão para editar esta notícia.');
    header("Location: dashboard.php");
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $conteudo = trim($_POST['noticia'] ?? '');
    $imagem = trim($_POST['imagem'] ?? '');
    $categoria_id = intval($_POST['categoria_id'] ?? 0);

    if (empty($titulo) || empty($conteudo)) {
        $erro = 'Título e conteúdo são obrigatórios.';
    } elseif ($categoria_id <= 0) {
        $erro = 'Selecione uma categoria.';
    } else {
        $stmt = $pdo->prepare("UPDATE noticias SET titulo = ?, noticia = ?, imagem = ?, categoria_id = ? WHERE id = ? AND autor = ?");
        $stmt->execute([$titulo, $conteudo, $imagem ?: null, $categoria_id, $id, get_usuario_id()]);

        set_mensagem('sucesso', 'Notícia atualizada com sucesso!');
        header("Location: dashboard.php");
        exit;
    }
} else {
    $titulo = $noticia['titulo'];
    $conteudo = $noticia['noticia'];
    $imagem = $noticia['imagem'];
    $categoria_id = $noticia['categoria_id'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Notícia - GGNews</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../css/style.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #0f0f0f;
            color: #ffffff;
            min-height: 100vh;
        }

        .site-header {
            background: #1a1a2e;
            border-bottom: 1px solid #2a2a3e;
            padding: 0.75rem 1rem;
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
            color: #ffffff;
            text-decoration: none;
        }

        .header-logo span {
            color: #7c3aed;
        }

        .btn-voltar {
            color: #9ca3af;
            font-size: 0.9rem;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .btn-voltar:hover {
            color: #ffffff;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .titulo {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 2rem;
            color: #ffffff;
        }

        .form-card {
            background: #1a1a2e;
            border: 1px solid #2a2a3e;
            border-radius: 0.75rem;
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            color: #9ca3af;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.375rem;
        }

        .form-label .required {
            color: #ef4444;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            background: #0f0f0f;
            border: 1px solid #2a2a3e;
            border-radius: 0.5rem;
            color: #ffffff;
            font-family: inherit;
            font-size: 0.9rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-input::placeholder {
            color: #4a4a5e;
        }

        .form-input:focus {
            outline: none;
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.2);
        }

        .form-select {
            width: 100%;
            padding: 0.75rem 1rem;
            background: #0f0f0f;
            border: 1px solid #2a2a3e;
            border-radius: 0.5rem;
            color: #ffffff;
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
            background: #1a1a2e;
            color: #ffffff;
        }

        .form-select:focus {
            outline: none;
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.2);
        }

        .form-textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            background: #0f0f0f;
            border: 1px solid #2a2a3e;
            border-radius: 0.5rem;
            color: #ffffff;
            font-family: inherit;
            font-size: 0.9rem;
            resize: vertical;
            min-height: 200px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-textarea::placeholder {
            color: #4a4a5e;
        }

        .form-textarea:focus {
            outline: none;
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.2);
        }

        .form-hint {
            color: #4a4a5e;
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .btn-primary {
            background: #7c3aed;
            color: #ffffff;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .btn-primary:hover {
            background: #6d28d9;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #2a2a3e;
            color: #ffffff;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: background 0.2s ease;
        }

        .btn-secondary:hover {
            background: #3a3a4e;
        }

        .msg-erro {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid #ef4444;
            color: #f87171;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
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
        }

        @media (max-width: 480px) {
            .container {
                padding: 1rem 0.5rem;
            }

            .titulo {
                font-size: 1.5rem;
            }

            .header-logo {
                font-size: 1.2rem;
            }
        }
    </style>

    <script>
        (function() {
            var t = localStorage.getItem('gg-theme') || 'light';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
</head>
<body>

    <header class="site-header">
        <div class="header-inner">
            <a href="../../index.php" class="header-logo">
                🎮 <span>GG</span>News
            </a>
            <a href="dashboard.php" class="btn-voltar">← Voltar ao Painel</a>
        </div>
    </header>

    <main class="container">

        <h1 class="titulo">✏️ Editar Notícia</h1>

        <?php if ($erro): ?>
            <div class="msg-erro"><?= escape($erro) ?></div>
        <?php endif; ?>

        <form method="POST" class="form-card">

            <div class="form-group">
                <label for="titulo" class="form-label">
                    Título da Notícia <span class="required">*</span>
                </label>
                <input type="text" id="titulo" name="titulo"
                       value="<?= escape($titulo) ?>"
                       class="form-input"
                       placeholder="Ex: LOUD vence campeonato mundial..."
                       required>
            </div>

            <div class="form-group">
                <label for="categoria_id" class="form-label">
                    Categoria <span class="required">*</span>
                </label>
                <select id="categoria_id" name="categoria_id" class="form-select" required>
                    <option value="">Selecione uma categoria...</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $categoria_id == $cat['id'] ? 'selected' : '' ?>>
                            <?= $cat['icone'] ?> <?= escape($cat['nome']) ?>
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
                          required><?= escape($conteudo) ?></textarea>
            </div>

            <div class="form-group">
                <label for="imagem" class="form-label">URL da Imagem <span style="color:#4a4a5e;">(opcional)</span></label>
                <input type="url" id="imagem" name="imagem"
                       value="<?= escape($imagem ?? '') ?>"
                       class="form-input"
                       placeholder="https://exemplo.com/imagem.jpg">
                <p class="form-hint">Cole a URL de uma imagem da internet ou deixe em branco.</p>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Salvar Alterações</button>
                <a href="dashboard.php" class="btn-secondary">Cancelar</a>
            </div>

        </form>

    </main>

</body>
</html>