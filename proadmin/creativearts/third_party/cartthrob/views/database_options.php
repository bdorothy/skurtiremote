<!-- start instruction box -->
<div class="ct_instruction_box">
	<h2><?=lang('database_options_header')?> </h2>
	<p><?=lang('database_options_description')?></p>
</div>
<!-- end instruction box -->
<div id="ct_form_options">

	<fieldset>
		<label><?=lang('database_options_tax_settings')?></label>
		<label class="radio">
			<input class='radio' type='radio' name='database_tax' value='1' <?php if (@$settings['database_tax']) : ?>checked='checked'<?php endif; ?> /> 
			<?=lang('yes')?>
		</label>
		<label class="radio">
			<input class='radio' type='radio' name='database_tax' value='0' <?php if ( ! @$settings['database_tax']) : ?>checked='checked'<?php endif; ?> /> 
			<?=lang('no')?>
		</label>
	</fieldset>

	
</div>