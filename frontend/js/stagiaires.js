console.log("JS chargé");

const tableBody = document.querySelector("#stagiairesTable tbody");
const form = document.getElementById("stagiaireForm");
const formError = document.getElementById("formError");

const searchInput = document.getElementById('searchInput');
const filterService = document.getElementById('filterService');
const filterStatut = document.getElementById('filterStatut');

const serviceSelect = document.getElementById('serviceSelect'); // le select dans le formulaire

let services = []; // stocker la liste des services {id, nom}

// Charger les services depuis backend/services.php (à créer)
// et remplir le select et le filtre
async function chargerServices() {
  try {
    const res = await fetch("../../backend/services.php");
    const data = await res.json();
    if (data.success && Array.isArray(data.services)) {
      services = data.services;

      // Remplir select formulaire
      serviceSelect.innerHTML = '<option value="">-- Choisir un service --</option>';
      services.forEach(s => {
        const option = document.createElement('option');
        option.value = s.id;
        option.textContent = s.nom;
        serviceSelect.appendChild(option);
      });

      // Remplir filtre service
      filterService.innerHTML = '<option value="">Filtrer par service</option>';
      services.forEach(s => {
        const option = document.createElement('option');
        option.value = s.id;
        option.textContent = s.nom;
        filterService.appendChild(option);
      });
    }
  } catch (err) {
    console.error("Erreur chargement services :", err);
  }
}

// Charger stagiaires
async function chargerStagiaires() {
  try {
    const res = await fetch("../../backend/stagiaires.php?action=list");
    const data = await res.json();
    if (data.success) {
      tableBody.innerHTML = "";

      data.stagiaires.forEach(s => {
        // Trouver nom service par id
        const service = services.find(ser => ser.id == s.service_id);
        const nomService = service ? service.nom : "Non défini";

        const stagiaireData = JSON.stringify(s).replace(/'/g, "&apos;");

        const row = `
          <tr>
            <td>${s.nom || ''}</td>
            <td>${nomService}</td>
            <td>${s.statut || ''}</td>
            <td>${s.adresse || ''}</td>
            <td>${s.telephone || ''}</td>
            <td>${s.parcours || ''}</td>
            <td>${s.document ? `<a href="../../backend/uploads/${s.document}" target="_blank">Voir</a>` : 'Aucun'}</td>
            <td>
              <button class="btn btn-sm btn-warning me-1" data-stagiaire='${stagiaireData}' onclick="onClickModifier(this)">Modifier</button>
              <button class="btn btn-sm btn-danger me-1" onclick='supprimerStagiaire(${s.id})'>Supprimer</button>
              <button class="btn btn-sm btn-success" onclick='terminerStagiaire(${s.id}, "${s.statut}")'>Terminer</button>
            </td>
          </tr>
        `;
        tableBody.innerHTML += row;
      });

      filtrerStagiaires();
    }
  } catch (err) {
    console.error("Erreur de chargement :", err);
  }
}

function filtrerStagiaires() {
  const recherche = searchInput.value.toLowerCase();
  const serviceChoisi = filterService.value;
  const statutChoisi = filterStatut.value.toLowerCase();

  Array.from(tableBody.rows).forEach(row => {
    const nom = row.cells[0].textContent.toLowerCase();
    // On compare service_id via le texte visible du nom de service
    const serviceNom = row.cells[1].textContent;
    const statut = row.cells[2].textContent.toLowerCase();

    const matchRecherche = nom.includes(recherche);
    const matchService = serviceChoisi === '' || (services.find(s => s.nom === serviceNom)?.id == serviceChoisi);
    const matchStatut = statutChoisi === '' || statut === statutChoisi;

    row.style.display = (matchRecherche && matchService && matchStatut) ? '' : 'none';
  });
}

// Gérer formulaire submit
form.addEventListener("submit", async function(e) {
  e.preventDefault();

  // Vérifie tous les champs requis
  if (!form.checkValidity()) {
    form.classList.add("was-validated");
    return; // Stoppe ici : on n'envoie pas le formulaire si validation échoue
  }

  // Si tout est bon, on continue avec l'envoi AJAX
  const formData = new FormData(form);
  if (!formData.get("id")) {
    formData.delete("id");
  }

  try {
    const res = await fetch("../../backend/stagiaires.php?action=add", {
      method: "POST",
      body: formData
    });

    const text = await res.text();
    const result = JSON.parse(text);

    if (result.success) {
      form.reset();
      form.classList.remove("was-validated");

      const modal = bootstrap.Modal.getInstance(document.getElementById('modalAjouterStagiaire'));
      if (modal) modal.hide();

      await chargerStagiaires();
    } else {
      formError.textContent = result.message || "Erreur lors de l'enregistrement.";
    }
  } catch (err) {
    console.error("Erreur JSON :", err);
    formError.textContent = "Erreur lors de l'enregistrement.";
  }
});


// Modifier
function onClickModifier(button) {
  const stagiaire = JSON.parse(button.getAttribute('data-stagiaire'));
  modifierStagiaire(stagiaire);

  // Ouvrir la modale à la modification aussi
  const modal = new bootstrap.Modal(document.getElementById('modalAjouterStagiaire'));
  modal.show();
}

function modifierStagiaire(stagiaire) {
  document.getElementById("id").value = stagiaire.id;
  document.getElementById("nom").value = stagiaire.nom;
  // mettre service_id dans select
  document.getElementById("serviceSelect").value = stagiaire.service_id || '';
  document.getElementById("statut").value = stagiaire.statut;
  document.getElementById("telephone").value = stagiaire.telephone;
  document.getElementById("adresse").value = stagiaire.adresse;
  document.getElementById("parcours").value = stagiaire.parcours;
}

// Supprimer
async function supprimerStagiaire(id) {
  if (!confirm("Confirmer la suppression ?")) return;

  const res = await fetch("../../backend/stagiaires.php?action=delete", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ id })
  });

  const result = await res.json();
  if (result.success) {
    await chargerStagiaires();
  }
}

// Terminer
window.terminerStagiaire = async function(id, statut) {
  if (statut.trim().toLowerCase() === "actif") {
    if (!confirm("Voulez-vous terminer ce stagiaire ?")) return;

    try {
      const res = await fetch("../../backend/stagiaires.php?action=terminer", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id })
      });

      const result = await res.json();
      if (result.success) {
        await chargerStagiaires();
      } else {
        alert("Erreur : " + result.message);
      }
    } catch (err) {
      console.error("Erreur :", err);
      alert("Erreur serveur");
    }
  } else {
    alert("Ce stagiaire est déjà terminé.");
  }
};

searchInput.addEventListener('input', filtrerStagiaires);
filterService.addEventListener('change', filtrerStagiaires);
filterStatut.addEventListener('change', filtrerStagiaires);

// Charger services d'abord, puis stagiaires
(async function init() {
  await chargerServices();
  await chargerStagiaires();
})();
