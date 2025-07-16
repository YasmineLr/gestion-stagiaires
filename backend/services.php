<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO('mysql:host=localhost;dbname=gestion-stagiaires;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données']);
    exit;
}

try {
    $stmt = $pdo->query("SELECT id, nom FROM services ORDER BY nom");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'services' => $services]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors du chargement des services']);
}
