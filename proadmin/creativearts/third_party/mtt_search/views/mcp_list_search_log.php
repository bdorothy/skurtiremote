<?php if (empty($log) && ! $filtered): ?>

	<p><?=lang('search_log_is_empty')?></p>

<?php else: ?>

	<div class="mtt-search-log-msg">
		<?php if ($is_admin): ?><a class="submit" href="<?=$base_url?>&amp;method=clear_search_log"><?=lang('clear_search_log')?></a><?php endif; ?>
		<a class="submit" href="<?=$base_url?>&amp;method=export_search_log"><?=lang('export_search_log')?></a>
		<?php if ( ! empty($log)): ?><p><?=$viewing_rows?></p><?php endif; ?>
	</div>

	<form action="<?=$base_url?>&amp;method=search_log" method="post">
		<div>
			<input type="hidden" name="<?=$csrf_token_name?>" value="<?=$csrf_token_value?>" />
		</div>
		<fieldset id="mtt-search-filter">
			<legend><?=lang('filter_search_log')?></legend>
			<input type="text" name="filter[keywords]" placeholder="<?=lang('keywords')?>"
				value="<?=(isset($filter['keywords']) ? htmlspecialchars($filter['keywords']) : '')?>" />

			<select name="filter[member_id]">
				<option value=""><?=lang('member')?></option>
				<?php foreach($members AS $member_id => $screen_name): ?>
					<option value="<?=$member_id?>"
						<?=((isset($filter['member_id']) && $filter['member_id'] == $member_id) ? 'selected="selected"' : '')?>>
						<?=htmlspecialchars($screen_name)?>
					</option>
				<?php endforeach; ?>
			</select>

			<input type="text" name="filter[ip_address]" placeholder="<?=lang('ip_address')?>"
				value="<?=(isset($filter['ip_address']) ? htmlspecialchars($filter['ip_address']) : '')?>" />

			<select name="filter[search_date]">
				<option value=""><?=lang('search_date')?></option>
				<?php foreach($dates AS $date): ?>
					<option value="<?=$date?>"
						<?=((isset($filter['search_date']) && $filter['search_date'] == $date) ? 'selected="selected"' : '')?>>
						<?=htmlspecialchars($date)?>
					</option>
				<?php endforeach; ?>
			</select>

			<button type="submit" class="submit"><?=lang('filter')?></button>

		</fieldset>
	</form>

	<?php if ( ! empty($log)): ?>
		<table cellpadding="0" cellspacing="0" style="width:100%" class="mainTable -list" id="mtt-search-log">
			<colgroup>
				<col style="width:25%" />
				<col style="width:15%" />
				<col style="width:15%" />
				<col style="width:15%" />
				<col style="width:29%" />
				<?php if ($member_can_manage_shortcuts): ?><col style="width:1%" /><?php endif; ?>
			</colgroup>
			<thead>
				<tr>
					<th scope="col"><?=lang('keywords')?></th>
					<th scope="col"><?=lang('member')?></th>
					<th scope="col"><?=lang('ip_address')?></th>
					<th scope="col"><?=lang('search_date')?></th>
					<th scope="col" id="params-header"><?=lang('parameters')?></th>
					<?php if ($member_can_manage_shortcuts): ?><th scope="col"></th><?php endif; ?>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($log AS $row): ?>
					<tr class="<?=mtt_zebra()?>">
						<td><?=htmlspecialchars($row['keywords'])?></td>
						<td><?=htmlspecialchars($row['member_id'])?></td>
						<td><?=htmlspecialchars($row['ip_address'])?></td>
						<td><?=htmlspecialchars($row['search_date'])?></td>
						<td class="params">
							<?php if ($row['parameters']): ?>
								<ul>
									<?php foreach ($row['parameters'] AS $key => $val): ?>
										<li><strong><?=$key?></strong>: <?=htmlspecialchars(is_array($val) ? implode('|', $val) : $val)?></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</td>
						<?php if ($member_can_manage_shortcuts): ?>
						<td class="shortcut"><a href="<?=$row['shortcut_url']?>" title="<?=lang('create_shortcut_from_log')?>"
							style="background-image:url(<?=$themes_url?>cp_themes/default/images/ui-icons_505050_256x240.png)">&rarr;</a></td>
						<?php endif; ?>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php if ($pagination !== FALSE): ?>
			<p id="paginationLinks">
				<?=$pagination?>
			</p>
		<?php endif; ?>

	<?php else: ?>

		<div class="-feedback">
			No results for this filter
		</div>

	<?php endif; ?>


<?php endif; ?>