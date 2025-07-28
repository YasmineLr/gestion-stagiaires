<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=localhost;port=3307;dbname=gestion-stagiaires;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Note moyenne des évaluations (stagiaires non supprimés)
    $stmt = $pdo->query("
        SELECT AVG(note) as moyenne 
        FROM evaluations e
        JOIN stagiaires s ON e.stagiaire_id = s.id
        WHERE s.supprime = 0
    ");
    $moyenne = $stmt->fetchColumn();
    $moyenne = $moyenne !== null ? round($moyenne, 2) : 0;

    // Nombre total d’évaluations (par tuteur) pour stagiaires non supprimés
    $stmt = $pdo->query("
        SELECT COUNT(*) as total
        FROM evaluations e
        JOIN stagiaires s ON e.stagiaire_id = s.id
        WHERE s.supprime = 0
    ");
    $evaluationsParTuteur = (int) $stmt->fetchColumn();

    // Nombre de stagiaires non évalués (sans aucune évaluation)
    $stmt = $pdo->query("
        SELECT COUNT(*) 
        FROM stagiaires s
        LEFT JOIN evaluations e ON s.id = e.stagiaire_id
        WHERE s.supprime = 0
        AND e.id IS NULL
    ");
    $stagiairesNonEvalues = (int) $stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'note_moyenne' => $moyenne,
        'evaluations_par_tuteur' => $evaluationsParTuteur,
        'stagiaires_non_evalues' => $stagiairesNonEvalues,
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur DB: ' . $e->getMessage()
    ]);
}
