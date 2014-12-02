<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Log model
 *
 * @package CartThrob
 * @author Chris Newton
 * @since 2.0
 * 
 * used to read and write log entries
 */
class Log_model extends CI_Model
{
	public $cartthrob, $store, $cart;
	
	public function __construct()
	{
		$this->load->library('logger');
 	}
	
	/**
	 * log
	 *
	 * writes an entry to EE's console log
	 * 
	 * @param string|array $action 
	 * @param string $method 
	 * @return void
	 * @author Chris Newton
	 */
	public function log($message, $method = FALSE)
	{
		if ($method === 'js')
		{
			$this->load->library('javascript');
			
			echo '<script type="text/javascript">var log = '.json_encode(array('message' => $message)).'; if (window.console){ window.console.log(log.message); }</script>';
		}
		else if ($method === 'console')
		{
			if (is_string($message))
			{
				echo $message.PHP_EOL;
			}
			else
			{
				var_dump($message);
			}
		}
		else
		{
			if (file_exists(PATH_THIRD.'omnilog/classes/omnilogger.php'))
			{
				require_once PATH_THIRD.'omnilog/classes/omnilogger.php';
				
				if (is_array($message))
				{
					$simple_array = TRUE;
				
					foreach ($message as $key => $value)
					{
						if ( ! is_int($key) || ($value && ! is_string($value)))
						{
							$simple_array = FALSE;
						}
					}
					
					$message = ($simple_array) ? implode("\r\n", $message) : print_r($message, TRUE);
				}
				
				$omnilog_entry = new Omnilog_entry(array(
					'addon_name' => 'CartThrob',
					'date' => time(),
					'message' => $message,
					'notify_admin' => FALSE,
					'type' => Omnilog_entry::NOTICE,
				));
		
				Omnilogger::log($omnilog_entry);
				
				unset($omnilog_entry);
			}
			else
			{
				$this->logger->log_action($message);
			}
		}
 	}
	// END
}
// END CLASS