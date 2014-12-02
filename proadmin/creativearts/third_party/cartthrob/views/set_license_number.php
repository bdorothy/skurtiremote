<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<caption><?=lang('set_license_number_header')?></caption>
		<thead class="visualEscapism">
			<tr>
				<th><?=lang('preference')?></th><th><?=lang('setting')?></th>
			</tr>
		</thead>
		<tbody>
			<tr class="even">
				<td>
					<label><?=lang('license_number_label')?></label>
 				</td>
				<td style='width:50%;'>
					<input  dir='ltr' type='text' name='license_number' id='license_number' value='<?=$settings['license_number']?>' size='90' maxlength='100' />
				</td>
			</tr>
		</tbody>
	</table>
