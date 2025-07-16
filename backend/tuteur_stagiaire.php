<?php
// Affichage erreurs pour dev (à désactiver en prod)
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

try {
    $pdo = new PDO('mysql:host=localhost;dbname=gestion-stagiaires;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $action = $_GET['action'] ?? '';
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);

    if ($action === 'load-options') {
        // Charger stagiaires et tuteurs NON supprimés (supprime = 0)
        $stagiaires = $pdo->query("SELECT id, prenom, nom FROM stagiaires WHERE supprime = 0")->fetchAll(PDO::FETCH_ASSOC);
        $tuteurs = $pdo->query("SELECT id, prenom, nom FROM tuteurs WHERE supprime = 0")->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'stagiaires' => $stagiaires,
            'tuteurs' => $tuteurs
        ]);
        exit;

    } elseif ($action === 'list') {
        // Liste des affectations actives et dont stagiaires + tuteurs ne sont pas supprimés
        $stmt = $pdo->prepare("
            SELECT ts.id,
                   CONCAT(s.prenom, ' ', s.nom) AS nom_stagiaire,
                   t.nom AS nom_tuteur,
                   ts.date_affectation,
                   e.note
            FROM tuteur_stagiaire ts
            JOIN stagiaires s ON ts.stagiaire_id = s.id AND s.supprime = 0
            JOIN tuteurs t ON ts.tuteur_id = t.id AND t.supprime = 0
            LEFT JOIN evaluations e ON e.stagiaire_id = ts.stagiaire_id AND e.supprime = 0
            WHERE ts.supprime = 0
            ORDER BY ts.date_affectation DESC
            LIMIT 100
        ");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'affectations' => $rows]);
        exit;

    } elseif ($action === 'add') {
        $stagiaire_id = $input['stagiaire_id'] ?? null;
        $tuteur_id = $input['tuteur_id'] ?? null;

        if ($stagiaire_id && $tuteur_id) {
            // Vérifier si une affectation déjà existe et est soft-supprimée, on la réactive sinon on crée une nouvelle
            $stmtCheck = $pdo->prepare("SELECT id FROM tuteur_stagiaire WHERE stagiaire_id = ? AND tuteur_id = ?");
            $stmtCheck->execute([$stagiaire_id, $tuteur_id]);
            $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                // Mettre à jour supprime = 0 si supprimée
                $stmtUpdate = $pdo->prepare("UPDATE tuteur_stagiaire SET supprime = 0, date_affectation = CURDATE() WHERE id = ?");
                $stmtUpdate->execute([$existing['id']]);
            } else {
                // Nouvelle insertion
                $stmt = $pdo->prepare("INSERT INTO tuteur_stagiaire (stagiaire_id, tuteur_id, date_affectation, supprime) VALUES (?, ?, CURDATE(), 0)");
                $stmt->execute([$stagiaire_id, $tuteur_id]);
            }

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Champs obligatoires manquants']);
        }
        exit;

    } elseif ($action === 'delete') {
        $id = $input['id'] ?? null;

        if ($id) {
            // Soft delete : mettre supprime=1 au lieu de DELETE physique
            $stmt = $pdo->prepare("UPDATE tuteur_stagiaire SET supprime = 1 WHERE id = ?");
            $stmt->execute([$id]);

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
        }
        exit;

    } else {
        echo json_encode(['success' => false, 'message' => 'Action non valide']);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur DB : ' . $e->getMessage()]);
}
