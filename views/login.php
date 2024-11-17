<?php
include '../config/db.php';
session_start();

// Récupérer l'ID de l'utilisateur connecté (si disponible)
$user_id = $_SESSION['user_id'] ?? null;

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Requête pour vérifier si l'utilisateur existe avec l'email fourni
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Vérifier le mot de passe et démarrer la session si les informations sont correctes
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: index.php");
        exit;
    } else {
        // Afficher un message d'erreur en cas d'identifiants incorrects
        $error_message = "Email ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Connexion - StudentSwap</title>
    <script src="../assets/js/validation.js"></script>
</head>

<body>
    <div class="wrapper">
        <!-- En-tête avec navigation -->
        <header>
            <a href="../views/index.php" class="site-title">StudentSwap</a>
            <nav class="navbar">
                <a href="../views/index.php">Accueil</a>
                <?php if ($user_id): ?>
                <a href="../controllers/add_service.php">Ajouter un service</a>
                <a href="../public/logout.php">Déconnexion</a>
                <a href="../views/profile.php?id=<?= htmlspecialchars($user_id) ?>" class="profile-icon">
                    <img src="../assets/img/<?= htmlspecialchars($profile_picture ?? 'default-picture.png') ?>"
                        alt="Photo de profil">
                </a>
                <?php else: ?>
                <a href="../views/login.php">Connexion</a>
                <?php endif; ?>
            </nav>
        </header>

        <!-- Formulaire de connexion -->
        <div class="form-container">
            <h2>Se connecter</h2>

            <!-- Affichage du message d'erreur -->
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <!-- Formulaire de saisie des identifiants -->
            <form method="POST" id="login-form">
                <input type="email" name="email" placeholder="Email" required>

                <!-- Champ pour le mot de passe avec icône pour afficher/masquer -->
                <div class="password-container">
                    <input type="password" id="password" name="password" class="login-input" placeholder="Mot de passe"
                        required>
                    <span id="toggle-new-password" class="icon">
                        <img id="show-new-password-icon" src="../assets/svg/show-password.svg"
                            alt="Montrer le mot de passe">
                        <img id="hide-new-password-icon" src="../assets/svg/hide-password.svg"
                            alt="Masquer le mot de passe" style="display: none;">
                    </span>
                </div>

                <button type="submit">Se connecter</button>
            </form>

            <!-- Liens d'action supplémentaires -->
            <div class="form-actions">
                <a href="register.php">Créer un compte</a>
                <a href="reset_password.php">Mot de passe oublié ?</a>
            </div>
        </div>

        <!-- Inclusion du pied de page -->
        <?php include '../includes/footer.php'; ?>
    </div>
</body>

</html>