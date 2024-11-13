<?php
include '../config/db.php';
session_start();

// Vérifier si la requête est de type POST (soumission du formulaire d'inscription)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Vérifier si l'email ou le nom d'utilisateur est déjà utilisé
    $check_sql = "SELECT * FROM users WHERE email = ? OR username = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->execute([$email, $username]);
    $existingUser = $stmt->fetch();

    // Afficher un message d'erreur si l'utilisateur existe déjà
    if ($existingUser) {
        echo "L'email ou le nom d'utilisateur est déjà utilisé.";
    } else {
        // Insérer le nouvel utilisateur dans la base de données avec 10 points par défaut
        $sql = "INSERT INTO users (username, email, password, points) VALUES (?, ?, ?, 10)";
        $stmt = $conn->prepare($sql);

        // Vérifier si l'insertion s'est déroulée avec succès
        if ($stmt->execute([$username, $email, $password])) {
            // Rediriger l'utilisateur vers la page de connexion après inscription réussie
            header("Location: login.php");
            exit;
        } else {
            // Afficher un message d'erreur en cas de problème lors de l'inscription
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

        <!-- Inclusion de l'en-tête -->
        <?php include '../includes/header.php'; ?>

        <!-- Formulaire d'inscription -->
        <div class="form-container">
            <h2>Créer un compte</h2>
            <form method="POST">
                <input type="text" name="username" placeholder="Nom d'utilisateur" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Mot de passe" required>
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