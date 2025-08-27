<?php
/**
 * Template for the Create Addendum form
 * 
 * @var array $organizations Array of organization posts
 * @var array $instructors Array of instructor users  
 * @var array $contracts Array of existing contracts
 */
?>

<div class="wrap">
    <h1><?php echo esc_html__('Create Contract Addendum', 'wp-cddu-manager'); ?></h1>
    
    <!-- Notifications container -->
    <div id="addendum-notifications" class="cddu-notifications-container"></div>
    
    <form id="addendum-form">
        <?php wp_nonce_field('cddu_addendum_nonce', 'addendum_nonce'); ?>
        
        <div class="cddu-form-section">
            <h2><?php echo esc_html__('Base Contract Information', 'wp-cddu-manager'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="original_contract_id"><?php echo esc_html__('Original Contract', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <select id="original_contract_id" name="original_contract_id" class="regular-text">
                            <option value=""><?php echo esc_html__('-- Select Original Contract --', 'wp-cddu-manager'); ?></option>
                            <?php if (!empty($contracts)): ?>
                                <?php foreach ($contracts as $contract): ?>
                                    <option value="<?php echo esc_attr($contract->ID); ?>">
                                        <?php echo esc_html($contract->post_title . ' - ' . get_post_meta($contract->ID, 'instructor_name', true)); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="addendum_number"><?php echo esc_html__('Addendum Number', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="addendum_number" name="addendum_number" class="regular-text" value="1" min="1" />
                    </td>
                </tr>
            </table>
        </div>

        <div class="cddu-form-section">
            <h2><?php echo esc_html__('Organization Details', 'wp-cddu-manager'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="org_name"><?php echo esc_html__('Organization Name', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="org_name" name="org_name" class="regular-text" value="NEXT FORMA" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="org_rcs_city"><?php echo esc_html__('RCS City', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="org_rcs_city" name="org_rcs_city" class="regular-text" value="Paris" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="org_rcs_number"><?php echo esc_html__('RCS Number', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="org_rcs_number" name="org_rcs_number" class="regular-text" value="518 333 109" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="org_address"><?php echo esc_html__('Organization Address', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="org_address" name="org_address" class="regular-text" value="77, Rue du Rocher – 75008 PARIS" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="org_manager_title"><?php echo esc_html__('Manager Title', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <select id="org_manager_title" name="org_manager_title" class="regular-text">
                            <option value="Monsieur">Monsieur</option>
                            <option value="Madame">Madame</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="org_manager_name"><?php echo esc_html__('Manager Name', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="org_manager_name" name="org_manager_name" class="regular-text" value="Igal OINOUNOU" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="org_manager_role"><?php echo esc_html__('Manager Role', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="org_manager_role" name="org_manager_role" class="regular-text" value="Gérant" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="org_city"><?php echo esc_html__('City', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="org_city" name="org_city" class="regular-text" value="Paris" />
                    </td>
                </tr>
            </table>
        </div>

        <div class="cddu-form-section">
            <h2><?php echo esc_html__('Instructor Details', 'wp-cddu-manager'); ?></h2>
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
                <tr>
                    <th scope="row">
                        <label for="instructor_gender"><?php echo esc_html__('Instructor Gender', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <select id="instructor_gender" name="instructor_gender" class="regular-text">
                            <option value="Monsieur">Monsieur</option>
                            <option value="Madame">Madame</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="instructor_full_name"><?php echo esc_html__('Full Name', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="instructor_full_name" name="instructor_full_name" class="regular-text" placeholder="[...]" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="instructor_birth_date"><?php echo esc_html__('Birth Date', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="instructor_birth_date" name="instructor_birth_date" class="regular-text" placeholder="[...]" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="instructor_birth_place"><?php echo esc_html__('Birth Place', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="instructor_birth_place" name="instructor_birth_place" class="regular-text" placeholder="[...]" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="instructor_address"><?php echo esc_html__('Address', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="instructor_address" name="instructor_address" class="regular-text" placeholder="[...]" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="instructor_social_security"><?php echo esc_html__('Social Security Number', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="instructor_social_security" name="instructor_social_security" class="regular-text" placeholder="[...]" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="instructor_job_title"><?php echo esc_html__('Job Title', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="instructor_job_title" name="instructor_job_title" class="regular-text" value="formateur en informatique" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="instructor_classification"><?php echo esc_html__('Classification Level', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="instructor_classification" name="instructor_classification" class="regular-text" value="Palier 9" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="instructor_coefficient"><?php echo esc_html__('Coefficient', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="instructor_coefficient" name="instructor_coefficient" class="regular-text" value="200" />
                    </td>
                </tr>
            </table>
        </div>

        <div class="cddu-form-section">
            <h2><?php echo esc_html__('Contract Modification Details', 'wp-cddu-manager'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="original_contract_date"><?php echo esc_html__('Original Contract Date', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <input type="date" id="original_contract_date" name="original_contract_date" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="original_end_date"><?php echo esc_html__('Original End Date', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <input type="date" id="original_end_date" name="original_end_date" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="new_end_date"><?php echo esc_html__('New End Date', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <input type="date" id="new_end_date" name="new_end_date" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="hourly_rate"><?php echo esc_html__('Hourly Rate (€)', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <input type="number" step="0.01" id="hourly_rate" name="hourly_rate" class="regular-text" value="13.17" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="effective_date"><?php echo esc_html__('Effective Date', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <input type="date" id="effective_date" name="effective_date" class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="signature_date"><?php echo esc_html__('Signature Date', 'wp-cddu-manager'); ?></label>
                    </th>
                    <td>
                        <input type="date" id="signature_date" name="signature_date" class="regular-text" />
                    </td>
                </tr>
            </table>
        </div>

        <div class="cddu-form-section">
            <h2><?php echo esc_html__('Monthly Hours Breakdown', 'wp-cddu-manager'); ?></h2>
            <p class="description"><?php echo esc_html__('Add monthly hours breakdown for the addendum period.', 'wp-cddu-manager'); ?></p>
            
            <div id="monthly-breakdown-container">
                <div class="monthly-breakdown-item">
                    <h4><?php echo esc_html__('November 2023', 'wp-cddu-manager'); ?></h4>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="november_total_hours"><?php echo esc_html__('Total Hours', 'wp-cddu-manager'); ?></label>
                            </th>
                            <td>
                                <input type="number" step="0.01" id="november_total_hours" name="november_total_hours" class="regular-text" value="15.28" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="november_af_hours"><?php echo esc_html__('AF Hours', 'wp-cddu-manager'); ?></label>
                            </th>
                            <td>
                                <input type="number" step="0.01" id="november_af_hours" name="november_af_hours" class="regular-text" value="11.00" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="november_pr_hours"><?php echo esc_html__('PR Hours', 'wp-cddu-manager'); ?></label>
                            </th>
                            <td>
                                <input type="number" step="0.01" id="november_pr_hours" name="november_pr_hours" class="regular-text" value="4.28" />
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="monthly-breakdown-item">
                    <h4><?php echo esc_html__('December 2023', 'wp-cddu-manager'); ?></h4>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="december_total_hours"><?php echo esc_html__('Total Hours', 'wp-cddu-manager'); ?></label>
                            </th>
                            <td>
                                <input type="number" step="0.01" id="december_total_hours" name="december_total_hours" class="regular-text" value="20.83" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="december_af_hours"><?php echo esc_html__('AF Hours', 'wp-cddu-manager'); ?></label>
                            </th>
                            <td>
                                <input type="number" step="0.01" id="december_af_hours" name="december_af_hours" class="regular-text" value="15.00" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="december_pr_hours"><?php echo esc_html__('PR Hours', 'wp-cddu-manager'); ?></label>
                            </th>
                            <td>
                                <input type="number" step="0.01" id="december_pr_hours" name="december_pr_hours" class="regular-text" value="5.83" />
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="monthly-breakdown-item">
                    <h4><?php echo esc_html__('January 2024', 'wp-cddu-manager'); ?></h4>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="january_total_hours"><?php echo esc_html__('Total Hours', 'wp-cddu-manager'); ?></label>
                            </th>
                            <td>
                                <input type="number" step="0.01" id="january_total_hours" name="january_total_hours" class="regular-text" value="5.55" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="january_af_hours"><?php echo esc_html__('AF Hours', 'wp-cddu-manager'); ?></label>
                            </th>
                            <td>
                                <input type="number" step="0.01" id="january_af_hours" name="january_af_hours" class="regular-text" value="4.00" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="january_pr_hours"><?php echo esc_html__('PR Hours', 'wp-cddu-manager'); ?></label>
                            </th>
                            <td>
                                <input type="number" step="0.01" id="january_pr_hours" name="january_pr_hours" class="regular-text" value="1.55" />
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <p class="submit">
                <button type="button" id="add-month-btn" class="button button-secondary">
                    <?php echo esc_html__('Add Month', 'wp-cddu-manager'); ?>
                </button>
            </p>
        </div>

        <div class="cddu-form-section">
            <h2><?php echo esc_html__('Weekly Schedule (Optional)', 'wp-cddu-manager'); ?></h2>
            <p class="description"><?php echo esc_html__('Define weekly breakdown if needed. Leave empty to display placeholder fields.', 'wp-cddu-manager'); ?></p>
            
            <div id="weekly-schedule-container">
                <!-- Weekly schedule items will be added dynamically -->
            </div>

            <p class="submit">
                <button type="button" id="add-week-btn" class="button button-secondary">
                    <?php echo esc_html__('Add Week', 'wp-cddu-manager'); ?>
                </button>
            </p>
        </div>

        <div class="cddu-form-section">
            <h2><?php echo esc_html__('Generate Addendum', 'wp-cddu-manager'); ?></h2>
            <p class="submit">
                <button type="button" id="calculate-addendum-btn" class="button button-secondary">
                    <?php echo esc_html__('Calculate Values', 'wp-cddu-manager'); ?>
                </button>
                <button type="button" id="generate-addendum-btn" class="button button-primary">
                    <?php echo esc_html__('Generate Addendum PDF', 'wp-cddu-manager'); ?>
                </button>
                <button type="button" id="preview-addendum-btn" class="button button-secondary">
                    <?php echo esc_html__('Preview Addendum', 'wp-cddu-manager'); ?>
                </button>
            </p>
        </div>

        <div id="calculation-results" class="cddu-form-section" style="display:none;">
            <h2><?php echo esc_html__('Calculated Values', 'wp-cddu-manager'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php echo esc_html__('November 2023 Amount', 'wp-cddu-manager'); ?></th>
                    <td id="calc-november-amount">-</td>
                </tr>
                <tr>
                    <th scope="row"><?php echo esc_html__('December 2023 Amount', 'wp-cddu-manager'); ?></th>
                    <td id="calc-december-amount">-</td>
                </tr>
                <tr>
                    <th scope="row"><?php echo esc_html__('January 2024 Amount', 'wp-cddu-manager'); ?></th>
                    <td id="calc-january-amount">-</td>
                </tr>
                <tr>
                    <th scope="row"><strong><?php echo esc_html__('Total Amount', 'wp-cddu-manager'); ?></strong></th>
                    <td><strong id="calc-total-amount">-</strong></td>
                </tr>
            </table>
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
.monthly-breakdown-item {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px;
    margin: 15px 0;
}
.monthly-breakdown-item h4 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #333;
}
.weekly-schedule-item {
    background: #f0f8ff;
    border: 1px solid #b0d4f1;
    border-radius: 4px;
    padding: 15px;
    margin: 15px 0;
    position: relative;
}
.weekly-schedule-item h4 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #0073aa;
}
.remove-week-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #dc3232;
    color: white;
    border: none;
    border-radius: 3px;
    padding: 5px 10px;
    cursor: pointer;
    font-size: 12px;
}
.remove-week-btn:hover {
    background: #a00;
}
#calculation-results {
    background: #f9f9f9;
}
</style>

<script>
jQuery(document).ready(function($) {
    let weekCounter = 0;
    
    // Add week functionality
    $('#add-week-btn').on('click', function() {
        weekCounter++;
        const weekItem = `
            <div class="weekly-schedule-item" data-week="${weekCounter}">
                <button type="button" class="remove-week-btn" onclick="removeWeek(${weekCounter})">×</button>
                <h4>Week ${weekCounter}</h4>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="week_${weekCounter}_start_date">Start Date</label>
                        </th>
                        <td>
                            <input type="date" id="week_${weekCounter}_start_date" name="weeks[${weekCounter}][start_date]" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="week_${weekCounter}_end_date">End Date</label>
                        </th>
                        <td>
                            <input type="date" id="week_${weekCounter}_end_date" name="weeks[${weekCounter}][end_date]" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="week_${weekCounter}_af_hours">AF Hours</label>
                        </th>
                        <td>
                            <input type="number" step="0.01" id="week_${weekCounter}_af_hours" name="weeks[${weekCounter}][af_hours]" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="week_${weekCounter}_pr_hours">PR Hours</label>
                        </th>
                        <td>
                            <input type="number" step="0.01" id="week_${weekCounter}_pr_hours" name="weeks[${weekCounter}][pr_hours]" class="regular-text" />
                        </td>
                    </tr>
                </table>
            </div>
        `;
        $('#weekly-schedule-container').append(weekItem);
    });
    
    // Calculate addendum values
    $('#calculate-addendum-btn').on('click', function() {
        const hourlyRate = parseFloat($('#hourly_rate').val()) || 13.17;
        
        const novemberHours = parseFloat($('#november_total_hours').val()) || 0;
        const decemberHours = parseFloat($('#december_total_hours').val()) || 0;
        const januaryHours = parseFloat($('#january_total_hours').val()) || 0;
        
        const novemberAmount = novemberHours * hourlyRate;
        const decemberAmount = decemberHours * hourlyRate;
        const januaryAmount = januaryHours * hourlyRate;
        const totalAmount = novemberAmount + decemberAmount + januaryAmount;
        
        $('#calc-november-amount').text(novemberAmount.toFixed(2) + ' €');
        $('#calc-december-amount').text(decemberAmount.toFixed(2) + ' €');
        $('#calc-january-amount').text(januaryAmount.toFixed(2) + ' €');
        $('#calc-total-amount').text(totalAmount.toFixed(2) + ' €');
        
        $('#calculation-results').show();
    });
    
    // Auto-populate instructor details when selected
    $('#instructor_user_id').on('change', function() {
        const userId = $(this).val();
        if (userId) {
            // Make AJAX call to get instructor details
            // This would need to be implemented in the backend
        }
    });
    
    // Auto-populate contract details when original contract is selected
    $('#original_contract_id').on('change', function() {
        const contractId = $(this).val();
        if (contractId) {
            // Make AJAX call to get contract details
            // This would need to be implemented in the backend
        }
    });
});

function removeWeek(weekNumber) {
    jQuery(`.weekly-schedule-item[data-week="${weekNumber}"]`).remove();
}
</script>
