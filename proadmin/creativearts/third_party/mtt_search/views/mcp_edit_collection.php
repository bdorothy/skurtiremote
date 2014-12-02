<?php if ($channels): ?>

<form method="post" action="<?=$base_url?>&amp;method=save_collection" class="-form">
	<div>
		<input type="hidden" name="collection_id" id="collection_id" value="<?=$collection_id?>" />
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
				<td><label for="collection_channel"><em>*</em> <?=lang('channel')?></label></td>
				<td>
					<select name="channel_id" id="collection_channel">
						<option value="">--</option>
						<?php foreach ($channels AS $c_id => $row): ?>
							<option value="<?=$c_id?>"<?php if ($channel_id == $c_id): ?> selected="selected"<?php endif; ?>><?=htmlspecialchars($row['channel_title'])?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr class="<?=mtt_zebra()?>">
				<td>
					<label for="collection_label"><em>*</em> <?=lang('collection_label')?></label>
					<p><?=lang('collection_label_notes')?></p>
				</td>
				<td><input class="medium" type="text" name="collection_label" id="collection_label" value="<?=htmlspecialchars($collection_label)?>" /></td>
			</tr>
			<tr class="<?=mtt_zebra()?>">
				<td>
					<label for="collection_name"><em>*</em> <?=lang('collection_name')?></label>
					<p><?=lang('collection_name_notes')?></p>
				</td>
				<td><input class="medium" type="text" name="collection_name" id="collection_name" value="<?=htmlspecialchars($collection_name)?>" /></td>
			</tr>
			<tr class="<?=mtt_zebra()?>">
				<td>
					<label for="collection_modifier"><?=lang('collection_modifier')?></label>
					<p><?=lang('collection_modifier_notes')?></p>
				</td>
				<td><input class="small" name="modifier" id="collection_modifier" decimal="." size="5" type="number" min="0.5" max="10" step="0.5" value="<?=$modifier?>" /></td>
			</tr>
		</tbody>
	</table>

	<?php foreach ($channels AS $c_id => $row): ?>
	<div class="mtt-search-collection-settings hidden" id="mtt-search-channel-<?=$c_id?>">

		<table class="mainTable" cellspacing="0" cellpadding="0">
			<colgroup>
				<col style="width:30%" />
				<col style="width:40%" />
				<col style="width:30%" />
			</colgroup>
			<thead>
				<tr>
					<th scope="col"><?=lang('field')?></th>
					<th scope="col"><?=lang('weight')?></th>
					<th scope="col"><?=lang('excerpt')?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($row['fields'] AS $field_id => $field_label): ?>
				<tr class="<?=mtt_zebra()?>">
					<td><?=$field_label?></td>
					<td><?=mtt_weight_selection("settings[{$c_id}][{$field_id}]", @$settings[$field_id])?></td>
					<td>
						<input type="radio" name="excerpt[<?=$c_id?>]" value="<?=$field_id?>"
							<?php if ($excerpt == $field_id): ?> checked="checked"<?php endif; ?> />
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<?php foreach ($row['cat_group'] AS $cat_group): ?>
			<?php $group = $cat_groups[$cat_group]; ?>

			<table class="mainTable" cellspacing="0" cellpadding="0">
				<colgroup>
					<col style="width:30%" />
					<col style="width:70%" />
				</colgroup>
				<thead>
					<tr>
						<th scope="col"><?=lang('category_group')?>: <?=$group['group_name']?></th>
						<th scope="col"><?=lang('weight')?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($group['fields'] AS $field_id => $field_label): ?>
						<?php $key = $cat_group.':'.$field_id; ?>
						<tr class="<?=mtt_zebra()?>">
							<td><?=$field_label?></td>
							<td><?=mtt_weight_selection("settings[{$c_id}][{$key}]", @$settings[$key])?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endforeach; ?>

	</div>
	<?php endforeach; ?>

	<p><input type="submit" class="submit" value="<?=lang('save')?>" /></p>

</form>

<?php else : ?>

	<p><?=lang('no_searchable_channels_found')?></p>

<?php endif; ?>