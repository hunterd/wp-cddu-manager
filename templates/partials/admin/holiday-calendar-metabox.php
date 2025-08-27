<?php
/**
 * Holiday calendar metabox template
 * @var WP_Post $post
 * @var array $holidays
 */
?>
<div id="holiday-calendar-container">
    <div class="holiday-list">
        <h4><?php echo esc_html__('Holidays', 'wp-cddu-manager'); ?></h4>
        
        <div id="holidays-list">
            <?php foreach ($holidays as $index => $holiday): ?>
                <div class="holiday-item" data-index="<?php echo esc_attr($index); ?>">
                    <div class="holiday-fields">
                        <div class="field-group">
                            <label><?php echo esc_html__('Date', 'wp-cddu-manager'); ?></label>
                            <input type="date" 
                                   name="holidays[<?php echo esc_attr($index); ?>][date]" 
                                   value="<?php echo esc_attr($holiday['date'] ?? ''); ?>" 
                                   required />
                        </div>
                        
                        <div class="field-group">
                            <label><?php echo esc_html__('Holiday Name', 'wp-cddu-manager'); ?></label>
                            <input type="text" 
                                   name="holidays[<?php echo esc_attr($index); ?>][name]" 
                                   value="<?php echo esc_attr($holiday['name'] ?? ''); ?>" 
                                   placeholder="<?php echo esc_attr__('Holiday name', 'wp-cddu-manager'); ?>"
                                   required />
                        </div>
                        
                        <div class="field-group">
                            <label><?php echo esc_html__('Type', 'wp-cddu-manager'); ?></label>
                            <select name="holidays[<?php echo esc_attr($index); ?>][type]">
                                <option value="public" <?php selected($holiday['type'] ?? 'public', 'public'); ?>>
                                    <?php echo esc_html__('Public Holiday', 'wp-cddu-manager'); ?>
                                </option>
                                <option value="organization" <?php selected($holiday['type'] ?? 'public', 'organization'); ?>>
                                    <?php echo esc_html__('Organization Holiday', 'wp-cddu-manager'); ?>
                                </option>
                                <option value="bridge" <?php selected($holiday['type'] ?? 'public', 'bridge'); ?>>
                                    <?php echo esc_html__('Bridge Day', 'wp-cddu-manager'); ?>
                                </option>
                            </select>
                        </div>
                        
                        <div class="field-group">
                            <label>
                                <input type="checkbox" 
                                       name="holidays[<?php echo esc_attr($index); ?>][recurring]" 
                                       value="1" 
                                       <?php checked($holiday['recurring'] ?? 0, 1); ?> />
                                <?php echo esc_html__('Recurring yearly', 'wp-cddu-manager'); ?>
                            </label>
                        </div>
                        
                        <div class="field-group">
                            <button type="button" class="button remove-holiday" title="<?php echo esc_attr__('Remove holiday', 'wp-cddu-manager'); ?>">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="add-holiday-section">
            <button type="button" id="add-holiday" class="button button-secondary">
                <span class="dashicons dashicons-plus-alt"></span>
                <?php echo esc_html__('Add Holiday', 'wp-cddu-manager'); ?>
            </button>
        </div>
    </div>
    
    <div class="holiday-presets">
        <h4><?php echo esc_html__('Quick Add Common Holidays', 'wp-cddu-manager'); ?></h4>
        <p class="description"><?php echo esc_html__('Click to add common French holidays to your calendar', 'wp-cddu-manager'); ?></p>
        
        <div class="preset-buttons">
            <button type="button" class="button" data-preset="french-public">
                <?php echo esc_html__('French Public Holidays', 'wp-cddu-manager'); ?>
            </button>
            <button type="button" class="button" data-preset="french-religious">
                <?php echo esc_html__('French Religious Holidays', 'wp-cddu-manager'); ?>
            </button>
        </div>
    </div>
</div>

<style>
#holiday-calendar-container {
    margin-top: 10px;
}

.holiday-item {
    border: 1px solid #ddd;
    padding: 15px;
    margin-bottom: 10px;
    background: #f9f9f9;
    border-radius: 4px;
}

.holiday-fields {
    display: grid;
    grid-template-columns: 1fr 2fr 1fr auto auto;
    gap: 10px;
    align-items: end;
}

.field-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
}

.field-group input,
.field-group select {
    width: 100%;
}

.remove-holiday {
    color: #dc3232;
    border-color: #dc3232;
}

.remove-holiday:hover {
    background: #dc3232;
    color: white;
}

.add-holiday-section {
    margin: 20px 0;
    text-align: center;
}

.holiday-presets {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
}

.preset-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.preset-buttons .button {
    margin-bottom: 5px;
}

@media (max-width: 782px) {
    .holiday-fields {
        grid-template-columns: 1fr;
        gap: 15px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    let holidayIndex = <?php echo count($holidays); ?>;
    
    // Holiday presets
    const holidayPresets = {
        'french-public': [
            {name: 'Jour de l\'An', date: '01-01', type: 'public', recurring: true},
            {name: 'Fête du Travail', date: '05-01', type: 'public', recurring: true},
            {name: 'Victoire 1945', date: '05-08', type: 'public', recurring: true},
            {name: 'Fête Nationale', date: '07-14', type: 'public', recurring: true},
            {name: 'Assomption', date: '08-15', type: 'public', recurring: true},
            {name: 'Toussaint', date: '11-01', type: 'public', recurring: true},
            {name: 'Armistice 1918', date: '11-11', type: 'public', recurring: true},
            {name: 'Noël', date: '12-25', type: 'public', recurring: true}
        ],
        'french-religious': [
            {name: 'Lundi de Pâques', date: '', type: 'public', recurring: true, note: 'Variable date'},
            {name: 'Ascension', date: '', type: 'public', recurring: true, note: 'Variable date'},
            {name: 'Lundi de Pentecôte', date: '', type: 'public', recurring: true, note: 'Variable date'}
        ]
    };
    
    // Add holiday
    $('#add-holiday').on('click', function() {
        addHolidayRow();
    });
    
    // Remove holiday
    $(document).on('click', '.remove-holiday', function() {
        $(this).closest('.holiday-item').remove();
    });
    
    // Add preset holidays
    $('.preset-buttons .button').on('click', function() {
        const preset = $(this).data('preset');
        const holidays = holidayPresets[preset] || [];
        const currentYear = new Date().getFullYear();
        
        holidays.forEach(function(holiday) {
            if (holiday.date) {
                const fullDate = currentYear + '-' + holiday.date;
                addHolidayRow(holiday.name, fullDate, holiday.type, holiday.recurring);
            } else {
                // For variable dates, add empty row with name filled
                addHolidayRow(holiday.name, '', holiday.type, holiday.recurring);
            }
        });
    });
    
    function addHolidayRow(name = '', date = '', type = 'public', recurring = false) {
        const currentYear = new Date().getFullYear();
        const defaultDate = date || currentYear + '-01-01';
        
        const holidayHtml = `
            <div class="holiday-item" data-index="${holidayIndex}">
                <div class="holiday-fields">
                    <div class="field-group">
                        <label><?php echo esc_js(__('Date', 'wp-cddu-manager')); ?></label>
                        <input type="date" 
                               name="holidays[${holidayIndex}][date]" 
                               value="${date}" 
                               required />
                    </div>
                    
                    <div class="field-group">
                        <label><?php echo esc_js(__('Holiday Name', 'wp-cddu-manager')); ?></label>
                        <input type="text" 
                               name="holidays[${holidayIndex}][name]" 
                               value="${name}" 
                               placeholder="<?php echo esc_js(__('Holiday name', 'wp-cddu-manager')); ?>"
                               required />
                    </div>
                    
                    <div class="field-group">
                        <label><?php echo esc_js(__('Type', 'wp-cddu-manager')); ?></label>
                        <select name="holidays[${holidayIndex}][type]">
                            <option value="public" ${type === 'public' ? 'selected' : ''}><?php echo esc_js(__('Public Holiday', 'wp-cddu-manager')); ?></option>
                            <option value="organization" ${type === 'organization' ? 'selected' : ''}><?php echo esc_js(__('Organization Holiday', 'wp-cddu-manager')); ?></option>
                            <option value="bridge" ${type === 'bridge' ? 'selected' : ''}><?php echo esc_js(__('Bridge Day', 'wp-cddu-manager')); ?></option>
                        </select>
                    </div>
                    
                    <div class="field-group">
                        <label>
                            <input type="checkbox" 
                                   name="holidays[${holidayIndex}][recurring]" 
                                   value="1" 
                                   ${recurring ? 'checked' : ''} />
                            <?php echo esc_js(__('Recurring yearly', 'wp-cddu-manager')); ?>
                        </label>
                    </div>
                    
                    <div class="field-group">
                        <button type="button" class="button remove-holiday" title="<?php echo esc_js(__('Remove holiday', 'wp-cddu-manager')); ?>">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        $('#holidays-list').append(holidayHtml);
        holidayIndex++;
    }
});
</script>
