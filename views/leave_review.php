<?php
session_start();
include '../config/db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: login.php");
    exit;
}

// Récupérer les informations de l'utilisateur connecté
$user_id = $_SESSION['user_id'];
$sql = "SELECT username, email, points, profile_picture FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Utiliser l'image par défaut si aucune photo de profil n'est définie
$profile_picture = $user['profile_picture'] ?? 'default-picture.png';


$user_id = $_SESSION['user_id'];
$service_id = $_GET['id'];
$message = "";

try {
    // Récupérer les informations du service
    $sql = "SELECT services.*, users.username AS provider 
            FROM services 
            JOIN users ON services.user_id = users.id 
            WHERE services.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$service_id]);
    $service = $stmt->fetch();

    if (!$service) {
        die("Le service n'existe pas.");
    }

    // Ajouter une évaluation
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $rating = $_POST['rating'];
        $comment = $_POST['comment'];

        // Vérifier que la note est valide
        if ($rating < 1 || $rating > 5) {
            $message = "La note doit être entre 1 et 5.";
        } else {
            $sql = "INSERT INTO reviews (service_id, user_id, rating, comment) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$service_id, $user_id, $rating, $comment]);
            $message = "Merci pour votre évaluation !";
        }
    }
} catch (PDOException $e) {
    $message = "Erreur lors de l'évaluation : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laisser une évaluation</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="wrapper">
        <?php
        include '../includes/header.php';
        ?>

        <main class="main-content">
            <section class="review-form">
                <h2>Évaluer le service : <?= htmlspecialchars($service['title']) ?></h2>
                <?php if ($message): ?>
                <p class="alert alert-success"><?= htmlspecialchars($message) ?></p>
                <?php endif; ?>
                <form method="POST">
                    <label for="rating">Note (1 à 5 étoiles) :</label>
                    <select name="rating" id="rating" required>
                        <option value="">Choisir une note</option>
                        <option value="1">⭐ - Nul </option>
                        <option value="2">⭐⭐ - Pas top </option>
                        <option value="3">⭐⭐⭐ - Moyen </option>
                        <option value="4">⭐⭐⭐⭐ - Très bien </option>
                        <option value="5">⭐⭐⭐⭐⭐ - Execellent </option>
                    </select>

                    <label for="comment">Commentaire :</label>
                    <textarea name="comment" id="comment" placeholder="Votre commentaire..." required></textarea>

                    <button type="submit" class="button">Soumettre</button>
                </form>
            </section>
        </main>

        <?php
        include '../includes/footer.php';
        ?>
    </div>
</body>

</html>