<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Mtt Search language file
 *
 * @package        mtt_search
 * @author         MyTopTeacher <contact@mytopteacher.com>
 * @link           http://mytopteacher.com/addons/mtt-search
 * @copyright      Copyright (c) 2014, Mtt
 */

$lang = array(

//----------------------------------------
// Required for MODULES page
//----------------------------------------

"mtt_search_module_name" =>
"Mtt Search",

"mtt_search_module_description" =>
"Powerful site search and Find &amp; Replace utility",

//----------------------------------------
// Module home page
//----------------------------------------

"site_search" =>
"Site Search",

"settings" =>
"Settings",

"build_index_url" =>
"Build index URL",

"edit_settings" =>
"Edit Settings",

"no_collections_exist" =>
"No collections exist",

"manage_collections" =>
"Manage Collections",

"create_new_collection" =>
"Create New Collection",

"view_search_log" =>
"View Search Log",

"new_collection" =>
"New Collection",

"collection_label" =>
"Collection Label",

"collection_label_notes" =>
"Full name of the search collection.",

"collection_name" =>
"Collection Name",

"collection_name_notes" =>
"Short name of the search collection: single word, no spaces, underscores and dashes allowed.",

"edit_preferences" =>
"Edit Preferences",

"search_index" =>
"Search Index",

"build_index" =>
"Build Index",

"update_index" =>
"Update Index",

"rebuild_index" =>
"Rebuild Index",

"index_status_empty" =>
"Collection is new. You can now build the index.",

"index_status_old" =>
"Index does not appear to be up to date. Please rebuild.",

"build_all_indexes" =>
"Build all indexes",

"no_entries" =>
"No entries in this collection.",

"total_entries" =>
"Total entries",

//----------------------------------------
// Edit collection page - fields
//----------------------------------------

"collections" =>
"Collections",

"edit_collection" =>
"Edit Collection",

"collection_modifier" =>
"Modifier",

"collection_modifier_notes" =>
"For keyword searches, relevance scores in this collection will be multiplied by the modifier. From 0.5 to 10.",

"title" =>
"Title",

"field" =>
"Field",

"weight" =>
"Weight",

"excerpt" =>
"Excerpt",

"delete_collection_confirm" =>
"Delete Collection",

"delete_collection_confirm_message" =>
"Are you sure you want to delete this collection? <strong>It cannot be undone!</strong>",

"cancel_go_back" =>
"Cancel and go back",

"collection_deleted" =>
"Collection deleted",

"no_searchable_channels_found" =>
"No channels with searchable fields found",

//----------------------------------------
// Shortcuts
//----------------------------------------

"groups" =>
"Shortcut groups",

"group" =>
"Group",

"shortcuts" =>
"Shortcuts",

"new_group" =>
"New shortcut group",

"no_groups_exist" =>
"No shortcut groups exist",

"group_label" =>
"Group label",

"edit_group" =>
"Edit group",

"new_shortcut" =>
"New shortcut",

"edit_shortcut" =>
"Edit shortcut",

"shortcut_label" =>
"Shortcut label",

"shortcut_name" =>
"Shortcut name",

"no_shortcuts_in_group" =>
"No shortcuts in this group",

"add_parameter" =>
"Add parameter",

"delete_group_confirm" =>
"Delete Shortcut Group",

"delete_group_confirm_message" =>
"Are you sure you want to delete this shortcut group? <strong>All shortcuts it contains will be deleted, too!</strong>",

"group_deleted" =>
"Shortcut group deleted",

"delete_shortcut_confirm" =>
"Delete Shortcut",

"delete_shortcut_confirm_message" =>
"Are you sure you want to delete this shortcut? <strong>This action cannot be undone!</strong>",

"shortcut_deleted" =>
"Shortcut deleted",

//----------------------------------------
// Shortcut validation
//----------------------------------------

"shortcut_invalid_group" =>
"Invalid group ID given.",

"shortcut_invalid_params" =>
"Parameters are invalid.",

"shortcut_no_params" =>
"No parameters given.",

"shortcut_name_not_available" =>
"Name is not available.",

"shortcut_invalid_name" =>
"Name is invalid. Use alphanumeric characters only.",

"shortcut_no_name" =>
"No name given.",

//----------------------------------------
// Find & Replace
//----------------------------------------

"find_replace" =>
"Find &amp; Replace",

"channels" =>
"Channels",

"categories" =>
"Categories",

"select_all" =>
"Select all",

"find" =>
"Find",

"show_preview" =>
"Show preview",

"replace" =>
"Replace with",

"replace_selected" =>
"Replace selected",

"matching_entries_for" =>
"Matching entries for",

"no_matching_entries_found" =>
"No matching entries found.",

"replaced_x_with_y" =>
"Replaced “<strong>%s</strong>” with “<strong>%s</strong>”",

"in_1_entry" =>
"in one entry",

"in_n_entries" =>
"in <strong>%s</strong> entries",

"clear_replace_log" =>
"Clear Replace Log",

"view_replace_log" =>
"View Replace Log",

"replace_log" =>
"Replace Log",

"replace_date" =>
"Date",

"affected_entries" =>
"Affected&nbsp;entries",

"replace_log_is_empty" =>
"Replace log is empty",

//----------------------------------------
// View Log
//----------------------------------------

"search_log" =>
"Search Log",

"filter_search_log" =>
"Filter search log",

"filter" =>
"Filter",

"clear_search_log" =>
"Clear Search Log",

"export_search_log" =>
"Export Search Log",

"search_log_is_empty" =>
"Search Log is empty",

"keywords" =>
"Keywords",

"parameters" =>
"Parameters",

"show_parameters" =>
"Show parameters",

"collection" =>
"Collection",

"search_mode" =>
"Search mode",

"loose_ends" =>
"Loose ends",

"result_page" =>
"Result page",

"search_date" =>
"Search date",

"member" =>
"Member",

"viewing_rows" =>
"Viewing rows %s to %s of %s rows in total.",

"create_shortcut_from_log" =>
"Create shortcut from this query",

//----------------------------------------
// Extension settings
//----------------------------------------

"license_key" =>
"License Key",

"license_key_help" =>
"Enter the license key you obtained from mytopteacher.com or devot-ee.com.",

"encode_query" =>
"Encode query",

"encode_query_help" =>
"Choose to either encode the query in the URI or use GET variables.",

"default_result_page" =>
"Default result page",

"default_result_page_help" =>
"If a <code>result_page</code> is not explicitly given, the search will fall back to this result page.",

"min_word_length" =>
"Minimum word length",

"min_word_length_help" =>
"The <code>ft_min_word_len</code> setting of your MySQL installation. This indicates the minimum length of words indexed by the FULLTEXT index.
If you&rsquo;re not sure, leave it on the default value of <strong>4</strong>.",

"excerpt_length" =>
"Excerpt length",

"excerpt_length_help" =>
"Maximum amount of words that the search excerpt will contain.",

"excerpt_hilite" =>
"Highlight keywords",

"excerpt_hilite_help" =>
"Select which tag to use when highlighting keywords in the search excerpt.",

"do_not_hilite" =>
"Do not highlight keywords",

"use_hilite_tag" =>
"Use %s tag",

"title_hilite" =>
"Highlight keywords in title",

"batch_size" =>
"Batch size",

"batch_size_help" =>
"Building a collection index via the Control Panel happens in batches. Enter the number of entries you want to process in one batch.",

"default_search_mode" =>
"Default search mode",

"default_search_mode_help" =>
"If a <code>search_mode</code> is not explicitly given, the search will fall back to this mode.",

"all_mode" =>
"All words",

"any_mode" =>
"Any word",

"exact_mode" =>
"Exact phrase",

"auto_mode" =>
"Automatic",

"search_log_size" =>
"Search Log size",

"search_log_size_help" =>
"Maximum number of searches to keep in the Search Log. Set to <code>0</code> to disable logging.",

"stop_words" =>
"Stop Words",

"stop_words_help" =>
"By default, MySQL keeps <a href=\"http://dev.mysql.com/doc/refman/5.1/en/fulltext-stopwords.html\">a list of stop words</a>,
which are ignored in the FULLTEXT index. An alternative search method is triggered when the search query contains one or more
of these words.",

"ignore_words" =>
"Ignore Words",

"ignore_words_help" =>
"Words to automatically filter out of the given keywords for non-exact searches, e.g.: <em>the</em>",

"member_group" =>
"Member group",

"can_manage" =>
"Can manage collections",

"can_manage_shortcuts" =>
"Can manage shortcuts",

"can_replace" =>
"Can find &amp; replace",

"can_view_search_log" =>
"Can view search log",

"can_view_replace_log" =>
"Can view replace log",

//----------------------------------------
// Feedback messages
//----------------------------------------

"changes_saved" =>
"Changes saved",

"collection_missing" =>
"No collection given",

"keywords_missing" =>
"No keywords given",

"channel_cannot_be_empty" =>
"Please choose a channel for your collection.",

"collection_name_cannot_be_empty" =>
"Collection Short Name cannot be empty.",

"collection_name_has_wrong_chars" =>
"Collection Short Name can only contain alphanumeric characters, dashes and/or underscores.",

"collection_name_exists" =>
"This Collection Short Name already exists. Please choose a unique Short Name.",

"deleting" =>
"Deleting...",

"optimizing" =>
"Optimizing index...",

"working" =>
"Working...",

"done" =>
"Done",

"no_keywords_given" =>
"No keywords given",

"no_fields_selected" =>
"No channel fields selected",

"no_entries_selected" =>
"No entries selected",

/* END */
''=>''
);
