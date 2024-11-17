/**
 * Fonction pour valider le mot de passe.
 * Le mot de passe doit contenir :
 * - Au moins 8 caractères
 * - Au moins 1 lettre minuscule
 * - Au moins 1 lettre majuscule
 * - Au moins 1 chiffre
 * - Au moins 1 caractère spécial
 */
function validatePassword() {
  const passwordInput = document.getElementById("new_password");
  const errorDiv = document.getElementById("password-error");
  const password = passwordInput ? passwordInput.value : "";
  const pattern =
    /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

  // Vérifie si le mot de passe respecte les règles
  if (!pattern.test(password)) {
    errorDiv.textContent =
      "Le mot de passe doit contenir au moins 8 caractères, dont 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial.";
    errorDiv.style.color = "red";
    if (passwordInput) passwordInput.style.borderColor = "red";
    return false;
  }

  // Réinitialise le message d'erreur si le mot de passe est valide
  errorDiv.textContent = "";
  if (passwordInput) passwordInput.style.borderColor = "";
  return true;
}

/**
 * Fonction pour basculer l'affichage du mot de passe
 * @param {string} inputId - L'ID du champ de mot de passe
 * @param {string} showIconId - L'ID de l'icône pour montrer le mot de passe
 * @param {string} hideIconId - L'ID de l'icône pour cacher le mot de passe
 */
function togglePasswordVisibility(inputId, showIconId, hideIconId) {
  const passwordInput = document.getElementById(inputId);
  const showIcon = document.getElementById(showIconId);
  const hideIcon = document.getElementById(hideIconId);

  if (!passwordInput || !showIcon || !hideIcon) {
    console.error(
      `L'élément avec l'ID ${inputId}, ${showIconId}, ou ${hideIconId} n'existe pas.`
    );
    return;
  }

  // Bascule entre l'affichage et le masquage du mot de passe
  if (passwordInput.type === "password") {
    passwordInput.type = "text";
    showIcon.style.display = "none";
    hideIcon.style.display = "block";
  } else {
    passwordInput.type = "password";
    showIcon.style.display = "block";
    hideIcon.style.display = "none";
  }
}

/**
 * Ajoute des écouteurs d'événements pour les champs de mot de passe
 */
document.addEventListener("DOMContentLoaded", () => {
  // Gestion du champ de mot de passe principal (login et inscription)
  const toggleNewPassword = document.getElementById("toggle-new-password");
  const showNewPasswordIcon = document.getElementById("show-new-password-icon");
  const hideNewPasswordIcon = document.getElementById("hide-new-password-icon");
  const newPasswordInput =
    document.getElementById("new_password") ||
    document.getElementById("password");

  if (
    toggleNewPassword &&
    newPasswordInput &&
    showNewPasswordIcon &&
    hideNewPasswordIcon
  ) {
    toggleNewPassword.addEventListener("click", () =>
      togglePasswordVisibility(
        newPasswordInput.id,
        "show-new-password-icon",
        "hide-new-password-icon"
      )
    );
  }

  // Gestion du champ de confirmation du mot de passe (réinitialisation du mot de passe)
  const toggleConfirmPassword = document.getElementById(
    "toggle-confirm-password"
  );
  const showConfirmPasswordIcon = document.getElementById(
    "show-confirm-password-icon"
  );
  const hideConfirmPasswordIcon = document.getElementById(
    "hide-confirm-password-icon"
  );
  const confirmPasswordInput = document.getElementById("confirm_password");

  if (
    toggleConfirmPassword &&
    confirmPasswordInput &&
    showConfirmPasswordIcon &&
    hideConfirmPasswordIcon
  ) {
    toggleConfirmPassword.addEventListener("click", () =>
      togglePasswordVisibility(
        "confirm_password",
        "show-confirm-password-icon",
        "hide-confirm-password-icon"
      )
    );
  }
});
