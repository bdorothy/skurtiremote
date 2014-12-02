<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
    <!-- start instruction box -->
    <div class="ct_instruction_box">
      <h2><?=lang('coupon_error_msgs_heading')?></h2>
      <p><?=lang('warning_and_error_messages')?></p>
    </div>

    <!-- end instruction box -->
      <div id="ct_form_options">

	      	<div class="ct_form_header">
				<h2><?=lang('warning_and_error_messages')?></h2>
	        </div>		
			<fieldset>
			<label><?=lang('coupon_valid_msg')?> (<?=lang('variables')?>: <span class="red">{discount}</span> <?=lang('and')?> <span class="red">{total}</span></label>
			<input  dir='ltr' type='text' name='coupon_valid_msg' id='coupon_valid_msg' value='<?=$settings['coupon_valid_msg']?>' size='90' maxlength='100' />
			</fieldset>

			<fieldset>
			<label><?=lang('coupon_invalid_msg')?></label>
			<input  dir='ltr' type='text' name='coupon_invalid_msg' id='coupon_invalid_msg' value='<?=$settings['coupon_invalid_msg']?>' size='90' maxlength='100' />
			</fieldset>

			<fieldset>
			<label><?=lang('coupon_inactive_msg')?></label>
			<input  dir='ltr' type='text' name='coupon_inactive_msg' id='coupon_inactive_msg' value='<?=$settings['coupon_inactive_msg']?>' size='90' maxlength='100' />
			</fieldset>

			<fieldset>
			<label><?=lang('coupon_expired_msg')?></label>
			<input  dir='ltr' type='text' name='coupon_expired_msg' id='coupon_expired_msg' value='<?=$settings['coupon_expired_msg']?>' size='90' maxlength='100' />
			</fieldset>

			<fieldset>
			<label><?=lang('coupon_limit_msg')?></label>
			<input  dir='ltr' type='text' name='coupon_limit_msg' id='coupon_limit_msg' value='<?=$settings['coupon_limit_msg']?>' size='90' maxlength='100' />
			</fieldset>

			<fieldset>
			<label><?=lang('coupon_user_limit_msg')?></label>
			<input  dir='ltr' type='text' name='coupon_user_limit_msg' id='coupon_user_limit_msg' value='<?=$settings['coupon_user_limit_msg']?>' size='90' maxlength='100' />
			</fieldset>
</div>