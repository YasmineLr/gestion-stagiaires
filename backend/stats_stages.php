<?php
// Pour test local sans session
header('Content-Type: application/json');

// 🔧 Remplace cette valeur par un ID tuteur existant dans ta table `stages`
$tuteur_id = 5;

try {
    $pdo = new PDO('mysql:host=localhost;port=3307;dbname=gestion-stagiaires;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 🔹 Stages en cours
    $stmtEnCours = $pdo->prepare("SELECT COUNT(*) FROM stages WHERE tuteur_id = ? AND statut = 'En cours'");
    $stmtEnCours->execute([$tuteur_id]);
    $en_cours = $stmtEnCours->fetchColumn();

    // 🔹 Stages terminés
    $stmtTermines = $pdo->prepare("SELECT COUNT(*) FROM stages WHERE tuteur_id = ? AND statut = 'Terminé'");
    $stmtTermines->execute([$tuteur_id]);
    $termines = $stmtTermines->fetchColumn();

    // 🔹 Stages en retard = en cours mais date_fin dépassée
    $stmtRetard = $pdo->prepare("SELECT COUNT(*) FROM stages WHERE tuteur_id = ? AND statut = 'En cours' AND date_fin < CURDATE()");
    $stmtRetard->execute([$tuteur_id]);
    $retard = $stmtRetard->fetchColumn();

    // 🔹 Répartition par domaine (nom_stage)
    $stmtDomaines = $pdo->prepare("
        SELECT nom_stage AS domaine, COUNT(*) AS total
        FROM stages
        WHERE tuteur_id = ? AND statut = 'En cours'
        GROUP BY nom_stage
    ");
    $stmtDomaines->execute([$tuteur_id]);
    $domaines = $stmtDomaines->fetchAll(PDO::FETCH_ASSOC);

    // ✅ Résultat
    echo json_encode([
        'success' => true,
        'en_cours' => (int) $en_cours,
        'termines' => (int) $termines,
        'retard' => (int) $retard,
        'domaines' => $domaines
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de connexion : ' . $e->getMessage()
    ]);
}