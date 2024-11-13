<?php
session_start();
include '../config/db.php';

// Vérification de la connexion utilisateur
if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Vérification de l'ID du service passé via le formulaire
if (!isset($_POST['service_id'])) {
    die("Aucun service spécifié.");
}

$service_id = $_POST['service_id'];

try {
    // Vérification que le service appartient à l'utilisateur connecté
    $sql = "SELECT * FROM services WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$service_id, $user_id]);
    $service = $stmt->fetch();

    // Si le service n'est pas trouvé ou ne correspond pas à l'utilisateur
    if (!$service) {
        die("Service introuvable ou vous n'êtes pas autorisé à le supprimer.");
    }

    // Suppression des demandes associées à ce service
    $sql = "DELETE FROM service_requests WHERE service_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$service_id]);

    // Suppression du service
    $sql = "DELETE FROM services WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$service_id]);

    // Redirection avec message de confirmation
    $_SESSION['message'] = "Le service a été supprimé avec succès.";
    header("Location: ../views/profile.php");
    exit;
} catch (PDOException $e) {
    // Gestion des erreurs et redirection avec message d'erreur
    $_SESSION['message'] = "Erreur lors de la suppression du service : " . $e->getMessage();
    header("Location: ../views/profile.php");
    exit;
}
?>