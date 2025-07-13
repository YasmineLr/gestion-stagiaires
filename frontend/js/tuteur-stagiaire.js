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
        stagiaireSelect.innerHTML += `<option value="${s.id}">${s.nom}</option>`;
      });

      data.tuteurs.forEach(t => {
        tuteurSelect.innerHTML += `<option value="${t.id}">${t.nom}</option>`;
      });
    }
  } catch (err) {
    console.error("Erreur chargement options", err);
  }
}

async function loadAffectations() {
  const res = await fetch("../../backend/tuteur_stagiaire.php?action=list");
  const data = await res.json();
  if (data.success) {
    tableBody.innerHTML = "";
   data.affectations.forEach(a => {
  const note = a.evaluation !== null ? a.evaluation : 'Non noté';
  const row = `
    <tr>
      <td>${a.nom_stagiaire}</td>
      <td>${a.nom_tuteur}</td>
      <td>${a.date_affectation}</td>
      <td>${note}</td>  <!-- Affiche la note -->
      <td>
        <button class="btn btn-danger btn-sm" onclick="deleteAffectation(${a.id})">Supprimer</button>
      </td>
    </tr>
  `;
  tableBody.innerHTML += row;
});
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

  const res = await fetch("../../backend/tuteur_stagiaire.php?action=add", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ stagiaire_id, tuteur_id })
  });

  const data = await res.json();
  console.log(data)
  if (data.success) {
    await loadAffectations();
    affectationForm.reset();
  } else {
    formError.textContent = data.message || "Erreur lors de l'affectation";
  }
});

async function deleteAffectation(id) {
  if (!confirm("Confirmer la suppression de cette affectation ?")) return;

  const res = await fetch("../../backend/tuteur_stagiaire.php?action=delete", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ id })
  });

  const data = await res.json();
  if (data.success) {
    await loadAffectations();
  } else {
    alert("Erreur suppression");
  }
}

loadOptions();
loadAffectations();
