<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<caption><?=lang('coupon_options_header')?></caption>
		<thead class="">
			<tr>
				<th colspan="2">
					<strong><?=lang('coupon_options_heading')?></strong><br />
					<?=lang('coupon_options_description')?>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr class="even">
				<td>
					<label><?=lang('coupon_code_channel')?></label>
				</td>
				<td style='width:50%;'>
					<select name='coupon_code_channel' class="channels" id="select_coupon_code">
					<option value=''></option>
					<?php foreach ($channels as $channel) : ?>
					<option value="<?=$channel['channel_id']?>" <?php if ($settings['coupon_code_channel'] == $channel['channel_id']) : ?>selected="selected"<?php endif; ?>><?=$channel['channel_title']?></option>
					<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr class="odd">
				<td>
					<label><?=lang('coupon_code_field')?></label>
				</td>
				<td style='width:50%'>
					<select name='coupon_code_field' class="field_coupon_code">
					<option value='' class="blank" ></option>
					<option value='title' class="blank" <?php if ($settings['coupon_code_field'] == 'title') : ?>selected="selected"<?php endif; ?>><?=lang('title')?></option>
					<?php if ($settings['coupon_code_channel']) : ?>
					<?php foreach ($fields[$settings['coupon_code_channel']] as $field) : ?>
					<option value="<?=$field['field_id']?>" <?php if ($settings['coupon_code_field'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>><?=$field['field_label']?></option>
					<?php endforeach; ?>
					<?php endif; ?>
					</select>
				</td>
			</tr>
			<tr class="even">
				<td>
					<label><?=lang('coupon_code_type')?></label>
				</td>
				<td style='width:50%'>
					<select name='coupon_code_type' class="field_coupon_code">
					<option value='' class="blank" ></option>
					<?php if ($settings['coupon_code_channel']) : ?>
					<?php foreach ($fields[$settings['coupon_code_channel']] as $field) : ?>
					<option value="<?=$field['field_id']?>" <?php if ($settings['coupon_code_type'] == $field['field_id']) : ?>selected="selected"<?php endif; ?>><?=$field['field_label']?></option>
					<?php endforeach; ?>
					<?php endif; ?>
					</select>
				</td>
			</tr>
		</tbody>
	</table>
