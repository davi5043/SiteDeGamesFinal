-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 26/06/2026 às 02:15
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `noticias`
--

INSERT INTO `noticias` (`id`, `titulo`, `noticia`, `data`, `autor`, `imagem`, `categoria_id`) VALUES
(6, 'Team Aurora conquista título internacional de CS2', 'A Team Aurora surpreendeu o cenário mundial ao derrotar a favorita Titan Gaming por 3 a 1 na grande final do campeonato internacional de Counter-Strike 2. A equipe mostrou um desempenho dominante nos mapas Mirage e Ancient, garantindo o troféu e uma premiação milionária.\r\n\r\nAnalistas destacaram a atuação do jogador \"Raven\", que terminou a série com o maior número de eliminações da final.', '2026-06-23 21:22:57', 1, 'https://images.unsplash.com/photo-1542751371-adc38448a05e', 1),
(7, 'Organização anuncia entrada no cenário de VALORANT', 'A organização Phoenix Esports anunciou oficialmente sua entrada no competitivo de VALORANT. O elenco contará com jogadores experientes da América do Sul e um treinador europeu com passagem por equipes campeãs.\r\n\r\nA estreia está prevista para o próximo split regional.', '2026-06-23 21:27:21', 1, 'https://images.unsplash.com/photo-1511512578047-dfb367046420', 2),
(8, 'Campeonato Universitário de Esports bate recordes e consolida crescimento do setor', 'O Campeonato Universitário de Esports alcançou números históricos nesta temporada e confirmou a força do segmento estudantil dentro do mercado competitivo.\r\n\r\nSegundo os organizadores, mais de 500 mil espectadores únicos acompanharam as transmissões ao longo da competição. O evento reuniu equipes de dezenas de instituições de ensino e distribuiu bolsas de estudo, equipamentos e premiações em dinheiro.', '2026-06-23 21:28:07', 1, 'https://images.unsplash.com/photo-1493711662062-fa541adb3fc8', 4),
(9, 'Nova arena de esports promete transformar cenário competitivo da América do Sul', 'Foi inaugurada nesta semana uma das maiores arenas dedicadas exclusivamente aos esportes eletrônicos na América do Sul. O espaço foi projetado para receber eventos internacionais e possui estrutura comparável às principais arenas da Ásia, Europa e América do Norte.\r\n\r\nA arena conta com capacidade para mais de oito mil espectadores, telões de alta definição, sistema avançado de iluminação, estúdios de transmissão, áreas de treinamento e espaços voltados para criadores de conteúdo.', '2026-06-23 21:28:37', 1, 'https://images.unsplash.com/photo-1511882150382-421056c89033', 4),
(10, 'League of Legends registra crescimento recorde de audiência em campeonato mundial', 'O cenário competitivo de League of Legends voltou a demonstrar sua força após registrar números impressionantes de audiência durante a fase principal do campeonato mundial.\r\n\r\nAs transmissões foram acompanhadas por milhões de espectadores simultaneamente em diversas plataformas de streaming. O crescimento foi impulsionado pela participação de equipes tradicionais, rivalidades históricas e uma produção considerada uma das melhores já realizadas no cenário competitivo.', '2026-06-23 21:29:13', 1, 'https://images.unsplash.com/photo-1545239351-1141bd82e8a6', 3),
(11, 'LOUD conquista o VALORANT Champions e entra para a história do Brasil', 'A LOUD se tornou a primeira organização brasileira a conquistar o título do VALORANT Champions, o maior torneio da modalidade no mundo. A equipe eliminou a favorita norte-americana NRG por 3 sets a 1 na grande final disputada em Seul, Coreia do Sul.\n\nO destaque da série foi o jogador aspas, que encerrou a final com 34 eliminações e foi eleito o MVP do torneio. A torcida brasileira lotou as redes sociais com celebrações, tornando o hashtag #VCT_LOUD um dos assuntos mais comentados do planeta.\n\nO título representa um marco histórico para os esports nacionais e consolida o Brasil como uma das maiores potências competitivas de VALORANT no mundo.', '2026-06-22 10:00:00', 1, 'https://images.unsplash.com/photo-1542751371-adc38448a05e', 2),
(12, 'FURIA recebe aporte de R$ 120 milhões e anuncia expansão para Europa', 'A FURIA Esports anunciou um investimento histórico de R$ 120 milhões liderado por um grupo de fundos de venture capital brasileiros e internacionais. O capital será utilizado para expandir as operações da organização para o mercado europeu.\n\nCom o aporte, a FURIA planeja abrir uma sede em Lisboa, Portugal, contratar atletas europeus para suas equipes de CS2 e VALORANT, e desenvolver uma divisão de conteúdo voltada ao público da Europa.\n\nO CEO da organização afirmou que este é o maior investimento já realizado em uma organização de esports da América do Sul, e que o objetivo é tornar a FURIA uma das cinco maiores do mundo até 2028.', '2026-06-21 14:30:00', 1, 'https://images.unsplash.com/photo-1526304640581-d334cdbbf45e', 4),
(13, 'Riot Games anuncia novo agente de VALORANT com habilidades de controle de área', 'A Riot Games revelou oficialmente o próximo agente de VALORANT: Spectra, uma controladora de origem indiana com habilidades focadas em visão e controle de área. O personagem será lançado no início do próximo episódio.\n\nSpectra possui uma habilidade ultimate capaz de criar uma zona de neblina que bloqueia completamente a visão dos inimigos em um raio de dez metros, além de uma smoke de recarga automática e uma ferramenta de reconhecimento silencioso.\n\nOs designers do jogo explicaram em um blog post que Spectra foi criada para preencher uma lacuna no meta competitivo, oferecendo uma alternativa mais versátil para equipes que dependem de controle de mapa nos turnos de ataque.', '2026-06-20 16:00:00', 1, 'https://images.unsplash.com/photo-1511512578047-dfb367046420', 2),
(14, 'CBLOL 2026: análise completa dos favoritos à taça de inverno', 'O segundo split do CBLOL 2026 está a todo vapor e as disputas pela liderança entre as organizações brasileiras prometem uma fase playoffs eletrizante. Confira a análise dos principais candidatos ao título.\n\nA paiN Gaming lidera a tabela com aproveitamento de 78%, impulsionada pelo desempenho consistente do mid-laner 4Dreamer, que apresenta o maior KDA entre todos os jogadores da competição nesta temporada.\n\nLOUD e RED Canids aparecem logo atrás e devem garantir presença nas quartas de final com folga. A surpresa positiva do split é a Fluxo, que venceu cinco jogos consecutivos após uma reformulação no elenco no início do mês.', '2026-06-19 18:00:00', 1, 'https://images.unsplash.com/photo-1545239351-1141bd82e8a6', 3),
(15, 'Brasileiro é contratado pela T1 para reforçar equipe de CS2 na temporada europeia', 'O awper brasileiro Marcos \"Coldzera Jr\" Pereira, de apenas 19 anos, foi anunciado como novo integrante da T1 para a divisão de CS2. O jogador, que se destacou na FURIA Academy, assinou contrato de dois anos com a organização sul-coreana.\n\nA contratação chama atenção por ser a primeira vez que a T1 aposta em um brasileiro para sua equipe principal de um FPS competitivo. O head coach da divisão disse que o atleta impressionou nos scrims durante o bootcamp de avaliação realizado no mês passado em Seul.\n\nColdzera Jr viaja para a Coreia do Sul nos próximos dias para iniciar os treinos com o novo elenco antes da estreia no campeonato regional europeu.', '2026-06-18 11:00:00', 1, 'https://images.unsplash.com/photo-1593305841991-05c297ba4575', 1),
(16, 'Free Fire World Series registra 6 milhões de espectadores simultâneos', 'O Free Fire World Series 2026 bateu seu próprio recorde de audiência ao reunir mais de 6 milhões de espectadores simultâneos durante a grande final realizada em Bangkok, Tailândia. O número supera em 40% o pico registrado na edição anterior.\n\nA equipe brasileira Corinthians Esports chegou à semifinal e foi eliminada pela eventual campeã, a representante tailandesa Thunder. A partida entre as duas equipes foi o momento de maior audiência do torneio.\n\nO crescimento expressivo do Free Fire competitivo confirma o jogo como um dos maiores títulos de esports mobile do mundo, especialmente no sudeste asiático e na América Latina.', '2026-06-17 20:00:00', 1, 'https://images.unsplash.com/photo-1550745165-9bc0b252726f', 4),
(17, 'Rocket League: equipe gaúcha chega à final sul-americana pela primeira vez', 'A Vortex RS, equipe de Caxias do Sul, garantiu sua vaga inédita na grande final do Campeonato Sul-Americano de Rocket League após eliminar a favorita chilena Galáctico por 4 a 2 na semifinal disputada de forma online.\n\nA conquista é histórica para o Rio Grande do Sul, estado que raramente havia chegado às fases finais de competições regionais de Rocket League. O trio formado por Fenix, Striker e Blaze vem treinando juntos há dois anos e atribuem o sucesso ao foco em mecânica aérea avançada.\n\nA final acontece na próxima semana e o vencedor garante vaga direta no torneio mundial da Psyonix, com premiação de US$ 50 mil.', '2026-06-16 15:00:00', 1, 'https://images.unsplash.com/photo-1616588589676-62b3bd4ff6d2', 4),
(18, 'Valve anuncia The International 2026 com premiação recorde de US$ 40 milhões', 'A Valve revelou os detalhes do The International 2026, o maior torneio de Dota 2 da história. A premiação total chegará a US$ 40 milhões, com US$ 18 milhões destinados ao campeão. O evento será realizado em São Paulo, marcando a primeira edição do TI no Brasil.\n\nA escolha da cidade foi recebida com euforia pela comunidade brasileira de Dota 2. O Ginásio do Ibirapuera passará por adaptações para receber o evento, que está previsto para outubro de 2026 e deve atrair mais de 20 mil espectadores presenciais por dia.\n\nAo menos três equipes brasileiras estão em processo de classificação para o torneio através do circuito regional, o que pode resultar em uma presença inédita do país no palco principal do TI.', '2026-06-15 09:00:00', 1, 'https://images.unsplash.com/photo-1560253023-3ec5d502959f', 4),
(19, 'Jogadora brasileira se torna a primeira mulher a vencer etapa do circuito mundial de SF6', 'Isabela \"Isamaki\" Carvalho, de Belo Horizonte, conquistou sua primeira vitória em uma etapa do Capcom Pro Tour de Street Fighter 6, tornando-se a primeira mulher a vencer um evento oficial do circuito mundial do game.\n\nIsamaki derrotou o atual campeão mundial japonês Daigo Umehara nas semifinais e venceu o final contra o norte-americano GrandMaster em uma série emocionante de cinco games. Ela utilizou Cammy durante toda a competição e impressionou os analistas com sua precisão em combos de punição.\n\nA vitória repercutiu internacionalmente e o canal oficial da Capcom publicou um vídeo de destaque sobre sua trajetória, que acumulou mais de 3 milhões de visualizações em 24 horas.', '2026-06-14 17:00:00', 1, 'https://images.unsplash.com/photo-1535016120720-40c646be5580', 5),
(20, 'Nvidia anuncia GeForce RTX 6090 com foco em streaming e IA para criadores de conteúdo', 'A Nvidia apresentou oficialmente a GeForce RTX 6090, sua mais nova placa de vídeo topo de linha, com foco em criadores de conteúdo, streamers profissionais e jogadores de alto nível. O hardware promete encodagem em 4K a 240fps com latência zero utilizando o novo encoder NVENC de sexta geração.\n\nPara os streamers, o destaque é o modo \"Broadcast AI\", que ajusta automaticamente a taxa de bits, resolução e filtragem de ruído de áudio em tempo real usando aprendizado de máquina, sem consumir recursos da GPU principal durante o jogo.\n\nA placa chega ao mercado brasileiro no segundo semestre de 2026, com preço sugerido de R$ 12.990. A pré-venda nos Estados Unidos se esgotou em menos de seis horas após o anúncio.', '2026-06-13 12:00:00', 1, 'https://images.unsplash.com/photo-1605647540924-852290f6b0d5', 5),
(21, 'Apex Legends lança nova temporada com lenda inspirada na cultura nordestina brasileira', 'A Respawn Entertainment surpreendeu a comunidade ao revelar que a próxima lenda de Apex Legends, chamada Repentista, foi inspirada na cultura popular do nordeste brasileiro. O personagem é um rapper de cordel com habilidades baseadas em som e ilusão.\n\nA revelação foi recebida com enorme entusiasmo no Brasil. Repentista possui uma ultimate que cria duplicatas sonoras de todos os aliados, confundindo inimigos por até oito segundos, e uma passiva que reduz o ruído de seus passos ao caminhar em linha reta.\n\nA Respawn contratou consultores culturais brasileiros e um artista de cordel de Caruaru para garantir autenticidade na construção do personagem. A temporada tem início na próxima semana.', '2026-06-12 14:00:00', 1, 'https://images.unsplash.com/photo-1538481199705-c710c4e965fc', 5),
(22, 'Overwatch 2 anuncia liga universitária brasileira com bolsas de estudo como premiação', 'A Blizzard Entertainment, em parceria com o Ministério da Educação, anunciou a primeira Liga Universitária de Overwatch 2 do Brasil. A competição reunirá equipes de instituições de ensino superior de todo o país, com bolsas de estudo integrais como principal premiação.\n\nAs inscrições estão abertas para times de cinco jogadores matriculados em qualquer curso superior no Brasil. A liga será dividida em conferências regionais, com as melhores equipes se classificando para uma fase nacional presencial a ser realizada em Brasília.\n\nAlém das bolsas, os participantes terão acesso a mentorias com profissionais do setor de esports, workshops de gestão e produção de conteúdo, e oportunidades de estágio em empresas parceiras do programa.', '2026-06-11 10:30:00', 1, 'https://images.unsplash.com/photo-1471958680802-1345a694ba6d', 4),
(23, 'Twitch Brasil bate recorde histórico com 2,8 milhões ao vivo durante final do CBLOL', 'A final do primeiro split do CBLOL 2026 entre paiN Gaming e LOUD estabeleceu um novo recorde de audiência da Twitch Brasil, com 2,8 milhões de espectadores simultâneos no pico da transmissão. O número supera o anterior em 400 mil viewers.\n\nA partida foi transmitida simultaneamente nas plataformas YouTube e Twitch, além de canais de TV fechada. Os analistas estimam que o número total de espectadores, somando todas as plataformas, chegou próximo de 5 milhões no momento do teamfight decisivo do jogo 5.\n\nO evento movimentou também o mercado publicitário: cinco marcas nacionais de grande porte estrearam patrocínios em esports durante a transmissão, sinalizando o interesse crescente do setor corporativo pela audiência gamer.', '2026-06-10 22:00:00', 1, 'https://images.unsplash.com/photo-1574629810360-7efbbe195018', 3),
(24, 'Governo federal regulamenta profissão de atleta de esports no Brasil', 'O presidente da República sancionou a lei que regulamenta a profissão de atleta de esports no Brasil, tornando o país um dos primeiros do mundo a reconhecer oficialmente os jogadores profissionais de jogos eletrônicos como trabalhadores com direitos garantidos por lei.\n\nA nova legislação garante carteira assinada, FGTS, férias remuneradas, aposentadoria e cobertura pelo INSS para jogadores com contrato formal com organizações esportivas. As equipes terão prazo de doze meses para adequar seus contratos à nova norma.\n\nRepresentantes de organizações como LOUD, FURIA e paiN Gaming participaram das audiências públicas que antecederam a aprovação do texto final. A lei é considerada um avanço histórico para a profissionalização do setor no Brasil.', '2026-06-09 16:00:00', 1, 'https://images.unsplash.com/photo-1567427017947-545c5f8d16ad', 4),
(25, 'EA FC 26 é revelado com novo modo Esports Career e parceria com CBLOL', 'A EA Sports revelou o EA FC 26 com um conjunto de novidades voltadas ao cenário competitivo. O destaque é o modo Esports Career, que permite ao jogador gerenciar uma organização de futebol eletrônico, contratar jogadores, definir estratégias de treino e competir em ligas simuladas baseadas em torneios reais.\n\nA parceria com o CBLOL traz ao jogo os arenas e camisas de nove organizações brasileiras de esports, que poderão ser usadas tanto no Ultimate Team quanto no modo de clubes. A Riot Games colaborou no desenvolvimento para garantir a autenticidade dos assets visuais.\n\nO lançamento oficial está previsto para setembro de 2026, com acesso antecipado para assinantes do EA Play a partir de 20 de agosto.', '2026-06-08 11:00:00', 1, 'https://images.unsplash.com/photo-1579952363873-27f3bade9f55', 5),
(26, 'Torneio presencial de Tekken 8 lota arena em São Paulo com 3 mil espectadores', 'O SP Fighting Championship de Tekken 8 reuniu mais de três mil espectadores no Espaço Unimed, em São Paulo, consolidando-se como o maior evento de jogos de luta já realizado no Brasil. A arena ficou lotada para as finais do torneio, que contou com participantes de dezoito países.\n\nO campeão foi o paulistano Rafael \"Dragunov BR\" Souza, que venceu o adversário mexicano em uma final eletrizante de cinco rounds. Rafael utilizou Dragunov durante toda a competição e foi ovacionado de pé pela plateia ao confirmar o título.\n\nO evento também contou com painéis de jogadores profissionais, campeonatos amadores, área de exposição de periféricos e uma fila para testar o próximo DLC do game, que inclui um personagem brasileiro inédito.', '2026-06-07 19:00:00', 1, 'https://images.unsplash.com/photo-1580327344181-c1163234e5a0', 5),
(27, 'HyperX lança headset profissional desenvolvido em colaboração com atleta da FURIA', 'A HyperX anunciou o Cloud Esports Pro Edition, desenvolvido em parceria com o jogador de CS2 da FURIA, arT. O periférico foi criado a partir de meses de feedback do atleta durante treinos e competições internacionais.\n\nO headset traz drivers de 53mm com cancelamento de ruído ativo, suporte de alumínio anodizado com ajuste de tensão personalizável, e espumas de memória com certificação de uso prolongado. A latência ultrabaixa de 2ms é o principal diferencial para o uso competitivo.\n\nO produto será lançado no Brasil por R$ 799, com uma edição limitada assinada por arT disponível no site oficial da FURIA. Cada unidade da edição especial inclui o número da carteirinha profissional do atleta estampado na lateral do headset.', '2026-06-06 13:00:00', 1, 'https://images.unsplash.com/photo-1452780212458-61f2f4e7a9c4', 5),
(28, 'PUBG Mobile Championship nacional define os quatro finalistas do torneio', 'A fase de grupos do PUBG Mobile Brasil Championship definiu os quatro finalistas que disputarão o título nacional no próximo fim de semana. As equipes classificadas são: Fluxo Esports, Corinthians Gaming, Vikings Esports e a estreante Team Phoenix.\n\nA grande surpresa foi a eliminação da atual campeã NiP Brasil na fase de quartas de final, vítima de uma virada histórica da Team Phoenix nos últimos círculos da partida decisiva. O capitão da equipe revelou que a estratégia de usar veículos para reposicionamento rápido foi desenvolvida especificamente para aquele mapa.\n\nA final presencial acontece no Ginásio Nilson Nelson, em Brasília, com transmissão ao vivo pelo canal oficial do PUBG Mobile no YouTube. A premiação para o campeão é de R$ 250 mil.', '2026-06-05 14:00:00', 1, 'https://images.unsplash.com/photo-1596495578065-6e0763fa1178', 4),
(29, 'paiN Gaming contrata treinador campeão mundial para comandar divisão de LoL', 'A paiN Gaming anunciou a contratação do treinador sul-coreano Kim \"Guru\" Jae-won para assumir o comando técnico de sua divisão de League of Legends. Guru foi o responsável por conduzir o T1 ao título mundial em 2024 e tem histórico de três campeonatos internacionais em sua carreira.\n\nA chegada do treinador é considerada a maior contratação da história da organização e reforça a ambição da paiN em superar o desempenho histórico no Worlds de 2026. O técnico já está em São Paulo e iniciou os bootcamps com o elenco titular na última segunda-feira.\n\nEm entrevista coletiva, Guru afirmou que o nível técnico dos jogadores brasileiros o surpreendeu positivamente e que pretende trabalhar principalmente na disciplina mental e na leitura de jogo nos estágios médios da partida.', '2026-06-04 09:00:00', 1, 'https://images.unsplash.com/photo-1585298723682-7115561c51b7', 3),
(30, 'Minecraft Speedrun World Record é quebrado por brasileiro de 16 anos', 'O jovem Gustavo \"Guga\" Ferreira, de Porto Alegre, quebrou o recorde mundial de speedrun de Minecraft na categoria Any% Glitchless ao completar o jogo em 11 minutos e 42 segundos, superando o recorde anterior em quase 30 segundos.\n\nA corrida foi realizada ao vivo em sua stream pessoal, que contava com apenas 800 espectadores no momento do feito. O clipe viralizou nas redes sociais e seu canal cresceu de 2 mil para mais de 400 mil seguidores em menos de 48 horas.\n\nGuga atribuiu o recorde a meses de estudo de seeds, otimização de rotas e um setup de computador montado com peças que economizou durante dois anos. O jovem ainda está no ensino médio e pretende disputar o próximo Speedrunning Summer to Remember, maior evento de speedrun do mundo.', '2026-06-03 21:00:00', 1, 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64', 5);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `criado_em`, `username`, `foto`) VALUES
(1, 'Admin', 'admin@ggnews.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-06-24 20:10:59', NULL, NULL),
(2, 'Mauricio De Campos', 'mauriciodecamposhihi@gmail.com', '$2y$10$.MqbJV3FwAeBC6DdhEBqkuHba9KKbAR5CowSoCt.rVKkwj3jTdO7y', '2026-06-25 19:12:56', '', NULL);

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
-- AUTO_INCREMENT de tabela `noticias`
--
ALTER TABLE `noticias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restrições para tabelas despejadas
--

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
