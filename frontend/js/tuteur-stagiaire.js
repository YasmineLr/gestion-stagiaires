const stagiaireSelect = document.getElementById("stagiaireSelect");
const tuteurSelect = document.getElementById("tuteurSelect");
const affectationForm = document.getElementById("affectationForm");
const formError = document.getElementById("formError");
const tableBody = document.querySelector("#affectationsTable tbody");

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

async function loadAffectations() {
  try {
    const res = await fetch("../../backend/tuteur_stagiaire.php?action=list");
    const data = await res.json();

    if (data.success) {
      tableBody.innerHTML = "";

      data.affectations.forEach(a => {
        const note = (a.note !== null && a.note !== undefined) ? a.note : 'Non noté';
        const nomStagiaire = a.nom_stagiaire || "Nom stagiaire inconnu";
        const nomTuteur = a.nom_tuteur || "Nom tuteur inconnu";
        const dateAffect = a.date_affectation || "-";

        const row = `
          <tr>
            <td>${nomStagiaire}</td>
            <td>${nomTuteur}</td>
            <td>${dateAffect}</td>
            <td>${note}</td>
            <td>
              <button class="btn btn-danger btn-sm" onclick="deleteAffectation(${a.id})">Supprimer</button>
            </td>
          </tr>
        `;
        tableBody.innerHTML += row;
      });
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
