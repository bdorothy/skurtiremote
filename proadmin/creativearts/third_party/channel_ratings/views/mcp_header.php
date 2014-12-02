<ul class="tab_menu" id="tab_menu_tabs">
	<li class="content_tab <?=(($PageHeader == 'home')) ? ' current': ''?>"><a href="<?=$base_url?>"><?=lang('rating:home')?></a></li>
	<li class="content_tab <?=(($PageHeader == 'fields')) ? ' current': ''?>"><a href="<?=$base_url?>&method=fields"><?=lang('rating:fields')?></a></li>
	<li class="content_tab <?=(($PageHeader == 'import')) ? ' current': ''?>"><a href="<?=$base_url?>&method=import"><?=lang('rating:import')?></a></li>
	<li class="content_tab <?=(($PageHeader == 'recount')) ? ' current': ''?>"><a href="<?=$base_url?>&method=recount"><?=lang('rating:recount')?></a></li>
</ul>
<br />

<?php if ($PageHeader == 'home'):?>

<?php elseif ($PageHeader == 'fields'):?>

<span class="cp_button"><a href="<?=$base_url?>&method=add_field"><?=lang('rating:add_field')?></a></span>
<br clear="left">

<?php endif;?>