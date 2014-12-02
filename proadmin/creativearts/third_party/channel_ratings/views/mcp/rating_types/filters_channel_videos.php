<div class="filter">
	<input type="text" name="entry_title" placeholder="<?=lang('cr:entry_title')?>">
</div>

<div class="filter">
	<?=form_multiselect('channels[]', $channels, '', ' class="chosen" data-placeholder="'.lang('cr:channel').'" style="width:97.7%;" ')?>
</div>

<div class="filter">
	<input type="text" name="video_title" placeholder="<?=lang('cr:video_title')?>">
</div>

<div class="filter">
	<?=form_multiselect('video_service[]', array('youtube' => 'Youtube', 'vimeo' => 'Vimeo'), '', ' class="chosen" data-placeholder="'.lang('cr:video_service').'" style="width:97.7%;" ')?>
</div>

<div class="filter">
	<input type="text" name="date_from" class="datepicker" placeholder="<?=lang('cr:date_from')?>">
</div>

<div class="filter">
	<input type="text" name="date_to" class="datepicker" placeholder="<?=lang('cr:date_to')?>">
</div>