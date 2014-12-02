<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Creativearts - by Creativelab
 *
 * @package		Creativearts
 * @author		Creativelab Dev Team
 * @copyright	Copyright (c) 2003 - 2014, Creativelab, Inc.
 * @license		http://creativelab.com/creativearts/user-guide/license.html
 * @link		http://creativelab.com
 * @since		Version 2.5
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Creativearts Bold RTE Tool
 *
 * @package		Creativearts
 * @subpackage	RTE
 * @category	RTE
 * @author		Creativelab Dev Team
 * @link		http://creativelab.com
 */
class Bold_rte {

	public $info = array(
		'name'			=> 'Bold',
		'version'		=> '1.0',
		'description'	=> 'Bolds and un-bolds selected text',
		'cp_only'		=> 'n'
	);

	private $EE;

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function __construct()
	{
		// Make a local reference of the Creativearts super object
		$this->EE =& get_instance();
	}

	// --------------------------------------------------------------------

	/**
	 * Globals we need
	 *
	 * @access	public
	 */
	function globals()
	{
		ee()->lang->loadfile('rte');
		return array(
			'rte.bold'	=> array(
				'add'		=> lang('make_bold'),
				'remove'	=> lang('remove_bold')
			)
		);
	}

	// --------------------------------------------------------------------

	/**
	 * JS Definition
	 *
	 * @access	public
	 */
	function definition()
	{
		ob_start(); ?>

		WysiHat.addButton('bold', {
			label: 			EE.rte.bold.add,
			'toggle-text':	EE.rte.bold.remove
		});

<?php	$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}

} // END Bold_rte

/* End of file rte.bold.php */
/* Location: ./system/creativearts/rte_tools/bold/rte.bold.php */