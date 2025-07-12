<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO('mysql:host=localhost;dbname=gestion-stagiaires;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur connexion BDD']);
    exit;
}

$action = $_GET['action'] ?? '';

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

switch ($action) {
    case 'list':
        $stmt = $pdo->query("SELECT * FROM tuteurs ORDER BY nom");
        $tuteurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'tuteurs' => $tuteurs]);
        break;

    case 'add':
        if (empty($input['nom']) || empty($input['role']) || empty($input['service'])) {
            echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires']);
            exit;
        }
        $stmt = $pdo->prepare("INSERT INTO tuteurs (nom, role, service) VALUES (?, ?, ?)");
        $stmt->execute([
            $input['nom'],
            $input['role'],
            $input['service']
        ]);
        echo json_encode(['success' => true]);
        break;

    case 'update':
        if (empty($input['id']) || empty($input['nom']) || empty($input['role']) || empty($input['service'])) {
            echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires']);
            exit;
        }
        $stmt = $pdo->prepare("UPDATE tuteurs SET nom=?, role=?, service=? WHERE id=?");
        $stmt->execute([
            $input['nom'],
            $input['role'],
            $input['service'],
            $input['id']
        ]);
        echo json_encode(['success' => true]);
        break;

    case 'delete':
        if (empty($input['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM tuteurs WHERE id=?");
        $stmt->execute([$input['id']]);
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Action invalide']);
}
