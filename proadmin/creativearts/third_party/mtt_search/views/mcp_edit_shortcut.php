<form method="post" action="<?=$base_url?>&amp;method=save_shortcut" class="-form">
	<div>
		<input type="hidden" name="shortcut_id" value="<?=$shortcut_id?>" />
		<input type="hidden" name="<?=$csrf_token_name?>" value="<?=$csrf_token_value?>" />
	</div>
	<table class="mainTable" cellspacing="0" cellpadding="0">
		<colgroup>
			<col style="width:30%" />
			<col style="width:70%" />
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><?=lang('preference')?></th>
				<th scope="col"><?=lang('setting')?></th>
			</tr>
		</thead>
		<tbody>
			<tr class="<?=mtt_zebra()?>">
				<td><label for="group"><?=lang('group')?></label></td>
				<td>
					<select name="group_id" id="group">
						<?php foreach ($groups AS $key => $val): ?>
							<option value="<?=$key?>"<?php if ($key == $group_id): ?> selected="selected"<?php endif; ?>><?=htmlspecialchars($val)?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr class="<?=mtt_zebra()?>">
				<td><label for="shortcut_label"><?=lang('shortcut_label')?></label></td>
				<td><input class="medium" type="text" name="shortcut_label" id="shortcut_label" value="<?=htmlspecialchars($shortcut_label)?>" /></td>
			</tr>
			<tr class="<?=mtt_zebra()?>">
				<td><label for="shortcut_name"><?=lang('shortcut_name')?></label></td>
				<td><input class="medium" type="text" name="shortcut_name" id="shortcut_name" value="<?=htmlspecialchars($shortcut_name)?>" /></td>
			</tr>
			<tr class="<?=mtt_zebra()?>">
				<td class="multiple"><label><?=lang('parameters')?></label></td>
				<td id="parameters"><button type="button"><b>+</b> <?=lang('add_parameter')?></button></td>
			</tr>
		</tbody>
	</table>
	<p><input type="submit" class="submit" value="<?=lang('save')?>" /></p>
</form>

<div id="parameter-template" style="display:none">
	<input type="text" name="param-key[]" class="param-key" /> = <input type="text" name="param-val[]" class="param-val" />
	<button type="button" class="remove"><b>&minus;</b> <?=lang('remove')?></button>
</div>

<?php if ($params_json): ?>
<script>
	MTT_Search_parameters = <?=$params_json?>;
</script>
<?php endif; ?>