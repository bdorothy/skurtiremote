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

class Mtt_favourites {
	
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
	$count = ee()->db->count_all_results('exp_mtt_favourites');
	if($count >= 1){
	return $count = "y";
	}else{
	return $count = "n";
	}
	}
	
	function limitcheck(){
	ee()->db->where('member_id',ee()->session->userdata('member_id'));
	$count = ee()->db->count_all_results('exp_mtt_favourites');
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
	ee()->db->insert('exp_mtt_favourites', $data);
	return;
	}
	
	// unfavourite
	public function unfavourite(){
	$entry_id = ee()->input->post('EID');
	ee()->db->delete('exp_mtt_favourites', array('entry_id' => $entry_id)); 
	return;
	}
	*/
	
	
	
	public function favourite(){
	
	$entry_id = ee()->input->post('EID');
	ee()->db->where('member_id',ee()->session->userdata('member_id'));
	ee()->db->where('entry_id',$entry_id);
	$count = ee()->db->count_all_results('exp_mtt_favourites');
	
	if($count > 0){
	// unfavourite it
	ee()->db->delete('exp_mtt_favourites', array('entry_id' => $entry_id)); 
	}else{
	// add to favourite
	$data = array(
		'entry_id' 					=> ee()->input->post('EID'),
		'member_id' 				=> ee()->session->userdata('member_id'),
		'date'						=> ee()->localize->now,
		);
	ee()->db->insert('exp_mtt_favourites', $data);
	ee()->functions->redirect(current_url(), 'refresh');
	}
	
	}
	
	
	public function myfavourites(){
	// get all the favourites for this member
	$member_id = ee()->session->userdata('member_id');
	
	$query = ee()->db->query('select GROUP_CONCAT(entry_id SEPARATOR "|") as ids from ls_mtt_favourites where member_id = '.$member_id.'');
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
	$count = ee()->db->count_all_results('exp_mtt_favourites');
	return $count;
	}
	
	
	// the form
	public function form(){
	
	 // Find the entry_id of the teacher to add the form for
    $entry_id = $this->EE->TMPL->fetch_param('entry_id');
	$return	  = $this->EE->TMPL->fetch_param('return');
    if( $entry_id === FALSE ) {
        return "";
    }
	// Build an array to hold the form's hidden fields
    $hidden_fields = array(
        "EID" => $entry_id,
		"ACT" => $this->EE->functions->fetch_action_id( 'Mtt_favourites', 'favourite_process' ),
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
	
	
	
	// the form
	public function set_comparelist_form(){
	// Find the entry_id of the teacher to add the form for
  	$return	  = $this->EE->TMPL->fetch_param('return');
   
	// Build an array to hold the form's hidden fields
    $hidden_fields = array(
		"ACT" => $this->EE->functions->fetch_action_id( 'Mtt_favourites', 'set_comparelist' ),
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
	
	
	public function set_comparelist(){	
	$member_id = ee()->session->userdata('member_id');
	$return 	= $this->EE->input->post("RET", TRUE);
	$addtocompare = $this->EE->input->post("addtocompare");
	$addtocompare = implode('|',$addtocompare);
	/*$data = array(
	'member_id' => $member_id,
	'comparelist' => $addtocompare
	);
	*/
	//ee()->cache->save('comparedteachers', $data,500, Cache::GLOBAL_SCOPE);
	// 	set cookies
	//	print_r( $addtocompare);
	//	$this->EE->functions->set_cookie('entry_id', $addtocompare, 3600);
	//	print_r( $addtocompare);
	// if no active session we start a new one
	if (session_id() == "") 
	{
	session_start(); 
	}
	$_SESSION['comparelist'] = $addtocompare;	
	}
	
	
	public function get_comparelist()	{
	$entry_exists = 'n';
	//return $this->EE->input->cookie('entry_id');
	// if no active session we start a new one
	if (session_id() == "") 
	{
	session_start(); 
	}
	// saved teachers in this session
	if (isset($_SESSION['comparelist']))
	{
	
	$c_entries = $_SESSION['comparelist'];
	if($c_entries != ""){
	$entry_exists = 'y';
	}
	$vars = array(
	'centries' => $c_entries,
	'entry_exists' => $entry_exists
	);
	
	
	return $this->EE->TMPL->parse_variables_row($this->EE->TMPL->tagdata, $vars);
	}
	// nothing saved earlier then	
	else
	{
	return '';
	}
	
	
	
	}// End of retrieve function
	
	
	
	public function compare_cookie_exists(){
	$entry_id 	= $this->EE->TMPL->fetch_param('entry_id');
	$data = '';
	if (session_id() == "") 
	{
	session_start(); 
	}
	if (isset($_SESSION['comparelist'])){
	$data = $_SESSION['comparelist'];
	};
	$cookies = explode('|',$data);
	
	if (in_array($entry_id, $cookies)) {
	
    return "checked";
	}
	}
	
	public function compare_cookie_count(){
	if (session_id() == ""){
	session_start(); 
	}
	if (isset($_SESSION['comparelist'])){
	$cookies = $_SESSION['comparelist'];
	$cookies = explode('|',$cookies);
	return sizeof($cookies);
	}else{return '';}	

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
	
	//ee()->functions->redirect($this->EE->functions->fetch_site_index().'login/user','refresh');
	return $this->EE->output->send_ajax_response(array('error'=>True));
    }
	
	// PROCESS THE FAVORITE REQUEST
	ee()->db->where('member_id',$member_id);
	ee()->db->where('entry_id',$entry_id);
	$count = ee()->db->count_all_results('exp_mtt_favourites');
	
	if($count > 0){
	// unfavourite it
	ee()->db->delete('exp_mtt_favourites', array('entry_id' => $entry_id,'member_id' => $member_id)); 
	
	// now unset this entry id from the session
	if (session_id() == ""){
	session_start(); 
	}	
	if (isset($_SESSION['comparelist'])){
	$cookies = $_SESSION['comparelist'];
	$cookies = explode('|',$cookies);
	if(($key = array_search($entry_id, $cookies)) !== false) {
	unset($cookies[$key]);
	$cookies = implode('|',$cookies);
	$_SESSION['comparelist'] = $cookies;
	}
	}
	
	}else{
	// add to favourite
	$data = array(
		'entry_id' 					=> ee()->input->post('EID'),
		'member_id' 				=> ee()->session->userdata('member_id'),
		'date'						=> ee()->localize->now,
		);
	ee()->db->insert('exp_mtt_favourites', $data);		
	}	
	
	// NOW DELETE ONE FAVOURITE THAT IS OLDEST AND MORE THAN 6
	ee()->db->where('member_id',$member_id);
	$count = ee()->db->count_all_results('exp_mtt_favourites');
	if($count > 6){
	ee()->db->query('
	DELETE FROM ls_mtt_favourites WHERE entry_id NOT IN ( 
	SELECT entry_id 
	FROM ( 
    SELECT entry_id 
    FROM ls_mtt_favourites 
    where member_id = '.$member_id.'
    ORDER BY date DESC 
    LIMIT 6
	) x 
	);'); 
	// deleted
	}
	return $this->EE->output->send_ajax_response(array('success'=>True));
   // ee()->functions->redirect($return, 'refresh');
	}

	
	
	
}
/* End of file mod.Mtt_contacts.php */
/* Location: /system/expressionengine/third_party/Mtt_contacts/mod.Mtt_contacts.php */