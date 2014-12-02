<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<!-- begin right column -->
<div class="ct_top_nav">
	<div class="ct_nav" >
			<?php foreach (array_keys($nav) as $method) : ?>
				<?php if (!in_array($method, $no_nav)) : ?>
					<?php  if ($method =="modules"):?>
 						<?php foreach ($nav[$method] as $k => $v) : ?>
 							<span class="button"><a class="nav_button" href="<?=BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$k.AMP.'method='.$v[$k]?>"><?=lang('nav_'.$v[$k])?></a></span>
						<?php endforeach; ?>
					<?php else: ?>
				<span class="button"><a class="nav_button<?php if ( ! $this->input->get('method') || $this->input->get('method') == $method) : ?> current<?php endif; ?>" href="<?=BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method='.$method?>"><?=lang('nav_'.$method)?></a></span>
			<?php endif; ?>
					
			<?php endif; ?>
			<?php endforeach; ?>
		<div class="clear_both"></div>	
	</div>	
</div>
<div class="clear_left shun"></div>
<?php if ($subnav) : ?>
<ul class="tab_menu" id="tab_menu_tabs">
			<?php foreach ($subnav as $_method) : ?>
        <li class="content_tab<?php if ($this->input->get('method') == $_method) : ?> current<?php endif; ?>">
                <a href="<?=BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method='.$_method?>"><?=lang('subnav_'.$_method)?></a>
        </li>
			<?php endforeach; ?>
</ul>
<?php endif; ?>
<div class="clear_left shun"></div>
<?php if ($this->session->flashdata('cartthrob_system_error')) : ?>
	<div id="ct_system_error">
		<h4><?=$this->session->flashdata('cartthrob_system_error')?></h4>
	</div>
<?php endif; ?>
<?php if ($this->session->flashdata('cartthrob_system_message')) : ?>
	<div id="ct_system_error">
		<h4><?=$this->session->flashdata('cartthrob_system_message')?></h4>
	</div>
<?php endif; ?>


 
<?php if ( ! $extension_enabled) : ?>
	<div id="ct_system_error">
		<h4><?=lang('extension_not_installed')?></h4>
		<?=lang('please')?> <a href="<?=BASE.AMP.'C=addons_extensions'?>"><?=lang('enable')?></a> <?=lang('before_proceeding')?>
	</div>
<?php endif; ?>
<?php if ( ! $module_enabled) : ?>
	<div id="ct_system_error">
		<h4><?=lang('module_not_installed')?></h4>
		<?=lang('please')?> <a href="<?=BASE.AMP.'C=addons_modules'?>"><?=lang('install')?></a> <?=lang('before_proceeding')?>
	</div>
<?php endif; ?>
<?php if ( ! $no_form) : ?>
<?=$form_open?>
<?php endif; ?>
<div id="cartthrob_settings_content">
	<?php if (version_compare(APP_VER, '2.2', '<')) $orig_view_path = $this->load->_ci_view_path; ?>
	
	<?php if ($settings['license_number'] == '') : ?>
		<?php if (version_compare(APP_VER, '2.2', '<')) $this->load->_ci_view_path = (isset($view_paths['set_license_number'])) ? $view_paths['set_license_number'] : $orig_view_path; ?>
		<?=$this->load->view('set_license_number', $data, TRUE)?>
	<?php endif; ?>
	
	<?php foreach ($sections as $section) : ?>
		
		<?php if ($settings['license_number'] != '' && $section == "set_license_number") : ?>
			<?php if (version_compare(APP_VER, '2.2', '<')) $this->load->_ci_view_path = (isset($view_paths['set_license_number'])) ? $view_paths['set_license_number'] : $orig_view_path; ?>
			<?=$this->load->view('set_license_number', $data, TRUE)?>
		<?php elseif ($section !="set_license_number"): ?>
			<?php if (version_compare(APP_VER, '2.2', '<')) $this->load->_ci_view_path = (isset($view_paths[$section])) ? $view_paths[$section] : $orig_view_path; ?>
			<?=$this->load->view($section, $data, TRUE)?>
		<?php endif; ?>
		
	<?php endforeach; ?>
<?php if ( ! $no_form) : ?>
<p><input type="submit" name="submit" value="Submit" class="submit" /></p>
</form>
<?php endif; ?>

