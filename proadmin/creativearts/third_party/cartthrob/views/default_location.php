<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<caption><?=lang('default_location_header')?></caption>
		<thead class="">
			<tr>
				<th><strong><?=lang('default_location_form_field_name')?></strong><br />
				<?=lang('default_location_form_field_description')?></th>
				<th><strong><?=lang('default_location_default_display_text')?></strong><br />
				<?=lang('default_location_default_display_description')?></th>
			</tr>
		</thead>
		<tbody>
			<tr class="even">
				<td>
					<label><?=lang('default_location_state')?></label>
 				</td>
				<td style='width:50%;'>
					<select name="default_location[state]" class="states_blank" value="<?=element('state', element('default_location', $settings))?>"></select>
				</td>
			</tr>
			<tr class="odd">
				<td>
					<label><?=lang('default_location_zip')?></label>
 				</td>
				<td style='width:50%;'>
					<input name="default_location[zip]" type="text" value="<?=element('zip', element('default_location', $settings))?>" />
				</td>
			</tr>
			<tr class="even">
				<td>
					<label><?=lang('default_location_country_code')?></label>
 				</td>
				<td style='width:50%;'>
					<select name="default_location[country_code]" class="countries_blank" value="<?=element('country_code', element('default_location', $settings))?>"></select>
				</td>
			</tr>
			<tr class="odd">
				<td>
					<label><?=lang('default_location_region')?></label>
 				</td>
				<td style='width:50%;'>
					<input name="default_location[region]" type="text" value="<?=element('region', element('default_location', $settings))?>" />
				</td>
			</tr>
			<tr class="even">
				<td>
					<label><?=lang('default_location_shipping_state')?></label>
 				</td>
				<td style='width:50%;'>
					<select name="default_location[shipping_state]" class="states_blank" value="<?=element('shipping_state', element('default_location', $settings))?>"></select>
				</td>
			</tr>
			<tr class="odd">
				<td>
					<label><?=lang('default_location_shipping_zip')?></label>
 				</td>
				<td style='width:50%;'>
					<input name="default_location[shipping_zip]" type="text" value="<?=element('shipping_zip', element('default_location', $settings))?>" />
				</td>
			</tr>
			<tr class="even">
				<td>
					<label><?=lang('default_location_shipping_country_code')?></label>
 				</td>
				<td style='width:50%;'>
					<select name="default_location[shipping_country_code]" class="countries_blank" value="<?=element('shipping_country_code', element('default_location', $settings))?>"></select>
				</td>
			</tr>
			<tr class="odd">
				<td>
					<label><?=lang('default_location_shipping_region')?></label>
 				</td>
				<td style='width:50%;'>
					<input name="default_location[shipping_region]" type="text" value="<?=element('shipping_region', element('default_location', $settings))?>" />
				</td>
			</tr>
		</tbody>
	</table>
