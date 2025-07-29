-- Création de la base de données
CREATE DATABASE IF NOT EXISTS gestion_arbitre CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestion_arbitre;

-- Table des ligues
CREATE TABLE ligues (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL DEFAULT 'CNP',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des arbitres
CREATE TABLE arbitres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    adresse TEXT,
    email VARCHAR(150) UNIQUE,
    statut ENUM('Actif', 'Inactif') DEFAULT 'Actif',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des équipes
CREATE TABLE equipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    ville VARCHAR(100) NOT NULL,
    statut ENUM('Actif', 'Inactif') DEFAULT 'Actif',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des matchs
CREATE TABLE matchs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ligue_id INT NOT NULL DEFAULT 1,
    nom_competition VARCHAR(200) NOT NULL,
    ville VARCHAR(100) NOT NULL,
    stade VARCHAR(200) NOT NULL,
    tour VARCHAR(100) NOT NULL,
    date_match DATE NOT NULL,
    heure_match TIME NOT NULL,
    equipe_a_id INT NOT NULL,
    equipe_b_id INT NOT NULL,
    arbitre_id INT,
    assistant_1_id INT,
    assistant_2_id INT,
    officiel_4_id INT,
    assesseur_id INT,
    publier ENUM('Oui', 'Non') DEFAULT 'Non',
    statut ENUM('Programmé', 'En cours', 'Terminé', 'Annulé') DEFAULT 'Programmé',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ligue_id) REFERENCES ligues(id),
    FOREIGN KEY (equipe_a_id) REFERENCES equipes(id),
    FOREIGN KEY (equipe_b_id) REFERENCES equipes(id),
    FOREIGN KEY (arbitre_id) REFERENCES arbitres(id),
    FOREIGN KEY (assistant_1_id) REFERENCES arbitres(id),
    FOREIGN KEY (assistant_2_id) REFERENCES arbitres(id),
    FOREIGN KEY (officiel_4_id) REFERENCES arbitres(id),
    FOREIGN KEY (assesseur_id) REFERENCES arbitres(id)
);

-- Table pour suivre l'historique des arbitrages par équipe
CREATE TABLE arbitrage_equipe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    arbitre_id INT NOT NULL,
    equipe_id INT NOT NULL,
    match_id INT NOT NULL,
    date_match DATE NOT NULL,
    FOREIGN KEY (arbitre_id) REFERENCES arbitres(id),
    FOREIGN KEY (equipe_id) REFERENCES equipes(id),
    FOREIGN KEY (match_id) REFERENCES matchs(id)
);

-- Insertion des données de base
INSERT INTO ligues (nom) VALUES ('CNP');

INSERT INTO arbitres (nom, prenom, adresse, email) VALUES
('Dupont', 'Jean', '123 Rue de la Paix, Dakar', 'jean.dupont@email.com'),
('Martin', 'Pierre', '456 Avenue Liberté, Thiès', 'pierre.martin@email.com'),
('Bernard', 'Michel', '789 Boulevard Central, Saint-Louis', 'michel.bernard@email.com'),
('Petit', 'André', '321 Rue du Commerce, Ziguinchor', 'andre.petit@email.com'),
('Robert', 'René', '654 Avenue des Sports, Dakar', 'rene.robert@email.com'),
('Dubois', 'Paul', '987 Rue de l\'Arbitrage, Thiès', 'paul.dubois@email.com'),
('Moreau', 'Jacques', '147 Boulevard des Arbitres, Saint-Louis', 'jacques.moreau@email.com');

INSERT INTO equipes (nom, ville) VALUES
('AS Dakar', 'Dakar'),
('US Thiès', 'Thiès'),
('FC Saint-Louis', 'Saint-Louis'),
('ASC Diaraf', 'Dakar'),
('Casa Sport', 'Ziguinchor'),
('ASC Linguère', 'Saint-Louis'),
('US Ouakam', 'Dakar'),
('ASC Niarry Tally', 'Dakar'); 