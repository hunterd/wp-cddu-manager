<?php
/**
 * Template for the Create Contract form
 * 
 * @var array $organizations Array of organization posts
 * @var array $instructors Array of instructor users  
 * @var array $missions Array of mission posts
 */
?>

<div class="wrap">
    <h1><?php echo esc_html__('Create CDDU Contract', 'wp-cddu-manager'); ?></h1>
    
    <div id="contract-notifications" class="cddu-notifications-container"></div>
    
    <form id="contract-form">
        <?php wp_nonce_field('cddu_contract_nonce', 'contract_nonce'); ?>
        
        <!-- Contract type is fixed to 'contract' for CDDU creation -->
        <input type="hidden" id="contract_type" name="contract_type" value="contract" />

        <div id="contract-form-sections">
        <div class="cddu-form-section">
            <h2><?php echo esc_html__('Organization', 'wp-cddu-manager'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="organization_id"><?php echo esc_html__('Select Organization', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <select id="organization_id" name="organization_id" class="regular-text">
                            <option value=""><?php echo esc_html__('-- Select Organization --', 'wp-cddu-manager'); ?></option>
                            <?php foreach ($organizations as $org): ?>
                                <option value="<?php echo esc_attr($org->ID); ?>">
                                    <?php echo esc_html($org->post_title); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
        </div>

        <div class="cddu-form-section">
            <h2><?php echo esc_html__('Instructor', 'wp-cddu-manager'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="instructor_user_id"><?php echo esc_html__('Select Instructor', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <select id="instructor_user_id" name="instructor_user_id" class="regular-text">
                            <option value=""><?php echo esc_html__('-- Select Instructor --', 'wp-cddu-manager'); ?></option>
                            <?php foreach ($instructors as $instructor): ?>
                                <option value="<?php echo esc_attr($instructor->ID); ?>">
                                    <?php echo esc_html($instructor->display_name . ' (' . $instructor->user_email . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
        </div>

        <div class="cddu-form-section">
            <h2><?php echo esc_html__('Mission Details', 'wp-cddu-manager'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="mission_id"><?php echo esc_html__('Select Mission (Optional)', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <select id="mission_id" name="mission_id" class="regular-text">
                            <option value=""><?php echo esc_html__('-- Select Mission or fill manually --', 'wp-cddu-manager'); ?></option>
                            <?php foreach ($missions as $mission): ?>
                                <option value="<?php echo esc_attr($mission->ID); ?>">
                                    <?php echo esc_html($mission->post_title); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="action"><?php echo esc_html__('Training Action', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="action" name="action" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="location"><?php echo esc_html__('Location', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="location" name="location" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="annual_hours"><?php echo esc_html__('Annual Hours (H_a)', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <input type="number" step="0.01" id="annual_hours" name="annual_hours" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="hourly_rate"><?php echo esc_html__('Hourly Rate (€)', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <input type="number" step="0.01" id="hourly_rate" name="hourly_rate" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="start_date"><?php echo esc_html__('Start Date', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <input type="date" id="start_date" name="start_date" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="end_date"><?php echo esc_html__('End Date', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <input type="date" id="end_date" name="end_date" class="regular-text" />
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="button" id="calculate-btn" class="button button-secondary">
                    <?php echo esc_html__('Calculate Values', 'wp-cddu-manager'); ?>
                </button>
            </p>
        </div>

        <div class="cddu-form-section">
            <h2><?php echo esc_html__('Contract Content', 'wp-cddu-manager'); ?></h2>
            <p class="description">
                <?php echo esc_html__('Customize the contract content using the rich editor below. You can use the following variables for interpolation:', 'wp-cddu-manager'); ?>
            </p>
            
            <div class="contract-variables-helper">
                <h4><?php echo esc_html__('Available Variables:', 'wp-cddu-manager'); ?></h4>
                <div class="variables-grid">
                    <div class="variable-group">
                        <strong><?php echo esc_html__('Organization:', 'wp-cddu-manager'); ?></strong>
                        <ul>
                            <li><code>{{org.name}}</code> - <?php echo esc_html__('Organization name', 'wp-cddu-manager'); ?></li>
                            <li><code>{{org.address}}</code> - <?php echo esc_html__('Organization address', 'wp-cddu-manager'); ?></li>
                            <li><code>{{org.city}}</code> - <?php echo esc_html__('Organization city', 'wp-cddu-manager'); ?></li>
                        </ul>
                    </div>
                    <div class="variable-group">
                        <strong><?php echo esc_html__('Instructor:', 'wp-cddu-manager'); ?></strong>
                        <ul>
                            <li><code>{{instructor.full_name}}</code> - <?php echo esc_html__('Instructor full name', 'wp-cddu-manager'); ?></li>
                            <li><code>{{instructor.email}}</code> - <?php echo esc_html__('Instructor email', 'wp-cddu-manager'); ?></li>
                            <li><code>{{instructor.address}}</code> - <?php echo esc_html__('Instructor address', 'wp-cddu-manager'); ?></li>
                        </ul>
                    </div>
                    <div class="variable-group">
                        <strong><?php echo esc_html__('Mission:', 'wp-cddu-manager'); ?></strong>
                        <ul>
                            <li><code>{{mission.action}}</code> - <?php echo esc_html__('Training action', 'wp-cddu-manager'); ?></li>
                            <li><code>{{mission.location}}</code> - <?php echo esc_html__('Location', 'wp-cddu-manager'); ?></li>
                            <li><code>{{mission.start_date}}</code> - <?php echo esc_html__('Start date', 'wp-cddu-manager'); ?></li>
                            <li><code>{{mission.end_date}}</code> - <?php echo esc_html__('End date', 'wp-cddu-manager'); ?></li>
                            <li><code>{{mission.annual_hours}}</code> - <?php echo esc_html__('Annual hours', 'wp-cddu-manager'); ?></li>
                            <li><code>{{mission.hourly_rate}}</code> - <?php echo esc_html__('Hourly rate', 'wp-cddu-manager'); ?></li>
                        </ul>
                    </div>
                    <div class="variable-group">
                        <strong><?php echo esc_html__('Calculations:', 'wp-cddu-manager'); ?></strong>
                        <ul>
                            <li><code>{{calc.H_p}}</code> - <?php echo esc_html__('Preparation hours', 'wp-cddu-manager'); ?></li>
                            <li><code>{{calc.H_t}}</code> - <?php echo esc_html__('Total hours', 'wp-cddu-manager'); ?></li>
                            <li><code>{{calc.M_brut}}</code> - <?php echo esc_html__('Gross amount', 'wp-cddu-manager'); ?></li>
                            <li><code>{{calc.prime}}</code> - <?php echo esc_html__('Usage bonus', 'wp-cddu-manager'); ?></li>
                            <li><code>{{calc.cp}}</code> - <?php echo esc_html__('Paid leave', 'wp-cddu-manager'); ?></li>
                            <li><code>{{calc.total}}</code> - <?php echo esc_html__('Total amount', 'wp-cddu-manager'); ?></li>
                            <li><code>{{calc.intensity}}</code> - <?php echo esc_html__('Weekly intensity', 'wp-cddu-manager'); ?></li>
                        </ul>
                    </div>
                    <div class="variable-group">
                        <strong><?php echo esc_html__('General:', 'wp-cddu-manager'); ?></strong>
                        <ul>
                            <li><code>{{current_date}}</code> - <?php echo esc_html__('Current date', 'wp-cddu-manager'); ?></li>
                            <li><code>{{contract_date}}</code> - <?php echo esc_html__('Contract date', 'wp-cddu-manager'); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="template-management">
                <h4><?php echo esc_html__('Template Management:', 'wp-cddu-manager'); ?></h4>
                <div class="template-actions">
                    <div class="template-load">
                        <select id="template-selector" class="regular-text">
                            <option value=""><?php echo esc_html__('-- Select Template --', 'wp-cddu-manager'); ?></option>
                        </select>
                        <button type="button" id="load-template-btn" class="button button-secondary">
                            <?php echo esc_html__('Load Template', 'wp-cddu-manager'); ?>
                        </button>
                    </div>
                    <div class="template-save">
                        <input type="text" id="template-name" class="regular-text" placeholder="<?php echo esc_attr__('Template name...', 'wp-cddu-manager'); ?>" />
                        <button type="button" id="save-template-btn" class="button button-secondary">
                            <?php echo esc_html__('Save as Template', 'wp-cddu-manager'); ?>
                        </button>
                    </div>
                </div>
            </div>

            <?php
            // Default contract content template
            $default_content = '
<h1>CONTRAT DE TRAVAIL À DURÉE DÉTERMINÉE D\'USAGE (CDDU)</h1>

<h2>ORGANISATION</h2>
<p><strong>{{org.name}}</strong><br>
{{org.address}}</p>

<h2>FORMATEUR</h2>
<p><strong>{{instructor.full_name}}</strong><br>
Email: {{instructor.email}}<br>
Adresse: {{instructor.address}}</p>

<h2>MISSION DE FORMATION</h2>
<ul>
    <li><strong>Action de formation:</strong> {{mission.action}}</li>
    <li><strong>Lieu:</strong> {{mission.location}}</li>
    <li><strong>Période:</strong> du {{mission.start_date}} au {{mission.end_date}}</li>
    <li><strong>Heures annuelles (H_a):</strong> {{mission.annual_hours}} heures</li>
    <li><strong>Taux horaire:</strong> {{mission.hourly_rate}} €</li>
    <li><strong>Intensité hebdomadaire:</strong> {{calc.intensity}} heures/semaine</li>
</ul>

<h2>RÉMUNÉRATION</h2>
<table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd;">
    <tr style="background-color: #f9f9f9;">
        <td style="padding: 8px; border: 1px solid #ddd;"><strong>Élément</strong></td>
        <td style="padding: 8px; border: 1px solid #ddd;"><strong>Valeur</strong></td>
    </tr>
    <tr>
        <td style="padding: 8px; border: 1px solid #ddd;">Heures de préparation (H_p)</td>
        <td style="padding: 8px; border: 1px solid #ddd;">{{calc.H_p}} heures</td>
    </tr>
    <tr>
        <td style="padding: 8px; border: 1px solid #ddd;">Heures totales (H_t)</td>
        <td style="padding: 8px; border: 1px solid #ddd;">{{calc.H_t}} heures</td>
    </tr>
    <tr>
        <td style="padding: 8px; border: 1px solid #ddd;">Montant brut</td>
        <td style="padding: 8px; border: 1px solid #ddd;">{{calc.M_brut}} €</td>
    </tr>
    <tr>
        <td style="padding: 8px; border: 1px solid #ddd;">Prime d\'usage (6%)</td>
        <td style="padding: 8px; border: 1px solid #ddd;">{{calc.prime}} €</td>
    </tr>
    <tr>
        <td style="padding: 8px; border: 1px solid #ddd;">Congés payés (12%)</td>
        <td style="padding: 8px; border: 1px solid #ddd;">{{calc.cp}} €</td>
    </tr>
    <tr style="background-color: #f9f9f9; font-weight: bold;">
        <td style="padding: 8px; border: 1px solid #ddd;">TOTAL À PAYER</td>
        <td style="padding: 8px; border: 1px solid #ddd;">{{calc.total}} €</td>
    </tr>
</table>

<h2>DURÉE DU TRAVAIL</h2>
<p>Le temps de travail hebdomadaire moyen est fixé à <strong>{{calc.intensity}} heures</strong>.</p>
<p>Le contrat est conclu pour la période indiquée ci-dessus.</p>

<p style="margin-top: 40px;">
Fait à {{org.city}}, le {{current_date}}.
</p>

<table style="width: 100%; margin-top: 40px;">
    <tr>
        <td style="text-align: center; width: 50%;">
            <strong>Signature de l\'organisation</strong>
        </td>
        <td style="text-align: center; width: 50%;">
            <strong>Signature du formateur</strong>
        </td>
    </tr>
    <tr>
        <td style="height: 80px; border-bottom: 1px solid #000; width: 50%;"></td>
        <td style="height: 80px; border-bottom: 1px solid #000; width: 50%;"></td>
    </tr>
</table>
';

            wp_editor(
                trim($default_content),
                'contract_content',
                [
                    'textarea_name' => 'contract_content',
                    'textarea_rows' => 25,
                    'editor_height' => 600,
                    'media_buttons' => false,
                    'teeny' => false,
                    'quicktags' => true,
                    'tinymce' => [
                        'toolbar1' => 'formatselect,bold,italic,underline,strikethrough,|,alignleft,aligncenter,alignright,alignjustify,|,bullist,numlist,|,link,unlink',
                        'toolbar2' => 'undo,redo,|,table,|,forecolor,backcolor,|,hr,|,code,fullscreen',
                        'block_formats' => 'Paragraph=p;Heading 1=h1;Heading 2=h2;Heading 3=h3;Heading 4=h4;Preformatted=pre',
                        'content_css' => false
                    ]
                ]
            );
            ?>
            
            <p class="description" style="margin-top: 10px;">
                <?php echo esc_html__('Use the variables above to dynamically insert contract data. The content will be processed when generating the PDF.', 'wp-cddu-manager'); ?>
            </p>
        </div>

        <div id="calculation-results" class="cddu-form-section" style="display:none;">
            <h2><?php echo esc_html__('Calculated Values', 'wp-cddu-manager'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php echo esc_html__('Number of weeks', 'wp-cddu-manager'); ?></th>
                    <td id="calc-nb-weeks">-</td>
                </tr>
                <tr>
                    <th scope="row"><?php echo esc_html__('Weekly intensity', 'wp-cddu-manager'); ?></th>
                    <td id="calc-intensity">-</td>
                </tr>
                <tr>
                    <th scope="row"><?php echo esc_html__('Preparation hours (H_p)', 'wp-cddu-manager'); ?></th>
                    <td id="calc-hp">-</td>
                </tr>
                <tr>
                    <th scope="row"><?php echo esc_html__('Total hours (H_t)', 'wp-cddu-manager'); ?></th>
                    <td id="calc-ht">-</td>
                </tr>
                <tr>
                    <th scope="row"><?php echo esc_html__('Daily intensity', 'wp-cddu-manager'); ?></th>
                    <td id="calc-daily-intensity">-</td>
                </tr>
                <tr>
                    <th scope="row"><?php echo esc_html__('Working days needed', 'wp-cddu-manager'); ?></th>
                    <td id="calc-working-days">-</td>
                </tr>
                <tr>
                    <th scope="row"><?php echo esc_html__('Daily working hours (Org)', 'wp-cddu-manager'); ?></th>
                    <td id="calc-daily-working-hours">-</td>
                </tr>
                <tr>
                    <th scope="row"><?php echo esc_html__('Gross amount', 'wp-cddu-manager'); ?></th>
                    <td id="calc-gross">-</td>
                </tr>
                <tr>
                    <th scope="row"><?php echo esc_html__('Usage bonus (6%)', 'wp-cddu-manager'); ?></th>
                    <td id="calc-bonus">-</td>
                </tr>
                <tr>
                    <th scope="row"><?php echo esc_html__('Paid leave (12%)', 'wp-cddu-manager'); ?></th>
                    <td id="calc-paid-leave">-</td>
                </tr>
                <tr>
                    <th scope="row"><strong><?php echo esc_html__('Total amount', 'wp-cddu-manager'); ?></strong></th>
                    <td><strong id="calc-total">-</strong></td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="button" id="generate-contract-btn" class="button button-primary">
                    <?php echo esc_html__('Generate Contract', 'wp-cddu-manager'); ?>
                </button>
                <button type="button" id="preview-contract-btn" class="button button-secondary">
                    <?php echo esc_html__('Preview Contract', 'wp-cddu-manager'); ?>
                </button>
            </p>
        </div>
        </div> <!-- End contract-form-sections -->
    </form>
</div>

<style>
.cddu-form-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
}
.cddu-form-section h2 {
    margin-top: 0;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}
#calculation-results {
    background: #f9f9f9;
}
.contract-variables-helper {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 15px;
    margin: 15px 0;
}
.contract-variables-helper h4 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #495057;
}
.variables-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}
.variable-group {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    padding: 15px;
}
.variable-group strong {
    display: block;
    margin-bottom: 10px;
    color: #212529;
    font-size: 14px;
}
.variable-group ul {
    margin: 0;
    padding-left: 0;
    list-style: none;
}
.variable-group li {
    margin-bottom: 5px;
    font-size: 13px;
    line-height: 1.4;
}
.variable-group code {
    background: #e9ecef;
    color: #495057;
    padding: 2px 4px;
    border-radius: 3px;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 12px;
    margin-right: 8px;
    min-width: 120px;
    display: inline-block;
}
.variable-group li:hover {
    background: #f8f9fa;
    border-radius: 3px;
    padding: 2px 4px;
    margin: 2px -4px;
    cursor: pointer;
}
.template-management {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 4px;
    padding: 15px;
    margin: 15px 0;
}
.template-management h4 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #856404;
}
.template-actions {
    display: flex;
    gap: 20px;
    align-items: center;
    flex-wrap: wrap;
}
.template-load, .template-save {
    display: flex;
    gap: 10px;
    align-items: center;
}
.template-load select, .template-save input {
    min-width: 200px;
}
@media (max-width: 768px) {
    .template-actions {
        flex-direction: column;
        align-items: stretch;
    }
    .template-load, .template-save {
        flex-direction: column;
        gap: 5px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Contract type is fixed to 'contract' for CDDU creation
    
    // Load mission data when mission is selected
</script>
