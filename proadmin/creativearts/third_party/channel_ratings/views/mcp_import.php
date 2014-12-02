<?php echo $this->view('mcp_header'); ?>

<?php if (isset($candidates['solspace_rating']) !== FALSE):?>
	<?php $ss = $candidates['solspace_rating'];?>
	<h3><?=lang('rating:import:ss_rating')?></h3>

	<h4><?=lang('rating:import:totals')?>:</h4>
	<p>
		<?=lang('rating:import:ratings')?>: <?=$ss['totals']['ratings']?>&nbsp;&nbsp;&nbsp;&nbsp;
		<?=lang('rating:import:fields')?>: <?=$ss['totals']['fields']?> &nbsp;&nbsp;&nbsp;&nbsp;
		<?=lang('rating:import:reviews')?>: <?=$ss['totals']['reviews']?>
	</p> <br />

	<h4><?=lang('rating:import:fields')?></h4>
	<p><?=lang('rating:import:action:fields_exp')?></p>
	<div class="ss_fields">
		<a href="<?=$base_url?>&method=import_solspace_rating&action=fields" class="StartImport"><?=lang('rating:import:action:fields')?></a>
		<span class="importing"><?=lang('rating:importing')?></span>
		<span class="importing_done"><?=lang('rating:importing_done')?></span>
		<div class="results"></div>
	</div>

	<br /><br />

	<h4><?=lang('rating:import:ratings')?></h4>
	<p><?=lang('rating:import:action:ratings_exp')?></p>
	<div class="ss_ratings">
		<ul>
		<?php foreach ($ss['ratings_channels'] as $channel_id => $channel):?>
			<li>
				&bull; <?=$channel?>:
				<a href="<?=$base_url?>&method=import_solspace_rating&action=ratings&channel_id=<?=$channel_id?>" class="StartImport"><?=lang('rating:import:action:ratings')?></a>
				<span class="importing"><?=lang('rating:importing')?></span>
				<span class="importing_done"><?=lang('rating:importing_done')?></span>
			</li>
		<?php endforeach;?>
		</ul>

		<div class="results"></div>
	</div>


<?php endif;?>
