<?php
session_start();
header('Content-Type: application/json');

try {
    $pdo = new PDO('mysql:host=localhost;port=3307;dbname=gestion-stagiaires;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_SESSION['tuteur_id'])) {
        echo json_encode(['success' => false, 'message' => 'Tuteur non connecté']);
        exit;
    }

    $tuteur_id = $_SESSION['tuteur_id'];

    // 1. Stages à venir
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM stages s
        JOIN tuteur_stagiaire ts ON ts.stagiaire_id = s.stagiaire_id AND ts.supprime = 0
        WHERE ts.tuteur_id = ? AND s.date_debut > CURDATE()
    ");
    $stmt->execute([$tuteur_id]);
    $a_venir = $stmt->fetchColumn();

    // 2. Durée moyenne (en jours)
    $stmt = $pdo->prepare("
        SELECT AVG(DATEDIFF(s.date_fin, s.date_debut)) AS moyenne
        FROM stages s
        JOIN tuteur_stagiaire ts ON ts.stagiaire_id = s.stagiaire_id AND ts.supprime = 0
        WHERE ts.tuteur_id = ? AND s.date_fin IS NOT NULL AND s.date_debut IS NOT NULL
    ");
    $stmt->execute([$tuteur_id]);
    $duree_moyenne = round($stmt->fetchColumn() ?? 0);

    // 3. Nombre évalués
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT e.stagiaire_id)
        FROM evaluations e
        JOIN tuteur_stagiaire ts ON ts.stagiaire_id = e.stagiaire_id AND ts.supprime = 0
        WHERE ts.tuteur_id = ? AND e.supprime = 0
    ");
    $stmt->execute([$tuteur_id]);
    $evalues = $stmt->fetchColumn();

    // 4. Nombre total de stagiaires affectés
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT ts.stagiaire_id)
        FROM tuteur_stagiaire ts
        WHERE ts.tuteur_id = ? AND ts.supprime = 0
    ");
    $stmt->execute([$tuteur_id]);
    $total_stagiaires = $stmt->fetchColumn();

    $non_evalues = $total_stagiaires - $evalues;

    // 5. Stages incomplets (date_debut ou date_fin null)
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM stages s
        JOIN tuteur_stagiaire ts ON ts.stagiaire_id = s.stagiaire_id AND ts.supprime = 0
        WHERE ts.tuteur_id = ? AND (s.date_debut IS NULL OR s.date_fin IS NULL)
    ");
    $stmt->execute([$tuteur_id]);
    $incomplets = $stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'a_venir' => $a_venir,
        'duree_moyenne' => $duree_moyenne,
        'evalues' => $evalues,
        'non_evalues' => $non_evalues,
        'incomplets' => $incomplets
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur DB : ' . $e->getMessage()]);
}