<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title><?=$cp_page_title?> | <?=APP_NAME?></title>
<?=$this->view->head_link('css/login.css'); ?>
</head>
<body id="login" onload="<?=$cp_page_onload?>">
<div id="content">

<div id="white">
		<div class="success">
			<p><?=$message_success?></p>
		</div>

	<p><a href="<?=BASE.AMP.'C=login'?>"><?=lang('return_to_login')?></a></p>
</div>

</div>
</body>
</html>
