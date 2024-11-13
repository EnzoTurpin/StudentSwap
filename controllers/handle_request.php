<?php
session_start();
include '../config/db.php';

// Vérifier si l'utilisateur est connecté et si les données nécessaires sont fournies
if (!isset($_SESSION['user_id']) || !isset($_POST['service_id']) || !isset($_POST['action'])) {
    header("Location: ../views/profile.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$service_id = $_POST['service_id'];
$action = $_POST['action'];

try {
    // Vérifier si la demande existe dans la table service_requests
    $sql = "SELECT * FROM service_requests WHERE service_id = ? AND status = 'requested'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$service_id]);
    $service_request = $stmt->fetch();

    if (!$service_request) {
        die("La demande de service n'existe pas ou a déjà été traitée.");
    }

    if ($action === 'accept') {
        // Accepter la demande et mettre à jour le statut à "accepted"
        $sql = "UPDATE service_requests SET status = 'accepted', accepted_at = NOW() WHERE service_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$service_id]);

        // Mettre à jour le statut du service dans la table services
        $sql = "UPDATE services SET status = 'accepted' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$service_id]);

        $message = "Demande acceptée avec succès.";
    } elseif ($action === 'reject') {
        // Rejeter la demande et remettre le service disponible
        $sql = "UPDATE service_requests SET status = 'rejected' WHERE service_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$service_id]);

        $sql = "UPDATE services SET status = 'available' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$service_id]);

        $message = "Demande refusée, le service est de nouveau disponible.";
    } else {
        $message = "Action non valide.";
    }
} catch (PDOException $e) {
    die("Erreur lors du traitement de la demande : " . $e->getMessage());
}

// Rediriger vers la page de profil avec un message de confirmation
header("Location: ../views/profile.php?message=" . urlencode($message));
exit;