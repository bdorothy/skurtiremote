<?php 

 
$html = '<table class="mainTable cartthrobMatrix cartthrobPriceModifiersConfigurator" id="'.$field_id_name.'" border="0" cellspacing="0" cellpadding="0">
	<thead>
		<tr>
 			<th data-tag="{all_values}"><span>'.lang('all_values').'</span></th>
			<th data-tag="{option_value}"><span>'.lang('option_value').'</span></th>
			<th data-tag="{option_name}"><span>'.lang('option_name').'</span></th>
			<th data-tag="{price}"><span>'.lang('price').'</span></th>
			';
			if ($show_inventory)
			{
				$html .='<th data-tag="{inventory}"><span>'.lang('inventory').'</span></th>'; 
			}
			$html.='
			<th></th>
		</tr>
	</thead>
	<tbody>
		';
 		foreach ($options as $key => $value)
		{
			$details = NULL; 
			if (element($key, $all_values, array()))
			{
				$data = _unserialize(element($key, $all_values, array() ), TRUE);
				foreach ($data as $attr => $val)
				{
					$details.="<strong>{$attr}:</strong> {$val}<br />";
				}
			}
 			
			$all_values_input = array(
				'readonly'		=> TRUE,
				'name'        =>  $field_id_name.'['.$key.'][all_values]',
				'value'       => element($key, $all_values),
			);
			
			$option_value_input = array(
				'name'        =>  $field_id_name.'['.$key.'][option_value]',
				'value'       => element($key, $option_value),
				'maxlength'   => '32',
			);
			$option_label_input = array(
				'name'        =>  $field_id_name.'['.$key.'][option_name]',
				'value'       => element($key, $option_label),
				'maxlength'   => '32',
			);

			$price_input = array(
				'name'        =>  $field_id_name.'['.$key.'][price]',
				'value'       => element($key, $price),
				'readonly'	=> TRUE,
				'maxlength'   => '8',
			);

			if ($show_inventory)
			{
				$inventory_input = array(
					'name'        =>  $field_id_name.'['.$key.'][inventory]',
					'value'       => element($key, $inventory),
					'maxlength'   => '10',
				);
			}
			

			$html.= "
			<tr class='". alternator('even', 'odd')."'>
 				<td>
					".$details."<span style='display:none'>".form_input($all_values_input)."</span>
				</td>
				<td>
					".form_input($option_value_input)."
				</td>
				<td>
					".form_input($option_label_input)."
				</td>
				<td>
					".form_input($price_input)."
				</td>
				";

				if ( $show_inventory )
				{
					$html .=
						"<td>
							".form_input($inventory_input)."
						</td>";
				}
				
			$html .="</tr>";
		}
  
$html .='
	</tbody>
</table>
';

echo $html; 
 