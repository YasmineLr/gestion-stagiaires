document.addEventListener("DOMContentLoaded", () => {
  fetch("../../backend/statistiques_duree.php")
    .then(res => res.json())
    .then(data => {
      if (!data.success) throw new Error(data.message);

      // Affiche la durée moyenne
      document.getElementById("dureeMoyenne").textContent = data.duree_moyenne + " semaines";

      // Graphique de répartition
      new Chart(document.getElementById("dureeChart"), {
        type: "bar",
        data: {
          labels: data.par_duree.labels,
          datasets: [{
            label: "Nombre de stagiaires",
            data: data.par_duree.values,
            backgroundColor: "#4e73df"
          }]
        },
        options: {
          scales: {
            y: {
              beginAtZero: true,
              precision: 0
            }
          }
        }
      });
    })
    .catch(err => {
      alert("Erreur chargement des statistiques : " + err.message);
    });
});
