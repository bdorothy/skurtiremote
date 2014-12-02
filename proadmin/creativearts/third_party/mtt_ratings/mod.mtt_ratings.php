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
 * MTT Ratings Module Front End File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Raj Sadh
 * @link		http://www.sixth.co.in
 */

class Mtt_ratings {
	
	public $return_data;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
	$this->EE =& get_instance();
	ee()->load->library('typography');
    ee()->typography->initialize();
    ee()->typography->parse_images = TRUE;
    ee()->typography->allow_headings = FALSE;
	}
	
	
	public function logged_in_is_author(){				
		$entry_id = ee()->TMPL->fetch_param('entry_id');		
		$logged_in_id = ee()->session->userdata('member_id');
		$author_id = 0;
		// get the author_id of this entry
		ee()->db->select('author_id');
		ee()->db->where('entry_id',$entry_id);
		$query = ee()->db->get('exp_channel_titles');
		if ($query->num_rows() > 0){	
		$row = $query->row();
		$author_id = $row->author_id;	
		}
			if($author_id == $logged_in_id){
			return 'y';
			}else{
			return 'n';
			}	
	}
	
	// checks to see if the teacher / institute has already responded to this comment?
	// find a comment_id where parent is = comment_id
	public function responded(){
	$comment_id = ee()->TMPL->fetch_param('comment_id');
	ee()->db->where('parent_id',$comment_id);
	$count = ee()->db->count_all_results('exp_mtt_ratings');
	if($count > 0){return 'y';}
	else{return 'n';}	
	}
	
	
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
	
	
	public function ratings(){
	// Find the ratings for this comment
	$comment_id = ee()->TMPL->fetch_param('comment_id');	
	ee()->db->select('knowledge,communication,attention,patience,fees,amount,session,anonymous');
	ee()->db->where('comment_id',$comment_id);
	//ee()->db->where('parent_id',0);
	
	$query = ee()->db->get('mtt_ratings');
	
	if ($query->num_rows() > 0){
	
	$row = $query->row();
	
	$variables[] = array(
		'knowledge' => $row->knowledge,
		'communication' => $row->communication,
		'attention' => $row->attention,
		'patience' => $row->patience,
		'fees' => $row->fees,
		'amount' => $row->amount,
		'session' => $row->session,
		'anonymous' => $row->anonymous,
		'response'	=> 'n'
	);
	
	return ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $variables);
	}
	
	}
	// ----------------------------------------------------------------
	public function total_ratings(){
	$entry_id = ee()->TMPL->fetch_param('entry_id');
	ee()->db->where('entry_id',$entry_id);
	$count = ee()->db->count_all_results('exp_mtt_ratings');
	return $count;
	}
	// ----------------------------------------------------------------
	public function score($entry_id = "", $parameter = ""){
	// get the score for all ratings for this teacher
	$entry_id = ee()->TMPL->fetch_param('entry_id');
	$parameter = ee()->TMPL->fetch_param('type');
	if($parameter == "overall"){
	$parameters = array('knowledge','attention','fees','patience','communication');
	foreach($parameters as $parameter){
	$overall[$parameter] = $this->get_score($entry_id,$parameter);
	//return $overall;
	}
	// for only institutional, teachers fees is 0
	if ($overall['fees'] == 0){
	return number_format(round((array_sum($overall)/4),1),1);
	}
	// else other teachers
	else{
	return number_format(round((array_sum($overall)/5),1),1);
	}
	}
	return $this->get_score($entry_id,$parameter);
	}
	
	public function get_score($entry_id,$parameter){
	$query = ee()->db->query("Select AVG($parameter) as $parameter from exp_mtt_ratings where entry_id = $entry_id AND $parameter != '0' AND 'is_resonse' != 'y'");
	if ($query->num_rows() > 0){	
	$row = $query->row();
		//print_r($row);
	$parameter = round($row->$parameter,1);
	return number_format($parameter,1);
	}
	}
	
	
	public function standard(){
	$entry_id = ee()->TMPL->fetch_param('entry_id');
	$parameter = ee()->TMPL->fetch_param('type');
	$overall = $this->score($entry_id,$parameter);
	$standard = "";
	if($overall == ""){
	return;
	}else{
	
	switch ($overall) {    
	case ($overall > 0 && $overall <= 1.49):
        $standard =  "Needs Improvement";
        break;
    case ($overall > 1.49 && $overall <= 2.49):
        $standard =  "Below Expectations";
        break;
    case ($overall > 2.49 && $overall <= 3.49):
         $standard =  "Average";
        break;
    case ($overall > 3.49 && $overall <= 4.49):
         $standard =  "Good";
        break;
    case ($overall > 4.49 && $overall <= 5):
         $standard =  "Great";
        break;
	}
	return $this->return_data = $standard;
	
	}
	}
	
	
	//********************************************************
	// the form
	public function responseform(){	
	// Find the entry_id of the teacher to add the form for
   	$return	  		= $this->EE->TMPL->fetch_param('return');
	$entry_id	  	= $this->EE->TMPL->fetch_param('entry_id'); 
	$parent_id	  	= $this->EE->TMPL->fetch_param('parent_id'); // id of original comment ..responded for !
  
	// Build an array to hold the form's hidden fields
    $hidden_fields = array(
		"PID" => $parent_id,
		"entry_id" => $entry_id,
       	"ACT" => $this->EE->functions->fetch_action_id( 'Mtt_ratings', 'response_process' ),
		"RET" => $return,
		"URI" => (ee()->uri->uri_string == '') ? 'index' : ee()->uri->uri_string,
    );
	// Build an array with the form data
    $form_data = array(
        "id" => $this->EE->TMPL->form_id,
        "class" => $this->EE->TMPL->form_class,
        "hidden_fields" => $hidden_fields
    );

    // Fetch contents of the tag pair, ie, the form contents
    $tagdata = $this->EE->TMPL->tagdata;

    $form = $this->EE->functions->form_declaration($form_data) . 
        $tagdata . "</form>";
    return $form;	
	}
	//********************************************************
	
	
	
	//********************************************************
	public function response_process(){	
	// Grab the comment now
	$comment 	= $this->EE->input->post("comment", TRUE);
		
	// if no comment is punched in : just throw an error now JSON 
	if ($comment == ""){ 
	return $this->EE->output->send_ajax_response(array('error' => true, 'field_errors' => 'Comment is required'));
	}
	// load comment class and assign it to a variable here : $comment
	if ( ! class_exists('Comment'))
    {
    require_once PATH_MOD.'comment/mod.comment.php';
    }
    $comment = new Comment(); 
	foreach(get_object_vars($this) as $key => $value)	{
	$comment->{$key} = $value;
	}
	$comment->insert_new_comment();
	}
	//********************************************************
	
	//********************************************************
	public function entries(){
	
	if ( ! class_exists('Comment'))
    {
    require_once PATH_MOD.'comment/mod.comment.php';
    }

	$comment = new Comment();
	$comment->entries(); 
	}
	
	
}
/* End of file mod.mtt_ratings.php */
/* Location: /system/expressionengine/third_party/mtt_ratings/mod.mtt_ratings.php */