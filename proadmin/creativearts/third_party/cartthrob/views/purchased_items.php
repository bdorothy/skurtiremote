<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php 
/*
$this->load->library('table');

$tmpl = array (
                    'table_open'          => '<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">',

                    'heading_row_start'   => '<tr>',
                    'heading_row_end'     => '</tr>',
                    'heading_cell_start'  => '<th>',
                    'heading_cell_end'    => '</th>',

                    'row_start'           => '<tr class="even">',
                    'row_end'             => '</tr>',
                    'cell_start'          => '<td style="width:50%">',
                    'cell_end'            => '</td>',

                    'row_alt_start'       => '<tr class="odd">',
                    'row_alt_end'         => '</tr>',
                    'cell_alt_start'      => '<td style="width:50%">',
                    'cell_alt_end'        => '</td>',

                    'table_close'         => '</table>'
              );

$this->table->set_template($tmpl);

$data[] = array(
	lang('save_purchased_items'), 
	
	form_radio(array(
	    'name'        => 'save_purchased_items',
	     'value'       => '1',
	    'checked'     => $settings['save_purchased_items'],
	  )).lang('yes'). 
	form_radio(array(
	    'name'        => 'save_purchased_items',
	     'value'       => '0',
	    'checked'     => $settings['save_purchased_items'],
	  )).lang('no')
); 

$channel_dropdown_options= array(); 
foreach ($channels as $channel)
{
	$channel_dropdown_options[$channel['channel_id']] = $channel['channel_title']; 
}
$data[] = array(
	lang('purchased_items_channel'), 
	
	form_dropdown(array('purchased_items_channel', $channel_dropdown_options, $settings['purchased_items_channel'], 'class="channels" id="select_purchased_items"')),
); 


$this->table->set_heading("<strong>".lang('purchased_items_headers')."</strong><br />".lang('purchased_items_description'));

echo $this->table->generate($data);
*/  
$purchased_items_fields[''] = ''; 

if ($settings['purchased_items_channel'] && isset($fields[$settings['purchased_items_channel']]))
{
	foreach ($fields[$settings['purchased_items_channel']] as $field)
	{
		$purchased_items_fields[$field['field_id']] = $field['field_label']; 
	}
}

$purchased_items_statuses[''] = ''; 

if ($settings['purchased_items_channel'] && isset($fields[$settings['purchased_items_channel']]))
{
	foreach ($statuses[$settings['purchased_items_channel']] as $status)
	{
		$purchased_items_statuses[$status['status']] = $status['status']; 
	}
}


?>

 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<caption><?=lang('purchased_items_header')?></caption>
		<thead class="">
			<tr>
				<th colspan="2">
					<strong><?=lang('purchased_items_headers')?></strong><br />
					<?=lang('purchased_items_description')?>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<?=lang('save_purchased_items')?>
 				</td>
				<td style='width:50%;'>
					<input class='radio' type='radio' name='save_purchased_items' value='1' <?php if ($settings['save_purchased_items']) : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('yes')?>
					<input class='radio' type='radio' name='save_purchased_items' value='0' <?php if ( ! $settings['save_purchased_items']) : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('no')?>
 				</td>
			</tr>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<?=lang('save_packages_too')?>
					<div class="subtext"><?=lang('save_packages_too_note')?> </div>
					
 				</td>
				<td style='width:50%;'>
					<input class='radio' type='radio' name='save_packages_too' value='1' <?php if ($settings['save_packages_too']) : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('yes')?>
					<input class='radio' type='radio' name='save_packages_too' value='0' <?php if ( ! $settings['save_packages_too']) : ?>checked='checked'<?php endif; ?> /> 
					<?=lang('no')?>
 				</td>
			</tr>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<label><?=lang('purchased_items_channel')?></label>
 				</td>
				<td style='width:50%;'>
					<select name='purchased_items_channel' class="channels" id='select_purchased_items' >
						<option value=''></option>
						<?php foreach ($channels as $channel) : ?>
							<option value="<?=$channel['channel_id']?>" <?php if ($settings['purchased_items_channel'] == $channel['channel_id']) : ?>selected="selected"<?php endif; ?>>
								<?=$channel['channel_title']?>
							</option>
						<?php endforeach; ?>
					</select>
 				</td>
			</tr>
			
			
			<?php 
				$options = array(
					'name'	=> 'purchased_items_default_status',
					'selected' => $settings['purchased_items_default_status'],
					'options' => $purchased_items_statuses, 
					'extras' => 'class="select status_purchased_items"',
				);
			?>
			<tr class="<?php echo alternator('even', 'odd');?>">
 				<td>
					<label><?=lang($options['name'])?></label>
 					<div class="subtext"><?=lang('purchased_items_set_status')?> </div>
				</td>
				<td style='width:50%;'>
					<?php echo  str_replace(' value="" ', ' value="" class="blank" ', form_dropdown($options['name'], $options['options'] ,$options['selected'], $options['extras']) ); ?>
 				</td>
			</tr>
			

			<?php 
				$options = array(
					'name'	=> 'purchased_items_processing_status',
					'selected' => $settings['purchased_items_processing_status'],
					'options' => $purchased_items_statuses, 
					'extras' => 'class="select status_purchased_items"',
				);
			?>
			<tr class="<?php echo alternator('even', 'odd');?>">
 				<td>
					<label><?=lang($options['name'])?></label>
 				</td>
				<td style='width:50%;'>
					<?php echo  str_replace(' value="" ', ' value="" class="blank" ', form_dropdown($options['name'], $options['options'] ,$options['selected'], $options['extras']) ); ?>
 				</td>
			</tr>
 			
			<?php 
				$options = array(
					'name'	=> 'purchased_items_declined_status',
					'selected' => $settings['purchased_items_declined_status'],
					'options' => $purchased_items_statuses, 
					'extras' => 'class="select status_purchased_items"',
				);
			?>
			<tr class="<?php echo alternator('even', 'odd');?>">
 				<td>
					<label><?=lang($options['name'])?></label>
 				</td>
				<td style='width:50%;'>
					<?php echo  str_replace(' value="" ', ' value="" class="blank" ', form_dropdown($options['name'], $options['options'] ,$options['selected'], $options['extras']) ); ?>
 				</td>
			</tr>
 
			<?php 
				$options = array(
					'name'	=> 'purchased_items_failed_status',
					'selected' => $settings['purchased_items_failed_status'],
					'options' => $purchased_items_statuses, 
					'extras' => 'class="select status_purchased_items"',
				);
			?>
			<tr class="<?php echo alternator('even', 'odd');?>">
 				<td>
					<label><?=lang($options['name'])?></label>
 				</td>
				<td style='width:50%;'>
					<?php echo  str_replace(' value="" ', ' value="" class="blank" ', form_dropdown($options['name'], $options['options'] ,$options['selected'], $options['extras']) ); ?>
 				</td>
			</tr>
 			
			<?php 
				$options = array(
					'name'	=> 'status_pending',
					'selected' => $settings['purchased_items_status_pending'],
					'options' => $purchased_items_statuses, 
					'extras' => 'class="select status_purchased_items"',
				);
			?>
			<tr class="<?php echo alternator('even', 'odd');?>">
 				<td>
					<label><?=lang($options['name'])?></label>
 				</td>
				<td style='width:50%;'>
					<?php echo  str_replace(' value="" ', ' value="" class="blank" ', form_dropdown('purchased_items_'.$options['name'], $options['options'] ,$options['selected'], $options['extras']) ); ?>
 				</td>
			</tr>
				
				<?php 
					$options = array(
						'name'	=> 'status_expired',
						'selected' => $settings['purchased_items_status_expired'],
						'options' => $purchased_items_statuses, 
						'extras' => 'class="select status_purchased_items"',
					);
				?>
				<tr class="<?php echo alternator('even', 'odd');?>">
	 				<td>
						<label><?=lang($options['name'])?></label>
	 				</td>
					<td style='width:50%;'>
						<?php echo  str_replace(' value="" ', ' value="" class="blank" ', form_dropdown('purchased_items_'.$options['name'], $options['options'] ,$options['selected'], $options['extras']) ); ?>
	 				</td>
				</tr>
 
				<?php 
					$options = array(
						'name'	=> 'status_canceled',
						'selected' => $settings['purchased_items_status_canceled'],
						'options' => $purchased_items_statuses, 
						'extras' => 'class="select status_purchased_items"',
					);
				?>
				<tr class="<?php echo alternator('even', 'odd');?>">
	 				<td>
						<label><?=lang($options['name'])?></label>
	 				</td>
					<td style='width:50%;'>
						<?php echo  str_replace(' value="" ', ' value="" class="blank" ', form_dropdown('purchased_items_'.$options['name'], $options['options'] ,$options['selected'], $options['extras']) ); ?>
	 				</td>
				</tr>
 
				<?php 
					$options = array(
						'name'	=> 'status_voided',
						'selected' => $settings['purchased_items_status_voided'],
						'options' => $purchased_items_statuses, 
						'extras' => 'class="select status_purchased_items"',
					);
				?>
				<tr class="<?php echo alternator('even', 'odd');?>">
	 				<td>
						<label><?=lang($options['name'])?></label>
	 				</td>
					<td style='width:50%;'>
						<?php echo  str_replace(' value="" ', ' value="" class="blank" ', form_dropdown('purchased_items_'.$options['name'], $options['options'] ,$options['selected'], $options['extras']) ); ?>
	 				</td>
				</tr>
 
					<?php 
						$options = array(
							'name'	=> 'status_refunded',
							'selected' => $settings['purchased_items_status_refunded'],
							'options' => $purchased_items_statuses, 
							'extras' => 'class="select status_purchased_items"',
						);
					?>
					<tr class="<?php echo alternator('even', 'odd');?>">
		 				<td>
							<label><?=lang($options['name'])?></label>
		 				</td>
						<td style='width:50%;'>
							<?php echo  str_replace(' value="" ', ' value="" class="blank" ', form_dropdown('purchased_items_'.$options['name'], $options['options'] ,$options['selected'], $options['extras']) ); ?>
		 				</td>
					</tr>
 	
					<?php 
						$options = array(
							'name'	=> 'status_reversed',
							'selected' => $settings['purchased_items_status_reversed'],
							'options' => $purchased_items_statuses, 
							'extras' => 'class="select status_purchased_items"',
						);
					?>
					<tr class="<?php echo alternator('even', 'odd');?>">
		 				<td>
							<label><?=lang($options['name'])?></label>
		 				</td>
						<td style='width:50%;'>
							<?php echo  str_replace(' value="" ', ' value="" class="blank" ', form_dropdown('purchased_items_'.$options['name'], $options['options'] ,$options['selected'], $options['extras']) ); ?>
		 				</td>
					</tr>
 
					
					<?php 
						$options = array(
							'name'	=> 'status_offsite',
							'selected' => $settings['purchased_items_status_offsite'],
							'options' => $purchased_items_statuses, 
							'extras' => 'class="select status_purchased_items"',
						);
					?>
					<tr class="<?php echo alternator('even', 'odd');?>">
		 				<td>
							<label><?=lang($options['name'])?></label>
		 				</td>
						<td style='width:50%;'>
							<?php echo  str_replace(' value="" ', ' value="" class="blank" ', form_dropdown('purchased_items_'.$options['name'], $options['options'] ,$options['selected'], $options['extras']) ); ?>
		 				</td>
					</tr>

		</tbody>	
	</table>

 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
 		<caption><?=lang('purchased_items_data_fields')?></caption>
		<thead class="">
			<tr>
				<th><strong><?=lang('purchased_items_data_type')?></strong><br />
				<?=lang('purchased_items_data_type_description')?>
				</th>
				<th><strong><?=lang('purchased_items_channel_field')?></strong><br />
					<?=lang('purchased_items_channel_field_description')?>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr class="<?php echo alternator('even', 'odd');?>">
 				<td>
					<label><?=lang('purchased_items_title_prefix')?></label>
 				</td>
				<td style='width:50%;'>
					<input type="text" name="purchased_items_title_prefix" value="<?=$settings['purchased_items_title_prefix']?>" />
 				</td>
			</tr>
			
			<?php 
				$options = array(
					'name'	=> 'purchased_items_id_field',
					'selected' => $settings['purchased_items_id_field'],
					'options' => $purchased_items_fields, 
					'extras' => 'class="select field_purchased_items"',
				);
			?>
			<tr class="<?php echo alternator('even', 'odd');?>">
 				<td>
					<label><?=lang($options['name'])?></label>
 				</td>
				<td style='width:50%;'>
					<?php echo  str_replace(' value="" ', ' value="" class="blank" ', form_dropdown($options['name'], $options['options'] ,$options['selected'], $options['extras']) ); ?>

 				</td>
			</tr>
			
			
			<?php 
				$options = array(
					'name'	=> 'purchased_items_quantity_field',
					'selected' => $settings['purchased_items_quantity_field'],
					'options' => $purchased_items_fields, 
					'extras' => 'class="select field_purchased_items"',
				);
			?>
			<tr class="<?php echo alternator('even', 'odd');?>">
 				<td>
					<label><?=lang($options['name'])?></label>
 				</td>
				<td style='width:50%;'>
					<?php echo  str_replace(' value="" ', ' value="" class="blank" ', form_dropdown($options['name'], $options['options'] ,$options['selected'], $options['extras']) ); ?>

 				</td>
			</tr>
			
			<?php 
				$options = array(
					'name'	=> 'purchased_items_price_field',
					'selected' => $settings['purchased_items_price_field'],
					'options' => $purchased_items_fields, 
					'extras' => 'class="select field_purchased_items"',
				);
			?>
			<tr class="<?php echo alternator('even', 'odd');?>">
 				<td>
					<label><?=lang($options['name'])?></label>
 				</td>
				<td style='width:50%;'>
					<?php echo  str_replace(' value="" ', ' value="" class="blank" ', form_dropdown($options['name'], $options['options'] ,$options['selected'], $options['extras']) ); ?>
 				</td>
			</tr>


			<?php 
				$options = array(
					'name'	=> 'purchased_items_order_id_field',
					'selected' => $settings['purchased_items_order_id_field'],
					'options' => $purchased_items_fields, 
					'extras' => 'class="select field_purchased_items"',
				);
			?>
			<tr class="<?php echo alternator('even', 'odd');?>">
 				<td>
					<label><?=lang($options['name'])?></label>
 				</td>
				<td style='width:50%;'>
					<?php echo  str_replace(' value="" ', ' value="" class="blank" ', form_dropdown($options['name'], $options['options'] ,$options['selected'], $options['extras']) ); ?>
 				</td>
			</tr>

			<?php 
				$options = array(
					'name'	=> 'purchased_items_package_id_field',
					'selected' => $settings['purchased_items_package_id_field'],
					'options' => $purchased_items_fields, 
					'extras' => 'class="select field_purchased_items"',
				); 
				
			?>
			<tr class="<?php echo alternator('even', 'odd');?>">
 				<td>
					<label><?=lang($options['name'])?></label>
 				</td>
				<td style='width:50%;'>
 					<?php echo  str_replace(' value="" ', ' value="" class="blank" ', form_dropdown($options['name'], $options['options'] ,$options['selected'], $options['extras']) ); ?>
  				</td>
			</tr>
			
			<?php  if (array_key_exists('purchased_items_sub_id_field', $settings)):  ?>
			<?php 
				$options = array(
					'name'	=> 'purchased_items_sub_id_field',
					'selected' => $settings['purchased_items_sub_id_field'],
					'options' => $purchased_items_fields, 
					'extras' => 'class="select field_purchased_items"',
				);
			?>
			<tr class="<?php echo alternator('even', 'odd');?>">
 				<td>
					<label><?=lang($options['name'])?></label>
 				</td>
				<td style='width:50%;'>
					<?php echo  str_replace(' value="" ', ' value="" class="blank" ', form_dropdown($options['name'], $options['options'] ,$options['selected'], $options['extras']) ); ?>
				</td>
			</tr>
			<?php endif; ?>

			<?php 
				$options = array(
					'name'	=> 'purchased_items_discount_field',
					'selected' => $settings['purchased_items_discount_field'],
					'options' => $purchased_items_fields, 
					'extras' => 'class="select field_purchased_items"',
				);
			?>
			<tr class="<?php echo alternator('even', 'odd');?>">
 				<td>
					<label><?=lang($options['name'])?></label>
 				</td>
				<td style='width:50%;'>

					<?php echo  str_replace(' value="" ', ' value="" class="blank" ', form_dropdown($options['name'], $options['options'] ,$options['selected'], $options['extras']) ); ?>
 				</td>
			</tr>

			<?php 
				$options = array(
					'name'	=> 'purchased_items_license_number_field',
					'selected' => $settings['purchased_items_license_number_field'],
					'options' => $purchased_items_fields, 
					'extras' => 'class="select field_purchased_items"',
				);
			?>
			<tr class="<?php echo alternator('even', 'odd');?>">
 				<td>
					<label><?=lang($options['name'])?></label>
 				</td>
				<td style='width:50%;'>

					<?php echo  str_replace(' value="" ', ' value="" class="blank" ', form_dropdown($options['name'], $options['options'] ,$options['selected'], $options['extras']) ); ?>
 				</td>
			</tr>
			
			
			<tr class="<?php echo alternator('even', 'odd');?>">
 				<td>
					<label><?=lang('purchased_items_license_number_type')?></label>
 				</td>
				<td style='width:50%;'>
					<select name='purchased_items_license_number_type' class='select field_purchased_items' >
						<option value='uuid' class="blank" ><?=lang('license_number_uuid')?></option>
					</select>
 				</td>
			</tr>
		</tbody>
	</table>
