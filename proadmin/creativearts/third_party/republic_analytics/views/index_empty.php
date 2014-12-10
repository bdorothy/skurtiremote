<div id="result">
  <div id="spinner" style="width: 100%; height: 300px"></div>
	<?php if ($is_data_cached) : ?>
		<p class="status_msg"><?php echo lang('republic_analytics_loading_data_cached');?></p>
	<?php else : ?>
		<p class="status_msg"><?php echo lang('republic_analytics_loading_data_not_cached');?></p>
	<?php endif; ?>
</div>

<div id="profile_id" style="display:none"><?php echo $profile_id; ?></div>
