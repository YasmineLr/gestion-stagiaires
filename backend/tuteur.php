<?php
session_start();

// Vérifie si le tuteur est connecté
if (!isset($_SESSION['tuteur_id'])) {
    header("Location: ../frontend/pages/login.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <title>Mes Stagiaires</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- Bootstrap + Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../frontend/styles/tuteur.css" />
    <link rel="stylesheet" href="../frontend/styles/vueensemble.css" />
    <link rel="stylesheet" href="../frontend/styles/tuteur-header.css">
</head>

<body>

    <!-- HEADER NAVBAR -->
    <div class="header-container">
        <nav class="navbar navbar-expand-lg navbar-custom">
            <div class="container">
                <div class="collapse navbar-collapse justify-content-center">
                    <ul class="navbar-nav">
                        <li class="nav-item nav-item1 mx-1">
                            <a class="nav-link text-white d-flex align-items-center"
                                href="../frontend/pages/dashboard_tuteur.html">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard Tuteur
                            </a>
                        <li class="nav-item nav-item1 mx-1 active active1">
                            <a class="nav-link text-white d-flex align-items-center" href="#">
                                <i class="bi bi-bar-chart-line-fill me-2 text-white"></i>
                                Mes stagiaires
                            </a>
                        </li>
                        <li class="nav-item nav-item1 mx-1">
                            <a class="nav-link text-white d-flex align-items-center"
                                href="../frontend/pages/evaluation.html">
                                <i class="bi bi-pencil-fill me-2"></i> Evaluer Stagiaire
                            </a>
                        </li>
                        <li class="nav-item nav-item1 mx-1">
                            <a class="nav-link text-white d-flex align-items-center"
                                href="../frontend/pages/historique_tuteur.html">
                                <i class="bi bi-clock-history me-2"></i> Historique
                            </a>
                        </li>

                        <li class="nav-item nav-item1 mx-1">
                            <a class="nav-link text-white d-flex align-items-center"
                                href="../frontend/pages/login.html">
                                <i class="bi bi-box-arrow-right me-2"></i> Déconnexion
                            </a>
                        </li>

                    </ul>
                </div>
            </div>
        </nav>
    </div>

    <!-- CONTENU PRINCIPAL -->
    <div class="container mt-5 pt-5">
        <h2 class="mb-4 text-center">Mes Stagiaires</h2>
        <div class="table-responsive">
            <table class="table table-bordered text-center">
                <thead class="table-light">
                    <tr>
                        <th>Nom</th>
                        <th>Téléphone</th>
                        <th>Adresse</th>
                        <th>Parcours</th>
                        <th>Date Affectation</th>
                        <th>Note</th>
                        <th>Action</th>
                        <th>Commentaire</th>
                        <th>Statut</th> <!-- ✅ NOUVELLE COLONNE -->

                    </tr>
                </thead>
                <tbody id="stagiairesBody">
                    <!-- Rempli par JS -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODALE D'ÉVALUATION -->
    <div class="modal fade" id="evalModal" tabindex="-1" aria-labelledby="evalModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="evalForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="evalModalLabel">Évaluer le stagiaire</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="stagiaireIdInput" />
                        <div class="mb-3">
                            <label for="noteInput" class="form-label">Note (sur 20)</label>
                            <input type="number" class="form-control" id="noteInput" min="0" max="20" required />
                        </div>
                        <div class="mb-3">
                            <label for="commentaireInput" class="form-label">Commentaire</label>
                            <textarea class="form-control" id="commentaireInput" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../frontend/js/tuteur.js"></script>

</body>

</html>