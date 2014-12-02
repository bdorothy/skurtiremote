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
class Blockquote_rte {

	public $info = array(
		'name'			=> 'Blockquote',
		'version'		=> '1.0',
		'description'	=> 'Triggers the RTE to block quote or un-quote the selected block of text',
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
			'rte.blockquote'	=> array(
				'add'		=> lang('make_blockquote'),
				'remove'	=> lang('remove_blockquote')
			)
		);
	}

	// --------------------------------------------------------------------

	/**
	 * JS Defintion
	 *
	 * @access	public
	 */
	function definition()
	{
		ob_start(); ?>

		WysiHat.addButton('blockquote', {
			label:			EE.rte.blockquote.add,
			'toggle-text': 	EE.rte.blockquote.remove,
			handler: function(state) {
				this.toggle('blockquote');
				this.Selection.set(state.selection);
			},
			query: function() {
				var
				selection	= window.getSelection(),
				hasRange	= !! selection.rangeCount,
				el			= selection.anchorNode;

				if ( hasRange )
				{
					while ( el.nodeType != "1" )
					{
						el = el.parentNode;

						if (el == null)
						{
							break;
						}
					}
				}

				$blockquote	= $(el).parents('blockquote');
				return  !! $blockquote.length;
			}
		});

<?php	$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}

} // END Blockquote_rte

/* End of file rte.blockquote.php */
/* Location: ./system/creativearts/rte_tools/blockquote/rte.blockquote.php */