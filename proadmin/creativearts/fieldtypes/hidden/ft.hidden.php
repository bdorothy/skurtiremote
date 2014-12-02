<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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

// --------------------------------------------------------------------

/**
 * Creativearts Text Fieldtype Class
 *
 * @package		Creativearts
 * @subpackage	Fieldtypes
 * @category	Fieldtypes
 * @author		Creativelab Dev Team
 * @link		http://creativelab.com
 */
class Hidden_ft extends EE_Fieldtype {

	var $info = array(
		'name'		=> 'Hidden Field',
		'version'	=> '1.0'
	);

	// Parser Flag (preparse pairs?)
	var $has_array_data = FALSE;


	function display_field($data)
	{
		ee()->javascript->set_global('publish.hidden_fields', array($this->field_id => $this->field_name));
		return form_hidden($this->field_name, $data);
	}
}

// END Hidden_Ft class

/* End of file ft.hidden.php */
/* Location: ./system/creativearts/fieldtypes/ft.hidden.php */