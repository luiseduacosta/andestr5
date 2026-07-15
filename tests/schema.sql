-- SQLite compatible schema for tests

CREATE TABLE `apoios` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `nomedoevento` varchar(15) DEFAULT NULL,
  `evento_id` int(11) NOT NULL,
  `caderno` varchar(15) NOT NULL,
  `numero_texto` int(11) NOT NULL,
  `tema` varchar(10) NOT NULL,
  `gt` varchar(50) DEFAULT NULL,
  `gt_id` int(11) NOT NULL,
  `titulo` varchar(256) DEFAULT NULL,
  `autor` text DEFAULT NULL,
  `texto` text DEFAULT NULL
);

CREATE TABLE `eventos` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `ordem` int(11) NOT NULL,
  `nome` varchar(25) NOT NULL,
  `data` varchar(50) NOT NULL,
  `local` varchar(25) NOT NULL,
  `ativo` boolean NOT NULL DEFAULT 0
);

CREATE TABLE `gts` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `sigla` varchar(20) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `outras` varchar(50) NOT NULL
);

CREATE TABLE `items` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `apoio_id` int(11) NOT NULL,
  `tr` int(3) NOT NULL,
  `item` varchar(11) NOT NULL,
  `texto` text NOT NULL,
  `user_id` int(11) DEFAULT NULL
);

CREATE TABLE `phinxlog` (
  `version` bigint(20) NOT NULL PRIMARY KEY,
  `migration_name` varchar(100) DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `breakpoint` boolean NOT NULL DEFAULT 0
);

CREATE TABLE `users` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
);

CREATE TABLE `votacoes` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `user_id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `grupo` int(11) NOT NULL,
  `tr` int(3) NOT NULL,
  `item_id` int(11) NOT NULL,
  `item` varchar(10) NOT NULL,
  `votacao` varchar(10) NOT NULL,
  `resultado` varchar(12) DEFAULT NULL,
  `data` datetime DEFAULT CURRENT_TIMESTAMP,
  `item_modificada` text DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `destaque_minoria` boolean NOT NULL DEFAULT 0
);
