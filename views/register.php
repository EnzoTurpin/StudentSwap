<?php
include '../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Vérifier si l'email ou le nom d'utilisateur est déjà utilisé
    $check_sql = "SELECT * FROM users WHERE email = ? OR username = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->execute([$email, $username]);
    $existingUser = $stmt->fetch();

    if ($existingUser) {
        echo "L'email ou le nom d'utilisateur est déjà utilisé.";
    } else {
        // Insérer le nouvel utilisateur avec 10 points par défaut
        $sql = "INSERT INTO users (username, email, password, points) VALUES (?, ?, ?, 10)";
        $stmt = $conn->prepare($sql);

        if ($stmt->execute([$username, $email, $password])) {
            header("Location: login.php");
            exit;
        } else {
            echo "Erreur lors de l'inscription. Veuillez réessayer.";
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
</head>

<body>
    <div class="wrapper">

        <?php include '../includes/header.php'; ?>

        <div class="form-container">
            <h2>Créer un compte</h2>
            <form method="POST">
                <input type="text" name="username" placeholder="Nom d'utilisateur" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Mot de passe" required>
                <button type="submit">S'inscrire</button>
            </form>
            <div class="form-actions">
                <p>Déjà inscrit ? <a href="login.php">Se connecter</a></p>
            </div>

        </div>
        <?php include '../includes/footer.php'; ?>
    </div>
</body>

</html>