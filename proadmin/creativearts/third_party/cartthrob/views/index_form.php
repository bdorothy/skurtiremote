
<?=$form_open?>
<div id="cartthrob_settings_content">
	<?php $orig_view_path = $this->load->_ci_view_path; ?>
	<?php foreach ($sections as $section) : ?>
		<?php $this->load->_ci_view_path = (isset($view_paths[$section])) ? $view_paths[$section] : $orig_view_path; ?>
<h3 data-hash="<?=$section?>"><?=lang($section.'_header')?></h3>
<div style="padding: 5px 1px;">
			<?=$this->load->view($section, $data, TRUE)?>
</div>
	<?php endforeach; ?>
</div>
<p><input type="submit" name="submit" value="Submit" class="submit" /></p>
</form>
