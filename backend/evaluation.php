<?php
session_start();
header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=localhost;port=3307;dbname=gestion-stagiaires", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_SESSION['tuteur_id'])) {
        echo json_encode(['success' => false, 'message' => 'Non connecté']);
        exit;
    }

    $tuteur_id = $_SESSION['tuteur_id'];
    $stagiaire_id = $_POST['stagiaire_id'] ?? '';
    $note = $_POST['note'] ?? '';
    $commentaire = $_POST['commentaire'] ?? '';

    if ($stagiaire_id && $note && $commentaire) {
        $stmt = $pdo->prepare("INSERT INTO evaluations (stagiaire_id, tuteur_id, note, commentaire) VALUES (?, ?, ?, ?)");
        $stmt->execute([$stagiaire_id, $tuteur_id, $note, $commentaire]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Champs manquants']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur : ' . $e->getMessage()]);
}
?>