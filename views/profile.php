<?php
session_start();
include '../config/db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Définir les variables utilisateur et profil
$user_id = $_SESSION['user_id'];
$profile_user_id = $_GET['id'] ?? $user_id;
$message = "";

try {
    // Récupérer les informations de l'utilisateur
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$profile_user_id]);
    $user = $stmt->fetch();

    // Si l'utilisateur n'existe pas
    if (!$user) {
        die("L'utilisateur n'existe pas.");
    }

    // Déterminer l'image de profil à afficher
    $profile_picture_path = "../uploads/profile_pictures/" . $user['profile_picture'];
    $default_picture = "../assets/img/default-picture.png";

    // Utiliser l'image par défaut si aucune photo n'est définie ou si le fichier n'existe pas
    if (empty($user['profile_picture']) || !file_exists($profile_picture_path)) {
        $profile_picture = $default_picture;
    } else {
        $profile_picture = $profile_picture_path;
    }

    // Récupérer les services proposés
    $sql = "SELECT * FROM services WHERE user_id = ? AND status = 'available' ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $services = $stmt->fetchAll();

    // Récupérer les services demandés (status 'requested')
    $sql = "SELECT sr.*, s.title, u.username AS requester
            FROM service_requests sr
            JOIN services s ON sr.service_id = s.id
            JOIN users u ON sr.requester_id = u.id
            WHERE sr.status = 'requested' AND s.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $requested_services = $stmt->fetchAll();

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

    // Récupérer les services acceptés (status 'accepted')
    $sql = "SELECT sr.*, s.title, s.points_cost, u.username AS requester
            FROM service_requests sr
            JOIN services s ON sr.service_id = s.id
            JOIN users u ON sr.requester_id = u.id
            WHERE sr.status = 'accepted' AND s.user_id = ?
            ORDER BY sr.accepted_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $accepted_services = $stmt->fetchAll();

    // Récupérer les services demandés par l'utilisateur connecté (status 'requested')
    $sql = "SELECT sr.*, s.title, s.points_cost, s.user_id AS provider_id
        FROM service_requests sr
        JOIN services s ON sr.service_id = s.id
        WHERE sr.requester_id = ? AND sr.status = 'requested'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $requested_by_user_services = $stmt->fetchAll();

    // Créer un objet DateTime à partir de la date 'accepted_at'
    $date = new DateTime($service['accepted_at']);
    $formatted_date = $date->format('d F Y à H\hi'); // Exemple : "17 Novembre 2024, 23h56"

    // Convertir le mois en français
    $mois_francais = [
    'January' => 'Janvier', 'February' => 'Février', 'March' => 'Mars', 'April' => 'Avril',
    'May' => 'Mai', 'June' => 'Juin', 'July' => 'Juillet', 'August' => 'Août',
    'September' => 'Septembre', 'October' => 'Octobre', 'November' => 'Novembre', 'December' => 'Décembre'
    ];
    
    $formatted_date = str_replace(array_keys($mois_francais), array_values($mois_francais), $formatted_date);

    // Mise à jour des informations du profil
    if ($profile_user_id == $user_id && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Mise à jour de la photo de profil
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
            $profile_picture = $_FILES['profile_picture'];
            $file_ext = pathinfo($profile_picture['name'], PATHINFO_EXTENSION);
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

            // Vérifier si le fichier est une image
            if (in_array(strtolower($file_ext), $allowed_ext)) {
                $new_filename = "profile_" . $user_id . "." . $file_ext;
                $upload_dir = "../uploads/profile_pictures/";
                $upload_path = $upload_dir . $new_filename;

                // Déplacer le fichier téléchargé vers le dossier des photos de profil
                if (move_uploaded_file($profile_picture['tmp_name'], $upload_path)) {
                    $sql = "UPDATE users SET profile_picture = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$new_filename, $user_id]);

                    // Mettre à jour la variable de session
                    $user['profile_picture'] = $new_filename;
                    $_SESSION['profile_picture'] = $new_filename;
                    $message = "Photo de profil mise à jour avec succès.";
                } else {
                    $message = "Erreur lors du téléchargement de l'image.";
                }
            } else {
                $message = "Format de fichier non supporté.";
            }
        }

        // Mise à jour de l'email et du mot de passe
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET email = ?, password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$email, $hashed_password, $user_id]);
        } else {
            $sql = "UPDATE users SET email = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$email, $user_id]);
        }

        $message = "Profil mis à jour avec succès.";
    }
} catch (PDOException $e) {
    $message = "Erreur : " . $e->getMessage();
}
?>

<!-- Code HTML pour la page de profil -->
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - StudentSwap</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="wrapper">
        <?php include '../includes/header.php'; ?>

        <main class="main-content">

            <!-- Fenêtre "Mon Profil" -->
            <section class="profile-info">
                <h2>Mon Profil</h2>
                <?php if ($message): ?>
                <p class="alert alert-success"><?= htmlspecialchars($message) ?></p>
                <?php endif; ?>
                <img src="../uploads/profile_pictures/<?= htmlspecialchars($profile_picture) ?>" alt="Photo de profil"
                    class="profile-picture" onerror="this.onerror=null; this.src='../assets/img/default-picture.png';">
                <p><strong>Nom d'utilisateur :</strong> <?= htmlspecialchars($user['username']) ?></p>
                <p><strong>Email :</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>Solde de points :</strong> <?= htmlspecialchars($user['points']) ?> points</p>
                <p><strong>Évaluation moyenne :</strong> <?= $average_rating ?> / 5 (<?= $total_reviews ?> avis)</p>

                <?php if ($profile_user_id == $user_id): ?>
                <form method="POST" enctype="multipart/form-data">
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"
                        placeholder="Email">
                    <input type="password" name="password" placeholder="Nouveau mot de passe">
                    <div class="custom-file-input">
                        <label for="profile_picture" class="file-label">📁 Choisir une photo</label>
                        <input type="file" id="profile_picture" name="profile_picture" accept=".jpg,.jpeg,.png,.gif">
                        <span class="file-name">Aucune photo sélectionnée</span>
                    </div>
                    <button type="submit" class="button">Mettre à jour</button>
                </form>
                <?php endif; ?>
            </section>

            <!-- Fenêtre "Mes services" -->
            <div class="windows-container">
                <div class="window">
                    <h3>Mes services</h3>
                    <div class="window-content">
                        <?php if (count($services) > 0): ?>
                        <?php foreach ($services as $service): ?>
                        <div class="service-card">
                            <h4><?= htmlspecialchars($service['title']) ?></h4>
                            <p><?= htmlspecialchars($service['description']) ?></p>
                            <form method="POST" action="../controllers/delete_service.php">
                                <input type="hidden" name="service_id" value="<?= htmlspecialchars($service['id']) ?>">
                                <button type="submit" class="button-delete">&nbsp;&nbsp; 🗑️ &nbsp;&nbsp;</button>
                            </form>

                        </div>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <p>Pas de services proposés.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Fenêtre "Demandes en attente" -->
                <div class="window">
                    <h3>Demandes en attente</h3>
                    <div class="window-content">
                        <?php if (count($requested_services) > 0): ?>
                        <?php foreach ($requested_services as $service): ?>
                        <div class="request-card">
                            <h4><?= htmlspecialchars($service['title']) ?></h4>
                            <p>Demandé par : <?= htmlspecialchars($service['requester']) ?></p>
                            <form method="POST" action="../controllers/handle_request.php">
                                <input type="hidden" name="service_id" value="<?= $service['service_id'] ?>">
                                <button type="submit" name="action" value="accept"
                                    class="button-accept">Accepter</button>
                                <button type="submit" name="action" value="reject"
                                    class="button-reject">Refuser</button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <p>Aucune demande en attente.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Section "Services acceptés" -->
                <div class="window">
                    <h3>Services acceptés</h3>
                    <div class="window-content">
                        <?php if (count($accepted_services) > 0): ?>
                        <?php foreach ($accepted_services as $service): ?>
                        <div class="accepted-service-card">
                            <h4><?= htmlspecialchars($service['title']) ?></h4>
                            <p><strong>Demandé par :</strong> <?= htmlspecialchars($service['requester']) ?></p>
                            <p class="points"><strong>Coût :</strong> <?= htmlspecialchars($service['points_cost']) ?>
                                points</p>
                            <p class="date"><strong>Date d'acceptation : </strong>Le
                                <?= htmlspecialchars($formatted_date) ?></p>
                        </div>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <p>Aucun service accepté pour le moment.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Fenêtre "Mes demandes de services" -->
                <div class="window">
                    <h3>Mes demandes de services</h3>
                    <div class="window-content">
                        <?php if (count($requested_by_user_services) > 0): ?>
                        <?php foreach ($requested_by_user_services as $service): ?>
                        <div class="request-card">
                            <h4><?= htmlspecialchars($service['title']) ?></h4>
                            <p><strong>Coût :</strong> <?= htmlspecialchars($service['points_cost']) ?> points</p>
                            <form method="POST" action="../controllers/cancel_request.php">
                                <input type="hidden" name="service_id"
                                    value="<?= htmlspecialchars($service['service_id']) ?>">
                                <input type="hidden" name="request_id" value="<?= htmlspecialchars($service['id']) ?>">
                                <button type="submit" class="button-delete">❌ Annuler la demande</button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <p>Aucune demande de service en cours.</p>
                        <?php endif; ?>
                    </div>
                </div>


            </div>

        </main>

        <?php include '../includes/footer.php'; ?>
    </div>
</body>

</html>