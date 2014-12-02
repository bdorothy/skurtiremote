<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Mtt Search helper functions
 *
 * @package        mtt_search
 * @author         MyTopTeacher <contact@mytopteacher.com>
 * @link           http://mytopteacher.com/addons/mtt-search
 * @copyright      Copyright (c) 2014, Mtt
 */

// --------------------------------------------------------------------

/**
 * Encode an array for use in the URI
 */
if ( ! function_exists('mtt_search_encode'))
{
	function mtt_search_encode($array = array(), $url = TRUE)
	{
		// Filter the array
		$array = array_filter($array, 'mtt_not_empty');

		// PHP 5.2 support
		$str = defined('JSON_FORCE_OBJECT')
			? json_encode($array, JSON_FORCE_OBJECT)
			: json_encode($array);

		// If we want a url-safe encode, base64-it
		if ($url)
		{
			// Our own version of URL encoding
			$str = base64_encode($str);

			// Clean stuff
			$str = rtrim($str, '=');
			$str = str_replace('/', '_', $str);
		}

		return $str;
	}
}

/**
 * Decode a query back to the array
 */
if ( ! function_exists('mtt_search_decode'))
{
	function mtt_search_decode($str = '', $url = TRUE)
	{
		// Bail out if not valid
		if ( ! (is_string($str) && strlen($str))) return array();

		// Override url setting if we're looking at an encoded string
		if (substr($str, 0, 3) == 'YTo') $url = TRUE;

		// Are we decoding a url-safe query?
		if ($url)
		{
			// Translate back
			$str = str_replace('_', '/', $str);

			// In a URI, plusses get replaced by spaces. Put the plusses back
			$str = str_replace(' ', '+', $str);

			// Decode back
			$str = base64_decode($str);
		}

		// Decoding method
		$array = (substr($str, 0, 2) == 'a:') ? @unserialize($str) : @json_decode($str, TRUE);

		// Force array output
		if ( ! is_array($array)) $array = array();

		return $array;
	}
}

// --------------------------------------------------------------------

/**
 * Returns an array of all substring index positions
 *
 * @param      string
 * @param      string
 * @return     array
 */
if ( ! function_exists('mtt_strpos_all'))
{
	function mtt_strpos_all($haystack, $needle)
	{
		$all = array();

		if (preg_match_all('#'.preg_quote($needle, '#').'#', $haystack, $matches))
		{
			$total = count($matches[0]);
			$offset = 0;

			while ($total--)
			{
				$pos = strpos($haystack, $needle, $offset);
				$all[] = $pos;
				$offset = $pos + 1;
			}
		}

		return $all;
	}
}

/**
 * Returns an array of all substrings within haystack with given length and optional padding
 *
 * @param      string
 * @param      array
 * @param      int
 * @param      int
 * @return     array
 */
if ( ! function_exists('mtt_substr_pad'))
{
	function mtt_substr_pad($haystack, $pos = array(), $length = 0, $pad = 0)
	{
		$all = array();
		$haystack_length = strlen($haystack);

		foreach ($pos AS $p)
		{
			// account for left padding
			$p -= $pad;
			if ($p < 0) $p = 0;

			// Account for right padding
			$l = $length + ($pad * 2);
			if (($p + $l) > $haystack_length) $l = $haystack_length - $p;

			$all[] = substr($haystack, $p, $l);
		}

		return $all;
	}
}

/**
 * Wraps occurrences of $needle found in $haystack in <strong> tags
 *
 * @param      string
 * @param      string
 * @return     string
 */
if ( ! function_exists('mtt_hilite'))
{
	function mtt_hilite($haystack, $needle)
	{
		return preg_replace('#('.preg_quote($needle, '#').')#', '<strong>$1</strong>', $haystack);
	}
}

// --------------------------------------------------------------------

/**
 * Strip string from unwanted chars for better sorting
 *
 * @param      string    String to clean up
 * @param      array     Array of words to ignore (strip out)
 * @return     string
 */
if ( ! function_exists('mtt_clean_string'))
{
	function mtt_clean_string($str, $ignore = array())
	{
		static $chars = array();

		// --------------------------------------
		// Empty string? Don't bother...
		// --------------------------------------

		if (empty($str)) return $str;

		// --------------------------------------
		// Get translation array from native foreign_chars.php file
		// --------------------------------------

		if ( ! $chars)
		{
			// This will replace accented chars with non-accented chars
			if (file_exists(APPPATH.'config/foreign_chars.php'))
			{
				include APPPATH.'config/foreign_chars.php';

				if (isset($foreign_characters) && is_array($foreign_characters))
				{
					foreach ($foreign_characters AS $k => $v)
					{
						$chars[mtt_chr($k)] = $v;
					}
				}
			}

			// Punctuation characters and misc ascii symbols
			$punct = array_merge(
				range(33, 46),   range(58, 64),     range(91, 96),
				range(123, 126), range(145, 156),   range(161, 172),
				range(174, 191), range(8208, 8217), range(8220, 8231)
			);

			// Add punctuation characters to chars array
			foreach ($punct AS $k)
			{
				$chars[mtt_chr($k)] = ' ';
			}
		}

		// --------------------------------------
		// Change brs to spaces
		// --------------------------------------

		$str = preg_replace('#<br\s?/?>#i', ' ', $str);

		// --------------------------------------
		// Get rid of tags
		// --------------------------------------

		$str = strip_tags($str);

		// --------------------------------------
		// Convert non-breaking spaces entities to regular ones
		// --------------------------------------

		$str = str_replace(array('&nbsp;', '&#160;', '&#xa0;') , ' ', $str);

		// --------------------------------------
		// Get rid of entities
		// --------------------------------------

		$str = html_entity_decode($str, ENT_QUOTES, (UTF8_ENABLED ? 'UTF-8' : NULL));

		// --------------------------------------
		// Replace accented chars with unaccented versions
		// Options explored:
		// - CI's convert_accented_characters() with a preg_replace_callback ==> Very s
		// - Static array with 'accented char' => 'unaccented char' and a strtr() ==> Missing chars
		//   But using the native foreign_chars.php file, users can edit the array themselves
		// - iconv seems to work nicely: http://stackoverf.com/questions/3542717/
		// --------------------------------------

		//$str = preg_replace_callback('/(.)/', 'convert_accented_characters', $str);
		//$str = iconv((UTF8_ENABLED ? 'UTF-8' : 'ISO-8859-1'), 'ASCII//TRANSLIT//IGNORE', $str);

		if ($chars)
		{
			$str = strtr($str, $chars);
		}

		// --------------------------------------
		// Change to ercase
		// --------------------------------------

		$str = function_exists('mb_strtolower') ? mb_strtolower($str) : strtolower($str);

		// --------------------------------------
		// Ignore words
		// --------------------------------------

		if ($ignore)
		{
			if ( ! is_array($ignore))
			{
				$ignore = explode(' ', $ignore);
			}

			foreach ($ignore AS $word)
			{
				$str = preg_replace('#\b'.preg_quote($word).'\b#', '', $str);
			}
		}

		// --------------------------------------
		// Strip out new lines and superfluous spaces
		// --------------------------------------

		$str = preg_replace('/[\n\r]+/', ' ', $str);
		$str = preg_replace('/\s{2,}/', ' ', $str);

		// --------------------------------------
		// Return trimmed
		// --------------------------------------

		return trim($str);
	}
}

// --------------------------------------------------------------------

/**
 * Get utf-8 character from ascii integer
 *
 * @access     public
 * @param      int
 * @return     string
 */
if ( ! function_exists('mtt_chr'))
{
	function mtt_chr($int)
	{
		return html_entity_decode('&#'.$int.';', ENT_QUOTES, (UTF8_ENABLED ? 'UTF-8' : NULL));
		//return mb_convert_encoding("&#{$int};", 'UTF-8', 'HTML-ENTITIES');
	}
}

// --------------------------------------------------------------------

/**
 * Clean up given list of words
 *
 * @access      private
 * @param       string
 * @return      string
 */
if ( ! function_exists('mtt_prep_word_list'))
{
	function mtt_prep_word_list($str = '')
	{
		$str = strtolower($str);
		$str = preg_replace("/[^a-z0-9'\s\n]/", '', $str);
		$str = array_unique(array_filter(preg_split('/(\s|\n)/', $str)));
		sort($str);

		return implode(' ', $str);
	}
}

// --------------------------------------------------------------------

/**
 * Format string in given format
 *
 * @access     public
 * @param      string
 * @param      string
 * @return     string
 */
if ( ! function_exists('mtt_format'))
{
	function mtt_format($str = '', $format = 'html')
	{
		// Encode/decode chars specifically for EE params
		$code = array(
			'&quot;' => '"',
			'&apos;' => "'",
			'&#123;' => '{',
			'&#125;' => '}'
		);

		switch ($format)
		{
			case 'url':
				$str = urlencode($str);
			break;

			case 'html':
				$str = htmlspecialchars($str);
				$str = mtt_format($str, 'ee-encode');
			break;

			case 'clean':
				$str = mtt_clean_string($str);
			break;

			case 'ee-encode':
				$str = str_replace(array_values($code), array_keys($code), $str);
			break;

			case 'ee-decode':
				$str = str_replace(array_keys($code), array_values($code), $str);
			break;
		}

		return $str;
	}
}

// --------------------------------------------------------------------

/**
 * Create parameter string from array
 *
 * @access     public
 * @param      array
 * @return     string
 */
if ( ! function_exists('mtt_weight_selection'))
{
	function mtt_weight_selection($name, $active = 0)
	{
		$out    = array();
		$tmpl   = '<label><input type="radio" name="%s" value="%s" %s/> %s</label>';
		$active = (int) $active;

		for ($w = 0; $w <= MTT_SEARCH_MAX_WEIGHT; $w++)
		{
			$selected = ($w === $active) ? 'checked="checked" ' : '';

			$out[] = sprintf($tmpl, $name, $w, $selected, $w);
		}

		return implode("\n", $out);
	}
}

// --------------------------------------------------------------------

/**
 * Create parameter string from array
 *
 * @access     public
 * @param      array
 * @return     string
 */
if ( ! function_exists('mtt_param_string'))
{
	function mtt_param_string($array)
	{
		// prep output
		$out = array();

		foreach ($array AS $key => $val)
		{
			// Disal non-string values
			if ( ! is_string($val)) continue;

			$out[] = sprintf('%s="%s"', $key, $val);
		}

		// Return the string
		return implode(' ', $out);
	}
}

/**
 * Converts EE parameter to workable php vars
 *
 * @access     public
 * @param      string    String like 'not 1|2|3' or '40|15|34|234'
 * @return     array     [0] = array of ids, [1] = boolean whether to include or exclude: TRUE means include, FALSE means exclude
 */
if ( ! function_exists('mtt_explode_param'))
{
	function mtt_explode_param($str)
	{
		// --------------------------------------
		// Initiate $in var to TRUE
		// --------------------------------------

		$in = TRUE;

		// --------------------------------------
		// Check if parameter is "not bla|bla"
		// --------------------------------------

		if (strtolower(substr($str, 0, 4)) == 'not ')
		{
			// Change $in var accordingly
			$in = FALSE;

			// Strip 'not ' from string
			$str = substr($str, 4);
		}

		// --------------------------------------
		// Return two values in an array
		// --------------------------------------

		return array(preg_split('/(&&?|\|)/', $str), $in);
	}
}

/**
 * Converts array to EE parameter
 *
 * @access     public
 * @param      array
 * @param      bool
 * @param      string
 * @return     string
 */
if ( ! function_exists('mtt_implode_param'))
{
	function mtt_implode_param($array = array(), $in = TRUE, $sep = '|')
	{
		// --------------------------------------
		// Initiate string
		// --------------------------------------

		$str = '';

		// --------------------------------------
		// Implode array
		// --------------------------------------

		if ( ! empty($array))
		{
			$str = implode($sep, $array);

			// Prepend 'not '
			if ($in === FALSE) $str = 'not '.$str;
		}

		// --------------------------------------
		// Return string
		// --------------------------------------

		return $str;
	}
}

/**
 * Merges two parameters; first one is leading
 *
 * @access     public
 * @param      mixed
 * @param      mixed
 * @param      bool
 * @return     string
 */
if ( ! function_exists('mtt_merge_params'))
{
	function mtt_merge_params($haystack, $needles, $as_param = FALSE)
	{
		// Prep the haystack
		if ( ! is_array($haystack))
		{
			// Explode the param, forget about the 'not '
			list($haystack, ) = mtt_explode_param($haystack);
		}

		// Prep the needles
		if ( ! is_array($needles))
		{
			list($needles, $in) = mtt_explode_param($needles);
		}
		else
		{
			$in = TRUE;
		}

		// Choose function to merge
		$method = $in ? 'array_intersect' : 'array_diff';

		// Do the merge thing
		$merged = $method($haystack, $needles);

		// Change back to parameter syntax if necessary
		if ($as_param)
		{
			$merged = mtt_implode_param($merged);
		}

		return $merged;
	}
}

// --------------------------------------------------------------------

/**
 * Converts {if foo IN (1|2|3)} to {if foo == "1" OR foo == "2" OR foo == "3"}
 * in given tagdata
 *
 * @access     public
 * @param      string    tagdata
 * @return     string    Prep'ed tagdata
 */
if ( ! function_exists('mtt_prep_in_conditionals'))
{
	function mtt_prep_in_conditionals($tagdata = '')
	{
		if (preg_match_all('#'.LD.'if (([\w\-_]+)|((\'|")(.+)\\4)) (NOT)?\s?IN \((.*?)\)'.RD.'#', $tagdata, $matches))
		{
			foreach ($matches[0] AS $key => $match)
			{
				$left    = $matches[1][$key];
				$operand = $matches[6][$key] ? '!=' : '==';
				$andor   = $matches[6][$key] ? ' AND ' : ' OR ';
				$items   = preg_replace('/(&(amp;)?)+/', '|', $matches[7][$key]);
				$cond    = array();
				foreach (explode('|', $items) AS $right)
				{
					$tmpl   = preg_match('#^(\'|").+\\1$#', $right) ? '%s %s %s' : '%s %s "%s"';
					$cond[] = sprintf($tmpl, $left, $operand, $right);
				}

				// Replace {if foo IN (a|b|c)} with {if foo == 'a' OR foo == 'b' OR foo == 'c'}
				$tagdata = str_replace(
					$match,
					LD.'if '.implode($andor, $cond).RD,
					$tagdata
				);
			}
		}
		return $tagdata;
	}
}

// --------------------------------------------------------------------

/**
 * Flatten results
 *
 * Given a DB result set, this will return an (associative) array
 * based on the keys given
 *
 * @param      array
 * @param      string    key of array to use as value
 * @param      string    key of array to use as key (optional)
 * @return     array
 */
if ( ! function_exists('mtt_flatten_results'))
{
	function mtt_flatten_results($resultset, $val, $key = FALSE)
	{
		$array = array();

		foreach ($resultset AS $row)
		{
			if ($key !== FALSE)
			{
				$array[$row[$key]] = $row[$val];
			}
			else
			{
				$array[] = $row[$val];
			}
		}

		return $array;
	}
}

// --------------------------------------------------------------------

/**
 * Associate results
 *
 * Given a DB result set, this will return an (associative) array
 * based on the keys given
 *
 * @param      array
 * @param      string    key of array to use as key
 * @param      bool      sort by key or not
 * @return     array
 */
if ( ! function_exists('mtt_associate_results'))
{
	function mtt_associate_results($resultset, $key, $sort = FALSE)
	{
		$array = array();

		foreach ($resultset AS $row)
		{
			if (array_key_exists($key, $row) && ! array_key_exists($row[$key], $array))
			{
				$array[$row[$key]] = $row;
			}
		}

		if ($sort === TRUE)
		{
			ksort($array);
		}

		return $array;
	}
}

// --------------------------------------------------------------

/**
 * Is current request an Ajax request or not?
 *
 * @return     bool
 */
if ( ! function_exists('is_ajax'))
{
	function is_ajax()
	{
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
	}
}

// --------------------------------------------------------------

/**
 * Returns TRUE if var is not empty (NULL, FALSE or empty string)
 *
 * @param      mixed
 * @return     bool
 */
if ( ! function_exists('mtt_not_empty'))
{
	function mtt_not_empty($var)
	{
		$empty = FALSE;

		if (is_null($var) || $var === FALSE || (is_string($var) && ! strlen($var)))
		{
			$empty = TRUE;
		}

		return ! $empty;
	}
}

// --------------------------------------------------------------

/**
 * Is array numeric; filled with numeric values?
 *
 * @param      array
 * @return     bool
 */
if ( ! function_exists('mtt_array_is_numeric'))
{
	function mtt_array_is_numeric($array = array())
	{
		$numeric = TRUE;

		foreach ($array AS $val)
		{
			if ( ! is_numeric($val))
			{
				$numeric = FALSE;
				break;
			}
		}

		return $numeric;
	}
}

/**
 * Get prefixed parameters
 *
 * @access     public
 * @param      string
 * @return     array
 */
if ( ! function_exists('mtt_array_get_prefixed'))
{
	function mtt_array_get_prefixed($array, $prefix = '', $strip = FALSE)
	{
		$vals = array();

		// Do we have a prefix?
		if (is_array($array) && $prefix_length = strlen($prefix))
		{
			// Loop through array
			foreach ($array AS $key => $val)
			{
				// Check the prefix
				if (substr($key, 0, $prefix_length) == $prefix)
				{
					if ($strip === TRUE) $key = substr($key, $prefix_length);
					$vals[$key] = $val;
				}
			}
		}

		return $vals;
	}
}

/**
 * Add prefix to values in arra
 *
 * @access     public
 * @param      string
 * @return     array
 */
if ( ! function_exists('mtt_array_add_prefix'))
{
	function mtt_array_add_prefix($array, $prefix = '')
	{
		foreach ($array AS &$val)
		{
			$val = $prefix . (string) $val;
		}

		return $array;
	}
}

// --------------------------------------------------------------

/**
 * Is parameter numeric?
 *
 * @param      string
 * @return     bool
 */
if ( ! function_exists('mtt_param_is_numeric'))
{
	function mtt_param_is_numeric($str)
	{
		return preg_match('/^(not\s|=)?(\d+[|&]?)+$/i', $str);
	}
}

// --------------------------------------------------------------

/**
 * Compare one score to another, used to sort search results for alternative method
 *
 * @param       array
 * @param       array
 * @return      int
 */
if ( ! function_exists('mtt_by_score'))
{
	function mtt_by_score($a, $b)
	{
		if ( ! isset($a['score']) || ! isset($b['score']) || $a['score'] == $b['score']) return 0;
		return ($a['score'] > $b['score']) ? -1 : 1;
	}
}

// --------------------------------------------------------------

/**
 * Order by keywords
 *
 * @param       array
 * @param       array
 * @return      int
 */
if ( ! function_exists('mtt_by_keywords'))
{
	function mtt_by_keywords($a, $b)
	{
		return strcasecmp($a['keywords_clean'], $b['keywords_clean']);
	}
}
// --------------------------------------------------------------

/**
 * Get cache value, either using the cache method (EE2.2+) or directly from cache array
 *
 * @param       string
 * @param       string
 * @return      mixed
 */
if ( ! function_exists('mtt_get_cache'))
{
	function mtt_get_cache($a, $b)
	{
		if (method_exists(ee()->session, 'cache'))
		{
			return ee()->session->cache($a, $b);
		}
		else
		{
			return (isset(ee()->session->cache[$a][$b]) ? ee()->session->cache[$a][$b] : FALSE);
		}
	}
}

// --------------------------------------------------------------

/**
 * Set cache value, either using the set_cache method (EE2.2+) or directly to cache array
 *
 * @param       string
 * @param       string
 * @param       mixed
 * @return      void
 */
if ( ! function_exists('mtt_set_cache'))
{
	function mtt_set_cache($a, $b, $c)
	{
		if (method_exists(ee()->session, 'set_cache'))
		{
			ee()->session->set_cache($a, $b, $c);
		}
		else
		{
			ee()->session->cache[$a][$b] = $c;
		}
	}
}

// --------------------------------------------------------------

/**
 * Zebra table helper
 *
 * @param       bool
 * @return      string
 */
if ( ! function_exists('mtt_zebra'))
{
	function mtt_zebra($reset = FALSE)
	{
		static $i = 0;

		if ($reset) $i = 0;

		return (++$i % 2 ? 'odd' : 'even');
	}
}

// --------------------------------------------------------------

/**
 * Debug
 *
 * @param       mixed
 * @param       bool
 * @return      void
 */
if ( ! function_exists('mtt_dump'))
{
	function mtt_dump($var, $exit = TRUE)
	{
		echo '<pre>'.print_r($var, TRUE).'</pre>';
		if ($exit) exit;
	}
}

// --------------------------------------------------------------

/* End of file mtt_search_helper.php */