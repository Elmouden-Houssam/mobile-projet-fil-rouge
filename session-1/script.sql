CREATE DATABASE plateforme_encheres;

USE plateforme_encheres;

CREATE TABLE utilisateur (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'utilisateur') DEFAULT 'utilisateur'
);

CREATE TABLE type_lot (
    id_type INT AUTO_INCREMENT PRIMARY KEY,
    nom_type VARCHAR(100) NOT NULL
);

CREATE TABLE article (
    id_article INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150) NOT NULL,
    description TEXT,
    prix_depart DECIMAL(10,2) NOT NULL,
    date_debut DATETIME NOT NULL,
    date_fin DATETIME NOT NULL,
    image VARCHAR(255),
    statut ENUM('en_attente', 'accepte', 'refuse') DEFAULT 'en_attente',
    id_utilisateur INT NOT NULL,
    id_type INT NOT NULL,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur),
    FOREIGN KEY (id_type) REFERENCES type_lot(id_type)
);

CREATE TABLE offre (
    id_offre INT AUTO_INCREMENT PRIMARY KEY,
    montant DECIMAL(10,2) NOT NULL,
    date_offre DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_utilisateur INT NOT NULL,
    id_article INT NOT NULL,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur),
    FOREIGN KEY (id_article) REFERENCES article(id_article)
);
