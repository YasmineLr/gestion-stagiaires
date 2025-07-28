document.addEventListener("DOMContentLoaded", () => {
  fetch("../../backend/statistiques_evaluations.php")
    .then(res => res.json())
    .then(data => {
      if (!data.success) throw new Error(data.message);

      document.getElementById("noteMoyenne").textContent = data.note_moyenne;
      document.getElementById("evaluationsParTuteur").textContent = data.evaluations_par_tuteur;
      document.getElementById("stagiairesNonEvalues").textContent = data.stagiaires_non_evalues;
    })
    .catch(err => {
      alert("Erreur chargement des statistiques : " + err.message);
    });
});
