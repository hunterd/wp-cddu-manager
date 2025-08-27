<?php
/**
 * Simple HTML template for the CDDU contract - English, translatable strings
 * Variables expected: $org, $instructor, $mission, $calc
 */
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?php echo esc_html__('CDDU Contract', 'wp-cddu-manager'); ?></title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
</head>
<body>
<h1><?php echo esc_html__('CDDU Contract', 'wp-cddu-manager'); ?></h1>
<h2><?php echo esc_html__('Organization', 'wp-cddu-manager'); ?></h2>
<p><?= esc_html($org['name'] ?? $org['denomination'] ?? '') ?><br>
<?= esc_html($org['address'] ?? $org['adresse'] ?? '') ?></p>

<h2><?php echo esc_html__('Instructor', 'wp-cddu-manager'); ?></h2>
<p><?= esc_html($instructor['full_name'] ?? $instructor['nom_prenom'] ?? (($instructor['prenom'] ?? '') . ' ' . ($instructor['nom'] ?? ''))) ?><br>
<?= esc_html($instructor['address'] ?? $instructor['adresse'] ?? '') ?></p>

<h2><?php echo esc_html__('Mission', 'wp-cddu-manager'); ?></h2>
<ul>
  <li><?php echo esc_html__('Action', 'wp-cddu-manager'); ?>: <?= esc_html($mission['action'] ?? '') ?></li>
  <li><?php echo esc_html__('Period', 'wp-cddu-manager'); ?>: <?= esc_html($mission['start_date'] ?? $mission['date_debut'] ?? '') ?> &#8594; <?= esc_html($mission['end_date'] ?? $mission['date_fin'] ?? '') ?></li>
  <li><?php echo esc_html__('Annual hours (H_a)', 'wp-cddu-manager'); ?>: <?= esc_html($mission['annual_hours'] ?? $mission['H_a'] ?? '') ?> h</li>
  <li><?php echo esc_html__('Hourly rate', 'wp-cddu-manager'); ?>: <?= esc_html($mission['hourly_rate'] ?? $mission['taux_horaire'] ?? '') ?> &euro;</li>
  <li><?php echo esc_html__('Weekly intensity', 'wp-cddu-manager'); ?>: <?= esc_html(number_format($calc['intensity'] ?? $calc['intensite'] ?? 0, 2)) ?> h/wk</li>
</ul>

<h2><?php echo esc_html__('Remuneration', 'wp-cddu-manager'); ?></h2>
<ul>
  <li><?php echo esc_html__('Preparation hours (H_p)', 'wp-cddu-manager'); ?>: <?= esc_html(number_format($calc['H_p'] ?? $calc['hp'] ?? 0, 2)) ?> h</li>
  <li><?php echo esc_html__('Total hours (H_t)', 'wp-cddu-manager'); ?>: <?= esc_html(number_format($calc['H_t'] ?? $calc['ht'] ?? 0, 2)) ?> h</li>
  <li><?php echo esc_html__('Gross amount', 'wp-cddu-manager'); ?>: <?= esc_html(number_format($calc['M_brut'] ?? $calc['gross'] ?? 0, 2)) ?> &euro;</li>
  <li><?php echo esc_html__('Usage bonus (6%)', 'wp-cddu-manager'); ?>: <?= esc_html(number_format($calc['prime'] ?? $calc['bonus'] ?? 0, 2)) ?> &euro;</li>
  <li><?php echo esc_html__('Paid leave (12%)', 'wp-cddu-manager'); ?>: <?= esc_html(number_format($calc['cp'] ?? $calc['paid_leave'] ?? 0, 2)) ?> &euro;</li>
  <li><strong><?php echo esc_html__('Total payable', 'wp-cddu-manager'); ?></strong>: <?= esc_html(number_format($calc['total'] ?? 0, 2)) ?> &euro;</li>
</ul>

<h2><?php echo esc_html__('Work duration', 'wp-cddu-manager'); ?></h2>
<p><?php echo esc_html__('Average weekly working time is set to', 'wp-cddu-manager'); ?> <?= esc_html(number_format($calc['intensity'] ?? $calc['intensite'] ?? 0, 2)) ?> <?php echo esc_html__('hours.', 'wp-cddu-manager'); ?>
<?php echo esc_html__('The contract is concluded for the period shown above.', 'wp-cddu-manager'); ?></p>

<p><?php echo esc_html__('Organization daily working hours:', 'wp-cddu-manager'); ?> <?= esc_html(number_format($calc['daily_working_hours'] ?? 7, 1)) ?> <?php echo esc_html__('hours per day.', 'wp-cddu-manager'); ?>
<?php echo esc_html__('Total working days needed:', 'wp-cddu-manager'); ?> <?= esc_html(number_format($calc['working_days'] ?? 0, 1)) ?> <?php echo esc_html__('days.', 'wp-cddu-manager'); ?></p>

<p style="margin-top:40px"><?php echo esc_html__('Done at', 'wp-cddu-manager'); ?> <?= esc_html($org['ville'] ?? '') ?>, <?php echo date('d/m/Y'); ?>.</p>
<p><?php echo esc_html__('Organization signature', 'wp-cddu-manager'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo esc_html__('Instructor signature', 'wp-cddu-manager'); ?></p>
</body>
</html>
