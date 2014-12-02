<?php echo $form_action;?>
  <table class="mainTable padTable">
                          <thead>
                            <tr>
                                <th width="168">Settings</th>
                                <th width="334"><i class="icon24 i-gear"></i> Preferences</th>
                            </tr>
        </thead>
                              <tbody>
          <tr>
            <td>Product Channel</td>
            <td><?=form_dropdown('products_channel', $all_channels,$products_channel)?></td>
          </tr>
          <tr>
             <td width="168"><i class="icon icon24 i-user"></i>Order Channel</td>
            <td width="334"><?=form_dropdown('orders_channel', $all_channels,$orders_channel)?></td>
            </tr>
                              <tr>
                                <td>Skin Url</td>
                                <td><?=form_input(array('id'=>'skin_url','name'=>'skin_url','class'=>'skin_url','value'=> $skin_url ))?></td>
                              </tr>
							  
							   <tr>
                                <td>Allow Wholesale</td>
                                <td>
								<input type="radio" name="allow_wholesale" value="yes" <?=($allow_wholesale == 'yes' ? 'checked' : '');?>> Yes
								<input type="radio"  name="allow_wholesale" value="no" <?=($allow_wholesale == 'no' ? 'checked' : '');?>> No</td>
                              </tr>
							  
							  
                              <tr>
                                <td>&nbsp;</td>
                                <td><?php echo form_submit('mysubmit', 'Submit Settings');?></td>
                              </tr>
                              </tbody>
      </table><?php echo form_close();?>
      