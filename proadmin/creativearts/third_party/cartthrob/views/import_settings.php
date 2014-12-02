<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<caption><?=lang('import_settings_header')?></caption>
		<thead class="">
			<tr>
				<th colspan="2">
					<strong><?=lang('import_settings_header')?></strong><br />
					<?=lang('import_settings_description')?>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr class="odd">
				<td>
					<label><?=lang('import_import_settings')?></label>
					<div class="subtext"><?=lang('import_overwrite_settings')?></div>
 				</td>
				<td style='width:50%;'>
					<?=$form_open?>
						<input type="file" name="settings" />
						<input type="submit" name="submit" value="Submit" class="submit" />
					<?=form_close()?>
				</td>
			</tr>
		</tbody>
	</table>

	
	
	
	