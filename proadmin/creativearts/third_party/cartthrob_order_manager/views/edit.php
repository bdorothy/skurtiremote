<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$this->EE =& get_instance(); 
$this->EE->load->library("number"); 
 ?>
	
	<!-- <input  dir='ltr' type='text' name='level'  value='<?=$view['status']?>' size='30' maxlength='100' /> --> 
		<br><!-- @TODO remove this br & the inline styles of teh submit button, and fix with CSS -->

			<h3>
			<?php if ($refunded) echo "<del>"; ?>
			<?=lang('cartthrob_order_manager_order_info')?>: <?=$view['title']?> (<a href='<?=$entry_href. $view['entry_id']?>'><?=$view['entry_id']?> &raquo;</a>)
			<?php if ($refunded) echo "</del> ".lang('refunded'); ?>
			 </h3>
		
	  	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
			<thead class="">
				<tr>
					<th>
 	 				</th>
				</tr>
			</thead>
			<tbody>
				<tr class="<?php echo alternator('even', 'odd');?>">
					<td>
						<strong>Transaction ID</strong> <?=$view['order_transaction_id']?> 
 					</td>
				</tr>
				<tr class="<?php echo alternator('even', 'odd');?>">
					<td>
						<strong>Payment Method</strong> <?=ucwords(str_replace("_", " ", $view['order_payment_gateway']))?>
					</td>
				</tr>
				<tr class="<?php echo alternator('even', 'odd');?>">
					<td>
						<strong>Total</strong> <?=$view['orders_total']?>
					</td>
				</tr>
				<tr class="<?php echo alternator('even', 'odd');?>">
					<td>
						<strong>Subtotal</strong> <?=$view['orders_total']?>
					</td>
				</tr>
				<tr class="<?php echo alternator('even', 'odd');?>">
					<td>
						<strong>Tax</strong> <?=$view['orders_tax']?>
					</td>
				</tr>
				<tr class="<?php echo alternator('even', 'odd');?>">
					<td>
						<strong>Shipping</strong> <?=$view['orders_shipping']?>
					</td>
				</tr>
				<tr class="<?php echo alternator('even', 'odd');?>">
					<td>
								<?php echo $form_delete; ?>
								<?php 
									
									if ($href_invoice)
									{
										echo '	<a class="submit" href="'.$href_invoice.'" target="_blank"  >'.lang("cartthrob_order_manager_print_invoice").'</a> ';
									}
									
									if ($href_packing_slip)
									{
										echo '	<a class="submit" href="'.$href_packing_slip.'" target="_blank"  >'.lang("cartthrob_order_manager_print_packing_slip").'</a> ';
									}
								
									if ($custom_templates)
									{
										foreach ($custom_templates as $key => $template_data)
										{
 	 										echo '	<a class="submit" href="'.$template_data['link'].'" target="_blank"  >'.lang("cartthrob_order_manager_print")." ".$template_data['name'].'</a> ';
										}
									}
									
								
								?>

				 				<input type="submit"  name="submit" value="<?=lang('cartthrob_order_manager_delete_this_order')?>" class="submit" />

							<input type="hidden" value="<?=$view['entry_id']?>" name="id" /> 
							<input type="hidden" value="view" name="return" /> 
						</form>
						
					
					</td>
				</tr>
				<tr>
							
					<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
						<thead class="">
							<tr>
								<th>
									<?=lang('cartthrob_order_manager_item_title')?>
			 	 				</th>
								<th>
									<?=lang('cartthrob_order_manager_item_price')?>
			 	 				</th>
								<th>
									<?=lang('cartthrob_order_manager_item_subtotal')?>
			 	 				</th>
								<th>
									<?=lang('cartthrob_order_manager_item_quantity')?>
			 	 				</th>
								<th>
									<?=lang('cartthrob_order_manager_item_shipping')?>
			 	 				</th>
								<th>
									<?=lang('cartthrob_order_manager_item_weight')?>
			 	 				</th>
								<th>
									<?=lang('cartthrob_order_manager_item_entry_id')?>
			 	 				</th>
								<th>
									<?=lang('cartthrob_order_manager_item_taxable')?>
			 	 				</th>
								<th>
									<?=lang('cartthrob_order_manager_item_shippable')?>
			 	 				</th>
								<th>
									<?=lang('actions')?>
				 	 				</th>
							</tr>
						</thead>
						<tbody>
								
						<?php foreach ($view['order_items'] as $item): ?>
								<tr class="<?php echo alternator('even', 'odd');?>">
									<td>
										<a href="<?php echo $href_entry. $item['entry_id'];  ?>"><?=$item['title']?> (<?=$item['entry_id']?>) &raquo; </a>
									</td>
									<td>
										<?=$this->EE->number->format($item['price'])?> (<?=$this->EE->number->format($item['price_plus_tax'])?>)
									</td>
									<td>
										<?=$this->EE->number->format($item['price'] * $item['quantity'])?> (<?=$this->EE->number->format($item['price_plus_tax']* $item['quantity'])?>)
									</td>
									<td>
										<?=$item['quantity']?>
									</td>
									<td>
										<?=$this->EE->number->format($item['shipping'])?>
									</td>
									<td>
										<?=$item['weight']?>
									</td>
									<td>
										<?=$item['entry_id']?>
									</td>
									<td>
										<?php 
										if ($item['no_shipping'])
										{
											echo "no"; 
										}
										else
										{
											echo "yes"; 
										}
										?>
 									</td>
									<td>
										<?php 
										if ($item['no_tax'])
										{
											echo "no"; 
										}
										else
										{
											echo "yes"; 
										}
										?>
 									</td>
									<td>
 										<a href="<?php echo $href_entry. $item['entry_id'];  ?>"><?=lang('cartthrob_order_manager_view_product')?>&nbsp;&raquo;</a><br>
									</td>
								</tr>

								<?php
								$skip_keys = array(
									'shipping',
									'weight',
									'price_plus_tax',
									'price',
									'quantity',
									'title',
									'entry_id',
									'order_id',
									'row_order',
									'row_id',
									'no_tax',
									'no_shipping',
								); 
								$count = 0; 
								foreach ($item as $key=> $value):
									if (!in_array($key, $skip_keys)): 	
									$count ++; 
										if ($count ==1)
										{
											echo '<tr class="'.alternator('even', 'odd').'">
												<td></td>
												<td colspan="9">'; 
										}
										if ($key == "sub_items")
										{
											echo '<strong>Packaged Items</strong><br>'; // @TODO language
											foreach ($value as $k => $v)
											{
												echo $v['title'] . " x " .$v['quantity']; 
												echo ' <a href="'.$href_entry. $item['entry_id'].'">'.lang('cartthrob_order_manager_view_product').'&nbsp;&raquo;</a><br>';
												foreach ($v as $kk => $vv)
												{
													if (!in_array($kk, $skip_keys))
													{
														if ($vv)
														{
															echo "&nbsp;&nbsp;<strong>". ucwords(str_replace("_", " ", $kk))."</strong>&nbsp; - &nbsp;". $vv."<br>"; 
														}
													}
												} 
											}
											echo "<br>"; 
										}
										else
										{
											if ($value)
											{
												echo '<strong>'.ucwords(str_replace("_", " ", $key)).'</strong>&nbsp; - &nbsp;';
												echo $value;
												echo "<br>"; 
												
											}
										}
									endif; 
								endforeach; 
								if ($count)
								{
									echo "</td></tr>";
								}
								?>
								
							<?php endforeach; ?>
						</tbody>
					</table>
				</tr>
			</tbody>
		</table>
<?php echo $form_edit; ?>
		
		<h3><?=lang('cartthrob_order_manager_address_info')?></h3>
	  	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
			<thead class="">
				<tr>
					<th>
						<strong><?=lang('billing_address')?></strong> 
	 				</th>
					<th>
						<strong><?=lang('shipping_address')?></strong> 
	 				</th>
				</tr>
			</thead>
			<tbody>
				<tr class="<?php echo alternator('even', 'odd');?>">
					<td>
 						<?=$view['orders_billing_first_name']?> <?=$view['orders_billing_last_name']?><br />
						<?=$view['orders_billing_company']?> <br />
						<?=$view['orders_billing_address']?> <?=$view['orders_billing_address2']?> <br />
						<?=$view['orders_billing_city']?>, <?=$view['orders_billing_state']?> <br />
						<?=$view['orders_billing_zip']?><br><br>
						<?=$view['orders_country_code']?><br><br>
						
						<?php 
							if (!empty($view['author_id']))
							{ 
								echo "<a href='".$href_member.$view['author_id']."'>(".$view['author_id'].") ". lang('cartthrob_order_manager_member_details')." &raquo;</a><br> "; 
							}
						?>
 						<strong><?=lang('cartthrob_order_manager_payment_method')?></strong> 
						<?php 
						if (isset($view['order_payment_gateway']))
						{
							echo ucwords(str_replace("_", " ",$view['order_payment_gateway'] ));
						}
						 ?>
						<br><br>
					</td>
					<td>
 						<?=$view['orders_shipping_first_name']?> <?=$view['orders_shipping_last_name']?> <br />
						<?=$view['orders_shipping_company']?> <br />
						<?=$view['orders_shipping_address']?> <?=$view['orders_shipping_address2']?> <br />
						<?=$view['orders_shipping_city']?>, <?=$view['orders_shipping_state']?> <br />
						<?=$view['orders_shipping_zip']?><br><br>
						<?=$view['orders_shipping_country_code']?>
					</td>
				</tr>
			</tbody>
		</table>
	 
	<input type="hidden" value="<?=$view['entry_id']?>" name="id" /> 
	
	<!-- <p><input type="submit" name="submit" value="<?=lang('submit')?>" class="submit" /></p> -->
	
</form>
<?php 
	if ($form_capture): 
?>
<h3><?=lang('cartthrob_order_manager_capture')?></h3>
 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
	<thead class="">
		<tr>
			<th>
 				</th>
			<th>
 				</th>
		</tr>
	</thead>
	<tbody>
		<tr class="<?php echo alternator('even', 'odd');?>">
		<td><p><?=lang('cartthrob_order_manager_capture_description')?></p></td>
			<td valign="top" >
				<?php echo $form_capture; ?>
					<p>
				<label for="total">
				Total
				<input id="total" type="text" value="<?=$view['orders_total']?>" name="total" /> 
				</label>
				</p>
				<input type="hidden" value="<?=$view['entry_id']?>" name="id" /> 
				
				<p><input type="submit" name="submit" value="<?=lang('cartthrob_order_manager_capture_order')?>" class="submit" /></p>
				</form>
			</td>
			
		</tr>
	</tbody>
</table>
 <?php endif; ?>
<?php 
	if ($form_void): 
?>
<h3><?=lang('cartthrob_order_manager_void')?></h3>
 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
	<thead class="">
		<tr>
			<th>
 				</th>
			<th>
 				</th>
		</tr>
	</thead>
	<tbody>
		<tr class="<?php echo alternator('even', 'odd');?>">
		<td><p><?=lang('cartthrob_order_manager_void_description')?></p></td>
			<td valign="top" >
				<?php echo $form_void; ?>
				
				<input type="hidden" value="<?=$view['entry_id']?>" name="id" /> 
	
				<p><input type="submit" name="submit" value="<?=lang('cartthrob_order_manager_void_order')?>" class="submit" /></p>
</form>
			</td>
		</tr>
	</tbody>
</table>
 <?php endif; ?>
<?php echo $resend_email; ?>
	<h3><?=lang('cartthrob_order_manager_resend_email')?></h3>
	
	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<thead class="">
			<tr>
				<th>
					Email Address
	 			</th>
				<th>
					Subject
	 			</th>
			</tr>
		</thead>
		<tbody>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<p>
						<label for="resend_email_address">
						Email Address
						<input id="resend_email_address" type="text" value="<?=$view['orders_customer_email']?>" name="email_address" /> 
 						
						</label>
					</p>
				</td>
				<td>
					<p>
						<label for="subject">
						Subject
						<input id="subject" type="text" value="Order Complete" name="email_subject" /> 
 
						</label>
					</p>
				</td>
			</tr>
		</tbody>
	</table>
	
	
	<input type="hidden" value="<?=$view['entry_id']?>" name="id" /> 
	<input type="hidden" value="view" name="return" /> 
	<p><input type="submit" name="submit" value="<?=lang('submit')?>" class="submit" /></p>
</form>

<?php echo $add_tracking; ?>

	<h3><?=lang('cartthrob_order_manager_tracking_info')?></h3>
  	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<thead class="">
			<tr>
				<th>
					Tracking Number
	 			</th>
				<th>
					Note
	 			</th>
				<th>
					Status
	 			</th>
			</tr>
		</thead>
		<tbody>
			<tr class="<?php echo alternator('even', 'odd');?>">
				<td>
					<p>
						<label for="tracking_number">
						Tracking Number
						<?php if ($view['order_tracking_number'] === FALSE): ?>
							<?=lang('cartthrob_order_manager_requires_tracking_field')?>
						<?php else: ?>
							<input id="tracking_number" type="text" value="<?=$view['order_tracking_number']?>" name="order_tracking_number" /> 
						<?php endif; ?>
						
						</label>
					</p>
				</td>
				<td>
					<p>
						<label for="note">
						Shipping Note
						<?php if ($view['order_shipping_note'] === FALSE): ?>
							<?=lang('cartthrob_order_manager_requires_note_field')?>
						<?php else: ?>
						<input id="tracking_note" type="text" value="<?=$view['order_shipping_note']?>" name="order_shipping_note" /> 
						<?php endif; ?>

						</label>
					</p>
				</td>
				<td>
					<p>
						<label for="order_status">
						Status
						
						<select name='status' id="status" class='statuses' >
							<option value='<?= $view['status'] ?>' ><?= $view['status'] ?></option>
						</select>
						</label>
					</p>
				</td>
			</tr>
		</tbody>
	</table>
	<input type="hidden" value="<?=$view['entry_id']?>" name="id" /> 
	
	<p><input type="submit" name="submit" value="<?=lang('submit')?>" class="submit" /></p>

</form>

<?php 
	if ($form_refund): 
?>
<h3><?=lang('cartthrob_order_manager_refunds')?></h3>
 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
	<thead class="">
		<tr>
			<th>
 				</th>
			<th>
 				</th>
		</tr>
	</thead>
	<tbody>
		<tr class="<?php echo alternator('even', 'odd');?>">
			<td valign="top" >
				<?php echo $form_refund; ?>
					<p>
				<label for="total">
				Total
				<input id="total" type="text" value="<?=$view['orders_total']?>" readonly="readonly" class="disabled" name="total" /> 
				</label>
				</p>
				<input type="hidden" value="<?=$view['orders_shipping']?>" name="shipping" /> 
				<input type="hidden" value="<?=$view['orders_tax']?>" name="tax" /> 
				<input type="hidden" value="<?=$view['orders_subtotal']?>" name="subtotal" /> 
				<input type="hidden" value="<?=$view['entry_id']?>" name="id" /> 
				
				<p><input type="submit" name="submit" value="<?=lang('cartthrob_order_manager_full_refund')?>" class="submit" /></p>
				</form>
			</td>
			<td valign="top" >
				<?php echo $form_refund; ?>

				<p>
				<label for="subtotal">
				Subtotal
				<input id="subtotal" type="text" value="<?=$view['orders_subtotal']?>" name="subtotal" /> 
				</label>
				</p>

				<p>
				<label for="tax">Tax
				<input id="tax" type="text" value="<?=$view['orders_tax']?>" name="tax" /> 
				</label>
				</p>

				<p>
				<label for="shipping">
				Shipping
				<input id="shipping" type="text" value="<?=$view['orders_shipping']?>" name="shipping" /> 
				</label>
				</p>
				<input type="hidden" value="<?=$view['entry_id']?>" name="id" /> 

				<p><input type="submit" name="submit" value="<?=lang('cartthrob_order_manager_partial_refund')?>" class="submit" /></p>
				</form>
					<p>Inventory is not adjusted for partial refunds.</p>
				
			</td>
		</tr>
	</tbody>
</table>
 <?php endif; ?>

<?php 	if ($custom_templates): ?>
<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
	<tbody>
		<thead class="">
			<tr>
				<th>
 					Email Custom Template
	 			</th>
			</tr>
		</thead>
<?php  	foreach ($custom_templates as $key => $template_data): ?>

			<tr class="<?php echo alternator('even', 'odd');?>">
				<?=$template_data['form']?>
				<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
				<tr>
				<td width="20%">
					<p>
							<?=$template_data['name'];?>
					</p>
				</td>
				<td  width="35%">
					<p>
						<label>
						Email Address
						<input type="text" value="<?=$view['orders_customer_email']?>" name="email_address" /> 

						</label>
					</p>
				</td>
				<td  width="35%">
					<p>
						<label>
						Subject
						<input  type="text" value="<?=$template_data['name']?>" name="email_subject" /> 

						</label>
					</p>
				</td>
				<td  width="10%">
					<p><input type="submit" name="submit" value="<?=lang('submit')?>" class="submit" /></p>

				</td>
			</tr>
			<input type="hidden" value="<?=$view['entry_id']?>" name="id" /> 
			<input type="hidden" value="view" name="return" /> 
			</form>
			</table>
 		</tr>

			
<?php endforeach; ?>
</tbody>
</table>

<?php endif;?>