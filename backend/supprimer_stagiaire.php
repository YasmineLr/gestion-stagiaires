<?php
session_start();
header('Content-Type: application/json');

try {
    $pdo = new PDO('mysql:host=localhost;port=3307;dbname=gestion-stagiaires;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = json_decode(file_get_contents("php://input"), true);
    $stagiaire_id = $data['stagiaire_id'] ?? null;

    if (!$stagiaire_id) {
        echo json_encode(['success' => false, 'message' => 'ID stagiaire manquant']);
        exit;
    }

    // Supprimer en base (soft delete ici)
    $stmt = $pdo->prepare("UPDATE tuteur_stagiaire SET supprime = 1 WHERE stagiaire_id = ?");
    $stmt->execute([$stagiaire_id]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}