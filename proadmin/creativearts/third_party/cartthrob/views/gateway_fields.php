<?php 

echo $hidden; 

$use_bootstrap = TRUE; 

	switch ($field_format)
	{
		case "bootstrap":
			$use_bootstrap = TRUE; 
			break;
		case "default":
			$use_bootstrap = FALSE; 
			break;
		default: 
			$use_bootstrap = TRUE; 
	}

foreach ($sections as $section => $fields)
{
	echo form_fieldset(lang($section), array('class' => $section, 'id' => $section)); 
	
	foreach ($fields as $field)
	{
		$class="";
		if ($use_bootstrap)
		{
			echo '<div class="control-group">'; 
			$class="control-label"; 
		}


		if (in_array($field, $required_fields))
		{
			$class .= " required"; 
			echo form_label(lang($field), $field,  array('class' => $class) );
		}
		else
		{
			echo form_label(lang($field), $field,  array('class' => $class) );
		}
		
		$attributes = array('id' => $field);

		if (in_array($field, $required_fields))
		{
			$attributes['class'] = 'required';
		}

		$value = $this->cartthrob->cart->customer_info($field);

		$field_name = $field;
		if (in_array($field, $nameless_fields))
		{
			$field_name = NULL;  
		}
		// if there are extra fields and they contain something like something_state, or something_country_code, they'll be output as 
		// the appropriate TYPE of field. must contain _state, or _country_code or _month etc, so we dont parse something like "estate" as state
		if (in_array($field, $extra_fields))
		{
			$field_name = $field;  
			
			if (strpos($field, "_country_code") !== FALSE)
			{
				$field = "country_code"; 
			}
			if (strpos($field, "_state") !== FALSE)
			{
				$field = "state"; 
			}
			if (strpos($field, "_month") !== FALSE)
			{
				$field = "expiration_month"; 
			}
		}
		
		switch ($field)
		{
			case 'account_type': 
				$field = form_dropdown($field_name, $account_types, $value, _parse_attributes($attributes)); 
				break;
			case 'card_type': 
				$field = form_dropdown($field_name, $card_types, $value, _parse_attributes($attributes)); 
				break;
			case 'subscription_interval_length': 
				$field = form_dropdown($field_name, array('months' => 'Months', 'days' => 'Days', 'weeks' => 'Weeks', 'years' => 'Years'), $value, _parse_attributes($attributes)); 
			case 'subscription_interval_units': 
				$field = form_dropdown($field_name, $subscription_interval_units, $value, _parse_attributes($attributes)); 
				break;	
			case 'expiration_month':
			case 'begin_month':
			case 'bday_month':
				$field = form_dropdown($field_name, $months, $value, _parse_attributes($attributes)); 
				break;
			case 'expiration_year':
				$field =form_dropdown($field_name, $exp_years, $value, _parse_attributes($attributes)); 
				break;
			case 'begin_year':
				$field = form_dropdown($field_name, $begin_years, $value, _parse_attributes($attributes)); 
				break;
			case 'bday_year':
				$field = form_dropdown($field_name, $bday_year, $value, _parse_attributes($attributes)); 
				break;
			case 'bday_day':
				$field = form_dropdown($field_name, $bday_day, $value, _parse_attributes($attributes)); 
				break;
		case 'state': 
			case 'shipping_state':
				$states = array_merge(array('' => '---'), $states);
				$field = form_dropdown($field_name, $states, $value, _parse_attributes($attributes)); 
				break;
			case 'country': 
			case 'shipping_country': 
			case 'country_code': 
			case 'shipping_country_code':
				#$countries = array_merge(array('' => '---'), $countries);
				$field = form_dropdown($field_name, $countries, $value, _parse_attributes($attributes));
				break;
			default:
				$attributes['name'] = $field_name;
				$attributes['value'] = $value;
				$field = form_input($attributes);
		}

		$field = str_replace('<option', str_repeat("\t", 2).'<option', $field);
		$field = str_replace('</select', str_repeat("\t", 1).'</select', $field);
		
		if ($use_bootstrap)
		{
			echo '<div class="controls">'; 
		}
		echo $field; 
		if ($use_bootstrap)
		{
			echo '</div><!-- end control --></div><!-- end control group -->'; 
		}
	}
	
	echo form_fieldset_close(); 
}


