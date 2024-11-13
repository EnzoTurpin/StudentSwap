<?php
// Démarrer la session uniquement si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Définir $user_id, $logged_in_user, et $profile_picture par défaut
$user_id = $_SESSION['user_id'] ?? null;
$profile_picture = 'default-picture.png';
$logged_in_user = null;

try {
    // Si l'utilisateur est connecté, récupérer ses informations depuis la base de données
    if (!empty($user_id)) {
        include '../config/db.php';

        // Récupérer les informations de l'utilisateur connecté
        $sql = "SELECT id, username, email, points, profile_picture FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id]);
        $logged_in_user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si l'utilisateur existe, mettre à jour la photo de profil
        if ($logged_in_user && !empty($logged_in_user['profile_picture'])) {
            $profile_picture = file_exists("../uploads/profile_pictures/" . $logged_in_user['profile_picture']) ?
                $logged_in_user['profile_picture'] :
                'default-picture.png';
        }
    }
} catch (PDOException $e) {
    error_log("Erreur dans le header : " . $e->getMessage());
}
?>

<header>
    <a href="../views/index.php" class="site-title">StudentSwap</a>
    <nav class="navbar">
        <a href="../views/index.php">Accueil</a>
        <?php if (!empty($user_id) && $logged_in_user): ?>
        <a href="../controllers/add_service.php">Ajouter un service</a>
        <a href="../public/logout.php">Déconnexion</a>
        <a href="../views/profile.php?id=<?= htmlspecialchars($user_id) ?>" class="profile-icon">
            <img src="../uploads/profile_pictures/<?= htmlspecialchars($profile_picture) ?>" alt="Photo de profil"
                onerror="this.onerror=null; this.src='../assets/img/default-picture.png';">
        </a>
        <?php else: ?>
        <a href="../views/login.php">Connexion</a>
        <?php endif; ?>
    </nav>
</header>