<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
 
<h3><?=lang("cartthrob_order_manager_total_customers")?>: <?= $data['customer_count']?></h3>

<div>
<?=$data['html']?>
</div>
<?=$export_csv?>
<p>
	<input type="hidden" value="true" name="download" /> 
 	<input type="submit" name="download" value="<?=lang('cartthrob_order_manager_export_csv')?>" class="submit" />  <input type="text" name="filename" value="Customers" style="width:235px"/> 	
	
</p>
</form>

