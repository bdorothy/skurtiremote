<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title><?=$cp_page_title?> | <?=APP_NAME?></title>
<?=$this->view->head_link('css/login.css'); ?>
</head>
<body id="login" onload="<?=$cp_page_onload?>">


	<div id="content">

<?php if ($message != ''):?>
<div class='highlight'><?=$message?></div>
<?php endif;?>

<?=form_open('C=login'.AMP.'M=reset_password')?>

<?=form_hidden('resetcode', $resetcode)?>

<dl>
	<dt><?=lang('new_password')?>:</dt>
	<dd>
		<?=form_password(array('style' => 'width:100%', 'size' => '35', 'dir' => 'ltr', 'name' => "password", 'id' => "password", 'maxlength' => 80, 'autocomplete' => 'off'))?>
		<?=form_error('password')?>
	</dd>
	<dt><?=lang('new_password_confirm')?>:</dt>
	<dd>
		<?=form_password(array('style' => 'width:100%', 'size' => '35', 'dir' => 'ltr', 'name' => "password_confirm", 'id' => "password_confirm", 'maxlength' => 80, 'autocomplete' => 'off'))?>
		<?=form_error('password_confirm')?>
	</dd>
</dl>

<p><?=form_submit('submit', lang('submit'), 'class="submit"')?> <span><a href="<?=BASE.AMP.'C=login'?>"><?=lang('return_to_login')?></a></span></p>

</form>

</div>
</body>
</html>
