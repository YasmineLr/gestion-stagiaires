<?php
header('Content-Type: application/json');
session_start();

$tuteur_id = $_SESSION['tuteur_id'] ?? 1;

try {
    $pdo = new PDO('mysql:host=localhost;port=3307;dbname=gestion-stagiaires;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer tous les stagiaires de ce tuteur
    $stmt = $pdo->prepare("SELECT stagiaire_id FROM tuteur_stagiaire WHERE tuteur_id = ? AND supprime = 0");
    $stmt->execute([$tuteur_id]);
    $stagiaire_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($stagiaire_ids)) {
        echo json_encode([
            'success' => true,
            'en_cours' => 0,
            'termines' => 0,
            'retard' => 0,
            'domaines' => []
        ]);
        exit;
    }

    $placeholders = implode(',', array_fill(0, count($stagiaire_ids), '?'));

    // Récupérer TOUS les stages de ces stagiaires (pas juste les plus récents)
    $stmt = $pdo->prepare("SELECT * FROM stages WHERE stagiaire_id IN ($placeholders)");
    $stmt->execute($stagiaire_ids);
    $stages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $en_cours = $termines = $retard = 0;
    $domaines = [];

    foreach ($stages as $stage) {
        if ($stage['statut'] === 'En cours') {
            $en_cours++;
            if (strtotime($stage['date_fin']) < strtotime(date('Y-m-d'))) {
                $retard++;
            }
        } elseif ($stage['statut'] === 'Terminé') {
            $termines++;
        }

        $domaine = $stage['sujet'];
        if (!isset($domaines[$domaine])) {
            $domaines[$domaine] = 0;
        }
        $domaines[$domaine]++;
    }

    $domaines_array = [];
    foreach ($domaines as $domaine => $total) {
        $domaines_array[] = ['domaine' => $domaine, 'total' => $total];
    }

    echo json_encode([
        'success' => true,
        'en_cours' => $en_cours,
        'termines' => $termines,
        'retard' => $retard,
        'domaines' => $domaines_array
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur BDD : ' . $e->getMessage()
    ]);
}