<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=localhost;port=3307;dbname=gestion-stagiaires;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $action = $_GET['action'] ?? '';
    $annee = $_GET['annee'] ?? '';

    if ($action === 'list') {
        $sql = "
        SELECT 
            st.id AS id_stagiaire,
            st.nom AS nom,
            st.prenom AS prenom,
            sg.date_debut,
            sg.date_fin,
            s.nom AS service_nom,
            sg.type_stage,
            sg.nom_stage,
            sg.document_stage,
            ev.note,
            ev.commentaires,
            t.nom AS nom_tuteur,
            t.prenom AS prenom_tuteur
        FROM stagiaires st
        JOIN stages sg ON sg.stagiaire_id = st.id
        LEFT JOIN services s ON sg.service_id = s.id
        LEFT JOIN evaluations ev ON ev.stagiaire_id = st.id
        LEFT JOIN tuteur_stagiaire ts ON ts.stagiaire_id = st.id AND ts.supprime = 0
        LEFT JOIN tuteurs t ON t.id = ts.tuteur_id
        WHERE LOWER(st.statut) = 'terminé' AND st.supprime = 0
        ";

        // Filtrer par année si précisé
        if (!empty($annee) && preg_match('/^\d{4}$/', $annee)) {
            $sql .= " AND YEAR(sg.date_debut) = :annee ";
        }

        $sql .= " ORDER BY st.id DESC ";

        $stmt = $pdo->prepare($sql);

        if (!empty($annee) && preg_match('/^\d{4}$/', $annee)) {
            $stmt->bindValue(':annee', $annee, PDO::PARAM_INT);
        }

        $stmt->execute();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'records' => $records
        ]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Action non valide']);
    exit;

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur DB : ' . $e->getMessage()]);
    exit;
}
