<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['tuteur_id'])) {
  echo json_encode(['labels' => [], 'values' => [], 'error' => 'Non connecté']);
  exit;
}

$tuteur_id = $_SESSION['tuteur_id'];

try {
  $pdo = new PDO('mysql:host=localhost;port=3307;dbname=gestion-stagiaires;charset=utf8', 'root', '');
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $stmt = $pdo->prepare("
    SELECT s.sexe, COUNT(*) as total
    FROM tuteur_stagiaire ts
    JOIN stagiaires s ON s.id = ts.stagiaire_id
    WHERE ts.tuteur_id = ? AND ts.supprime = 0
    GROUP BY s.sexe
  ");
  $stmt->execute([$tuteur_id]);
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $labels = array_column($results, 'sexe');
  $values = array_column($results, 'total');

  echo json_encode(['labels' => $labels, 'values' => $values]);

} catch (PDOException $e) {
  echo json_encode(['labels' => [], 'values' => [], 'error' => $e->getMessage()]);
}