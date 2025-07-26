<?php
session_start();
header('Content-Type: application/json');

try {
    $pdo = new PDO('mysql:host=localhost;port=3307;dbname=gestion-stagiaires;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_SESSION['tuteur_id'])) {
        echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
        exit;
    }

    $tuteur_id = $_SESSION['tuteur_id'];

    $stmt = $pdo->prepare("
        SELECT s.nom, s.prenom, s.telephone, s.adresse, s.parcours,
               ts.date_affectation,
               (SELECT note FROM evaluations e WHERE e.stagiaire_id = s.id AND e.supprime = 0 LIMIT 1) AS note
        FROM stagiaires s
        JOIN tuteur_stagiaire ts ON ts.stagiaire_id = s.id
        WHERE ts.tuteur_id = ? AND ts.supprime = 0 AND s.supprime = 0
    ");
    $stmt->execute([$tuteur_id]);
    $stagiaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'stagiaires' => $stagiaires]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur DB: ' . $e->getMessage()]);
}