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
    `date_de_naissance`  DATE           DEFAULT NULL,
    `role`               ENUM('admin', 'participant', 'organisateur') NOT NULL DEFAULT 'participant',
    PRIMARY KEY (`id_user`),
    UNIQUE KEY `uk_nom_user` (`nom_user`)
);