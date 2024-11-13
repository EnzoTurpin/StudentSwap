<!-- includes/footer.php -->
<?php
// Démarrer la session si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialisation des variables par défaut
$user_id = $_SESSION['user_id'] ?? null;
$profile_picture = 'default-picture.png';
$logged_in_user = null;

try {
    // Si l'utilisateur est connecté, récupérer ses informations
    if (!empty($user_id)) {
        include '../config/db.php';

        // Récupérer les informations de l'utilisateur depuis la base de données
        $sql = "SELECT id, username, email, points, profile_picture FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id]);
        $logged_in_user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérifier et mettre à jour la photo de profil si elle existe
        if ($logged_in_user && !empty($logged_in_user['profile_picture'])) {
            $profile_path = "../uploads/profile_pictures/" . $logged_in_user['profile_picture'];
            $profile_picture = file_exists($profile_path) ? $logged_in_user['profile_picture'] : 'default-picture.png';
        }
    }
} catch (PDOException $e) {
    // Enregistrer l'erreur dans les logs en cas de problème
    error_log("Erreur dans le header : " . $e->getMessage());
}
?>

<header>
    <!-- Titre du site et lien vers la page d'accueil -->
    <a href="../views/index.php" class="site-title">StudentSwap</a>
    <nav class="navbar">
        <a href="../views/index.php">Accueil</a>

        <!-- Afficher les options de navigation en fonction de l'état de connexion -->
        <?php if (!empty($user_id) && $logged_in_user): ?>
        <a href="../controllers/add_service.php">Ajouter un service</a>
        <a href="../public/logout.php">Déconnexion</a>
        <!-- Affichage de l'icône de profil avec la photo de l'utilisateur -->
        <a href="../views/profile.php?id=<?= htmlspecialchars($user_id) ?>" class="profile-icon">
            <img src="../uploads/profile_pictures/<?= htmlspecialchars($profile_picture) ?>" alt="Photo de profil"
                onerror="this.onerror=null; this.src='../assets/img/default-picture.png';">
        </a>
        <?php else: ?>
        <!-- Lien vers la page de connexion si l'utilisateur n'est pas connecté -->
        <a href="../views/login.php">Connexion</a>
        <?php endif; ?>
    </nav>
</header>