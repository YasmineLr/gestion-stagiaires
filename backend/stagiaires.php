<?php
header('Content-Type: application/json');

$pdo = new PDO("mysql:host=localhost;dbname=gestion-stagiaires", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$action = $_GET['action'] ?? '';

if ($action === 'list') {
    $stmt = $pdo->query("SELECT * FROM stagiaires ORDER BY id DESC");
    echo json_encode(['success' => true, 'stagiaires' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
}

if ($action === 'add') {
    $id = $_POST['id'] ?? null;
    $nom = $_POST['nom'] ?? '';
    $service = $_POST['service'] ?? '';
    $statut = $_POST['statut'] ?? '';
    $adresse = $_POST['adresse'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $parcours = $_POST['parcours'] ?? '';
    $documentNomFichier = null;

    if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
        $uploadsDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }
        $extension = pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('doc_') . '.' . $extension;
        $fullPath = $uploadsDir . $filename;

        if (move_uploaded_file($_FILES['document']['tmp_name'], $fullPath)) {
            $documentNomFichier = $filename;
        }
    }

    if ($id) {
        // Mise à jour
        if ($documentNomFichier) {
            $stmt = $pdo->prepare("UPDATE stagiaires SET nom=?, service=?, statut=?, telephone=?, adresse=?, parcours=?, document=? WHERE id=?");
            $stmt->execute([$nom, $service, $statut, $telephone, $adresse, $parcours, $documentNomFichier, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE stagiaires SET nom=?, service=?, statut=?, telephone=?, adresse=?, parcours=? WHERE id=?");
            $stmt->execute([$nom, $service, $statut, $telephone, $adresse, $parcours, $id]);
        }
    } else {
        // Insertion
        $stmt = $pdo->prepare("INSERT INTO stagiaires (nom, service, statut, telephone, adresse, parcours, document) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $service, $statut, $telephone, $adresse, $parcours, $documentNomFichier]);
    }

    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'delete') {
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $pdo->prepare("DELETE FROM stagiaires WHERE id = ?");
    $stmt->execute([$data['id']]);
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Action non valide']);
