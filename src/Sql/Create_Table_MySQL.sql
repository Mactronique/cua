##
## This file is part of Composer Update Analyser package.
##
## @author Jean-Baptiste Nahan <814683+macintoshplus@users.noreply.github.com>
## @copyright 2016-2019 - Jean-Baptiste Nahan
## @license MIT
##

## FOR MySQL

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `dependencies` (
  `id` int(11) NOT NULL,
  `project` varchar(50) NOT NULL,
  `library` varchar(250) NOT NULL,
  `version` varchar(250) NOT NULL,
  `state` varchar(20) NOT NULL,
  `to_library` varchar(250) DEFAULT NULL,
  `to_version` varchar(250) DEFAULT NULL,
  `deprecated` tinyint(1) DEFAULT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `dependencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_proj_lib` (`project`,`library`(191)) USING BTREE,
  ADD KEY `idx_plv` (`project`,`library`(191),`version`(191));

ALTER TABLE `dependencies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


CREATE TABLE IF NOT EXISTS `security` (
  `id` int(11) NOT NULL,
  `project` varchar(50) NOT NULL,
  `library` varchar(255) NOT NULL,
  `version` varchar(255) NOT NULL,
  `state` varchar(20) NOT NULL,
  `details` text NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `security`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `security`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


CREATE TABLE IF NOT EXISTS `projects` (
  `code` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `path` varchar(255) NOT NULL,
  `lock_path` varchar(255) NOT NULL,
  `php_path` varchar(255) NOT NULL,
  `private_dependencies` text DEFAULT NULL,
  `private_dependencies_strategy` varchar(10) DEFAULT 'remove',
  `check_dependencies` tinyint(1) DEFAULT NULL,
  `check_security` tinyint(1) DEFAULT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `projects`
  ADD PRIMARY KEY (`code`);
