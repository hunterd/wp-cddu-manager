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
                container.html('<p>' + cddu_addendum_templates.strings.no_custom_templates + '</p>');
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

                    $('#template-form-title').text(cddu_addendum_templates.strings.edit_template_title);
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

                    $('#template-form-title').text(cddu_addendum_templates.strings.create_template_title);
                    $('#cancel-edit').hide();
                    $('.nav-tab[href="#create-template"]').trigger('click');
                }
            });
        },

        deleteTemplate: function() {
            if (!confirm(cddu_addendum_templates.strings.confirm_delete)) {
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

            $('#template-form-title').text(cddu_addendum_templates.strings.create_template_title);
            $('#cancel-edit').hide();
        },

        cancelEdit: function() {
            AddendumTemplateManager.resetForm();
        }
    };

    AddendumTemplateManager.init();
});
