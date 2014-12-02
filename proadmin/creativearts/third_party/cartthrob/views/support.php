<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
$this->EE =& get_instance(); 
?>

<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
	<caption><?=lang('support_overview_header')?></caption>
	<thead class="">
		<tr>
			<th colspan="2">
				</th>
		</tr>
	</thead>
	<tbody>	
		<tr class="<?=alternator('even', 'odd')?>">
			<td colspan="2">
				<a href="<?php echo $this->EE->cp->masked_url('http://livecart.com/docs');?>" target="_blank">
					<?=lang('support_documentation')?>
				</a>
			</td>
		</tr>
		<tr class="<?=alternator('even', 'odd')?>">
			<td colspan="2">
				<a href="<?php echo $this->EE->cp->masked_url('http://livecart.com/docs/shipping/index.html');?>" target="_blank">
					<?=lang('support_shipping_overview')?>
				</a>
			</td>
		</tr>
		<tr class="<?=alternator('even', 'odd')?>">
			<td colspan="2">
				<a href="<?php echo $this->EE->cp->masked_url('http://livecart.com/docs/taxes/index.html');?>" target="_blank">
					<?=lang('support_taxes_overview')?>
				</a>
			</td>
		</tr>
		<tr class="<?=alternator('even', 'odd')?>">
			<td colspan="2">
				<a href="<?php echo $this->EE->cp->masked_url('http://livecart.com/docs/payments/index.html');?>" target="_blank">
					<?=lang('support_checkout')?>
				</a>
			</td>
		</tr>
		<tr class="<?=alternator('even', 'odd')?>">
			<td colspan="2">
				<a href="<?php echo $this->EE->cp->masked_url('http://livecart.com/docs/discounts/index.html');?>" target="_blank">
					<?=lang('support_discounts_overview')?>
				</a>
			</td>
		</tr>
		
		</tbody>
</table>


<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
	<caption><?=lang('support_header')?></caption>
	<thead class="">
		<tr>
			<th colspan="2">
 				</th>
		</tr>
	</thead>
	<tbody>	
		<tr class="<?=alternator('even', 'odd')?>">
			<td colspan="2">
				<a href="<?php echo $this->EE->cp->masked_url('http://livecart.com/docs/pages/troubleshooting/index.html');?>" target="_blank">
					<?=lang('support_troubleshooting')?>
				</a>
			</td>
		</tr>
		<tr class="<?=alternator('even', 'odd')?>">
			<td colspan="2">
				<a href="<?php echo $this->EE->cp->masked_url('http://mightyliverobot.com/contact');?>" target="_blank">
					<?=lang('contact_us')?>
				</a>
			</td>
		</tr>
		</tbody>
</table>
 
<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
	<caption><?=lang('support_system_test')?></caption>
	<thead class="">
		<tr>
			<th colspan="2">
				<?=lang('support_system_test_overview')?>
			</th>
		</tr>
	</thead>
	<tbody>	
		<tr class="<?=alternator('even', 'odd')?>">
			<td colspan="2">
				<?php if ( ! function_exists('curl_exec'))
				{
					echo "<span class='color:red'>".lang('support_curl_failed')."</span>"; 
 				}
				else
				{
					echo "<span class='color:green'>".lang('support_curl_success')."</span>"; 
				}
				?>
 
			</td>
		</tr>
		<tr class="<?=alternator('even', 'odd')?>">
			<td colspan="2">
				<?php if (!is_callable('ini_set'))
					{
						echo "<span class='color:red'>".lang('support_ini_set_failed')."</span>"; 
	 				}
					else
					{
						echo "<span class='color:green'>".lang('support_ini_set_success')."</span>"; 
					}
				?>
 
			</td>
		</tr>
		<tr class="<?=alternator('even', 'odd')?>">
			<td colspan="2">
				<?php if (!is_callable('apache_setenv'))
					{
						echo "<span class='color:red'>".lang('support_apache_setenv_failed')."</span>"; 
	 				}
					else
					{
						echo "<span class='color:green'>".lang('support_apache_setenv_success')."</span>"; 
					}	
				?>
 
			</td>
		</tr>
 		</tbody>
</table>