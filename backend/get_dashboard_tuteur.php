<?php
session_start();
header('Content-Type: application/json');

// ✅ Vérifie si le tuteur est connecté
if (!isset($_SESSION['tuteur_id'])) {
    echo json_encode(['success' => false, 'message' => 'Tuteur non connecté']);
    exit;
}

$tuteur_id = $_SESSION['tuteur_id'];

try {
    $pdo = new PDO('mysql:host=localhost;port=3307;dbname=gestion-stagiaires;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ✅ Récupérer toutes les statistiques via jointure avec stagiaires
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) AS total,
            SUM(CASE WHEN s.statut = 'actif' THEN 1 ELSE 0 END) AS actifs,
            SUM(CASE WHEN s.statut = 'terminé' THEN 1 ELSE 0 END) AS termines
        FROM tuteur_stagiaire ts
        JOIN stagiaires s ON s.id = ts.stagiaire_id
        WHERE ts.tuteur_id = :tuteur_id AND ts.supprime = 0
    ");
    $stmt->execute(['tuteur_id' => $tuteur_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'total' => (int)$data['total'],
        'actifs' => (int)$data['actifs'],
        'termines' => (int)$data['termines']
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur BDD : ' . $e->getMessage()]);
}