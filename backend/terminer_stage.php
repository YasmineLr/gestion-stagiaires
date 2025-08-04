<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['tuteur_id'])) {
  echo json_encode(['success' => false, 'message' => 'Tuteur non connecté']);
  exit;
}

$stagiaire_id = $_POST['stagiaire_id'] ?? '';

if (!$stagiaire_id) {
  echo json_encode(['success' => false, 'message' => 'ID stagiaire manquant']);
  exit;
}

try {
  $pdo = new PDO('mysql:host=localhost;port=3307;dbname=gestion-stagiaires', 'root', '');
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $stmt = $pdo->prepare("UPDATE stagiaires SET statut = 'terminé' WHERE id = ?");
  $stmt->execute([$stagiaire_id]);

  echo json_encode(['success' => true]);
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'message' => 'Erreur serveur : ' . $e->getMessage()]);
}