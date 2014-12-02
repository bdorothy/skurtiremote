<?php if(isset($total_results) && $total_results > 0):?>
	<?=lang('showing')?> <?=($total_results > 1) ? lang('results') : lang('result')?> <?=$showing?> <?=lang('to')?> <?=$showing_to?> <?=lang('out_of')?> <?=$total_results?> <?=($total_results > 1) ? lang('results') : lang('result')?>
<?php else:?>
	<?=lang('no_results')?>
<?php endif;?>

<div class="clear"></div>

<?=$pagination?>
<?=form_open($action_url_entries)?>
<div id="resultArea-inner">
<table class="mainTable resultTable sortable" width="100%" cellspacing="0">
<thead>
<tr>
	<?php if(isset($entry)):?>
		<th width="1%" id="entry_checkbox"><?=form_checkbox('', '', '', 'class="selectAll" id="checkcolumn"')?></th>
		
		<?php foreach($field_order as $order => $col_name) :?>
			
			
			<?php if(substr($col_name, 0, 6) != 'field_') :?>
				<?php /* -- standard stuff -- */ ?>
				
				<?php if(isset(${$col_name}) && ${$col_name} == "y") :?>

						<th id="<?=$column_headers['sort'][$col_name]?>" class="<?=($this->input->get_post('orderby') == $column_headers['sort'][$col_name] && $this->input->get_post('sort') == "desc") ? 'headerSortUp' : '' ;?><?=($this->input->get_post('orderby') == $column_headers['sort'][$col_name] && $this->input->get_post('sort') == "asc") ? 'headerSortDown' : '' ;?>"><?=$column_headers[$col_name]?></th>
				
				<?php endif;?>
			
			<?php else :?>
			
				<?php /* -- fields -- */ ?>
				<?php $field_id = str_replace('field_', '', $col_name);?>
				<?php if(isset($field[$field_id])):?>
				
						<th id="field_id_<?=$field_id?>" class="<?=($this->input->get_post('orderby') == 'field_id_'.$field_id && $this->input->get_post('sort') == "desc") ? 'headerSortUp' : '' ;?><?=($this->input->get_post('orderby') == 'field_id_'.$field_id && $this->input->get_post('sort') == "asc") ? 'headerSortDown' : '' ;?>"><?=$column_headers[$col_name]?></th>
					
				<?php endif;?>
				
			<?php endif;?>
		
		<?php endforeach;?>
		
		
	<?php endif;?>
	
</tr>
</thead>

<tbody>
	<?php if(isset($entry)):?>
	
		<?php $c = 1;?>		
		<?php foreach($entry as $id => $entry_info) :?>
		<?php $row_class = ($c % 2 == 0) ? 'odd' : 'even';?>
		<tr class="entryRow <?=$row_class?>">
			<td class="selectable"><?=form_checkbox('toggle[]', $id, '', 'class="checkcolumn"')?></td>
			
			<?php foreach($field_order as $order => $col_name) :?>
				<?php if(substr($col_name, 0, 6) != 'field_') :?>
					<?php /* -- standard stuff -- */ ?>
					<?php $data = str_replace('show_', '', $col_name);?>
					<?php if(isset(${$col_name}) && ${$col_name} == "y") :?>
						<td><?= ! empty($entry_info[$data]) ? $entry_info[$data] : '&nbsp;'?></td>
					<?php endif;?>
			
				<?php else :?>
			
					<?php /* -- fields -- */ ?>
					<?php $field_id = str_replace('field_', '', $col_name);?>
					<?php if(isset($entry_info['fields'][$field_id])):?>
					
							<td<?=($field_text_direction[$field_id] == 'rtl') ? ' style="direction: rtl; text-align: right" dir="rtl"' : ''?>><?=$entry_info['fields'][$field_id]?></td>
						
					<?php endif;?>
				<?php endif;?>
			<?php endforeach;?>
		</tr>
		<?php $c++;?>
		<?php endforeach;?>
	<?php endif;?>
</tbody>
</table>
</div>
<?=$pagination?>

<br /><br />

<?php if(isset($total_results) && $total_results > 0):?>
<table class="" border="0">
	<tr>
		<td>
			<fieldset>
				<legend><strong><?=lang('entries')?></strong></legend>
				<button name="action" type="submit" value="edit" class="submit zenbu_action_validate"><?=lang('edit')?></button>
				<?php if($can_delete_self_entries == 'y' || $can_delete_all_entries == 'y') :?>
				<button name="action" type="submit" value="delete" class="submit zenbu_action_validate"><?=lang('delete')?></button>
				<?php endif;?>
			</fieldset>
		</td>
		
		<td class="catEditButtons">
			<?php if($hide_categories != 'y' || empty($ch_id)) :?>
			<fieldset>
				<legend><strong>
					<?php $ch_id = $this->input->get_post('channel_id');?>
					<?php if($hide_categories != 'y' || empty($ch_id)) :?>
					<?=lang('categories')?>
					<?php endif; ?>
				</strong></legend>		
				<button name="action" type="submit" value="add_categories" class="submit zenbu_action_validate"><?=lang('add')?></button>
				<button name="action" type="submit" value="remove_categories" class="submit zenbu_action_validate"><?=lang('remove')?></button>
			</fieldset>
			<?php endif;?>
		</td>
	</tr>
</table>
<?php endif;?>


<?=form_close()?>