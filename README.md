# La Gazette de L-INFO

Yusuf KORKMAZ

## Présentation

La Gazette de L-INFO est un site d'actualités parodiques consacré à la vie de la Licence Informatique.

L'application propose :

* consultation des articles
* recherche multicritère
* authentification des utilisateurs
* inscription
* gestion du profil utilisateur
* publication d'articles (rédacteurs)
* édition et suppression d'articles
* système de commentaires
* upload d'images
* gestion des droits utilisateurs

L'ensemble des données est stocké dans une base de données MySQL et les pages sont générées dynamiquement en PHP.

## Fonctionnalités

### Partie publique

* Accueil dynamique
* Liste des actualités
* Recherche d'articles
* Consultation d'un article
* Présentation de la rédaction

### Partie utilisateur

* Connexion / Déconnexion
* Inscription
* Modification du compte
* Ajout de commentaires
* Suppression de ses commentaires

### Partie rédacteur

* Création d'articles
* Modification d'articles
* Suppression d'articles
* Gestion des commentaires de ses articles
* Upload des images d'illustration

## Structure du projet

```text
gazette/
│
├── index.php
├── php/
├── styles/
├── images/
├── upload/
└── html/
```

## Gestion des droits

Trois niveaux d'accès sont disponibles :

* Visiteur
* Utilisateur authentifié
* Rédacteur

Chaque rôle possède des fonctionnalités spécifiques.

## Illustrations

Les articles peuvent être accompagnés d'une image au format JPG.

Une image par défaut est utilisée lorsqu'aucune illustration n'est disponible.

## Installation

1. Cloner le dépôt

```bash
git clone https://github.com/votre-utilisateur/gazette.git
```

2. Importer la base de données.

3. Configurer les paramètres de connexion à MySQL.

4. Lancer le serveur PHP.

