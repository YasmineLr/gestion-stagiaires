<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=localhost;port=3307;dbname=gestion-stagiaires;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Stages en cours
    $en_cours = $pdo->query("SELECT COUNT(*) FROM stages WHERE statut = 'en cours'")->fetchColumn();

    // Stages terminés
    $termines = $pdo->query("SELECT COUNT(*) FROM stages WHERE statut = 'terminé'")->fetchColumn();

    // Stages en retard (date_fin < aujourd’hui ET statut = 'en cours')
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM stages WHERE statut = 'en cours' AND date_fin < CURRENT_DATE");
    $stmt->execute();
    $retard = $stmt->fetchColumn();

    // Stages à l’heure = stages en cours qui ne sont pas en retard
    $a_heure = $en_cours - $retard;

    echo json_encode([
        'success' => true,
        'en_cours' => (int) $en_cours,
        'termines' => (int) $termines,
        'retard' => (int) $retard,
        'a_heure' => (int) $a_heure
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur DB: ' . $e->getMessage()]);
}
