<?php
session_start();
include '../config/db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$service_id = $_POST['service_id'];
$request_id = $_POST['request_id'];

try {
    $conn->beginTransaction();

    // Supprimer la demande de service
    $sql = "DELETE FROM service_requests WHERE id = ? AND requester_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$request_id, $user_id]);

    // Mettre à jour le statut du service à 'available'
    $sql = "UPDATE services SET status = 'available' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$service_id]);

    $conn->commit();

    $_SESSION['message'] = "La demande a été annulée avec succès.";
    header("Location: ../views/profile.php");
    exit;
} catch (PDOException $e) {
    $conn->rollBack();
    $_SESSION['message'] = "Erreur lors de l'annulation de la demande : " . $e->getMessage();
    header("Location: ../views/profile.php");
    exit;
}
?>