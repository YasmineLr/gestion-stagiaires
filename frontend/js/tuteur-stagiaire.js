const stagiaireSelect = document.getElementById("stagiaireSelect");
const tuteurSelect = document.getElementById("tuteurSelect");
const affectationForm = document.getElementById("affectationForm");
const formError = document.getElementById("formError");
const tableBody = document.querySelector("#affectationsTable tbody");

// Charger options (stagiaires non affectés + tuteurs)
async function loadOptions() {
  try {
    const res = await fetch("../../backend/tuteur_stagiaire.php?action=load-options");
    const data = await res.json();

    if (data.success) {
      stagiaireSelect.innerHTML = '<option value="">-- Choisir un stagiaire --</option>';
      tuteurSelect.innerHTML = '<option value="">-- Choisir un tuteur --</option>';

      data.stagiaires.forEach(s => {
        const nomComplet = s.prenom ? `${s.prenom} ${s.nom}` : s.nom;
        stagiaireSelect.innerHTML += `<option value="${s.id}">${nomComplet}</option>`;
      });

      data.tuteurs.forEach(t => {
        const nomComplet = t.prenom ? `${t.prenom} ${t.nom}` : t.nom;
        tuteurSelect.innerHTML += `<option value="${t.id}">${nomComplet}</option>`;
      });
    } else {
      console.error("Erreur API load-options:", data.message);
    }
  } catch (err) {
    console.error("Erreur chargement options", err);
  }
}

// Charger affectations et grouper par tuteur avec rowspan
async function loadAffectations() {
  try {
    const res = await fetch("../../backend/tuteur_stagiaire.php?action=list");
    const data = await res.json();

    if (data.success) {
      tableBody.innerHTML = "";

      // Grouper par tuteur
      const groupes = {};
      data.affectations.forEach(item => {
        const tuteur = item.nom_tuteur || "Inconnu";
        if (!groupes[tuteur]) groupes[tuteur] = [];
        groupes[tuteur].push(item);
      });

      // Construire les lignes avec rowspan pour tuteur
      for (const [tuteur, stagiaires] of Object.entries(groupes)) {
        stagiaires.forEach((affect, index) => {
          const note = (affect.note !== null && affect.note !== undefined) ? affect.note : 'Non noté';
          const sujet = affect.sujet || '-';
          const nomStagiaire = affect.nom_stagiaire || 'Inconnu';
          const dateAffect = affect.date_affectation || '-';

          let row = "<tr>";
          if (index === 0) {
            row += `<td rowspan="${stagiaires.length}">${tuteur}</td>`;
          }
          row += `
            <td>${nomStagiaire}</td>
            <td>${sujet}</td>
            <td>${note}</td>
            <td>${dateAffect}</td>
            <td>
              <button class="btn btn-danger btn-sm" onclick="deleteAffectation(${affect.id})">Supprimer</button>
            </td>
          `;
          row += "</tr>";

          tableBody.innerHTML += row;
        });
      }
    } else {
      console.error("Erreur API list:", data.message);
    }
  } catch (err) {
    console.error("Erreur chargement affectations", err);
  }
}

affectationForm.addEventListener("submit", async (e) => {
  e.preventDefault();
  formError.textContent = "";

  const stagiaire_id = stagiaireSelect.value;
  const tuteur_id = tuteurSelect.value;

  if (!stagiaire_id || !tuteur_id) {
    formError.textContent = "Tous les champs sont obligatoires.";
    return;
  }

  try {
    const res = await fetch("../../backend/tuteur_stagiaire.php?action=add", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ stagiaire_id, tuteur_id })
    });

    const data = await res.json();
    if (data.success) {
      await loadAffectations();
      affectationForm.reset();
    } else {
      formError.textContent = data.message || "Erreur lors de l'affectation";
    }
  } catch (err) {
    console.error("Erreur lors de l'envoi du formulaire", err);
    formError.textContent = "Erreur réseau ou serveur.";
  }
});

async function deleteAffectation(id) {
  if (!confirm("Confirmer la suppression de cette affectation ?")) return;

  try {
    const res = await fetch("../../backend/tuteur_stagiaire.php?action=delete", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id })
    });

    const data = await res.json();
    if (data.success) {
      await loadAffectations();
    } else {
      alert(data.message || "Erreur suppression");
    }
  } catch (err) {
    alert("Erreur réseau ou serveur");
  }
}

// Chargement initial
loadOptions();
loadAffectations();
