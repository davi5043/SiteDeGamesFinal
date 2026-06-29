-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 29/06/2026 às 03:40
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(8, 'Valorant inicia nova era competitiva com atualização histórica', 'Depois de meses de testes fechados, especulações da comunidade e diversas pistas divulgadas pelos desenvolvedores, Valorant recebeu uma das maiores atualizações desde o seu lançamento. A Riot Games implementou mudanças profundas no sistema competitivo, no balanceamento dos agentes e no desempenho geral do jogo, com o objetivo de tornar as partidas mais equilibradas e estratégicas. A atualização também trouxe melhorias significativas para os servidores, reduzindo problemas relacionados à latência e aumentando a estabilidade durante confrontos decisivos. Além disso, diversos mapas passaram por modificações estruturais para oferecer novas possibilidades táticas aos jogadores, incentivando estilos de jogo mais criativos e menos previsíveis.\r\n\r\nAs mudanças rapidamente movimentaram o cenário profissional. Organizações internacionais iniciaram sessões intensivas de treinamento para adaptar suas estratégias ao novo meta, enquanto jogadores profissionais destacaram que a atualização exige um nível ainda maior de comunicação entre as equipes. Agentes considerados indispensáveis perderam parte de sua eficiência, enquanto personagens pouco utilizados ganharam espaço nas composições competitivas. Analistas acreditam que essa reformulação poderá resultar em um dos cenários mais equilibrados da história do FPS, permitindo que equipes de diferentes estilos disputem títulos em condições semelhantes.\r\n\r\nA comunidade recebeu a atualização de forma bastante positiva. Nas redes sociais, milhares de jogadores compartilharam novas estratégias, clipes de jogadas impressionantes e análises detalhadas sobre as alterações implementadas. Criadores de conteúdo passaram horas testando as novidades em transmissões ao vivo, enquanto campeonatos amadores começaram a adotar imediatamente a nova versão do jogo. A expectativa é de que os próximos torneios internacionais apresentem partidas muito mais imprevisíveis, elevando ainda mais o nível competitivo do título.', '2026-06-28 21:31:05', 1, 'https://images.unsplash.com/photo-1542751371-adc38448a05e', 2),
(9, 'League of Legends bate recorde mundial de audiência durante campeonato internacional', 'As finais do principal campeonato internacional de League of Legends registraram números históricos de audiência, consolidando novamente o MOBA da Riot Games como um dos maiores fenômenos do entretenimento eletrônico mundial. Milhões de espectadores acompanharam simultaneamente a decisão por meio das plataformas de streaming oficiais e retransmissões autorizadas em diversos idiomas. O evento reuniu equipes tradicionais, organizações emergentes e jogadores considerados verdadeiras lendas do cenário competitivo, proporcionando uma série de confrontos extremamente disputados do início ao fim.\r\n\r\nAlém das partidas, o campeonato impressionou pela qualidade da produção. Cerimônias de abertura com efeitos visuais, apresentações musicais, realidade aumentada e anúncios exclusivos fizeram parte da programação oficial. A Riot também revelou novos conteúdos para a próxima temporada, incluindo campeões inéditos, mudanças no sistema ranqueado, skins comemorativas e atualizações no cliente do jogo. Especialistas destacaram que o investimento crescente em produção audiovisual aproxima cada vez mais os esportes eletrônicos dos grandes eventos esportivos tradicionais.\r\n\r\nApós o encerramento da competição, jogadores profissionais, técnicos e analistas elogiaram o elevado nível técnico apresentado pelas equipes finalistas. Muitas partidas foram decididas apenas nos minutos finais, demonstrando equilíbrio entre estratégia, mecânica individual e coordenação coletiva. Com os números divulgados oficialmente, o torneio entra para a história como um dos maiores eventos de eSports já realizados, reforçando o crescimento constante da modalidade em escala global.', '2026-06-28 21:31:05', 1, 'https://images.unsplash.com/photo-1511512578047-dfb367046420', 1),
(10, 'Counter-Strike apresenta novo mapa competitivo e muda estratégias das equipes', 'O cenário competitivo de Counter-Strike iniciou uma nova fase após a chegada de um mapa totalmente inédito ao conjunto oficial utilizado nos campeonatos. Desenvolvido durante vários meses, o novo ambiente foi construído levando em consideração o feedback de atletas profissionais, criadores de conteúdo e membros ativos da comunidade. O resultado é um mapa equilibrado, com múltiplas rotas de avanço, posições estratégicas para defesa e grande variedade de possibilidades táticas tanto para terroristas quanto para contra-terroristas.\r\n\r\nPoucas horas após o lançamento, organizações profissionais já começaram a realizar sessões intensivas de treinamento para compreender todos os detalhes do novo cenário. Técnicos passaram a estudar posicionamentos, tempos de rotação, utilização de granadas e possíveis estratégias ofensivas e defensivas capazes de surpreender adversários durante campeonatos oficiais. Especialistas acreditam que os primeiros torneios disputados no novo mapa apresentarão resultados imprevisíveis, justamente pela ausência de um estilo dominante consolidado.\r\n\r\nA comunidade reagiu de maneira bastante positiva às novidades. Milhares de vídeos explicando táticas, marcações de fumaça, granadas de efeito moral e jogadas inteligentes foram publicados poucas horas após a atualização. Servidores dedicados exclusivamente ao novo mapa registraram filas constantes de jogadores interessados em aprender cada detalhe do cenário. Com isso, Counter-Strike demonstra mais uma vez sua capacidade de se renovar sem perder a essência competitiva que o tornou referência entre os jogos de tiro.', '2026-06-28 21:31:05', 1, 'https://images.unsplash.com/photo-1542751110-97427bbecf20', 1),
(11, 'Fortnite transforma completamente seu universo com temporada futurista', 'A Epic Games lançou oficialmente uma nova temporada de Fortnite inspirada em tecnologias futuristas, modificando completamente a aparência do mapa principal. Grandes cidades receberam arranha-céus iluminados, sistemas de transporte avançados e estruturas metálicas gigantescas, enquanto novas regiões foram criadas para incentivar a exploração dos jogadores. Armas inéditas baseadas em energia, veículos voadores e equipamentos tecnológicos também passaram a integrar as partidas, alterando significativamente a dinâmica dos confrontos.\r\n\r\nO Passe de Batalha desta temporada trouxe dezenas de recompensas cosméticas inéditas, incluindo trajes exclusivos, acessórios, gestos personalizados e efeitos visuais completamente novos. Além disso, desafios semanais foram reformulados para incentivar os jogadores a explorarem diferentes regiões do mapa e experimentarem todas as novidades implementadas pela desenvolvedora. Segundo a Epic Games, o objetivo desta temporada é oferecer uma experiência muito mais dinâmica e variada durante toda sua duração.\r\n\r\nInfluenciadores, criadores de conteúdo e jogadores profissionais realizaram transmissões ao vivo nas primeiras horas após o lançamento, apresentando ao público as principais novidades da atualização. A comunidade elogiou especialmente a criatividade do novo mapa e o cuidado com os detalhes visuais. Analistas acreditam que a temporada possui potencial para se tornar uma das mais populares da história de Fortnite, mantendo milhões de jogadores ativos diariamente ao longo dos próximos meses.', '2026-06-28 21:31:05', 1, 'https://images.unsplash.com/photo-1493711662062-fa541adb3fc8', 2),
(12, 'Minecraft recebe atualização gigantesca focada em exploração e criatividade', 'A Mojang Studios disponibilizou uma das maiores atualizações já lançadas para Minecraft, ampliando significativamente as possibilidades de exploração dentro do jogo. Diversos biomas inéditos foram adicionados ao mundo gerado proceduralmente, trazendo vegetações exclusivas, novos animais, cavernas reformuladas e estruturas raras que podem ser encontradas durante longas expedições. Os desenvolvedores afirmaram que o objetivo principal desta atualização foi estimular a curiosidade dos jogadores e tornar cada jornada pelo mapa ainda mais recompensadora.\r\n\r\nAlém da exploração, a construção também recebeu melhorias importantes. Centenas de novos blocos decorativos foram incorporados ao jogo, permitindo que arquitetos da comunidade criem projetos muito mais detalhados e criativos. O sistema de iluminação foi otimizado em algumas regiões, novos efeitos sonoros foram implementados e diversas mecânicas receberam ajustes para melhorar o desempenho geral tanto na versão para computadores quanto nos consoles e dispositivos móveis. Servidores públicos já iniciaram adaptações para incorporar todos os novos recursos disponíveis.\r\n\r\nA recepção da comunidade foi extremamente positiva desde as primeiras horas após o lançamento. Redes sociais passaram a ser inundadas por imagens de construções impressionantes, descobertas de estruturas raras e aventuras realizadas nos novos biomas. Criadores de conteúdo também iniciaram séries completas dedicadas exclusivamente à atualização, mostrando passo a passo todas as novidades presentes no jogo. Com essa expansão, Minecraft reafirma sua posição como um dos jogos mais influentes e duradouros da história da indústria dos videogames.', '2026-06-28 21:31:05', 1, 'https://images.unsplash.com/photo-1550745165-9bc0b252726f', 3),
(33, 'CS2 implementa sistema de treinamento avançado', 'A Valve lançou um novo modo de treinamento interativo que inclui desafios de mira, simulações de situação de jogo e tutoriais detalhados sobre grenades nos mapas oficiais. A ferramenta promete acelerar o aprendizado de novos jogadores e refinar as habilidades dos veteranos, com estatísticas detalhadas para análise de desempenho.', '2026-06-28 22:08:14', 1, 'https://images.unsplash.com/photo-1542751110-97427bbecf20', 1),
(34, 'VALORANT anuncia novo agente especialista em reconhecimento', 'A Riot Games revelou o mais novo agente do jogo, equipado com habilidades focadas em coleta de informações táticas e controle de área. Sua ultimate permite revelar toda a equipe inimiga no mapa, mudando completamente a dinâmica das rodadas. A comunidade já está debatendo possíveis sinergias com outros agentes.', '2026-06-28 22:08:14', 1, 'https://images.unsplash.com/photo-1542751371-adc38448a05e', 1),
(35, 'League of Legends anuncia rework completo de campeão clássico', 'O campeão receberá novas habilidades, visuais atualizados e uma lore expandida, buscando modernizar sua jogabilidade e torná-lo mais relevante no meta atual. Os desenvolvedores prometem manter a essência do personagem enquanto adicionam mecânicas mais interativas e fluidas para partidas profissionais.', '2026-06-28 22:08:14', 1, 'https://images.unsplash.com/photo-1511512578047-dfb367046420', 1),
(37, 'Geral: Xbox Game Pass adiciona 10 novos títulos', 'A Microsoft confirmou a chegada de novos jogos ao catálogo, incluindo lançamentos de peso e clássicos indie aclamados pela crítica. A lista inclui opções para todos os gostos, desde RPGs extensos até experiências casuais, reforçando a posição do serviço como um dos melhores custo-benefício do mercado.', '2026-06-28 22:08:14', 1, 'https://images.unsplash.com/photo-1518770660439-4636190af475', 5),
(38, 'CS2 atualiza economia das partidas competitivas', 'Mudanças significativas nos valores de recompensa por kills e abates com fuzil alteram completamente as estratégias de gerenciamento financeiro das equipes. Especialistas apontam que as mudanças incentivam rounds mais agressivos e reduzem a vantagem econômica excessiva de times dominantes.', '2026-06-28 22:08:14', 1, 'https://images.unsplash.com/photo-1511882150382-421056c89033', 1),
(39, 'VALORANT apresenta novo mapa inspirado na Índia', 'O mapa conta com elementos arquitetônicos tradicionais e mecânicas inéditas de teleporte que criam novas possibilidades táticas. Durante os primeiros dias de teste, jogadores profissionais já descobriram ângulos surpreendentes e estratégias criativas para ataque e defesa.', '2026-06-28 22:08:14', 1, 'https://images.unsplash.com/photo-1542751110-97427bbecf20', 1),
(40, 'League of Legends: novo modo de jogo temporário faz sucesso', 'O modo radical introduz mecânicas completamente diferentes do jogo base, com evolução instantânea de campeões, habilidades modificadas e regras dinâmicas que mudam a cada partida. A Riot planeja expandir o conceito se a recepção positiva continuar.', '2026-06-28 22:08:14', 1, 'https://images.unsplash.com/photo-1493711662062-fa541adb3fc8', 1),
(41, 'Esports: equipe brasileira vence campeonato internacional de VALORANT', 'Após uma campanha impecável, a equipe derrotou favoritos mundiais em uma grande final emocionante. A vitória marca um momento histórico para o cenário brasileiro, consolidando o país como potência no FPS tático e inspirando novas gerações de jogadores.', '2026-06-28 22:08:14', 1, 'https://images.unsplash.com/photo-1511512578047-dfb367046420', 5),
(42, 'Geral: Assassin\'s Creed ganha adaptação para animação', 'Produtora anunciou parceria com estúdio de animação para produzir série inspirada na franquia, com lançamento previsto para 2027. O projeto promete expandir o universo da série, apresentando novos personagens e explorando períodos históricos ainda não abordados nos jogos.', '2026-06-28 22:08:14', 1, 'https://images.unsplash.com/photo-1486572788966-cfd3df1f5b42', 5),
(43, 'CS2: comunidade cria mapa de surf oficial', 'Após anos de pedidos, a Valve incorporou ao jogo um mapa de surf criado pela comunidade, com validação competitiva. O mapa apresenta obstáculos inéditos e curvas que desafiam até os jogadores mais experientes, além de incluir melhorias visuais significativas.', '2026-06-28 22:08:14', 1, 'https://images.unsplash.com/photo-1550745165-9bc0b252726f', 1),
(44, 'VALORANT adiciona modo deathmatch competitivo', 'O novo modo permite que os jogadores pratiquem mira e duelos em um ambiente mais dinâmico, com leaderboards globais e recompensas exclusivas. A Riot busca oferecer uma alternativa mais descontraída para quem quer treinar sem a pressão das partidas ranqueadas.', '2026-06-28 22:08:14', 1, 'https://images.unsplash.com/photo-1542751371-adc38448a05e', 1),
(45, 'League of Legends renova sistema de recompensas', 'Novo sistema de progressão oferece recompensas mais significativas para jogadores que demonstram bom comportamento e desempenho consistente. Os desenvolvedores esperam incentivar uma comunidade mais positiva e engajada, reduzindo toxicidade e melhorando a experiência geral.', '2026-06-28 22:08:14', 1, 'https://images.unsplash.com/photo-1518770660439-4636190af475', 1),
(46, 'Esports: primeira liga feminina de CS2 é anunciada', 'Organização revelou competição exclusiva para equipes femininas com premiações equiparadas aos torneios masculinos. A iniciativa busca promover maior diversidade no cenário e criar oportunidades para novas talentos, com formato de liga regular e playoffs ao vivo.', '2026-06-28 22:08:14', 1, 'https://images.unsplash.com/photo-1511882150382-421056c89033', 5),
(48, 'CS2 atualiza sistema de ranking global', 'Novo sistema utiliza inteligência artificial para avaliar desempenho individual de forma mais precisa, considerando aspectos como contribuição tática, precisão e suporte à equipe. A mudança já está sendo testada no modo competitivo e deve ser expandida para todas as filas em breve.', '2026-06-28 22:08:14', 1, 'https://images.unsplash.com/photo-1542751110-97427bbecf20', 1),
(49, 'VALORANT lança pacote de skins com efeitos dinâmicos', 'As novas skins variam de cor e padrão conforme o desempenho do jogador durante a partida, criando uma experiência visual única e personalizada. O pacote inclui variantes para os principais modelos de armas e já está sendo considerado um dos mais criativos já lançados.', '2026-06-28 22:08:14', 1, 'https://images.unsplash.com/photo-1493711662062-fa541adb3fc8', 1),
(50, 'League of Legends revela nova rota experimental', 'Em um evento especial, a Riot apresentou uma rota alternativa que pode ser ativada em partidas personalizadas, com objetivos diferenciados e mecânicas exclusivas. Embora ainda seja um teste, a ideia já está gerando grande discussão na comunidade sobre seu potencial competitivo.', '2026-06-28 22:08:14', 1, 'https://images.unsplash.com/photo-1486572788966-cfd3df1f5b42', 1),
(51, 'Esports: recorde de premiação em torneio de VALORANT', 'O campeonato mundial distribuirá o maior prêmio da história do FPS, superando marcas anteriores e atraindo investimentos de grandes marcas globais. A final será realizada em estádio com capacidade para mais de 20 mil espectadores, prometendo um espetáculo inesquecível.', '2026-06-28 22:08:14', 1, 'https://images.unsplash.com/photo-1511512578047-dfb367046420', 5),
(52, 'Geral: saiba quais jogos chegam em julho de 2026', 'Lista inclui lançamentos aguardados como o novo RPG de ação da Square Enix, a continuação do sucesso indie de plataforma e a nova expansão de simulador espacial. Julho promete ser um dos meses mais movimentados para lançamentos, com opções para todos os tipos de jogadores.', '2026-06-28 22:08:14', 1, 'https://images.unsplash.com/photo-1518770660439-4636190af475', 5);

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
(1, 'Admin', 'admin@ggnews.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-06-26 18:31:02', NULL, NULL),
(2, 'Mauricio De Campos', 'mauriciodecamposhihi@gmail.com', '$2y$10$.MqbJV3FwAeBC6DdhEBqkuHba9KKbAR5CowSoCt.rVKkwj3jTdO7y', '2026-06-26 18:31:02', '', NULL),
(3, 'davi foppa', 'davimalinha01@gmail.com', '$2y$10$N0sLEEXWtV01jInctuZ6J.O0GiI88Hck0OAw/jVFpd0nyxdcLlpRW', '2026-06-26 15:32:22', '', 'user_3_6a3ee02c711fa.jpg');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `noticias`
--
ALTER TABLE `noticias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

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
  ADD CONSTRAINT `comentarios_ibfk_1` FOREIGN KEY (`noticia_id`) REFERENCES `noticias` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comentarios_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `noticias`
--
ALTER TABLE `noticias`
  ADD CONSTRAINT `noticias_ibfk_1` FOREIGN KEY (`autor`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `noticias_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
