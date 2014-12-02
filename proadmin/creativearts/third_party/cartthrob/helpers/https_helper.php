<?php

if ( ! function_exists('force_https'))
{
	function force_https($secure_domain = FALSE, $send_headers = TRUE)
	{
		$domain = (isset($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : getenv('SERVER_NAME');

		$secure_domain = ($secure_domain) ? $secure_domain : $domain;

		if (isset($_SERVER['REQUEST_URI']))
		{
			$request_uri = $_SERVER['REQUEST_URI'];
		}
		else
		{
			$request_uri = getenv('PATH_INFO');

			$request_uri .= (getenv('QUERY_STRING')) ? '?'.getenv('QUERY_STRING') : '';
		}

		if ( ! is_secure())
		{
			if ($send_headers)
			{
				header('HTTP/1.1 301 Moved Permanently');
			}
			
			header('Location: https://'.$secure_domain.$request_uri);

			exit;
		}
	}
}

if ( ! function_exists('is_secure'))
{
	function is_secure()
	{
		return (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on');
	}
}

if ( ! function_exists('secure_url'))
{
	function secure_url($url, $domain = FALSE)
	{
		if ($domain)
		{
			$url = preg_replace('/(https?:\/\/)([^\/]+)(.*)/', '\\1'.$domain.'\\3', $url);
		}
		
		return str_replace('http://', 'https://', $url);
	}
}