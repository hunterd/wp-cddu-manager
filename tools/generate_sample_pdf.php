<?php
require __DIR__ . '/../vendor/autoload.php';

use CDDU_Manager\DocumentGenerator;

$template = __DIR__ . '/../templates/contracts/contract.html.php';
$data = [
    'org' => ['name' => 'NEXT FORMA', 'denomination' => 'NEXT FORMA', 'address' => "1 Rue Exemple\n75000 Paris", 'adresse' => "1 Rue Exemple\n75000 Paris", 'ville' => 'Paris'],
    'instructor' => ['full_name' => 'Jean Dupont', 'nom_prenom' => 'Jean Dupont', 'prenom' => 'Jean', 'nom' => 'Dupont', 'address' => "10 rue Test\n75010 Paris", 'adresse' => "10 rue Test\n75010 Paris"],
    // prefer English keys but keep French aliases for compatibility
    'mission' => ['action' => 'PHP Training', 'start_date' => '2025-09-01', 'end_date' => '2025-10-15', 'annual_hours' => 40, 'hourly_rate' => 30, 'date_debut' => '2025-09-01', 'date_fin' => '2025-10-15', 'H_a' => 40, 'taux_horaire' => 30],
    'calc' => CDDU_Manager\Calculations::calculate(['annual_hours' => 40, 'hourly_rate' => 30, 'start_date' => '2025-09-01', 'end_date' => '2025-10-15'])
];

$output = '/tmp/cddu_sample.pdf';
$ok = DocumentGenerator::generatePdf($template, $data, $output);
if ($ok) {
    echo "Generated PDF: $output\n";
} else {
    echo "Failed to generate PDF\n";
}
