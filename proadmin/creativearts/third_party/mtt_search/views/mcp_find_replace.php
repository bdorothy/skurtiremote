<form method="post" id="-find-replace" action="<?=$base_url?>&amp;method=find&amp;preview=yes">
	<div>
		<input type="hidden" name="<?=$csrf_token_name?>" value="<?=$csrf_token_value?>" />
	</div>

	<div id="-filters">

		<ul id="-tabs">
			<li class="active"><a href="#-channel-fields"><?=lang('channels')?></a></li>
			<?php if ($categories): ?><li><a href="#-categories"><?=lang('categories')?></a></li><?php endif; ?>
		</ul>

		<fieldset id="-channel-fields" class="tab active">
			<div class="-pane">
				<div class="-boxes">
					<label><input type="checkbox" class="-select-all" /> <?=lang('select_all')?></label>
				</div>
				<?php foreach ($channels AS $channel_id => $row): ?>
				<div class="-boxes">
					<h4><span><?=htmlspecialchars($row['channel_title'])?></span></h4>
					<?php foreach ($row['fields'] AS $field_id => $field_name): ?>
						<label>
							<input type="checkbox" name="fields[<?=$channel_id?>][]" value="<?=$field_id?>" />
							<?=htmlspecialchars($field_name)?>
						</label>
					<?php endforeach; ?>
				</div>
				<?php endforeach; ?>
			</div>

		</fieldset>

		<?php if ($categories): ?>
		<fieldset id="-categories" class="tab">
			<div class="-pane">
				<div class="-boxes">
					<label><input type="checkbox" class="-select-all" /> <?=lang('select_all')?></label>
				</div>
				<?php foreach ($categories AS $group_id => $row): ?>
				<div class="-boxes">
					<h4><span><?=htmlspecialchars($row['group_name'])?></span></h4>
					<?php foreach ($row['cats'] AS $cat_id => $cat): ?>
						<label>
							<?=$cat['indent']?>
							<input type="checkbox" name="cats[]" value="<?=$cat_id?>" />
							<?=$cat['name']?>
						</label>
					<?php endforeach; ?>
				</div>
				<?php endforeach; ?>
			</div>
		</fieldset>
		<?php endif; ?>

	</div>
	<div id="-find" class="-inline-form">
		<label for="-keywords"><?=lang('find')?>:</label>
		<input type="text" id="-keywords" name="keywords" />
		<button class="submit" type="submit"><?=lang('show_preview')?></button>
	</div>
</form>

<div id="-preview">
	<?php if (isset($feedback)) include(PATH_THIRD.'/mtt_search/views/ajax_replace_feedback.php'); ?>
	<?php if (isset($preview))  include(PATH_THIRD.'/mtt_search/views/ajax_preview.php'); ?>
</div>
