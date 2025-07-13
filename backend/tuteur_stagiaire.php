<?php
header('Content-Type: application/json');
$pdo = new PDO('mysql:host=localhost;dbname=gestion-stagiaires', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$action = $_GET['action'] ?? '';

if ($action === 'load-options') {
    $stagiaires = $pdo->query("SELECT id, nom FROM stagiaires")->fetchAll(PDO::FETCH_ASSOC);
    $tuteurs = $pdo->query("SELECT id, nom FROM tuteurs")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'stagiaires' => $stagiaires, 'tuteurs' => $tuteurs]);
} elseif ($action === 'list') {
    $stmt = $pdo->query("
        SELECT ts.id, s.nom AS nom_stagiaire, t.nom AS nom_tuteur, ts.date_affectation, ts.evaluation
        FROM tuteur_stagiaire ts
        JOIN stagiaires s ON ts.stagiaire_id = s.id
        JOIN tuteurs t ON ts.tuteur_id = t.id
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'affectations' => $rows]);
} elseif ($action === 'delete') {
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $pdo->prepare("DELETE FROM tuteur_stagiaire WHERE id = ?");
    $stmt->execute([$data['id']]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Action non valide']);
}
