<?php
include '../config/db.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: ../views/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$service_id = $_GET['id'];

try {
    // Récupérer les informations du service
    $sql = "SELECT services.*, users.username AS provider_username, users.id AS provider_id, users.points AS provider_points 
            FROM services 
            JOIN users ON services.user_id = users.id 
            WHERE services.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$service_id]);
    $service = $stmt->fetch();

    if (!$service) {
        die("Le service n'existe pas.");
    }

    // Vérifier si l'utilisateur essaie de demander son propre service
    if ($service['provider_id'] == $user_id) {
        die("Vous ne pouvez pas demander votre propre service.");
    }

    // Vérifier si le service est déjà demandé
    if ($service['status'] != 'available') {
        die("Ce service a déjà été demandé ou n'est plus disponible.");
    }

    // Récupérer les informations de l'utilisateur connecté
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    // Vérifier si l'utilisateur a suffisamment de points
    if ($user['points'] < $service['points_cost']) {
        die("Vous n'avez pas suffisamment de points pour demander ce service.");
    }

    // Effectuer la transaction de points
    $conn->beginTransaction();

    // Déduire les points de l'utilisateur demandeur
    $sql = "UPDATE users SET points = points - ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$service['points_cost'], $user_id]);

    // Mettre à jour le statut du service à "requested"
    $sql = "UPDATE services SET status = 'requested' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$service_id]);

    // Ajouter une entrée dans la table service_requests
    $sql = "INSERT INTO service_requests (service_id, requester_id, status, requested_at) 
            VALUES (?, ?, 'requested', NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$service_id, $user_id]);

    $conn->commit();

    $message = "Service demandé avec succès. Le fournisseur du service sera notifié.";
} catch (PDOException $e) {
    $conn->rollBack();
    die("Erreur lors de la demande du service : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demander un service - StudentSwap</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="wrapper">
        <?php
        include '../includes/header.php';
        ?>

        <main class="main-content">
            <section class="request-confirmation">
                <h2>Demande de service</h2>
                <p><?= htmlspecialchars($message) ?></p>
                <a href="../views/index.php" class="button">Retour à l'accueil</a>
            </section>
        </main>

        <?php
        include '../includes/footer.php';
        ?>
    </div>
</body>

</html>