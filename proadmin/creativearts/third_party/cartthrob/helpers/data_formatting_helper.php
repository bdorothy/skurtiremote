<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// this helper cleans and formats data

if ( ! function_exists('set'))
{
	function set()
	{
		$args = func_get_args(); 
		foreach ($args as $arg)
		{
			if ($arg) return $arg;
		}
		
		return end($args);
	}
}
/*
input example: 

$data =
  array (
    0 => 
    array (
      'option_group' => 'Size',
      'label' => 'Size',
      'options' => 
      array (
        'option' => 
        array (
          2 => 'small',
          406177402 => 'medium',
        ),
        'price' => 
        array (
          2 => '10',
          406177402 => '20',
        ),
        'option_template' => '',
        'price_template' => '',
      ),
    ),
    1 => 
    array (
      'option_group' => 'Color',
      'label' => 'Color',
      'options' => 
      array (
        'option' => 
        array (
          2 => 'red',
          5103247566 => 'blue',
          7396813386 => '',
        ),
        'price' => 
        array (
          2 => '10',
          5103247566 => '15',
          7396813386 => '',
        ),
        'option_template' => '',
        'price_template' => '',
      ),
    ),
);

//////////////
 ouput
	array (
	  0 => 
	  array (
	    'Size' => 'small',
	    'Color' => 'red',
	  ),
	  1 => 
	  array (
	    'Size' => 'medium',
	    'Color' => 'red',
	  ),
etc: 

*/ 
if ( ! function_exists('cartesian'))
{
	function cartesian($input) 
	{
		$result = array();

		while (list($key, $values) = each($input)) 
		{
			// If is empty, skip it. 
			if (empty($values)) 
			{
				continue;
			}
 			if (empty($result)) 
			{
				if (!is_array($values) && $values !== FALSE && $values !==NULL && $values != "")
				{
					$result[] = array($key => $values);
				}
				else
				{
					foreach($values as $value) 
					{
						if ($value !== "" && $value !== NULL && $value !== FALSE)
						{
							$result[] = array($key => $value);
						}
						else
						{
						}
					}
				}
			}
			else 
			{
				$append = array();

				foreach($result as &$product) 
				{
					if (is_array($values))
					{
						$product[$key] = array_shift($values);
						$copy = $product;
						foreach($values as $item) 
						{
							if ($item !=="" && $item !== NULL && $item !== FALSE)
							{
								$copy[$key] = $item;
								$append[] = $copy;
							}
							else
							{
							}
						}

					array_unshift($values, $product[$key]);
					}
				}

					if ($append)
					{
					$result = array_merge($result, $append);
				}
			}
		}
		return $result;
	}
}
if ( ! function_exists('cartesian_to_price'))
{
	/*
	input something like this, 
	[0]=>
	  array(2) {
	    ["Size"]=>
	    string(2) "10"
	    ["Color"]=>
	    string(2) "10"
	  }
	  [1]=>
	  array(2) {
	    ["Size"]=>
	    string(2) "20"
	    ["Color"]=>
	    string(2) "10"
	  }
	
	and you get this back
	  [0]=>
	  int(20)
	  [1]=>
	  int(30)
	
	*/ 
	function cartesian_to_price($input) 
	{
 		$prices = array(); 
		foreach ($input as $key => $value)
		{
			$prices[$key]= 0; 
			foreach ($value as $k => $price)
			{
				$price = trim($price); 
				$price +=0; // cast as number; 
 				$prices[$key] += trim($price);
			}
		}
		
 		return $prices; 
	}
}

/**
 * Removes all non-numeric, non-decimal formatting from a string
 *
 * @access private
 * @param string $number
 * @return float|string|int
 */
if ( ! function_exists('sanitize_number'))
{
	function sanitize_number($number = NULL, $allow_negative = FALSE)
	{
		if (is_int($number) || is_float($number) || ctype_digit($number))
		{
			return $number;
		}

		if ( ! $number)
		{
			return 0;
		}

		$prefix = ($allow_negative && preg_match('/^-/', $number)) ? '-' : '';
		// @TODO should probably figure out how to check and see if this number was formatted Euro-style with commas replacing decimal points
		$number = preg_replace('/[^0-9\.]/', '', $number);

		// changed so that '' won't be returned
		if (is_numeric($number) || is_int($number) || is_float($number) || ctype_digit($number))
		{
			return $prefix.$number;
		}
		else
		{
			return 0; 
		}
	}
}

if ( ! function_exists('_array_merge'))
{
	function _array_merge($a, $b)
	{
		foreach ($b as $key => $value)
		{
			if (is_array($value) && isset($a[$key]))
			{
				$a[$key] = @_array_merge($a[$key], $value);
			}
			else
			{
				$a[$key] = $value;
			}
		}
		
		return $a;
	}
}

if ( ! function_exists('array_key_prefix'))
{
	function array_key_prefix(array $array, $prefix = '')
	{
		$return = array();
		
		foreach ($array as $key => $value)
		{
			$return[$prefix.$key] = $value;
		}
		
		return $return;
	}
}

if ( ! function_exists('array_value'))
{
	/**
	 * array value
	 *
	 * get a value nested in a multi-dimensional array
	 *
	 * ex.
	 *
	 * $array = array(
	 * 	'foo' => array(
	 * 		1 => 'bar',
	 * 		2 => 'baz',
	 * 	)
	 * );
	 *
	 * echo array_value($array, 'foo', 2);
	 *
	 * //outputs 'baz'
	 * 
	 * @param array $array, [mixed $index]...
	 * 
	 * @return mixed
	 */
	function array_value($array)
	{
		if ( ! is_array($array))
		{
			return FALSE;
		}
		
		$args = func_get_args();
		
		array_shift($args);
		
		foreach ($args as $key)
		{
			if (isset($array[$key]))
			{
				$array = $array[$key];
			}
			else
			{
				return FALSE;
			}
		}
		
		return $array;
	}
}

/**
 * Strips all non-numeric formatting from a string
 *
 * @access private
 * @param string $credit_card_number
 * @return int|string
 */
if ( ! function_exists('sanitize_credit_card_number'))
{
	function sanitize_credit_card_number($credit_card_number=NULL)
	{
		if ( ! $credit_card_number)
		{
			return false;
		}

		$credit_card_number = preg_replace('/[^0-9]/', '', $credit_card_number);

		// SOMETIMES php_int_max is smaller than a CC number (32 bit systems)
		// ideally we want the CC number returned as an integer, but we'll return it as a string if we have to
		if (defined('PHP_INT_MAX') && $credit_card_number <= PHP_INT_MAX ) 
		{
			$credit_card_number = (int) $credit_card_number;
		}

		return $credit_card_number;
	}
}

/**
 * Converts a multi-line string to an array
 *
 * @access private
 * @param string $data textarea content
 * @return array
 * @since 1.0.0
 * @author Rob Sanchez
 */
if ( ! function_exists('textarea_to_array'))
{
	function textarea_to_array($data)
	{
		return preg_split('/[\r\n]+/', $data);
	}
}

if ( ! function_exists('param_string_to_array'))
{
	function param_string_to_array($string)
	{
		$values = array();

		if ($string)
		{
			foreach (explode('|', $string) as $value)
			{
				if (strpos($value, ':') !== FALSE)
				{
					$value = explode(':', $value);

					$values[$value[0]] = $value[1];
				}
				else
				{
					$values[$value] = $value;
				}
			}
		}
		return $values;
	}
}
/**
 * view_formatted_phone_number
 *
 * returns an array of phone parts
 * @param string $phone 
 * @return string formatted string | array of number parts
 * @author Chris Newton
 * @since 1.0
 * @access private
 */
if ( ! function_exists('get_phone_number_array'))
{
	function get_phone_number_array($phone) 
	{
		if (!$phone)
		{
			return NULL; 
		}
		$return = get_formatted_phone($phone);

		$output ="";
		if ($return['international'])
		{
			$output .=$return['international']."-";
		}
		if ($return['area_code'])
		{
			$output .=$return['area_code']."-";
		}
		if ($return['prefix'])
		{
			$output .=$return['prefix']."-";
		}
		if ($return['suffix'])
		{
			$output .=$return['suffix'];
		}
		return $output; 
	
	}
}

if ( ! function_exists('get_formatted_phone'))
{
	function get_formatted_phone($phone)
	{
		$phone = preg_replace("/[^0-9]/", "", $phone);
	    if (strlen($phone) == 7) 
		{
	      $phone =  preg_replace("/([0-9]{3})([0-9]{4})/", "$1$2", $phone);
	    } 
		elseif (strlen($phone) == 10) 
		{
	      $phone = preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "$1$2$3", $phone);
	    } 
		$return['international']="";	
		$return['area_code'] = ""; 
		$return['prefix'] = "";
		$return['suffix'] = "";
	
		if (strlen($phone)>10)
		{
			$return['international'] = substr($phone, 0, -10);
		}
		if (strlen($phone)>=10)
		{
			$return['area_code'] = substr($phone, -10, 3);
		}
		if (strlen($phone)>=7)
		{
			$return['prefix'] = substr($phone, -7, 3);
		}
		if (strlen($phone)>4)
		{
			$return['suffix'] = substr($phone, -4, 4);
		}
		return $return; 
	}
}
if ( ! function_exists('response_xml_array'))
{
	function response_xml_array($xml) {
		$xml_array = array();

		$node_chars = '/<(\w+)\s*([^\/>]*)\s*(?:\/>|>(.*)<\/\s*\\1\s*>)/s';
		$attr_chars = '/(\w+)=(?:"|\')([^"\']*)(:?"|\')/';

		preg_match_all($node_chars, $xml, $elements);

		foreach ($elements[1] as $key => $value) 
		{
			if ($elements[3][$key]) 
			{
				$xml_array[$elements[1][$key]] = $elements[3][$key];
			}
		}
		return $xml_array;
	}
}
	
	/**
	 * xml_to_array
	 *
	 * This converts xml to an array. The default will only output 
	 * one child node at a time. For our purposes this is generally fine, 
	 * most of the xml returned from gateway processes do not contain 
	 * multiple child nodes at the same level.
	 *
	 * @param string $xml 
	 * @return array
	 * @author Chris Newton
	 * @since 1.0
	 * @access public
	 */
if ( ! function_exists('xml_to_array'))
{
	function xml_to_array($xml, $build_type="basic") 
	{ 
		$values = array(); 

		$index  = array(); 

		$array  = array(); 

		$parser = xml_parser_create(); 

		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0); 

		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1); 

		xml_parse_into_struct($parser, $xml, $values, $index); 

		xml_parser_free($parser); 
		$count = 0; 

		$name = $values[$count]['tag']; 


		if (isset($values[$count]['attributes']))
		{
			$array[$name] = $values[$count]['attributes'];

		}
		else
		{
			$array[$name] = "";			
		}
		
		$array[$name] = _build_array($values, $count, $build_type); 

	    return $array; 
	}
}
	/**
	 * build_array
	 *
	 * recursively builds array out of xml
	 * set the build type as "complete" and this will build a complete array
	 * even in cases where there are multiple child nodes at the same level. 
	 * The default will only output one child node at a time. For our purposes
	 * this is generally fine, most of the xml returned from gateway processes
	 * do not contain multiple child nodes at the same level.
	 * 
	 * @param string $xml_data 
	 * @param string $count 
	 * @param string $build_type basic / complete
	 * @return array
	 * @author Chris Newton
	 * @since 1.0
	 * @access private
	 */
if ( ! function_exists('_build_array'))
{
	function _build_array($xml_data, &$count, $build_type="basic") 
	{ 
	    $child = array();

	    if (isset($xml_data[$count]['value'])) 
		{
			array_push($child, $xml_data[$count]['value']); 
		}
		if ($count == 0)
		{
			$name = @$xml_data[0]['tag']; 

			if(!empty($xml_data[0]['attributes'])) 
			{                
				foreach ($xml_data[0]['attributes'] as $key=> $value)
				{
					$child[$key] = $value; 
				}    
			}
		}

	    while ($count++ < count($xml_data)) 
		{ 
			switch ($xml_data[$count]['type']) 
			{ 
				case 'cdata': 
					@array_push($child, $xml_data[$count]['value']); 
					break; 
				case 'complete': 
					$name = $xml_data[$count]['tag']; 
					if(!empty($name))
					{ 
						if (isset($xml_data[$count]['value']))
						{
							if ($build_type=="complete")
							{
								$child[$name][]['data'] = $xml_data[$count]['value']; 

							}
							else
							{
								$child[$name]['data'] = $xml_data[$count]['value']; 
							}
						}
						else
						{
							$child[$name] = ""; 	
						}
						if(isset($xml_data[$count]['attributes'])) 
						{                
							foreach ($xml_data[$count]['attributes'] as $key=> $value)
							{
								$curr = count ($child[$name]);
								if ($build_type=="complete")
								{
									$child[$name][$curr-1][$key] = $value; 
								}
								else
								{
									$child[$name][$key] = $value; 
								}
							}    
						}
						if (empty($new_count))
						{
							$new_count = 1; 
						}
						else
						{
							$new_count ++;  
						
						}
					}    
					break; 
				case 'open': 
					$name = $xml_data[$count]['tag']; 
					if (isset($child[$name]))
					{
						$size = count($child[$name]); 
					}
					else
					{
						$size = 0; 
					}
					$child[$name][$size] = _build_array($xml_data, $count); 
					break; 
				case 'close': 
					return $child; 
					break; 
			}
		} 
		return $child; 
	}
	// END
}

/**
 * url_string_to_array
 *
 * converts a urlencoded string into an array. 
 *  
 * @access public
 * @param string $url_string URLencoded string to split
 * @return array
 * @author Chris Newton
 * @since 1.0.0
 * @author Rob Sanchez
 **/
if ( ! function_exists('url_string_to_array'))
{
	function url_string_to_array($url_string, $split_character = "&")
	{
		parse_str($url_string, $data);
		return $data;
	
		$array = explode($split_character, $url_string);
		$i = 0;
		while ($i < count($array)) {
			$b = split('=', $array[$i]);
			if ( ! isset($b[1]))
			{
				$b[1] = '';
			}
			$no_space_key=rtrim(htmlspecialchars(urldecode($b[0])));
			$new_array[$no_space_key] = htmlspecialchars(urldecode($b[1]));
			$i++;
		}
		return $new_array;
	}
}
if ( ! function_exists('bool_string'))
{
	function bool_string($string, $default = FALSE)
	{
		switch (strtolower($string))
		{
			case 'true':
			case 't':
			case 'yes':
			case 'y':
			case 'on':
			case '1':
				return TRUE;
				break;
			case 'false':
			case 'f':
			case 'no':
			case 'n':
			case 'off':
			case '0':
				return FALSE;
				break;
			default:
				return $default;
		}
	}
}
if ( ! function_exists('create_bool_string'))
{
	// gives us a little more obscurity
	// for our encrypted boolean form values
	function create_bool_string($bool = FALSE)
	{
		switch(rand(1, 6))
		{
			case 1:
				$string = ($bool) ? 'true' : 'false';
				break;
			case 2:
				$string = ($bool) ? 't' : 'f';
				break;
			case 3:
				$string = ($bool) ? 'yes' : 'no';
				break;
			case 4:
				$string = ($bool) ? 'y' : 'n';
				break;
			case 5:
				$string = ($bool) ? 'on' : 'off';
				break;
			case 6:
				$string = ($bool) ? '1' : '0';
				break;
		}

		$output = '';

		foreach (str_split($string) as $char)
		{
			$output .= (rand(0,1)) ? $char : strtoupper($char);
		}

		return $output;
	}
}


if ( ! function_exists('_unserialize'))
{
	/**
	 * Unserialize data, and always return an array
	 * 
	 * @param	mixed $data
	 * @param	mixed $base64_decode = FALSE
	 * @return	array
	 */
	function _unserialize($data, $base64_decode = FALSE)
	{
		if (is_array($data))
		{
			return $data;
		}
		
		if ($base64_decode)
		{
			$data = base64_decode($data);
		}
		
		if (FALSE === ($data = @unserialize($data)))
		{
			return array();
		}
		
		return $data;
	}
}


if ( ! function_exists('split_url_string'))
{
	/**
	 * split_url_string
	 *
	 * converts a urlencoded string into an array. 
	 *  
	 * @access public
	 * @param string $url_string URLencoded string to split
	 * @return array
	 * @author Chris Newton, Rob Sanchez
	 * @since 1.0
	 **/
	function split_url_string($url_string, $split_character = '&')
	{
		$array = explode($split_character, $url_string);
		$i = 0;
		while ($i < count($array)) {
 			$b = explode('=', $array[$i], 2); 
 
			if ( ! isset($b[1]))
			{
				$b[1] = '';
			}
			$no_space_key=rtrim(htmlspecialchars(urldecode($b[0])));
			$new_array[$no_space_key] = htmlspecialchars(urldecode($b[1]));
			$i++;
		}
		return $new_array;
	}
}


if ( ! function_exists('split_delimited_string'))
{
	/**
	 * split_delimited_string
	 *
	 * converts a pipe or comma delimited string into an array. 
	 *  
	 * @access public
	 * @param string $string string to split
	 * @param array|string $split_character the character(s) to split by
	 * @return array
	 * @author Chris Newton, Rob Sanchez
	 * @since 1.0
	 **/
	function split_delimited_string($string, $split_character = array(',', '|'), $trim = TRUE)
	{
		if (is_array($split_character))
		{
			$regex = '['.preg_quote(implode($split_character)).']';
		}
		else
		{
			if (strlen($split_character) > 1)
			{
				$regex = $split_character;
			}
			else
			{
				$regex = '['.preg_quote($split_character).']';
			}
		}
		
		$regex = ($trim) ? "/\s*{$regex}\s*/" : "/{$regex}/";
		
		if ($trim)
		{
			$string = trim($string);
		}
		
		return preg_split($regex, $string);
	}
}

if ( ! function_exists('convert_response_xml'))
{
	/**
	 * convert_response_xml function
	 *
	 * This converts xml nodes to array keys. This is really only useful for very small xml responses and does not return attributes, only nodes and node names.
	 *
	 * @param string $xml xml to be converted to array
	 * @return array
	 * @since 1.0
	 * @access public
	 * @author Chris Newton
	 **/
	function convert_response_xml($xml) {
		// ported 11/30 by barrett
		$xml_array = array();

		$node_chars = '/<(\w+)\s*([^\/>]*)\s*(?:\/>|>(.*)<\/\s*\\1\s*>)/s';
		$attr_chars = '/(\w+)=(?:"|\')([^"\']*)(:?"|\')/';

		preg_match_all($node_chars, $xml, $elements);

		foreach ($elements[1] as $key => $value) 
		{
			if ($elements[3][$key]) 
			{
				$xml_array[$elements[1][$key]] = $elements[3][$key];
			}
		}
		return $xml_array;
	}
}

if ( ! function_exists('convert_number_to_string'))
{
	function convert_number_to_string($number) 
	{ 
		if (($number < 0) || ($number > 999999999)) 
		{ 
			return $number;
		} 

		$Gn = floor($number / 1000000);  /* Millions (giga) */ 
		$number -= $Gn * 1000000; 
		$kn = floor($number / 1000);	 /* Thousands (kilo) */ 
		$number -= $kn * 1000; 
		$Hn = floor($number / 100);	  /* Hundreds (hecto) */ 
		$number -= $Hn * 100; 
		$Dn = floor($number / 10);	   /* Tens (deca) */ 
		$n = $number % 10;			   /* Ones */ 

		$res = ""; 

		if ($Gn) 
		{ 
			$res .= convert_number($Gn) . " Million"; 
		} 

		if ($kn) 
		{ 
			$res .= (empty($res) ? "" : " ") . 
				convert_number($kn) . " Thousand"; 
		} 

		if ($Hn) 
		{ 
			$res .= (empty($res) ? "" : " ") . 
				convert_number($Hn) . " Hundred"; 
		} 

		$ones = array("", "One", "Two", "Three", "Four", "Five", "Six", 
			"Seven", "Eight", "Nine", "Ten", "Eleven", "Twelve", "Thirteen", 
			"Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eightteen", 
			"Nineteen"); 
		$tens = array("", "", "Twenty", "Thirty", "Fourty", "Fifty", "Sixty", 
			"Seventy", "Eigthy", "Ninety"); 

		if ($Dn || $n) 
		{ 
			if (!empty($res)) 
			{ 
				$res .= " and "; 
			} 

			if ($Dn < 2) 
			{ 
				$res .= $ones[$Dn * 10 + $n]; 
			} 
			else 
			{ 
				$res .= $tens[$Dn]; 

				if ($n) 
				{ 
					$res .= "-" . $ones[$n]; 
				} 
			} 
		} 

		if (empty($res)) 
		{ 
			$res = "zero"; 
		} 

		return strtolower($res); 
	}
}

if ( ! function_exists('convert_card_type'))
{
	function convert_card_type($card_type_value, $return_type)
	{
		switch(strtolower($card_type_value))
		{
			case "mastercard":
			case "mc":
			case "master card":
				if ($return_type == "title" )
				{
					return "Mastercard";
				}
				elseif ($return_type == "camel")
				{
					return "MasterCard";
				}
				elseif ($return_type == "abbreviate")
				{
					return "MC";
				}
				elseif ($return_type == "single")
				{
					return "Mastercard";
				}
			break;
			case "visa":
				return "Visa";
			break;
			case "discover":
				return "Discover";
			break;
			case "diners club":
			case "dc":
			case "diners":
			case "dinersclub":
				if ($return_type == "title" )
				{
					return "Diners Club";
				}
				elseif ($return_type == "camel")
				{
					return "DinersClub";
				}
				elseif ($return_type == "abbreviate")
				{
					return "Diners";
				}
				elseif ($return_type == "single")
				{
					return "Dinersclub";
				}
			break;
			case "american express":
			case "amex":
			case "americanexpress":
				if ($return_type == "title" )
				{
					return "American Express";
				}
				elseif ($return_type == "camel")
				{
					return "AmericanExpress";
				}
				elseif ($return_type == "abbreviate")
				{
					return "amex";
				}
				elseif ($return_type == "single")
				{
					return "Americanexpress";
				}
			break;
			case "switch":
				return "Switch";
			case "laser":
				return "Laser";
			case "maestro":
				return "Maestro";
			case "solo":
				return "Solo";
			case "delta":
				return "Delta";
			default: 
				return $card_type_value; 
		}
		return $card_type_value; 
	}
}
/**
 * arr
 *
 * @param array $array 
 * @param string $key 
 * @return string|null
 * @author Chris Newton
 * 
 * Checks an array for a key, returns it if set, or NULL if not set.
 */
if ( ! function_exists('arr'))
{	
	function arr($array, $key)
	{
		if (isset($array[$key]))
		{
			return $array[$key]; 
		}
		else
		{
			return NULL; 
		}
	}
}