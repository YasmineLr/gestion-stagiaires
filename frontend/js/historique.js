const tableBody = document.querySelector("#historiqueTable tbody");
const searchInput = document.getElementById("searchNom");
const filterService = document.getElementById("filterService");
const dateDebutFilter = document.getElementById("dateDebutFilter");
const dateFinFilter = document.getElementById("dateFinFilter");

let allRecords = [];

async function chargerHistorique() {
  try {
    const res = await fetch("../../backend/historique.php?action=list");
    const data = await res.json();

    if (data.success) {
      allRecords = data.records;
      remplirFiltreServices(allRecords);
      afficherFiltres();
    } else {
      tableBody.innerHTML = `<tr><td colspan="11" class="text-center text-danger">Erreur: ${data.message}</td></tr>`;
    }
  } catch (err) {
    console.error("Erreur chargement historique:", err);
    tableBody.innerHTML = `<tr><td colspan="11" class="text-center text-danger">Erreur serveur.</td></tr>`;
  }
}

function remplirFiltreServices(data) {
  const services = [...new Set(data.map(r => r.service_nom).filter(Boolean))];
  filterService.innerHTML = '<option value="">Tous les services</option>';
  services.forEach(service => {
    filterService.innerHTML += `<option value="${service}">${service}</option>`;
  });
}

function afficherFiltres() {
  const search = searchInput.value.trim().toLowerCase();
  const selectedService = filterService.value;
  const dateDebut = dateDebutFilter.value;
  const dateFin = dateFinFilter.value;

  const filtrés = allRecords.filter(rec => {
    const nom = `${rec.nom || ''} ${rec.prenom || ''}`.toLowerCase();
    const matchNom = nom.includes(search);
    const matchService = selectedService === "" || rec.service_nom === selectedService;
    const matchDateDebut = !dateDebut || (rec.date_debut && rec.date_debut >= dateDebut);
    const matchDateFin = !dateFin || (rec.date_fin && rec.date_fin <= dateFin);
    return matchNom && matchService && matchDateDebut && matchDateFin;
  });

  tableBody.innerHTML = "";

  if (filtrés.length === 0) {
    tableBody.innerHTML = `<tr><td colspan="11" class="text-center">Aucun résultat trouvé.</td></tr>`;
    return;
  }

  filtrés.forEach(rec => {
    const documents = rec.document_stage
      ? `<a href="../../backend/uploads/${rec.document_stage}" target="_blank">Voir</a>`
      : 'Aucun';

    const note = rec.note !== undefined && rec.note !== null ? rec.note : 'Non noté';
    const commentaires = rec.commentaires ? rec.commentaires : '-';

    const nomTuteur = (rec.prenom_tuteur || rec.nom_tuteur)
      ? `${rec.prenom_tuteur || ''} ${rec.nom_tuteur || ''}`.trim()
      : '-';

    const nomStagiaire = `${rec.nom || ''} ${rec.prenom || ''}`.trim() || '-';

    const row = `
    <tr>
    <td>${nomStagiaire}</td>
    <td>${nomTuteur}</td>
    <td>${rec.date_debut || '-'}</td>
    <td>${rec.date_fin || '-'}</td>
    <td>${rec.service_nom || '-'}</td>
    <td>${rec.type_stage || '-'}</td>
    <td>${rec.nom_stage || '-'}</td>
    <td>${note}</td>
    <td>${commentaires}</td>
    <td>${documents}</td>
  </tr>
    `;
    tableBody.innerHTML += row;
  });
}

// Écouteurs sur les champs de filtre
searchInput.addEventListener("input", afficherFiltres);
filterService.addEventListener("change", afficherFiltres);
dateDebutFilter.addEventListener("change", afficherFiltres);
dateFinFilter.addEventListener("change", afficherFiltres);

// Lancer le chargement initial
chargerHistorique();
