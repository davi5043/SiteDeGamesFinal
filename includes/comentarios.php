<?php
// =====================================================
// HELPER: Comentários e Feedbacks - CORRIGIDO E PADRONIZADO
// =====================================================

// Inclui funções auxiliares para usar escape e outras funções
require_once __DIR__ . '/funcoes.php';

/**
 * Busca todos os comentários de uma notícia
 * 
 * @param PDO $pdo Conexão com o banco de dados
 * @param int $noticia_id ID da notícia
 * @param int $limite Limite de comentários (opcional)
 * @return array Lista de comentários
 */
function get_comentarios($pdo, $noticia_id, $limite = null) {
    // Verifica se a conexão é válida
    if ($pdo === null) {
        error_log("get_comentarios: PDO connection is null");
        return [];
    }
    
    try {
        $sql = "
            SELECT c.*, u.nome, u.foto, u.email
            FROM comentarios c
            INNER JOIN usuarios u ON c.usuario_id = u.id
            WHERE c.noticia_id = ?
            ORDER BY c.data DESC
        ";
        
        if ($limite !== null && $limite > 0) {
            $sql .= " LIMIT " . intval($limite);
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$noticia_id]);
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $resultado !== false ? $resultado : [];
        
    } catch (PDOException $e) {
        error_log("get_comentarios: " . $e->getMessage());
        return [];
    }
}

/**
 * Busca comentários com paginação
 * 
 * @param PDO $pdo Conexão com o banco de dados
 * @param int $noticia_id ID da notícia
 * @param int $pagina Número da página
 * @param int $por_pagina Quantos comentários por página
 * @return array Lista de comentários com dados de paginação
 */
function get_comentarios_paginados($pdo, $noticia_id, $pagina = 1, $por_pagina = 10) {
    if ($pdo === null) {
        error_log("get_comentarios_paginados: PDO connection is null");
        return [
            'comentarios' => [],
            'total' => 0,
            'pagina' => $pagina,
            'por_pagina' => $por_pagina,
            'total_paginas' => 0
        ];
    }
    
    try {
        // Conta total de comentários
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM comentarios WHERE noticia_id = ?");
        $stmt->execute([$noticia_id]);
        $total = (int) $stmt->fetchColumn();
        
        // Calcula offset
        $offset = ($pagina - 1) * $por_pagina;
        $total_paginas = ceil($total / $por_pagina);
        
        // Busca comentários da página
        $sql = "
            SELECT c.*, u.nome, u.foto, u.email
            FROM comentarios c
            INNER JOIN usuarios u ON c.usuario_id = u.id
            WHERE c.noticia_id = ?
            ORDER BY c.data DESC
            LIMIT ? OFFSET ?
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$noticia_id, $por_pagina, $offset]);
        $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'comentarios' => $comentarios !== false ? $comentarios : [],
            'total' => $total,
            'pagina' => $pagina,
            'por_pagina' => $por_pagina,
            'total_paginas' => $total_paginas
        ];
        
    } catch (PDOException $e) {
        error_log("get_comentarios_paginados: " . $e->getMessage());
        return [
            'comentarios' => [],
            'total' => 0,
            'pagina' => $pagina,
            'por_pagina' => $por_pagina,
            'total_paginas' => 0
        ];
    }
}

/**
 * Conta os comentários de uma notícia
 * 
 * @param PDO $pdo Conexão com o banco de dados
 * @param int $noticia_id ID da notícia
 * @return int Número de comentários
 */
function contar_comentarios($pdo, $noticia_id) {
    if ($pdo === null) {
        error_log("contar_comentarios: PDO connection is null");
        return 0;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM comentarios WHERE noticia_id = ?");
        $stmt->execute([$noticia_id]);
        $resultado = $stmt->fetchColumn();
        return $resultado !== false ? (int) $resultado : 0;
        
    } catch (PDOException $e) {
        error_log("contar_comentarios: " . $e->getMessage());
        return 0;
    }
}

/**
 * Adiciona um comentário
 * 
 * @param PDO $pdo Conexão com o banco de dados
 * @param int $noticia_id ID da notícia
 * @param int $usuario_id ID do usuário
 * @param string $conteudo Conteúdo do comentário
 * @return int|bool ID do comentário inserido ou false em caso de erro
 */
function adicionar_comentario($pdo, $noticia_id, $usuario_id, $conteudo) {
    if ($pdo === null) {
        error_log("adicionar_comentario: PDO connection is null");
        return false;
    }
    
    // Validação básica
    if (empty($conteudo) || strlen(trim($conteudo)) < 3) {
        error_log("adicionar_comentario: Conteúdo muito curto");
        return false;
    }
    
    try {
        // Sanitiza o conteúdo
        $conteudo = trim(strip_tags($conteudo));
        
        $stmt = $pdo->prepare("
            INSERT INTO comentarios (noticia_id, usuario_id, conteudo, data) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$noticia_id, $usuario_id, $conteudo]);
        
        return $pdo->lastInsertId();
        
    } catch (PDOException $e) {
        error_log("adicionar_comentario: " . $e->getMessage());
        return false;
    }
}

/**
 * Exclui um comentário (apenas o autor ou admin)
 * 
 * @param PDO $pdo Conexão com o banco de dados
 * @param int $comentario_id ID do comentário
 * @param int $usuario_id ID do usuário que está tentando excluir
 * @param bool $is_admin Se o usuário é administrador
 * @return bool True se excluiu, false se falhou ou sem permissão
 */
function excluir_comentario($pdo, $comentario_id, $usuario_id, $is_admin = false) {
    if ($pdo === null) {
        error_log("excluir_comentario: PDO connection is null");
        return false;
    }
    
    try {
        if ($is_admin) {
            // Admin pode excluir qualquer comentário
            $stmt = $pdo->prepare("DELETE FROM comentarios WHERE id = ?");
            return $stmt->execute([$comentario_id]);
        } else {
            // Usuário comum só pode excluir seus próprios comentários
            $stmt = $pdo->prepare("DELETE FROM comentarios WHERE id = ? AND usuario_id = ?");
            return $stmt->execute([$comentario_id, $usuario_id]);
        }
        
    } catch (PDOException $e) {
        error_log("excluir_comentario: " . $e->getMessage());
        return false;
    }
}

/**
 * Verifica se o usuário pode excluir um comentário
 * 
 * @param PDO $pdo Conexão com o banco de dados
 * @param int $comentario_id ID do comentário
 * @param int $usuario_id ID do usuário
 * @param bool $is_admin Se o usuário é administrador
 * @return bool
 */
function pode_excluir_comentario($pdo, $comentario_id, $usuario_id, $is_admin = false) {
    if ($is_admin) {
        return true;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT usuario_id FROM comentarios WHERE id = ?");
        $stmt->execute([$comentario_id]);
        $comentario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$comentario) {
            return false;
        }
        
        return (int) $comentario['usuario_id'] === (int) $usuario_id;
        
    } catch (PDOException $e) {
        error_log("pode_excluir_comentario: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtém os últimos comentários (para sidebar/widget)
 * 
 * @param PDO $pdo Conexão com o banco de dados
 * @param int $limite Quantos comentários retornar
 * @return array
 */
function get_ultimos_comentarios($pdo, $limite = 5) {
    if ($pdo === null) {
        error_log("get_ultimos_comentarios: PDO connection is null");
        return [];
    }
    
    try {
        $sql = "
            SELECT c.*, u.nome, u.foto, n.titulo AS noticia_titulo
            FROM comentarios c
            INNER JOIN usuarios u ON c.usuario_id = u.id
            INNER JOIN noticias n ON c.noticia_id = n.id
            ORDER BY c.data DESC
            LIMIT ?
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$limite]);
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resultado !== false ? $resultado : [];
        
    } catch (PDOException $e) {
        error_log("get_ultimos_comentarios: " . $e->getMessage());
        return [];
    }
}

/**
 * Formata um comentário para exibição (com links, menções, etc)
 * 
 * @param string $conteudo Conteúdo do comentário
 * @return string Conteúdo formatado
 */
function formatar_comentario($conteudo) {
    // Escapa HTML
    $conteudo = escape($conteudo);
    
    // Converte URLs em links
    $conteudo = preg_replace(
        '/(https?:\/\/[^\s]+)/',
        '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>',
        $conteudo
    );
    
    // Converte quebras de linha em <br>
    $conteudo = nl2br($conteudo);
    
    return $conteudo;
}

/**
 * Obtém os comentários de um usuário específico
 * 
 * @param PDO $pdo Conexão com o banco de dados
 * @param int $usuario_id ID do usuário
 * @param int $limite Quantos comentários retornar
 * @return array
 */
function get_comentarios_usuario($pdo, $usuario_id, $limite = 10) {
    if ($pdo === null) {
        error_log("get_comentarios_usuario: PDO connection is null");
        return [];
    }
    
    try {
        $sql = "
            SELECT c.*, n.titulo AS noticia_titulo
            FROM comentarios c
            INNER JOIN noticias n ON c.noticia_id = n.id
            WHERE c.usuario_id = ?
            ORDER BY c.data DESC
            LIMIT ?
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id, $limite]);
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resultado !== false ? $resultado : [];
        
    } catch (PDOException $e) {
        error_log("get_comentarios_usuario: " . $e->getMessage());
        return [];
    }
}

/**
 * Exibe o HTML dos comentários (função helper para views)
 * 
 * @param array $comentarios Lista de comentários
 * @param int $usuario_id ID do usuário logado
 * @param bool $is_admin Se o usuário é admin
 * @return void
 */
function exibir_comentarios($comentarios, $usuario_id = null, $is_admin = false) {
    if (empty($comentarios)) {
        echo '<p class="text-muted">Nenhum comentário ainda. Seja o primeiro!</p>';
        return;
    }
    
    foreach ($comentarios as $comentario): ?>
        <div class="comentario mb-4 p-4 border rounded-lg" style="background:var(--bg-surface); border-color:var(--border)">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <?php if (!empty($comentario['foto'])): ?>
                        <img src="<?= escape($comentario['foto']) ?>" 
                             alt="<?= escape($comentario['nome']) ?>" 
                             class="w-10 h-10 rounded-full object-cover">
                    <?php else: ?>
                        <div class="w-10 h-10 rounded-full flex items-center justify-center" 
                             style="background:var(--accent); color:#fff; font-weight:bold;">
                            <?= strtoupper(substr(escape($comentario['nome']), 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <strong style="color:var(--text-primary)"><?= escape($comentario['nome']) ?></strong>
                        <span class="text-xs" style="color:var(--text-muted)">
                            <?= formatar_data($comentario['data']) ?>
                        </span>
                    </div>
                    
                    <div class="mt-2" style="color:var(--text-secondary)">
                        <?= formatar_comentario($comentario['conteudo']) ?>
                    </div>
                    
                    <?php if ($usuario_id && (int) $usuario_id === (int) $comentario['usuario_id'] || $is_admin): ?>
                        <div class="mt-2">
                            <button onclick="excluirComentario(<?= $comentario['id'] ?>)" 
                                    class="text-xs text-red-500 hover:text-red-700">
                                🗑️ Excluir
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach;
}

/**
 * Gera o HTML do formulário de comentário
 * 
 * @param int $noticia_id ID da notícia
 * @param bool $usuario_logado Se o usuário está logado
 * @return void
 */
function exibir_formulario_comentario($noticia_id, $usuario_logado = false) {
    if (!$usuario_logado): ?>
        <div class="p-4 border rounded-lg text-center" style="border-color:var(--border)">
            <p style="color:var(--text-muted)">
                Faça <a href="<?= BASE_URL ?>pages/auth/login.php" style="color:var(--accent)">login</a> 
                ou <a href="<?= BASE_URL ?>pages/auth/cadastro.php" style="color:var(--accent)">cadastre-se</a> 
                para comentar.
            </p>
        </div>
    <?php else: ?>
        <form method="POST" action="<?= BASE_URL ?>pages/noticias/noticia.php?id=<?= $noticia_id ?>" class="mt-4">
            <input type="hidden" name="csrf_token" value="<?= gerar_token_csrf() ?>">
            <input type="hidden" name="noticia_id" value="<?= $noticia_id ?>">
            
            <div class="mb-3">
                <textarea name="comentario" 
                          rows="4" 
                          class="w-full p-3 border rounded-lg resize-y"
                          style="background:var(--bg-surface); border-color:var(--border); color:var(--text-primary)"
                          placeholder="Escreva seu comentário..."
                          required></textarea>
            </div>
            
            <button type="submit" 
                    name="submit_comentario" 
                    class="btn-primary">
                Enviar Comentário
            </button>
        </form>
    <?php endif;
}

// =====================================================
// FUNÇÕES DE PROCESSAMENTO DE FORMULÁRIO (OPCIONAL)
// =====================================================

/**
 * Processa o envio de um comentário via POST
 * 
 * @param PDO $pdo Conexão com o banco
 * @param array $post Dados do POST
 * @param int $usuario_id ID do usuário logado
 * @return array Resultado com status e mensagem
 */
function processar_comentario_post($pdo, $post, $usuario_id) {
    $resultado = [
        'sucesso' => false,
        'mensagem' => ''
    ];
    
    // Verifica token CSRF
    if (!isset($post['csrf_token']) || !verificar_token_csrf($post['csrf_token'])) {
        $resultado['mensagem'] = 'Token de segurança inválido.';
        return $resultado;
    }
    
    // Verifica se tem conteúdo
    if (empty($post['comentario']) || strlen(trim($post['comentario'])) < 3) {
        $resultado['mensagem'] = 'O comentário deve ter pelo menos 3 caracteres.';
        return $resultado;
    }
    
    // Verifica se o usuário está logado
    if (!$usuario_id) {
        $resultado['mensagem'] = 'Você precisa estar logado para comentar.';
        return $resultado;
    }
    
    // Adiciona o comentário
    $comentario_id = adicionar_comentario(
        $pdo, 
        $post['noticia_id'], 
        $usuario_id, 
        $post['comentario']
    );
    
    if ($comentario_id) {
        $resultado['sucesso'] = true;
        $resultado['mensagem'] = 'Comentário adicionado com sucesso!';
        $resultado['comentario_id'] = $comentario_id;
    } else {
        $resultado['mensagem'] = 'Erro ao adicionar comentário. Tente novamente.';
    }
    
    return $resultado;
}
?>