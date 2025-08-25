<?php
/**
 * Template HTML simple de l'avenant – à convertir en PDF.
 * Variables: $contrat, $nouvelle_mission, $calc
 */
?>
<!doctype html>
<html lang="fr">
<head><meta charset="utf-8"><title>Avenant au CDDU</title></head>
<body>
<h1>Avenant au CDDU</h1>
<p>Référence du contrat initial: <?= esc_html($contrat['reference'] ?? 'N/A') ?></p>
<h2>Nouvelles modalités</h2>
<ul>
  <li>Période: <?= esc_html($nouvelle_mission['date_debut'] ?? '') ?> → <?= esc_html($nouvelle_mission['date_fin'] ?? '') ?></li>
  <li>Volume animation (H_a): <?= esc_html($nouvelle_mission['H_a'] ?? '') ?> h</li>
  <li>Taux horaire: <?= esc_html($nouvelle_mission['taux_horaire'] ?? '') ?> €</li>
</ul>
<h2>Récap recalculé</h2>
<ul>
  <li>H_p: <?= esc_html(number_format($calc['H_p'] ?? 0, 2)) ?> h</li>
  <li>H_t: <?= esc_html(number_format($calc['H_t'] ?? 0, 2)) ?> h</li>
  <li>M_brut: <?= esc_html(number_format($calc['M_brut'] ?? 0, 2)) ?> €</li>
  <li>Prime usage (6%): <?= esc_html(number_format($calc['prime'] ?? 0, 2)) ?> €</li>
  <li>Congés payés (12%): <?= esc_html(number_format($calc['cp'] ?? 0, 2)) ?> €</li>
  <li><strong>Total</strong>: <?= esc_html(number_format($calc['total'] ?? 0, 2)) ?> €</li>
</ul>
<p style="margin-top:40px">Fait le <?= date('d/m/Y') ?>.</p>
</body>
</html>
