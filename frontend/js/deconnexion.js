document.getElementById("logoutForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const confirmation = confirm("Voulez-vous vraiment vous déconnecter ?");

  if (confirmation) {
    // Si vous avez une session à détruire, appelez une API ici

    // Redirection vers la page de login
    window.location.href = "login.html";
  }
});
