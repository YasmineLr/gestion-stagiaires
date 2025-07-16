<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=localhost;dbname=gestion-stagiaires;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Récupérer toutes les évaluations avec nom complet stagiaire
        $stmt = $pdo->query("
            SELECT 
                s.id AS stagiaire_id,
                CONCAT(IFNULL(s.prenom, ''), ' ', IFNULL(s.nom, '')) AS nom,
                e.note,
                e.commentaires,
                e.ameliorations,
                e.date_evaluation
            FROM evaluations e
            JOIN stagiaires s ON e.stagiaire_id = s.id
            WHERE s.supprime = 0
        ");
        $evaluations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($evaluations);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Récupérer données POST
        $stagiaire_id = $_POST['stagiaire_id'] ?? null;
        $note = $_POST['note'] ?? null;
        $commentaire = $_POST['commentaire'] ?? '';
        $ameliorations = $_POST['ameliorations'] ?? '';

        // Validation simple
        if (!$stagiaire_id || !is_numeric($note)) {
            echo json_encode(['success' => false, 'message' => 'Données invalides']);
            exit;
        }

        // Définir un tuteur_id fixe (à adapter selon ta session)
        $tuteur_id = 1;
        $date_evaluation = date('Y-m-d');

        // Vérifier si une évaluation existe déjà pour ce stagiaire
        $check = $pdo->prepare("SELECT id FROM evaluations WHERE stagiaire_id = ?");
        $check->execute([$stagiaire_id]);

        if ($check->rowCount() > 0) {
            // Mise à jour
            $stmt = $pdo->prepare("
                UPDATE evaluations 
                SET note = ?, commentaires = ?, ameliorations = ?, tuteur_id = ?, date_evaluation = ?
                WHERE stagiaire_id = ?
            ");
            $stmt->execute([$note, $commentaire, $ameliorations, $tuteur_id, $date_evaluation, $stagiaire_id]);
        } else {
            // Ajout
            $stmt = $pdo->prepare("
                INSERT INTO evaluations (stagiaire_id, tuteur_id, note, commentaires, ameliorations, date_evaluation) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$stagiaire_id, $tuteur_id, $note, $commentaire, $ameliorations, $date_evaluation]);
        }

        echo json_encode(['success' => true]);
        exit;
    }

    // Méthode non gérée
    echo json_encode(['success' => false, 'message' => 'Méthode HTTP non autorisée']);
    exit;

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur DB : ' . $e->getMessage()]);
}
