const tableBody = document.querySelector("#historiqueTable tbody");

async function chargerHistorique() {
  try {
    const res = await fetch("../../backend/historique.php?action=list");
    const data = await res.json();

    if (data.success) {
      tableBody.innerHTML = "";

      if (data.records.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="11" class="text-center">Aucun stagiaire terminé trouvé.</td></tr>`;
        return;
      }

      data.records.forEach(rec => {
        const documents = rec.document_stage
          ? `<a href="../../backend/uploads/${rec.document_stage}" target="_blank">Voir</a>` : 'Aucun';

        const note = rec.note !== undefined && rec.note !== null ? rec.note : '-';
        const commentaires = rec.commentaires ? rec.commentaires : '-';

        const nomTuteur = rec.nom_tuteur || rec.prenom_tuteur
          ? `${rec.prenom_tuteur || ''} ${rec.nom_tuteur || ''}`.trim()
          : '-';

        const row = `
          <tr>
            <td>${rec.nom} ${rec.prenom || ''}</td>
            <td>${rec.date_debut || '-'}</td>
            <td>${rec.date_fin || '-'}</td>
            <td>${rec.service_nom || '-'}</td>
            <td>${rec.type_stage || '-'}</td>
            <td>${rec.nom_stage || '-'}</td>
            <td>${note}</td>
            <td>${commentaires}</td>
            <td>${documents}</td>
            <td>${nomTuteur}</td>
            <td>${rec.date_affectation || '-'}</td>
          </tr>
        `;
        tableBody.innerHTML += row;
      });
    } else {
      tableBody.innerHTML = `<tr><td colspan="11" class="text-center text-danger">Erreur: ${data.message}</td></tr>`;
    }
  } catch (err) {
    console.error("Erreur chargement historique:", err);
    tableBody.innerHTML = `<tr><td colspan="11" class="text-center text-danger">Erreur serveur.</td></tr>`;
  }
}

chargerHistorique();
