<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=localhost;port=3307;dbname=gestion-stagiaires;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $action = $_GET['action'] ?? '';

    if ($action === 'list') {
        $stmt = $pdo->query("
            SELECT st.*, s.nom AS service_nom
            FROM stagiaires st
            LEFT JOIN services s ON st.service_id = s.id
            WHERE st.supprime = 0
            ORDER BY st.id DESC
        ");
        $stagiaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'stagiaires' => $stagiaires]);
        exit;
    }

    if ($action === 'add') {
        $id = $_POST['id'] ?? null;
        $nom = $_POST['nom'] ?? '';
        $service_id = $_POST['service_id'] ?? '';
        $statut = $_POST['statut'] ?? '';
        $adresse = $_POST['adresse'] ?? '';
        $telephone = $_POST['telephone'] ?? '';
        $parcours = $_POST['parcours'] ?? '';
        $documentNomFichier = null;

        // Champs stage supplémentaires
        $date_debut = $_POST['date_debut'] ?? '';
        $date_fin = $_POST['date_fin'] ?? '';
        $sujet = $_POST['sujet'] ?? '';
        $type_stage = $_POST['type_stage'] ?? '';
        $nom_stage = $_POST['nom_stage'] ?? '';

        if (!$nom || !$service_id || !$statut || !$date_debut || !$date_fin || !$type_stage || !$nom_stage) {
            echo json_encode(['success' => false, 'message' => 'Tous les champs obligatoires doivent être remplis']);
            exit;
        }

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
            if ($documentNomFichier) {
                $stmt = $pdo->prepare("UPDATE stagiaires SET nom=?, service_id=?, statut=?, telephone=?, adresse=?, parcours=?, document=? WHERE id=?");
                $stmt->execute([$nom, $service_id, $statut, $telephone, $adresse, $parcours, $documentNomFichier, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE stagiaires SET nom=?, service_id=?, statut=?, telephone=?, adresse=?, parcours=? WHERE id=?");
                $stmt->execute([$nom, $service_id, $statut, $telephone, $adresse, $parcours, $id]);
            }
        } else {
            $stmt = $pdo->prepare("INSERT INTO stagiaires (nom, service_id, statut, telephone, adresse, parcours, document, supprime) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
            $stmt->execute([$nom, $service_id, $statut, $telephone, $adresse, $parcours, $documentNomFichier]);
            $stagiaire_id = $pdo->lastInsertId();

            // Insertion du stage lié
            $stmtStage = $pdo->prepare("INSERT INTO stages (stagiaire_id, service_id, date_debut, date_fin, sujet, type_stage, nom_stage) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmtStage->execute([$stagiaire_id, $service_id, $date_debut, $date_fin, $sujet, $type_stage, $nom_stage]);
        }

        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'delete') {
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE stagiaires SET supprime = 1 WHERE id = ?");
        $stmt->execute([$data['id']]);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'terminer') {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'] ?? null;

        if (!empty($id)) {
            $stmt = $pdo->prepare("SELECT statut FROM stagiaires WHERE id = ? AND supprime = 0");
            $stmt->execute([$id]);
            $stagiaire = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($stagiaire && strtolower(trim($stagiaire['statut'])) === 'actif') {
                $update = $pdo->prepare("UPDATE stagiaires SET statut = 'Terminé' WHERE id = ?");
                $update->execute([$id]);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Déjà terminé ou introuvable']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
        }
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Action non valide']);
    exit;

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur DB : ' . $e->getMessage()]);
}
