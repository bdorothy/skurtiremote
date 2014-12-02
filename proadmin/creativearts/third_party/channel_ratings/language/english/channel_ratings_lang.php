<?php if (!defined('BASEPATH')) die('No direct script access allowed');

$lang = array(

// Required for MODULES page
'channel_ratings'			=>	'Channel Ratings',
'channel_ratings_module_name'=>	'Channel Ratings',
'channel_ratings_module_description'	=>	'Rate everything, powerful & flexible',

//----------------------------------------
// General
//----------------------------------------
'cr:rating'     =>	'Rating',
'cr:ratings'    =>	'Ratings',
'cr:vote'       =>	'Vote',
'cr:like'       =>	'Like',
'cr:likes'      =>	'Likes',
'cr:dislike'    =>	'Dislike',
'cr:dislikes'   =>	'Dislikes',
'cr:guest'      =>	'Guest',
'cr:yes'		=>	'Yes',
'cr:no'			=>	'No',

'cr:rating_date'   =>	'Rating Date',
'cr:rating_author' =>	'Rating Author',
'cr:like_date'     =>	'Like Date',
'cr:like_author'   =>	'Like Author',
'cr:status'        =>	'Status',
'cr:email'			=>	'Email',
'cr:ip'			=>	'IP Address',
'cr:open'		=>	'Open',
'cr:close'		=>	'Close',
'cr:closed'		=>	'Closed',

'cr:edit'		=>	'Edit',
'cr:delete'		=>	'Delete',
'cr:save'		=>	'Save',
'cr:close'		=>	'Close',

//----------------------------------------
// Rating Types
//----------------------------------------
'cr:type:entry'               => 'Entries',
'cr:type_long:entry'          => 'Channel Entries',
'cr:type:comment_review'      => 'Reviews',
'cr:type_long:comment_review' => 'Comment "Reviews"',
'cr:type:comment_entry'       => 'Comments',
'cr:type_long:comment_entry'  => 'Comment "Entries"',
'cr:type:member'              => 'Members',
'cr:type_long:member'         => 'Site Members',
'cr:type:channel_images'      => 'Images',
'cr:type_long:channel_images' => 'Channel Images',
'cr:type:channel_files'       => 'Files',
'cr:type_long:channel_files'  => 'Channel Files',
'cr:type:channel_videos'      => 'Videos',
'cr:type_long:channel_videos' => 'Channel Videos',
'cr:type:br_product'          => 'Br. Products',
'cr:type_long:br_product'     => 'BrilliantRetail Products',

//----------------------------------------
// Sidebar
//----------------------------------------
'cr:rating_type'	=>	'Rating Type',
'cr:rating_types'	=>	'Rating Types',
'cr:like_types'		=>	'Like Types',
'cr:filter_by'		=>	'Filter By',
'cr:vis_cols'		=>	'Visible Columns',
'cr:reset'			=>	'Reset',
'cr:date_from'		=>	'Date From',
'cr:date_to'		=>	'Date To',

//----------------------------------------
// Columns & Filters
//----------------------------------------
'cr:entry_title'	=>	'Entry Title',
'cr:entry_url_title'	=>	'Entry URL Title',
'cr:channel'		=>	'Channel',
'cr:review'			=>	'Review',
'cr:comment'		=>	'Comment',
'cr:cmt_author'		=>	'Comment Author',
'cr:username'		=>	'Username',
'cr:screen_name'	=>	'Screen Name',
'cr:email'			=>	'Email Address',
'cr:member_group'	=>	'Member Group',
'cr:filename'		=>	'Filename',
'cr:image_title'	=>	'Image Title',
'cr:file_title'		=>	'File Title',
'cr:video_title'	=>	'Video Title',
'cr:video_service'	=>	'Video Service',
'cr:product'		=>	'Product',
'cr:sku'			=>	'SKU',

//----------------------------------------
// Fields
//----------------------------------------
'cr:field'		=>	'Field',
'cr:fields'		=>	'Fields',
'cr:field_add'	=>	'Add Field',
'cr:field_label'=>	'Field Label',
'cr:field_name'	=>	'Field Name',
'cr:required'	=>	'Required',
'cr:actions'	=>	'Actions',
'cr:no_fields'	=>	'No fields have yet been added..',
'cr:field_add_l'=>	'Add Rating Field',

//----------------------------------------
// Collections
//----------------------------------------
'cr:collection' =>	'Collection',
'cr:collections' =>	'Collections',
'cr:coll_label'	=>	'Collection Label',
'cr:coll_name'	=>	'Collection Name',
'cr:default'	=>	'Default',
'cr:collections_add'=>	'Add Collection',
'cr:collections_add_long'=>	'Add Rating Collection',
'cr:coll_manage'=>	'Manage Collections',

//----------------------------------------
// Settings
//----------------------------------------
'cr:settings'	=>	'Settings',
'cr:bayesian_act_url'	=>	'Bayesian ACT URL',

//----------------------------------------
// Alerts
//----------------------------------------
'cr:alert:edit_rating' =>	'Edit Rating',
'cr:alert:saving'      =>	'saving...',
'cr:alert:delete'      =>	'Are you sure you want to delete? You may need to recount the stats after delete.',
















//----------------------------------------
// Maintanence
//----------------------------------------
'rating:entry'		=>	'Channel Entries',
'rating:comment_review'	=>	'Comment "Review"',
'rating:comment_entry'	=>	'Comment "Entry"',
'rating:members'	=>	'Site Members',
'rating:channel_images'	=>	'Channel Images',
'rating:channel_files'	=>	'Channel Files',
'rating:channel_videos'	=>	'Channel Videos',
'rating:br_product'		=>	'BrilliantRetail Product',
'rating:br_products'	=>	'BrilliantRetail Products',

'rating:member'		=>	'Member',
'rating:username'	=>	'Username',
'rating:screen_name'	=>	'Screen Name',
'rating:member_group'	=>	'Member Group',
'rating:title'		=>	'Title',
'rating:entry_title'=>	'Entry Title',
'rating:channel'	=>	'Channel',
'rating:comment'	=>	'Comment',
'rating:filename'	=>	'Filename',
'rating:image_title'=>	'Image Title',
'rating:file_title'	=>	'File Title',
'rating:video_title'=>	'Video Title',
'rating:product'	=>	'Product',
'rating:sku'		=>	'SKU',
'rating:price'		=>	'Price',
'rating:avg_rating'	=>	'Avg. Rating',
'rating:date'		=>	'Date',
'rating:date_from'	=>	'Date From',
'rating:date_to'	=>	'Date To',
'rating:'	=>	'',

//----------------------------------------
// Import
//----------------------------------------
'rating:import'		=>	'Import',
'rating:import:ss_rating'	=>	'Solspace Rating Module',
'rating:import:totals'	=>	'Totals',
'rating:import:ratings'	=>	'Ratings',
'rating:import:fields'	=>	'Rating Fields',
'rating:import:reviews'	=>	'Reviews',
'rating:import:action:fields'		=>	'Click here to import all rating fields.',
'rating:import:action:fields_exp'	=>	'Only rating fields of type \'number\' will be imported.',
'rating:import:action:ratings'		=>	'Import ratings now!',
'rating:import:action:ratings_exp'	=>	'Only ratings with an \'open\' status will be imported, quarantined ratings will be ignored',
'rating:importing'		=>	'Importing please wait, this can take a while.',
'rating:importing_done'	=>	'Done importing!',

//----------------------------------------
// Recount
//----------------------------------------
'rating:recount'		=>	'Recount',
'rating:recount:start'	=>	'Start the recounting process.',

//----------------------------------------
// Submission Errors
//----------------------------------------
'rating:error:missing_data'			=>	'Missing Data.',
'rating:error:not_authorized'		=>	'You are not authorized to perform this action',
'rating:error:captcha_required'		=>	'You must submit the word that appears in the image',
'rating:error:captcha_incorrect'	=>	'You did not submit the word exactly as it appears in the image',
'rating:error:duplicate_rating'		=>	'You have already rated this entry.',
'rating:error:duplicate_like'		=>	'You have already reviewed this entry.',
'rating:error:missing_rating_input'	=>	'No Rating form field found!',
'rating:error:field_notfound'		=>	'Rating field not found',
'rating:error:required_field'		=>	'Missing REQUIRED rating field: ',
'rating:error:rating_required'		=>	'A rating is required',
'rating:error:invalid_rating_format'=>	'Only NUMBERS are allowed as a rating: ',
'rating:error:out_of_range'			=>	'RATING is out of the allowed range: ',
'rating:success:new_like'			=>	'Thank you! Your review has been recorded',
'rating:success:vote_deleted'		=>	'Successfully removed your vote!',


// END
''=>''
);

/* End of file channel_ratings_lang.php */
/* Location: ./system/expressionengine/third_party/channel_ratings/channel_ratings_lang.php */