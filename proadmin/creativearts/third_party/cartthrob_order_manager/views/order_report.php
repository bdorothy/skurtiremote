<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
 
<?php
if ($data['reports']): 
echo $reports_filter; 
?>
	<p>
 	Report <?=form_dropdown('report', $data['reports'])?>
	<?=form_submit('', lang('cartthrob_order_manager_saved_reports'), 'class="submit"')?>
        <script>
        var bni = {};
        bni.BaseURL = '<?php echo BASE;?>';
        bni.redirectURL_S = '';
        bni.redirectURL_ = '';
        bni.redirectURL = '';
        bni.redirectURL_Base = '';
        
        bni.removeReport = function(e){
            if(bni.BaseURL.indexOf('S=') >= 0){
                bni.redirectURL_S = bni.BaseURL.substring(bni.BaseURL.indexOf('.php?')+5,bni.BaseURL.indexOf('&amp;D=cp'));
                bni.redirectURL_ = '/cp/addons_modules/show_module_cp?module=cartthrob_order_manager&method=remove_report&reportID=' + $(e).siblings("select[name='report']").val() + '&';
                bni.redirectURL_Base = bni.BaseURL.substring(0,bni.BaseURL.indexOf('.php?') + 5);
                bni.redirectURL = bni.redirectURL_Base + bni.redirectURL_ + bni.redirectURL_S;
                document.location.href = bni.redirectURL;
            }
        };
        </script>
        <button type="button" onclick="bni.removeReport(this);">Remove?</button>
	</p>
</form>
<?php endif; ?>
<?=$run_report ?>
 	
<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
	<thead>
		<tr>
			<th>
				<?=lang('cartthrob_order_manager_status')?>
			</th>
		</tr>
	</thead>
 	<tbody>
		<tr class="<?php echo alternator('even', 'odd');?>">
			<td>
				<select name="where[status]" class="statuses_blank">
				</select>
			</td>
		</tr>
	</tbody>
</table>
  
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
<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
	<thead>
		<tr>
			<th>
				<?=lang('cartthrob_order_manager_price_range')?>
			</th>
		</tr>
	</thead>
	<tbody>
 		
		<tr class="<?php echo alternator('even', 'odd');?>">
			<td>
				<?=lang('cartthrob_order_manager_total_minimum')?> 
				<input type="text" value="<?=$data['date_start']?>"  name="where[total_minimum]" size="30"/> 
			</td>
		</tr>
		<tr class="<?php echo alternator('even', 'odd');?>">
			<td>
				<?=lang('cartthrob_order_manager_total_max')?> 
				<input type="text" value="<?=$data['date_finish']?>"  name="where[total_maximum]" size="30"/> 
			</td>
		</tr>
	 </tbody>
<table>	

 		<!--<?=$data['member_inputs']?>-->
 		<?=$data['search_fields']?>

 		<?=$data['order_totals']?>
		<?=$data['order_fields']?>
	<p>
		<input type="submit" name="submit" value="<?=lang('cartthrob_order_manager_run_report')?>" class="submit" /> 

		<input type="submit" name="download" value="<?=lang('cartthrob_order_manager_export_csv')?>" class="submit" /> 

	</p>
	
	<p>
		<?=lang('cartthrob_order_manager_report_title')?><input type="text" name="report_title" value="" />
	</p>
	<p>
		<input type="submit" name="save_report" value="<?=lang('cartthrob_order_manager_save_report')?>" class="submit" /> 
	</p>
	
 </form>
