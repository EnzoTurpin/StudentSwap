<?php
session_start();
include '../config/db.php';

try {
    // Vérifier si l'utilisateur est connecté
    $user_id = $_SESSION['user_id'] ?? null;
    $profile_picture = 'default-picture.png';
    $user = null;

    // Si l'utilisateur est connecté, récupérer ses informations
    if ($user_id) {
        $sql = "SELECT username, email, COALESCE(points, 10) AS points, profile_picture FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Définir une valeur par défaut pour la photo de profil
        $profile_picture = $user['profile_picture'] ?? 'default-picture.png';

        // Si la clé 'points' n'est toujours pas présente, initialiser à 10
        if (!array_key_exists('points', $user)) {
            $user['points'] = 10;
        }
    }

    // Récupérer les catégories
    $sql = "SELECT * FROM categories";
    $stmt = $conn->query($sql);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les villes
    $sql = "SELECT * FROM cities ORDER BY name ASC";
    $stmt = $conn->query($sql);
    $cities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Initialiser les critères de recherche
    $selected_category = $_GET['category_id'] ?? '';
    $selected_city = $_GET['city_id'] ?? '';
    $search_query = $_GET['search_query'] ?? '';

    // Construire la requête de recherche
    $sql = "SELECT services.*, users.username, categories.name AS category_name 
            FROM services 
            JOIN users ON services.user_id = users.id
            JOIN categories ON services.category_id = categories.id
            WHERE services.status = 'available'";

    // Filtrer par catégorie si sélectionnée
    if (!empty($selected_category)) {
        $sql .= " AND services.category_id = :category_id";
    }

    // Filtrer par ville si sélectionnée
    if (!empty($selected_city)) {
        $sql .= " AND services.location = :city_id";
    }

    // Filtrer par mot-clé si une recherche est effectuée
    if (!empty($search_query)) {
        $sql .= " AND (services.title LIKE :search_query OR services.description LIKE :search_query)";
    }

    $sql .= " ORDER BY services.created_at DESC";
    $stmt = $conn->prepare($sql);

    // Lier les paramètres
    if (!empty($selected_category)) {
        $stmt->bindParam(':category_id', $selected_category, PDO::PARAM_INT);
    }
    if (!empty($selected_city)) {
        $stmt->bindParam(':city_id', $selected_city, PDO::PARAM_STR);
    }
    if (!empty($search_query)) {
        $search_query = '%' . $search_query . '%';
        $stmt->bindParam(':search_query', $search_query, PDO::PARAM_STR);
    }

    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
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
        <?php include '../includes/header.php'; ?>

        <main class="main-content">
            <section class="welcome">
                <?php if ($user): ?>
                <h2>Bienvenue, <?= htmlspecialchars($user['username']) ?>!</h2>
                <p>Solde de points : <strong><?= htmlspecialchars($user['points'] ?? 10) ?></strong> points</p>
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
                            value="<?= htmlspecialchars($search_query) ?>">
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

            <!-- Affichage des services -->
            <section class="services">
                <h3>Services disponibles</h3>
                <?php if (count($services) > 0): ?>
                <div class="service-list">
                    <?php foreach ($services as $service): ?>
                    <?php
                                $sql = "SELECT AVG(rating) AS average_rating FROM reviews WHERE service_id = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->execute([$service['id']]);
                                $review = $stmt->fetch(PDO::FETCH_ASSOC);
                                $average_rating = $review['average_rating'] ? round($review['average_rating'], 1) : 'Pas de note';
                            ?>
                    <div class="service-card">
                        <h4><strong><?= htmlspecialchars($service['title']) ?></strong></h4>
                        <p><?= htmlspecialchars($service['description']) ?></p>
                        <small><strong>Proposé par :</strong> <a class="user_id"
                                href="view_profile.php?id=<?= htmlspecialchars($service['user_id']) ?>"><?= htmlspecialchars($service['username']) ?></a></small><br>
                        <small><strong>Catégorie :</strong>
                            <?= htmlspecialchars($service['category_name']) ?></small><br>
                        <small><strong>Localisation :</strong> <?= htmlspecialchars($service['location']) ?></small><br>
                        <small><strong>Coût :</strong> <?= htmlspecialchars($service['points_cost']) ?>
                            points</small><br>
                        <small><strong>Note moyenne :</strong> <?= $average_rating ?>/5</small><br><br>

                        <?php if ($user): ?>
                        <a href="../controllers/request_service.php?id=<?= $service['id'] ?>" class="btn">Demander
                            ce
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

        <?php include '../includes/footer.php'; ?>
    </div>
</body>

</html>