<div class="filter">
	<input type="text" name="entry_title" placeholder="<?=lang('cr:entry_title')?>">
</div>

<div class="filter">
	<?=form_multiselect('channels[]', $channels, '', ' class="chosen" data-placeholder="'.lang('cr:channel').'" style="width:97.7%;"')?>
</div>

<div class="filter">
	<input type="text" name="image_title" placeholder="<?=lang('cr:image_title')?>">
</div>

<div class="filter">
	<input type="text" name="filename" placeholder="<?=lang('cr:filename')?>">
</div>

<div class="filter">
	<input type="text" name="date_from" class="datepicker" placeholder="<?=lang('cr:date_from')?>">
</div>

<div class="filter">
	<input type="text" name="date_to" class="datepicker" placeholder="<?=lang('cr:date_to')?>">
</div>