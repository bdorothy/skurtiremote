<div id="-modal">
<table cellpadding="0" cellspacing="0" style="width:100%" class="mainTable">
	<colgroup>
		<col style="width:10%" />
		<col style="width:70%" />
		<col style="width:20%" />
	</colgroup>
	<thead>
		<tr>
			<th scope="col">#</th>
			<th scope="col"><?=lang('title')?></th>
			<th scope="col"><?=lang('channel')?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($entries AS $row): ?>
			<tr class="<?=mtt_zebra()?>">
				<td><?=$row['entry_id']?></td>
				<td>
					<a href="<?=BASE?>&amp;C=content_publish&amp;M=entry_form&amp;channel_id=<?=$row['channel_id']?>&amp;entry_id=<?=$row['entry_id']?>">
						<?=htmlspecialchars($row['title'])?>
					</a>
				</td>
				<td><?=htmlspecialchars($channels[$row['channel_id']])?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
</div>

<!-- <h3>Channels &amp; Fields</h3>
<?php foreach ($fields AS $channel => $fields): ?>
	<h4><?=htmlspecialchars($channel)?></h4>
	<ul>
		<?php foreach ($fields AS $field): ?>
			<li><?=htmlspecialchars($field)?></li>
		<?php endforeach; ?>
	</ul>
<?php endforeach; ?>
 -->

