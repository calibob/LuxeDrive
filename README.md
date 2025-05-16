# Luxe Drive - Location de Voitures de Luxe

## À propos

Luxe Drive est une application web de location de voitures de luxe qui permet aux utilisateurs de parcourir un catalogue de véhicules haut de gamme, de réserver des voitures et d'effectuer des paiements simulés.

## Fonctionnalités

- **Catalogue de véhicules** : Parcourez notre sélection de voitures de luxe
- **Système de réservation** : Réservez un véhicule pour des dates spécifiques
- **Espace client** : Gérez vos réservations et votre profil
- **Simulation de paiement** : Processus de paiement sécurisé (simulation)
- **Administration** : Gestion complète des véhicules, réservations et utilisateurs

## Structure du projet

- **Pages principales** : index.php, catalog.php, login.php, register.php
- **Section client** : dashboard.php, profile.php, reservation.php et pages de paiement
- **Section admin** : dashboard.php, vehicles.php, users.php, reservations.php

## Installation

1. Clonez ce dépôt dans votre répertoire web (ex: xampp/htdocs/)
2. Importez le fichier de base de données `database/schema.sql` dans votre serveur MySQL
3. Copiez le fichier `.env.example` vers `.env` et configurez les paramètres
4. Installez les dépendances avec Composer : `composer install`
5. Accédez au site via `http://localhost/luxury-car-rental`

## Compte administrateur par défaut

- **Email** : admin@luxurycars.com
- **Mot de passe** : admin123

## Technologies utilisées

- PHP 7.4+
- MySQL
- HTML5/CSS3
- Bootstrap 5
- JavaScript

## Mode développement

Cette application utilise un système de paiement simulé pour faciliter les démonstrations et le développement. Aucune intégration de paiement réelle n'est nécessaire pour le fonctionnement.

## Licence

Ce projet est un exemple éducatif et n'est pas destiné à un usage commercial.
