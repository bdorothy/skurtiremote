<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<caption><?=lang('export_settings_header')?></caption>
		<thead class="">
			<tr>
				<th colspan="2">
					<strong><?=lang('export_settings_header')?></strong><br />
					<?=lang('export_settings_description')?>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr class="even">
				<td>
					<label><?=lang('export_to_file')?></label>
 				</td>
				<td style='width:50%;'>
					<a href="<?=BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method=export_settings'?>"><?=lang('export_to_file')?></a>
				</td>
			</tr>
		</tbody>
	</table>

	
