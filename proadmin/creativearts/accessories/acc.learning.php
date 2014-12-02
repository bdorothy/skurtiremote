<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Creativearts - by Creativelab
 *
 * @package		Creativearts
 * @author		Creativelab Dev Team
 * @copyright	Copyright (c) 2003 - 2014, Creativelab, Inc.
 * @license		http://creativelab.com/creativearts/user-guide/license.html
 * @link		http://creativelab.com
 * @since		Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Creativearts Learning Accessory
 *
 * @package		Creativearts
 * @subpackage	Control Panel
 * @category	Accessories
 * @author		Creativelab Dev Team
 * @link		http://creativelab.com
 */
class Learning_acc {

	var $name			= 'Learning EE';
	var $id				= 'learningEE';
	var $version		= '1.0';
	var $description	= 'Educational Resources for Creativearts';
	var $sections		= array();

	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->EE =& get_instance();
	}

	// --------------------------------------------------------------------

	/**
	 * Set Sections
	 *
	 * Set content for the accessory
	 *
	 * @access	public
	 * @return	void
	 */
	function set_sections()
	{
		$this->sections = array(

			ee()->lang->line('community_tutorials')	 => 	'<ul>
						<li><a href="'.ee()->cp->masked_url('http://train-ee.com/').'" title="'.ee()->lang->line('train_ee').'">'.ee()->lang->line('train_ee').'</a></li>
						<li><a href="'.ee()->cp->masked_url('http://www.eescreencasts.com/').'" title="'.ee()->lang->line('ee_screencasts').'">'.ee()->lang->line('ee_screencasts').'</a></li>
						<li><a href="'.ee()->cp->masked_url('http://loweblog.com/freelance/article/ee-search-bookmarklet/').'" title="'.ee()->lang->line('ee_seach_bookmarklet').'">'.ee()->lang->line('ee_seach_bookmarklet').'</a></li>
					</ul>'
						,

			ee()->lang->line('community_resources') => '<ul>
						<li><a href="'.ee()->cp->masked_url('http://eeinsider.com/').'" title="'.ee()->lang->line('ee_insider').'">'.ee()->lang->line('ee_insider').'</a></li>
						<li><a href="'.ee()->cp->masked_url('http://devot-ee.com/').'" title="'.ee()->lang->line('devot_ee').'">'.ee()->lang->line('devot_ee').'</a></li>
						<li><a href="'.ee()->cp->masked_url('http://ee-podcast.com/').'" title="'.ee()->lang->line('ee_podcast').'">'.ee()->lang->line('ee_podcast').'</a></li>
						<li><a href="'.ee()->cp->masked_url('http://show-ee.com/').'" title="Show-EE">Show-EE</a></li>
					</ul>
			',
			ee()->lang->line('support') => '<ul>
						<li><a href="'.ee()->cp->masked_url(ee()->config->item('doc_url')).'" title="'.ee()->lang->line('documentation').'">'.ee()->lang->line('documentation').'</a></li>
						<li><a href="'.ee()->cp->masked_url('http://creativelab.com/forums/').'" title="'.ee()->lang->line('support_forums').'">'.ee()->lang->line('support_forums').'</a></li>
					</ul>'
		);
	}

	// --------------------------------------------------------------------

}
// END CLASS

/* End of file acc.learning.php */
/* Location: ./system/creativearts/accessories/acc.learning.php */