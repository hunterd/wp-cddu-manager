<?php
/**
 * Addendum Templates Management Page
 */

// Security check
if (!defined('WPINC')) {
    die;
}

// Check user permissions
if (!current_user_can('cddu_manage')) {
    wp_die(__('You do not have sufficient permissions to access this page.', 'wp-cddu-manager'));
}
?>

<div class="wrap">
    <h1><?php _e('Addendum Templates Management', 'wp-cddu-manager'); ?></h1>
    
    <div id="addendum-template-manager" class="cddu-template-manager">
        
        <!-- Tab Navigation -->
        <nav class="nav-tab-wrapper">
            <a href="#custom-templates" class="nav-tab nav-tab-active" id="custom-tab">
                <?php _e('Custom Templates', 'wp-cddu-manager'); ?>
            </a>
            <a href="#default-templates" class="nav-tab" id="default-tab">
                <?php _e('Default Templates', 'wp-cddu-manager'); ?>
            </a>
            <a href="#create-template" class="nav-tab" id="create-tab">
                <?php _e('Create New Template', 'wp-cddu-manager'); ?>
            </a>
        </nav>

        <!-- Custom Templates Tab -->
        <div id="custom-templates" class="tab-content active">
            <h2><?php _e('Your Custom Addendum Templates', 'wp-cddu-manager'); ?></h2>
            <p><?php _e('Manage your custom addendum templates. You can create, edit, and delete templates here.', 'wp-cddu-manager'); ?></p>
            
            <div id="custom-templates-list">
                <div class="templates-loading">
                    <span class="spinner is-active"></span>
                    <?php _e('Loading templates...', 'wp-cddu-manager'); ?>
                </div>
            </div>
        </div>

        <!-- Default Templates Tab -->
        <div id="default-templates" class="tab-content">
            <h2><?php _e('Default Addendum Templates', 'wp-cddu-manager'); ?></h2>
            <p><?php _e('These are the default addendum templates provided by the plugin. You can use them as a starting point for your custom templates.', 'wp-cddu-manager'); ?></p>
            
            <div id="default-templates-list">
                <div class="templates-loading">
                    <span class="spinner is-active"></span>
                    <?php _e('Loading default templates...', 'wp-cddu-manager'); ?>
                </div>
            </div>
        </div>

        <!-- Create/Edit Template Tab -->
        <div id="create-template" class="tab-content">
            <h2 id="template-form-title"><?php _e('Create New Addendum Template', 'wp-cddu-manager'); ?></h2>
            
            <form id="addendum-template-form">
                <?php wp_nonce_field('cddu_addendum_nonce', 'cddu_addendum_nonce'); ?>
                
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="template_name"><?php _e('Template Name', 'wp-cddu-manager'); ?></label>
                            </th>
                            <td>
                                <input name="template_name" type="text" id="template_name" class="regular-text" required />
                                <p class="description"><?php _e('Enter a unique name for this addendum template.', 'wp-cddu-manager'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="template_content"><?php _e('Template Content', 'wp-cddu-manager'); ?></label>
                            </th>
                            <td>
                                <?php
                                wp_editor('', 'template_content', [
                                    'textarea_name' => 'template_content',
                                    'textarea_rows' => 20,
                                    'teeny' => false,
                                    'media_buttons' => false,
                                    'tinymce' => [
                                        'toolbar1' => 'bold,italic,underline,strikethrough,|,bullist,numlist,|,link,unlink,|,undo,redo',
                                        'toolbar2' => 'formatselect,|,alignleft,aligncenter,alignright,alignjustify,|,outdent,indent,|,removeformat'
                                    ]
                                ]);
                                ?>
                                <p class="description">
                                    <?php _e('Use HTML and variables like {{organization.name}}, {{instructor.first_name}}, {{calculations.total_hours}}, etc.', 'wp-cddu-manager'); ?>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="template-variables-help">
                    <h3><?php _e('Available Variables', 'wp-cddu-manager'); ?></h3>
                    <p><?php _e('Click on any variable to insert it into your template:', 'wp-cddu-manager'); ?></p>
                    
                    <div class="variables-grid">
                        <div class="variable-group">
                            <h4><?php _e('Organization', 'wp-cddu-manager'); ?></h4>
                            <div class="variable-list">
                                <span class="variable-tag" data-variable="{{organization.name}}"><?php _e('Name', 'wp-cddu-manager'); ?></span>
                                <span class="variable-tag" data-variable="{{organization.address}}"><?php _e('Address', 'wp-cddu-manager'); ?></span>
                                <span class="variable-tag" data-variable="{{organization.city}}"><?php _e('City', 'wp-cddu-manager'); ?></span>
                                <span class="variable-tag" data-variable="{{organization.registration_number}}"><?php _e('Registration Number', 'wp-cddu-manager'); ?></span>
                                <span class="variable-tag" data-variable="{{organization.legal_representative}}"><?php _e('Legal Representative', 'wp-cddu-manager'); ?></span>
                                <span class="variable-tag" data-variable="{{organization.legal_representative_role}}"><?php _e('Representative Role', 'wp-cddu-manager'); ?></span>
                            </div>
                        </div>
                        
                        <div class="variable-group">
                            <h4><?php _e('Instructor', 'wp-cddu-manager'); ?></h4>
                            <div class="variable-list">
                                <span class="variable-tag" data-variable="{{instructor.first_name}}"><?php _e('First Name', 'wp-cddu-manager'); ?></span>
                                <span class="variable-tag" data-variable="{{instructor.last_name}}"><?php _e('Last Name', 'wp-cddu-manager'); ?></span>
                                <span class="variable-tag" data-variable="{{instructor.civility}}"><?php _e('Civility', 'wp-cddu-manager'); ?></span>
                                <span class="variable-tag" data-variable="{{instructor.address}}"><?php _e('Address', 'wp-cddu-manager'); ?></span>
                                <span class="variable-tag" data-variable="{{instructor.birth_date}}"><?php _e('Birth Date', 'wp-cddu-manager'); ?></span>
                                <span class="variable-tag" data-variable="{{instructor.birth_place}}"><?php _e('Birth Place', 'wp-cddu-manager'); ?></span>
                                <span class="variable-tag" data-variable="{{instructor.social_security_number}}"><?php _e('Social Security', 'wp-cddu-manager'); ?></span>
                                <span class="variable-tag" data-variable="{{instructor.job_title}}"><?php _e('Job Title', 'wp-cddu-manager'); ?></span>
                                <span class="variable-tag" data-variable="{{instructor.classification_level}}"><?php _e('Classification', 'wp-cddu-manager'); ?></span>
                                <span class="variable-tag" data-variable="{{instructor.coefficient}}"><?php _e('Coefficient', 'wp-cddu-manager'); ?></span>
                            </div>
                        </div>
                        
                        <div class="variable-group">
                            <h4><?php _e('Contract', 'wp-cddu-manager'); ?></h4>
                            <div class="variable-list">
                                <span class="variable-tag" data-variable="{{contract.start_date}}"><?php _e('Start Date', 'wp-cddu-manager'); ?></span>
                                <span class="variable-tag" data-variable="{{contract.end_date}}"><?php _e('End Date', 'wp-cddu-manager'); ?></span>
                                <span class="variable-tag" data-variable="{{contract.start_date_formatted}}"><?php _e('Start Date (Formatted)', 'wp-cddu-manager'); ?></span>
                                <span class="variable-tag" data-variable="{{contract.end_date_formatted}}"><?php _e('End Date (Formatted)', 'wp-cddu-manager'); ?></span>
                            </div>
                        </div>
                        
                        <div class="variable-group">
                            <h4><?php _e('Addendum', 'wp-cddu-manager'); ?></h4>
                            <div class="variable-list">
                                <span class="variable-tag" data-variable="{{addendum.number}}"><?php _e('Number', 'wp-cddu-manager'); ?></span>
                                <span class="variable-tag" data-variable="{{addendum.effective_date}}"><?php _e('Effective Date', 'wp-cddu-manager'); ?></span>
                                <span class="variable-tag" data-variable="{{addendum.signature_date}}"><?php _e('Signature Date', 'wp-cddu-manager'); ?></span>
                                <span class="variable-tag" data-variable="{{addendum.new_end_date}}"><?php _e('New End Date', 'wp-cddu-manager'); ?></span>
                                <span class="variable-tag" data-variable="{{addendum.new_end_date_formatted}}"><?php _e('New End Date (Formatted)', 'wp-cddu-manager'); ?></span>
                                <span class="variable-tag" data-variable="{{addendum.month}}"><?php _e('Month', 'wp-cddu-manager'); ?></span>
                            </div>
                        </div>
                        
                        <div class="variable-group">
                            <h4><?php _e('Calculations', 'wp-cddu-manager'); ?></h4>
                            <div class="variable-list">
                                <span class="variable-tag" data-variable="{{calculations.animation_hours}}"><?php _e('Animation Hours', 'wp-cddu-manager'); ?></span>
                                <span class="variable-tag" data-variable="{{calculations.preparation_hours}}"><?php _e('Preparation Hours', 'wp-cddu-manager'); ?></span>
                                <span class="variable-tag" data-variable="{{calculations.total_hours}}"><?php _e('Total Hours', 'wp-cddu-manager'); ?></span>
                                <span class="variable-tag" data-variable="{{calculations.hourly_rate}}"><?php _e('Hourly Rate', 'wp-cddu-manager'); ?></span>
                                <span class="variable-tag" data-variable="{{calculations.gross_monthly_salary}}"><?php _e('Gross Monthly Salary', 'wp-cddu-manager'); ?></span>
                                <span class="variable-tag" data-variable="{{calculations.usage_allowance}}"><?php _e('Usage Allowance', 'wp-cddu-manager'); ?></span>
                                <span class="variable-tag" data-variable="{{calculations.paid_vacation}}"><?php _e('Paid Vacation', 'wp-cddu-manager'); ?></span>
                                <span class="variable-tag" data-variable="{{calculations.total_amount}}"><?php _e('Total Amount', 'wp-cddu-manager'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e('Save Template', 'wp-cddu-manager'); ?>" />
                    <input type="button" id="cancel-edit" class="button" value="<?php _e('Cancel', 'wp-cddu-manager'); ?>" style="display: none;" />
                </p>
            </form>
        </div>
    </div>
</div>

<!-- Templates List Template (Custom) -->
<script type="text/template" id="custom-template-item-template">
    <div class="template-item" data-template-name="<%= name %>">
        <div class="template-header">
            <h3><%= name %></h3>
            <div class="template-actions">
                <button class="button edit-template" data-template="<%= name %>"><?php _e('Edit', 'wp-cddu-manager'); ?></button>
                <button class="button delete-template" data-template="<%= name %>"><?php _e('Delete', 'wp-cddu-manager'); ?></button>
            </div>
        </div>
        <div class="template-meta">
            <p><?php _e('Created:', 'wp-cddu-manager'); ?> <span class="template-date"><%= created_date %></span></p>
        </div>
    </div>
</script>

<!-- Templates List Template (Default) -->
<script type="text/template" id="default-template-item-template">
    <div class="template-item default-template" data-template-name="<%= key %>">
        <div class="template-header">
            <h3><%= name %></h3>
            <div class="template-actions">
                <button class="button use-template" data-template="<%= key %>" data-type="default"><?php _e('Use as Base', 'wp-cddu-manager'); ?></button>
                <button class="button preview-template" data-template="<%= key %>" data-type="default"><?php _e('Preview', 'wp-cddu-manager'); ?></button>
            </div>
        </div>
        <div class="template-meta">
            <p><?php _e('Type:', 'wp-cddu-manager'); ?> <span class="template-type"><?php _e('Default Template', 'wp-cddu-manager'); ?></span></p>
        </div>
    </div>
</script>
