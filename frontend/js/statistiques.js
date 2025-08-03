document.addEventListener("DOMContentLoaded", () => {
  fetch("../../backend/statistiques.php")
    .then(res => res.json())
    .then(data => {
      if (!data.success) throw new Error(data.message);

      // Compteurs
      document.getElementById("totalStagiaires").textContent = data.total;
      document.getElementById("actifsStagiaires").textContent = data.actifs;

      // Fonction pour afficher nombre + pourcentage dans les tooltips
      const generateTooltipWithPercentage = (context) => {
        const dataset = context.dataset;
        const total = dataset.data.reduce((a, b) => a + b, 0);
        const currentValue = dataset.data[context.dataIndex];
        const percentage = ((currentValue / total) * 100).toFixed(1);
        return `${context.label}: ${currentValue} (${percentage}%)`;
      };

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

      // Graphique par type de stage (avec pourcentage dans tooltip)
      new Chart(document.getElementById("typeStageChart"), {
        type: "pie",
        data: {
          labels: data.par_type.labels,
          datasets: [{
            label: "Type de stage",
            data: data.par_type.values,
            backgroundColor: ["#36b9cc", "#f6c23e", "#e74a3b", "#1cc88a"]
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

      // Graphique par sexe (avec pourcentage dans tooltip)
      new Chart(document.getElementById("sexeChart"), {
        type: "doughnut",
        data: {
          labels: data.par_sexe.labels,
          datasets: [{
            label: "Répartition par sexe",
            data: data.par_sexe.values,
            backgroundColor: ["#f6c23e", "#36b9cc"]
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
      alert("Erreur chargement statistiques : " + err.message);
    });
});
