<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method=reports', 'id="reports_filter"')?>
	Report <?=form_dropdown('report', $reports, $current_report)?>
	<?=form_submit('', lang('refresh'), 'class="submit"')?>
<?=form_close()?>
<?php if ($current_report) : ?>
<div id="reports_view">
	<?=$view?>
</div>
<?php else : ?>
<?=$view?>
<?php endif; ?>

<?=$order_totals?>

<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method=reports'.AMP.'save=1')?>
	<?=$reports_list?>
	<?=form_submit('', lang('submit'), 'class="submit"')?>
<?=form_close()?>

<script type="text/javascript">
$("select[name=report]").live("change", function(){
	$(this).parents("form").submit();
});
$("#reports").show();
</script>