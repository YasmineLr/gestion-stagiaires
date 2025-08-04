<?php 
session_start();
header('Content-Type: application/json');

try {
    $pdo = new PDO('mysql:host=localhost;port=3307;dbname=gestion-stagiaires;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_SESSION['tuteur_id'])) {
        echo json_encode([
            'success' => false,
            'message' => '❌ Tuteur non connecté.'
        ]);
        exit;
    }

    $tuteur_id = $_SESSION['tuteur_id'];

    $stmt = $pdo->prepare("
        SELECT 
            s.nom AS nom,
            s.prenom AS prenom,
            t.nom AS nom_tuteur,
            t.prenom AS prenom_tuteur,
            ts.date_affectation,
            st.date_debut,
            st.date_fin,
            srv.nom AS service,
            st.sujet AS sujet,
            st.nom_stage AS nom_stage,
            st.type_stage AS type_stage,
            st.documents AS documents,
            (
                SELECT e.note 
                FROM evaluations e 
                WHERE e.stagiaire_id = s.id AND e.supprime = 0 
                ORDER BY e.id DESC LIMIT 1
            ) AS note,
            (
                SELECT e.commentaire 
                FROM evaluations e 
                WHERE e.stagiaire_id = s.id AND e.supprime = 0 
                ORDER BY e.id DESC LIMIT 1
            ) AS commentaire
        FROM stagiaires s
        INNER JOIN tuteur_stagiaire ts ON ts.stagiaire_id = s.id AND ts.supprime = 0
        INNER JOIN tuteurs t ON t.id = ts.tuteur_id
        INNER JOIN stages st ON st.stagiaire_id = s.id
        LEFT JOIN services srv ON srv.id = st.service_id
        WHERE s.statut = 'terminé'
          AND ts.tuteur_id = :tuteur_id
          AND s.supprime = 0
    ");

    $stmt->execute(['tuteur_id' => $tuteur_id]);
    $stagiaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $stagiaires
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur : ' . $e->getMessage()
    ]);
}