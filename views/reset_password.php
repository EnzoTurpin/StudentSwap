<?php
include '../config/db.php';
session_start();

// Vérifier si le formulaire a été soumis

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Vérifier que les mots de passe correspondent
    if ($new_password !== $confirm_password) {
        $error_message = "Les mots de passe ne correspondent pas.";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $new_password)) {
        $error_message = "Le mot de passe doit contenir au moins 8 caractères, avec 1 majuscule, 1 minuscule, 1 chiffre, et 1 caractère spécial.";
    } else {
        // Requête pour vérifier si l'email existe dans la base de données
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();
       
        // Vérifier si l'utilisateur existe
        if (!$user) {
            $error_message = "Aucun compte trouvé avec cet email.";
        } elseif (password_verify($new_password, $user['password'])) {
            // Vérifier que le nouveau mot de passe est différent de l'ancien
            $error_message = "Le nouveau mot de passe ne peut pas être identique à l'ancien.";
        } else {
            // Mise à jour du mot de passe dans la base de données
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = ? WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$hashed_password, $email]);

            // Message de succès après mise à jour
            $success_message = "Votre mot de passe a été mis à jour avec succès. <a href='login.php'>Se connecter</a>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Réinitialiser le mot de passe</title>
    <script src="../assets/js/validation.js"></script>
</head>

<body>
    <div class="wrapper">

        <!-- En-tête du site avec navigation -->
        <header>
            <a href="../views/index.php" class="site-title">StudentSwap</a>
            <nav class="navbar">
                <a href="../views/index.php">Accueil</a>
                <a href="../views/login.php">Connexion</a>
            </nav>
        </header>

        <!-- Conteneur du formulaire de réinitialisation de mot de passe -->
        <div class="form-container">
            <h2>Réinitialiser le mot de passe</h2>

            <!-- Affichage des messages d'erreur -->
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <!-- Affichage du message de succès -->
            <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?= $success_message ?></div>
            <?php else: ?>

            <form method="POST" onsubmit="return validatePassword()" id="login-form">
                <input class="login-input" type="email" name="email" placeholder="Votre email" required>

                <!-- Champ pour le nouveau mot de passe avec icône pour afficher/masquer -->
                <div class="password-container">
                    <input type="password" id="new_password" name="new_password" placeholder="Nouveau mot de passe"
                        required>
                    <span id="toggle-new-password" class="icon">
                        <!-- Icône pour montrer le mot de passe (fichier SVG externe) -->
                        <img id="show-new-password-icon" src="../assets/svg/show-password.svg"
                            alt="Montrer le mot de passe">
                        <!-- Icône pour cacher le mot de passe (fichier SVG externe) -->
                        <img id="hide-new-password-icon" src="../assets/svg/hide-password.svg"
                            alt="Masquer le mot de passe" style="display: none;">
                    </span>
                </div>

                <!-- Champ pour la confirmation du mot de passe avec icône pour afficher/masquer -->
                <div class="password-container">
                    <input type="password" id="confirm_password" name="confirm_password"
                        placeholder="Confirmer le mot de passe" required>
                    <span id="toggle-confirm-password" class="icon">
                        <!-- Icône pour montrer le mot de passe (fichier SVG externe) -->
                        <img id="show-confirm-password-icon" src="../assets/svg/show-password.svg"
                            alt="Montrer le mot de passe">
                        <!-- Icône pour cacher le mot de passe (fichier SVG externe) -->
                        <img id="hide-confirm-password-icon" src="../assets/svg/hide-password.svg"
                            alt="Masquer le mot de passe" style="display: none;">
                    </span>
                </div>

                <button type="submit">Réinitialiser</button>
            </form>

            <div class="form-actions">
                <p>Déjà inscrit ? <a href="login.php">Se connecter</a></p>
                <a href="register.php">Créer un compte</a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Inclusion du pied de page -->
        <?php include '../includes/footer.php'; ?>
    </div>
</body>

</html>