<?php
if ( ! function_exists('generate_license_number'))
{
	function generate_license_number($type='uuid')
	{
		if ($type == 'uuid')
		{
			if (function_exists('com_create_guid'))
			{
				return substr(com_create_guid(), 1, -1);
			}
			else
			{
				return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', rand(0, 0xffff), rand(0, 0xffff), rand(0, 0xffff), rand(0, 0x0fff) | 0x4000, rand(0, 0x3fff) | 0x8000, rand(0, 0xffff), rand(0, 0xffff), rand(0, 0xffff));
			}
		}

		return FALSE;
	}
}