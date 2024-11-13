<?php
// Paramètres de connexion
$host = 'localhost';
$dbname = 'studentswap';
$username = 'root';
$password = '';

try {
    // Connexion à la base de données avec PDO
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Gestion des erreurs
    die("Erreur de connexion : " . $e->getMessage());
}
?>