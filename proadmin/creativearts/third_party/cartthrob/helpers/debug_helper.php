<?php

if ( ! function_exists('debug'))
{
	function debug($data)
	{
		if (in_array('xdebug', get_loaded_extensions()))
		{
			var_dump($data);
		}
		else
		{
			echo '<pre>'.print_r($data, TRUE).'</pre>';
		}
	}
	
	function backtrace($limit = FALSE)
	{
		$backtrace = debug_backtrace(FALSE);
		
		array_shift($backtrace);
		array_shift($backtrace);
		
		$return = array();
		
		foreach ($backtrace as $i => $data)
		{
			unset($data['args']);
			
			$return[] = $data;
			
			debug($data);
			
			if ($limit !== FALSE && $i === ($limit - 1))
			{
				break;
			}
		}
		
		return $return;
	}
	
	function caller($which = 0)
	{
		$which += 2;
		
		$backtrace = debug_backtrace(FALSE);
		
		return (isset($backtrace[$which]['function'])) ? $backtrace[$which]['function'] : FALSE;
	}
}