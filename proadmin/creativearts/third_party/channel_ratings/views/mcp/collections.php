<?php echo $this->view('mcp/_header'); ?>
<div class="cbody"><div id="fcontents">


<div id="crcontentwrapper" style="width:100%">
	<div id="title_block">
		<h2><?=lang('cr:rating')?> <?=lang('cr:collections')?></h2>

		<a href="<?=$base_url?>&method=add_collection" class="linkbtn add"><span><?=lang('cr:collections_add')?></span></a>
	</div>

	<div id="crcontent">
		<table class="CRTable" cellspacing="0" cellpadding="0" border="0" width="100%">
			<thead>
				<tr>
					<th>ID</th>
					<th><?=lang('cr:coll_label')?></th>
					<th><?=lang('cr:coll_name')?></th>
					<th><?=lang('cr:default')?></th>
					<th><?=lang('cr:actions')?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($collections as $coll):?>
				<tr>
					<td><?=$coll->collection_id?></td>
					<td><?=$coll->collection_label?></td>
					<td><?=$coll->collection_name?></td>
					<td><?php if($coll->default ==1):?> <strong style="color:green"><?=lang('cr:yes')?></strong> <?php else:?> <strong style="color:blue"><?=lang('cr:no')?></strong> <?php endif;?></td>
					<td>
						<a href="<?=$base_url?>&method=add_collection&collection_id=<?=$coll->collection_id?>" class="label"><?=lang('cr:edit')?></a>
						<?php if (count($collections) > 1 && $coll->default == 0):?>
						<a href="<?=$base_url?>&method=update_collection&delete=yes&collection_id=<?=$coll->collection_id?>" class="label label-important DelIcon"><?=lang('cr:delete')?></a>
						<?php endif;?>
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