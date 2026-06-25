-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 24/06/2026 às 02:56
-- Versão do servidor: 10.4.28-MariaDB
-- Versão do PHP: 8.0.28

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
-- Estrutura para tabela `noticias`
--

CREATE TABLE `noticias` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `noticia` text NOT NULL,
  `data` datetime DEFAULT current_timestamp(),
  `autor` int(11) NOT NULL,
  `imagem` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `noticias`
--

INSERT INTO `noticias` (`id`, `titulo`, `noticia`, `data`, `autor`, `imagem`) VALUES
(6, 'Team Aurora conquista título internacional de CS2', 'Data: 22/06/2026\r\n\r\nA Team Aurora surpreendeu o cenário mundial ao derrotar a favorita Titan Gaming por 3 a 1 na grande final do campeonato internacional de Counter-Strike 2. A equipe mostrou um desempenho dominante nos mapas Mirage e Ancient, garantindo o troféu e uma premiação milionária.\r\n\r\nAnalistas destacaram a atuação do jogador \"Raven\", que terminou a série com o maior número de eliminações da final.', '2026-06-23 21:22:57', 1, 'https://images.unsplash.com/photo-1542751371-adc38448a05e'),
(7, 'Organização anuncia entrada no cenário de VALORANT', 'A organização Phoenix Esports anunciou oficialmente sua entrada no competitivo de VALORANT. O elenco contará com jogadores experientes da América do Sul e um treinador europeu com passagem por equipes campeãs.\r\n\r\nA estreia está prevista para o próximo split regional.', '2026-06-23 21:27:21', 1, 'https://images.unsplash.com/photo-1511512578047-dfb367046420'),
(8, 'Campeonato Universitário de Esports bate recordes e consolida crescimento do setor', 'Categoria: Esports Universitário\r\nData: 20/06/2026\r\nAutor: Redação Esports News\r\n\r\nO Campeonato Universitário de Esports alcançou números históricos nesta temporada e confirmou a força do segmento estudantil dentro do mercado competitivo.\r\n\r\nSegundo os organizadores, mais de 500 mil espectadores únicos acompanharam as transmissões ao longo da competição. O evento reuniu equipes de dezenas de instituições de ensino e distribuiu bolsas de estudo, equipamentos e premiações em dinheiro.\r\n\r\nAlém dos jogos, os participantes tiveram acesso a palestras sobre carreira profissional, produção de conteúdo, gestão esportiva e tecnologia aplicada aos esports.\r\n\r\nDiversos atletas que atualmente competem em equipes profissionais iniciaram suas carreiras em campeonatos universitários semelhantes. Por esse motivo, especialistas consideram essas competições essenciais para a renovação do cenário competitivo.\r\n\r\nOs organizadores já confirmaram uma expansão para a próxima edição, que contará com novas modalidades e mais vagas para equipes participantes.\r\n\r\nImagem:\r\nhttps://images.unsplash.com/photo-1493711662062-fa541adb3fc8\r\n\r\nJogador brasileiro é eleito MVP mundial após temporada espetacular\r\n\r\nCategoria: Esports Internacional\r\nData: 19/06/2026\r\nAutor: Redação Esports News\r\n\r\nO brasileiro Lucas \"Shadow\" Martins foi eleito o jogador mais valioso da temporada em uma premiação que reuniu representantes das principais organizações de esports do mundo.\r\n\r\nA escolha foi baseada no desempenho apresentado ao longo do ano, considerando estatísticas individuais, conquistas coletivas e impacto dentro das partidas.\r\n\r\nShadow participou de cinco finais internacionais durante a temporada e conquistou três títulos de grande expressão. Seu desempenho chamou a atenção de analistas, patrocinadores e torcedores, consolidando seu nome entre os maiores atletas da modalidade.\r\n\r\nDurante seu discurso de agradecimento, o jogador destacou a importância da família, da comissão técnica e dos fãs que acompanharam sua trajetória desde o início da carreira.\r\n\r\nA premiação também reforça o crescimento dos atletas brasileiros no cenário internacional, demonstrando a competitividade da região em torneios globais.', '2026-06-23 21:28:07', 1, 'https://images.unsplash.com/photo-1560253023-3ec5d502959f'),
(9, 'Nova arena de esports promete transformar cenário competitivo da América do Sul', 'Categoria: Infraestrutura\r\nData: 18/06/2026\r\nAutor: Redação Esports News\r\n\r\nFoi inaugurada nesta semana uma das maiores arenas dedicadas exclusivamente aos esportes eletrônicos na América do Sul. O espaço foi projetado para receber eventos internacionais e possui estrutura comparável às principais arenas da Ásia, Europa e América do Norte.\r\n\r\nA arena conta com capacidade para mais de oito mil espectadores, telões de alta definição, sistema avançado de iluminação, estúdios de transmissão, áreas de treinamento e espaços voltados para criadores de conteúdo.\r\n\r\nOs investidores acreditam que a nova estrutura ajudará a atrair campeonatos internacionais para a região, fortalecendo o mercado local e gerando oportunidades para atletas, organizadores e empresas parceiras.\r\n\r\nA inauguração contou com partidas de exibição, apresentações musicais e participação de influenciadores famosos do universo gamer.\r\n\r\nEspecialistas afirmam que a profissionalização da infraestrutura é um passo importante para o amadurecimento do setor e para o aumento da competitividade regional.', '2026-06-23 21:28:37', 1, 'https://images.unsplash.com/photo-1511882150382-421056c89033'),
(10, 'League of Legends registra crescimento recorde de audiência em campeonato mundial', 'League of Legends registra crescimento recorde de audiência em campeonato mundial\r\n\r\nCategoria: League of Legends\r\nData: 17/06/2026\r\nAutor: Redação Esports News\r\n\r\nO cenário competitivo de League of Legends voltou a demonstrar sua força após registrar números impressionantes de audiência durante a fase principal do campeonato mundial.\r\n\r\nAs transmissões foram acompanhadas por milhões de espectadores simultaneamente em diversas plataformas de streaming. O crescimento foi impulsionado pela participação de equipes tradicionais, rivalidades históricas e uma produção considerada uma das melhores já realizadas no cenário competitivo.\r\n\r\nAnalistas destacaram a qualidade técnica das partidas e o equilíbrio entre as equipes participantes. Diversos confrontos foram decididos apenas nos momentos finais, aumentando ainda mais o interesse do público.\r\n\r\nAlém da audiência, o torneio também movimentou patrocinadores, marcas de tecnologia e empresas do setor de entretenimento, consolidando os esports como uma das indústrias de crescimento mais acelerado do mundo.', '2026-06-23 21:29:13', 1, 'https://images.unsplash.com/photo-1545239351-1141bd82e8a6');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `noticias`
--
ALTER TABLE `noticias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `autor` (`autor`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `noticias`
--
ALTER TABLE `noticias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `noticias`
--
ALTER TABLE `noticias`
  ADD CONSTRAINT `noticias_ibfk_1` FOREIGN KEY (`autor`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
