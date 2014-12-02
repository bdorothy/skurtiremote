<?php if ($total_entries = count($preview)): ?>

<form method="post" id="-previewed-entries" action="<?=$form_action?>">
	<div>
		<input type="hidden" name="<?=$csrf_token_name?>" value="<?=$csrf_token_value?>" />
		<input type="hidden" name="encoded_preview" value="<?=$encoded_preview?>" />
	</div>

	<div id="-replace" class="-inline-form">
		<label for="-replacement"><?=lang('replace')?>:</label>
		<input type="text" id="-replacement" name="replacement" />
		<button class="submit" type="submit"><?=lang('replace_selected')?></button>
	</div>

	<h2><?=lang('matching_entries_for')?> “<?=$keywords?>”: <?=$total_entries?></h2>

	<table cellpadding="0" cellspacing="0" class="mainTable">
		<colgroup>
			<col style="width:5%" />
			<col style="width:79%" />
			<col style="width:15%" />
			<col style="width:1%" />
		</colgroup>
		<thead>
			<tr>
				<th scope="col">#</th>
				<th scope="col"><?=lang('title')?></th>
				<th scope="col"><?=lang('channel')?></th>
				<th scope="col"><input type="checkbox" id="-select-all" /></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($preview AS $row): ?>
				<tr class="<?=mtt_zebra()?>">
					<td><?=$row['entry_id']?></td>
					<td>
						<a href="<?=BASE.'&amp;C=content_publish&amp;M=entry_form&amp;channel_id='.$row['channel_id'].'&amp;entry_id='.$row['entry_id']?>">
						<?=htmlspecialchars($row['title'])?></a>
						<dl>
							<?php foreach ($row['matches'] AS $field_id => $matches): ?>
								<dt><?=$channels[$row['channel_id']]['fields'][$field_id]?>:</dt>
								<?php foreach ($matches AS $match): ?>
									<dd>&hellip;<?=$match?>&hellip;</dd>
								<?php endforeach; ?>
							<?php endforeach; ?>
						</dl>
					</td>
					<td><?=htmlspecialchars($channels[$row['channel_id']]['channel_title'])?></td>
					<td><input type="checkbox" name="entries[<?=$row['channel_id']?>][]" value="<?=$row['entry_id']?>" /></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</form>

<?php else: ?>

	<div class="-feedback">
		<p><?=lang('no_matching_entries_found')?></p>
	</div>

<?php endif; ?>