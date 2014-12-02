<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include config file
include(PATH_THIRD.'mtt_search/config.php');

/**
 * Mtt Search Base Class
 *
 * @package        mtt_search
 * @author         MyTopTeacher <contact@mytopteacher.com>
 * @link           http://mytopteacher.com/addons/mtt-search
 * @copyright      Copyright (c) 2014, Mtt
 */
class Mtt_search_base {

	// --------------------------------------------------------------------
	// PROPERTIES
	// --------------------------------------------------------------------

	/**
	 * Add-on name
	 *
	 * @var        string
	 * @access     public
	 */
	public $name = MTT_SEARCH_NAME;

	/**
	 * Add-on version
	 *
	 * @var        string
	 * @access     public
	 */
	public $version = MTT_SEARCH_VERSION;

	/**
	 * URL to module docs
	 *
	 * @var        string
	 * @access     public
	 */
	public $docs_url = MTT_SEARCH_DOCS;

	// --------------------------------------------------------------------

	/**
	 * Package name
	 *
	 * @var        string
	 * @access     protected
	 */
	protected $package = MTT_SEARCH_PACKAGE;

	/**
	 * Main class shortcut
	 *
	 * @var        string
	 * @access     protected
	 */
	protected $class_name;

	/**
	 * Site id shortcut
	 *
	 * @var        int
	 * @access     protected
	 */
	protected $site_id;

	/**
	 * Models used
	 *
	 * @var        array
	 * @access     protected
	 */
	protected $models = array(
		'mtt_search_collection_model',
		'mtt_search_index_model',
		'mtt_search_log_model',
		'mtt_search_replace_log_model',
		'mtt_search_group_model',
		'mtt_search_shortcut_model'
	);

	// --------------------------------------------------------------------
	// METHODS
	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access     public
	 * @return     void
	 */
	public function __construct()
	{
		// -------------------------------------
		//  Define the package path
		// -------------------------------------

		ee()->load->add_package_path(PATH_THIRD.$this->package);

		// -------------------------------------
		//  Load helper
		// -------------------------------------

		ee()->load->helper($this->package);

		// -------------------------------------
		//  Libraries
		// -------------------------------------

		ee()->load->library('Mtt_search_settings');

		// -------------------------------------
		//  Load the models
		// -------------------------------------

		ee()->load->model($this->models);

		// -------------------------------------
		//  Class name shortcut
		// -------------------------------------

		$this->class_name = ucfirst(MTT_SEARCH_PACKAGE);

		// -------------------------------------
		//  Get site shortcut
		// -------------------------------------

		$this->site_id = (int) ee()->config->item('site_id');
	}

	// --------------------------------------------------------------------

	/**
	 * Return an MCP URL
	 *
	 * @access     protected
	 * @param      string
	 * @return     string
	 */
	protected function mcp_url($method = NULL, $extra = NULL)
	{
		$url = function_exists('cp_url')
		     ? cp_url('addons_modules/show_module_cp', array('module' => $this->package))
		     : BASE.AMP.'C=addons_modules&amp;M=show_module_cp&amp;module='.$this->package;

		if ($method) $url .= AMP.'method='.$method;
		if ($extra)  $url .= AMP.$extra;

		return $url;
	}

	// --------------------------------------------------------------------

} // End class Mtt_search_base