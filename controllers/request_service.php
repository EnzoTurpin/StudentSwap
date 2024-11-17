<?php
include '../config/db.php';
session_start();

// Vérification de la connexion utilisateur et de l'ID du service
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: ../views/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$service_id = $_GET['id'];

try {
    // Récupération des informations du service et du fournisseur
    $sql = "SELECT services.*, users.username AS provider_username, users.id AS provider_id, users.points AS provider_points 
            FROM services 
            JOIN users ON services.user_id = users.id 
            WHERE services.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$service_id]);
    $service = $stmt->fetch();

    // Vérification de l'existence du service
    if (!$service) {
        $_SESSION['error_message'] = "Le service n'existe pas.";
        header("Location: ../views/index.php");
        exit;
    }

    // Empêcher l'utilisateur de demander son propre service
    if ($service['provider_id'] == $user_id) {
        $_SESSION['error_message'] = "Vous ne pouvez pas demander votre propre service.";
        header("Location: ../views/index.php");
        exit;
    }

    // Vérifier si le service est disponible
    if ($service['status'] != 'available') {
        $_SESSION['error_message'] = "Ce service a déjà été demandé ou n'est plus disponible.";
        header("Location: ../views/index.php");
        exit;
    }

    // Récupération des informations de l'utilisateur connecté
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    // Vérifier si l'utilisateur a suffisamment de points pour demander le service
    if ($user['points'] < $service['points_cost']) {
        $_SESSION['error_message'] = "Vous n'avez pas suffisamment de points pour demander ce service.";
        header("Location: ../views/index.php");
        exit;
    }

    // Début de la transaction pour la demande de service
    $conn->beginTransaction();

    // Déduire les points de l'utilisateur
    $sql = "UPDATE users SET points = points - ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$service['points_cost'], $user_id]);

    // Mettre à jour le statut du service à "requested"
    $sql = "UPDATE services SET status = 'requested' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$service_id]);

    // Ajouter une entrée dans la table des demandes de service
    $sql = "INSERT INTO service_requests (service_id, requester_id, status, requested_at) 
            VALUES (?, ?, 'requested', NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$service_id, $user_id]);

    // Valider la transaction
    $conn->commit();

    // Définir un message de succès
    $_SESSION['success_message'] = "Service demandé avec succès. Le fournisseur du service sera notifié.";
    header("Location: ../views/index.php");
    exit;

} catch (PDOException $e) {
    // Annulation de la transaction en cas d'erreur
    $conn->rollBack();
    $_SESSION['error_message'] = "Erreur lors de la demande du service : " . $e->getMessage();
    header("Location: ../views/index.php");
    exit;
}
?>