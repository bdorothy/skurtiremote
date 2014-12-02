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
 * MTT Ratings Extension
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Extension
 * @author		Raj Sadh
 * @link		http://www.sixth.co.in
 */

class Ls_ratings_ext {

	
	public $settings 		= array();
	public $description		= 'Ratings Extension';
	public $docs_url		= 'http://www.sixth.co.in';
	public $name			= 'LS Ratings';
	public $settings_exist	= 'n';
	public $version			= '3.2';
	
	
	private $EE;
	
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct($settings = '')
	{
		$this->EE =& get_instance();
		$this->settings = $settings;
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Settings Form
	 *
	 * If you wish for ExpressionEngine to automatically create your settings
	 * page, work in this method.  If you wish to have fine-grained control
	 * over your form, use the settings_form() and save_settings() methods 
	 * instead, and delete this one.
	 *
	 * @see http://expressionengine.com/user_guide/development/extensions.html#settings
	 */
	public function settings()
	{
		return array(
			
		);
		
		
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Activate Extension
	 *
	 * This function enters the extension into the exp_extensions table
	 *
	 * @see http://codeigniter.com/user_guide/database/index.html for
	 * more information on the db class.
	 *
	 * @return void
	 */
	public function activate_extension()
	{
		// Setup custom settings in this array.
		$this->settings = array();
		
		$data = array(
			'class'		=> __CLASS__,
			'method'	=> 'insert_comment_end',
			'hook'		=> 'insert_comment_end',
			'settings'	=> serialize($this->settings),
			'version'	=> $this->version,
			'enabled'	=> 'y'
		);

		$this->EE->db->insert('extensions', $data);			
		
	}	

	// ----------------------------------------------------------------------
	
	/**
	 * insert_comment_end
	 *
	 * @param 
	 * @return 
	 */
	public function insert_comment_end($data, $comment_moderate, $comment_id)
	{		
	$entry_id = $data['entry_id'];
	// Get all the parameters of rating
	$rating = ee()->input->post('rating');
	
	$data = array(
	'entry_id'					=> $entry_id,
	'comment_id' 				=> $comment_id,
	'rating'					=> $rating,	
	);
	ee()->db->insert('ls_ratings', $data); 
	$this->update_ratings_stats($entry_id);
	
	// do not throw ajax for rating box
	if($parent_id != 0){
	return $this->EE->output->send_ajax_response(array('success' => true,
	'field_errors' => 'Thanks for your reviews. We shall check and update it very soon.'));
	}
	}
	
	
	
	
	ee()->db->where('entry_id',$entry_id);
	if (ee()->db->count_all_results('exp_ls_ratings_stats') == 0) {
    $query = ee()->db->insert('exp_ls_ratings_stats', $records);
    } else {
      // A record does exist, update it.
    $query = ee()->db->update('exp_ls_ratings_stats', $records, array('entry_id'=>$entry_id));
    }

	
	// NO ALSO UPDATE THE CUSTOM FIELD OVERALL RATINGS FOR THIS ENTRY ID
	$cfrecords = array(
	'entry_id'					=> $entry_id,
	'field_id_28'				=> $records['overall_ratings'],
	'field_id_29'				=> $records['total_ratings'],
	);
	$query = ee()->db->update('exp_channel_data', $cfrecords, array('entry_id'=>$entry_id));
	ee()->db->flush_cache();
	}
	
	
	public function overall_ratings($entry_id){
	// get the score for all ratings for this teacher
	$parameter = 'overall';
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
	$query = ee()->db->query("
	Select AVG($parameter) as $parameter from exp_mtt_ratings where entry_id = $entry_id AND $parameter != '0' 
	AND 'parent_id' = 0	");
	if ($query->num_rows() > 0){	
	$row = $query->row();
		//print_r($row);
	$parameter = round($row->$parameter,1);
	return number_format($parameter,1);
	}
	}
	
	
		
	public function total_ratings($entry_id){
	ee()->db->where('entry_id',$entry_id);
	$count = ee()->db->count_all_results('exp_mtt_ratings');
	return $count;
	}

	// ----------------------------------------------------------------------

	/**
	 * Disable Extension
	 *
	 * This method removes information from the exp_extensions table
	 *
	 * @return void
	 */
	function disable_extension()
	{
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
	}

	// ----------------------------------------------------------------------

	/**
	 * Update Extension
	 *
	 * This function performs any necessary db updates when the extension
	 * page is visited
	 *
	 * @return 	mixed	void on update / false if none
	 */
	function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
	}	
	
	// ----------------------------------------------------------------------
}

/* End of file ext.mtt_ratings.php */
/* Location: /system/expressionengine/third_party/mtt_ratings/ext.mtt_ratings.php */