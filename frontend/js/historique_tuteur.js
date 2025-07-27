document.addEventListener("DOMContentLoaded", () => {
  fetch("../../backend/get_historique.php")
    .then((res) => res.json())
    .then((data) => {
      const tbody = document.getElementById("historiqueBody");

      if (data.success && data.data.length > 0) {
        data.data.forEach((stagiaire) => {
          const row = document.createElement("tr");
          row.innerHTML = `
            <td>${stagiaire.prenom} ${stagiaire.nom}</td>
            <td>${stagiaire.date_affectation}</td>
            <td>${stagiaire.date_debut}</td>
            <td>${stagiaire.date_fin}</td>
            <td>${stagiaire.service}</td>
            <td>${stagiaire.sujet}</td>
            <td>${stagiaire.nom_stage}</td>
            <td>${stagiaire.type_stage}</td>
            <td>${stagiaire.note ?? "-"}</td>
            <td>${stagiaire.commentaire ?? "-"}</td>
            <td>${
              stagiaire.documents
                ? `<a href="${stagiaire.documents}" target="_blank">Voir</a>`
                : "-"
            }</td>
          `;
          tbody.appendChild(row);
        });
      } else {
        tbody.innerHTML = `<tr><td colspan="11" class="text-center text-danger">Aucun stagiaire terminé trouvé.</td></tr>`;
      }
    })
    .catch((err) => {
      console.error("Erreur chargement historique:", err);
    });
});
