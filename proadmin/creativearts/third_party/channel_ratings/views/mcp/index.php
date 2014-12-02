<?php echo $this->view('mcp/_header'); ?>
<div class="cbody" id="mcp-ratings"><div id="fcontents">


<div id="crsidebar">
	<h6><?=lang('cr:rating_types')?></h6>
	<div class="sidebar-labels rating_type_toggler">
	<?php foreach($rating_types as $type):?>
		<span class="label bs-tooltip" title="<?=form_prep(lang('cr:type_long:' . $type))?>" data-type="<?=$type?>" data-section="ratings"><?=lang('cr:type:' . $type)?></span>
	<?php endforeach;?>
	<br clear="all">
	</div>

	<h6><?=lang('cr:filter_by')?></h6>
	<div id="SideBarFilters"></div>

	<h6><?=lang('cr:vis_cols')?> <span class="reset" data-type="columns"><?=lang('cr:reset')?></span></h6>
	<div id="SideBarColumns"></div>
</div>


<div id="crcontentwrapper">
	<div id="title_block">
		<h2><?=lang('cr:ratings')?></h2>

		<a href="#" class="linkbtn RatingAction disabled OpenRating" data-type="rating" data-action="open"><span><?=lang('cr:open')?></span></a>
		<a href="#" class="linkbtn RatingAction disabled CloseRating" data-type="rating" data-action="close"><span><?=lang('cr:close')?></span></a>
		<a href="#" class="linkbtn RatingAction disabled DeleteRating" data-type="rating" data-action="delete"><span><?=lang('cr:delete')?></span></a>
	</div>

	<div id="crcontent">

	</div>
</div>

<br clear="all">
</div></div>
<?php echo $this->view('mcp/_footer'); ?>