<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
	<tbody>
	<?php foreach ($settings as $i => $setting) : ?>
		<tr class="<?=($setting['plugin_type'] == 'global') ? 'odd' : 'even'?> <?=$setting['plugin_type']?>">
			<td<?php if ($i == 0) : ?> style="border-top:1px solid #D0D7DF;"<?php endif; ?>><strong><?=lang($setting['name'])?></strong><?php if ( ! empty($setting['note'])) : ?><br /><span><?=lang($setting['note'])?></span><?php endif; ?></td>
			<td<?php if ($i == 0) : ?> style="border-top:1px solid #D0D7DF;"<?php endif; ?>><?=$setting['display_field']?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>