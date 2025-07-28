document.addEventListener("DOMContentLoaded", () => {
  fetch("../../backend/statistiques.php")
    .then(res => res.json())
    .then(data => {
      if (!data.success) throw new Error(data.message);

      // Compteurs
      document.getElementById("totalStagiaires").textContent = data.total;
      document.getElementById("actifsStagiaires").textContent = data.actifs;

      // Graphique par service
      new Chart(document.getElementById("parServiceChart"), {
        type: "bar",
        data: {
          labels: data.par_service.labels,
          datasets: [{
            label: "Stagiaires par service",
            data: data.par_service.values,
            backgroundColor: "#4e73df"
          }]
        }
      });

      // Graphique par type de stage
      new Chart(document.getElementById("typeStageChart"), {
        type: "pie",
        data: {
          labels: data.par_type.labels,
          datasets: [{
            label: "Type de stage",
            data: data.par_type.values,
            backgroundColor: ["#36b9cc", "#f6c23e", "#e74a3b", "#1cc88a"]
          }]
        }
      });

      // Graphique par tranche d’âge
      new Chart(document.getElementById("parAgeChart"), {
        type: "bar",
        data: {
          labels: data.par_age.labels,
          datasets: [{
            label: "Répartition par âge",
            data: data.par_age.values,
            backgroundColor: "#1cc88a"
          }]
        }
      });

      // Graphique par sexe
      new Chart(document.getElementById("sexeChart"), {
        type: "doughnut",
        data: {
          labels: data.par_sexe.labels,
          datasets: [{
            label: "Répartition par sexe",
            data: data.par_sexe.values,
            backgroundColor: ["#f6c23e", "#36b9cc"]
          }]
        }
      });
    })
    .catch(err => {
      alert("Erreur chargement statistiques : " + err.message);
    });
});
