document.addEventListener("DOMContentLoaded", () => {
  fetch("../../backend/statistiques_niveau.php")
    .then(res => res.json())
    .then(data => {
      if (!data.success) throw new Error(data.message);

      // Afficher le nombre de stagiaires diplômés
      document.getElementById("nombreDiplomes").textContent = data.nombre_diplomes;

      // Fonction tooltip affichant nombre + pourcentage
      const tooltipWithPercentage = (context) => {
        const dataset = context.dataset;
        const total = dataset.data.reduce((acc, val) => acc + val, 0);
        const currentValue = dataset.data[context.dataIndex];
        const percentage = ((currentValue / total) * 100).toFixed(1);
        return `${context.label}: ${currentValue} (${percentage}%)`;
      };

      // Graphique niveau d'études avec tooltip pourcentage
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
        },
        options: {
          plugins: {
            tooltip: {
              callbacks: {
                label: tooltipWithPercentage
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
