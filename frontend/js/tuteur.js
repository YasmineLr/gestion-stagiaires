document.addEventListener("DOMContentLoaded", () => {
  fetch("get_mes_stagiaires.php", {
    method: "GET",
    credentials: "include",
  })
    .then((res) => res.json())
    .then((data) => {
      const table = document.getElementById("stagiairesBody");

      if (data.success && data.stagiaires.length > 0) {
        data.stagiaires.forEach((stagiaire) => {
          const row = document.createElement("tr");

          row.innerHTML = `
            <td>
              <a href="../frontend/pages/evaluation.html?stagiaire_id=${
                stagiaire.stagiaire_id
              }" class="link-primary text-decoration-none">
                ${stagiaire.prenom} ${stagiaire.nom}
              </a>
            </td>
            <td>${stagiaire.telephone}</td>
            <td>${stagiaire.adresse}</td>
            <td>${stagiaire.parcours}</td>
            <td>${stagiaire.date_affectation}</td>
            <td>${stagiaire.note ?? ""}</td>
            <td>
              <div class="d-flex justify-content-center gap-2">
                <button class="btn btn-sm btn-primary modifier-btn" data-id="${
                  stagiaire.stagiaire_id
                }">
                  ✏ Modifier
                </button>
                <button class="btn btn-sm btn-danger supprimer-btn" data-id="${
                  stagiaire.stagiaire_id
                }">
                  🗑 Supprimer
                </button>
              </div>
            </td>
            <td>${stagiaire.commentaire ?? "-"}</td>
            <td>${stagiaire.statut ?? "-"}</td>
          `;

          table.appendChild(row);
        });

        // 🔁 Événements : Modifier
        document.querySelectorAll(".modifier-btn").forEach((btn) => {
          btn.addEventListener("click", (e) => {
            const id = e.target.dataset.id;
            window.location.href = `../frontend/pages/evaluation.html?stagiaire_id=${id}`;
          });
        });

        // 🔁 Événements : Supprimer
        document.querySelectorAll(".supprimer-btn").forEach((btn) => {
          btn.addEventListener("click", (e) => {
            const id = e.target.dataset.id;
            if (confirm("Voulez-vous vraiment supprimer ce stagiaire ?")) {
              fetch("supprimer_stagiaire.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ stagiaire_id: id }),
                credentials: "include",
              })
                .then((res) => res.json())
                .then((result) => {
                  if (result.success) {
                    alert("✅ Stagiaire supprimé !");
                    location.reload();
                  } else {
                    alert("❌ Erreur : " + result.message);
                  }
                })
                .catch((err) => {
                  alert("❌ Erreur serveur !");
                  console.error(err);
                });
            }
          });
        });
      } else {
        table.innerHTML =
          '<tr><td colspan="9" class="text-center">Aucun stagiaire affecté</td></tr>';
      }
    })
    .catch((error) => {
      console.error("Erreur lors du chargement des stagiaires :", error);
    });
});
