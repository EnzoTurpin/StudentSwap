document.addEventListener("DOMContentLoaded", () => {
  // Bouton pour charger plus d'avis et conteneur des avis
  const loadMoreButton = document.getElementById("load-more-btn");
  const reviewList = document.querySelector(".review-list");

  // Initialisation du décalage pour la pagination et récupération de l'ID utilisateur
  let offset = 3;
  const userId = loadMoreButton.dataset.userId;

  // Action au clic sur le bouton
  loadMoreButton.addEventListener("click", () => {
    // Requête pour récupérer les avis supplémentaires
    fetch(`../public/load_more_reviews.php?user_id=${userId}&offset=${offset}`)
      .then((response) => response.text()) // Traiter la réponse comme du texte
      .then((data) => {
        if (data.trim() === "NO_MORE_REVIEWS") {
          // Si plus d'avis, désactiver le bouton
          loadMoreButton.innerText = "Aucun autre avis";
          loadMoreButton.disabled = true;
        } else {
          // Ajouter les nouveaux avis et mettre à jour l'offset
          reviewList.insertAdjacentHTML("beforeend", data);
          offset += 5;
        }
      })
      .catch((error) => {
        // Gérer les erreurs éventuelles
        console.error("Erreur lors de la récupération des avis :", error);
      });
  });
});
