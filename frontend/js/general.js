fetch("../../../backend/stats_general.php")
  .then((res) => res.json())
  .then((data) => {
    // Affichage cartes
    document.getElementById("cards").innerHTML = `
      <div>Total stagiaires : ${data.total}</div>
      <div>Actifs : ${data.actifs}</div>
    `;

    // Sexe
    new Chart(document.getElementById("sexeChart"), {
      type: "pie",
      data: {
        labels: data.sexes.map((e) => e.sexe),
        datasets: [
          {
            label: "Répartition par sexe",
            data: data.sexes.map((e) => e.total),
            backgroundColor: ["#007bff", "#ff6384"],
          },
        ],
      },
    });

    // Type
    new Chart(document.getElementById("typeChart"), {
      type: "bar",
      data: {
        labels: data.types.map((e) => e.type_stage),
        datasets: [
          {
            label: "Par type de stage",
            data: data.types.map((e) => e.total),
            backgroundColor: ["#28a745", "#ffc107", "#17a2b8"],
          },
        ],
      },
    });

    // Âges
    new Chart(document.getElementById("ageChart"), {
      type: "bar",
      data: {
        labels: Object.keys(data.ages),
        datasets: [
          {
            label: "Répartition par âge",
            data: Object.values(data.ages),
            backgroundColor: ["#6f42c1", "#20c997", "#fd7e14"],
          },
        ],
      },
    });
  });
