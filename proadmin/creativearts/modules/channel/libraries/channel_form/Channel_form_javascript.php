<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Creativearts - by Creativelab
 *
 * @package		Creativearts
 * @author		Creativelab Dev Team
 * @copyright	Copyright (c) 2003 - 2014, Creativelab, Inc.
 * @license		http://creativelab.com/creativearts/user-guide/license.html
 * @link		http://creativelab.com
 * @since		Version 2.7
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Creativearts Channel Form Javascript Class
 *
 * @package		Creativearts
 * @subpackage	Core
 * @category	Core
 * @author		Creativelab Dev Team
 * @link		http://creativelab.com
 */
class Channel_form_javascript
{
	private $js_path;

	/**
	 * Constructor
	 */
	public function __construct($params = array())
	{
		if (ee()->config->item('use_compressed_js') == 'n')
		{
			$this->js_path = PATH_THEMES.'javascript/src/';

			if ( ! defined('PATH_JQUERY'))
			{
				define('PATH_JQUERY', $this->js_path.'jquery/');
			}
		}
		else
		{
			$this->js_path = PATH_THEMES.'javascript/compressed/';

			if ( ! defined('PATH_JQUERY'))
			{
				define('PATH_JQUERY', $this->js_path.'jquery/');
			}
		}

		ee()->lang->loadfile('jquery');
	}

	// --------------------------------------------------------------------

	/**
	 * Combo Load
	 */
	public function combo_load()
	{
		ee()->load->library('javascript_loader');
		ee()->javascript_loader->combo_load();

		if (ee()->input->get('include_jquery') == 'y')
		{
			ee()->output->set_output(file_get_contents(PATH_JQUERY.'jquery.js')."\n\n".ee()->output->get_output());
		}

		if (ee()->input->get('use_live_url') == 'y')
		{
			ee()->output->append_output(ee()->channel_form->_url_title_js()."\n\n");
		}

		ee()->load->helper('smiley');

		ee()->output->append_output(((ee()->config->item('use_compressed_js') != 'n') ? str_replace(array("\n", "\t"), '', smiley_js('', '', FALSE)) : smiley_js('', '', FALSE))."\n\n");

		ee()->output->append_output(file_get_contents($this->js_path.'channel_form.js'));

		ee()->output->set_header('Content-Length: '.strlen(ee()->output->get_output()));
	}
}

/* End of file Channel_form_Javascript.php */
/* Location: ./system/creativearts/modules/channel/libraries/Channel_form_Javascript.php */