<?php

class Gravity_Forms_Age_Calculation
{
	
    public function __construct()
	{
		
        add_action('init', array($this, 'init'));
		
    }
	
    function init()
	{
		
        if (!class_exists('GFForms') || !property_exists('GFForms', 'version') && version_compare(GFForms::$version, '1.9', '>='))
            return;
			
        add_filter('gform_pre_render', array($this, 'maybe_output_script'));
        add_filter('gform_merge_tag_value_pre_calculation', array($this, 'enable_age_modifier'), 10, 6);
		
    }
	
    function maybe_output_script($form)
	{
		
        $has_age_calculation = false;
		
        foreach ($form['fields'] as $field):
			
            if (!$field->has_calculation())
                continue;
			
            if (isset($field->calculationFormula) && strpos($field->calculationFormula, ':age}') !== false):
                $has_age_calculation = true;
                break;
				
            endif;
			
        endforeach;
		
        if ($has_age_calculation)
            $this->output_script();
		
        return $form;
		
    }
	
    function output_script()
	{
		
        ?>

        <script type='text/javascript'>
			
            gform.addFilter('gform_merge_tag_value_pre_calculation', function (value, mergeTagArr, isVisible, formulaField, formId) {
				
                if (mergeTagArr[4] == 'age' && isVisible)
				{
					
                    var fieldId = parseInt(mergeTagArr[1]),
                        inputId = '#input_' + formId + '_' + fieldId,
                        datePicker = jQuery(inputId),
                        yearInput = jQuery(inputId + '_3'),
                        monthInput = jQuery(inputId + '_1'),
                        dayInput = jQuery(inputId + '_2'),
                        dateString = '';
					
                    if (datePicker.length == 1)
					{
						
                        dateString = datePicker.val();
                        var separator = '/',
                            reverse_date = false;
						
                        if (datePicker.hasClass('dmy')) {
							
                            reverse_date = true;
							
                        } else if (datePicker.hasClass('dmy_dash')) {
							
                            separator = '-';
                            reverse_date = true;
							
                        } else if (datePicker.hasClass('dmy_dot')) {
							
                            separator = '.';
                            reverse_date = true;
							
                        }
						
                        if (reverse_date) {
							
                            dateString = dateString.split(separator).reverse().join('/');
							
                        }
						
                    }
					
                    if (yearInput.length == 1 && monthInput.length == 1 && dayInput.length == 1) {
						
                        var dateField = [];
                        dateField[0] = yearInput.val();
                        dateField[1] = monthInput.val();
                        dateField[2] = dayInput.val();
						
                        if (dateField[0] != '' && dateField[1] != '' && dateField[2] != '') {
							
                            dateString = dateField.join('/');
							
                        }
						
                    }
                    if (dateString != '') {

                        var today = new Date(),
                            birthDate = new Date(dateString),
                            m = today.getMonth() - birthDate.getMonth();
						
                        value = today.getFullYear() - birthDate.getFullYear();
						
                        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
							
                            value--;
							
                        }
						
                    }
					
                }
				
                return value;
				
            });
			
            gform.addAction('gform_post_calculation_events', function (mergeTagArr, formulaField, formId, calcObj) {
				
                var fieldId = parseInt(mergeTagArr[1]),
                    inputId = '#input_' + formId + '_' + fieldId,
                    dateField = jQuery(inputId + '_1, ' + inputId + '_2, ' + inputId + '_3');
				
                if (mergeTagArr[4] == 'age' && dateField.length == 3) {
					
                    console.log(dateField);
                    dateField.change(function () {
                        calcObj.bindCalcEvent(fieldId, formulaField, formId, 0);
                    });
					
                }
				
            });
			
        </script>

    <?php
		
    }
	
    function enable_age_modifier($value, $input_id, $modifier, $field, $form, $entry)
	{
		
        $target_field = RGFormsModel::get_field($form, $input_id);
		
        if ($target_field->type == 'date' && $modifier == 'age'):
		
            $dob   = rgar($entry, $input_id);
            $today = new DateTime();
            $diff  = $today->diff(new DateTime($dob));
            $value = $diff->y;
		
        endif;
		
        return $value;
		
    }
	
}

new Gravity_Forms_Age_Calculation();

?>