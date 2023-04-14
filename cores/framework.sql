-- --------------------------------------------------------
-- Hôte :                        localhost
-- Version du serveur:           8.0.18 - MySQL Community Server - GPL
-- SE du serveur:                Win64
-- HeidiSQL Version:             10.2.0.5599
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Listage de la structure de la table framework_sidunis. config_role
CREATE TABLE IF NOT EXISTS `config_role` (
  `code` varchar(32) NOT NULL,
  `libelle` varchar(50) DEFAULT NULL,
  `defaut` enum('Y','N') DEFAULT NULL,
  `actif` enum('Y','N') DEFAULT NULL,
  PRIMARY KEY (`code`),
  KEY `actif` (`actif`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Listage des données de la table framework_sidunis.config_role : 6 rows
/*!40000 ALTER TABLE `config_role` DISABLE KEYS */;
INSERT INTO `config_role` (`code`, `libelle`, `defaut`, `actif`) VALUES
	('ADM', 'ADMIN', NULL, 'Y'),
	('DEV', 'DEV', NULL, 'Y'),
	('VIS', 'VISITEUR', 'Y', 'Y'),
	('CTRL', 'CONTROLEUR', NULL, 'Y'),
	('GESTC', 'GESTIONNAIRE CREDIT', NULL, 'Y'),
	('ADMC', 'ADMIN CREDIT', NULL, NULL);
/*!40000 ALTER TABLE `config_role` ENABLE KEYS */;

-- Listage de la structure de la table framework_sidunis. config_user_role
CREATE TABLE IF NOT EXISTS `config_user_role` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_user` bigint(20) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `lastupdate_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `lastupdate_by` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `role` (`role`),
  KEY `id_user` (`id_user`)
) ENGINE=MyISAM AUTO_INCREMENT=75 DEFAULT CHARSET=utf8;

-- Listage des données de la table framework_sidunis.config_user_role : 36 rows
/*!40000 ALTER TABLE `config_user_role` DISABLE KEYS */;
INSERT INTO `config_user_role` (`id`, `id_user`, `role`, `lastupdate_date`, `lastupdate_by`) VALUES
	(66, 1, 'GESTC', '2021-06-28 10:43:23', 7),
	(2, 2, 'CTRL', '2019-03-01 14:14:57', 1),
	(3, 3, 'CTRL', '2019-03-01 14:20:31', 1),
	(60, 4, 'CTRL', '2021-02-19 15:35:06', 7),
	(26, 5, 'CTRL', '2020-02-13 16:07:01', 1),
	(6, 6, 'ADM', '2019-06-25 11:30:46', 1),
	(55, 7, 'ADMC', '2020-12-18 13:53:44', 7),
	(8, 8, 'ADM', '2020-01-21 10:17:58', 1),
	(65, 1, 'DEV', '2021-06-28 10:43:23', 7),
	(59, 4, 'DEV', '2021-02-19 15:35:06', 7),
	(13, 8, 'GESTC', '2020-02-12 11:46:37', 8),
	(14, 8, 'ADMC', '2020-02-12 11:46:51', 8),
	(25, 5, 'ADM', '2020-02-13 16:07:01', 1),
	(23, 9, 'GESTC', '2020-02-13 16:06:46', 1),
	(22, 9, 'CTRL', '2020-02-13 16:06:46', 1),
	(24, 9, 'ADMC', '2020-02-13 16:06:46', 1),
	(27, 5, 'GESTC', '2020-02-13 16:07:01', 1),
	(28, 5, 'ADMC', '2020-02-13 16:07:01', 1),
	(64, 1, 'ADM', '2021-06-28 10:43:23', 7),
	(37, 10, 'DEV', '2020-09-08 11:53:13', 8),
	(54, 7, 'GESTC', '2020-12-18 13:53:44', 7),
	(53, 7, 'CTRL', '2020-12-18 13:53:44', 7),
	(52, 7, 'VIS', '2020-12-18 13:53:44', 7),
	(51, 7, 'DEV', '2020-12-18 13:53:44', 7),
	(50, 7, 'ADM', '2020-12-18 13:53:44', 7),
	(61, 4, 'ADMC', '2021-02-19 15:35:06', 7),
	(62, 12, 'DEV', '2021-02-19 15:35:27', 7),
	(63, 13, 'ADM', '2021-06-28 10:25:22', 7),
	(67, 1, 'ADMC', '2021-06-28 10:43:23', 7),
	(68, 14, 'ADM', '2021-06-29 12:57:21', 7),
	(69, 15, 'ADM', '2021-11-19 10:45:37', 7),
	(70, 15, 'DEV', '2021-11-19 10:45:37', 7),
	(71, 15, 'VIS', '2021-11-19 10:45:37', 7),
	(72, 15, 'CTRL', '2021-11-19 10:45:37', 7),
	(73, 15, 'GESTC', '2021-11-19 10:45:37', 7),
	(74, 15, 'ADMC', '2021-11-19 10:45:37', 7);
/*!40000 ALTER TABLE `config_user_role` ENABLE KEYS */;

-- Listage de la structure de la table framework_sidunis. log
CREATE TABLE IF NOT EXISTS `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `title` varchar(64) NOT NULL,
  `txt` longtext NOT NULL,
  `type` varchar(32) DEFAULT NULL,
  `sess` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `title` (`title`),
  KEY `date` (`date`),
  KEY `type` (`type`),
  KEY `sess` (`sess`)
) ENGINE=MyISAM AUTO_INCREMENT=56 DEFAULT CHARSET=utf8;

-- Listage des données de la table framework_sidunis.log : 5 rows
/*!40000 ALTER TABLE `log` DISABLE KEYS */;
/*!40000 ALTER TABLE `log` ENABLE KEYS */;

-- Listage de la structure de la table framework_sidunis. sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_user` bigint(20) NOT NULL,
  `token` varchar(128) NOT NULL,
  `expiration` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Listage des données de la table framework_sidunis.sessions : 1 rows
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;

-- Listage de la structure de la table framework_sidunis. user
CREATE TABLE IF NOT EXISTS `user` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `actif` enum('Y','N') NOT NULL DEFAULT 'Y',
  `nom` varchar(50) DEFAULT NULL,
  `prenom` varchar(50) DEFAULT NULL,
  `mail` varchar(256) DEFAULT NULL,
  `pass` varchar(256) DEFAULT NULL,
  `lastupdate_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `lastupdate_by` bigint(20) DEFAULT NULL,
  `date_inscription` datetime DEFAULT NULL,
  `lastcnx` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mail` (`mail`(255)),
  KEY `nom` (`nom`),
  KEY `prenom` (`prenom`),
  KEY `actif` (`actif`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COMMENT='utilisateurs du site';

-- Listage des données de la table framework_sidunis.user : 5 rows
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` (`id`, `actif`, `nom`, `prenom`, `mail`, `pass`, `lastupdate_date`, `lastupdate_by`, `date_inscription`, `lastcnx`) VALUES
	(1, 'Y', 'REYNET', 'Jordane', 'jreynet@groupefbo.com', '$2y$10$OOKdYGhlOMfyrwjc/CvKdu07Cd.LkFnUv6BzwfFrsrD2ZOhMBRYkO', '2021-11-19 10:53:20', 1, '2018-09-18 20:57:05', '2020-09-10 11:44:18'),
	(8, 'Y', 'SAUDRAIS', 'Yoan', 'ysaudrais@groupefbo.com', '$2y$10$s136KCsaxkGdrFqSQpvCkuYKuR0It31aWpAggDbXsjUKiyYFl7itm', '2021-06-28 10:27:33', 1, '2018-09-18 20:57:05', '2020-09-10 10:43:22'),
	(4, 'Y', 'GIRARD', 'Harold', 'harold@groupefbo.com', '$2y$10$2VzBT7bbIXIdHPeBCBEf8eAVT7XsPCY7viqv8dIZIiKH2hCuI1oIS', '2021-06-29 13:44:14', NULL, '2019-03-01 13:59:31', '2020-09-07 10:05:40'),
	(7, 'Y', 'NADEAU', 'Mathieu', 'mnadeau@groupefbo.com', '$2y$10$/yo1.y4pOHfzZaFLsV0queGYugfDwS./Jl8QYkL9sERTfjaJ6Q/tu', '2022-05-20 17:10:54', 1, '2018-09-18 20:57:05', '2022-05-20 17:10:54'),
	(15, 'Y', 'KENNE', 'Tuan', 'tkenne@groupefbo.com', '$2y$10$6HKfAwQU5KoJDzdgQbC7AOhXkdA4GJbeWVkumNVGZUlSjj9xpompe', '2021-11-19 10:45:37', 7, '2021-11-19 10:45:37', NULL);
