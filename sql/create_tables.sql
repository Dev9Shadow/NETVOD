
SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

-- ---------------------------------------------------------
-- Suppression préalable
-- ---------------------------------------------------------
DROP TABLE IF EXISTS comment;
DROP TABLE IF EXISTS progress;
DROP TABLE IF EXISTS favorite;
DROP TABLE IF EXISTS episode;
DROP TABLE IF EXISTS serie;
DROP TABLE IF EXISTS user;

-- ---------------------------------------------------------
-- Table : user
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nom VARCHAR(100) DEFAULT '',
    prenom VARCHAR(100) DEFAULT '',
);

-- ---------------------------------------------------------
-- Table : serie
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS serie (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    genre VARCHAR(100) DEFAULT '',
    annee INT NOT NULL,
    image_url VARCHAR(255) DEFAULT NULL
);

-- ---------------------------------------------------------
-- Table : episode
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS episode (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_serie INT NOT NULL,
    numero INT NOT NULL,
    titre VARCHAR(255) NOT NULL,
    resume TEXT,
    duree INT NOT NULL DEFAULT 0,
    file VARCHAR(255) DEFAULT NULL,
    CONSTRAINT fk_episode_serie
        FOREIGN KEY (id_serie)
        REFERENCES serie(id)
        ON DELETE CASCADE
);

-- ---------------------------------------------------------
-- Table : favorite
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS favorite (
    id_user INT NOT NULL,
    id_serie INT NOT NULL,
    PRIMARY KEY (id_user, id_serie),
    CONSTRAINT fk_favorite_user
        FOREIGN KEY (id_user)
        REFERENCES user(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_favorite_serie
        FOREIGN KEY (id_serie)
        REFERENCES serie(id)
        ON DELETE CASCADE
);

-- ---------------------------------------------------------
-- Table : progress
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS progress (
    id_user INT NOT NULL,
    id_serie INT NOT NULL,
    last_episode_id INT NOT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_user, id_serie),
    CONSTRAINT fk_progress_user
        FOREIGN KEY (id_user)
        REFERENCES user(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_progress_serie
        FOREIGN KEY (id_serie)
        REFERENCES serie(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_progress_episode
        FOREIGN KEY (last_episode_id)
        REFERENCES episode(id)
        ON DELETE CASCADE
);

-- ---------------------------------------------------------
-- Table : comment
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS comment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_serie INT NOT NULL,
    note TINYINT NOT NULL,
    contenu TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_comment_user
        FOREIGN KEY (id_user)
        REFERENCES user(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_comment_serie
        FOREIGN KEY (id_serie)
        REFERENCES serie(id)
        ON DELETE CASCADE
);

SET foreign_key_checks = 1;
