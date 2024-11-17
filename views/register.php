<?php
include '../config/db.php';
session_start();

// Vérifier si la requête est de type POST (soumission du formulaire d'inscription)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Vérifier la sécurité du mot de passe
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        $error_message = "Le mot de passe doit contenir au moins 8 caractères, avec 1 majuscule, 1 minuscule, 1 chiffre, et 1 caractère spécial.";
    } else {
        // Hacher le mot de passe sécurisé
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Vérifier si l'email ou le nom d'utilisateur est déjà utilisé
        $check_sql = "SELECT * FROM users WHERE email = ? OR username = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->execute([$email, $username]);
        $existingUser = $stmt->fetch();

        if ($existingUser) {
            $error_message = "L'email ou le nom d'utilisateur est déjà utilisé.";
        } else {
            // Insérer le nouvel utilisateur dans la base de données
            $sql = "INSERT INTO users (username, email, password, points) VALUES (?, ?, ?, 10)";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute([$username, $email, $hashed_password])) {
                header("Location: login.php");
                exit;
            } else {
                $error_message = "Erreur lors de l'inscription. Veuillez réessayer.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - StudentSwap</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/validation.js"></script>
</head>

<body>
    <div class="wrapper">

        <!-- Inclusion de l'en-tête -->
        <?php include '../includes/header.php'; ?>

        <!-- Formulaire d'inscription -->
        <div class="form-container">
            <h2>Créer un compte</h2>
            <form method="POST" id="login-form">
                <input type="text" name="username" placeholder="Nom d'utilisateur" required>
                <input type="email" name="email" placeholder="Email" required>

                <!-- Champ pour le mot de passe avec icône pour afficher/masquer -->
                <div class="password-container">
                    <input type="password" id="new_password" name="new_password" placeholder="Nouveau mot de passe"
                        required>
                    <span id="toggle-new-password" class="icon">
                        <img id="show-new-password-icon" src="../assets/svg/show-password.svg"
                            alt="Montrer le mot de passe">
                        <img id="hide-new-password-icon" src="../assets/svg/hide-password.svg"
                            alt="Masquer le mot de passe" style="display: none;">
                    </span>
                </div>

                <button type="submit">S'inscrire</button>
            </form>

            <!-- Liens vers la page de connexion pour les utilisateurs déjà inscrits -->
            <div class="form-actions">
                <p>Déjà inscrit ? <a href="login.php">Se connecter</a></p>
            </div>
        </div>


        <!-- Inclusion du pied de page -->
        <?php include '../includes/footer.php'; ?>
    </div>
</body>

</html>