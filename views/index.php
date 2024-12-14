<?php
session_start();
include '../config/db.php';

try {
    // Initialiser les variables utilisateur
    $user_id = $_SESSION['user_id'] ?? null;
    $profile_picture = 'default-picture.png';
    $user = null;

    // Si l'utilisateur est connecté, récupérer ses informations depuis la base de données
    if ($user_id) {
        $sql = "SELECT username, email, COALESCE(points, 10) AS points, profile_picture, is_admin FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Définir la photo de profil par défaut si elle n'est pas définie
        $profile_picture = $user['profile_picture'] ?? '../assets/img/default-picture.png';

        // Si les points ne sont pas présents, les initialiser à 10
        if (!array_key_exists('points', $user)) {
            $user['points'] = 10;
        }
    }

    // Vérifier si l'utilisateur est administrateur
    if (!isset($user['is_admin']) || $user['is_admin'] != 1) {
        $user_points = htmlspecialchars($user['points'] ?? 10);
    }

    // Récupérer les catégories et les villes
    $sql = "SELECT * FROM categories";
    $stmt = $conn->query($sql);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT * FROM cities ORDER BY name ASC";
    $stmt = $conn->query($sql);
    $cities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Initialiser les critères de recherche
    $raw_search_query = $_GET['search_query'] ?? ''; // Texte brut fourni par l'utilisateur
    $search_query = '%' . $raw_search_query . '%'; // Utilisé pour la requête SQL
    $selected_category = $_GET['category_id'] ?? '';
    $selected_city = $_GET['city_id'] ?? '';

    // Construire la requête de recherche pour les services disponibles
    $sql = "SELECT services.*, users.username, categories.name AS category_name 
            FROM services 
            JOIN users ON services.user_id = users.id
            JOIN categories ON services.category_id = categories.id
            WHERE services.status = 'available'";

    // Ajouter des filtres de recherche selon les critères fournis
    if (!empty($selected_category)) {
        $sql .= " AND services.category_id = :category_id";
    }
    if (!empty($selected_city)) {
        $sql .= " AND services.location = :city_id";
    }
    if (!empty($raw_search_query)) {
        $sql .= " AND (services.title LIKE :search_query OR services.description LIKE :search_query)";
    }

    $sql .= " ORDER BY services.created_at DESC";
    $stmt = $conn->prepare($sql);

    // Lier les paramètres de la requête
    if (!empty($selected_category)) {
        $stmt->bindParam(':category_id', $selected_category, PDO::PARAM_INT);
    }
    if (!empty($selected_city)) {
        $stmt->bindParam(':city_id', $selected_city, PDO::PARAM_STR);
    }
    if (!empty($raw_search_query)) {
        $stmt->bindParam(':search_query', $search_query, PDO::PARAM_STR);
    }

    // Exécuter la requête et récupérer les services
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Afficher une erreur en cas d'échec
    die("Erreur lors de la récupération des données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentSwap - Accueil</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="wrapper">
        <!-- Inclusion de l'en-tête -->
        <?php include '../includes/header.php'; ?>

        <!-- Affichage des messages de succès -->
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success-message">
            <p><?= htmlspecialchars($_SESSION['success_message']) ?></p>
        </div>
        <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <!-- Affichage des messages d'erreur -->
        <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error-message">
            <p><?= htmlspecialchars($_SESSION['error_message']) ?></p>
        </div>
        <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Affichage des messages d'erreur -->
        <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error-message">
            <p><?= htmlspecialchars($_SESSION['error_message']) ?></p>
        </div>
        <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <main class="main-content">
            <!-- Section de bienvenue -->
            <section class="welcome">
                <?php if ($user): ?>
                <h2>Bienvenue, <?= htmlspecialchars($user['username']) ?>!</h2>
                <p>Solde de points :
                    <?php if (isset($user['is_admin']) && $user['is_admin'] == 1): ?>
                    <img src="../assets/svg/infinite.svg" alt="Points illimités" class="infinite-icon">
                    <?php else: ?>
                    <strong><?= htmlspecialchars($user['points']) ?></strong> points
                    <?php endif; ?>
                </p>
                <?php else: ?>
                <h2>Bienvenue sur StudentSwap !</h2>
                <p>Connectez-vous pour accéder à toutes les fonctionnalités.</p>
                <?php endif; ?>
            </section>

            <!-- Formulaire de recherche -->
            <div class="search-form-container">
                <section class="search-form">
                    <form method="GET" action="index.php">
                        <input type="text" name="search_query" placeholder="Rechercher..."
                            value="<?= htmlspecialchars($raw_search_query) ?>">
                        <select name="category_id">
                            <option value="">Catégorie</option>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>"
                                <?= ($selected_category == $category['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <select name="city_id">
                            <option value="">Ville</option>
                            <?php foreach ($cities as $city): ?>
                            <option value="<?= htmlspecialchars($city['name']) ?>"
                                <?= ($selected_city == $city['name']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($city['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit">🔍</button>
                    </form>
                </section>
            </div>

            <!-- Liste des services disponibles -->
            <section class="services">
                <h3>Services disponibles</h3>
                <?php if (count($services) > 0): ?>
                <div class="service-list">
                    <?php foreach ($services as $service): ?>
                    <div class="service-card">
                        <h4><strong><?= htmlspecialchars($service['title']) ?></strong></h4>
                        <p><?= htmlspecialchars($service['description']) ?></p>
                        <small><strong>Proposé par :</strong> <a class="user_id"
                                href="view_profile.php?id=<?= htmlspecialchars($service['user_id']) ?>"><?= htmlspecialchars($service['username']) ?></a></small><br>
                        <small><strong>Catégorie :</strong>
                            <?= htmlspecialchars($service['category_name']) ?></small><br>
                        <small><strong>Localisation :</strong> <?= htmlspecialchars($service['location']) ?></small><br>
                        <small><strong>Coût :</strong>
                            <?php if ($service['points_cost'] == 0): ?>
                            <span class="free-service">GRATUIT</span>
                            <?php else: ?>
                            <?= htmlspecialchars($service['points_cost']) ?> points
                            <?php endif; ?>
                        </small><br><br>


                        <?php if ($user): ?>
                        <a href="../controllers/request_service.php?id=<?= $service['id'] ?>" class="btn">Demander ce
                            service</a>
                        <a href="leave_review.php?id=<?= $service['id'] ?>" class="btn-evaluate">Évaluer</a>
                        <?php else: ?>
                        <button class="btn" disabled>Connectez-vous pour demander ce service</button>
                        <button class="btn-evaluate" disabled>Connectez-vous pour évaluer</button>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p>Aucun service trouvé pour votre recherche.</p>
                <?php endif; ?>
            </section>
        </main>

        <!-- Inclusion du pied de page -->
        <?php include '../includes/footer.php'; ?>
    </div>
</body>

</html>