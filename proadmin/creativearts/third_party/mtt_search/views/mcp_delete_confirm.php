<form method="post" action="<?=$form_action?>">
	<div>
		<input type="hidden" name="<?=$csrf_token_name?>" value="<?=$csrf_token_value?>" />
		<?php foreach ($hidden_fields AS $name => $val): ?>
			<input type="hidden" name="<?=$name?>" value="<?=$val?>" />
		<?php endforeach; ?>
	</div>
	<p><?=$are_you_sure?></p>
	<p>
		<input type="submit" class="submit" value="<?=$confirm?>" />
		<a style="margin-left:20px" class="cancel" href="<?=$cancel_url?>"><?=lang('cancel_go_back')?></a>
	</p>
</form>