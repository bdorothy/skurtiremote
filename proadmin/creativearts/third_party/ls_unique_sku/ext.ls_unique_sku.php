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
 * Low Search Category Filter Extension
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Extension
 * @author		Sixthsense
 * @link		http://www.sixth.co.in
 */

class Ls_unique_sku_ext {
	
	public $settings 		= array();
	public $description		= 'Does not allow publish of duplicate sku product';
	public $docs_url		= 'http://www.sixth.co.in';
	public $name			= 'LS Unique SKU';
	public $settings_exist	= 'y';
	public $version			= '3.2.0';
	
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
	}// ----------------------------------------------------------------------
	
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
		
		  $hooks = array(
   		   'entry_submission_start' => 'entry_submission_start',
      		'safecracker_submit_entry_start' => 'safecracker_submit_entry_start'
    		);


		foreach ($hooks as $hook => $method)
		{
			$data = array(
				'class'		=> __CLASS__,
				'method'	=> $method,
				'hook'		=> $hook,
				'settings'	=> serialize($this->settings),
				'version'	=> $this->version,
				'enabled'	=> 'y'
			);

			$this->EE->db->insert('extensions', $data);			
		}
	}	

	// ----------------------------------------------------------------------
	
	/**
	 * safecracker_submit_entry_start
	 *
	 * @param 
	 * @return 
	 */
	public function safecracker_submit_entry_start($data)
	{
	
	}
	
	

	// ----------------------------------------------------------------------
	
	/**
	 * entry_submission_start
	 *
	 * @param 
	 * @return 
	 */
	public function entry_submission_start($channel_id=0, $autosave=FALSE)
	{ 
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

/* End of file ext.low_search_category_filter.php */
/* Location: /system/expressionengine/third_party/low_search_category_filter/ext.low_search_category_filter.php */