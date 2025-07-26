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
          ? `<a href="../../backend/uploads/${rec.document_stage}" target="_blank">Voir</a>` 
          : 'Aucun';

        const note = (rec.note !== undefined && rec.note !== null) ? rec.note : '-';
        const commentaires = rec.commentaires ? rec.commentaires : '-';

        const nomTuteur = (rec.prenom_tuteur || rec.nom_tuteur)
          ? `${rec.prenom_tuteur || ''} ${rec.nom_tuteur || ''}`.trim()
          : '-';

        const nomStagiaire = `${rec.nom || ''} ${rec.prenom || ''}`.trim() || '-';

        const row = `
          <tr>
            <td>${nomStagiaire}</td>                  <!-- Nom du stagiaire -->
            <td>${nomTuteur}</td>                     <!-- Tuteur -->
            <td>${rec.date_affectation || '-'}</td>  <!-- Date affectation -->
            <td>${rec.date_debut || '-'}</td>        <!-- Date début -->
            <td>${rec.date_fin || '-'}</td>          <!-- Date fin -->
            <td>${rec.service_nom || '-'}</td>       <!-- Service -->
            <td>${rec.type_stage || '-'}</td>        <!-- Type de stage -->
            <td>${rec.nom_stage || '-'}</td>         <!-- Nom du stage -->
            <td>${note}</td>                          <!-- Note -->
            <td>${commentaires}</td>                  <!-- Commentaires -->
            <td>${documents}</td>                     <!-- Documents -->
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
