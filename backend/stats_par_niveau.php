<?php
session_start();
header('Content-Type: application/json');

// Vérifie que le tuteur est connecté
if (!isset($_SESSION['tuteur_id'])) {
  echo json_encode(['labels' => [], 'values' => [], 'success' => false, 'message' => 'Non connecté']);
  exit;
}

$tuteur_id = $_SESSION['tuteur_id'];

try {
  // Connexion directe à la base de données (ajuste si nécessaire)
  $pdo = new PDO('mysql:host=localhost;port=3307;dbname=gestion-stagiaires;charset=utf8', 'root', '');
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Requête pour récupérer les niveaux d'études des stagiaires liés au tuteur
  $stmt = $pdo->prepare("
    SELECT s.niveau_etude, COUNT(*) as total
    FROM tuteur_stagiaire ts
    JOIN stagiaires s ON ts.stagiaire_id = s.id
    WHERE ts.tuteur_id = ? AND ts.supprime = 0
    GROUP BY s.niveau_etude
  ");
  $stmt->execute([$tuteur_id]);

  $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $labels = [];
  $values = [];

  foreach ($result as $row) {
    $labels[] = $row['niveau_etude'];
    $values[] = (int)$row['total'];
  }

  echo json_encode([
    'success' => true,
    'labels' => $labels,
    'values' => $values
  ]);

} catch (PDOException $e) {
  echo json_encode([
    'success' => false,
    'labels' => [],
    'values' => [],
    'message' => 'Erreur DB : ' . $e->getMessage()
  ]);
}