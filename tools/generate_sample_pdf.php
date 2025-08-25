<?php
require __DIR__ . '/../vendor/autoload.php';

use CDDU_Manager\DocumentGenerator;

$template = __DIR__ . '/../templates/contracts/cddu.html.php';
$data = [
    'org' => ['denomination' => 'NEXT FORMA', 'adresse' => "1 Rue Exemple\n75000 Paris", 'ville' => 'Paris'],
    'formateur' => ['prenom' => 'Jean', 'nom' => 'Dupont', 'adresse' => "10 rue Test\n75010 Paris"],
    'mission' => ['action' => 'Formation PHP', 'date_debut' => '2025-09-01', 'date_fin' => '2025-10-15', 'H_a' => 40, 'taux_horaire' => 30],
    'calc' => CDDU_Manager\Calculs::calculer(['H_a' => 40, 'taux_horaire' => 30, 'date_debut' => '2025-09-01', 'date_fin' => '2025-10-15'])
];

$output = '/tmp/cddu_sample.pdf';
$ok = DocumentGenerator::generatePdf($template, $data, $output);
if ($ok) {
    echo "Generated PDF: $output\n";
} else {
    echo "Failed to generate PDF\n";
}
