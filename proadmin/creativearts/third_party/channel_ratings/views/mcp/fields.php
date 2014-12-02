<?php echo $this->view('mcp/_header'); ?>
<div class="cbody"><div id="fcontents">


<div id="crcontentwrapper" style="width:100%">
	<div id="title_block">
		<h2><?=lang('cr:rating')?> <?=lang('cr:fields')?></h2>

		<a href="<?=$base_url?>&method=add_field" class="linkbtn add"><span><?=lang('cr:field_add')?></span></a>
	</div>

	<div id="crcontent">
		<table class="CRTable" cellspacing="0" cellpadding="0" border="0" width="100%">
			<thead>
				<tr>
					<th>ID</th>
					<th><?=lang('cr:field_label')?></th>
					<th><?=lang('cr:field_name')?></th>
					<th><?=lang('cr:required')?></th>
					<th><?=lang('cr:actions')?></th>
				</tr>
			</thead>
			<tbody>
				<?php if (empty($fields) == TRUE):?> <tr><td colspan="99"><?=lang('cr:no_fields')?></td></tr> <?php endif;?>
				<?php foreach($fields as $field):?>
				<tr>
					<td><?=$field->field_id?></td>
					<td><?=$field->title?></td>
					<td><?=$field->short_name?></td>
					<td><?php if($field->required ==1):?> <strong style="color:green"><?=lang('cr:yes')?></strong> <?php else:?> <strong style="color:blue"><?=lang('cr:no')?></strong> <?php endif;?></td>
					<td>
						<a href="<?=$base_url?>&method=add_field&field_id=<?=$field->field_id?>" class="label"><?=lang('cr:edit')?></a>
						<a href="<?=$base_url?>&method=update_field&delete=yes&field_id=<?=$field->field_id?>" class="label label-important DelIcon"><?=lang('cr:delete')?></a>
					</td>
				</tr>
				<?php endforeach;?>
			</tbody>
		</table>
	</div>
</div>

<br clear="all">
</div></div>
<?php echo $this->view('mcp/_footer'); ?>