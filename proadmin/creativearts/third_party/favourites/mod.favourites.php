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

class Favourites {
	
	public $return_data;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
	$this->EE =& get_instance();
	}
	
	
	// Security check for not processing any request without post
	public function securitycheck(){
	if(isset($_POST['EID']) && $_POST['EID'] != ""){
	return 'pass';
	};
	}
	
	
	function saved(){	
	$entry_id = ee()->TMPL->fetch_param('entry_id');	
	ee()->db->where('entry_id', $entry_id);
	ee()->db->where('member_id',ee()->session->userdata('member_id'));
	$count = ee()->db->count_all_results('exp_favourites');
	if($count >= 1){
	return $count = "y";
	}else{
	return $count = "n";
	}
	}
	
	function limitcheck(){
	ee()->db->where('member_id',ee()->session->userdata('member_id'));
	$count = ee()->db->count_all_results('exp_favourites');
	return $count;
	}
	
	/*
	// insert favourite
	public function favourite(){
	$data = array(
		'entry_id' 					=> ee()->input->post('EID'),
		'member_id' 				=> ee()->session->userdata('member_id'),
		'date'						=> ee()->localize->now,
		);
	ee()->db->insert('exp_favourites', $data);
	return;
	}
	
	// unfavourite
	public function unfavourite(){
	$entry_id = ee()->input->post('EID');
	ee()->db->delete('exp_favourites', array('entry_id' => $entry_id)); 
	return;
	}
	*/
	
	
	
	public function favourite(){
	
	$entry_id = ee()->input->post('EID');
	ee()->db->where('member_id',ee()->session->userdata('member_id'));
	ee()->db->where('entry_id',$entry_id);
	$count = ee()->db->count_all_results('exp_favourites');
	
	if($count > 0){
	// unfavourite it
	ee()->db->delete('exp_favourites', array('entry_id' => $entry_id)); 
	}else{
	// add to favourite
	$data = array(
		'entry_id' 					=> ee()->input->post('EID'),
		'member_id' 				=> ee()->session->userdata('member_id'),
		'date'						=> ee()->localize->now,
		);
	ee()->db->insert('exp_favourites', $data);
	ee()->functions->redirect(current_url(), 'refresh');
	}
	
	}
	
	
	public function myfavourites(){
	// get all the favourites for this member
	$member_id = ee()->session->userdata('member_id');
	
	$query = ee()->db->query('select GROUP_CONCAT(entry_id SEPARATOR "|") as ids from ls_favourites where member_id = '.$member_id.'');
	if ($query->num_rows() > 0){	
	$row = $query->row();
	$ids = $row->ids;
	}
	return $ids;
	}
	
	public function myfavourites_count(){
	// get all the favourites for this member
	$member_id = ee()->session->userdata('member_id');
	ee()->db->where('member_id',$member_id);
	$count = ee()->db->count_all_results('exp_favourites');
	return $count;
	}
	
	
	
	public function favouriteform(){
	
	 // Find the entry_id of the teacher to add the form for
    $entry_id = $this->EE->TMPL->fetch_param('entry_id');
	$return	  = $this->EE->TMPL->fetch_param('return');
    if( $entry_id === FALSE ) {
        return "";
    }
	// Build an array to hold the form's hidden fields
    $hidden_fields = array(
        "EID" => $entry_id,
		"ACT" => $this->EE->functions->fetch_action_id( 'favourites', 'favourite_process' ),
		"RET" => $return
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
	
	
	public function favourite_process(){
	$entry_id 	= $this->EE->input->post("EID", TRUE);	
	$return 	= $this->EE->input->post("RET", TRUE);	
	$member_id = $this->EE->session->userdata('member_id');  
	
	// DO NOT ALLOW NON LOGGED IN USERS TO ADD TO FAVOURITES
	if( $member_id == 0 ) {
	/*$ret = $this->EE->functions->fetch_site_index().'login';
	$data = array(
    'title' => 'Please login to add teachers to favourites',
    'heading' => 'Please login to add teachers to favourites',
    'content' => "Adding teachers to favourites requires you to be logged in.",
    'link' => array($ret, "Go to Login Page")
	);
	$this->EE->output->show_message($data); 
	*/
	ee()->functions->redirect($this->EE->functions->fetch_site_index().'login/user','refresh');
    }
	
	// PROCESS THE FAVORITE REQUEST
	ee()->db->where('member_id',$member_id);
	ee()->db->where('entry_id',$entry_id);
	$count = ee()->db->count_all_results('exp_favourites');
	
	if($count > 0){
	// unfavourite it
	ee()->db->delete('exp_favourites', array('entry_id' => $entry_id)); 
	}else{
	// add to favourite
	$data = array(
		'entry_id' 					=> ee()->input->post('EID'),
		'member_id' 				=> ee()->session->userdata('member_id'),
		'date'						=> ee()->localize->now,
		);
	ee()->db->insert('exp_favourites', $data);	
	}	
    ee()->functions->redirect($return, 'refresh');
	}

	
	
	
}
/* End of file mod.favourites.php */
/* Location: /system/expressionengine/third_party/contacts/mod.favourites.php */