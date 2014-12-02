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

<?=form_open('C=login'.AMP.'M=send_reset_token')?>

<dl>
	<dt><?=lang('submit_email_address')?>:</dt>
	<dd><?=form_input(array('style' => 'width:100%', 'size' => '35', 'dir' => 'ltr', 'name' => "email", 'id' => "email", 'maxlength' => 80, 'autocomplete' => 'off'))?></dd>
</dl>

<p><?=form_submit('submit', lang('submit'), 'class="submit"')?> <span><a href="<?=BASE.AMP.'C=login'?>"><?=lang('return_to_login')?></a></span></p>

</form>

</div>
</body>
</html>
