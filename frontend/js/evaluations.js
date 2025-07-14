document.addEventListener('DOMContentLoaded', () => {
  const tableBody = document.querySelector("#evaluationsTable tbody");
  const searchInput = document.getElementById("searchInput");
  const noteFilter = document.getElementById("noteFilter");

  const form = document.getElementById("evaluationForm");
  const stagiaireIdField = document.getElementById("stagiaireId");

  function loadEvaluations() {
    fetch('../../backend/evaluations.php')  // ✅ fetch corrigé
      .then(res => res.json())
      .then(data => {
        let filtered = data;

        // Filtrage par nom
        const search = searchInput.value.toLowerCase();
        if (search) {
          filtered = filtered.filter(e =>
            e.nom.toLowerCase().includes(search)
          );
        }

        // Tri par note
        if (noteFilter.value === "asc") {
          filtered.sort((a, b) => a.note - b.note);
        } else if (noteFilter.value === "desc") {
          filtered.sort((a, b) => b.note - a.note);
        }

        // Remplir le tableau
        tableBody.innerHTML = "";
        filtered.forEach(evaluation => {
          tableBody.innerHTML += `
            <tr>
              <td>${evaluation.nom}</td>
              <td>${evaluation.note}</td>
              <td>${evaluation.commentaires}</td>
              <td>
                <button class="btn btn-sm btn-warning me-1" onclick="editEvaluation(${evaluation.stagiaire_id}, ${evaluation.note}, '${evaluation.commentaires}', '${evaluation.ameliorations}')">
                  Modifier
                </button>
              </td>
            </tr>
          `;
        });
      });
  }

  // Fonction pour modifier une évaluation
  window.editEvaluation = (id, note, commentaire, ameliorations) => {
    stagiaireIdField.value = id;
    document.getElementById("note").value = note;
    document.getElementById("commentaire").value = commentaire;
    document.getElementById("ameliorations").value = ameliorations;
  };

  // Soumission du formulaire
  form.addEventListener('submit', (e) => {
    e.preventDefault();

    const formData = new FormData(form);
    fetch('../../backend/evaluations.php', {
      method: 'POST',
      body: formData
    })
    .then(() => {
      form.reset();
      loadEvaluations();
    });
  });

  searchInput.addEventListener('input', loadEvaluations);
  noteFilter.addEventListener('change', loadEvaluations);

  loadEvaluations();
});
