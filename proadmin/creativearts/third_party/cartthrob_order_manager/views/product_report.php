<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>


<?=$data['products']?>


<?=$run_report?>

<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
	<thead>
		<tr>
			<th>
				<?=lang('cartthrob_order_manager_date_range')?>
			</th>
		</tr>
	</thead>
 	<tbody>
		<tr class="<?php echo alternator('even', 'odd');?>">
			<td>
				<?=lang('cartthrob_order_manager_date_start')?> 
				<input type="text" value="<?=$data['date_start']?>" class="datepicker" name="where[date_start]" size="30"/> 
			</td>
		</tr>				
		<tr class="<?php echo alternator('even', 'odd');?>">
			<td>
				<?=lang('cartthrob_order_manager_date_finish')?> 
				<input type="text" value="<?=$data['date_finish']?>" class="datepicker" name="where[date_finish]" size="30"/> 
			</td>
		</tr>	
	</tbody>
</table>
<input type="submit" name="submit" value="<?=lang('cartthrob_order_manager_run_report')?>" class="submit" /> 
</form>

<?=$export_csv?>
<?=$data['hidden_inputs']?>

<p>
 	<input type="text" name="filename" value="Products" style="width:135px"/> 
	<input type="submit" name="download" value="<?=lang('cartthrob_order_manager_export_csv')?>" class="submit" /> 
</p>

 

</form>