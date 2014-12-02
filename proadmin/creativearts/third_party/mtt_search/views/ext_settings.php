<form method="post" action="<?=$base_url?>&amp;method=save_settings" class="-form">
	<div>
		<input type="hidden" name="<?=$csrf_token_name?>" value="<?=$csrf_token_value?>" />
	</div>
	<table cellpadding="0" cellspacing="0" class="mainTable">
		<colgroup>
			<col style="width:50%" />
			<col style="width:50%" />
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><?=lang('preference')?></th>
				<th scope="col"><?=lang('setting')?></th>
			</tr>
		</thead>
		<tbody>
			<tr class="<?=mtt_zebra()?>">
				<td>
					<label for="license_key"><?=lang('license_key')?></label>
					<p><?=lang('license_key_help')?></p>
				</td>
				<td>
					<input type="text" name="license_key" id="license_key" value="<?=htmlspecialchars($license_key)?>" />
				</td>
			</tr>
			<tr class="<?=mtt_zebra()?>">
				<td>
					<label for="encode_query"><?=lang('encode_query')?></label>
					<p><?=lang('encode_query_help')?></p>
				</td>
				<td>
					<label style="margin-right:1em"><input type="radio" name="encode_query" value="y"
						<?php if ($encode_query == 'y'): ?>checked="checked"<?php endif; ?> />
						<?=lang('yes')?>
					</label>
					<label><input type="radio" name="encode_query" value="n"
						<?php if ($encode_query == 'n'): ?>checked="checked"<?php endif; ?>
						<?php if ($uri_protocol == 'QUERY_STRING'): ?>disabled="disabled"<?php endif; ?> />
						<?=lang('no')?>
					</label>
				</td>
			</tr>
			<tr class="<?=mtt_zebra()?>">
				<td>
					<label for="default_result_page"><?=lang('default_result_page')?></label>
					<p><?=lang('default_result_page_help')?></p>
				</td>
				<td>
					<input type="text" name="default_result_page" id="default_result_page" class="medium" value="<?=htmlspecialchars($default_result_page)?>" />
				</td>
			</tr>
			<tr class="<?=mtt_zebra()?>">
				<td>
					<label for="default_search_mode"><?=lang('default_search_mode')?></label>
					<p><?=lang('default_search_mode_help')?></p>
				</td>
				<td>
					<select name="default_search_mode" id="default_search_mode">
					<?php foreach ($search_modes AS $mode): ?>
						<option value="<?=$mode?>"<?php if ($mode == $default_search_mode): ?> selected="selected"<?php endif; ?>>
							<?=lang($mode.'_mode')?>
						</option>
					<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr class="<?=mtt_zebra()?>">
				<td>
					<label for="excerpt_length"><?=lang('excerpt_length')?></label>
					<p><?=lang('excerpt_length_help')?></p>
				</td>
				<td>
					<input type="text" name="excerpt_length" id="excerpt_length" value="<?=htmlspecialchars($excerpt_length)?>" class="small" />
				</td>
			</tr>
			<tr class="<?=mtt_zebra()?>">
				<td class="multiple">
					<label for="excerpt_hilite"><?=lang('excerpt_hilite')?></label>
					<p><?=lang('excerpt_hilite_help')?></p>
				</td>
				<td>
					<select name="excerpt_hilite" id="excerpt_hilite">
						<option value=""><?=lang('do_not_hilite')?></option>
						<?php foreach ($hilite_tags AS $tag): ?>
						<option value="<?=$tag?>"<?php if ($tag == $excerpt_hilite): ?> selected="selected"<?php endif; ?>>
							<?=sprintf(lang('use_hilite_tag'), '&lt;'.$tag.'&gt;')?>
						</option>
						<?php endforeach; ?>
					</select>
					<div id="title_hilite" class="-more-settings"<?php if ( ! $excerpt_hilite): ?> style="display:none"<?php endif; ?>>
						<label>
							<input type="checkbox" name="title_hilite" value="y"<?php if ($title_hilite == 'y'): ?> checked="checked"<?php endif; ?> />
							<?=lang('title_hilite')?>
						</label>
					</div>
				</td>
			</tr>
			<tr class="<?=mtt_zebra()?>">
				<td>
					<label for="batch_size"><?=lang('batch_size')?></label>
					<p><?=lang('batch_size_help')?></p>
				</td>
				<td>
					<input type="text" name="batch_size" id="batch_size" value="<?=htmlspecialchars($batch_size)?>" class="small" />
				</td>
			</tr>
			<tr class="<?=mtt_zebra()?>">
				<td>
					<label for="search_log_size"><?=lang('search_log_size')?></label>
					<p><?=lang('search_log_size_help')?></p>
				</td>
				<td>
					<input type="text" name="search_log_size" id="search_log_size" value="<?=htmlspecialchars($search_log_size)?>" class="small" />
				</td>
			</tr>
			<tr class="<?=mtt_zebra()?>">
				<td>
					<label for="min_word_length"><?=lang('min_word_length')?></label>
					<p><?=lang('min_word_length_help')?></p>
				</td>
				<td>
					<input type="text" name="min_word_length" id="min_word_length" value="<?=htmlspecialchars($min_word_length)?>" class="small" />
				</td>
			</tr>
			<tr class="<?=mtt_zebra()?>">
				<td class="multiple">
					<label for="stop_words"><?=lang('stop_words')?></label>
					<p><?=lang('stop_words_help')?></p>
				</td>
				<td>
					<textarea name="stop_words" id="stop_words" cols="30" rows="10"><?=htmlspecialchars($stop_words)?></textarea>
				</td>
			</tr>
			<tr class="<?=mtt_zebra()?>">
				<td class="multiple">
					<label for="ignore_words"><?=lang('ignore_words')?></label>
					<p><?=lang('ignore_words_help')?></p>
				</td>
				<td>
					<textarea name="ignore_words" id="ignore_words" cols="30" rows="10"><?=htmlspecialchars($ignore_words)?></textarea>
				</td>
			</tr>
		</tbody>
	</table>
	<?php if ($groups): ?>
	<table cellpadding="0" cellspacing="0" style="width:100%" class="mainTable -extension-settings">
		<colgroup>
			<col style="width:16.67%" />
			<col style="width:16.67%" />
			<col style="width:16.67%" />
			<col style="width:16.67%" />
			<col style="width:16.67%" />
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><?=lang('member_group')?></th>
				<?php foreach ($permissions AS $perm): ?>
				<th scope="col"><?=lang($perm)?></th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($groups AS $group_id => $group_name): ?>
				<tr class="<?=mtt_zebra()?>">
					<td>
						<label><?=htmlspecialchars($group_name)?></label>
					</td>
					<?php foreach ($permissions AS $perm): ?>
					<td><input type="checkbox" name="<?=$perm?>[]" value="<?=$group_id?>"
						<?php if (in_array($group_id, $$perm)): ?>checked="checked"<?php endif;?> /></td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php endif; ?>
	<input type="submit" class="submit" value="<?=lang('save')?>" />
</form>