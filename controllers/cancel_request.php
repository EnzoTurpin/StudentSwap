<?php
session_start();
include '../config/db.php';

// Vérification de la connexion utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/login.php");
    exit;
}

// Récupération des identifiants utilisateur et service
$user_id = $_SESSION['user_id'];
$service_id = $_POST['service_id'];
$request_id = $_POST['request_id'];

try {
    // Démarrer une transaction
    $conn->beginTransaction();

    // Suppression de la demande de service
    $sql = "DELETE FROM service_requests WHERE id = ? AND requester_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$request_id, $user_id]);

    // Mise à jour du statut du service à 'available'
    $sql = "UPDATE services SET status = 'available' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$service_id]);

    // Valider la transaction
    $conn->commit();

    // Redirection avec message de succès
    $_SESSION['message'] = "La demande a été annulée avec succès.";
    header("Location: ../views/profile.php");
    exit;
} catch (PDOException $e) {
    // Annuler la transaction en cas d'erreur
    $conn->rollBack();
    $_SESSION['message'] = "Erreur lors de l'annulation de la demande : " . $e->getMessage();
    header("Location: ../views/profile.php");
    exit;
}
?>