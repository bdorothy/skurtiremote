<?php if (empty($log)): ?>

	<p><?=lang('replace_log_is_empty')?></p>

<?php else: ?>

	<div class="mtt-search-log-msg">
		<?php if ($is_admin): ?><a class="submit" href="<?=$base_url?>&amp;method=clear_replace_log"><?=lang('clear_replace_log')?></a><?php endif; ?>
		<p><?=$viewing_rows?></p>
	</div>

	<table cellpadding="0" cellspacing="0" style="width:100%" class="mainTable -list" id="mtt-search-log">
		<colgroup>
			<col style="width:20%" />
			<col style="width:20%" />
			<col style="width:20%" />
			<col style="width:20%" />
			<col style="width:20%" />
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><?=lang('keywords')?></th>
				<th scope="col"><?=lang('replacement')?></th>
				<th scope="col"><?=lang('member')?></th>
				<th scope="col"><?=lang('replace_date')?></th>
				<th scope="col"><?=lang('affected_entries')?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($log AS $row): ?>
				<tr class="<?=mtt_zebra()?>">
					<td><?=htmlspecialchars($row['keywords'])?></td>
					<td><?=htmlspecialchars($row['replacement'])?></td>
					<td><?=htmlspecialchars($row['member_id'])?></td>
					<td><?=htmlspecialchars($row['replace_date'])?></td>
					<td>
						<a href="<?=$base_url?>&amp;method=replace_details&amp;log_id=<?=$row['log_id']?>" class="-show-dialog">
							<?=count($row['entries'])?>
						</a>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<div id="-dialog"></div>

	<?php if ($pagination !== FALSE): ?>
		<p id="paginationLinks">
			<?=$pagination?>
		</p>
	<?php endif; ?>

<?php endif; ?>