<?php
include '../config/db.php';

$user_id = $_GET['user_id'] ?? null;
$offset = intval($_GET['offset'] ?? 0);
$limit = 5;

if (!$user_id) {
    echo "Erreur : ID utilisateur manquant.";
    exit;
}

try {
    // Requête pour récupérer les avis avec des variables pour LIMIT et OFFSET
    $sql = "SELECT reviews.*, users.username AS reviewer_name
            FROM reviews
            JOIN users ON reviews.user_id = users.id
            JOIN services ON reviews.service_id = services.id
            WHERE services.user_id = ?
            ORDER BY reviews.created_at DESC
            LIMIT $limit OFFSET $offset";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $reviews = $stmt->fetchAll();

    // Vérifier s'il y a des avis
    if (empty($reviews)) {
        echo "NO_MORE_REVIEWS";
        exit;
    }

    // Générer le HTML pour chaque avis
    foreach ($reviews as $review) {
        echo '<div class="review-card">';
        echo '<h4>Évalué par : ' . htmlspecialchars($review['reviewer_name']) . '</h4>';
        echo '<p>Note : ' . str_repeat('⭐', $review['rating']) . '</p>';
        echo '<p>Commentaire : ' . htmlspecialchars($review['comment']) . '</p>';
        echo '<small>Posté le : ' . htmlspecialchars($review['created_at']) . '</small>';
        echo '</div>';
    }
} catch (PDOException $e) {
    echo "Erreur lors de la récupération des avis : " . $e->getMessage();
}
?>