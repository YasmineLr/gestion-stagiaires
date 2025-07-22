<?php
header('Content-Type: application/json');
$pdo = new PDO("mysql:host=localhost;post=3307;dbname=gestion_stagiaires;charset=utf8", "root", "");

$data = [
  "total" => 0,
  "actifs" => 0,
  "parType" => [],
  "parSexe" => [],
  "parAge" => []
];

// total stagiaires
$data["total"] = $pdo->query("SELECT COUNT(*) FROM stagiaires")->fetchColumn();

// stagiaires actifs
$data["actifs"] = $pdo->query("SELECT COUNT(*) FROM stagiaires WHERE date_fin > CURDATE()")->fetchColumn();

// par type
$types = $pdo->query("SELECT type_stage, COUNT(*) as nb FROM stagiaires GROUP BY type_stage");
foreach ($types as $row) {
  $data["parType"][$row["type_stage"]] = $row["nb"];
}

// par sexe
$sexes = $pdo->query("SELECT sexe, COUNT(*) as nb FROM stagiaires GROUP BY sexe");
foreach ($sexes as $row) {
  $data["parSexe"][$row["sexe"]] = $row["nb"];
}

// par âge
$ages = $pdo->query("SELECT TIMESTAMPDIFF(YEAR, date_naissance, CURDATE()) AS age FROM stagiaires");
$tranches = ["18-25" => 0, "26-30" => 0, "31+" => 0];
foreach ($ages as $row) {
  $age = $row["age"];
  if ($age >= 18 && $age <= 25) $tranches["18-25"]++;
  elseif ($age <= 30) $tranches["26-30"]++;
  else $tranches["31+"]++;
}
$data["parAge"] = $tranches;

echo json_encode($data);