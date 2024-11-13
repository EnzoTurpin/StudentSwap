<?php
session_start();
include '../config/db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Vérifier si l'ID du service est passé via le formulaire
if (!isset($_POST['service_id'])) {
    die("Aucun service spécifié.");
}

$service_id = $_POST['service_id'];

try {
    // Vérifier que le service appartient à l'utilisateur connecté
    $sql = "SELECT * FROM services WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$service_id, $user_id]);
    $service = $stmt->fetch();

    if (!$service) {
        die("Service introuvable ou vous n'êtes pas autorisé à le supprimer.");
    }

    // Supprimer les demandes liées au service
    $sql = "DELETE FROM service_requests WHERE service_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$service_id]);

    // Supprimer le service
    $sql = "DELETE FROM services WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$service_id]);

    // Rediriger l'utilisateur avec un message de succès
    $_SESSION['message'] = "Le service a été supprimé avec succès.";
    header("Location: ../views/profile.php");
    exit;
} catch (PDOException $e) {
    $_SESSION['message'] = "Erreur lors de la suppression du service : " . $e->getMessage();
    header("Location: ../views/profile.php");
    exit;
}
?>