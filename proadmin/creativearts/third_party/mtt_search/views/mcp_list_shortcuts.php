<p><a class="submit" href="<?=$new_shortcut_url?>">+ <?=lang('new_shortcut')?></a></p>

<?php if (empty($shortcuts)): ?>

	<p style="margin:0"><?=lang('no_shortcuts_in_group')?></p>

<?php else: ?>

	<table cellpadding="0" cellspacing="0" class="mainTable -list" id="mtt-search-shortcuts">
		<colgroup>
			<col style="width:2%" />
			<col style="width:5%" />
			<col style="width:44%" />
			<col style="width:44%" />
			<col style="width:5%" />
		</colgroup>
		<thead>
			<tr>
				<th></th>
				<th scope="col">#</th>
				<th scope="col"><?=lang('shortcut_label')?></th>
				<th scope="col"><?=lang('shortcut_name')?></th>
				<th scope="col"><?=lang('delete')?></th>
			</tr>
		</thead>
		<tbody id="sortable-shortcuts">
			<?php foreach ($shortcuts AS $row): ?>
				<tr class="<?=mtt_zebra()?>" data-id="<?=$row['shortcut_id']?>">
					<td class="drag-handle">&#9776;</td>
					<td><?=$row['shortcut_id']?></td>
					<td><a href="<?=$row['edit_url']?>"><?=htmlspecialchars($row['shortcut_label'])?></a></td>
					<td><?=htmlspecialchars($row['shortcut_name'])?></td>
					<td><a href="<?=$row['delete_url']?>">
						<img src="<?=$themes_url?>cp_themes/default/images/icon-delete.png" alt="<?=lang('delete')?>" />
					</a></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

<?php endif; ?>