<?php
/**
 * Template for NEXT FORMA detailed addendum
 * This template provides a comprehensive structure for contract addendums
 * with specific formatting for NEXT FORMA organization
 */

// Security check
if (!defined('WPINC')) {
    die;
}

// Available variables:
// $addendum - addendum data
// $contract - original contract data
// $organization - organization details
// $instructor - instructor details
// $calculations - updated calculations
// $addendum_number - addendum sequence number
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Avenant n° <?php echo $addendum_number; ?> au Contrat de Travail à Durée Déterminée</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 20px;
        }
        .header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .parties {
            margin-bottom: 30px;
        }
        .party {
            margin-bottom: 20px;
        }
        .articles {
            margin-bottom: 30px;
        }
        .article {
            margin-bottom: 25px;
        }
        .article-title {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 10px;
        }
        .work-breakdown {
            margin: 15px 0;
        }
        .work-breakdown ul {
            margin: 5px 0;
            padding-left: 20px;
        }
        .schedule-table {
            margin: 15px 0;
        }
        .remuneration {
            margin: 15px 0;
        }
        .signatures {
            margin-top: 50px;
            display: table;
            width: 100%;
        }
        .signature-block {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            text-align: center;
        }
        .signature-space {
            height: 80px;
        }
        .blank-line {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 150px;
            margin: 0 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        Avenant n° <?php echo $addendum_number; ?> au Contrat de Travail à Durée Déterminée
    </div>

    <div class="parties">
        <p><strong>ENTRE LES SOUSSIGNÉS,</strong></p>

        <div class="party">
            <p>La société <strong><?php echo esc_html($organization['name']); ?></strong>, société à responsabilité limitée immatriculée au Registre du Commerce et des Sociétés de <?php echo esc_html($organization['registration_city'] ?? 'Paris'); ?> sous le numéro <?php echo esc_html($organization['registration_number'] ?? '518 333 109'); ?>,<br>
            Dont le siège social est situé au <?php echo esc_html($organization['address']); ?><br>
            Prise en la personne de <?php echo esc_html($organization['legal_representative_title'] ?? 'Monsieur'); ?> <?php echo esc_html($organization['legal_representative'] ?? 'Igal OINOUNOU'); ?> en qualité de <?php echo esc_html($organization['legal_representative_role'] ?? 'Gérant'); ?>,</p>

            <p style="text-align: right; margin-right: 100px;"><strong>D'une part,</strong></p>
        </div>

        <div class="party">
            <p>Et</p>

            <p><?php echo esc_html($instructor['civility'] ?? 'Monsieur'); ?> <?php echo esc_html($instructor['first_name'] . ' ' . $instructor['last_name']); ?><br>
            Né le <?php echo esc_html($instructor['birth_date'] ?? '[...]'); ?> à <?php echo esc_html($instructor['birth_place'] ?? '[...]'); ?><br>
            Demeurant : <?php echo esc_html($instructor['address'] ?? '[...]'); ?><br>
            N° Sécurité Sociale : <?php echo esc_html($instructor['social_security_number'] ?? '[...]'); ?></p>

            <p style="text-align: right; margin-right: 100px;"><strong>D'autre part,</strong></p>
        </div>

        <p><strong>Il a été arrêté et convenu ce qui suit :</strong></p>
    </div>

    <div class="articles">
        <div class="article">
            <div class="article-title">ARTICLE 1 : <?php echo esc_html($addendum['article_1_title'] ?? 'Renouvellement du contrat de travail à durée déterminée'); ?></div>
            <p><?php echo wp_kses_post($addendum['article_1_content'] ?? 
                'Le présent contrat de travail à durée déterminée, conclu le ' . 
                date('j F Y', strtotime($contract['start_date'])) . 
                ', pour une durée déterminée courant jusqu\'au ' . 
                date('j F Y', strtotime($contract['end_date'])) . 
                ', sera renouvelé jusqu\'au ' . 
                date('j F Y', strtotime($addendum['new_end_date'])) . '.'
            ); ?></p>
        </div>

        <div class="article">
            <div class="article-title">ARTICLE 2 : Révision de l'article 3 intitulé « Durée du travail »</div>
            <p>L'article 3 du contrat de travail à durée déterminée est révisé, et désormais rédigé comme suit :</p>
            
            <p>« À la demande de <?php echo esc_html($instructor['civility'] ?? 'Monsieur'); ?> <?php echo esc_html($instructor['last_name']); ?>, la durée de travail est fixée comme suit :</p>

            <div class="work-breakdown">
                <?php if (isset($addendum['monthly_breakdown']) && is_array($addendum['monthly_breakdown'])): ?>
                    <?php foreach ($addendum['monthly_breakdown'] as $month => $breakdown): ?>
                        <p>- <?php echo esc_html($breakdown['total_hours']); ?> heures pour le mois de <?php echo esc_html($month); ?>, décomposée comme suit :</p>
                        <ul>
                            <li><?php echo esc_html($breakdown['animation_hours']); ?> heures d'Acte de formation (AF),</li>
                            <li><?php echo esc_html($breakdown['preparation_hours']); ?> heures de Préparation et Recherches (PR).</li>
                        </ul>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>- <span class="blank-line"></span> heures pour le mois de <span class="blank-line"></span>, décomposée comme suit :</p>
                    <ul>
                        <li><span class="blank-line"></span> heures d'Acte de formation (AF),</li>
                        <li><span class="blank-line"></span> heures de Préparation et Recherches (PR).</li>
                    </ul>
                <?php endif; ?>
            </div>

            <p>La répartition de la durée de travail sur les semaines de chaque mois est prévue comme suit :</p>

            <div class="schedule-table">
                <?php if (isset($addendum['weekly_schedule']) && is_array($addendum['weekly_schedule'])): ?>
                    <?php foreach ($addendum['weekly_schedule'] as $week): ?>
                        <p>- Semaine du <?php echo esc_html($week['start_date']); ?> au <?php echo esc_html($week['end_date']); ?> : <?php echo esc_html($week['af_hours']); ?> heures d'AF et <?php echo esc_html($week['pr_hours']); ?> heures de PR,</p>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php for ($i = 1; $i <= 8; $i++): ?>
                        <p>- Semaine du <span class="blank-line"></span> au <span class="blank-line"></span> : <span class="blank-line"></span> heures d'AF et <span class="blank-line"></span> heures de PR,</p>
                    <?php endfor; ?>
                <?php endif; ?>
            </div>

            <p>Le cas échéant, des heures complémentaires pourront être réalisées à la demande de la société <?php echo esc_html($organization['name']); ?>, et ce, dans la limite d'un tiers de la durée mensuelle de travail susvisée, et moyennant le paiement des majorations de salaire conventionnelles en vigueur.</p>

            <p>Les horaires de travail pour chaque journée travaillée seront remis à <?php echo esc_html($instructor['civility'] ?? 'Monsieur'); ?> <?php echo esc_html($instructor['last_name']); ?> par note d'information remise en main propre ou par courrier électronique ».</p>
        </div>

        <div class="article">
            <div class="article-title">ARTICLE 3 : Révision de l'article 4 intitulé « Classification conventionnelle - Rémunération »</div>
            <p>L'article 4 du contrat de travail à durée déterminée est révisé, et désormais rédigé comme suit :</p>

            <p>« En application des dispositions de la Convention Collective Nationale des Organismes de Formation, l'emploi de <?php echo esc_html($instructor['job_title'] ?? 'formateur'); ?> occupé par <?php echo esc_html($instructor['civility'] ?? 'Monsieur'); ?> <?php echo esc_html($instructor['last_name']); ?> est classé au <?php echo esc_html($instructor['classification_level'] ?? 'Palier 9'); ?>, coefficient <?php echo esc_html($instructor['coefficient'] ?? '200'); ?>.</p>

            <div class="remuneration">
                <p>La rémunération mensuelle brute de <?php echo esc_html($instructor['civility'] ?? 'Monsieur'); ?> <?php echo esc_html($instructor['last_name']); ?> est définie à partir d'un taux horaire brut de <?php echo esc_html(number_format($calculations['hourly_rate'], 2, ',', ' ')); ?> €, comme suit :</p>

                <?php if (isset($addendum['monthly_remuneration']) && is_array($addendum['monthly_remuneration'])): ?>
                    <?php foreach ($addendum['monthly_remuneration'] as $month => $remun): ?>
                        <p><?php echo esc_html($month); ?> : <?php echo esc_html(number_format($calculations['hourly_rate'], 2, ',', ' ')); ?> € x <?php echo esc_html($remun['hours']); ?> heures = <?php echo esc_html(number_format($remun['amount'], 2, ',', ' ')); ?> €</p>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p><span class="blank-line"></span> : <?php echo esc_html(number_format($calculations['hourly_rate'], 2, ',', ' ')); ?> € x <span class="blank-line"></span> heures = <span class="blank-line"></span> €</p>
                <?php endif; ?>

                <p>Au terme du présent contrat de travail, <?php echo esc_html($instructor['civility'] ?? 'Monsieur'); ?> <?php echo esc_html($instructor['last_name']); ?> pourra bénéficier d'une indemnité dite d'usage égale à 6% de la rémunération brute versée au salarié au titre du présent contrat de travail à durée déterminée, dès lors que ledit contrat n'est pas poursuivi par un contrat de travail à durée indéterminée.</p>

                <p><?php echo esc_html($instructor['civility'] ?? 'Monsieur'); ?> <?php echo esc_html($instructor['last_name']); ?> bénéficiera également d'une indemnité de congés payés et de congés mobiles égale à 12 % des rémunérations incluses dans l'assiette de calcul des congés payés telle que définie par les dispositions légales et conventionnelles en vigueur ».</p>
            </div>
        </div>

        <div class="article">
            <div class="article-title">ARTICLE 4 : Prise d'effet</div>
            <p>Le présent avenant prendra effet à compter du <?php echo esc_html($addendum['effective_date'] ?? '_______'); ?></p>
        </div>
    </div>

    <div style="margin-top: 40px;">
        <p>Fait à <?php echo esc_html($organization['city'] ?? 'Paris'); ?> en deux (2) exemplaires, le <?php echo esc_html($addendum['signature_date'] ?? '_______'); ?>, dont un est remis à chacun des deux (2) signataires,</p>
    </div>

    <div class="signatures">
        <div class="signature-block">
            <p><strong>Pour la société <?php echo esc_html($organization['name']); ?></strong></p>
            <p><?php echo esc_html($organization['legal_representative_title'] ?? 'Monsieur'); ?> <?php echo esc_html($organization['legal_representative'] ?? 'Igal OINOUNOU'); ?></p>
            <p><?php echo esc_html($organization['legal_representative_role'] ?? 'Gérant'); ?></p>
            <div class="signature-space"></div>
        </div>
        <div class="signature-block">
            <p><strong><?php echo esc_html($instructor['civility'] ?? 'Monsieur'); ?> <?php echo esc_html($instructor['first_name'] . ' ' . $instructor['last_name']); ?></strong></p>
            <div class="signature-space"></div>
        </div>
    </div>
</body>
</html>
