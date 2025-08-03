document.addEventListener("DOMContentLoaded", () => {
  fetch("../../backend/statistiques_stages.php")
    .then(res => res.json())
    .then(data => {
      if (!data.success) throw new Error(data.message);

      // Afficher les nombres globaux
      document.getElementById("stagesEnCours").textContent = data.en_cours;
      document.getElementById("stagesTermines").textContent = data.termines;

      // Fonction pour afficher nombre + pourcentage dans les tooltips
      const generateTooltipWithPercentage = (context) => {
        const dataset = context.dataset;
        const total = dataset.data.reduce((acc, val) => acc + val, 0);
        const currentValue = dataset.data[context.dataIndex];
        const percentage = ((currentValue / total) * 100).toFixed(1);
        return `${context.label}: ${currentValue} (${percentage}%)`;
      };

      // Graphique des stages en retard avec pourcentage
      new Chart(document.getElementById("retardChart"), {
        type: "doughnut",
        data: {
          labels: ["Stages en retard", "Stages à l’heure"],
          datasets: [{
            label: "Retards",
            data: [data.retard, data.a_heure],
            backgroundColor: ["#e74a3b", "#1cc88a"]
          }]
        },
        options: {
          plugins: {
            tooltip: {
              callbacks: {
                label: generateTooltipWithPercentage
              }
            }
          }
        }
      });
    })
    .catch(err => {
      alert("Erreur chargement des statistiques : " + err.message);
    });
});
