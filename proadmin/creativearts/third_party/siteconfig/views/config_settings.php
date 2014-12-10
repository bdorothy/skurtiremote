<?php echo $form_action;?>
  <table class="mainTable padTable">
                          <thead>
                            <tr>
                                <th width="276">Settings</th>
                                <th width="483"><i class="icon24 i-gear"></i> Preferences</th>
                            </tr>
        </thead>
                              <tbody>
                              <tr>
                                <td>Help Line <br>
                                {helpline}</td>
                                <td><?=form_input(array('id'=>'helpline','name'=>'helpline','class'=>'field','value'=> $helpline ))?></td>
                              </tr>
                              <tr>
                                 <td width="276"><i class="icon icon24 i-user"></i>Facebook URL<br>
{facebook_url}</td>
                                <td width="483"><?=form_input(array('id'=>'facebook_url','name'=>'facebook_url','class'=>'field','value'=> $facebook_url ))?></td>
                                </tr>
                                <tr>
                                  <td>Twitter URL <br>
                                  {twitter_url}</td>
                                  <td><?=form_input(array('id'=>'twitter_url','name'=>'twitter_url','class'=>'field','value'=> (set_value('twitter_url')?set_value('twitter_url'):$twitter_url) ))?></td>
                                </tr>
                                
                                <tr>
                                 <td width="276"><i class="icon icon24 i-user"></i>LinkedIn URL<br>
                                   {linkedin_url}</td>
                                <td width="483"><?=form_input(array('id'=>'linkedin_url','name'=>'linkedin_url','class'=>'field','value'=> $linkedin_url ))?></td>
                                </tr>
                                
                                <tr>
                                 <td width="276"><i class="icon icon24 i-user"></i>Pinterest URL<br>
                                   {pinterest_url}</td>
                                <td width="483"><?=form_input(array('id'=>'pinterest_url','name'=>'pinterest_url','class'=>'field','value'=> $pinterest_url ))?></td>
                                </tr>
                                
                                
                                <tr>
                                 <td width="276"><i class="icon icon24 i-user"></i>Youtube URL<br>
                                   {youtube_url}</td>
                                <td width="483"><?=form_input(array('id'=>'youtube_url','name'=>'youtube_url','class'=>'field','value'=> $youtube_url ))?></td>
                                </tr>
                                
                                
                                <tr>
                                 <td width="276"><i class="icon icon24 i-user"></i>Picasa URL<br>
                                   {picasa_url}</td>
                                <td width="483"><?=form_input(array('id'=>'picasa_url','name'=>'picasa_url','class'=>'field','value'=> $picasa_url ))?></td>
                                </tr>
                                
                                
                                   
                                <tr>
                                 <td width="276"><i class="icon icon24 i-user"></i>Vimeo URL<br>
                                   {vimeo_url}</td>
                                <td width="483"><?=form_input(array('id'=>'vimeo_url','name'=>'vimeo_url','class'=>'field','value'=> $vimeo_url ))?></td>
                                </tr>
                                
                                
                                
                                    
                                <tr>
                                 <td width="276"><i class="icon icon24 i-user"></i>Zopim Api<br>
                                   {zopim_url}</td>
                                <td width="483"><?=form_input(array('id'=>'zopim_api','name'=>'zopim_api','class'=>'field','value'=> $zopim_api ))?></td>
                                </tr>
                                <tr>
                                  <td>Google Analytics API<br>
                                 { google_analytics_api}</td>
                                  <td><?=form_input(array('id'=>'google_analytics_api','name'=>'google_analytics_api','class'=>'field','value'=> $google_analytics_api ))?></td>
                                </tr>
                                
                                
                                <tr>
                                 <td width="276"><i class="icon icon24 i-user"></i>About Us<br>
                                   {about_us}                                   <br></td>
                                <td width="483"><?=ee()->rte_lib->display_field($about_us, 'about_us', array('field_ta_rows' => 3,'field_text_direction' => 'ltr','field_fmt' => 'none',));?></td>
                                </tr>
                                
                                
                                 <tr>
                                 <td width="276"><i class="icon icon24 i-user"></i>Privacy Policy<br>
                                   {privacy_policy}</td>
                                <td width="483"><?=ee()->rte_lib->display_field($privacy_policy, 'privacy_policy', array('field_ta_rows' => 3,'field_text_direction' => 'ltr','field_fmt' => 'none',));?></td>
                                </tr>
                                
                                
                                 <tr>
                                 <td width="276"><i class="icon icon24 i-user"></i>Terms & Conditions<br>
                                   {terms_conditions}</td>
                                <td width="483"><?=ee()->rte_lib->display_field($terms_conditions, 'terms_conditions', array('field_ta_rows' => 3,'field_text_direction' => 'ltr','field_fmt' => 'none',));?></td>
                                </tr>
                                
                                
                                 <tr>
                                 <td width="276"><i class="icon icon24 i-user"></i>Delivery Information<br>
                                   {delivery_information}</td>
                                <td width="483"><?=ee()->rte_lib->display_field($delivery_information, 'delivery_information', array('field_ta_rows' => 3,'field_text_direction' => 'ltr','field_fmt' => 'none',));?></td>
                                </tr>
                                
                                
                                 <tr>
                                 <td width="276"><i class="icon icon24 i-user"></i>Contact Us<br>
                                   {contact_us}</td>
                                <td width="483"><?=ee()->rte_lib->display_field($contact_us, 'contact_us', array('field_ta_rows' => 3,'field_text_direction' => 'ltr','field_fmt' => 'none',));?></td>
                                </tr>
                                
                                
                                 <tr>
                                 <td width="276"><i class="icon icon24 i-user"></i>Faqs<br>
                                   {faqs}</td>
                                <td width="483"><?=ee()->rte_lib->display_field($faqs, 'faqs', array('field_ta_rows' => 3,'field_text_direction' => 'ltr','field_fmt' => 'none',));?></td>
                                </tr>
                                
                                
                                 <tr>
                                 <td width="276"><i class="icon icon24 i-user"></i>Return Policy<br>
                                   {return_policy}</td>
                                <td width="483"><?=ee()->rte_lib->display_field($return_policy, 'return_policy', array('field_ta_rows' => 3,'field_text_direction' => 'ltr','field_fmt' => 'none',));?></td>
                                </tr>
                                
                                
                                 <tr>
                                 <td width="276"><i class="icon icon24 i-user"></i>Shipping Policy<br>
                                   {shipping_policy}</td>
                                <td width="483"><?=ee()->rte_lib->display_field($shipping_policy, 'shipping_policy', array('field_ta_rows' => 3,'field_text_direction' => 'ltr','field_fmt' => 'none',));?></td>
                                </tr>
                                
                                <tr>
                                  <td>Default Shipping Duration<br>
                                  {default_shipping_duration}</td>
                                  <td><?=form_input(array('id'=>'default_shipping_duration','name'=>'default_shipping_duration','class'=>'field','value'=> $default_shipping_duration ))?></td>
                                </tr>
								
								 <tr>
                                  <td>Register Benefits (Optional)<br>
                                  {default_shipping_duration}
								  <br><small>This will show up to users as a message while registration. (Ex : "REGISTER NOW AND GET 10%OFF OR &#8377; 500/- OFF ON ORDERS ABOVE 5000" You may create a 10% off discount for first time orders for users.)</small>
								  <br>Tip : USE <strong>&amp;#8377; </strong> for <strong>&#8377;</strong> Symbol.
								  </td>
                                  <td><?=form_input(array('id'=>'register_benefits','name'=>'register_benefits','class'=>'register_benefits','placeholder' => 'Example : Register Now & Get &#8377; 500/- Discount Vouchers Free','value'=> $register_benefits ))?></td>
                                </tr>
                                                               
                                
                                <tr>
                                   <td>Default Currency Conversions<br>
								   <h3 style="line-height:1.2em; color:red">NOTE : Only use .00 and .50 as fractions <br> if not, Checkout will fail at times.</h3>
								   <h4 style="color:#f90">Wrong Usage Examples: 12.36, 72.69, 62.20, 85.05</h4>								   
								   <h4 style="color:green">Correct Usage Examples: 12.00, 72.50, 62.00, 85.00, </h4>
								   
								   </td>
                                   <td><table width="100%" border="1">
                                     <tr>
                                       <td width="46%" align="center">CURRENCY</td>
                                       <td width="11%" align="center">&nbsp;</td>
                                       <td width="43%" align="center">BASE CURRENCY (INR)</td>
                                     </tr>
                                     <tr>
                                       <td align="center">1 USD</td>
                                       <td align="center">=</td>
                                       <td align="center">
<?=form_input(array('id'=>'usd_inr','name'=>'usd_inr','class'=>'usd_inr','value'=> $usd_inr ))?> INR [<a href='https://www.google.co.in/#q=1usd+to+inr' target="_blank">Check]</a></td>
                                     </tr>
                                     <tr>
                                       <td align="center">1 GBP</td>
                                       <td align="center">= </td>
                                       <td align="center">
<?=form_input(array('id'=>'gbp_inr','name'=>'gbp_inr','class'=>'gbp_inr','value'=> $gbp_inr))?> INR [<a href='https://www.google.co.in/#q=1gbp+to+inr'  target="_blank">Check]</a></td>
                                     </tr>
                                      <tr>
                                       <td align="center">1 EUR</td>
                                       <td align="center">= </td>
                                       <td align="center">
<?=form_input(array('id'=>'eur_inr','name'=>'eur_inr','class'=>'eur_inr','value'=> $eur_inr))?> INR [<a href='https://www.google.co.in/#q=1eur+to+inr'  target="_blank">Check]</a></td>
                                     </tr>
                                      <tr>
                                       <td align="center">1 AUD</td>
                                       <td align="center">= </td>
                                       <td align="center">
<?=form_input(array('id'=>'aud_inr','name'=>'aud_inr','class'=>'aud_inr','value'=> $aud_inr))?> INR [<a href='https://www.google.co.in/#q=1aud+to+inr'  target="_blank">Check]</a></td>
                                     </tr>
                                     <tr>
                                       <td align="center">1 CAD</td>
                                       <td align="center">= </td>
                                       <td align="center">
<?=form_input(array('id'=>'cad_inr','name'=>'cad_inr','class'=>'cad_inr','value'=> $cad_inr))?> INR [<a href='https://www.google.co.in/#q=1cad+to+inr'  target="_blank">Check]</a></td>
                                     </tr>
                                   </table></td>
                                </tr>
                                <tr>
                                  <td>&nbsp;</td>
                                  <td><?php echo form_submit('mysubmit', 'Submit Settings');?></td>
                                </tr>
                                
                                
                              </tbody>
      </table><?php echo form_close();?>
      