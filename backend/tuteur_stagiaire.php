<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

try {
    $pdo = new PDO('mysql:host=localhost;port=3307;dbname=gestion-stagiaires;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $action = $_GET['action'] ?? '';
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);

    // 1. Charger les options (stagiaires non affectés + tous les tuteurs)
    if ($action === 'load-options') {
        $stagiaires = $pdo->query("
            SELECT s.id, s.prenom, s.nom 
            FROM stagiaires s
            WHERE s.supprime = 0 
              AND s.id NOT IN (
                SELECT stagiaire_id FROM tuteur_stagiaire WHERE supprime = 0
              )
        ")->fetchAll(PDO::FETCH_ASSOC);

        $tuteurs = $pdo->query("SELECT id, prenom, nom FROM tuteurs WHERE supprime = 0")->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'stagiaires' => $stagiaires, 'tuteurs' => $tuteurs]);
        exit;
    }

    // 2. Liste des affectations (avec sujet et note)
    if ($action === 'list') {
        $stmt = $pdo->prepare("
            SELECT 
                ts.id,
                CONCAT(t.prenom, ' ', t.nom) AS nom_tuteur,
                CONCAT(s.prenom, ' ', s.nom) AS nom_stagiaire,
                COALESCE(st.sujet, '') AS sujet,
                ts.date_affectation,
                COALESCE(e.note, 'Non noté') AS note
            FROM tuteur_stagiaire ts
            JOIN stagiaires s ON ts.stagiaire_id = s.id AND s.supprime = 0
            JOIN tuteurs t ON ts.tuteur_id = t.id AND t.supprime = 0
            LEFT JOIN stages st ON st.stagiaire_id = s.id
            LEFT JOIN evaluations e ON e.stagiaire_id = s.id AND e.supprime = 0
            WHERE ts.supprime = 0
            ORDER BY t.nom, ts.date_affectation DESC
        ");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'affectations' => $rows]);
        exit;
    }

    // 3. Ajouter une affectation + créer/mettre à jour un stage
    if ($action === 'add') {
        $stagiaire_id = $input['stagiaire_id'] ?? null;
        $tuteur_id = $input['tuteur_id'] ?? null;

        if ($stagiaire_id && $tuteur_id) {
            // Vérifier si une affectation existe déjà
            $stmtCheck = $pdo->prepare("SELECT id FROM tuteur_stagiaire WHERE stagiaire_id = ? AND tuteur_id = ?");
            $stmtCheck->execute([$stagiaire_id, $tuteur_id]);
            $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $stmtUpdate = $pdo->prepare("UPDATE tuteur_stagiaire SET supprime = 0, date_affectation = CURDATE() WHERE id = ?");
                $stmtUpdate->execute([$existing['id']]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO tuteur_stagiaire (stagiaire_id, tuteur_id, date_affectation, supprime) VALUES (?, ?, CURDATE(), 0)");
                $stmt->execute([$stagiaire_id, $tuteur_id]);
            }

            // 🔄 Mettre à jour ou créer le stage correspondant
            $stageExist = $pdo->prepare("SELECT id FROM stages WHERE stagiaire_id = ?");
            $stageExist->execute([$stagiaire_id]);
            $stage = $stageExist->fetch(PDO::FETCH_ASSOC);

            if ($stage) {
                $updateStage = $pdo->prepare("UPDATE stages SET tuteur_id = ?, statut = 'en cours' WHERE stagiaire_id = ?");
                $updateStage->execute([$tuteur_id, $stagiaire_id]);
            } else {
                $insertStage = $pdo->prepare("INSERT INTO stages (stagiaire_id, tuteur_id, sujet, statut) VALUES (?, ?, '', 'en cours')");
                $insertStage->execute([$stagiaire_id, $tuteur_id]);
            }

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Champs obligatoires manquants']);
        }
        exit;
    }

    // 4. Supprimer une affectation (logique)
    if ($action === 'delete') {
        $id = $input['id'] ?? null;
        if ($id) {
            $stmt = $pdo->prepare("UPDATE tuteur_stagiaire SET supprime = 1 WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
        }
        exit;
    }

    // Action non reconnue
    echo json_encode(['success' => false, 'message' => 'Action non valide']);
    exit;

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur DB : ' . $e->getMessage()]);
}
