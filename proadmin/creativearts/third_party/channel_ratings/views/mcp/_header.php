<div id="fmenu">
	<ul>
		<li class="<?=(($PageHeader == 'ratings')) ? ' current': ''?>"><a class="ratings" href="<?=$base_url?>&method=index"><?=lang('cr:ratings')?></a></li>
		<li class="<?=(($PageHeader == 'likes')) ? ' current': ''?>"><a class="likes" href="<?=$base_url?>&method=likes"><?=lang('cr:likes')?></a></li>
		<li class="<?=(($PageHeader == 'fields')) ? ' current': ''?>"><a class="fields" href="<?=$base_url?>&method=fields"><?=lang('cr:fields')?></a></li>
		<li class="<?=(($PageHeader == 'settings')) ? ' current': ''?>"><a class="settings" href="<?=$base_url?>&method=settings"><?=lang('cr:settings')?></a></li>

		<!--
		<li class="<?=(($PageHeader == 'import')) ? ' current': ''?>"><a class="templates" href="<?=$base_url?>&method=templates"><?=lang('form:templates')?></a></li>
		<li class="<?=(($PageHeader == 'recount')) ? ' current': ''?>"><a class="lists" href="<?=$base_url?>&method=lists"><?=lang('form:lists')?></a></li>
		<li class="<?=(($PageHeader == 'settings')) ? ' current': ''?>"><a class="settings" href="<?=$base_url?>&method=settings"><?=lang('form:settings')?></a></li>
	-->

		<li class="rightaligned dropdown <?=(($PageHeader == 'collections')) ? ' current': ''?>">
			<a href="#" class="collections dropdown-toggle" data-toggle="dropdown"><?=lang('cr:collection')?>: <small><?=$current_collection_label?></small></a>
			<ul class="dropdown-menu">
				<?php foreach($collections as $collection):?>
				<li><a href="<?=$base_url?>&method=switch_collection&collection_id=<?=$collection->collection_id?>" class="SwitchCollection" <?php if($current_collection ==$collection->collection_id) echo "style='font-weight:bold;'"?>><?=$collection->collection_label?> <?php if ($collection->default == 1) echo "<span style='font-weight:bold;color:red;'>*</span>";?></a></li>
				<?php endforeach;?>
				<li class="divider"></li>
				<li><a href="<?=$base_url?>&method=collections"><?=lang('cr:coll_manage')?></a></li>
			</ul>
		</li>
	</ul>
</div>

<div id="fbody">
