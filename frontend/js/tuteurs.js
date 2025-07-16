let tuteurs = [];
const rowsPerPage = 5;
let currentPage = 1;
let filteredTuteurs = [];

const tableBody = document.querySelector('#tuteursTable tbody');
const paginationDiv = document.getElementById('pagination');
const formModal = document.getElementById('formModal');
const modalTitle = document.getElementById('modalTitle');
const tuteurForm = document.getElementById('tuteurForm');
const formError = document.getElementById('formError');
const searchInput = document.getElementById('searchInput');
const serviceSelect = document.getElementById('serviceSelect'); // select

async function loadTuteurs() {
  try {
    const res = await fetch('../../backend/tuteurs.php?action=list');
    const data = await res.json();
    if (data.success) {
      tuteurs = data.tuteurs;
      filteredTuteurs = [...tuteurs];
      currentPage = 1;
      renderTable();
      renderPagination();
    } else {
      alert('Erreur lors du chargement des tuteurs');
    }
  } catch (error) {
    alert('Erreur réseau: ' + error.message);
  }
}

function renderTable() {
  tableBody.innerHTML = '';
  const start = (currentPage - 1) * rowsPerPage;
  const pageTuteurs = filteredTuteurs.slice(start, start + rowsPerPage);

  for (const tuteur of pageTuteurs) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${tuteur.nom}</td>
      <td>${tuteur.service_nom || 'Non défini'}</td>
      <td class="actions">
        <button class="btn btn-sm btn-warning me-1" onclick="openEditForm(${tuteur.id})">Modifier</button>
        <button class="btn btn-sm btn-danger" onclick="deleteTuteur(${tuteur.id})">Supprimer</button>
      </td>
    `;
    tableBody.appendChild(tr);
  }
}

function renderPagination() {
  paginationDiv.innerHTML = '';
  const pageCount = Math.ceil(filteredTuteurs.length / rowsPerPage);
  for (let i = 1; i <= pageCount; i++) {
    const btn = document.createElement('button');
    btn.textContent = i;
    btn.disabled = (i === currentPage);
    btn.addEventListener('click', () => {
      currentPage = i;
      renderTable();
      renderPagination();
    });
    paginationDiv.appendChild(btn);
  }
}

document.getElementById('addBtn').addEventListener('click', () => {
  modalTitle.textContent = 'Ajouter un tuteur';
  formError.textContent = '';
  tuteurForm.reset();
  document.getElementById('tuteurId').value = '';
  serviceSelect.value = '';
  formModal.style.display = 'flex';
});

function openEditForm(id) {
  const tuteur = tuteurs.find(t => t.id === id);
  if (!tuteur) return alert('Tuteur non trouvé');

  modalTitle.textContent = 'Modifier le tuteur';
  formError.textContent = '';
  document.getElementById('tuteurId').value = tuteur.id;
  document.getElementById('nom').value = tuteur.nom;
  serviceSelect.value = tuteur.service_id || '';
  formModal.style.display = 'flex';
}

document.getElementById('cancelBtn').addEventListener('click', () => {
  formModal.style.display = 'none';
});

tuteurForm.addEventListener('submit', async e => {
  e.preventDefault();
  formError.textContent = '';

  const id = document.getElementById('tuteurId').value;
  const nom = document.getElementById('nom').value.trim();
  const service_id = serviceSelect.value;

  if (!nom || !service_id) {
    formError.textContent = 'Tous les champs sont obligatoires.';
    return;
  }

  const action = id ? 'update' : 'add';
  const payload = { id, nom, service_id };

  try {
    const res = await fetch(`../../backend/tuteurs.php?action=${action}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.success) {
      await loadTuteurs();
      formModal.style.display = 'none';
    } else {
      formError.textContent = data.message || 'Erreur serveur';
    }
  } catch (error) {
    formError.textContent = 'Erreur réseau: ' + error.message;
  }
});

async function deleteTuteur(id) {
  if (!confirm('Voulez-vous vraiment supprimer ce tuteur ?')) return;
  try {
    const res = await fetch('../../backend/tuteurs.php?action=delete', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const data = await res.json();
    if (data.success) {
      await loadTuteurs();
    } else {
      alert(data.message || 'Erreur serveur');
    }
  } catch (error) {
    alert('Erreur réseau: ' + error.message);
  }
}

searchInput.addEventListener('input', () => {
  const term = searchInput.value.toLowerCase();
  filteredTuteurs = tuteurs.filter(t =>
    t.nom.toLowerCase().includes(term) ||
    (t.service_nom && t.service_nom.toLowerCase().includes(term))
  );
  currentPage = 1;
  renderTable();
  renderPagination();
});

// Charger les services dans le select
async function loadServices() {
  try {
    const res = await fetch('../../backend/services.php');
    const data = await res.json();
    if (data.success && Array.isArray(data.services)) {
      serviceSelect.innerHTML = '<option value="">-- Choisir un service --</option>';
      data.services.forEach(service => {
        const option = document.createElement('option');
        option.value = service.id;
        option.textContent = service.nom;
        serviceSelect.appendChild(option);
      });
    }
  } catch (error) {
    console.error('Erreur chargement services', error);
  }
}

// Chargement initial
loadServices();
loadTuteurs();
