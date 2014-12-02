<p><a class="submit" href="<?=$new_group_url?>">+ <?=lang('new_group')?></a></p>

<?php if (empty($groups)): ?>

	<p style="margin:0"><?=lang('no_groups_exist')?></p>

<?php else: ?>

	<table cellpadding="0" cellspacing="0" class="mainTable -list">
		<colgroup>
			<col style="width:5%" />
			<col style="width:45%" />
			<col style="width:45%" />
			<col style="width:5%" />
		</colgroup>
		<thead>
			<tr>
				<th scope="col">#</th>
				<th scope="col"><?=lang('group_label')?></th>
				<th scope="col"><?=lang('edit_group')?></th>
				<th scope="col"><?=lang('delete')?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($groups AS $row): ?>
				<tr class="<?=mtt_zebra()?>">
					<td><?=$row['group_id']?></td>
					<td><a href="<?=$row['show_url']?>"><?=htmlspecialchars($row['group_label'])?></a>
						(<?=(isset($counts[$row['group_id']]) ? $counts[$row['group_id']] : '0')?>)</td>
					<td><a href="<?=$row['edit_url']?>"><?=lang('edit_group')?></a></td>
					<td><a href="<?=$row['delete_url']?>">
						<img src="<?=$themes_url?>cp_themes/default/images/icon-delete.png" alt="<?=lang('delete')?>" />
					</a></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

<?php endif; ?>