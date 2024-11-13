<?php
// Détruire la session et effacer toutes les données
session_start();
session_destroy();
header("Location: ../views/login.php");
?>