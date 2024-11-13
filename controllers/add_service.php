<?php
include '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/login.php");
    exit;
}

// Récupérer les catégories depuis la base de données
$sql = "SELECT * FROM categories";
$stmt = $conn->query($sql);
$categories = $stmt->fetchAll();

// Récupérer les informations de l'utilisateur connecté
$user_id = $_SESSION['user_id'];
$sql = "SELECT username, email, points, profile_picture FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Utiliser l'image par défaut si aucune photo de profil n'est définie
$profile_picture = $user['profile_picture'] ?? 'default-picture.png';

// Récupérer les villes depuis la base de données
$sql = "SELECT * FROM cities ORDER BY name ASC";
$stmt = $conn->query($sql);
$cities = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $location = $_POST['location'];
    $points_cost = $_POST['points_cost'];

    // Insérer le service dans la base de données
    $sql = "INSERT INTO services (user_id, title, description, category_id, location, points_cost) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt->execute([$user_id, $title, $description, $category_id, $location, $points_cost])) {
        $message = "Service ajouté avec succès.";
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
        <?php
        include '../includes/header.php';
        ?>

        <div class="form-container">
            <h2>Ajouter un service</h2>
            <?php if (isset($message)): ?>
            <p class="alert"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>
            <form method="POST">
                <input type="text" name="title" placeholder="Titre du service" required>
                <textarea name="description" placeholder="Description" required></textarea>

                <!-- Liste déroulante des catégories -->
                <select name="category_id" required>
                    <option value="">Sélectionnez une catégorie</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <!-- Liste déroulante des villes -->
                <select name="location" required>
                    <option value="">Sélectionner une ville</option>
                    <?php foreach ($cities as $city): ?>
                    <option value="<?= htmlspecialchars($city['name']) ?>"><?= htmlspecialchars($city['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>

                <input type="number" name="points_cost" placeholder="Coût en points" required min="0">

                <!-- Conteneur pour les boutons -->
                <div class="button-container">
                    <button type="submit" class="button">Ajouter le service</button>
                    <a href="../views/index.php" class="button back-button">Retour à l'accueil</a>
                </div>
            </form>
        </div>

        <?php
        include '../includes/footer.php';
        ?>
    </div>
</body>

</html>