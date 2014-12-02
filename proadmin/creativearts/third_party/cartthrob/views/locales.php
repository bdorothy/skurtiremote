<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<caption><?=lang('locales_header')?></caption>
		<tbody>
			<tr class="even">
				<td>
					<label><?=lang('locales_countries')?></label>
					<div class="subtext"><?=lang('locales_countries_description')?></div>
 				</td>
				<td style='width:50%;'>
					<?php $select_all = ! element('locales_countries', $settings); ?>
					<label style="display:block;font-weight:normal;"><?=form_checkbox('locales_countries', '', $select_all)?>&nbsp;<?=lang('locales_countries_all')?></label>
					<?=form_multiselect('locales_countries[]', $countries, element('locales_countries', $settings), ($select_all) ? 'disabled="disabled"' : '' )?>
				</td>
			</tr>
		</tbody>
	</table>
