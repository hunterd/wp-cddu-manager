<?php
/**
 * NEXT FORMA Addendum HTML template – to be converted to PDF.
 * Variables: $contract, $instructor, $organization, $mission, $calc, $addendum_data
 */
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Avenant n° <?= esc_html($addendum_data['number'] ?? '1') ?> au Contrat de Travail à Durée Déterminée</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 40px;
            color: #000;
        }
        h1 {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 40px;
        }
        h2 {
            font-size: 14px;
            font-weight: bold;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        .parties {
            margin-bottom: 30px;
        }
        .partie {
            margin-bottom: 20px;
        }
        .article {
            margin-bottom: 25px;
        }
        .article-title {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 10px;
        }
        .indent {
            margin-left: 40px;
        }
        .signature-section {
            margin-top: 60px;
            page-break-inside: avoid;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            padding: 20px;
            vertical-align: top;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            height: 80px;
            margin-top: 20px;
        }
        ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        li {
            margin-bottom: 5px;
        }
        .hours-breakdown {
            margin: 15px 0;
        }
        .hours-breakdown li {
            margin-bottom: 8px;
        }
        .weekly-schedule {
            margin: 15px 0;
        }
        .weekly-schedule li {
            margin-bottom: 5px;
        }
        .monthly-details {
            margin: 10px 0;
        }
        .amount {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Avenant n° <?= esc_html($addendum_data['number'] ?? '1') ?> au Contrat de Travail à Durée Déterminée</h1>

    <div class="parties">
        <p><strong>ENTRE LES SOUSSIGNÉS,</strong></p>

        <div class="partie">
            <p>La société <strong><?= esc_html($organization['name'] ?? 'NEXT FORMA') ?></strong>, société à responsabilité limitée immatriculée au Registre du Commerce et des Sociétés de <?= esc_html($organization['rcs_city'] ?? 'Paris') ?> sous le numéro <?= esc_html($organization['rcs_number'] ?? '518 333 109') ?>,<br>
            Dont le siège social est situé au <?= esc_html($organization['address'] ?? '77, Rue du Rocher – 75008 PARIS') ?><br>
            Prise en la personne de <?= esc_html($organization['manager_title'] ?? 'Monsieur') ?> <?= esc_html($organization['manager_name'] ?? 'Igal OINOUNOU') ?> en qualité de <?= esc_html($organization['manager_role'] ?? 'Gérant') ?>,</p>
            
            <p style="text-align: right; margin: 20px 0;"><strong>D'une part,</strong></p>
        </div>

        <div class="partie">
            <p>Et</p>
            
            <p><?= esc_html($instructor['gender'] ?? 'Monsieur') ?> <?= esc_html($instructor['full_name'] ?? '[...]') ?><br>
            Né le <?= esc_html($instructor['birth_date'] ?? '[...]') ?> à <?= esc_html($instructor['birth_place'] ?? '[...]') ?><br>
            Demeurant : <?= esc_html($instructor['address'] ?? '[...]') ?><br>
            N° Sécurité Sociale : <?= esc_html($instructor['social_security'] ?? '[...]') ?></p>
            
            <p style="text-align: right; margin: 20px 0;"><strong>D'autre part,</strong></p>
        </div>
    </div>

    <p><strong>Il a été arrêté et convenu ce qui suit :</strong></p>

    <div class="article">
        <div class="article-title">ARTICLE 1 : Renouvellement du contrat de travail à durée déterminée</div>
        <p>Le présent contrat de travail à durée déterminée, conclu le <?= esc_html($contract['original_date'] ?? $addendum_data['original_contract_date'] ?? '2 novembre 2023') ?>, pour une durée déterminée courant jusqu'au <?= esc_html($contract['original_end_date'] ?? $addendum_data['original_end_date'] ?? '31 décembre 2023') ?>, sera renouvelé jusqu'au <?= esc_html($mission['end_date'] ?? $addendum_data['new_end_date'] ?? '4 janvier 2024') ?>.</p>
    </div>

    <div class="article">
        <div class="article-title">ARTICLE 2 : Révision de l'article 3 intitulé « Durée du travail »</div>
        <p>L'article 3 du contrat de travail à durée déterminée est révisé, et désormais rédigé comme suit :</p>
        
        <p>« À la demande de <?= esc_html($instructor['gender'] ?? 'Monsieur') ?> <?= esc_html($instructor['full_name'] ?? '[...]') ?>, la durée de travail est fixée comme suit :</p>
        
        <div class="hours-breakdown">
            <?php if (!empty($addendum_data['monthly_breakdown'])): ?>
                <?php foreach ($addendum_data['monthly_breakdown'] as $month => $data): ?>
                <div class="monthly-details">
                    <p>- <?= esc_html($data['hours']) ?> heures pour le mois de <?= esc_html($month) ?>, décomposée comme suit :</p>
                    <ul>
                        <li><?= esc_html($data['af_hours']) ?> heures d'Acte de formation (AF),</li>
                        <li><?= esc_html($data['pr_hours']) ?> heures de Préparation et Recherches (PR)<?= substr($data['pr_hours'], -1) === '1' && !strpos($data['pr_hours'], '.') ? '.' : ',') ?></li>
                    </ul>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="monthly-details">
                    <p>- <?= esc_html($calc['november_hours'] ?? '15,28') ?> heures pour le mois de novembre 2023, décomposée comme suit :</p>
                    <ul>
                        <li><?= esc_html($calc['november_af'] ?? '11,00') ?> heures d'Acte de formation (AF),</li>
                        <li><?= esc_html($calc['november_pr'] ?? '4,28') ?> heures de Préparation et Recherches (PR),</li>
                    </ul>
                </div>
                
                <div class="monthly-details">
                    <p>- <?= esc_html($calc['december_hours'] ?? '20,83') ?> heures pour le mois de décembre 2023, décomposée comme suit :</p>
                    <ul>
                        <li><?= esc_html($calc['december_af'] ?? '15,00') ?> heures d'Acte de formation (AF),</li>
                        <li><?= esc_html($calc['december_pr'] ?? '5,83') ?> heures de Préparation et Recherches (PR).</li>
                    </ul>
                </div>
                
                <div class="monthly-details">
                    <p>- <?= esc_html($calc['january_hours'] ?? '5,55') ?> heures pour le mois de janvier 2024, décomposée comme suit :</p>
                    <ul>
                        <li><?= esc_html($calc['january_af'] ?? '4,00') ?> heures d'Acte de formation (AF),</li>
                        <li><?= esc_html($calc['january_pr'] ?? '1,55') ?> heure de Préparation et Recherches (PR).</li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <p>La répartition de la durée de travail sur les semaines de chaque mois est prévue comme suit :</p>
        
        <div class="weekly-schedule">
            <?php if (!empty($addendum_data['weekly_schedule'])): ?>
                <?php foreach ($addendum_data['weekly_schedule'] as $week): ?>
                    <p>- Semaine du <?= esc_html($week['start_date']) ?> au <?= esc_html($week['end_date']) ?> : <?= esc_html($week['af_hours']) ?> heures d'AF et <?= esc_html($week['pr_hours']) ?> heures de PR,</p>
                <?php endforeach; ?>
            <?php else: ?>
                <p>- Semaine du _________ au _________ : __ heures d'AF et __ heures de PR,</p>
                <p>- Semaine du _________ au _________ : __ heures d'AF et __ heures de PR,</p>
                <p>- Semaine du _________ au _________ : __ heures d'AF et __ heures de PR,</p>
                <p>- Semaine du _________ au _________ : __ heures d'AF et __ heures de PR,</p>
                <p>- Semaine du _________ au _________ : __ heures d'AF et __ heures de PR,</p>
                <p>- Semaine du _________ au _________ : __ heures d'AF et __ heures de PR,</p>
                <p>- Semaine du _________ au _________ : __ heures d'AF et __ heures de PR,</p>
                <p>- Semaine du _________ au _________ : __ heures d'AF et __ heures de PR,</p>
            <?php endif; ?>
        </div>

        <p>Le cas échéant, des heures complémentaires pourront être réalisées à la demande de la société <?= esc_html($organization['name'] ?? 'NEXT FORMA') ?>, et ce, dans la limite d'un tiers de la durée mensuelle de travail susvisée, et moyennant le paiement des majorations de salaire conventionnelles en vigueur.</p>

        <p>Les horaires de travail pour chaque journée travaillée seront remis à <?= esc_html($instructor['gender'] ?? 'Monsieur') ?> <?= esc_html($instructor['full_name'] ?? '[...]') ?> par note d'information remise en main propre ou par courrier électronique ». </p>
    </div>

    <div class="article">
        <div class="article-title">ARTICLE 3 : Révision de l'article 4 intitulé « Classification conventionnelle - Rémunération »</div>
        <p>L'article 4 du contrat de travail à durée déterminée est révisé, et désormais rédigé comme suit :</p>
        
        <p>« En application des dispositions de la Convention Collective Nationale des Organismes de Formation, l'emploi de <?= esc_html($instructor['job_title'] ?? 'formateur en informatique') ?> occupé par <?= esc_html($instructor['gender'] ?? 'Monsieur') ?> <?= esc_html($instructor['full_name'] ?? '[...]') ?> est classé au <?= esc_html($instructor['classification_level'] ?? 'Palier 9') ?>, coefficient <?= esc_html($instructor['coefficient'] ?? '200') ?>.</p>

        <p>La rémunération mensuelle brute de <?= esc_html($instructor['gender'] ?? 'Monsieur') ?> <?= esc_html($instructor['full_name'] ?? '[...]') ?> est définie à partir d'un taux horaire brut de <?= esc_html($mission['hourly_rate'] ?? '13,18') ?> €, comme suit :</p>

        <div class="monthly-details">
            <?php if (!empty($addendum_data['monthly_breakdown'])): ?>
                <?php foreach ($addendum_data['monthly_breakdown'] as $month => $data): ?>
                    <p><?= esc_html($month) ?> : <?= esc_html($mission['hourly_rate'] ?? '13,17') ?> € x <?= esc_html($data['hours']) ?> heures = <span class="amount"><?= esc_html(number_format(($mission['hourly_rate'] ?? 13.17) * $data['hours'], 2)) ?> €</span></p>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Novembre 2023 : <?= esc_html($mission['hourly_rate'] ?? '13,17') ?> € x <?= esc_html($calc['november_hours'] ?? '15,28') ?> heures = <span class="amount"><?= esc_html($calc['november_amount'] ?? '201,23') ?> €</span></p>
                <p>Décembre 2023 : <?= esc_html($mission['hourly_rate'] ?? '13,17') ?> € x <?= esc_html($calc['december_hours'] ?? '20,83') ?> heures = <span class="amount"><?= esc_html($calc['december_amount'] ?? '274,33') ?> €</span></p>
                <p>Janvier 2024 : <?= esc_html($mission['hourly_rate'] ?? '13,17') ?> € x <?= esc_html($calc['january_hours'] ?? '5,55') ?> heures = <span class="amount"><?= esc_html($calc['january_amount'] ?? '73,09') ?> €</span></p>
            <?php endif; ?>
        </div>

        <p>Au terme du présent contrat de travail, <?= esc_html($instructor['gender'] ?? 'Monsieur') ?> <?= esc_html($instructor['full_name'] ?? '[...]') ?> pourra bénéficier d'une indemnité dite d'usage égale à 6% de la rémunération brute versée au salarié au titre du présent contrat de travail à durée déterminée, dès lors que ledit contrat n'est pas poursuivi par un contrat de travail à durée indéterminée.</p>

        <p><?= esc_html($instructor['gender'] ?? 'Monsieur') ?> <?= esc_html($instructor['full_name'] ?? '[...]') ?> bénéficiera également d'une indemnité de congés payés et de congés mobiles égale à 12 % des rémunérations incluses dans l'assiette de calcul des congés payés telle que définie par les dispositions légales et conventionnelles en vigueur ». </p>
    </div>

    <div class="article">
        <div class="article-title">ARTICLE 4 : Prise d'effet</div>
        <p>Le présent avenant prendra effet à compter du <?= esc_html($addendum_data['effective_date'] ?? '_______') ?> <?= esc_html($addendum_data['effective_year'] ?? '2023') ?></p>
    </div>

    <div class="signature-section">
        <p>Fait à <?= esc_html($organization['city'] ?? 'Paris') ?> en deux (2) exemplaires, le <?= esc_html($addendum_data['signature_date'] ?? '_________') ?> <?= esc_html($addendum_data['signature_year'] ?? '2023') ?>, dont un est remis à chacun des deux (2) signataires,</p>

        <table class="signature-table">
            <tr>
                <td>
                    <p><strong>Pour la société <?= esc_html($organization['name'] ?? 'NEXT FORMA') ?></strong><br>
                    <?= esc_html($organization['manager_title'] ?? 'Monsieur') ?> <?= esc_html($organization['manager_name'] ?? 'Igal OINOUNOU') ?><br>
                    <?= esc_html($organization['manager_role'] ?? 'Gérant') ?></p>
                    <div class="signature-line"></div>
                </td>
                <td>
                    <p><strong><?= esc_html($instructor['gender'] ?? 'Monsieur') ?> <?= esc_html($instructor['full_name'] ?? '[...]') ?></strong></p>
                    <div class="signature-line"></div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
