<?php $form_attr = array('id' => 'multi_cat');?>

<p><strong><?=lang('selected_entries')?></strong>: <?=$entry_title_list?></p>

<?php if( empty($categories) ) :?>
	<p><?=lang('no_category_groups_found'); ?></p>
	<a href="<?=BASE.AMP.'C=addons_modules&M=show_module_cp&module=zenbu&return_to_zenbu=y'?>" class="left cancel"><?=lang('cancel_and_return');?></a>
<?php else : ?>

	<?=form_open($action_url, $form_attr)?>
		
		<?=$hidden_fields?>
		
		<?php foreach($categories as $category_group_id => $category_array) :?>

		<table class="mainTable" border="0" cellpadding="0" cellspacing="0">
			<tr>
				<th class="center"><?=form_checkbox('', '')?></th>
				<th><?=$category_group_names[$category_group_id]?></th>
				<th><?=lang('currently_assigned_entries')?></th>
			</tr>
			<?php foreach($category_array as $cat_id => $cat_name): ?>
			<tr>
				<td width="1%" class="hoverable clickable center"><?=form_checkbox('category[]', $cat_id, FALSE, isset($entry_titles_per_category[$cat_id]) || $type == 'add' ? '' : 'disabled="disabled"')?></td>
				<td width="50%" class="hoverable"><?=$cat_name?></td>
				<td width="50%" class="hoverable">
					<?php if( isset($entry_titles_per_category[$cat_id]) ) : ?>
						<span class="subtext"><?=$entry_titles_per_category[$cat_id]?></span>
					<?php endif ?>
				</td>
			</tr>
			<?php endforeach ?>
		</table>
		<?php endforeach ?>

		<br />

		<button type="submit" class="submit left withloader" tabindex="1000">
			<span><?=lang('save')?></span>
			<span class="onsubmit invisible"><?=lang('saving')?> <i class="icon-spinner icon-spin"></i></span>
		</button>

		<a href="<?=BASE.AMP.'C=addons_modules&M=show_module_cp&module=zenbu&return_to_zenbu=y'?>" class="left cancel"><?=lang('cancel_and_return');?></a>

	<?=form_close();?>

	<style>
		.cancel
		{
			padding: 5px 0 0 15px;
		}
	</style>

<?php endif ?>
