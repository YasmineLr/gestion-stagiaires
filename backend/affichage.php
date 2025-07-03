<?php
// (plus tard tu pourras récupérer ces données depuis la base)
$typeStageData = [10, 25, 15]; // Court, Long, Fin d'études

// Aller chercher le fichier HTML
$html = file_get_contents('../frontend/pages/affichage.html');

// Injecter les données dynamiques dans le HTML
$html = str_replace('{{DATA}}', json_encode($typeStageData), $html);

// Afficher le HTML final
echo $html;
?>