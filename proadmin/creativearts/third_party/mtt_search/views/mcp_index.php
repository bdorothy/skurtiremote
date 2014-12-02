<div id="mtt-search-index">

	<?php if ($member_can_manage || $member_can_view_search_log || $member_can_manage_shortcuts): ?>
		<fieldset>
			<h3><?=lang('site_search')?></h3>
			<ul>
				<?php if ($member_can_manage): ?>
					<li><a href="<?=$base_url?>&amp;method=collections"><?=lang('collections')?></a></li>
				<?php endif; ?>
				<?php if ($member_can_manage_shortcuts): ?>
					<li><a href="<?=$base_url?>&amp;method=groups"><?=lang('shortcuts')?></a></li>
				<?php endif; ?>
				<?php if ($member_can_view_search_log): ?>
					<li><a href="<?=$base_url?>&amp;method=search_log"><?=lang('search_log')?></a></li>
				<?php endif; ?>
			</ul>
		</fieldset>
	<?php endif; ?>

	<?php if ($member_can_replace || $member_can_view_replace_log): ?>
		<fieldset>
			<h3><?=lang('find_replace')?></h3>
			<ul>
				<?php if ($member_can_replace): ?>
					<li><a href="<?=$base_url?>&amp;method=find"><?=lang('find_replace')?></a></li>
				<?php endif; ?>
				<?php if ($member_can_view_replace_log): ?>
					<li><a href="<?=$base_url?>&amp;method=replace_log"><?=lang('replace_log')?></a></li>
				<?php endif; ?>
			</ul>
		</fieldset>
	<?php endif; ?>

	<?php if ($member_group == 1): ?>
		<fieldset>
			<h3><?=lang('settings')?></h3>
			<ul>
				<li><a href="<?=$base_url?>&amp;method=settings"><?=lang('edit_settings')?></a></li>
				<li>
					<span>Open Search URL:</span>
					<code onclick="prompt('<?=lang('build_index_url')?>', $(this).text());"><?=$search_url?></code>
				</li>
				<?php if ($settings['license_key']): ?>
					<li><span><?=lang('build_index_url')?>:</span> <code onclick="prompt('<?=lang('build_index_url')?>', $(this).text());"><?=$build_url?></code></li>
				<?php endif; ?>
			</ul>
		</fieldset>
	<?php endif; ?>

</div>