document.addEventListener('DOMContentLoaded', () => {
  const tableBody = document.querySelector("#evaluationsTable tbody");
  const searchInput = document.getElementById("searchInput");
  const noteFilter = document.getElementById("noteFilter");

  const formModal = document.getElementById("formModal");
  const form = document.getElementById("evaluationForm");
  const stagiaireIdField = document.getElementById("stagiaireId");
  const cancelBtn = document.getElementById("cancelBtn");
  const formError = document.getElementById("formError");
  const modalTitle = document.getElementById("modalTitle");

  // Charger et afficher les évaluations
  function loadEvaluations() {
    fetch('../../backend/evaluations.php')
      .then(res => res.json())
      .then(data => {
        if (!Array.isArray(data)) {
          tableBody.innerHTML = '<tr><td colspan="4" class="text-danger text-center">Erreur de données reçues</td></tr>';
          return;
        }

        let filtered = data;

        // Filtrer par recherche sur nom stagiaire
        const search = searchInput.value.trim().toLowerCase();
        if (search) {
          filtered = filtered.filter(e =>
            e.nom.toLowerCase().includes(search)
          );
        }

        // Trier par note si demandé
        if (noteFilter.value === "asc") {
          filtered.sort((a, b) => (a.note ?? 0) - (b.note ?? 0));
        } else if (noteFilter.value === "desc") {
          filtered.sort((a, b) => (b.note ?? 0) - (a.note ?? 0));
        }

        // Afficher les lignes
        tableBody.innerHTML = "";
        if (filtered.length === 0) {
          tableBody.innerHTML = '<tr><td colspan="4" class="text-center">Aucune évaluation trouvée</td></tr>';
          return;
        }

        filtered.forEach(evaluation => {
          const commentairesEscaped = (evaluation.commentaires || '').replace(/'/g, "\\'");
          const ameliorationsEscaped = (evaluation.ameliorations || '').replace(/'/g, "\\'");

          const noteAffichee = (evaluation.note !== null && evaluation.note !== "") ? evaluation.note : "Non noté";
          const commentaireAffiche = (evaluation.commentaires !== null && evaluation.commentaires !== "") ? evaluation.commentaires : "-";

          tableBody.innerHTML += `
            <tr>
              <td>${evaluation.nom}</td>
              <td>${noteAffichee}</td>
              <td>${commentaireAffiche}</td>
              <td>
                <button class="btn btn-sm btn-warning me-1" 
                  onclick="editEvaluation(${evaluation.stagiaire_id}, ${evaluation.note || 0}, '${commentairesEscaped}', '${ameliorationsEscaped}')">
                  Modifier
                </button>
              </td>
            </tr>
          `;
        });
      })
      .catch(() => {
        tableBody.innerHTML = '<tr><td colspan="4" class="text-danger text-center">Erreur de chargement</td></tr>';
      });
  }

  // Ouvre la modale avec les données à modifier
  window.editEvaluation = (id, note, commentaire, ameliorations) => {
    modalTitle.textContent = "Modifier une évaluation";
    formError.textContent = '';
    stagiaireIdField.value = id;
    document.getElementById("note").value = note;
    document.getElementById("commentaire").value = commentaire || '';
    document.getElementById("ameliorations").value = ameliorations || '';
    formModal.style.display = 'flex';
  };

  // Annuler : ferme modale et reset form
  cancelBtn.addEventListener("click", () => {
    formModal.style.display = 'none';
    form.reset();
    formError.textContent = '';
  });

  // Soumission formulaire : ajouter ou modifier évaluation
  form.addEventListener('submit', (e) => {
    e.preventDefault();

    const formData = new FormData(form);
    fetch('../../backend/evaluations.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if(data.success) {
        formModal.style.display = 'none';
        form.reset();
        loadEvaluations();
      } else {
        formError.textContent = data.message || 'Erreur lors de l\'enregistrement';
      }
    })
    .catch(() => {
      formError.textContent = 'Erreur réseau';
    });
  });

  // Événements filtrage et tri
  searchInput.addEventListener('input', loadEvaluations);
  noteFilter.addEventListener('change', loadEvaluations);

  // Chargement initial
  loadEvaluations();
});
