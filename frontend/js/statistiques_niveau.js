document.addEventListener("DOMContentLoaded", () => {
  fetch("../../backend/statistiques_niveau.php")
    .then(res => res.json())
    .then(data => {
      if (!data.success) throw new Error(data.message);

      // Afficher le nombre de stagiaires diplômés
      document.getElementById("nombreDiplomes").textContent = data.nombre_diplomes;

      // Afficher le graphique de répartition par niveau d’études
      new Chart(document.getElementById("niveauChart"), {
        type: "doughnut",
        data: {
          labels: data.par_niveau.labels,
          datasets: [{
            label: "Répartition par niveau d'étude",
            data: data.par_niveau.values,
            backgroundColor: [
              "#4e73df",
              "#1cc88a",
              "#36b9cc",
              "#f6c23e",
              "#e74a3b"
            ]
          }]
        }
      });
    })
    .catch(err => {
      alert("Erreur chargement des statistiques : " + err.message);
    });
});
