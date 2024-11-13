# StudentSwap

**StudentSwap** est une application web interactive qui permet aux étudiants de proposer des services qu'ils peuvent offrir et de demander des services dont ils ont besoin. Cette plateforme facilite les échanges de compétences entre étudiants, favorisant l'entraide et le partage.

## Table des matières

- [Fonctionnalités](#fonctionnalités)
- [Technologies utilisées](#technologies-utilisées)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Configuration](#configuration)
- [Utilisation](#utilisation)
- [Arborescence du projet](#arborescence-du-projet)
- [Contributeurs](#contributeurs)

## Fonctionnalités

- **Inscription et Connexion** : Les utilisateurs peuvent créer un compte, se connecter, et gérer leurs informations personnelles.
- **Ajout et gestion des services** : Les utilisateurs peuvent proposer des services, les modifier ou les supprimer.
- **Recherche avancée** : Les utilisateurs peuvent filtrer les services par catégorie, ville ou mots-clés.
- **Demande de services** : Les utilisateurs peuvent demander un service et échanger des points pour compléter la transaction.
- **Évaluations et avis** : Les utilisateurs peuvent laisser des avis et des notes sur les services reçus.
- **Gestion des demandes** : Les demandes peuvent être acceptées ou rejetées par le fournisseur du service.
- **Profil utilisateur** : Affichage du profil avec les services proposés, les avis reçus et les informations de l'utilisateur.

## Technologies utilisées

- **Front-end** :
  - HTML, CSS (`style.css`) pour l'interface utilisateur
  - JavaScript pour l'interactivité
- **Back-end** :
  - PHP pour la logique serveur
  - PDO pour la gestion de la base de données
- **Base de données** :
  - MySQL pour stocker les utilisateurs, services, catégories, villes et avis

## Prérequis

- PHP >= 7.4
- MySQL >= 5.7
- Serveur Apache ou Nginx
- Navigateur web moderne (Chrome, Firefox, etc.)

## Installation

1. Clonez le projet :

   ```bash
   git clone https://github.com/votre-utilisateur/StudentSwap.git
   cd StudentSwap
   ```

2. Configurez la base de données :

   - Créez une base de données nommée `studentswap`.
   - Importez le fichier `studentswap.sql` dans la base de données :

     ```bash
     mysql -u root -p studentswap < studentswap.sql
     ```

3. Configurez la connexion à la base de données dans `config/db.php` :

   ```php
   $host = 'localhost';
   $dbname = 'studentswap';
   $username = 'root';
   $password = '';
   ```

4. Démarrez le serveur PHP :

   ```bash
   php -S localhost:8000
   ```

5. Accédez à l'application à l'adresse :

   ```
   http://localhost:8000/views/index.php
   ```

## Configuration

- **Base de données** : Modifiez `config/db.php` pour configurer la connexion à votre base de données.
- **Images de profil** : Les images de profil sont stockées dans `uploads/profile_pictures/`.

## Arborescence du projet

```
StudentSwap/
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── img/
│   │   └── default-picture.png
│   └── js/
├── config/
│   └── db.php
├── controllers/
│   ├── add_service.php
│   ├── delete_service.php
│   ├── handle_request.php
│   └── request_service.php
├── includes/
│   ├── footer.php
│   └── header.php
├── public/
│   ├── load_more_reviews.php
│   └── logout.php
├── uploads/
│   └── profile_pictures/
│       └── profile_2.jpg
├── views/
│   ├── index.php
│   ├── leave_review.php
│   ├── login.php
│   ├── profile.php
│   ├── register.php
│   ├── reset_password.php
│   └── view_profile.php
└── README.md
```

## Utilisation

1. **Créer un compte** : Accédez à la page d'inscription (`register.php`).
2. **Se connecter** : Connectez-vous avec votre email et mot de passe (`login.php`).
3. **Ajouter un service** : Proposez un service via le formulaire d'ajout (`add_service.php`).
4. **Rechercher des services** : Utilisez la barre de recherche sur la page d'accueil (`index.php`).
5. **Demander un service** : Cliquez sur "Demander ce service" pour effectuer une transaction.
6. **Évaluer un service** : Laissez une évaluation après avoir utilisé un service (`leave_review.php`).
7. **Gérer votre profil** : Consultez votre profil et vos services proposés sur la page profil (`profile.php`).

## Contributeurs

- **Enzo Turpin**
- **Daryl Matro**

Contributions, issues et demandes de fonctionnalités sont les bienvenues !
