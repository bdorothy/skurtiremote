<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (isset($this->EE) == FALSE) $this->EE =& get_instance(); // For EE 2.2.0+


$defaults = array();
$defaults['rating'] = array('name' => $this->EE->lang->line('cr:rating'), 'sortable' => 'true');
$defaults['rating_date'] = array('name' => $this->EE->lang->line('cr:rating_date'), 'sortable' => 'true');
$defaults['rating_author'] = array('name' => $this->EE->lang->line('cr:rating_author'), 'sortable' => 'true');
$defaults['rating_status'] = array('name' => $this->EE->lang->line('cr:status'), 'sortable' => 'true');

$config['cr_columns'] = array();

$config['cr_columns']['entry']['standard']['entry_title']   = array('name' => $this->EE->lang->line('cr:entry_title'), 'sortable' => 'true');
$config['cr_columns']['entry']['standard']['entry_channel']  = array('name' => $this->EE->lang->line('cr:channel'), 'sortable' => 'true');
$config['cr_columns']['entry']['standard']            	= array_merge($config['cr_columns']['entry']['standard'], $defaults);
$config['cr_columns']['entry']['extra']['entry_url_title']  = array('name' => $this->EE->lang->line('cr:entry_url_title'), 'sortable' => 'true');

$config['cr_columns']['comment_review']['standard']['entry_title']   = array('name' => $this->EE->lang->line('cr:entry_title'), 'sortable' => 'true');
$config['cr_columns']['comment_review']['standard']['entry_channel'] = array('name' => $this->EE->lang->line('cr:channel'), 'sortable' => 'true');
$config['cr_columns']['comment_review']['standard']            = array_merge($config['cr_columns']['comment_review']['standard'], $defaults);
$config['cr_columns']['comment_review']['extra']['entry_url_title']  = array('name' => $this->EE->lang->line('cr:entry_url_title'), 'sortable' => 'true');
$config['cr_columns']['comment_review']['extra']['comment']  = array('name' => $this->EE->lang->line('cr:review'), 'sortable' => 'true');

//$config['cr_columns']['comment_entry']['standard']['comment_author']   = array('name' => $this->EE->lang->line('cr:cmt_author'), 'sortable' => 'true');
$config['cr_columns']['comment_entry']['standard']['entry_title']   = array('name' => $this->EE->lang->line('cr:entry_title'), 'sortable' => 'true');
$config['cr_columns']['comment_entry']['standard']            = array_merge($config['cr_columns']['comment_entry']['standard'], $defaults);
$config['cr_columns']['comment_entry']['extra']['entry_channel'] = array('name' => $this->EE->lang->line('cr:channel'), 'sortable' => 'true');
$config['cr_columns']['comment_entry']['extra']['entry_url_title']  = array('name' => $this->EE->lang->line('cr:entry_url_title'), 'sortable' => 'true');
$config['cr_columns']['comment_entry']['extra']['comment']  = array('name' => $this->EE->lang->line('cr:comment'), 'sortable' => 'true');

$config['cr_columns']['member']['standard']['username']    = array('name' => $this->EE->lang->line('cr:username'), 'sortable' => 'true');
$config['cr_columns']['member']['standard']['screen_name'] = array('name' => $this->EE->lang->line('cr:screen_name'), 'sortable' => 'true');
$config['cr_columns']['member']['standard']['email']       = array('name' => $this->EE->lang->line('cr:email'), 'sortable' => 'true');
$config['cr_columns']['member']['standard']                = array_merge($config['cr_columns']['member']['standard'], $defaults);
$config['cr_columns']['member']['extra']['member_group']   = array('name' => $this->EE->lang->line('cr:member_group'), 'sortable' => 'true');

$config['cr_columns']['channel_images']['standard']['filename']   = array('name' => $this->EE->lang->line('cr:filename'), 'sortable' => 'true');
$config['cr_columns']['channel_images']['standard']['image_title']   = array('name' => $this->EE->lang->line('cr:image_title'), 'sortable' => 'true');
$config['cr_columns']['channel_images']['standard']            	= array_merge($config['cr_columns']['channel_images']['standard'], $defaults);
$config['cr_columns']['channel_images']['extra']['entry_channel'] = array('name' => $this->EE->lang->line('cr:channel'), 'sortable' => 'true');
$config['cr_columns']['channel_images']['extra']['entry_title']   = array('name' => $this->EE->lang->line('cr:entry_title'), 'sortable' => 'true');
$config['cr_columns']['channel_images']['extra']['entry_url_title']  = array('name' => $this->EE->lang->line('cr:entry_url_title'), 'sortable' => 'true');

$config['cr_columns']['channel_files']['standard']['filename']   = array('name' => $this->EE->lang->line('cr:filename'), 'sortable' => 'true');
$config['cr_columns']['channel_files']['standard']['file_title']   = array('name' => $this->EE->lang->line('cr:file_title'), 'sortable' => 'true');
$config['cr_columns']['channel_files']['standard']            	= array_merge($config['cr_columns']['channel_files']['standard'], $defaults);
$config['cr_columns']['channel_files']['extra']['entry_channel'] = array('name' => $this->EE->lang->line('cr:channel'), 'sortable' => 'true');
$config['cr_columns']['channel_files']['extra']['entry_title']   = array('name' => $this->EE->lang->line('cr:entry_title'), 'sortable' => 'true');
$config['cr_columns']['channel_files']['extra']['entry_url_title']  = array('name' => $this->EE->lang->line('cr:entry_url_title'), 'sortable' => 'true');

$config['cr_columns']['channel_videos']['standard']['video_title']   = array('name' => $this->EE->lang->line('cr:video_title'), 'sortable' => 'true');
$config['cr_columns']['channel_videos']['standard']['video_service']   = array('name' => $this->EE->lang->line('cr:video_service'), 'sortable' => 'true');
$config['cr_columns']['channel_videos']['standard']            	= array_merge($config['cr_columns']['channel_videos']['standard'], $defaults);
$config['cr_columns']['channel_videos']['extra']['entry_channel'] = array('name' => $this->EE->lang->line('cr:channel'), 'sortable' => 'true');
$config['cr_columns']['channel_videos']['extra']['entry_title']   = array('name' => $this->EE->lang->line('cr:entry_title'), 'sortable' => 'true');
$config['cr_columns']['channel_videos']['extra']['entry_url_title']  = array('name' => $this->EE->lang->line('cr:entry_url_title'), 'sortable' => 'true');

$config['cr_columns']['br_product']['standard']['product']   = array('name' => $this->EE->lang->line('cr:product'), 'sortable' => 'true');
$config['cr_columns']['br_product']['standard']            	= array_merge($config['cr_columns']['br_product']['standard'], $defaults);
$config['cr_columns']['br_product']['extra']['sku'] = array('name' => $this->EE->lang->line('cr:sku'), 'sortable' => 'true');




/* End of file rating_types_columns.php */
/* Location: ./system/expressionengine/third_party/channel_ratings/config/rating_types_columns.php */