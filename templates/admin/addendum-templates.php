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
    wp_die(__('You do not have sufficient permissions to access this page.'));
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

<style>
.cddu-template-manager {
    max-width: 1200px;
}

.tab-content {
    display: none;
    padding: 20px 0;
}

.tab-content.active {
    display: block;
}

.templates-loading {
    text-align: center;
    padding: 20px;
    color: #666;
}

.template-item {
    border: 1px solid #ddd;
    padding: 15px;
    margin-bottom: 10px;
    background: #fff;
    border-radius: 3px;
}

.template-item.default-template {
    border-left: 4px solid #0073aa;
}

.template-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.template-header h3 {
    margin: 0;
}

.template-actions .button {
    margin-left: 5px;
}

.template-meta {
    color: #666;
    font-size: 13px;
}

.template-variables-help {
    background: #f9f9f9;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 3px;
    margin-top: 20px;
}

.variables-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

.variable-group h4 {
    margin: 0 0 10px 0;
    color: #0073aa;
    border-bottom: 1px solid #ddd;
    padding-bottom: 5px;
}

.variable-list {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}

.variable-tag {
    background: #0073aa;
    color: white;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 11px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.variable-tag:hover {
    background: #005a87;
}

#templates-preview-modal .template-preview {
    max-height: 500px;
    overflow-y: auto;
    border: 1px solid #ddd;
    padding: 20px;
    background: white;
}
</style>

<script>
jQuery(document).ready(function($) {
    const AddendumTemplateManager = {
        init: function() {
            this.bindEvents();
            this.loadCustomTemplates();
            this.loadDefaultTemplates();
        },

        bindEvents: function() {
            // Tab switching
            $('.nav-tab').on('click', this.switchTab);
            
            // Template form
            $('#addendum-template-form').on('submit', this.saveTemplate);
            $('#cancel-edit').on('click', this.cancelEdit);
            
            // Variable tags
            $('.variable-tag').on('click', this.insertVariable);
            
            // Template actions
            $(document).on('click', '.edit-template', this.editTemplate);
            $(document).on('click', '.delete-template', this.deleteTemplate);
            $(document).on('click', '.use-template', this.useTemplate);
            $(document).on('click', '.preview-template', this.previewTemplate);
        },

        switchTab: function(e) {
            e.preventDefault();
            const target = $(this).attr('href');
            
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            
            $('.tab-content').removeClass('active');
            $(target).addClass('active');
        },

        loadCustomTemplates: function() {
            $.post(ajaxurl, {
                action: 'cddu_get_addendum_templates',
                nonce: $('#cddu_addendum_nonce').val()
            }, function(response) {
                if (response.success) {
                    AddendumTemplateManager.renderCustomTemplates(response.data.templates);
                }
            });
        },

        loadDefaultTemplates: function() {
            $.post(ajaxurl, {
                action: 'cddu_get_default_addendum_templates',
                nonce: $('#cddu_addendum_nonce').val()
            }, function(response) {
                if (response.success) {
                    AddendumTemplateManager.renderDefaultTemplates(response.data.templates);
                }
            });
        },

        renderCustomTemplates: function(templates) {
            const container = $('#custom-templates-list');
            const template = $('#custom-template-item-template').html();
            
            container.empty();
            
            if (Object.keys(templates).length === 0) {
                container.html('<p><?php _e('No custom templates found. Create your first template!', 'wp-cddu-manager'); ?></p>');
                return;
            }
            
            $.each(templates, function(name, data) {
                const html = template.replace(/<%= name %>/g, name)
                                  .replace(/<%= created_date %>/g, data.created_date);
                container.append(html);
            });
        },

        renderDefaultTemplates: function(templates) {
            const container = $('#default-templates-list');
            const template = $('#default-template-item-template').html();
            
            container.empty();
            
            $.each(templates, function(key, data) {
                const html = template.replace(/<%= key %>/g, key)
                                  .replace(/<%= name %>/g, data.name);
                container.append(html);
            });
        },

        insertVariable: function() {
            const variable = $(this).data('variable');
            const editor = tinyMCE.get('template_content');
            
            if (editor && !editor.isHidden()) {
                editor.insertContent(variable);
            } else {
                const textarea = $('#template_content');
                const cursorPos = textarea.prop('selectionStart');
                const textBefore = textarea.val().substring(0, cursorPos);
                const textAfter = textarea.val().substring(cursorPos);
                textarea.val(textBefore + variable + textAfter);
            }
        },

        saveTemplate: function(e) {
            e.preventDefault();
            
            const formData = {
                action: 'cddu_save_addendum_template',
                nonce: $('#cddu_addendum_nonce').val(),
                template_name: $('#template_name').val(),
                template_content: tinyMCE.get('template_content') ? 
                                 tinyMCE.get('template_content').getContent() : 
                                 $('#template_content').val()
            };
            
            $.post(ajaxurl, formData, function(response) {
                if (response.success) {
                    alert(response.data.message);
                    AddendumTemplateManager.loadCustomTemplates();
                    AddendumTemplateManager.resetForm();
                } else {
                    alert(response.data.message);
                }
            });
        },

        editTemplate: function() {
            const templateName = $(this).data('template');
            
            $.post(ajaxurl, {
                action: 'cddu_load_addendum_template',
                nonce: $('#cddu_addendum_nonce').val(),
                template_name: templateName,
                template_type: 'custom'
            }, function(response) {
                if (response.success) {
                    $('#template_name').val(templateName);
                    
                    if (tinyMCE.get('template_content')) {
                        tinyMCE.get('template_content').setContent(response.data.content);
                    } else {
                        $('#template_content').val(response.data.content);
                    }
                    
                    $('#template-form-title').text('<?php _e('Edit Addendum Template', 'wp-cddu-manager'); ?>');
                    $('#cancel-edit').show();
                    $('.nav-tab[href="#create-template"]').trigger('click');
                }
            });
        },

        useTemplate: function() {
            const templateName = $(this).data('template');
            const templateType = $(this).data('type');
            
            $.post(ajaxurl, {
                action: 'cddu_load_addendum_template',
                nonce: $('#cddu_addendum_nonce').val(),
                template_name: templateName,
                template_type: templateType
            }, function(response) {
                if (response.success) {
                    $('#template_name').val('');
                    
                    if (tinyMCE.get('template_content')) {
                        tinyMCE.get('template_content').setContent(response.data.content);
                    } else {
                        $('#template_content').val(response.data.content);
                    }
                    
                    $('#template-form-title').text('<?php _e('Create New Addendum Template', 'wp-cddu-manager'); ?>');
                    $('#cancel-edit').hide();
                    $('.nav-tab[href="#create-template"]').trigger('click');
                }
            });
        },

        deleteTemplate: function() {
            if (!confirm('<?php _e('Are you sure you want to delete this template?', 'wp-cddu-manager'); ?>')) {
                return;
            }
            
            const templateName = $(this).data('template');
            
            $.post(ajaxurl, {
                action: 'cddu_delete_addendum_template',
                nonce: $('#cddu_addendum_nonce').val(),
                template_name: templateName
            }, function(response) {
                if (response.success) {
                    alert(response.data.message);
                    AddendumTemplateManager.loadCustomTemplates();
                } else {
                    alert(response.data.message);
                }
            });
        },

        resetForm: function() {
            $('#template_name').val('');
            
            if (tinyMCE.get('template_content')) {
                tinyMCE.get('template_content').setContent('');
            } else {
                $('#template_content').val('');
            }
            
            $('#template-form-title').text('<?php _e('Create New Addendum Template', 'wp-cddu-manager'); ?>');
            $('#cancel-edit').hide();
        },

        cancelEdit: function() {
            AddendumTemplateManager.resetForm();
        }
    };

    AddendumTemplateManager.init();
});
</script>
