<?php
/**
 * Simple addendum HTML template – to be converted to PDF.
 * Variables: $contrat, $new_mission, $calc
 */
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title><?php echo esc_html__('Addendum to CDDU', 'wp-cddu-manager'); ?></title></head>
<body>
<h1><?php echo esc_html__('Addendum to CDDU', 'wp-cddu-manager'); ?></h1>
<p><?php echo esc_html__('Original contract reference:', 'wp-cddu-manager'); ?> <?= esc_html($contrat['reference'] ?? 'N/A') ?></p>
<h2><?php echo esc_html__('New terms', 'wp-cddu-manager'); ?></h2>
<ul>
  <li><?php echo esc_html__('Period:', 'wp-cddu-manager'); ?> <?= esc_html($new_mission['start_date'] ?? $new_mission['date_debut'] ?? '') ?> → <?= esc_html($new_mission['end_date'] ?? $new_mission['date_fin'] ?? '') ?></li>
  <li><?php echo esc_html__('Teaching volume (H_a):', 'wp-cddu-manager'); ?> <?= esc_html($new_mission['annual_hours'] ?? $new_mission['H_a'] ?? '') ?> h</li>
  <li><?php echo esc_html__('Hourly rate:', 'wp-cddu-manager'); ?> <?= esc_html($new_mission['hourly_rate'] ?? $new_mission['taux_horaire'] ?? '') ?> €</li>
</ul>
<h2><?php echo esc_html__('Recalculated summary', 'wp-cddu-manager'); ?></h2>
<ul>
  <li><?php echo esc_html__('H_p', 'wp-cddu-manager'); ?>: <?= esc_html(number_format($calc['H_p'] ?? 0, 2)) ?> h</li>
  <li><?php echo esc_html__('H_t', 'wp-cddu-manager'); ?>: <?= esc_html(number_format($calc['H_t'] ?? 0, 2)) ?> h</li>
  <li><?php echo esc_html__('Gross amount (M_brut)', 'wp-cddu-manager'); ?>: <?= esc_html(number_format($calc['M_brut'] ?? 0, 2)) ?> €</li>
  <li><?php echo esc_html__('Usage bonus (6%)', 'wp-cddu-manager'); ?>: <?= esc_html(number_format($calc['prime'] ?? 0, 2)) ?> €</li>
  <li><?php echo esc_html__('Paid leave (12%)', 'wp-cddu-manager'); ?>: <?= esc_html(number_format($calc['cp'] ?? 0, 2)) ?> €</li>
  <li><strong><?php echo esc_html__('Total', 'wp-cddu-manager'); ?></strong>: <?= esc_html(number_format($calc['total'] ?? 0, 2)) ?> €</li>
</ul>
<p style="margin-top:40px"><?php echo esc_html__('Done at', 'wp-cddu-manager'); ?> <?= date('d/m/Y') ?>.</p>
</body>
</html>
