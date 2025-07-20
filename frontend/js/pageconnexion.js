document.addEventListener("DOMContentLoaded", function () {
  const role = localStorage.getItem("userRole"); // Doit être 'admin' ou 'tuteur'
  const link = document.getElementById("first-login-link");

  // Redirection lien si nécessaire (optionnel)
  if (link && role) {
    if (role === "admin") {
      link.href = "tuteurs.html";
    } else if (role === "tuteur") {
      link.href = "login_tuteur.html";
    }
  }

  // Charger header
  fetch("../pages/header.html")
    .then((res) => res.text())
    .then((data) => {
      document.getElementById("header-placeholder").innerHTML = data;
    });

  // Connexion utilisateur
  document.getElementById("loginForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const email = document.getElementById("email").value.trim(); // utilisé comme username
    const password = document.getElementById("password").value.trim();
    const role = localStorage.getItem("userRole");

    if (!role || (role !== "admin" && role !== "tuteur")) {
      document.getElementById("error").textContent =
        "Rôle invalide. Veuillez choisir entre admin ou tuteur.";
      return;
    }

    fetch("../php/auth.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `username=${encodeURIComponent(
        email
      )}&password=${encodeURIComponent(password)}&role=${encodeURIComponent(
        role
      )}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          if (role === "admin") {
            window.location.href = "admin_dashboard.php";
          } else if (role === "tuteur") {
            window.location.href = "tuteur_dashboard.php";
          }
        } else {
          document.getElementById("error").textContent =
            data.message || "Identifiants incorrects.";
        }
      })
      .catch((error) => {
        console.error("Erreur :", error);
        document.getElementById("error").textContent =
          "Erreur de communication avec le serveur.";
      });
  });

  window.addEventListener("load", () => {
    document.getElementById("loginForm").reset();
  });
});
