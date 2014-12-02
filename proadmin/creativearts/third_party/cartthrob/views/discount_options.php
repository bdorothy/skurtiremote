<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<caption><?=lang('discount_options_header')?></caption>
		<thead class="">
			<tr>
				<th colspan="2">
					<strong><?=lang('discount_options_header')?></strong><br />
					<?=lang('discount_options_description')?>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr class="even">
				<td>
					<label><?=lang('discount_channel')?></label>
				</td>
				<td style='width:50%;'>
					<select name='discount_channel' class="channels" id="select_discount">
					<option value=''></option>
					<?php foreach ($channels as $channel) : ?>
					<option value="<?=$channel['channel_id']?>" <?php if ($settings['discount_channel'] == $channel['channel_id']) : ?>selected="selected"<?php endif; ?>><?=$channel['channel_title']?></option>
					<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr class="odd">
				<td>
					<label><?=lang('discount_type')?></label>
				</td>
				<td style='width:50%;'>
					<select name='discount_type' class="field_discount">
					<option value='' class="blank" ></option>
					<?php if ($settings['discount_channel']) : ?>
					<?php foreach ($fields[$settings['discount_channel']] as $field) : ?>
					<option value="<?=$field['field_id']?>" <?php if ($settings['discount_type'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>><?=$field['field_label']?></option>
					<?php endforeach; ?>
					<?php endif; ?>
					</select>
				</td>
			</tr>
		</tbody>
	</table>
