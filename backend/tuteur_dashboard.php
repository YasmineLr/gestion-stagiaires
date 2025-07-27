<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Tuteur</title>
    <script>
    async function loadStagiaires() {
        const response = await fetch('get_mes_stagiaires.php');
        const data = await response.json();
        const tableBody = document.getElementById('stagiaires-list');

        if (data.success) {
            data.stagiaires.forEach(stagiaire => {
                let row = `<tr>
                        <td>${stagiaire.nom}</td>
                        <td>${stagiaire.prenom}</td>
                        <td>${stagiaire.telephone}</td>
                        <td>${stagiaire.adresse}</td>
                        <td>${stagiaire.note ?? '-'}</td>
                    </tr>`;
                tableBody.innerHTML += row;
            });
        } else {
            tableBody.innerHTML = `<tr><td colspan="5">${data.message}</td></tr>`;
        }
    }
    window.onload = loadStagiaires;
    </script>
</head>

<body>
    <h1>Mes Stagiaires</h1>
    <table border="1">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Téléphone</th>
                <th>Adresse</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody id="stagiaires-list"></tbody>
    </table>
</body>

</html>