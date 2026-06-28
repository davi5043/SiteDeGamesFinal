-- =====================================================
-- PORTAL DE NOTÍCIAS - GAMES E E-SPORTS (COMPLETO)
-- =====================================================

DROP DATABASE IF EXISTS portal_games;

CREATE DATABASE portal_games
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE portal_games;

-- =====================================================
-- TABELA: usuarios
-- =====================================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- TABELA: categorias
-- =====================================================
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    icone VARCHAR(50) DEFAULT NULL
) ENGINE=InnoDB;

-- =====================================================
-- TABELA: noticias
-- =====================================================
CREATE TABLE noticias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    noticia TEXT NOT NULL,
    data DATETIME DEFAULT CURRENT_TIMESTAMP,
    autor INT NOT NULL,
    categoria_id INT NOT NULL,
    imagem VARCHAR(255) DEFAULT NULL,

    FOREIGN KEY (autor) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- TABELA: comentarios
-- =====================================================
CREATE TABLE comentarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    noticia_id INT NOT NULL,
    usuario_id INT NOT NULL,
    comentario TEXT NOT NULL,
    data DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (noticia_id) REFERENCES noticias(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- USUÁRIO PADRÃO
-- senha: 123456
-- =====================================================
INSERT INTO usuarios (nome, email, senha) VALUES
('Admin Portal', 'admin@portal.com', '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQkFjeSMW0aOQvJPJnMGZBfYq3OUUG');

-- =====================================================
-- CATEGORIAS (IGUAL AO SEU PRINT)
-- =====================================================
INSERT INTO categorias (id, nome, slug, icone) VALUES
(1, 'CS2', 'cs2', '🎯'),
(2, 'VALORANT', 'valorant', '🎯'),
(3, 'League of Legends', 'lol', '🦉'),
(4, 'Esports', 'esports', '🏆'),
(5, 'Geral', 'geral', '🎮');

-- =====================================================
-- NOTÍCIAS (AGORA COM CATEGORIA)
-- =====================================================
INSERT INTO noticias (titulo, noticia, data, autor, categoria_id, imagem) VALUES

('Banco de dados funcionando!',
'Banco de dados funcionando corretamente!',
'2026-06-26 18:31:02', 1, 5,
'https://images.unsplash.com/photo-1542751371-adc38448a05e'),

('CS2 Recebe Atualização Massiva',
'Novo mapa competitivo "Thera" chega ao CS2 com mudanças importantes.',
'2026-06-19 14:30:00', 1, 1, NULL),

('LOUD vence campeonato de Valorant',
'A equipe brasileira dominou o cenário internacional.',
'2026-06-18 20:00:00', 1, 2, NULL),

('Worlds de LoL 2026 anunciado',
'Evento será na Coreia do Sul com recorde esperado de audiência.',
'2026-06-20 10:00:00', 1, 3, NULL),

('Brasil domina Fortnite',
'Jogadores brasileiros lideram ranking mundial.',
'2026-06-16 16:45:00', 1, 4, NULL);

-- =====================================================
-- COMENTÁRIOS DE EXEMPLO
-- =====================================================
INSERT INTO comentarios (noticia_id, usuario_id, comentario) VALUES
(1, 1, 'Sistema funcionando perfeitamente!'),
(2, 1, 'Atualização muito boa 🔥'),
(3, 1, 'LOUD é gigante!');