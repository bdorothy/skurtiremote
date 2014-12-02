<?php echo $this->view('mcp/_header'); ?>
<div class="cbody"><div id="fcontents">

<?=form_open($base_url_short.AMP.'method=update_field')?>
<div id="crcontentwrapper" style="width:100%">
	<div id="title_block">
		<h2><?=lang('cr:field_add_l')?></h2>
	</div>

	<div id="crcontent">
		<table class="FormTable" cellspacing="0" cellpadding="0" border="0" width="400px">
		<tbody>
		    <tr>
		          <td><label><?=lang('cr:field_label')?></label></td>
		          <td><?=form_input('field_title', $title, " id='slugsource' ")?></td>
		    </tr>
		    <tr>
		          <td><label><?=lang('cr:field_name')?></label></td>
		          <td><?=form_input('field_name', $title, " id='slugdest' ")?></td>
		    </tr>
		    <tr>
		          <td><label><?=lang('cr:required')?></label></td>
		          <td><?=form_dropdown('field_required', array('1' => lang('cr:yes'), '0' => lang('cr:no')), $required )?></td>
		    </tr>
		</tbody>
		</table>
	</div>
</div>

<?=form_hidden('field_id', $field_id)?>
<?=form_hidden('collection_id', $current_collection)?>

&nbsp;&nbsp;&nbsp;<button class="btn btn-primary"><?=lang('cr:save')?> <?=lang('cr:field')?></button>
<?=form_close()?>

<br clear="all">
</div></div>
<?php echo $this->view('mcp/_footer'); ?>