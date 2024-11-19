<!DOCTYPE html>
<html lang="fr">

<head>
    <!-- Définition des métadonnées principales -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès refusé</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="wrapper">
        <!-- Inclusion de l'en-tête -->
        <?php include '../includes/header.php'; ?>

        <div class="form-container">

            <!-- Message d'accès refusé -->
            <h2>Accès refusé</h2>
            <p>Vous n'avez pas les droits suffisants pour voir ce profil.</p>

            <!-- Bouton de retour à la page d'accueil -->
            <div class="button-container">
                <a href="../views/index.php" class="button back-button">Retour à l'accueil</a>
            </div>
        </div>

        <!-- Inclusion du pied de page -->
        <?php include '../includes/footer.php'; ?>
    </div>
</body>

</html>