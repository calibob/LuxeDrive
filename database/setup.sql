-- Création de la base de données avec le bon encodage
CREATE DATABASE IF NOT EXISTS luxury_car_rental
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- Création de l'utilisateur et attribution des privilèges
CREATE USER IF NOT EXISTS 'luxury_user'@'localhost' 
    IDENTIFIED BY 'Secure_P@ssw0rd';

-- Attribution des privilèges minimaux nécessaires
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP
    ON luxury_car_rental.*
    TO 'luxury_user'@'localhost';

-- Forcer le rechargement des privilèges
FLUSH PRIVILEGES;

-- Utiliser la base de données
USE luxury_car_rental;

-- Importer le schéma principal (à exécuter séparément)
-- SOURCE schema.sql;
