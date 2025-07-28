document.addEventListener("DOMContentLoaded", () => {
  fetch("../../backend/statistiques_stages.php")
    .then(res => res.json())
    .then(data => {
      if (!data.success) throw new Error(data.message);

      document.getElementById("stagesEnCours").textContent = data.en_cours;
      document.getElementById("stagesTermines").textContent = data.termines;

      new Chart(document.getElementById("retardChart"), {
        type: "doughnut",
        data: {
          labels: ["Stages en retard", "Stages à l’heure"],
          datasets: [{
            label: "Retards",
            data: [data.retard, data.a_heure],
            backgroundColor: ["#e74a3b", "#1cc88a"]
          }]
        }
      });
    })
    .catch(err => {
      alert("Erreur chargement des statistiques : " + err.message);
    });
});
