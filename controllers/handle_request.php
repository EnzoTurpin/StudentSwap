<?php
session_start();
include '../config/db.php';

// Vérification de la connexion utilisateur et des données envoyées
if (!isset($_SESSION['user_id']) || !isset($_POST['service_id']) || !isset($_POST['action'])) {
    header("Location: ../views/profile.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$service_id = $_POST['service_id'];
$action = $_POST['action'];

try {
    // Vérification de l'existence de la demande de service
    $sql = "SELECT sr.*, s.points_cost, s.user_id AS provider_id 
            FROM service_requests sr
            JOIN services s ON sr.service_id = s.id
            WHERE sr.service_id = ? AND sr.status = 'requested'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$service_id]);
    $service_request = $stmt->fetch();

    // Si la demande n'existe pas ou a déjà été traitée
    if (!$service_request) {
        die("La demande de service n'existe pas ou a déjà été traitée.");
    }

    $points_cost = $service_request['points_cost'];
    $provider_id = $service_request['provider_id'];

    if ($action === 'accept') {
        // Accepter la demande et mettre à jour le statut à "accepted"
        $sql = "UPDATE service_requests SET status = 'accepted', accepted_at = NOW() WHERE service_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$service_id]);

        // Mettre à jour le statut du service à "accepted"
        $sql = "UPDATE services SET status = 'accepted' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$service_id]);

        // Ajouter les points au fournisseur du service
        $sql = "UPDATE users SET points = points + ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$points_cost, $provider_id]);

        $message = "Demande acceptée avec succès. Points ajoutés au fournisseur.";
    } elseif ($action === 'reject') {
        // Rejeter la demande et remettre le service à "available"
        $sql = "UPDATE service_requests SET status = 'rejected' WHERE service_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$service_id]);

        $sql = "UPDATE services SET status = 'available' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$service_id]);

        $message = "Demande refusée, le service est de nouveau disponible.";
    } else {
        // Action non reconnue
        $message = "Action non valide.";
    }
} catch (PDOException $e) {
    // Gestion des erreurs
    die("Erreur lors du traitement de la demande : " . $e->getMessage());
}

// Redirection vers la page de profil avec un message
header("Location: ../views/profile.php?message=" . urlencode($message));
exit;
?>