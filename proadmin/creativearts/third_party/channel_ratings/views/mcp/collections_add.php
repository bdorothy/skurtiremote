<?php echo $this->view('mcp/_header'); ?>
<div class="cbody"><div id="fcontents">

<?=form_open($base_url_short.AMP.'method=update_collection')?>
<div id="crcontentwrapper" style="width:100%">
	<div id="title_block">
		<h2><?=lang('cr:collections_add_long')?></h2>
	</div>

	<div id="crcontent">
		<table class="FormTable" cellspacing="0" cellpadding="0" border="0" width="400px">
		<tbody>
		    <tr>
		          <td><label><?=lang('cr:coll_label')?></label></td>
		          <td><?=form_input('collection_label', $collection_label, " id='slugsource' ")?></td>
		    </tr>
		    <tr>
		          <td><label><?=lang('cr:coll_name')?></label></td>
		          <td><?=form_input('collection_name', $collection_name, " id='slugdest' ")?></td>
		    </tr>
		    <tr>
		          <td><label><?=lang('cr:default')?></label></td>
		          <td><?=form_dropdown('default', array('0' => lang('cr:no'), '1' => lang('cr:yes')), $default)?></td>
		    </tr>
		</tbody>
		</table>
	</div>
</div>

<?=form_hidden('collection_id', $collection_id)?>

&nbsp;&nbsp;&nbsp;<button class="btn btn-primary"><?=lang('cr:save')?> <?=lang('cr:collection')?></button>
<?=form_close()?>

<br clear="all">
</div></div>
<?php echo $this->view('mcp/_footer'); ?>
