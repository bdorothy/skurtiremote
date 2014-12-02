<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
	<caption><?=lang('helpspot_overview_header')?></caption>
	<thead class="">
		<tr>
			<th colspan="2">
				<?=lang('helpspot_support_description')?>
				
			</th>
		</tr>
	</thead>
	<tbody>	
		<tr class="<?=alternator('even', 'odd')?>">
			<td colspan="2">
				<div class="cths_form">
				<?php echo $helpspot_create_form; ?>
					<h3><?=lang('level_1_support')?>* </h3>
					<label for="first_name"><?=lang('first_name')?></label>
					<input type="text" value="<?=$first_name?>" name="first_name" id="first_name"  size='90'  /> 
					
					<br>
					
					<label for="last_name"><?=lang('last_name')?></label>
					<input type="text" value="<?=$last_name?>" name="last_name" id="last_name"  size='90' /> 
					
					<br>
					<label for="sTitle"><?=element('sTitle', $helpspot_labels)?></label>
					<input type="text" value="Support Request" name="sTitle" id="sTitle"  size='90' /> 
					
					<br>
					<label for="tNote"><?=lang('message')?></label>
					<textarea name="tNote" id="tNote" rows="4" cols="40"/></textarea>
					
 					<br>

					<label for="xCategory"><?=lang('category')?></label>
					<select name="xCategory"  id="xCategory" class="cths_category">
						<?php foreach ($categories as $category_id => $category_name)
						{
							echo '<option value="'.$category_id.'">'.$category_name.'</option>'; 
						}
 						?>
					</select>
 					<br>
					
					<div id="support-form-custom-fields">
					
					</div>
					
					<label for="fUrgent"><?=lang('mark_as_urgent')?></label> 
					<input type="checkbox" value="1" name="fUrgent" id="fUrgent" /> 
					<br> 
					<hr>
					<h3><?=lang('level_2_support')?>**</h3>
					
					<label for="level2"><?=lang('grant_access')?>***</label> 
					<input type="checkbox" value="1" name="grant_access" id="level2" /> 
					<br> 
				
				
					<input type="submit" name="Submit" /> 
				</form>
				
				</div>
				<p><span style="color:red">*</span><small><?=lang('helpspot_form_description')?></small></p>
				<p><span style="color:red">**</span><small><?=lang('helpspot_level2_note')?></small></p>
				<p><span style="color:red">***</span><small><?=lang('helpspot_grant_login_access')?></small></p>
			</td>
		</tr>
		<?php 
		foreach ($requests as $request)
		{
			$request_id = element('xRequest', $request); 
			$date = element('dtGMTOpened', $request); 
			$category = element('sCategory', $request); 
			$title = element('sTitle', $request); 
			$accesskey = element('accesskey', $request); 
 			$class=''; 
			if (element('fUrgent', $request))
			{
				$class = "urgent"; 
			}
			
			echo '<tr class="'.alternator('even', 'odd').'">'; 
			echo '<td colspan="2">'; 
			echo '	<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">
					<caption>#'. $request_id." ". $title.'</caption>
					<thead class="">
						<tr>
							<th colspan="2">
								'.$category.'

							</th>
						</tr>
					</thead>
					<tbody>';
					
					foreach (element('request_history',$request) as $key=> $item)
					{
						// if there's only one request, it's not in a sub key. 
						if (empty($item[0]))
						{
							$backup = $item; 
							$item = array();
							$item[] = $backup; 
						}
 						foreach ($item as $it)
						{
							echo '<tr class="'.alternator('even', 'odd').'">'; 
							echo '<td colspan="2">';	 						

								echo "<h4 class='cths_date'>".date("M-d Y h:i a", element('dtGMTChange', $it))."</h4>"; 

								echo "<div class='cths_note'>". element('tNote', $it)."</div>"; 
								
								echo "<p class='cths_signature'><strong> - ". element('firstname', $it). "</strong></p>"; 

								if (element("files", $it))
								{
									echo "<h4>Files</h4>"; 
									echo "<div class='files'>"; 
									foreach (element('files', $it) as $f)
									{
										$file = element('file', $f ); 
										echo "<a target='_blank' href='".element('url', $file)."'>".element('sFilename', $f)."(".element('sFileMimeType', $f).")</a>"; 
									}
									echo "</div>"; 
								}
								
							#	echo "<hr>"; 
							echo"</td></tr>"; 
						}
						echo '<tr class="'.alternator('even', 'odd').'">'; 
						echo '<td colspan="2">';	 						
							echo $helpspot_update_form;
								
								echo "<h4>".lang('update_support_request')."* </h4>
								<input type='hidden' value='".element("accesskey", $request)."' name='accesskey' /> 
								<label for='tNote'>".lang('message')."</label>
								<textarea name='tNote' id='tNote' rows='4' cols='40'/></textarea>

								<br>

								<label for='fUrgent'>".lang('mark_as_urgent')."</label> 
								<input type='checkbox' value='1' name='fUrgent' id='fUrgent' /> 
								<br> 
								<hr>
								<!-- 
								<h4>".lang('level_2_support')."**</h4>

								<label for='level2'>".lang('grant_access')."***</label> 
								<input type='checkbox' value='1' name='grant_access' id='level2' /> 
								<br> 
								-->
								<input type='submit' name='Update' />
								";
							echo "</form>"; 
						echo "</tr></td>";
					}
				echo "</tbody></table>";
 			
			echo "</td>"; 
			echo "</tr>";
			}
		?>
 	</tbody>
</table>
