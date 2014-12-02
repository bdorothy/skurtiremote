<?php
if ( ! defined('CAL_GREGORIAN'))
{
	define('CAL_GREGORIAN', 0);
}

if ( ! function_exists('cal_days_in_month'))
{
	function cal_days_in_month($calendar, $month, $year)
	{
		return date('t', mktime(0, 0, 0, $month + 1, 0, $year)); 
	}
}