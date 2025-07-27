document.addEventListener("DOMContentLoaded", () => {
  fetch("../../backend/get_dashboard_tuteur.php", {
    credentials: "include", // important pour la session
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        document.getElementById("total-stagiaires").textContent = data.total;
        document.getElementById("stagiaires-actifs").textContent = data.actifs;
        document.getElementById("stagiaires-termines").textContent =
          data.termines;
      } else {
        alert("Erreur : " + data.message);
      }
    })
    .catch((err) => {
      console.error(err);
    });
});
