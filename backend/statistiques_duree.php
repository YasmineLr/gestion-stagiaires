<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=localhost;port=3307;dbname=gestion-stagiaires;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupère les dates de début/fin des stages
    $stmt = $pdo->query("
        SELECT date_debut, date_fin
        FROM stages
        WHERE date_debut IS NOT NULL AND date_fin IS NOT NULL
    ");

    $durations = ['Court (<=1 mois)' => 0, 'Moyen (1-3 mois)' => 0, 'Long (>3 mois)' => 0];
    $totalWeeks = 0;
    $count = 0;

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $start = new DateTime($row['date_debut']);
        $end = new DateTime($row['date_fin']);
        $weeks = floor($start->diff($end)->days / 7);

        $totalWeeks += $weeks;
        $count++;

        if ($weeks <= 4) {
            $durations['Court (<=1 mois)']++;
        } elseif ($weeks <= 12) {
            $durations['Moyen (1-3 mois)']++;
        } else {
            $durations['Long (>3 mois)']++;
        }
    }

    echo json_encode([
        'success' => true,
        'duree_moyenne' => $count > 0 ? round($totalWeeks / $count, 1) : 0,
        'par_duree' => [
            'labels' => array_keys($durations),
            'values' => array_values($durations)
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur DB: ' . $e->getMessage()]);
}
