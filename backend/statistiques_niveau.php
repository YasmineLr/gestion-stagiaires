<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=localhost;port=3307;dbname=gestion-stagiaires;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Note moyenne des évaluations
    $moyenne = $pdo->query("SELECT AVG(note) FROM evaluations")->fetchColumn();

    // Nombre d'évaluations par tuteur
    $stmt = $pdo->query("
        SELECT t.nom, COUNT(e.id) as count
        FROM tuteurs t
        LEFT JOIN evaluations e ON e.tuteur_id = t.id
        GROUP BY t.nom
    ");
    $evaluations_par_tuteur = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Nombre de stagiaires non évalués
    $non_eval = $pdo->query("
        SELECT COUNT(*)
        FROM stagiaires s
        LEFT JOIN evaluations e ON e.stagiaire_id = s.id
        WHERE e.id IS NULL AND s.supprime = 0
    ")->fetchColumn();

    // Répartition par niveau d'étude
    $stmt = $pdo->query("
        SELECT niveau_etude, COUNT(*) as count
        FROM stagiaires
        WHERE supprime = 0
        GROUP BY niveau_etude
    ");
    $par_niveau = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    echo json_encode([
        'success' => true,
        'moyenne' => round($moyenne, 2),
        'evaluations_par_tuteur' => [
            'labels' => array_keys($evaluations_par_tuteur),
            'values' => array_values($evaluations_par_tuteur)
        ],
        'non_eval' => (int) $non_eval,
        'par_niveau' => [
            'labels' => array_keys($par_niveau),
            'values' => array_values($par_niveau)
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur DB: ' . $e->getMessage()]);
}
