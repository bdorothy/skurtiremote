<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! class_exists(basename(__FILE__, '.php'))) :

/**
 * Crontabulous Helper
 *
 * @property $EE CI_Controller
 */
class Crontabulous_responder
{
	protected $EE;
	
	protected $enqueue = array();
	protected $errors = array();
	
	/**
	 * @var the key for this site
	 */
	protected $private_key;
	
	protected static $algo = 'sha256';//sha256, sha512, or sha1 PLS DO NOT CHANGE
	
	public function __construct($params = array())
	{
		$this->EE =& get_instance();
		
		$this->EE->load->library('services_json');
	}
	
	public function compatability_test()
	{
		return in_array(self::$algo, hash_algos());
	}
	
	public function set_private_key($key)
	{
		$this->private_key = $key;
		
		return $this;
	}
	
	/**
	 * Validate the incoming request
	 * 
	 * @param string $data  a string of request parameters, could be a query string, could be json, could be base64_encoded+serialized
	 * @param string $signature the signature from the request
	 * 
	 * @return bool
	 */
	public function validate_request()
	{
		if ( ! $this->private_key)
		{
			$this->add_error('No private key specified, quitting.');
			
			return FALSE;
		}
		
		if ( ! $signature = $this->EE->input->get('signature'))
		{
			$this->add_error('No signature in request, quitting.');
			
			return FALSE;
		}
		
		if ( ! $payload = $this->EE->input->get('payload'))
		{
			$this->add_error('No payload in request, quitting.');
			
			return FALSE;
		}
		
		if ( ! $signature === $this->create_signature($payload))
		{
			$this->add_error('Signature did not match, request denied.');
			
			return FALSE;
		}
		
		return TRUE;
	}
	
	/**
	 * Add a url to be added to the cron queue
	 * 
	 * @param string $url
	 * 
	 * @return $this
	 */
	public function enqueue($url)
	{
		$this->enqueue[] = $url;
		
		return $this;
	}
	
	public function add_error($error)
	{
		$this->errors[] = $error;
		
		return $this;
	}
	
	public function send_response()
	{
		$this->EE->output->send_ajax_response(array(
			'success' => count($this->errors) === 0,
			'errors' => $this->errors,
			'enqueue' => ($this->enqueue) ? array() : $this->enqueue,
		));
	}
	
	protected function create_signature(string $data)
	{
		if (function_exists('hash_hmac'))
		{
			return base64_encode(hash_hmac(self::$algo, $data, $this->private_key));
		}
		else if (function_exists('mhash'))
		{
			$algo_map = array(
				'sha1' => MHASH_SHA1,
				'sha256' => MHASH_SHA256,
				'sha512' => MHASH_SHA512,
			);
			
			return base64_encode(mhash($algo_map[self::$algo], $data, $this->private_key));
		}
		
		$pad = str_pad((strlen($this->private_key) <= 64) ? $this->private_key : pack('H*', hash(self::$algo, $this->private_key)), 64, chr(0x00));
		
		return base64_encode(pack('H*', hash(self::$algo, ($pad ^ str_repeat(chr(0x5c), 64)).pack('H*', hash(self::$algo, ($pad ^ str_repeat(chr(0x36), 64)).$data)))));
	}
}

endif;