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
    
    <form id="contract-form">
        <?php wp_nonce_field('cddu_contract_nonce', 'contract_nonce'); ?>
        
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
</style>
