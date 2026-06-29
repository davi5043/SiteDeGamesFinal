-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 29/06/2026 às 19:45
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `portal_games`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icone` varchar(10) DEFAULT '?'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `categorias`
--

INSERT INTO `categorias` (`id`, `nome`, `slug`, `icone`) VALUES
(1, 'CS2', 'cs2', '🔫'),
(2, 'VALORANT', 'valorant', '🎯'),
(3, 'League of Legends', 'lol', '⚔️'),
(4, 'Esports', 'esports', '🏆'),
(5, 'Geral', 'geral', '🎮');

-- --------------------------------------------------------

--
-- Estrutura para tabela `comentarios`
--

CREATE TABLE `comentarios` (
  `id` int(11) NOT NULL,
  `noticia_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `conteudo` text NOT NULL,
  `data` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `comentarios`
--

INSERT INTO `comentarios` (`id`, `noticia_id`, `usuario_id`, `conteudo`, `data`) VALUES
(5, 12, 3, 'Muito top!', '2026-06-29 14:35:56'),
(6, 11, 3, 'Muito top!', '2026-06-29 14:36:09'),
(7, 10, 3, 'Muito top!', '2026-06-29 14:36:21'),
(8, 9, 3, 'Muito top!', '2026-06-29 14:36:30'),
(9, 7, 3, 'Muito top!', '2026-06-29 14:36:50'),
(10, 6, 3, 'Muito top!', '2026-06-29 14:36:59'),
(11, 5, 3, 'Muito top!', '2026-06-29 14:37:08'),
(12, 4, 3, 'Muito top!', '2026-06-29 14:37:25'),
(13, 3, 3, 'Muito top!', '2026-06-29 14:37:33');

-- --------------------------------------------------------

--
-- Estrutura para tabela `noticias`
--

CREATE TABLE `noticias` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `noticia` text NOT NULL,
  `data` datetime DEFAULT current_timestamp(),
  `autor` int(11) NOT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `noticias`
--

INSERT INTO `noticias` (`id`, `titulo`, `noticia`, `data`, `autor`, `imagem`, `categoria_id`) VALUES
(3, 'FURIA anuncia novo elenco de CS2 para temporada 2026', 'A FURIA Esports anunciou oficialmente seu novo elenco de Counter-Strike 2 para a temporada 2026. A equipe conta com dois novos jogadores vindos da Europa e um treinador experiente no cenário internacional. O objetivo da organização é conquistar o primeiro Major da história do Brasil.', '2026-06-29 14:28:35', 3, 'https://images.unsplash.com/photo-1542751371-adc38448a05e', 1),
(4, 'LOUD é campeã do VCT Americas 2026', 'A LOUD venceu a grande final do VCT Americas 2026 contra a NRG por 3 a 1. A equipe brasileira mostrou um desempenho impecável e garantiu vaga para o Champions 2026. O destaque ficou para o jogador aspas, que foi eleito o MVP da competição.', '2026-06-29 14:29:14', 3, 'https://images.unsplash.com/photo-1511512578047-dfb367046420', 2),
(5, 'CBLOL 2026: paiN Gaming lidera a tabela após 5 rodadas', 'A paiN Gaming está invicta no CBLOL 2026 após 5 rodadas. A equipe vem mostrando um desempenho consistente e já é considerada a favorita ao título. O destaque fica para o mid-laner 4Dreamer, que tem o maior KDA da competição.', '2026-06-29 14:29:43', 3, 'https://images.unsplash.com/photo-1545239351-1141bd82e8a6', 3),
(6, 'Governo federal regulamenta profissão de atleta de esports no Brasil', 'O presidente sancionou a lei que regulamenta a profissão de atleta de esports no Brasil. A nova legislação garante direitos trabalhistas como carteira assinada, FGTS, férias e aposentadoria para os jogadores profissionais.', '2026-06-29 14:30:08', 3, 'https://images.unsplash.com/photo-1493711662062-fa541adb3fc8', 4),
(7, 'NVIDIA anuncia RTX 6090 com foco em gamers e streamers', 'A NVIDIA anunciou a nova RTX 6090, placa de vídeo topo de linha com foco em gamers e streamers. O hardware promete desempenho 4K a 240fps e recursos avançados de IA para streaming.', '2026-06-29 14:30:30', 3, 'https://images.unsplash.com/photo-1605647540924-852290f6b0d5', 5),
(8, 'Brasil terá 3 representantes no Major de CS2 pela primeira vez na história', 'Pela primeira vez na história, o Brasil terá três equipes representando o país em um Major de Counter-Strike 2. FURIA, MIBR e a nova promessa Sharks Esports garantiram vaga para o próximo Major que será realizado na Alemanha. A conquista histórica mostra o crescimento do cenário competitivo brasileiro e a força dos novos talentos que surgem no país. Os fãs já estão ansiosos para ver as equipes brilharem no palco internacional.', '2026-06-29 14:32:49', 3, 'https://images.unsplash.com/photo-1593305841991-05c297ba4575', 1),
(9, 'Brasil será sede do VALORANT Champions 2027', 'A Riot Games anunciou que o Brasil será o país sede do VALORANT Champions 2027, o maior torneio da modalidade no mundo. O evento acontecerá no Allianz Parque, em São Paulo, com capacidade para mais de 40 mil espectadores. A escolha do Brasil reflete o crescimento do cenário competitivo nacional, que tem se destacado com equipes como LOUD, FURIA e MIBR conquistando títulos internacionais.', '2026-06-29 14:33:23', 3, 'https://images.unsplash.com/photo-1538481199705-c710c4e965fc', 2),
(10, 'Riot Games revela novo campeão brasileiro no League of Legends', 'A Riot Games surpreendeu a comunidade ao revelar o novo campeão de League of Legends: Iara, uma maga inspirada na cultura brasileira. O personagem é baseado na lenda da Iara, com habilidades ligadas à água e à floresta amazônica. A revelação aconteceu durante o evento de abertura do CBLOL 2026 e foi recebida com entusiasmo pelos fãs brasileiros. O campeão já está disponível no servidor PBE e deve chegar ao jogo oficial na próxima atualização.', '2026-06-29 14:33:53', 3, 'https://images.unsplash.com/photo-1574629810360-7efbbe195018', 3),
(11, 'Brasil é eleito o melhor país para se viver de esports em 2026', 'O Brasil foi eleito o melhor país para se viver de esports em 2026, de acordo com um estudo realizado pela Esports Insider. O país se destacou pela profissionalização do setor, com a nova regulamentação da profissão de atleta, a grande quantidade de torneios presenciais e o alto engajamento dos fãs. Além disso, as organizações brasileiras vêm investindo em infraestrutura de ponta, com centros de treinamento de alto nível e salários competitivos para os jogadores.', '2026-06-29 14:34:15', 3, 'https://images.unsplash.com/photo-1596495578065-6e0763fa1178', 4),
(12, 'Free Fire anuncia modo competitivo com premiação de R$ 5 milhões', 'A Garena anunciou um novo modo competitivo para Free Fire, chamado \"Free Fire Championship\", com premiação total de R$ 5 milhões. O torneio será disputado por equipes de todo o mundo e terá transmissão ao vivo em várias plataformas. Além da premiação em dinheiro, os vencedores ganharão skins exclusivas e itens raros dentro do jogo. A primeira edição do campeonato começa em agosto e promete movimentar a comunidade global de Free Fire.', '2026-06-29 14:35:01', 3, 'https://images.unsplash.com/photo-1550745165-9bc0b252726f', 5);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `criado_em` datetime DEFAULT current_timestamp(),
  `username` varchar(50) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `criado_em`, `username`, `foto`) VALUES
(1, 'Admin', 'admin@ggnews.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-06-29 02:06:05', NULL, NULL),
(2, 'Mauricio De Campos', 'mauriciodecamposhihi@gmail.com', '$2y$10$.MqbJV3FwAeBC6DdhEBqkuHba9KKbAR5CowSoCt.rVKkwj3jTdO7y', '2026-06-29 02:06:05', '', NULL),
(3, 'davi foppa', 'davimalinha01@gmail.com', '$2y$10$CvP7AiL0WD.n3qZ2OsJlzOHUai8yWMEmKcwwTjrfEXoQF8TDI0t56', '2026-06-29 02:06:05', '', 'user_3_6a42a81cb8a73.png');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Índices de tabela `comentarios`
--
ALTER TABLE `comentarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `noticia_id` (`noticia_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `noticias`
--
ALTER TABLE `noticias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `autor` (`autor`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `comentarios`
--
ALTER TABLE `comentarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `noticias`
--
ALTER TABLE `noticias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `comentarios`
--
ALTER TABLE `comentarios`
  ADD CONSTRAINT `fk_comentarios_noticia` FOREIGN KEY (`noticia_id`) REFERENCES `noticias` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comentarios_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `noticias`
--
ALTER TABLE `noticias`
  ADD CONSTRAINT `fk_noticias_autor` FOREIGN KEY (`autor`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_noticias_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
