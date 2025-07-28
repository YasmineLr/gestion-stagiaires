<?php
header('Content-Type: application/json');

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=localhost;port=3307;dbname=gestion-stagiaires;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Nombre total de stagiaires (non supprimés)
    $total = $pdo->query("SELECT COUNT(*) FROM stagiaires WHERE supprime = 0")->fetchColumn();

    // 2. Nombre de stagiaires actifs
    $actifs = $pdo->query("SELECT COUNT(*) FROM stagiaires WHERE statut = 'actif' AND supprime = 0")->fetchColumn();

    // 3. Répartition par service
    $stmt = $pdo->query("
        SELECT s.nom, COUNT(stg.id) as count
        FROM services s
        LEFT JOIN stages st ON st.service_id = s.id
        LEFT JOIN stagiaires stg ON stg.id = st.stagiaire_id AND stg.supprime = 0
        GROUP BY s.nom
    ");
    $services = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // 4. Répartition par type de stage
    $stmt = $pdo->query("
        SELECT st.type_stage, COUNT(*) as count
        FROM stages st
        JOIN stagiaires sg ON st.stagiaire_id = sg.id
        WHERE sg.supprime = 0
        GROUP BY st.type_stage
    ");
    $types = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // 5. Répartition par sexe
    $stmt = $pdo->query("
        SELECT sexe, COUNT(*) as count
        FROM stagiaires
        WHERE supprime = 0
        GROUP BY sexe
    ");
    $sexes = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // 6. Répartition par tranches d’âge
    $stmt = $pdo->query("SELECT date_naissance FROM stagiaires WHERE supprime = 0 AND date_naissance IS NOT NULL");
    $ages = ['18-25' => 0, '26-30' => 0, '31+' => 0];
    $today = new DateTime();
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $dateNaissance) {
        $age = $today->diff(new DateTime($dateNaissance))->y;
        if ($age <= 25) {
            $ages['18-25']++;
        } elseif ($age <= 30) {
            $ages['26-30']++;
        } else {
            $ages['31+']++;
        }
    }

    // Réponse JSON finale
    echo json_encode([
        'success' => true,
        'total' => (int) $total,
        'actifs' => (int) $actifs,
        'par_service' => [
            'labels' => array_keys($services),
            'values' => array_values($services)
        ],
        'par_type' => [
            'labels' => array_keys($types),
            'values' => array_values($types)
        ],
        'par_age' => [
            'labels' => array_keys($ages),
            'values' => array_values($ages)
        ],
        'par_sexe' => [
            'labels' => array_keys($sexes),
            'values' => array_values($sexes)
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur DB: ' . $e->getMessage()]);
}
