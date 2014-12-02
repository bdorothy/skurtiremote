<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title><?=$cp_page_title?> | <?=APP_NAME?></title>
<?=$this->view->head_link('css/login.css'); ?>
</head>
<body id="login" onload="<?=$cp_page_onload?>">

	<div id="content">

		<div class="error">
			<?php foreach ($message as $message):?>
				<p><?=$message?></p>
			<?php endforeach;?>
			<ul>
				<?php foreach ($notices as $notice):?>
					<li><?=$notice?></li>
				<?php endforeach;?>
			</ul>
		</div>

		<?=form_open('C=login'.AMP.'M=update_un_pw', array(), $hidden)?>

		<dl>
			<?php if ($new_username_required):?>
			<dt><?=lang('existing_username')?>: <?=$username?><br />
				<?=lang('choose_new_un', 'new_username')?>:</dt>
			<dd><?=form_input('new_username', $new_username)?></dd>
			<?php endif;?>

			<?php if ($new_username_required AND ! $new_password_required): ?>
			<dt><?=lang('existing_password')?>:</dt>
			<dd><?=form_password('password')?></dd>
			<?php endif;?>

			<?php if ($new_password_required):?>
			<dt><?=lang('existing_password')?>:</dt>
			<dd><?=form_password('password')?></dd>
			<dt><?=lang('choose_new_pw', 'new_password')?>:</dt>
			<dd><?=form_password('new_password', $new_password)?></dd>
			<dt><?=lang('confirm_new_pw', 'confirm_new_pw')?></dt>
			<dd><?=form_password('new_password_confirm')?></dd>
			<?php endif;?>
		</dl>
		<p><?=form_submit('submit', 'Submit', 'class="submit"')?></p>
		<?=form_close()?>
	</div>

</body>
</html>