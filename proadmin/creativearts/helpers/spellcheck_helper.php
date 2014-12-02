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
 * Creativearts Segment Helper
 *
 * @package		Creativearts
 * @subpackage	Helpers
 * @category	Helpers
 * @author		Creativelab Dev Team
 * @link		http://creativelab.com
 */

// ------------------------------------------------------------------------

/**
 * Build Spellcheck
 *
 * Creates the proper html output for spellcheck results
 *
 * @access	public
 * @param	string	custom field id
 * @return	string
 */
function build_spellcheck($field_id)
{
	$EE =& get_instance();

	$r = '<div class="spellcheck_content clear_left js_hide" id="spellcheck_holder_'.$field_id.'">'."\n";
	$r .= "\t".'<p><a href="#" class="save_spellcheck">'.$EE->lang->line('save_spellcheck').'</a> | <a href="#" class="revert_spellcheck">'.$EE->lang->line('revert_spellcheck')."</a></p>\n";
	$r .= "\t".'<span id="spellcheck_hidden_'.$field_id.'"></span>'."\n";
	$r .= "\t".'<iframe class="spellcheck_frame" name="spellcheck_frame_'.$field_id.'" id="spellcheck_frame_'.$field_id.'" src="'.BASE.AMP.'C=content_publish'.AMP.'M=spellcheck_actions'.AMP.'action=iframe"></iframe>'."\n";
	$r .= "</div>";

	return $r;
}

/* End of file spellcheck_helper.php.php */
/* Location: ./system/creativearts/helpers/spellcheck_helper.php.php */
