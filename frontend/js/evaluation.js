document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("evaluationForm");
  const message = document.getElementById("message");

  form.addEventListener("submit", (e) => {
    e.preventDefault();

    const stagiaireId = document.getElementById("stagiaireId").value;
    const note = document.getElementById("note").value;
    const commentaire = document.getElementById("commentaire").value;

    fetch("../../backend/evaluation.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `stagiaire_id=${stagiaireId}&note=${note}&commentaire=${encodeURIComponent(
        commentaire
      )}`,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          message.innerHTML = "✅ Évaluation enregistrée avec succès.";
          message.style.color = "green";
          form.reset();
        } else {
          message.innerHTML = "❌ " + data.message;
          message.style.color = "red";
        }
      })
      .catch(() => {
        message.innerHTML = "❌ Erreur lors de l'envoi.";
        message.style.color = "red";
      });
  });
});
