<?php echo $this->view('mcp/_header'); ?>
<div class="cbody"><div id="fcontents">


<div id="crcontentwrapper" style="width:100%">
	<div id="title_block">
		<h2><?=lang('cr:settings')?></h2>
	</div>

	<div id="crcontent">
		<table class="FormTable" cellspacing="0" cellpadding="0" border="0" width="400px">
		<tbody>
			<tr>
		          <td><label>AJAX ACT URL</label></td>
		          <td><?=$ajax_act_url?></td>
		    </tr>
		    <tr>
		          <td><label><?=lang('cr:bayesian_act_url')?></label></td>
		          <td><?=$bayesian_act_url?></td>
		    </tr>
		</tbody>
		</table>


	</div>
</div>

<br clear="all">
</div></div>
<?php echo $this->view('mcp/_footer'); ?>