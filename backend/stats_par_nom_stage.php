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

  $stmt = $pdo->prepare("
    SELECT stg.nom_stage, COUNT(*) AS total
    FROM tuteur_stagiaire ts
    JOIN stagiaires s ON ts.stagiaire_id = s.id
    JOIN stages stg ON s.id = stg.stagiaire_id
    WHERE ts.supprime = 0 AND ts.tuteur_id = ?
    GROUP BY stg.nom_stage
  ");
  $stmt->execute([$tuteur_id]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $labels = array_column($rows, 'nom_stage');
  $values = array_column($rows, 'total');

  echo json_encode(['labels' => $labels, 'values' => $values]);

} catch (PDOException $e) {
  echo json_encode(['labels' => [], 'values' => [], 'error' => $e->getMessage()]);
}