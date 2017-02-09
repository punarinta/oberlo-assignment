SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE DATABASE `local-firm`;
USE `local-firm`;

CREATE TABLE `message` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sender` varchar(256) NOT NULL,
  `subject` varchar(256) DEFAULT NULL,
  `body` text NOT NULL,
  `ts` int(10) UNSIGNED NOT NULL,
  `flags` tinyint(3) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `message` ADD PRIMARY KEY (`id`);
ALTER TABLE `message` MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
