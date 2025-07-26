document.addEventListener("DOMContentLoaded", function () {
  const role = localStorage.getItem("userRole");

  document.getElementById("loginForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value.trim();

    if (!role || (role !== "admin" && role !== "tuteur")) {
      document.getElementById("error").textContent =
        "Rôle invalide. Veuillez choisir entre admin ou tuteur.";
      return;
    }

    fetch("../../backend/checklogin.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(
        password
      )}&role=${encodeURIComponent(role)}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Redirection selon le rôle
          if (role === "admin") {
            window.location.href = "tuteurs.html";
          } else if (role === "tuteur") {
            window.location.href = "tuteur.html";
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
