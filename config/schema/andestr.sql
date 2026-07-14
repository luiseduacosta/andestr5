-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 14/07/2026 às 06:04
-- Versão do servidor: 10.11.14-MariaDB-0ubuntu0.24.04.1
-- Versão do PHP: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `andestr`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `apoios`
--

CREATE TABLE IF NOT EXISTS `apoios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nomedoevento` varchar(15) DEFAULT NULL,
  `evento_id` int(11) NOT NULL,
  `caderno` enum('Principal','Anexo') NOT NULL,
  `numero_texto` int(11) NOT NULL,
  `tema` enum('I','II','III','IV') NOT NULL,
  `gt` varchar(50) DEFAULT NULL,
  `gt_id` int(11) NOT NULL,
  `titulo` varchar(256) DEFAULT NULL,
  `autor` text DEFAULT NULL,
  `texto` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cake_migrations`
--

CREATE TABLE IF NOT EXISTS `cake_migrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `version` bigint(20) NOT NULL,
  `migration_name` varchar(100) DEFAULT NULL,
  `plugin` varchar(100) DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `breakpoint` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `version_plugin_unique` (`version`,`plugin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cake_seeds`
--

CREATE TABLE IF NOT EXISTS `cake_seeds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin` varchar(100) DEFAULT NULL,
  `seed_name` varchar(100) NOT NULL,
  `executed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `eventos`
--

CREATE TABLE IF NOT EXISTS `eventos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ordem` int(11) NOT NULL,
  `nome` varchar(25) NOT NULL,
  `data` varchar(50) NOT NULL,
  `local` varchar(25) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `gts`
--

CREATE TABLE IF NOT EXISTS `gts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sigla` varchar(20) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `outras` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `items`
--

CREATE TABLE IF NOT EXISTS `items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `apoio_id` int(11) NOT NULL,
  `tr` int(3) NOT NULL,
  `item` varchar(11) NOT NULL,
  `texto` text NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `BY_USER_ID` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `votacoes`
--

CREATE TABLE IF NOT EXISTS `votacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `grupo` int(11) NOT NULL,
  `tr` int(3) NOT NULL,
  `item_id` int(11) NOT NULL,
  `item` varchar(10) NOT NULL,
  `votacao` varchar(10) NOT NULL,
  `resultado` varchar(12) DEFAULT NULL,
  `data` datetime NOT NULL DEFAULT current_timestamp(),
  `item_modificada` text DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `destaque_minoria` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
