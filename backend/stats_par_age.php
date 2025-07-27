<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['tuteur_id'])) {
  echo json_encode(['labels' => [], 'values' => []]);
  exit;
}

$tuteur_id = $_SESSION['tuteur_id'];

try {
  $pdo = new PDO('mysql:host=localhost;port=3307;dbname=gestion-stagiaires;charset=utf8', 'root', '');
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // On récupère tous les âges liés à ce tuteur
  $stmt = $pdo->prepare("
    SELECT s.age
    FROM tuteur_stagiaire ts
    JOIN stagiaires s ON ts.stagiaire_id = s.id
    WHERE ts.tuteur_id = ? AND ts.supprime = 0
  ");
  $stmt->execute([$tuteur_id]);

  $ages = $stmt->fetchAll(PDO::FETCH_COLUMN);

  // Regroupement par tranches d’âge
  $tranches = [
    '18-25' => 0,
    '26-30' => 0,
    '31-35' => 0,
    '36-40' => 0,
    '41+'   => 0
  ];

  foreach ($ages as $age) {
    if ($age >= 18 && $age <= 25) $tranches['18-25']++;
    elseif ($age >= 26 && $age <= 30) $tranches['26-30']++;
    elseif ($age >= 31 && $age <= 35) $tranches['31-35']++;
    elseif ($age >= 36 && $age <= 40) $tranches['36-40']++;
    else $tranches['41+']++;
  }

  echo json_encode([
    'labels' => array_keys($tranches),
    'values' => array_values($tranches)
  ]);

} catch (PDOException $e) {
  echo json_encode(['labels' => [], 'values' => [], 'error' => $e->getMessage()]);
}