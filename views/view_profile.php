<?php
session_start();
include '../config/db.php';

// Récupérer l'ID de l'utilisateur connecté
$user_id = $_SESSION['user_id'];
$logged_in_user_picture = 'default-picture.png';

// Récupérer les informations de l'utilisateur connecté
$sql = "SELECT id, username, email, points, profile_picture, is_admin FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$logged_in_user = $stmt->fetch();

// Déterminer la photo de profil de l'utilisateur connecté
$logged_in_user_picture = $logged_in_user['profile_picture'] ?? 'default-picture.png';

// Récupérer l'ID de l'utilisateur dont on veut afficher le profil
$profile_user_id = $_GET['id'] ?? null;
if (!$profile_user_id) {
    header("Location: index.php");
    exit;
}

$message = "";
$reviews = [];
$user_services = [];

try {
    // Récupérer les informations de l'utilisateur à afficher
$sql = "SELECT id, username, email, points, profile_picture, is_admin FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$profile_user_id]);
$user = $stmt->fetch();

// Vérifier si l'utilisateur existe
if (!$user) {
    die("L'utilisateur n'existe pas.");
}

// Nouvelle vérification : rediriger vers "access_denied.php" uniquement si
// l'utilisateur connecté n'est pas administrateur OU s'il essaie d'accéder à un profil administrateur autre que le sien
if ($user['is_admin'] == 1 && $user_id != $profile_user_id) {
    header("Location: access_denied.php");
    exit;
}

    // Définir la photo de profil de l'utilisateur à afficher
    $profile_picture = $user['profile_picture'] ?? 'default-picture.png';
    $profile_picture_path = file_exists("../uploads/profile_pictures/" . $profile_picture) ?
        "../uploads/profile_pictures/" . htmlspecialchars($profile_picture) :
        "../assets/img/default-picture.png";

    // Calculer la moyenne des évaluations et le nombre d'avis
    $sql = "SELECT AVG(reviews.rating) AS average_rating, COUNT(reviews.id) AS total_reviews
            FROM reviews
            JOIN services ON reviews.service_id = services.id
            WHERE services.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$profile_user_id]);
    $review_data = $stmt->fetch();
    $average_rating = $review_data['average_rating'] ? round($review_data['average_rating'], 1) : 'Pas encore évalué';
    $total_reviews = $review_data['total_reviews'] ?? 0;

    // Récupérer les 3 premiers avis pour l'utilisateur
    $sql = "SELECT reviews.*, users.username AS reviewer_name 
            FROM reviews
            JOIN users ON reviews.user_id = users.id
            JOIN services ON reviews.service_id = services.id
            WHERE services.user_id = ?
            ORDER BY reviews.created_at DESC
            LIMIT 3";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$profile_user_id]);
    $reviews = $stmt->fetchAll();

    // Récupérer les services proposés par l'utilisateur
    $sql = "SELECT * FROM services WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$profile_user_id]);
    $user_services = $stmt->fetchAll();

} catch (PDOException $e) {
    $message = "Erreur lors de la récupération des données : " . $e->getMessage();
}

function translateStatus($status) {
    switch ($status) {
        case 'available':
            return 'Disponible';
        case 'requested':
            return 'Demandé';
        case 'accepted':
            return 'Accepté';
        case 'rejected':
            return 'Refusé';
        default:
            return 'Inconnu';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - <?= htmlspecialchars($user['username']) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="wrapper">
        <?php include '../includes/header.php'; ?>

        <!-- Section d'affichage des informations de profil -->
        <main class="main-content">
            <section class="profile-info">
                <h2>Profil de <?= htmlspecialchars($user['username']) ?></h2>
                <img src="<?= $profile_picture_path ?>" alt="Photo de profil" class="profile-picture">
                <p><strong>Nom d'utilisateur :</strong> <?= htmlspecialchars($user['username']) ?></p>
                <p><strong>Email :</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>Solde de points :</strong>
                    <?php if (isset($user['is_admin']) && $user['is_admin'] == 1): ?>
                    <img src="../assets/svg/infinite.svg" alt="Points illimités" class="infinite-icon">
                    <?php else: ?>
                    <strong><?= htmlspecialchars($user['points']) ?></strong> points
                    <?php endif; ?>
                </p>
                <p><strong>Évaluation moyenne :</strong> <?= $average_rating ?> / 5 (<?= $total_reviews ?> avis)</p>
            </section>

            <!-- Section des services proposés -->
            <div class="windows-container">
                <div class="window">
                    <div class="profile-container">
                        <h2>Services proposés par <?= htmlspecialchars($user['username']) ?></h2>
                        <div class="window-content">
                            <section class="user-services">
                                <div class="service-list">
                                    <?php if (!empty($user_services)): ?>
                                    <?php foreach ($user_services as $service): ?>
                                    <div class="service-card">
                                        <h4><?= htmlspecialchars($service['title']) ?></h4>
                                        <p><?= htmlspecialchars($service['description']) ?></p>
                                        <small><strong>Lieu :</strong>
                                            <?= htmlspecialchars($service['location']) ?></small><br>
                                        <small><strong>Coût :</strong>
                                            <?php if ($service['points_cost'] == 0): ?>
                                            <span class="free-service">GRATUIT</span>
                                            <?php else: ?>
                                            <?= htmlspecialchars($service['points_cost']) ?> points
                                            <?php endif; ?>
                                        </small><br>
                                        <small><strong>Statut :</strong>
                                            <?= htmlspecialchars(translateStatus($service['status'])) ?></small>
                                    </div>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                    <p>Aucun service proposé par cet utilisateur.</p>
                                    <?php endif; ?>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

                <!-- Section des avis reçus -->
                <div class="window">
                    <div class="reviews-container">
                        <h2>Avis reçus</h2>
                        <div class="window-content">
                            <section class="user-reviews">
                                <?php if (!empty($reviews)): ?>
                                <div class="review-list">
                                    <?php foreach ($reviews as $review): ?>
                                    <div class="review-card">
                                        <h4>Évalué par : <?= htmlspecialchars($review['reviewer_name']) ?></h4>
                                        <p>Note : <?= str_repeat('⭐', $review['rating']) ?></p>
                                        <p>Commentaire : <?= htmlspecialchars($review['comment']) ?></p>
                                        <small>Posté le : <?= htmlspecialchars($review['created_at']) ?></small>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <button id="load-more-btn" class="button"
                                    data-user-id="<?= htmlspecialchars($profile_user_id) ?>">Afficher plus
                                    d'avis</button>
                                <?php else: ?>
                                <p>Aucun avis pour le moment.</p>
                                <?php endif; ?>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <?php include '../includes/footer.php'; ?>
    </div>
    <script src="../assets/js/load_more_reviews.js"></script>
</body>

</html>