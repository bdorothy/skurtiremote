<form method="post" action="<?=$base_url?>&amp;method=save_group" class="-form">
	<div>
		<input type="hidden" name="group_id" value="<?=$group_id?>" />
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
				<td>
					<label for="group_label"><em>*</em> <?=lang('group_label')?></label>
				</td>
				<td><input class="medium" type="text" name="group_label" id="group_label" value="<?=htmlspecialchars($group_label)?>" /></td>
			</tr>
		</tbody>
	</table>
	<p><input type="submit" class="submit" value="<?=lang('save')?>" /></p>
</form>