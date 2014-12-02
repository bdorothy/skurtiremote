<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<caption>Manual Update</caption>
		<tbody>
			<tr class="odd">
				<td>
					<label style="height:100%;">Run manual update</label>
				</td>
				<td style="width:50%;">
					<a class="submit" href="<?php echo BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method=update'; ?>">Run</a>
				</td>
			</tr>
		</tbody>	
	</table>
	
<?php if (count($templates_installed)) : ?>
	<h4><?=lang('installed')?>:</h4>
	<ul class="bullets">
		<?php foreach ($templates_installed as $installed) : ?>
			<li><?=$installed?></li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
<?php if (count($template_errors)) : ?>
	<h4><?=lang('errors')?>:</h4>
	<ul class="bullets">
		<?php foreach ($template_errors as $error) : ?>
			<li>
				<span class="alert"><?=$error?></span>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>


 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<caption><?=lang('install_channels_header')?></caption>
		<thead class="visualEscapism">
			<tr>
				<th><?=lang('preference')?></th><th><?=lang('setting')?></th>
			</tr>
		</thead>
		<tbody>
			<?php if (count($install_channels)) : ?>
				<tr class="<?=alternator('odd', 'even')?>">
					<td>
						<label style="height:100%;"><?=lang('section')?></label>
					</td>
					<td style='width:50%;'>
						<ul>
						<?php foreach ($install_channels as $index => $name): ?>
							<li>
								<label class="radio">
									<input type="checkbox" checked="checked" name="channels[]" class="templates" value="<?=$index?>" />
									<?=$name?>
								</label>
							</li>
						<?php endforeach; ?>
						</ul>
					</td>
				</tr>
				<?php 
				/*
				// sample channel data install is not ready for prime time. 
				<tr class="<?=alternator('odd', 'even')?>">
					<td>
						<label style="height:100%;"><?=lang('install_channel_data')?></label>
					</td>
					<td style='width:50%;'>
						<ul>
 							<li>
								<label class="radio">
								<input type="checkbox" checked="checked" name="channel_data"  value="1" />
									<?=lang('yes') ?>
								</label>
							</li>
 						</ul>
					</td>
				</tr>
				*/ 
				?>
 			<?php endif; ?>
			<?php if (count($install_template_groups)) : ?>
			<?php $checked = array('cart', 'cart_examples', 'cart_multi_page_checkout', 'cart_includes'); ?>
				<tr class="<?=alternator('odd', 'even')?>">
					<td>
						<label style="height:100%;"><?=lang('templates')?></label>
					</td>
					<td style='width:50%;'>
						<ul>
						<?php foreach ($install_template_groups as $index => $name): ?>
						<li>
							<label class="radio">
								<input type="checkbox" <?php if (in_array($name, $checked)) : ?>checked="checked" <?php endif; ?>name="templates[]" class="templates" value="<?=$index?>" />
								<?=$name?>
							</label>
						</li>
						<?php endforeach; ?>
						</ul>
					</td>
				</tr>	
			<?php endif; ?>
			<?php if (count($install_member_groups)) : ?>
			
 			<tr class="<?=alternator('odd', 'even')?>">
				<td>
					<label style="height:100%;"><?=lang('section')?></label>
				</td>
				<td style='width:50%;'>
					<ul>
					<?php foreach ($install_member_groups as $index => $name): ?>
						<li>
							<label class="radio">
								<input type="checkbox" checked="checked" name="templates[]" class="templates" value="<?=$index?>" />
								<?=$name?>
							</label>
						</li>
					<?php endforeach; ?>
					</ul>
				</td>
			</tr>
			<?php endif; ?>
		</tbody>	
	</table>

<?php if ($themes) : ?>	
	<p><input type="submit" name="submit" value="Install Templates &amp; Channels" class="submit"></p>

	</div>
</form>

	<?=str_repeat(BR, 2);?>
	<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method=install_theme')?>

	<div>
		
<?php if (count($themes_installed)) : ?>
	<h4><?=lang('installed')?>:</h4>
	<ul class="bullets">
		<?php foreach ($themes_installed as $installed) : ?>
			<li><?=$installed?></li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
<?php if (count($theme_errors)) : ?>
	<h4><?=lang('errors')?>:</h4>
	<ul class="bullets">
		<?php foreach ($theme_errors as $error) : ?>
			<li>
				<span class="alert"><?=$error?></span>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
		
 	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
		<caption>Install A Theme</caption>
		<tbody>
			<tr class="odd">
				<td>
					<label style="height:100%;">Select a theme to install:</label>
				</td>
				<td style="width:50%;">
					<?=form_dropdown('theme', $themes)?>
				</td>
			</tr>
		</tbody>	
	</table>
<?php endif; ?>

<p><input type="submit" name="submit" value="Install Templates &amp; Channels" class="submit"></p>

</div>
</form>