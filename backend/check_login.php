<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

try {
    $pdo = new PDO('mysql:host=localhost;port=3307;dbname=medical_billing;charset=utf8', 'root', '');

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    if ($email && $password) {
        if($role == 'admin')
            $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ? AND mot_de_passe = ?");
        else if($role == 'tuteur') 
            $stmt = $pdo->prepare("SELECT * FROM tuteurs WHERE email = ? AND mot_de_passe = ?");
        $stmt->execute([$email, $password]);

        if ($stmt->rowCount() > 0) {
          echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Champs manquants"]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erreur serveur : " . $e->getMessage()]);
}
?>