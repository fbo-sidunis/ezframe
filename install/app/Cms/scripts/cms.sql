

DROP TABLE IF EXISTS `cms_blocks`;
CREATE TABLE `cms_blocks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nb_colonnes` int DEFAULT NULL,
  `content` mediumtext COLLATE utf8mb4_general_ci NOT NULL,
  `created_by` int DEFAULT NULL,
  `lastupdate_by` int DEFAULT NULL,
  `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `lastupdate_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_layout` (`nb_colonnes`),
  CONSTRAINT `cms_blocks_ibfk_1` FOREIGN KEY (`nb_colonnes`) REFERENCES `cms_layouts` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `cms_layouts`;
CREATE TABLE `cms_layouts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `libelle` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `template_path` varchar(2048) COLLATE utf8mb4_general_ci DEFAULT '',
  `created_by` int DEFAULT NULL,
  `lastupdate_by` int DEFAULT NULL,
  `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `lastupdate_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='types de layout pour les blocks';


DROP TABLE IF EXISTS `cms_menu_items`;
CREATE TABLE `cms_menu_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_parent` int DEFAULT NULL,
  `libelle` varchar(128) COLLATE utf8mb4_general_ci NOT NULL,
  `extend_class` varchar(256) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'class CSS à ajouter à cet item du menu',
  `glyph` varchar(2048) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `link` varchar(2048) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alias` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_page` bigint DEFAULT NULL,
  `title` varchar(1024) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'pour balise title dans le head',
  `description` mediumtext COLLATE utf8mb4_general_ci COMMENT 'meta description pour seo',
  `re_ecriture_url` varchar(2048) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `public` enum('Y','N') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Y' COMMENT 'Page accessible sans etre logue',
  `active` enum('Y','N') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Y',
  `ordre` int NOT NULL DEFAULT '99' COMMENT 'ordre d''affichage',
  `niveau` int NOT NULL DEFAULT '0',
  `created_by` int DEFAULT NULL,
  `lastupdate_by` int DEFAULT NULL,
  `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `lastupdate_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_parent` (`id_parent`),
  KEY `id_page` (`id_page`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `cms_menu_roles`;
CREATE TABLE `cms_menu_roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_menu_item` int DEFAULT NULL,
  `code_role` varchar(64) COLLATE utf8mb4_general_ci NOT NULL,
  `c` enum('Y','N') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `r` enum('Y','N') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `u` enum('Y','N') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `d` enum('Y','N') COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `code_role` (`code_role`),
  KEY `id_menu_item` (`id_menu_item`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='gestion des accès en fonction des rôles';


DROP TABLE IF EXISTS `cms_pages`;
CREATE TABLE `cms_pages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `libelle` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `lastupdate_by` int DEFAULT NULL,
  `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `lastupdate_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `type` enum('ARTICLE','BLOG','AUTRE') COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `cms_pages_blocks`;
CREATE TABLE `cms_pages_blocks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_page` int DEFAULT NULL,
  `id_block` int DEFAULT NULL,
  `position` int DEFAULT '1' COMMENT 'ordre des blocks dans la page',
  `created_by` int DEFAULT NULL,
  `lastupdate_by` int DEFAULT NULL,
  `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `lastupdate_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_page` (`id_page`),
  KEY `id_block` (`id_block`),
  CONSTRAINT `cms_pages_blocks_ibfk_1` FOREIGN KEY (`id_page`) REFERENCES `cms_pages` (`id`),
  CONSTRAINT `cms_pages_blocks_ibfk_2` FOREIGN KEY (`id_block`) REFERENCES `cms_blocks` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `cms_slider`;
CREATE TABLE `cms_slider` (
  `id` int NOT NULL AUTO_INCREMENT,
  `libelle` varchar(256) COLLATE utf8mb4_general_ci DEFAULT ' ',
  `created_by` int DEFAULT NULL,
  `lastupdate_by` int DEFAULT NULL,
  `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `lastupdate_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `cms_slider_items`;
CREATE TABLE `cms_slider_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_slider` int DEFAULT NULL,
  `image` varchar(2048) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'chemin vers l''image ',
  `position` int DEFAULT '99' COMMENT 'ordre d''affichage dans le slider',
  `created_by` int DEFAULT NULL,
  `lastupdate_by` int DEFAULT NULL,
  `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `lastupdate_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_slider` (`id_slider`),
  CONSTRAINT `cms_slider_items_ibfk_1` FOREIGN KEY (`id_slider`) REFERENCES `cms_slider` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;