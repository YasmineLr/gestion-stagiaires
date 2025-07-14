<?php
// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Réponse JSON
header('Content-Type: application/json');

try {
    // Connexion à la base de données
    $pdo = new PDO('mysql:host=localhost;dbname=gestion-stagiaires;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Action GET
    $action = $_GET['action'] ?? '';

    if ($action === 'load-options') {
        // Charger les options stagiaires et tuteurs
        $stagiaires = $pdo->query("SELECT id, nom FROM stagiaires")->fetchAll(PDO::FETCH_ASSOC);
        $tuteurs = $pdo->query("SELECT id, nom FROM tuteurs")->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'stagiaires' => $stagiaires,
            'tuteurs' => $tuteurs
        ]);

    } elseif ($action === 'list') {
        // Lister les affectations avec le nom du stagiaire, tuteur et la note (si disponible)
        $stmt = $pdo->query("
            SELECT ts.id,
                   CONCAT(s.prenom, ' ', s.nom) AS nom_stagiaire,
                   t.nom AS nom_tuteur,
                   ts.date_affectation,
                   e.note
            FROM tuteur_stagiaire ts
            JOIN stagiaires s ON ts.stagiaire_id = s.id
            JOIN tuteurs t ON ts.tuteur_id = t.id
            LEFT JOIN evaluations e ON e.stagiaire_id = ts.stagiaire_id
            LIMIT 100
        ");

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'affectations' => $rows]);

    } elseif ($action === 'add') {
        // Ajouter une nouvelle affectation stagiaire-tuteur
        $data = json_decode(file_get_contents("php://input"), true);
        $stagiaire_id = $data['stagiaire_id'] ?? null;
        $tuteur_id = $data['tuteur_id'] ?? null;

        if ($stagiaire_id && $tuteur_id) {
            // Insérer l'affectation avec la date du jour
            $stmt = $pdo->prepare("
                INSERT INTO tuteur_stagiaire (stagiaire_id, tuteur_id, date_affectation) 
                VALUES (?, ?, CURDATE())
            ");
            $stmt->execute([$stagiaire_id, $tuteur_id]);

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Champs obligatoires manquants']);
        }

    } elseif ($action === 'delete') {
        // Supprimer une affectation
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['id'])) {
            $stmt = $pdo->prepare("DELETE FROM tuteur_stagiaire WHERE id = ?");
            $stmt->execute([$data['id']]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'ID manquant']);
        }

    } else {
        // Action non reconnue
        echo json_encode(['success' => false, 'message' => 'Action non valide']);
    }

} catch (PDOException $e) {
    // Gestion des erreurs SQL
    echo json_encode(['success' => false, 'message' => 'Erreur DB : ' . $e->getMessage()]);
}
