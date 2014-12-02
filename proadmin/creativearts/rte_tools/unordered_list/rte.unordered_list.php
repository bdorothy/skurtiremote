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
 * Creativearts Unordered List RTE Tool
 *
 * @package		Creativearts
 * @subpackage	RTE
 * @category	RTE
 * @author		Creativelab Dev Team
 * @link		http://creativelab.com
 */
class Unordered_list_rte {

	public $info = array(
		'name'			=> 'Unordered List',
		'version'		=> '1.0',
		'description'	=> 'Triggers the RTE to make the selected blocks into unordered list items',
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
	 * @return	mixed array of globals
	 */
	function globals()
	{
		ee()->lang->loadfile('rte');
		return array(
			'rte.unordered_list'	=> array(
				'add'		=> lang('make_ul'),
				'remove'	=> lang('remove_ul')
			)
		);
	}

	// --------------------------------------------------------------------

	/**
	 * Javascript Definition
	 *
	 * @access	public
	 */
	function definition()
	{
		ob_start(); ?>

		WysiHat.addButton('unordered_list', {
			label:			EE.rte.unordered_list.add,
			'toggle-text':	EE.rte.unordered_list.remove,
			handler: function(state){
				this.make('unorderedList');
			},
			query: function(){
				return this.is('unorderedList');
			}
		});

<?php	$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}

} // END Unordered_list_rte

/* End of file rte.unordered_list.php */
/* Location: ./system/creativearts/rte_tools/unordered_list/rte.unordered_list.php */