<?php

namespace Creativelab\Creativearts\Library\Parser\Conditional\Token;

/**
 * Creativearts - by Creativelab
 *
 * @package		Creativearts
 * @author		Creativelab Dev Team
 * @copyright	Copyright (c) 2003 - 2014, Creativelab, Inc.
 * @license		http://creativelab.com/creativearts/user-guide/license.html
 * @link		http://creativelab.com
 * @since		Version 2.9.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Creativearts Conditional Token Class
 *
 * @package		Creativearts
 * @subpackage	Core
 * @category	Core
 * @author		Creativelab Dev Team
 * @link		http://creativelab.com
 */
class Token {

	public $type;
	public $lexeme;	// as written in the template

	public $context;
	public $lineno;

	protected $value; // the real value

	public function __construct($type, $lexeme)
	{
		$this->type = $type;
		$this->lexeme = $lexeme;

		// for most tokens the template representation is their value
		$this->value = $lexeme;
	}

	public function canEvaluate()
	{
		return TRUE;
	}

	public function value()
	{
		return $this->value;
	}

	public function __toString()
	{
		return $this->lexeme;
	}

	public function toArray()
	{
		return array(
			$this->type,
			$this->lexeme
		);
	}
}