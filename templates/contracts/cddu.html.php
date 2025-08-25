<?php
/**
 * Template HTML simple du contrat CDDU – à convertir en PDF avec Dompdf/mPDF.
 * Variables attendues: $org, $formateur, $mission, $calc
 */
?>
<!doctype html>
<html lang="fr">
<head><meta charset="utf-8"><title>Contrat CDDU</title></head>
<body>
<h1>Contrat CDDU</h1>
<h2>Organisme</h2>
<p><?= esc_html($org['denomination'] ?? '') ?><br>
<?= esc_html($org['adresse'] ?? '') ?></p>

<h2>Formateur</h2>
<p><?= esc_html(($formateur['prenom'] ?? '') . ' ' . ($formateur['nom'] ?? '')) ?><br>
<?= esc_html($formateur['adresse'] ?? '') ?></p>

<h2>Mission</h2>
<ul>
  <li>Action: <?= esc_html($mission['action'] ?? '') ?></li>
  <li>Période: <?= esc_html($mission['date_debut'] ?? '') ?> → <?= esc_html($mission['date_fin'] ?? '') ?></li>
  <li>Volume animation (H_a): <?= esc_html($mission['H_a'] ?? '') ?> h</li>
  <li>Taux horaire: <?= esc_html($mission['taux_horaire'] ?? '') ?> €</li>
  <li>Intensité hebdo: <?= esc_html(number_format($calc['intensite'] ?? 0, 2)) ?> h/sem</li>
</ul>

<h2>Rémunération</h2>
<ul>
  <li>Heures préparation (H_p): <?= esc_html(number_format($calc['H_p'] ?? 0, 2)) ?> h</li>
  <li>Heures totales (H_t): <?= esc_html(number_format($calc['H_t'] ?? 0, 2)) ?> h</li>
  <li>Montant brut: <?= esc_html(number_format($calc['M_brut'] ?? 0, 2)) ?> €</li>
  <li>Prime usage (6%): <?= esc_html(number_format($calc['prime'] ?? 0, 2)) ?> €</li>
  <li>Congés payés (12%): <?= esc_html(number_format($calc['cp'] ?? 0, 2)) ?> €</li>
  <li><strong>Total à verser</strong>: <?= esc_html(number_format($calc['total'] ?? 0, 2)) ?> €</li>
</ul>

<h2>Durée du travail</h2>
<p>La durée hebdomadaire moyenne de travail est fixée à <?= esc_html(number_format($calc['intensite'] ?? 0, 2)) ?> heures.
Le contrat est conclu pour la période indiquée ci-dessus.</p>

<p style="margin-top:40px">Fait à <?= esc_html($org['ville'] ?? '') ?>, le <?= date('d/m/Y') ?>.</p>
<p>Signature Organisme&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Signature Formateur</p>
</body>
</html>
