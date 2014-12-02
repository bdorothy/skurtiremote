<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob_order_manager'.AMP.'method=om_sales_dashboard', 'id="reports_filter"')?>

<?=$reports_filter?>
 	Report <?=form_dropdown('report', $reports, $current_report)?>
	<?=form_submit('', lang('refresh'), 'class="submit"')?>
</form>

<?php if ($current_report) : ?>
<div id="reports_view">
	<?=$view?>
</div>
<?php else : ?>
<?=$view?>
<?php endif; ?>

<?=$todays_orders?>

<div>
<?=$reports_date?>
 	<p>
	<?=lang('cartthrob_order_manager_date_start')?> 
	<input type="text" value="" class="datepicker" name="date_start" size="30" style="width:100px"/> 
	<?=lang('cartthrob_order_manager_date_finish')?> 
	<input type="text" value="" class="datepicker" name="date_finish" size="30" style="width:100px"/> 
	<?=form_submit('', lang('cartthrob_order_manager_date_range'), 'class="submit"')?>
	</p>
</form>
</div>
<?=$order_totals?>

<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob_order_manager'.AMP.'method=om_sales_dashboard'.AMP.'save=1')?>
	<?=$reports_list?>
	<?=form_submit('', lang('submit'), 'class="submit"')?>
<?=form_close()?>

<script type="text/javascript">
$("select[name=report]").live("change", function(){
	$(this).parents("form").submit();
});
$("#reports").show();
</script>