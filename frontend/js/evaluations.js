document.addEventListener('DOMContentLoaded', () => {
  const tableBody = document.querySelector("#evaluationsTable tbody");
  const searchInput = document.getElementById("searchInput");
  const noteFilter = document.getElementById("noteFilter");
  const serviceFilter = document.getElementById("serviceFilter");
  const anneeFilter = document.getElementById("anneeFilter");

  const formModal = document.getElementById("formModal");
  const form = document.getElementById("evaluationForm");
  const stagiaireIdField = document.getElementById("stagiaireId");
  const cancelBtn = document.getElementById("cancelBtn");
  const formError = document.getElementById("formError");
  const modalTitle = document.getElementById("modalTitle");

  let allEvaluations = [];

  function loadEvaluations() {
    fetch('../../backend/evaluations.php')
      .then(res => res.json())
      .then(data => {
        if (!Array.isArray(data)) {
          tableBody.innerHTML = '<tr><td colspan="5" class="text-danger text-center">Erreur de données reçues</td></tr>';
          return;
        }

        allEvaluations = data;
        filterAndDisplay();
        remplirFiltres(data);
      })
      .catch(() => {
        tableBody.innerHTML = '<tr><td colspan="5" class="text-danger text-center">Erreur de chargement</td></tr>';
      });
  }

  function remplirFiltres(data) {
    const services = [...new Set(data.map(e => e.service_nom).filter(Boolean))];
    const annees = [...new Set(data.map(e => e.date_evaluation?.substring(0, 4)).filter(Boolean))];

    serviceFilter.innerHTML = '<option value="">Filtrer par service</option>';
    services.forEach(service => {
      serviceFilter.innerHTML += `<option value="${service}">${service}</option>`;
    });

    anneeFilter.innerHTML = '<option value="">Filtrer par année</option>';
    annees.forEach(annee => {
      anneeFilter.innerHTML += `<option value="${annee}">${annee}</option>`;
    });
  }

  function filterAndDisplay() {
    let filtered = [...allEvaluations];

    // Recherche par nom
    const search = searchInput.value.trim().toLowerCase();
    if (search) {
      filtered = filtered.filter(e =>
        e.nom.toLowerCase().includes(search)
      );
    }

    // Tri par note
    if (noteFilter.value === "asc") {
      filtered.sort((a, b) => (a.note ?? 0) - (b.note ?? 0));
    } else if (noteFilter.value === "desc") {
      filtered.sort((a, b) => (b.note ?? 0) - (a.note ?? 0));
    }

    // Filtrer par service
    const selectedService = serviceFilter.value;
    if (selectedService) {
      filtered = filtered.filter(e => e.service_nom === selectedService);
    }

    // Filtrer par année
    const selectedAnnee = anneeFilter.value;
    if (selectedAnnee) {
      filtered = filtered.filter(e => e.date_evaluation?.startsWith(selectedAnnee));
    }

    // Affichage
    tableBody.innerHTML = "";
    if (filtered.length === 0) {
      tableBody.innerHTML = '<tr><td colspan="5" class="text-center">Aucune évaluation trouvée</td></tr>';
      return;
    }

    filtered.forEach(evaluation => {
      const commentairesEscaped = (evaluation.commentaires || '').replace(/'/g, "\\'");
      const ameliorationsEscaped = (evaluation.ameliorations || '').replace(/'/g, "\\'");
      const noteAffichee = (evaluation.note !== null && evaluation.note !== "") ? evaluation.note : "Non noté";
      const commentaireAffiche = (evaluation.commentaires !== null && evaluation.commentaires !== "") ? evaluation.commentaires : "-";
      const service = evaluation.service_nom || "-";

      tableBody.innerHTML += `
        <tr>
          <td>${evaluation.nom}</td>
          <td>${noteAffichee}</td>
          <td>${commentaireAffiche}</td>
          <td>${service}</td>
          <td>
            <button class="btn btn-sm btn-warning me-1" 
              onclick="editEvaluation(${evaluation.stagiaire_id}, ${evaluation.note || 0}, '${commentairesEscaped}', '${ameliorationsEscaped}')">
              Modifier
            </button>
          </td>
        </tr>
      `;
    });
  }

  // Ouverture modale pour modifier
  window.editEvaluation = (id, note, commentaire, ameliorations) => {
    modalTitle.textContent = "Modifier une évaluation";
    formError.textContent = '';
    stagiaireIdField.value = id;
    document.getElementById("note").value = note;
    document.getElementById("commentaire").value = commentaire || '';
    document.getElementById("ameliorations").value = ameliorations || '';
    formModal.style.display = 'flex';
  };

  // Fermer la modale
  cancelBtn.addEventListener("click", () => {
    formModal.style.display = 'none';
    form.reset();
    formError.textContent = '';
  });

  // Soumettre le formulaire
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const formData = new FormData(form);
    fetch('../../backend/evaluations.php', {
      method: 'POST',
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
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

  // Filtres actifs
  searchInput.addEventListener('input', filterAndDisplay);
  noteFilter.addEventListener('change', filterAndDisplay);
  serviceFilter.addEventListener('change', filterAndDisplay);
  anneeFilter.addEventListener('change', filterAndDisplay);

  // Chargement initial
  loadEvaluations();
});
