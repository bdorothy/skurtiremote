<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * LS Ratings Module Front End File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Raj Sadh
 * @link		http://www.sixth.co.in
 */

class Ls_ratings {
	
	public $return_data;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
	$this->EE =& get_instance();
	ee()->load->library('typography');
    ee()->typography->initialize();
  	}
	
	
	
	// Checks to see if already rated?
	public function already_rated(){
	$entry_id = ee()->TMPL->fetch_param('entry_id');
	$is_author = $this->logged_in_is_author($entry_id);
	$member_id = ee()->session->userdata('member_id');
	// search if this non - author has already rated this teacher
	if ($is_author == "n"){
	ee()->db->where('entry_id',$entry_id);
	ee()->db->where('author_id',$member_id);
	$count = ee()->db->count_all_results('exp_comments');	
	if($count > 0){
		return 'y';
	}else{return 'n';}
	}
	}
	
	
	public function score(){
	// Find the ratings for this comment
	$comment_id = ee()->TMPL->fetch_param('comment_id');	
	ee()->db->select('rating');
	ee()->db->where('comment_id',$comment_id);		
	$query = ee()->db->get('exp_ls_ratings');
	if ($query->num_rows() > 0){	
	$row = $query->row();	
	return $row->score;
	}else{
	return 'No Ratings';
	}
	
	}
	
	// ----------------------------------------------------------------
	public function total_ratings(){
	$entry_id = ee()->TMPL->fetch_param('entry_id');
	ee()->db->where('entry_id',$entry_id);
	$count = ee()->db->count_all_results('exp_mtt_ratings');
	return $count;
	}
	

	
	
	public function avg_score(){
	$entry_id = ee()->TMPL->fetch_param('entry_id');

	$query = ee()->db->query("Select AVG(rating) as rating from exp_ls_ratings where entry_id = ".$entry_id." AND rating != '0' AND rating != 'NULL' GROUP BY entry_id");
	
	if ($query->num_rows() > 0)	{
	$row = $query->row();
	$parameter = round($row->rating,1);
	return number_format(rating,1);
	}	
	else{
	return 'No Ratings';
	}
	}
	
	
}
/* End of file mod.mtt_ratings.php */
/* Location: /system/expressionengine/third_party/mtt_ratings/mod.mtt_ratings.php */