<?php
// =====================================================
// EDITAR PERFIL DO USUÁRIO
// Arquivo: pages/usuario/editar_usuario.php
// =====================================================

require_once __DIR__ . '/../../includes/verifica_login.php';
require_once __DIR__ . '/../../includes/conexao.php';
require_once __DIR__ . '/../../includes/avatar_helper.php';

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([get_usuario_id()]);
$usuario = $stmt->fetch();

$erro    = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome       = trim($_POST['nome']       ?? '');
    $username   = trim($_POST['username']   ?? '');
    $email      = trim($_POST['email']      ?? '');
    $senha_atual = $_POST['senha_atual']    ?? '';
    $nova_senha  = $_POST['nova_senha']     ?? '';

    // ── Upload de foto ────────────────────────────────
    $foto = $usuario['foto'];

    if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $_FILES['foto']['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed_types)) {
            $erro = 'Formato de imagem inválido. Use JPG, PNG, GIF ou WEBP.';
        } elseif ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
            $erro = 'A imagem não pode ultrapassar 2 MB.';
        } else {
            $ext          = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $nomeArquivo  = 'user_' . get_usuario_id() . '_' . uniqid() . '.' . strtolower($ext);
            $dir_uploads  = __DIR__ . '/../../uploads/';
            $destino      = $dir_uploads . $nomeArquivo;

            // Cria a pasta se não existir
            if (!is_dir($dir_uploads)) {
                mkdir($dir_uploads, 0775, true);
            }

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
                // Remove foto antiga para não acumular arquivos
                if (!empty($usuario['foto'])) {
                    $antiga = $dir_uploads . $usuario['foto'];
                    if (file_exists($antiga)) {
                        unlink($antiga);
                    }
                }
                $foto = $nomeArquivo;
            } else {
                $erro = 'Não foi possível salvar a imagem. Verifique as permissões da pasta /uploads/.';
            }
        }
    }

    // ── Validações gerais ─────────────────────────────
    if (empty($erro)) {
        if (empty($nome) || empty($email)) {
            $erro = 'Nome e email são obrigatórios.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erro = 'Email inválido.';
        } else {
            // Verifica email duplicado
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $stmt->execute([$email, get_usuario_id()]);

            if ($stmt->fetch()) {
                $erro = 'Este email já está em uso por outro usuário.';
            } else {
                // Atualiza dados principais
                $stmt = $pdo->prepare("
                    UPDATE usuarios
                    SET nome = ?, username = ?, email = ?, foto = ?
                    WHERE id = ?
                ");
                $stmt->execute([$nome, $username, $email, $foto, get_usuario_id()]);

                // Atualiza foto na variável local para o preview imediato
                $usuario['foto'] = $foto;

                // ── Troca de senha (opcional) ─────────
                if (!empty($nova_senha)) {
                    if (empty($senha_atual)) {
                        $erro = 'Informe a senha atual para alterar a senha.';
                    } elseif (!password_verify($senha_atual, $usuario['senha'])) {
                        $erro = 'Senha atual incorreta.';
                    } elseif (strlen($nova_senha) < 6) {
                        $erro = 'A nova senha deve ter no mínimo 6 caracteres.';
                    } else {
                        $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
                        $stmt->execute([$hash, get_usuario_id()]);
                    }
                }

                if (empty($erro)) {
                    $_SESSION['usuario_nome'] = $nome;
                    set_mensagem('sucesso', 'Dados atualizados com sucesso!');
                    redirecionar('pages/noticias/dashboard.php');
                }
            }
        }
    }

} else {
    $nome     = $usuario['nome'];
    $username = $usuario['username'] ?? '';
    $email    = $usuario['email'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Conta - GGNews</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">

    <script>
        (function() {
            var t = localStorage.getItem('gg-theme') || 'light';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
</head>
<body class="min-h-screen" style="background:var(--bg-base); color:var(--text-primary)">

    <!-- HEADER -->
    <header class="site-header sticky top-0 z-30 px-4 py-3">
        <div class="max-w-xl mx-auto flex items-center justify-between">

            <a href="<?= BASE_URL ?>" class="flex items-center gap-2 text-xl font-bold"
               style="color:var(--text-primary); text-decoration:none;">
                🎮 <span style="color:var(--accent)">GG</span>News
            </a>

            <div class="flex items-center gap-3">
                <!-- Toggle de tema compacto -->
                <button id="theme-toggle"
                        class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm transition"
                        style="background:var(--bg-elevated); color:var(--text-secondary);
                               border:1px solid var(--border); cursor:pointer;"
                        aria-label="Alternar tema">
                    <div class="toggle-track" id="toggle-track" style="width:30px; height:18px;">
                        <div class="toggle-thumb" style="width:12px; height:12px; top:3px; left:3px;"></div>
                    </div>
                    <span id="theme-icon">🌙</span>
                </button>

                <a href="<?= BASE_URL ?>pages/noticias/dashboard.php"
                   class="text-sm transition"
                   style="color:var(--text-muted); text-decoration:none;">← Voltar</a>
            </div>

        </div>
    </header>

    <main class="max-w-xl mx-auto px-4 py-8">

        <h1 class="text-3xl font-bold mb-8" style="color:var(--text-primary)">Editar Perfil</h1>

        <!-- Mensagem de erro -->
        <?php if ($erro): ?>
            <div class="px-4 py-3 rounded-lg mb-6 border text-sm"
                 style="background:rgba(239,68,68,0.1); border-color:#ef4444; color:#dc2626;">
                <?= escape($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data"
              class="rounded-2xl p-8"
              style="background:var(--bg-surface); border:1px solid var(--border);">

            <!-- FOTO DE PERFIL -->
            <div class="mb-8 text-center">
                <div class="relative inline-block">
                    <!-- Preview do avatar -->
                    <div id="avatar-preview" class="mx-auto mb-3"
                         style="width:96px; height:96px;">
                        <?= render_avatar($usuario, 96) ?>
                    </div>

                    <!-- Label estilizado para o input file -->
                    <label for="foto"
                           class="inline-flex items-center gap-2 cursor-pointer text-sm font-medium px-4 py-2 rounded-lg transition"
                           style="background:var(--bg-elevated); color:var(--text-secondary);
                                  border:1px solid var(--border);">
                        📷 Alterar foto
                    </label>
                    <input type="file" name="foto" id="foto" accept="image/*"
                           class="hidden">
                    <p class="text-xs mt-2" style="color:var(--text-muted)">
                        JPG, PNG, WEBP — máx. 2 MB
                    </p>
                </div>
            </div>

            <!-- NOME -->
            <div class="mb-5">
                <label for="nome" class="block text-sm font-medium mb-1.5"
                       style="color:var(--text-secondary)">Nome</label>
                <input type="text" name="nome" id="nome"
                       value="<?= escape($nome) ?>"
                       placeholder="Seu nome completo">
            </div>

            <!-- USERNAME -->
            <div class="mb-5">
                <label for="username" class="block text-sm font-medium mb-1.5"
                       style="color:var(--text-secondary)">Username</label>
                <input type="text" name="username" id="username"
                       value="<?= escape($username) ?>"
                       placeholder="@seuusername">
            </div>

            <!-- EMAIL -->
            <div class="mb-5">
                <label for="email" class="block text-sm font-medium mb-1.5"
                       style="color:var(--text-secondary)">Email</label>
                <input type="email" name="email" id="email"
                       value="<?= escape($email) ?>"
                       placeholder="seu@email.com">
            </div>

            <!-- DIVISOR -->
            <div class="my-6" style="border-top:1px solid var(--border);"></div>
            <p class="text-sm mb-4" style="color:var(--text-muted)">
                Alterar senha <span style="color:var(--text-muted); font-style:italic;">(opcional)</span>
            </p>

            <!-- SENHA ATUAL -->
            <div class="mb-5">
                <label for="senha_atual" class="block text-sm font-medium mb-1.5"
                       style="color:var(--text-secondary)">Senha atual</label>
                <input type="password" name="senha_atual" id="senha_atual"
                       placeholder="••••••••">
            </div>

            <!-- NOVA SENHA -->
            <div class="mb-8">
                <label for="nova_senha" class="block text-sm font-medium mb-1.5"
                       style="color:var(--text-secondary)">Nova senha</label>
                <input type="password" name="nova_senha" id="nova_senha"
                       placeholder="Mínimo 6 caracteres">
            </div>

            <button type="submit" class="btn-primary w-full py-3 text-base">
                Salvar Alterações
            </button>

        </form>

    </main>

    <script>
    (function () {
        /* ── Toggle de tema ── */
        var html  = document.documentElement;
        var btn   = document.getElementById('theme-toggle');
        var track = document.getElementById('toggle-track');
        var icon  = document.getElementById('theme-icon');

        function applyTheme(theme) {
            html.setAttribute('data-theme', theme);
            localStorage.setItem('gg-theme', theme);
            if (theme === 'dark') {
                track && track.classList.add('is-dark');
                if (icon) icon.textContent = '☀️';
            } else {
                track && track.classList.remove('is-dark');
                if (icon) icon.textContent = '🌙';
            }
        }

        applyTheme(html.getAttribute('data-theme') || 'light');
        btn && btn.addEventListener('click', function () {
            applyTheme(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
        });

        /* ── Preview da foto antes do upload ── */
        var inputFoto   = document.getElementById('foto');
        var previewWrap = document.getElementById('avatar-preview');

        inputFoto && inputFoto.addEventListener('change', function () {
            var file = this.files[0];
            if (!file) return;

            // Valida tamanho no cliente (2 MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('A imagem não pode ultrapassar 2 MB.');
                this.value = '';
                return;
            }

            var reader = new FileReader();
            reader.onload = function (e) {
                previewWrap.innerHTML =
                    '<img src="' + e.target.result + '" ' +
                    'style="width:96px;height:96px;border-radius:50%;object-fit:cover;' +
                    'border:2px solid var(--border);">';
            };
            reader.readAsDataURL(file);
        });
    })();
    </script>

</body>
</html>