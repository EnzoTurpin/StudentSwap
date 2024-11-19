# StudentSwap

**StudentSwap** est une application web interactive conçue pour faciliter les échanges de services entre étudiants. Cette plateforme permet aux utilisateurs de proposer des services qu'ils peuvent offrir, ainsi que de demander des services dont ils ont besoin, tout en utilisant un système de points pour encourager les échanges.

## 🌟 Fonctionnalités

1. **Proposition de Services** :

   - Les étudiants peuvent créer des annonces pour proposer des services variés :
     - Aide aux devoirs ou aux cours.
     - Prêt de matériel (livres, ordinateurs, etc.).
     - Cours particuliers.
     - Organisation d'études de groupe.

2. **Demande de Services** :

   - Les utilisateurs peuvent rechercher et demander des services proposés par d'autres étudiants.
   - Les annonces peuvent être filtrées par catégorie, localisation et type de service.

3. **Système d'Échange Basé sur des Points** :

   - Un système de points est utilisé pour faciliter les échanges :
     - Offrir un service rapporte des points.
     - Demander un service coûte des points.
     - Les utilisateurs peuvent accumuler des points et les utiliser pour accéder à d'autres services.

4. **Interface Utilisateur Conviviale** :

   - Les utilisateurs peuvent :
     - Créer un compte et gérer leur profil.
     - Publier et consulter des annonces.
     - Évaluer les services reçus et donner des retours d'expérience.

5. **Sécurité et Authentification** :

   - Authentification via un système de connexion sécurisé.
   - Accès restreint aux fonctionnalités de l'application pour les utilisateurs connectés uniquement.
   - Protection des données des utilisateurs via des requêtes préparées et des mots de passe hachés.

6. **Gestion des Données** :
   - Utilisation d'une base de données pour stocker les informations sur les utilisateurs, les services, les transactions et les points.

## 🛠️ Installation

Pour installer et exécuter ce projet en local :

1. **Prérequis** :

   - Serveur Apache (via XAMPP, WAMP ou MAMP).
   - PHP 7.4 ou supérieur.
   - Base de données MySQL.

2. **Cloner le projet** :

   ```bash
   git clone https://github.com/EnzoTurpin/StudentSwap
   ```

3. **Configuration de la base de données** :

   - Créez une nouvelle base de données vierge excactement `studentswap`
   - Importez le fichier `studentswap.sql` dans votre base de données MySQL.
   - Modifiez le fichier `config/db.php` avec vos paramètres de connexion :
     ```php
     $host = 'localhost';
     $dbname = 'studentswap';
     $username = 'root';
     $password = '';
     ```

4. **Démarrer le serveur** :
   - Placez le projet dans le dossier `htdocs` (si vous utilisez XAMPP).
   - Démarrez Apache et MySQL depuis votre panneau de contrôle (XAMPP/WAMP/MAMP).
   - Accédez à l'application via [http://localhost/studentswap/views/index.php](http://localhost/studentswap/views/index.php).

## 📂 Structure du Projet

```plaintext
studentswap/
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── img/
│   │   └── default-picture.png
│   ├── js/
│   │   ├── load_more_reviews.php
│   │   └── validation.js
├── config/
│   ├── db.php
│   └── studentswap.sql
├── controllers/
│   ├── add_service.php
│   ├── cancel_request.php
│   ├── delete_service.php
│   ├── handle_request.php
│   └── request_service.php
├── includes/
│   ├── header.php
│   └── footer.php
├── public/
│   ├── load_more_reviews.php
│   └── logout.php
├── uploads/
│   └── profile_pictures/
├── views/
│   ├── index.php
│   ├── login.php
│   ├── register.php
│   ├── profile.php
│   ├── view_profile.php
│   ├── leave_review.php
│   └── reset_password.php
├── README.md
```

## 💾 Base de Données

Le projet utilise une base de données MySQL nommée `studentswap`. Voici les principales tables :

- **users** : stocke les informations des utilisateurs (id, nom d'utilisateur, email, mot de passe, points).
- **services** : stocke les services proposés par les utilisateurs (id, titre, description, catégorie, localisation, coût en points).
- **service_requests** : stocke les demandes de services (id, service_id, requester_id, statut).
- **reviews** : stocke les avis laissés par les utilisateurs sur les services reçus (id, service_id, user_id, note, commentaire).

## ⚙️ Configuration Initiale

Lors du premier démarrage de l’application, un compte administrateur par défaut est créé pour simplifier la gestion et l’accès au système. Voici les détails de ce compte :

- **Nom d'utilisateur** : `admin`
- **Mot de passe** : `admin`
- **Points** : Infini

## 📄 Exemples de Code

### Exemple de Connexion à la Base de Données (`db.php`)

```php
<?php
$host = 'localhost';
$dbname = 'studentswap';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
```

## 🔒 Sécurité

- Utilisation des requêtes préparées pour éviter les injections SQL.
- Les mots de passe des utilisateurs sont stockés sous forme hachée (via `password_hash()`).
- Les sessions sont utilisées pour gérer l'authentification et la sécurité des utilisateurs.

## 📈 Améliorations Futures

- Ajouter des notifications pour les demandes de services acceptées ou rejetées.
- Implémenter un système de messagerie entre utilisateurs.
- Ajouter des filtres de recherche avancés (par date, coût en points, etc.).
- Optimiser l'interface pour les appareils mobiles.

## 🤝 Contribution

Les contributions sont les bienvenues ! Si vous souhaitez contribuer, veuillez créer une _issue_ ou soumettre une _pull request_.

## 📝 Licence

Ce projet est sous licence MIT. Consultez le fichier `LICENSE` pour plus de détails.

## 📧 Contact

Pour toute question ou suggestion, veuillez contacter l'équipe de développement à l'adresse : support@studentswap.com.

## 📥 Importer la Base de Données

Pour configurer la base de données `studentswap`, suivez ces étapes :

1. Ouvrez **phpMyAdmin** ou utilisez la ligne de commande MySQL.
2. Créez une nouvelle base de données nommée `studentswap` :

   ```sql
   CREATE DATABASE studentswap;
   ```

3. Importez le fichier `studentswap.sql` situé dans le dossier config de ce projet :

   - Avec **phpMyAdmin** :
     - Sélectionnez la base de données `studentswap`.
     - Cliquez sur **Importer**, puis choisissez le fichier `studentswap.sql`.
     - Cliquez sur **Exécuter** pour importer la structure et les données.
   - Avec la **ligne de commande MySQL** :
     ```bash
     mysql -u root -p studentswap < studentswap.sql
     ```

4. Mettez à jour vos informations de connexion à la base de données dans le fichier `config/db.php` :

   ```php
   $host = 'localhost';
   $dbname = 'studentswap';
   $username = 'root';
   $password = '';
   ```

5. Votre base de données est maintenant prête à être utilisée avec l'application StudentSwap.
