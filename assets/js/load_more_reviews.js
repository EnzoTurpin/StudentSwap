document.addEventListener("DOMContentLoaded", () => {
  const loadMoreButton = document.getElementById("load-more-btn");
  const reviewList = document.querySelector(".review-list");
  let offset = 3; // Nombre initial d'avis affichés
  const userId = loadMoreButton.dataset.userId; // ID de l'utilisateur récupéré via un attribut

  loadMoreButton.addEventListener("click", () => {
    fetch(`../public/load_more_reviews.php?user_id=${userId}&offset=${offset}`)
      .then((response) => response.text())
      .then((data) => {
        if (data.trim() === "NO_MORE_REVIEWS") {
          loadMoreButton.innerText = "Aucun autre avis";
          loadMoreButton.disabled = true; // Désactiver le bouton
        } else {
          reviewList.insertAdjacentHTML("beforeend", data); // Ajouter les nouveaux avis
          offset += 5; // Incrémenter l'offset
        }
      })
      .catch((error) => {
        console.error("Erreur lors de la récupération des avis :", error);
      });
  });
});
