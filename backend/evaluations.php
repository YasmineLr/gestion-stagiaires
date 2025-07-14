<?php
// Connexion à la base de données
$pdo = new PDO("mysql:host=localhost;dbname=gestion-stagiaires;charset=utf8", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Si c'est une requête GET → Lire toutes les évaluations
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("
        SELECT 
            s.id AS stagiaire_id,
            CONCAT(s.prenom, ' ', s.nom) AS nom,
            e.note,
            e.commentaires,
            e.ameliorations,
            e.date_evaluation
        FROM evaluations e
        JOIN stagiaires s ON e.stagiaire_id = s.id
    ");

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// Si c'est une requête POST → Ajouter ou modifier une évaluation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données envoyées en POST
    $stagiaire_id = $_POST['stagiaire_id'];
    $note = $_POST['note'];
    $commentaire = $_POST['commentaire'];
    $ameliorations = $_POST['ameliorations'];

    // ➕ Astuce : définir un tuteur_id en dur ici (ex: 1)
    $tuteur_id = 1; // tu peux remplacer cela par $_SESSION['tuteur_id'] plus tard si tu as une session
    $date_evaluation = date('Y-m-d');

    // Vérifier si l'évaluation existe déjà
    $check = $pdo->prepare("SELECT id FROM evaluations WHERE stagiaire_id = ?");
    $check->execute([$stagiaire_id]);

    if ($check->rowCount() > 0) {
        // Modifier
        $stmt = $pdo->prepare("
            UPDATE evaluations 
            SET note = ?, commentaires = ?, ameliorations = ?, tuteur_id = ?, date_evaluation = ?
            WHERE stagiaire_id = ?
        ");
        $stmt->execute([$note, $commentaire, $ameliorations, $tuteur_id, $date_evaluation, $stagiaire_id]);
    } else {
        // Ajouter
        $stmt = $pdo->prepare("
            INSERT INTO evaluations (stagiaire_id, tuteur_id, note, commentaires, ameliorations, date_evaluation) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$stagiaire_id, $tuteur_id, $note, $commentaire, $ameliorations, $date_evaluation]);
    }

    echo json_encode(['success' => true]);
    exit;
}
?>