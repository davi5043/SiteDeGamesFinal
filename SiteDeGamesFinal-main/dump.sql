-- =====================================================
-- PORTAL DE NOTÍCIAS - GAMES E E-SPORTS
-- Arquivo: dump.sql
-- Descrição: Estrutura completa do banco de dados
-- Instruções: Importe este arquivo no phpMyAdmin
-- =====================================================

-- Cria o banco de dados caso não exista
CREATE DATABASE IF NOT EXISTS portal_games
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

-- Seleciona o banco de dados
USE portal_games;

-- =====================================================
-- TABELA: usuarios
-- Armazena os dados dos usuários cadastrados
-- =====================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,          -- Identificador único
    nome VARCHAR(100) NOT NULL,                 -- Nome completo do usuário
    email VARCHAR(150) NOT NULL UNIQUE,         -- Email (usado para login)
    senha VARCHAR(255) NOT NULL,                -- Senha criptografada com password_hash
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP -- Data de criação da conta
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABELA: noticias
-- Armazena as notícias publicadas pelos usuários
-- =====================================================
CREATE TABLE IF NOT EXISTS noticias (
    id INT AUTO_INCREMENT PRIMARY KEY,          -- Identificador único
    titulo VARCHAR(255) NOT NULL,               -- Título da notícia
    noticia TEXT NOT NULL,                      -- Conteúdo completo da notícia
    data DATETIME DEFAULT CURRENT_TIMESTAMP,    -- Data/hora da publicação
    autor INT NOT NULL,                         -- ID do usuário que publicou
    imagem VARCHAR(255) DEFAULT NULL,           -- Caminho ou URL da imagem (opcional)
    FOREIGN KEY (autor) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DADOS DE EXEMPLO
-- Usuário de teste: email: admin@portal.com | senha: 123456
-- =====================================================

-- Inserir usuário de exemplo (senha: 123456 criptografada com password_hash)
INSERT INTO usuarios (nome, email, senha) VALUES
('Admin Portal', 'admin@portal.com', '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQkFjeSMW0aOQvJPJnMGZBfYq3OUUG');

-- Inserir notícias de exemplo
INSERT INTO noticias (titulo, noticia, data, autor, imagem) VALUES
('Campeonato Mundial de League of Legends 2026 é Anunciado',
'A Riot Games anunciou oficialmente as datas e local do Campeonato Mundial de League of Legends 2026. O evento será realizado em Seul, Coreia do Sul, entre outubro e novembro. Equipes de todas as regiões competitivas do mundo se preparam para a maior competição do cenário. A expectativa é de que o torneio bata recordes de audiência, superando os números impressionantes do ano anterior. Os fãs brasileiros torcem para que as equipes nacionais conquistem uma vaga e representem o país no palco mundial.',
'2026-06-20 10:00:00', 1, NULL),

('CS2 Recebe Atualização Massiva com Novo Mapa Competitivo',
'A Valve surpreendeu a comunidade de Counter-Strike 2 ao lançar uma atualização que inclui um novo mapa para o pool competitivo. O mapa, chamado "Thera", é ambientado em uma ilha grega e traz mecânicas inovadoras de verticalidade. Jogadores profissionais já começaram a testar estratégias e as primeiras impressões são extremamente positivas. A atualização também inclui ajustes de balanceamento em armas e melhorias no sistema anti-cheat.',
'2026-06-19 14:30:00', 1, NULL),

('LOUD Conquista Título Internacional de Valorant',
'A organização brasileira LOUD mais uma vez provou seu domínio no cenário competitivo de Valorant ao conquistar o título do Masters Tokyo 2026. A equipe brasileira venceu a final por 3-1 contra a coreana DRX, em uma série emocionante que contou com jogadas individuais brilhantes. O destaque ficou para o jogador aspas, que foi eleito MVP do torneio pela terceira vez consecutiva. A torcida brasileira celebrou nas redes sociais.',
'2026-06-18 20:00:00', 1, NULL),

('Nintendo Anuncia Novo Console com Foco em E-Sports',
'Em uma apresentação surpresa, a Nintendo revelou seu novo console voltado para o público competitivo. O "Nintendo Switch Pro" conta com hardware capaz de rodar jogos em 4K a 120fps, além de um controle profissional com botões mecânicos e latência ultra-baixa. A empresa também anunciou parcerias com desenvolvedores de jogos competitivos para trazer títulos exclusivos à plataforma. O lançamento está previsto para o final de 2026.',
'2026-06-17 09:00:00', 1, NULL),

('Brasileiros Dominam Competição Internacional de Fortnite',
'O cenário competitivo de Fortnite ganhou uma nova força com a dominância dos jogadores brasileiros na última Copa do Mundo. Três dos cinco primeiros colocados são brasileiros, consolidando o país como uma potência no battle royale da Epic Games. Os jogadores atribuem o sucesso à dedicação nos treinos e ao apoio crescente das organizações nacionais de e-sports.',
'2026-06-16 16:45:00', 1, NULL);
