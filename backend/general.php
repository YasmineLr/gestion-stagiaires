<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "gestion-stagiaires");

// 🔹 Vérifie la connexion
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// 🔹 Requête pour les types de stage
$type_stage = $conn->query("SELECT type_stage, COUNT(*) as total FROM stagiaires GROUP BY type_stage");

// 🔹 Requête pour le sexe
$sexe = $conn->query("SELECT sexe, COUNT(*) as total FROM stagiaires GROUP BY sexe");

// 🔹 Requête pour les tranches d'âge
$ages = [
    "18-25" => 0,
    "26-30" => 0,
    "31+"   => 0
];
$res = $conn->query("SELECT age FROM stagiaires");
while($row = $res->fetch_assoc()) {
    $age = $row['age'];
    if ($age >= 18 && $age <= 25) $ages["18-25"]++;
    elseif ($age >= 26 && $age <= 30) $ages["26-30"]++;
    else $ages["31+"]++;
}

$data = [
    "type_stage" => [],
    "sexe" => [],
    "age" => $ages
];

while($row = $type_stage->fetch_assoc()) {
    $data['type_stage'][] = $row;
}
while($row = $sexe->fetch_assoc()) {
    $data['sexe'][] = $row;
}

echo json_encode($data);
?>