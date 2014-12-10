<?php

$lang = array(

	"republic_analytics_module_name" =>
	"Republic Analytics",

	"republic_analytics_main_name" =>
	"Statistics",

	"republic_analytics_module_description" =>
	"Displays Google Analytics data",

	"republic_analytics_configurations" =>
	"Configuration",

	"republic_analytics_label_key" =>
	"Key",

	"republic_analytics_label_value" =>
	"Value",

	"republic_analytics_label_action" =>
	"Action",

	"google_analytics_profile" =>
	"Google Analytics profile: ",

	"republic_analytics_today_error" =>
	"Couldn't get todays' data",

	"republic_analytics_yesterday_error" =>
	"Couldn't get yesterdays' data",

	"republic_analytics_connection_error" =>
	"There was a connection error, using cached data instead.",

	"republic_analytics_last_update" =>
	"Last updated: ",


	/* Configuration */

	"republic_analytics_google_login_required" =>
	"You need to login and authenticate your Google Analytics account before you can configure Republic Analytics",

	"republic_analytics_google_account_config" =>
	"Google Analytics account",

	"republic_analytics_google_account_login_authsub" =>
	"Login and authenticate Republic Analytics on your Google account using authSub",

	"republic_analytics_google_account_login_authsub_subtext" =>
	"You'll be redirected to a Google login page (if not allready logged in) and then asked to verify that Republic Analytics can access your Google Analytics data. After that you'll be redirected back here with the needed login token.",

	"republic_analytics_google_account_login" =>
	"Login",

	"republic_analytics_google_account_logout" =>
	"Log-out",

	"republic_analytics_google_account_label" =>
	"Log-out from your Google Account",

	"republic_analytics_google_profile_config" =>
	"Google Analytics profile settings",

	"republic_analytics_extension_config" =>
	"Extension settings",

	"republic_analytics_graph_config" =>
	"Graph settings",

	"republic_analytics_normal_config" =>
	"Statistics settings",

	"republic_analytics_google_config" =>
	"Google profile settings",

	"republic_analytics_access_config" =>
	"Module access",

	"republic_analytics_configuration_graph_type" =>
	"Type of graph you want to use",

	"republic_analytics_bars" =>
	"Bar Graph",

	"republic_analytics_lines" =>
	"Lines graph",

	"republic_analytics_configuration_redirect_on_login" =>
	"Redirect users to Republic Analytics when logging into the Control Panel",

	"republic_analytics_configuration_override_homepage_icon" =>
	"Redirect Home icon in main navigation to Republic Analytics ",

	"republic_analytics_configuration_override_homepage_page" =>
	"Redirect all links to CP Home to Republic Analytics",

	"republic_analytics_configuration_main" =>
	"Module settings",

	"republic_analytics_configuration_access" =>
	"Module access settings",

	"republic_analytics_configuration_addon_access" =>
	"Which member groups can access the module configuration",

	"republic_analytics_configuration_google_username" =>
	"Google Analytics account email",

	"republic_analytics_configuration_google_password" =>
	"Google Analytics account password",

	"republic_analytics_configuration_google_account" =>
	"Google Analytics profile",

	"republic_analytics_configuration_google_account_connection_error" =>
	"Could not retreive your Google Accounts from the Google Analytics API",

	"republic_analytics_configuration_google_not_valid" =>
	"You need to type in your Google information correctly to be able to select an account",

	"republic_analytics_configuration_google_connection_error" =>
	"There was error trying to establish the connection to the Google API",

	"republic_analytics_configuration_google_allow_member_groups" =>
	"Use different Google profiles for different member groups",

	"republic_analytics_configuration_google_allow_profile_switch" =>
	"Which member groups can switch between profiles in the Statistics view",

	"republic_analytics_configuration_show_latest_entries" =>
	"Show the latest updated entries",

	"republic_analytics_configuration_show_latest_comments" =>
	"Show the latest comments",

	"republic_analytics_configuration_monthly_view_show" =>
	"Show graph for the last three months",

	"republic_analytics_configuration_update_frequency" =>
	"Update frequency for all statistics",

	"republic_analytics_configuration_today_view_show" =>
	"Show statistics for today",

	"republic_analytics_configuration_yesterday_view_show" =>
	"Show statistics for yesterday",

	"republic_analytics_configuration_week_view_show" =>
	"Show statistics for week",

	"republic_analytics_configuration_month_view_show" =>
	"Show statistics for month",

	"republic_analytics_configuration_source_view_show" =>
	"Show statistics for sources",

	"republic_analytics_configuration_pages_view_show" =>
	"Show statistics for pages",

	"republic_analytics_configuration_browser_view_show" =>
	"Show statistics for browsers",

	"republic_analytics_configuration_operativsystem_view_show" =>
	"Show statistics for operation systems",

	"republic_analytics_configuration_visits_color" =>
	"Color for Visits (ie. #CCCCCC)",

	"republic_analytics_configuration_visitors_color" =>
	"Color for Visitors (ie. #95A4AF)",

	"republic_analytics_configuration_show_pages_view_in_table" =>
	"Show Page Views in the graph",

	"republic_analytics_configuration_pages_view_color" =>
	"Color for Page Views (ie. #EEEEEE)",

	"republic_analytics_configuration_member_groups" =>
	"Choose new profile for the following member groups",

	"republic_analytics_no_member_groups" =>
	"There are no member groups with access to the module",

	"republic_analytics_no_profile" =>
	"-- Profiles --",

	"republic_analytics_default_profile" =>
	"-- Use default profile --",

	"republic_analytics_loading_data_cached" =>
	"Updating your data from Google Analytics",

	"republic_analytics_loading_data_not_cached" =>
	"Updating your data from Google Analytics. This may take a minute since we are fetching all your data.",

	"republic_analytics_already_logged_in_part_1" =>
	"You are already logged in",

	"republic_analytics_click_here" =>
	"Click here",

	"republic_analytics_already_logged_in_part_2" =>
	"to log in using another account",

	"republic_analytics_google_not_logged_in" =>
	"You have to enter your credentials to access your Google Analytics data.",

	"republic_analytics_configuration_include_hosts" =>
	"Filter statistics, only show included hostnames",

	"republic_analytics_configuration_include_hosts_subtext" =>
	"Use this to sanitize the statistics to <b>include data</b> from the specified domain(s) only.<br />Seperate domains with a pipe, e.g: yourdomain.com|www.yourdomain.com",

	"republic_analytics_configuration_exclude_hosts" =>
	"Filter statistics, excluding the the following hostnames",

	"republic_analytics_configuration_exclude_hosts_subtext" =>
	"Use this to sanitize the statistics to <b>exclude data</b> from the specified domain(s).<br />Seperate domains with a pipe, e.g: yourdomain.com|www.yourdomain.com",

	/* Validation messages */

	"republic_analytics_configurations_saved" =>
	"The new configuration are now saved",

	"republic_analytics_configurations_google_failed" =>
	"The settings were saved, but the system was not able to authenticate your Google Analytics account using the information you provided.",

	"republic_analytics_configurations_google_success_no_profile" =>
	"The settings were saved and you were authenticated to Google Analytics, but you need to select Analytics profile below.",

	"republic_analytics_configurations_failure" =>
	"The settings were not saved. Google account username and password is required",

	"republic_analytics_logged_out" =>
	"You have successfully logged out and your tokens have been revoked",

	"republic_analytics_authentication_error_no_code" =>
	"Authentication Failed: The module did not receive the authentication code from Google.",

	"republic_analytics_authentication_error_no_refresh_token" =>
	"Authentication Failed: The module could not receive tokens from Google.",

	"republic_analytics_configuration_google_error_code" =>
	"<strong>Error code: </strong>",

	"republic_analytics_configuration_google_error_message" =>
	"<strong>Message: </strong>",

	/* Actions */

	"republic_analytics_add" =>
	"Add",

	"republic_analytics_submit" =>
	"Submit",

	"republic_analytics_update" =>
	"Update",

	"republic_analytics_delete" =>
	"Delete",


	/* Texts from views */

	"republic_analytics_all" =>
	"All",

	"republic_analytics_visits" =>
	"Visits",

	"republic_analytics_visitors" =>
	"Visitors",

	"republic_analytics_unique" =>
	"Unique",

	"republic_analytics_page_views" =>
	"Page Views",

	"republic_analytics_bouncerate" =>
	"Bounce rate",

	"republic_analytics_pages" =>
	"Pages",

	"republic_analytics_more" =>
	"More",

	"republic_analytics_sources" =>
	"Sources",

	"republic_analytics_time_on_page" =>
	"Time&nbsp;On&nbsp;Page",

	"republic_analytics_avg_time_on_page" =>
	"Avg&nbsp;Time&nbsp;On&nbsp;Page",

	"republic_analytics_avg_time_on_site" =>
	"Avg&nbsp;Time&nbsp;On&nbsp;Site",

	"republic_analytics_today" =>
	"Today",

	"republic_analytics_new_visits" =>
	"New Visits",

	"republic_analytics_browser" =>
	"Browser",

	"republic_analytics_os" =>
	"Operating system",

	"republic_analytics_yesterday" =>
	"Yesterday",

	"republic_analytics_week" =>
	"Last Week",

	"republic_analytics_month" =>
	"Last Month",

	"republic_analytics_no_profile_selected" =>
	"You have to select a Google profile under &ldquo;Configuration&rdquo; to receive Analytics data.",

	"republic_analytics_connection_error" =>
	"Can't connect to Google Analytics at the moment, using cached data.",

	"republic_analytics_configuration_member_group_redirect_on_login" =>
	"Which member groups should be redirected to Republic Analytics",

	"republic_analytics_configuration_member_group_redirect_on_login_desc" =>
	"",

	"republic_analytics_configuration_extension_required" =>
	"<i>Requires the extension to be installed</i>",

	"republic_analytics_no_data" =>
	"No data",

	''=>''
);
