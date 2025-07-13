console.log("JS chargé");

const tableBody = document.querySelector("#stagiairesTable tbody");
const form = document.getElementById("stagiaireForm");
const formError = document.getElementById("formError");

form.addEventListener("submit", async e => {
  e.preventDefault();
  console.log("Formulaire soumis");

  const formData = new FormData(form); // inclut les fichiers

  const res = await fetch("../../backend/stagiaires.php?action=add", {
    method: "POST",
    body: formData
  });

  const text = await res.text();
  console.log("Réponse brute du serveur:", text);

  try {
    const result = JSON.parse(text);
    if (result.success) {
      form.reset();
      formError.textContent = "";
      await chargerStagiaires();
    } else {
      formError.textContent = result.message || "Erreur lors de l'enregistrement";
    }
  } catch (err) {
    console.error("Erreur JSON :", err);
    formError.textContent = "Erreur lors de l'enregistrement.";
  }
});

async function chargerStagiaires() {
  try {
    const res = await fetch("../../backend/stagiaires.php?action=list");
    const data = await res.json();
    if (data.success) {
      tableBody.innerHTML = "";
      data.stagiaires.forEach(s => {
        // On échappe les apostrophes simples dans le JSON pour éviter de casser l'attribut HTML
        const stagiaireData = JSON.stringify(s).replace(/'/g, "&apos;");
        const row = `
          <tr>
            <td>${s.nom || ''}</td>
            <td>${s.service || ''}</td>
            <td>${s.statut || ''}</td>
            <td>${s.adresse || ''}</td>
            <td>${s.telephone || ''}</td>
            <td>${s.parcours || ''}</td>
            <td>
              ${s.document ? `<a href="../../backend/uploads/${s.document}" target="_blank">Voir</a>` : 'Aucun'}
            </td>
            <td>
              <button class="btn btn-sm btn-warning me-1" data-stagiaire='${stagiaireData}' onclick="onClickModifier(this)">Modifier</button>
              <button class="btn btn-sm btn-danger" onclick='supprimerStagiaire(${s.id})'>Supprimer</button>
            </td>
          </tr>
        `;
        tableBody.innerHTML += row;
      });
    }
  } catch (err) {
    console.error("Erreur de chargement :", err);
  }
}

// Nouvelle fonction appelée par le bouton Modifier
function onClickModifier(button) {
  const stagiaire = JSON.parse(button.getAttribute('data-stagiaire'));
  modifierStagiaire(stagiaire);
}

function modifierStagiaire(stagiaire) {
  console.log("Données à modifier :", stagiaire);

  document.getElementById("id").value = stagiaire.id;
  document.getElementById("nom").value = stagiaire.nom;
  document.getElementById("service").value = stagiaire.service;
  document.getElementById("statut").value = stagiaire.statut;
  document.getElementById("telephone").value = stagiaire.telephone;
  document.getElementById("adresse").value = stagiaire.adresse;
  document.getElementById("parcours").value = stagiaire.parcours;
}

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

chargerStagiaires();
