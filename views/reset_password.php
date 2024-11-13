<?php
include '../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Vérification que les mots de passe correspondent
    if ($new_password !== $confirm_password) {
        $error_message = "Les mots de passe ne correspondent pas.";
    } else {
        // Vérifier si l'email existe dans la base de données
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error_message = "Aucun compte trouvé avec cet email.";
        } else {
            // Vérifier si le nouveau mot de passe est identique à l'ancien
            if (password_verify($new_password, $user['password'])) {
                $error_message = "Le nouveau mot de passe ne peut pas être identique à l'ancien.";
            } else {
                // Mise à jour du mot de passe
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET password = ? WHERE email = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$hashed_password, $email]);

                $success_message = "Votre mot de passe a été mis à jour avec succès. <a href='login.php'>Se connecter</a>";
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
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Réinitialiser le mot de passe</title>
</head>

<body>
    <div class="wrapper">

        <header>
            <a href="../views/index.php" class="site-title">StudentSwap</a>
            <nav class="navbar">
                <a href="../views/index.php">Accueil</a>
                <a href="../views/login.php">Connexion</a>
            </nav>
        </header>

        <div class="form-container">
            <h2>Réinitialiser le mot de passe</h2>

            <?php if (!empty($error_message)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?= $success_message ?></div>
            <?php else: ?>
            <form method="POST">
                <input type="email" name="email" placeholder="Votre email" required>
                <input type="password" name="new_password" placeholder="Nouveau mot de passe" required>
                <input type="password" name="confirm_password" placeholder="Confirmer le mot de passe" required>
                <button type="submit">Réinitialiser</button>
            </form>
            <?php endif; ?>
        </div>
        <?php include '../includes/footer.php'; ?>
    </div>
</body>

</html>