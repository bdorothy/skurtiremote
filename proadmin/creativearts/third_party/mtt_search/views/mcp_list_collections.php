<p><a class="submit" href="<?=$new_collection_url?>">+ <?=lang('new_collection')?></a></p>

<?php if (empty($collections)): ?>

	<p><?=lang('no_collections_exist')?></p>

<?php else: ?>

	<table cellpadding="0" cellspacing="0" class="mainTable -list" id="mtt-search-collections">
		<colgroup>
			<col style="width:5%" />
			<col style="width:15%" />
			<col style="width:15%" />
			<col style="width:15%" />
			<col style="width:45%" />
			<col style="width:5%" />
		</colgroup>
		<thead>
			<tr>
				<th scope="col">#</th>
				<th scope="col"><?=lang('collection_label')?></th>
				<th scope="col"><?=lang('collection_name')?></th>
				<th scope="col"><?=lang('channel')?></th>
				<th scope="col"><?=lang('search_index')?></th>
				<th scope="col"><?=lang('delete')?></th>
			</tr>
		</thead>
		<?php if (count($collections) > 1): ?>
			<tfoot>
				<tr>
					<td colspan="4"></td>
					<td><a href="#" id="build-all-indexes"><?=lang('build_all_indexes')?></a></td>
					<td></td>
				</tr>
			</tfoot>
		<?php endif; ?>
		<tbody>
			<?php foreach ($collections AS $row): ?>
				<tr class="<?=mtt_zebra()?>">
					<td><?=$row['collection_id']?></td>
					<td><a href="<?=$base_url?>&amp;method=edit_collection&amp;collection_id=<?=$row['collection_id']?>" title="<?=lang('edit_preferences')?>"><?=htmlspecialchars($row['collection_label'])?></a></td>
					<td><?=htmlspecialchars($row['collection_name'])?></td>
					<td><?=htmlspecialchars($row['channel'])?></td>
					<td class="-index ready">
						<?php if ($totals[$row['channel_id']]): ?>
							<div class="index-options">
								<a href="<?=$row['index_url']?>" title="<?=lang('total_entries').': '.$totals[$row['channel_id']]?>"><?=lang(($row['index_status'] == 'empty') ? 'build_index' : 'rebuild_index')?></a>
								<?php if ($row['index_status'] != 'ok'): ?><em><?=lang('index_status_'.$row['index_status'])?></em><?php endif; ?>
							</div>
							<div class="index-progress-bar">
								0 / <?=$totals[$row['channel_id']]?>
							</div>
						<?php else: ?>
							<div class="index-options"><?=lang('no_entries')?></div>
						<?php endif; ?>
					</td>
					<td>
						<a href="<?=$base_url?>&amp;method=delete_collection_confirm&amp;collection_id=<?=$row['collection_id']?>">
							<img src="<?=$themes_url?>cp_themes/default/images/icon-delete.png" alt="<?=lang('delete')?>" />
						</a>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

<?php endif; ?>