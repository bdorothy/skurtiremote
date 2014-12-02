<?php echo $this->view('mcp_header'); ?>

<?php foreach($candidates as $section_name => $sections): ?>
<div class="RecountBlock" rel="<?=$section_name?>">
	<h3><?=lang('rating:recount')?>: <?=lang('rating:'.$section_name)?></h3>

	<?php foreach($sections as $seg): ?>
	<div class="CountBlock Queued" rel="<?=implode('|', $seg)?>"><?=implode(' - ', $seg)?></div>
	<?php endforeach;?>

	<br clear="left"> <a href="#" class="StartRecount"><?=lang('rating:recount:start')?></a>
</div>
<?php endforeach;?>
