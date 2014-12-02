<table id="RatingsDT" class="datatable CRTable" cellspacing="0" cellpadding="0" border="0" width="100%" data-url="" data-name="<?=$rating_type?>" data-checkable="yes" data-savestate="yes">
	<thead>
		<tr>
			<th><input type="checkbox" class="CheckAll">&nbsp;&nbsp;&nbsp;&nbsp;ID</th>
			<?php foreach($columns['standard'] as $col_name => $col):?>
			<th><?=$col['name']?></th>
			<?php endforeach;?>
			<?php foreach($columns['extra'] as $col_name => $col):?>
			<th><?=$col['name']?></th>
			<?php endforeach;?>
		</tr>
	</thead>
	<tbody>

	</tbody>
</table>