<?php
session_start(); // ⚠️ Obligatoire pour utiliser $_SESSION

ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

try {
    $pdo = new PDO('mysql:host=localhost;port=3307;dbname=gestion-stagiaires;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    if ($email && $password && $role) {
        if ($role === 'tuteur') {
            $stmt = $pdo->prepare("SELECT * FROM tuteurs WHERE email = ? AND mot_de_passe = ?");
        } elseif ($role === 'admin') {
            $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ? AND mot_de_passe = ?");
        } else {
            echo json_encode(['success' => false, 'message' => 'Rôle invalide']);
            exit;
        }

        $stmt->execute([$email, $password]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Enregistrer l'utilisateur dans la session
            if ($role === 'tuteur') {
                $_SESSION['tuteur_id'] = $user['id'];  // ✅ TRES IMPORTANT
            } elseif ($role === 'admin') {
                $_SESSION['admin_id'] = $user['id'];
            }

            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "message" => "Identifiants incorrects"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Champs manquants"]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erreur serveur : " . $e->getMessage()]);
}
?>