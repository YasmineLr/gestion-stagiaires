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

  $stmt = $pdo->prepare("
    SELECT srv.nom AS services, COUNT(*) AS total
    FROM tuteur_stagiaire ts
    JOIN stagiaires st ON ts.stagiaire_id = st.id
    JOIN services srv ON st.service_id = srv.id
    WHERE ts.supprime = 0 AND ts.tuteur_id = ?
    GROUP BY srv.nom
  ");
  $stmt->execute([$tuteur_id]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $labels = array_column($rows, 'services');
  $values = array_column($rows, 'total');

  echo json_encode(['labels' => $labels, 'values' => $values]);

} catch (Exception $e) {
  echo json_encode(['labels' => [], 'values' => [], 'error' => $e->getMessage()]);
}