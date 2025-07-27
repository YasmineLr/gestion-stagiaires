document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("evaluationForm");
  const message = document.getElementById("message");

  form.addEventListener("submit", (e) => {
    e.preventDefault();

    const stagiaireId = document.getElementById("stagiaireId").value;
    const note = document.getElementById("note").value;
    const commentaire = document.getElementById("commentaire").value;

    // 🔍 Debug : vérifie que l'ID est bien récupéré
    console.log("Stagiaire ID récupéré:", stagiaireId);

    if (!stagiaireId || !note || !commentaire) {
      message.innerHTML = `<span class="text-danger">Tous les champs sont obligatoires.</span>`;
      return;
    }

    fetch("../../backend/evaluation.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      credentials: "include", // 🔥 Nécessaire pour les sessions PHP
      body: `stagiaire_id=${encodeURIComponent(
        stagiaireId
      )}&note=${encodeURIComponent(note)}&commentaire=${encodeURIComponent(
        commentaire
      )}`,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          // ✅ Redirection vers la page des stagiaires si succès
          window.location.href = "../../backend/tuteur.php";
        } else {
          message.innerHTML = `<span class="text-danger">${data.message}</span>`;
        }
      })
      .catch((error) => {
        console.error("Erreur fetch:", error);
        message.innerHTML = `<span class="text-danger">Erreur serveur. Veuillez réessayer.</span>`;
      });
  });
});
