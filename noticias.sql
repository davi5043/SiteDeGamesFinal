-- phpMyAdmin SQL Dump (CORRIGIDO)
-- Banco: portal_games

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `noticias` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `noticia` text NOT NULL,
  `data` datetime DEFAULT current_timestamp(),
  `autor` int(11) NOT NULL,
  `imagem` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DADOS LIMPOS E CORRIGIDOS
-- =====================================================

INSERT INTO `noticias` (`id`, `titulo`, `noticia`, `data`, `autor`, `imagem`) VALUES

(1, 'Banco de dados funcionando!',
'Banco de dados funcionando corretamente!',
'2026-06-26 18:31:02', 1,
'https://images.unsplash.com/photo-1542751371-adc38448a05e'),

(2, 'CS2 Recebe Atualização Massiva',
'Novo mapa competitivo "Thera" chega ao CS2 com mudanças importantes.',
'2026-06-19 14:30:00', 1, NULL),

(3, 'LOUD vence campeonato de Valorant',
'A equipe brasileira dominou o cenário internacional.',
'2026-06-18 20:00:00', 1, NULL),

(4, 'Worlds de LoL 2026 anunciado',
'Evento será na Coreia do Sul com recorde esperado de audiência.',
'2026-06-20 10:00:00', 1, NULL),

(5, 'Brasil domina Fortnite',
'Jogadores brasileiros lideram ranking mundial.',
'2026-06-16 16:45:00', 1, NULL);

-- =====================================================
-- ÍNDICES
-- =====================================================

ALTER TABLE `noticias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `autor` (`autor`);

-- =====================================================
-- AUTO_INCREMENT
-- =====================================================

ALTER TABLE `noticias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

-- =====================================================
-- FOREIGN KEY
-- =====================================================

ALTER TABLE `noticias`
  ADD CONSTRAINT `noticias_ibfk_1`
  FOREIGN KEY (`autor`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

COMMIT;