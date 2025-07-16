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
        $stmt = $pdo->query("
            SELECT t.id, t.nom, t.service_id, s.nom AS service_nom
            FROM tuteurs t
            LEFT JOIN services s ON t.service_id = s.id
            WHERE t.supprime = 0
            ORDER BY t.nom
        ");
        echo json_encode(['success' => true, 'tuteurs' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        break;

    case 'add':
        if (empty($input['nom']) || empty($input['service_id'])) {
            echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires']);
            exit;
        }
        $stmt = $pdo->prepare("INSERT INTO tuteurs (nom, service_id, supprime) VALUES (?, ?, 0)");
        $stmt->execute([$input['nom'], $input['service_id']]);
        echo json_encode(['success' => true]);
        break;

    case 'update':
        if (empty($input['id']) || empty($input['nom']) || empty($input['service_id'])) {
            echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires']);
            exit;
        }
        $stmt = $pdo->prepare("UPDATE tuteurs SET nom = ?, service_id = ? WHERE id = ?");
        $stmt->execute([$input['nom'], $input['service_id'], $input['id']]);
        echo json_encode(['success' => true]);
        break;

    case 'delete':
        if (empty($input['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
            exit;
        }
        $stmt = $pdo->prepare("UPDATE tuteurs SET supprime = 1 WHERE id = ?");
        $stmt->execute([$input['id']]);
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Action invalide']);
}
