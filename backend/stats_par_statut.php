<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['tuteur_id'])) {
  echo json_encode(['labels' => [], 'values' => [], 'message' => 'Non connecté']);
  exit;
}

$tuteur_id = $_SESSION['tuteur_id'];

try {
  $pdo = new PDO('mysql:host=localhost;port=3307;dbname=gestion-stagiaires;charset=utf8', 'root', '');
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Récupération des données
  $stmtTotal = $pdo->prepare("
    SELECT COUNT(*) 
    FROM tuteur_stagiaire ts
    JOIN stagiaires s ON ts.stagiaire_id = s.id
    WHERE ts.tuteur_id = ? AND ts.supprime = 0
  ");
  $stmtTotal->execute([$tuteur_id]);
  $total = $stmtTotal->fetchColumn();

  $stmtActifs = $pdo->prepare("
    SELECT COUNT(*) 
    FROM tuteur_stagiaire ts
    JOIN stagiaires s ON ts.stagiaire_id = s.id
    WHERE ts.tuteur_id = ? AND s.statut = 'actif' AND ts.supprime = 0
  ");
  $stmtActifs->execute([$tuteur_id]);
  $actifs = $stmtActifs->fetchColumn();

  $stmtTermines = $pdo->prepare("
    SELECT COUNT(*) 
    FROM tuteur_stagiaire ts
    JOIN stagiaires s ON ts.stagiaire_id = s.id
    WHERE ts.tuteur_id = ? AND s.statut = 'terminé' AND ts.supprime = 0
  ");
  $stmtTermines->execute([$tuteur_id]);
  $termines = $stmtTermines->fetchColumn();

  // Préparer structure compatible avec Chart.js
  echo json_encode([
    'labels' => ['Actifs', 'Terminés'],
    'values' => [$actifs, $termines]
  ]);

} catch (PDOException $e) {
  echo json_encode(['labels' => [], 'values' => [], 'message' => 'Erreur BDD : ' . $e->getMessage()]);
}