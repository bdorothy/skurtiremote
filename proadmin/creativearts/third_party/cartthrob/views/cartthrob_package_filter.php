<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<div class="cartthrobPackageFilter">
	
	<input type="search" class="keywords" results="5" placeholder="<?=lang('keywords')?>">&nbsp;&nbsp;
	<?=form_dropdown('', $channels, NULL, 'class="channel_id"').NBS.NBS?>
	<?=form_dropdown('', array('' => lang('date_range'), 1 => lang('past_day'), 7 => lang('past_week'), 31 => lang('past_month'), 182 => lang('past_six_months'), 365 => lang('past_year')), NULL, 'class="date_range"').NBS.NBS?>
	<?=form_dropdown('', $categories, NULL, 'class="cat_id"').NBS.NBS?>
	<?=form_dropdown('', array('title' => lang('title_only'), 'body' => lang('title_and_body')), NULL, 'class="search_in"').NBS.NBS?>
	<?//=form_dropdown('', $order_select_options, $order_selected, 'id="f_select_options"').NBS.NBS?>
	<?//=form_dropdown('', $status_select_options, $status_selected, 'id="f_status"').NBS.NBS?>
	<?//=form_dropdown('', $perpage_select_options, $perpage_selected, 'id="f_perpage"')?>
	<?//=form_label('', lang('exact_match', 'exact_match').NBS.NBS.form_checkbox('exact_match', 'yes', $exact_match, 'id="exact_match"'))?>
	
	<ul class="cartthrobPackageFilteredEntries"></ul>
</div>
