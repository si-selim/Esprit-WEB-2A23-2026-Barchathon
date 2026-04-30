CREATE DATABASE IF NOT EXISTS barchathon;
USE barchathon;

CREATE TABLE IF NOT EXISTS `user` (
    `id_user`            INT            NOT NULL AUTO_INCREMENT,
    `nom_complet`        VARCHAR(100)   NOT NULL,
    `nom_user`           VARCHAR(50)    NOT NULL,
    `mot_de_passe`       VARCHAR(255)   NOT NULL,
    `age`                INT            DEFAULT NULL,
    `poids`              FLOAT          DEFAULT NULL,
    `taille`             INT            DEFAULT NULL,
    `email`              VARCHAR(100)   NOT NULL,
    `pays`               VARCHAR(50)    DEFAULT NULL,
    `ville`              VARCHAR(50)    DEFAULT NULL,
    `tel`                VARCHAR(20)    DEFAULT NULL,
    `occupation`         VARCHAR(100)   DEFAULT NULL,
    `profile_picture`    VARCHAR(255)   DEFAULT NULL,
    `role`               ENUM('admin', 'participant', 'organisateur') NOT NULL DEFAULT 'participant',
    `status`             ENUM('active','banned') NOT NULL DEFAULT 'active',
    `sexe`               ENUM('homme','femme','autre') DEFAULT NULL,
    `verified`           TINYINT(1)     NOT NULL DEFAULT 0,
    `verification_token` VARCHAR(64)    DEFAULT NULL,
    `face_descriptor`    TEXT           DEFAULT NULL,
    `google_id`          VARCHAR(64)    DEFAULT NULL,
    `reset_token`        VARCHAR(64)    DEFAULT NULL,
    `reset_token_expires` DATETIME      DEFAULT NULL,
    PRIMARY KEY (`id_user`),
    UNIQUE KEY `uk_nom_user` (`nom_user`),
    UNIQUE KEY `uk_google_id` (`google_id`)
);

ALTER TABLE `user` ADD COLUMN IF NOT EXISTS `status` ENUM('active','banned') NOT NULL DEFAULT 'active' AFTER `role`;
ALTER TABLE `user` ADD COLUMN IF NOT EXISTS `sexe` ENUM('homme','femme','autre') DEFAULT NULL AFTER `status`;
ALTER TABLE `user` ADD COLUMN IF NOT EXISTS `verified` TINYINT(1) NOT NULL DEFAULT 0 AFTER `sexe`;
ALTER TABLE `user` ADD COLUMN IF NOT EXISTS `verification_token` VARCHAR(64) DEFAULT NULL AFTER `verified`;
ALTER TABLE `user` ADD COLUMN IF NOT EXISTS `face_descriptor` TEXT DEFAULT NULL AFTER `verification_token`;
ALTER TABLE `user` ADD COLUMN IF NOT EXISTS `google_id` VARCHAR(64) DEFAULT NULL AFTER `face_descriptor`;
ALTER TABLE `user` ADD COLUMN IF NOT EXISTS `reset_token` VARCHAR(64) DEFAULT NULL AFTER `google_id`;
ALTER TABLE `user` ADD COLUMN IF NOT EXISTS `reset_token_expires` DATETIME DEFAULT NULL AFTER `reset_token`;
UPDATE `user` SET `verified` = 1 WHERE `verified` = 0 AND `id_user` IN (1,2,3);

INSERT INTO `user` (`nom_complet`, `nom_user`, `mot_de_passe`, `email`, `role`) VALUES
('Administrateur', 'admin', '$2y$10$/oucXT3oDuBMe2aeOKpeQOEqoWGK8LJkide3laCCgGSqww4PmTaeW', 'admin@barchathon.tn', 'admin'),
('Organisateur Demo', 'organisateur', '$2y$10$OTWqaCplEDuU4T6oE2.Bgu9SJnb6OdeHmGbzwtPXcGvmUKGg1DFPi', 'orga@barchathon.tn', 'organisateur'),
('Participant Demo', 'participant', '$2y$10$m/QCqoT/.uOK3cwuCOjC0uRuVPLDHqedWbYQg.0ESc1jgDYclhJR.', 'participant@barchathon.tn', 'participant');
