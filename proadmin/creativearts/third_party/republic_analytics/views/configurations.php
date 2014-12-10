<?php if ( empty($settings['google_token'])): ?>

	<style>
	div.notice  { border-left: 10px solid #E7174B; padding: 10px 15px 2px 15px; margin-bottom: 20px; font-weight: bold;	color: #E7174B; }
	ul {
		list-style: disc;
		margin-left: 20px;
		margin-bottom: 20px;
		line-height: 1.4;
	}
	li {
		padding-bottom: 3px;
	}
	</style>
	<div class="notice"><p><?php echo lang('republic_analytics_google_login_required');?></p></div>

	<h3>Quick guide to set up OAuth2 authenticatication to the Google Analytics API</h3>
	<p>
		Both OAuth1 and AuthSub, which we used before, is deprecated by Google as of April 20, 2012.
		Since you install Republic Analytics on your own, or a hosting company's server, you must register
		the application through Google's APIs Console. This is something you only need to do once, and you
		can do this by following the steps below.<br /><br />
		<em>Note:</em> Make sure you are logged in with the correct Google Account (the one you use for Google Analytics).<br /><br />
		<strong>By the way, there might be a better way to do this, but we wanted to get a new working copy of Republic Analytics out as soon as possible while we look in to other options...</strong><br /><br />
	</p>

	<h4 style="padding-bottom: 5px">Step 1 - Create a Client ID</h4>
	<ul>
		<li>Visit <a target="_blank" href="https://code.google.com/apis/console">https://code.google.com/apis/console</a> and log in with your Google account if not already logged in</li>
		<li>(If needed, create a project when prompted to do so)</li>
		<li>Press the down arrow in the left panel (under the Google apis logo)</li>
		<li>Press <em>Create…</em></li>
		<li>Name your project "Republic Analytics" (or something else)</li>
		<li>Press <em>Create project</em></li>
		<li>Now a list of APIs should appear. You want to find "Analytics API" and switch that API to "ON"</li>
		<li>Select the API Access tab on the left side.</li>
		<li>Press <em>Create OAuth 2.0 Client</em> and create your client.</li>
		<li>Enter a suitable Product name, like Republic Analytics, and click <em>Next</em></li>
		<li>Select <em>Web application</em></li>
		<li>
			Click <em>more options</em> and paste your Redirect URI in <em>Authorized Redirect URIs</em>:
			<br />
			<code><?php echo $return_url;?></code>
			<br /><br />
			<em>
				The domain in <em>Authorized Redirect URIs</em> must match the domain that you will be using Republic Analytics from. The only supported local domain
				is <code>http://localhost</code>, other local domains like <code>http://sitename.dev</code> won't work against the Google API.
				See <a href="https://developers.google.com/accounts/docs/OAuth2">https://developers.google.com/accounts/docs/OAuth2</a> for more information about this.
				<br /><br />
			</em>
		</li>
		<li>Enter your EE installs domain name in "Authorized JavaScript Origins"</li>
		<li>Click "Create client ID"</li>
	</ul>

	<h4 style="padding-bottom: 5px">Step 2 - Authenticate Republic Analytics</h4>
	<p>
		Now, copy the listed <strong>Client ID</strong>, <strong>Client secret</strong> and <strong>Redirect URI</strong>
		to the input fileds below and hit Authenticate. As long as you entered the correct values and have set up the correct Redirect URI
		in your Google API Console, as described above, you should be able to start using <em>Republic Analytics</em> in your ExpressionEngine Control Panel. Enjoy!
	</p>
	<p>&nbsp;</p>

<?php endif;?>

<!-- Google API login -->
<?php if ( empty($settings['google_token'])): ?>
	<?php echo form_open($authenticate_url, '', FALSE); ?>
<?php endif;?>

<table class="mainTable padTable" cellspacing="0" cellpadding="0" border="0" id="google_configs">
	<thead>
		<tr>
			<th colspan="2"><?php echo lang('republic_analytics_google_account_config');?></th>
		</tr>
	</thead>
	<tbody>
		<?php if ( empty($settings['google_token'])): ?>
		<tr class="even">
			<td style="width: 50%">
				<label for="google_authsub_login"><?php echo lang('republic_analytics_google_account_login_authsub');?></label>
				<div class="subtext">
					<?php echo lang('republic_analytics_google_account_login_authsub_subtext');?>
				</div>
			</td>
			<td>
				<div>
					<label for="client_id">Client ID</label>
					<input type="text" name="client_id" id="client_id" />
				</div>

				<div style="margin-top: 10px">
					<label for="client_secret">Client Secret</label>
					<input type="text" name="client_secret" id="client_secret" />
				</div>

				<div style="margin-top: 10px">
					<label for="redirect_url">Redirect URI</label>
					<input type="text" name="redirect_url" id="redirect_url" value="<?php echo $return_url;?>" />
					<p class="subtext">If the domain does not match your current domain, fix that, else you'll have problem with your session</p>
				</div>

				<div style="margin-top: 10px">
					<input type="submit" name="submit" value="Authenticate"/>
				</div>

			</td>
		</tr>
		<?php endif;?>

		<?php if ( ! empty($settings['google_token'])): ?>
			<tr class="even">
				<td style="width: 50%"><label for="google_authsub_login"><?php echo lang('republic_analytics_google_account_label');?></label></td>
				<td><a href="<?php echo $logout_url;?>"><?php echo lang('republic_analytics_google_account_logout');?></a></td>
			</tr>
		<?php endif;?>
	</tbody>
</table>
<?php if ( empty($settings['google_token'])): ?>
	<?php echo form_close(); ?>
<?php endif;?>

<?php if( ! empty($settings['google_token'])): ?>
<!-- Module configuratinos -->
<?php echo form_open($action_url, '', FALSE)?>

<?php $old_group = "";?>
<table class="mainTable padTable" cellspacing="0" cellpadding="0" border="0">
	<thead>
		<tr>
			<th colspan="2"><?php echo lang('republic_analytics_extension_config'); ?></th>
		</tr>
	<thead>
	<tbody>
		<?php if ($extension_installed) : ?>
		<tr class="even">
			<td style="width:50%">
				<label><?php echo lang('republic_analytics_configuration_redirect_on_login');?></label></td>
			</td>
			<td>
				<?php $yes_selected = (set_value('redirect_on_login', $settings['redirect_on_login']) === 'y') ? "checked=checked" : ""; ?>
				<?php $no_selected  = (set_value('redirect_on_login', $settings['redirect_on_login']) === 'n' OR set_value('redirect_on_login', $settings['redirect_on_login']) === '' ) ? "checked=checked" : ""; ?>
				<label for="redirect_on_login_y"><?php echo lang('yes');?></label>
				<input type="radio" id="redirect_on_login_y" name="redirect_on_login" value="y" style="margin: 0 10px 0 3px" <?php echo $yes_selected;?> />
				<label for="redirect_on_login_n"><?php echo lang('no');?></label>
				<input type="radio" id="redirect_on_login_n" name="redirect_on_login" value="n" style="margin: 0 10px 0 3px" <?php echo $no_selected;?> />
			</td>
		</tr>
		<tr class="odd">
			<td style="width:50%">
				<label><?php echo lang('republic_analytics_configuration_override_homepage_icon');?></label></td>
			</td>
			<td>
				<?php $yes_selected = (set_value('override_homepage_icon', $settings['override_homepage_icon']) === 'y') ? "checked=checked" : ""; ?>
				<?php $no_selected  = (set_value('override_homepage_icon', $settings['override_homepage_icon']) === 'n' OR set_value('override_homepage_icon', $settings['override_homepage_icon']) === '' ) ? "checked=checked" : ""; ?>
				<label for="override_homepage_icon_y"><?php echo lang('yes');?></label>
				<input type="radio" id="override_homepage_icon_y" name="override_homepage_icon" value="y" style="margin: 0 10px 0 3px" <?php echo $yes_selected;?> />
				<label for="override_homepage_icon_n"><?php echo lang('no');?></label>
				<input type="radio" id="override_homepage_icon_n" name="override_homepage_icon" value="n" style="margin: 0 10px 0 3px" <?php echo $no_selected;?> />
			</td>
		</tr>
		<tr class="even">
			<td style="width:50%">
				<label><?php echo lang('republic_analytics_configuration_override_homepage_page');?></label></td>
			</td>
			<td>
				<?php $yes_selected = (set_value('override_homepage_page', $settings['override_homepage_page']) === 'y') ? "checked=checked" : ""; ?>
				<?php $no_selected  = (set_value('override_homepage_page', $settings['override_homepage_page']) === 'n' OR set_value('override_homepage_page', $settings['override_homepage_page']) === '' ) ? "checked=checked" : ""; ?>
				<label for="override_homepage_page_y"><?php echo lang('yes');?></label>
				<input type="radio" id="override_homepage_page_y" name="override_homepage_page" value="y" style="margin: 0 10px 0 3px" <?php echo $yes_selected;?> />
				<label for="override_homepage_page_n"><?php echo lang('no');?></label>
				<input type="radio" id="override_homepage_page_n" name="override_homepage_page" value="n" style="margin: 0 10px 0 3px" <?php echo $no_selected;?> />
			</td>
		</tr>
		<tr class="odd">
			<td>
				<label><?php echo lang('republic_analytics_configuration_member_group_redirect_on_login');?></label>
				<div class="subtext"><?php echo lang('republic_analytics_configuration_member_group_redirect_on_login_desc');?></div>
			</td>
			<td>
				<?php if ( ! empty($member_groups)) : ?>
					<?php foreach ($member_groups AS $member_group) : ?>
						<?php $is_selected = (is_array($settings['member_group_redirect_on_login']) && in_array($member_group['group_id'], $settings['member_group_redirect_on_login'])) ? 'checked=checked' : "";?>
						<input id="member_group_redirect_on_login_<?php echo $member_group['group_id'];?>" type="checkbox" name="member_group_redirect_on_login[]" value="<?php echo $member_group['group_id'];?>" style="margin: 0 10px 0 3px" <?php echo $is_selected;?> />
						<label for="member_group_redirect_on_login_<?php echo $member_group['group_id'];?>"><?php echo $member_group['group_title']?></label><br />
					<?php endforeach ; ?>
				<?php else :?>
					<?php echo lang('republic_analytics_no_member_groups'); ?>
				<?php endif;?>
			</td>
		</tr>
		<?php else:?>
		<tr class="even">
			<td colspan="2">
				<?php echo lang('republic_analytics_configuration_extension_required');?>
				<input type="hidden" name="redirect_on_login" value="n" />
				<input type="hidden" name="member_group_redirect_on_login" value="" />
				<input type="hidden" name="override_homepage_icon" value="n" />
				<input type="hidden" name="override_homepage_page" value="n" />
			</td>
			</td>
		</tr>
		<?php endif;?>

	</tbody>
</table>

<table class="mainTable padTable" cellspacing="0" cellpadding="0" border="0">
	<thead>
		<tr>
			<th colspan="2"><?php echo lang('republic_analytics_configuration_main'); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr class="even">
			<td style="width: 50%">
				<label><?php echo lang('republic_analytics_configuration_addon_access');?></label>
			</td>
			<td>
				<?php if ( sizeof($member_groups) > 0) : ?>
					<?php foreach ($member_groups AS $member_group) : ?>
						<?php $is_selected = (is_array($settings['addon_access']) && in_array($member_group['group_id'], $settings['addon_access']) OR $member_group['group_id'] === '1') ? 'checked=checked' : "";?>
						<input id="addon_access_<?php echo $member_group['group_id'];?>" type="checkbox" name="addon_access[]" value="<?php echo $member_group['group_id'];?>" style="margin: 0 10px 0 3px" <?php echo $is_selected;?> <?php if ($member_group['group_id'] === '1'):?>disabled<?php endif;?>/>
						<label for="addon_access_<?php echo $member_group['group_id'];?>"><?php echo $member_group['group_title']?></label><br />
					<?php endforeach ; ?>
				<?php else :?>
					<?php echo lang('republic_analytics_no_member_groups'); ?>
				<?php endif;?>
			</td>
		</tr>
	</tbody>
</table>

<table class="mainTable padTable" cellspacing="0" cellpadding="0" border="0" id="google_configs">
	<thead>
		<tr>
			<th colspan="2"><?php echo lang('republic_analytics_google_profile_config');?></th>
		</tr>
	</thead>
	<tbody>
		<tr class="even" id="google_account_tr">
			<td style="width: 50%"><label for="google_account"><?php echo lang('republic_analytics_configuration_google_account');?></label></td>
			<td>
				<?php if ( ! $is_google_connection_error && ! $google_error) : ?>
					<select id="google_account" name="google_account">
						<option value=""><?php echo lang('republic_analytics_no_profile'); ?></option>
							<?php foreach ($google_accounts AS $key => $google_account) : ?>
								<?php $is_selected = ( set_value('google_account', $settings['google_account']['profile_id']) == $key ) ? "selected=selected" : ""; ?>
								<option value="<?php echo $key;?>" <?php echo $is_selected;?>><?php echo $google_account['title']; ?></option>
							<?php endforeach;?>
					</select>
				<?php else: ?>
					<?php echo lang('republic_analytics_configuration_google_account_connection_error');?>
					<?php if ($google_error !== FALSE):?>
						<br /><?php echo lang('republic_analytics_configuration_google_error_code');?> <?php echo $google_error['code']; ?><br />
						<?php echo lang('republic_analytics_configuration_google_error_message');?> <?php echo $google_error['message']; ?>
					<?php endif;?>
					<input type="hidden" name="google_account" value="<?php echo (isset($settings['google_account']['profile_id'])) ? $settings['google_account']['profile_id'] : '';?>" />
				<?php endif;?>
			</td>
		</tr>

		<tr class="odd">
			<td><label><?php echo lang('republic_analytics_configuration_google_allow_profile_switch');?></label></td>
			<td>
				<?php if (sizeof($member_groups) > 0): ?>
					<?php foreach ($member_groups AS $member_group) : ?>
						<?php $is_selected = (is_array($settings['google_allow_profile_switch']) && in_array($member_group['group_id'], $settings['google_allow_profile_switch']) OR $member_group['group_id'] === '1') ? 'checked=checked' : "";?>
						<input id="google_allow_profile_switch_<?php echo $member_group['group_id'];?>" type="checkbox" name="google_allow_profile_switch[]" value="<?php echo $member_group['group_id'];?>" style="margin: 0 10px 0 3px" <?php echo $is_selected;?> <?php if ($member_group['group_id'] === '1'):?>disabled<?php endif;?> />
						<label for="google_allow_profile_switch_<?php echo $member_group['group_id'];?>"><?php echo $member_group['group_title']?></label><br />
					<?php endforeach ; ?>
				<?php else :?>
					<?php echo lang('republic_analytics_no_member_groups'); ?>
				<?php endif;?>
			</td>
		</tr>

		<tr class="even">
			<td><label><?php echo lang('republic_analytics_configuration_google_allow_member_groups');?></label></td>
			<td>
				<?php $yes_selected = (set_value('google_allow_member_groups', $settings['google_allow_member_groups']) === 'y') ? "checked=checked" : ""; ?>
				<?php $no_selected  = (set_value('google_allow_member_groups', $settings['google_allow_member_groups']) === 'n' OR set_value('google_allow_member_groups', $settings['google_allow_member_groups']) === '' ) ? "checked=checked" : ""; ?>
				<label for="google_allow_member_groups_y"><?php echo lang('yes');?></label>
				<input id="google_allow_member_groups_y" type="radio" name="google_allow_member_groups" value="y" checked="checked" style="margin: 0 10px 0 3px" <?php echo $yes_selected;?> />
				<label for="google_allow_member_groups_n"><?php echo lang('no');?></label>
				<input type="radio" id="google_allow_member_groups_n" name="google_allow_member_groups" value="n" style="margin: 0 10px 0 3px" <?php echo $no_selected;?>  />
			</td>
		</tr>
	</tbody>
	<thead>
		<tr class="member_groups" <?php if($settings['google_allow_member_groups'] === 'n'):?>style="display:none"<?php endif;?>>
			<th colspan="2"><?php echo lang('republic_analytics_configuration_member_groups');?></th>
		</tr>
	</thead>
	<tbody>
		<?php $i = 0; ?>
		<?php foreach ($member_groups AS $member_group) : ?>
			<tr class="<?php if ($i++ %2 == 0) : ?>even<?php else:?>odd<?php endif;?> member_groups" <?php if($settings['google_allow_member_groups'] === 'n'):?>style="display:none"<?php endif;?>>
			<td><label for="group_google_account_<?php echo $member_group['group_id']; ?>"><?php echo $member_group['group_title']; ?></label></td>
			<td>
				<?php if ( ! $is_google_connection_error) : ?>
					<select id="group_google_account_<?php echo $member_group['group_id']; ?>" name="group_google_account[<?php echo $member_group['group_id']; ?>]">
						<option value=""><?php echo lang('republic_analytics_default_profile'); ?></option>
						<?php foreach ($google_accounts AS $key => $google_account) : ?>
							<?php $is_selected = ( isset($settings['group_google_account'][$member_group['group_id']]) && set_value('google_account', $settings['group_google_account'][$member_group['group_id']]['profile_id']) == $key ) ? "selected=selected" : ""; ?>
							<option value="<?php echo $key;?>" <?php echo $is_selected;?>><?php echo $google_account['title']; ?></option>
						<?php endforeach;?>
					</select>
				<?php else:?>
					<?php echo lang('republic_analytics_configuration_google_account_connection_error');?>
					<?php $value = ( isset($settings['group_google_account'][$member_group['group_id']]) && isset($settings['group_google_account'][$member_group['group_id']]['profile_id'])) ? $settings['group_google_account'][$member_group['group_id']]['profile_id'] : "";?>
					<input type="hidden" name="group_google_account[<?php echo $member_group['group_id']; ?>]" value="<?php echo (isset($settings['google_account']['profile_id'])) ? $settings['google_account']['profile_id'] : '';?>" />
				<?php endif;?>
			</td>
		<?php endforeach;?>
	</tbody>
</table>


<table class="mainTable padTable" cellspacing="0" cellpadding="0" border="0">
	<thead>
		<tr>
			<th colspan="2"><?php echo lang('republic_analytics_graph_config');?></th>
		</tr>
	</thead>
	<tbody>
		<tr class="even">
			<td style="width: 50%"><label><?php echo lang('republic_analytics_configuration_monthly_view_show');?></label></td>
			<td>
				<?php $yes_selected = (set_value('show_monthly_view', $settings['show_monthly_view']) === 'y') ? "checked=checked" : ""; ?>
				<?php $no_selected  = (set_value('show_monthly_view', $settings['show_monthly_view']) === 'n' OR set_value('show_monthly_view', $settings['show_monthly_view']) === '' ) ? "checked=checked" : ""; ?>
				<label for="monthly_view_show_y"><?php echo lang('yes');?></label>
				<input id="monthly_view_show_y" type="radio" name="show_monthly_view" value="y" checked="checked" style="margin: 0 10px 0 3px"  <?php echo $yes_selected;?> />
				<label for="monthly_view_show_n"><?php echo lang('no');?></label>
				<input id="monthly_view_show_n" type="radio" name="show_monthly_view" value="n" style="margin: 0 10px 0 3px" <?php echo $no_selected;?> />
			</td>
		</tr>

		<tr class="odd">
			<td><label for="graph_type"><?php echo lang('republic_analytics_configuration_graph_type');?></label></td>
			<td>
				<select id="graph_type" name="graph_type">
					<option value="bar" <?php if($settings['graph_type'] == "bar"):?>selected="selected"<?php endif;?>><?php echo lang('republic_analytics_bars'); ?></option>
					<option value="lines" <?php if($settings['graph_type'] == "lines"):?>selected="selected"<?php endif;?>><?php echo lang('republic_analytics_lines'); ?></option>
				</select>
			</td>
		</tr>

		<div id="theme_url" style="display:none"><?php echo $theme_url;?></div>
		<tr class="even">
			<td><label for="visits_color"><?php echo lang('republic_analytics_configuration_visits_color');?></label></td>
			<td><input id="visits_color" type="text" name="visits_color" value="<?php echo set_value('visitors_color', $settings['visits_color']);?>" autocomplete="off" style="width: 90%; float: left"/></td>
		</tr>

		<tr class="odd">
			<td><label for="visitors_color"><?php echo lang('republic_analytics_configuration_visitors_color');?></label></td>
			<td><input id="visitors_color" type="text" name="visitors_color" value="<?php echo set_value('visitors_color', $settings['visitors_color']);?>" autocomplete="off" style="width: 90%; float: left" /></td>
		</tr>
		<tr class="even">
			<td style="width: 50%"><label><?php echo lang('republic_analytics_configuration_show_pages_view_in_table');?></label></td>
			<td>
				<?php $yes_selected = (set_value('show_pages_view_in_table', $settings['show_pages_view_in_table']) === 'y') ? "checked=checked" : ""; ?>
				<?php $no_selected  = (set_value('show_pages_view_in_table', $settings['show_pages_view_in_table']) === 'n' OR set_value('show_pages_view_in_table', $settings['show_pages_view_in_table']) === '' ) ? "checked=checked" : ""; ?>
				<label for="show_pages_view_in_table_y"><?php echo lang('yes');?></label>
				<input id="show_pages_view_in_table_y" type="radio" name="show_pages_view_in_table" value="y" checked="checked" style="margin: 0 10px 0 3px"  <?php echo $yes_selected;?> />
				<label for="show_pages_view_in_table_n"><?php echo lang('no');?></label>
				<input id="show_pages_view_in_table_n" type="radio" name="show_pages_view_in_table" value="n" style="margin: 0 10px 0 3px" <?php echo $no_selected;?> />
			</td>
		</tr>
		<tr class="odd">
			<td><label for="pages_view_color"><?php echo lang('republic_analytics_configuration_pages_view_color');?></label></td>
			<td><input id="pages_view_color" type="text" name="pages_view_color" value="<?php echo set_value('pages_view_color', $settings['pages_view_color']);?>" autocomplete="off" style="width: 90%; float: left" /></td>
		</tr>
	</tbody>
</table>

<table class="mainTable padTable" cellspacing="0" cellpadding="0" border="0">
	<thead>
		<tr>
			<th colspan="2"><?php echo lang('republic_analytics_normal_config'); ?></th>
		</tr>
	</thead>
	<tbody>

		<tr class="even">
			<td width="50%"><label for="update_frequency"><?php echo lang('republic_analytics_configuration_update_frequency');?></label></td>
			<td>
				<select id="update_frequency" name="update_frequency">
					<?php foreach ($update_frequency AS $key => $frequency) : ?>
						<?php $is_selected = ( set_value('update_frequency', $settings['update_frequency']) == $key ) ? "selected=selected" : ""; ?>
						<option value="<?php echo $key;?>" <?php echo $is_selected;?>><?php echo $frequency;?></option>
					<?php endforeach;?>
				</select>
			</td>
		</tr>

		<tr class="odd">
			<td><label><?php echo lang('republic_analytics_configuration_source_view_show');?></label></td>
			<td>
				<?php $yes_selected = (set_value('show_source_view', $settings['show_source_view']) === 'y') ? "checked=checked" : ""; ?>
				<?php $no_selected  = (set_value('show_source_view', $settings['show_source_view']) === 'n' OR set_value('show_source_view', $settings['show_source_view']) === '' ) ? "checked=checked" : ""; ?>
				<label for="source_view_show_y"><?php echo lang('yes');?></label>
				<input id="source_view_show_y" type="radio" name="show_source_view" value="y" checked="checked" style="margin: 0 10px 0 3px"  <?php echo $yes_selected;?> />
				<label for="source_view_show_n"><?php echo lang('no');?></label>
				<input id="source_view_show_n" type="radio" name="show_source_view" value="n" style="margin: 0 10px 0 3px" <?php echo $no_selected;?> />
			</td>
		</tr>

		<tr class="even">
			<td><label><?php echo lang('republic_analytics_configuration_pages_view_show');?></label></td>
			<td>
				<?php $yes_selected = (set_value('show_pages_view', $settings['show_pages_view']) === 'y') ? "checked=checked" : ""; ?>
				<?php $no_selected  = (set_value('show_pages_view', $settings['show_pages_view']) === 'n' OR set_value('show_pages_view', $settings['show_pages_view']) === '' ) ? "checked=checked" : ""; ?>
				<label for="pages_view_show_y"><?php echo lang('yes');?></label>
				<input id="pages_view_show_y" type="radio" name="show_pages_view" value="y" checked="checked" style="margin: 0 10px 0 3px"  <?php echo $yes_selected;?> />
				<label for="pages_view_show_n"><?php echo lang('no');?></label>
				<input id="pages_view_show_n" type="radio" name="show_pages_view" value="n" style="margin: 0 10px 0 3px" <?php echo $no_selected;?> />
			</td>
		</tr>

		<tr class="odd">
			<td><label><?php echo lang('republic_analytics_configuration_today_view_show');?></label></td>
			<td>
				<?php $yes_selected = (set_value('show_today_view', $settings['show_today_view']) === 'y') ? "checked=checked" : ""; ?>
				<?php $no_selected  = (set_value('show_today_view', $settings['show_today_view']) === 'n' OR set_value('show_today_view', $settings['show_today_view']) === '' ) ? "checked=checked" : ""; ?>
				<label for="today_view_show_y"><?php echo lang('yes');?></label>
				<input id="today_view_show_y" type="radio" name="show_today_view" value="y" checked="checked" style="margin: 0 10px 0 3px"  <?php echo $yes_selected;?> />
				<label for="today_view_show_n"><?php echo lang('no');?></label>
				<input id="today_view_show_n" type="radio" name="show_today_view" value="n" style="margin: 0 10px 0 3px" <?php echo $no_selected;?> />
			</td>
		</tr>

		<tr class="even">
			<td><label><?php echo lang('republic_analytics_configuration_yesterday_view_show');?></label></td>
			<td>
				<?php $yes_selected = (set_value('show_yesterday_view', $settings['show_yesterday_view']) === 'y') ? "checked=checked" : ""; ?>
				<?php $no_selected  = (set_value('show_yesterday_view', $settings['show_yesterday_view']) === 'n' OR set_value('show_yesterday_view', $settings['show_yesterday_view']) === '' ) ? "checked=checked" : ""; ?>
				<label for="yesterday_view_show_y"><?php echo lang('yes');?></label>
				<input id="yesterday_view_show_y" type="radio" name="show_yesterday_view" value="y" checked="checked" style="margin: 0 10px 0 3px"  <?php echo $yes_selected;?> />
				<label for="yesterday_view_show_n"><?php echo lang('no');?></label>
				<input id="yesterday_view_show_n" type="radio" name="show_yesterday_view" value="n" style="margin: 0 10px 0 3px" <?php echo $no_selected;?> />
			</td>
		</tr>

		<tr class="odd">
			<td><label><?php echo lang('republic_analytics_configuration_week_view_show');?></label></td>
			<td>
				<?php $yes_selected = (set_value('show_week_view', $settings['show_week_view']) === 'y') ? "checked=checked" : ""; ?>
				<?php $no_selected  = (set_value('show_week_view', $settings['show_week_view']) === 'n' OR set_value('show_week_view', $settings['show_week_view']) === '' ) ? "checked=checked" : ""; ?>
				<label for="week_view_show_y"><?php echo lang('yes');?></label>
				<input id="week_view_show_y" type="radio" name="show_week_view" value="y" checked="checked" style="margin: 0 10px 0 3px"  <?php echo $yes_selected;?> />
				<label for="week_view_show_n"><?php echo lang('no');?></label>
				<input id="week_view_show_n" type="radio" name="show_week_view" value="n" style="margin: 0 10px 0 3px" <?php echo $no_selected;?> />
			</td>
		</tr>

		<tr class="even">
			<td><label><?php echo lang('republic_analytics_configuration_month_view_show');?></label></td>
			<td>
				<?php $yes_selected = (set_value('show_month_view', $settings['show_month_view']) === 'y') ? "checked=checked" : ""; ?>
				<?php $no_selected  = (set_value('show_month_view', $settings['show_month_view']) === 'n' OR set_value('show_month_view', $settings['show_month_view']) === '' ) ? "checked=checked" : ""; ?>
				<label for="month_view_show_y"><?php echo lang('yes');?></label>
				<input id="month_view_show_y" type="radio" name="show_month_view" value="y" checked="checked" style="margin: 0 10px 0 3px"  <?php echo $yes_selected;?> />
				<label for="month_view_show_n"><?php echo lang('no');?></label>
				<input id="month_view_show_n" type="radio" name="show_month_view" value="n" style="margin: 0 10px 0 3px" <?php echo $no_selected;?> />
			</td>
		</tr>

		<tr class="odd">
			<td><label><?php echo lang('republic_analytics_configuration_browser_view_show');?></label></td>
			<td>
				<?php $yes_selected = (set_value('show_browser_view', $settings['show_browser_view']) === 'y') ? "checked=checked" : ""; ?>
				<?php $no_selected  = (set_value('show_browser_view', $settings['show_browser_view']) === 'n' OR set_value('show_browser_view', $settings['show_browser_view']) === '' ) ? "checked=checked" : ""; ?>
				<label for="browser_view_show_y"><?php echo lang('yes');?></label>
				<input id="browser_view_show_y" type="radio" name="show_browser_view" value="y" checked="checked" style="margin: 0 10px 0 3px"  <?php echo $yes_selected;?> />
				<label for="browser_view_show_n"><?php echo lang('no');?></label>
				<input id="browser_view_show_n" type="radio" name="show_browser_view" value="n" style="margin: 0 10px 0 3px" <?php echo $no_selected;?> />
			</td>
		</tr>

		<tr class="even">
			<td><label><?php echo lang('republic_analytics_configuration_operativsystem_view_show');?></label></td>
			<td>
				<?php $yes_selected = (set_value('show_operativsystem_view', $settings['show_operativsystem_view']) === 'y') ? "checked=checked" : ""; ?>
				<?php $no_selected  = (set_value('show_operativsystem_view', $settings['show_operativsystem_view']) === 'n' OR set_value('show_operativsystem_view', $settings['show_operativsystem_view']) === '' ) ? "checked=checked" : ""; ?>
				<label for="operativsystem_view_show_y"><?php echo lang('yes');?></label>
				<input id="operativsystem_view_show_y" type="radio" name="show_operativsystem_view" value="y" checked="checked" style="margin: 0 10px 0 3px"  <?php echo $yes_selected;?> />
				<label for="operativsystem_view_show_n"><?php echo lang('no');?></label>
				<input id="operativsystem_view_show_n" type="radio" name="show_operativsystem_view" value="n" style="margin: 0 10px 0 3px" <?php echo $no_selected;?> />
			</td>
		</tr>

		<tr class="odd">
			<td><label for="include_hosts"><?php echo lang('republic_analytics_configuration_include_hosts');?></label><div class="subtext"><?php echo lang('republic_analytics_configuration_include_hosts_subtext');?></div></td>
			<td><input id="include_hosts" type="text" name="include_hosts" value="<?php echo set_value('include_hosts', $settings['include_hosts']);?>" autocomplete="off" /></td>
		</tr>


		<tr class="even">
			<td><label for="exclude_hosts"><?php echo lang('republic_analytics_configuration_exclude_hosts');?></label><div class="subtext"><?php echo lang('republic_analytics_configuration_exclude_hosts_subtext');?></div></td>
			<td><input id="exclude_hosts" type="text" name="exclude_hosts" value="<?php echo set_value('exclude_hosts', $settings['exclude_hosts']);?>" autocomplete="off" /></td>
		</tr>
	</tbody>
</table>

<div class="tableFooter">
	<div class="tableSubmit" style="margin-top: 0">
		<?php echo form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit'))?>
	</div>
</div>

<?php echo form_close(); ?>
<?php endif;?>
<script>
	$("input[name$='google_allow_member_groups']").change(function(){
		if($(this).val() == 'y'){
			$(".member_groups").show();
		}else{
			$(".member_groups").hide();
		}
	});
</script>
