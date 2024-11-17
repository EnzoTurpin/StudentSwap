<?php
// Inclusion de la configuration de la base de données et démarrage de la session
include '../config/db.php';
session_start();

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/login.php");
    exit;
}

// Récupération des catégories
$sql = "SELECT * FROM categories";
$stmt = $conn->query($sql);
$categories = $stmt->fetchAll();

// Récupération des informations de l'utilisateur connecté
$user_id = $_SESSION['user_id'];
$sql = "SELECT username, email, points, profile_picture FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$profile_picture = $user['profile_picture'] ?? 'default-picture.png';

// Récupération des villes
$sql = "SELECT * FROM cities ORDER BY name ASC";
$stmt = $conn->query($sql);
$cities = $stmt->fetchAll();

// Traitement du formulaire d'ajout de service
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $location = $_POST['location'];
    $points_cost = $_POST['points_cost'];

    // Insertion du service dans la base de données
    $sql = "INSERT INTO services (user_id, title, description, category_id, location, points_cost) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Vérification de la réussite de l'insertion
    if ($stmt->execute([$user_id, $title, $description, $category_id, $location, $points_cost])) {
        // Redirection vers l'index après ajout réussi
        header("Location: ../views/index.php");
        exit;
    } else {
        $message = "Erreur lors de l'ajout du service.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un service - StudentSwap</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="wrapper">
        <!-- Inclusion de l'en-tête -->
        <?php include '../includes/header.php'; ?>

        <!-- Formulaire d'ajout de service -->
        <div class="form-container">
            <h2>Ajouter un service</h2>
            <?php if (isset($message)): ?>
            <p class="alert"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>
            <form method="POST">
                <input type="text" name="title" placeholder="Titre du service" required>
                <textarea name="description" placeholder="Description" required></textarea>

                <!-- Sélection de la catégorie -->
                <select name="category_id" required>
                    <option value="">Sélectionnez une catégorie</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <!-- Sélection de la ville -->
                <select name="location" required>
                    <option value="">Sélectionner une ville</option>
                    <?php foreach ($cities as $city): ?>
                    <option value="<?= htmlspecialchars($city['name']) ?>"><?= htmlspecialchars($city['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>

                <input type="number" name="points_cost" placeholder="Coût en points" required min="0">

                <!-- Boutons d'action -->
                <div class="button-container">
                    <button type="submit" class="button">Ajouter le service</button>
                    <a href="../views/index.php" class="button back-button">Retour à l'accueil</a>
                </div>
            </form>
        </div>

        <!-- Inclusion du pied de page -->
        <?php include '../includes/footer.php'; ?>
    </div>
</body>

</html>