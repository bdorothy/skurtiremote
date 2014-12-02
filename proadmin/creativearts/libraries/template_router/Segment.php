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
 * Creativearts Route Segment Class
 *
 * @package		Creativearts
 * @subpackage	Core
 * @category	Core
 * @author		Creativelab Dev Team
 * @link		http://creativelab.com
 */
class EE_Route_segment {

	public function __construct($name, $rules = array())
	{
		$this->name = $name;
		$this->rules = $rules;
		ee()->lang->loadfile('template_router');
	}

	/**
	 * Compile the segment down to a named regex
	 *
	 * @access public
	 * @return string A regular expression for the segment
	 */
	public function regex()
	{
		return "(?P<{$this->name}>(" . $this->validator() . "))";
	}

	/**
	 * Validate the provided value against the segment rules
	 *
	 * @param mixed $val The value to be checked
	 * @access public
	 * @return bool
	 */
	public function validate($val)
	{
		$regex = "/" . $this->validator() . "/i";
		$result = preg_match($regex, $val);

		if ($result === FALSE)
		{
			throw new Exception(lang('validation_failed'));
		}

		return $result === 1;
	}

	/**
	 * Run through all the rules and combine them into one validator
	 *
	 * @access public
	 * @return A regular expression for all of the segment's validators
	 */
	public function validator()
	{
		$compiled_rules = "";

		foreach ($this->rules as $rule)
		{
			// Place each rule inside an anchored lookahead,
			// this will match the entire string if the rule matches.
			// This allows rules to work together without consuming the match.
			$compiled_rules .= "((?=\b" . $rule->validator() . "\b)([^\/]*))";
		}

		if (empty($this->rules))
		{
			// Default to a wildcard match if we have no rules
			$compiled_rules = "([^\/]*)";
		}

		return $compiled_rules;
	}

	public function set($val)
	{
		$this->value = $val;
	}

}
// END CLASS

/* End of file Segment.php */
/* Location: ./system/creativearts/libraries/template_router/Segment.php */