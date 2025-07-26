document.addEventListener("DOMContentLoaded", () => {
  fetch("../../backend/get_mes_stagiaires.php")
    .then((res) => res.json())
    .then((data) => {
      const table = document.getElementById("stagiairesTable");
      if (data.success && data.stagiaires.length > 0) {
        data.stagiaires.forEach((stagiaire) => {
          const row = document.createElement("tr");
          row.innerHTML = `
            <td>${stagiaire.prenom} ${stagiaire.nom}</td>
            <td>${stagiaire.telephone}</td>
            <td>${stagiaire.adresse}</td>
            <td>${stagiaire.parcours}</td>
            <td>${stagiaire.date_affectation}</td>
            <td>${stagiaire.note ?? ""}</td>
          `;
          table.appendChild(row);
        });
      } else {
        table.innerHTML =
          '<tr><td colspan="6" class="text-center">Aucun stagiaire affecté</td></tr>';
      }
    });
});
