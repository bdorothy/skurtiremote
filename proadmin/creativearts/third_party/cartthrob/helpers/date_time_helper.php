<?php

if ( ! function_exists('days'))
{
	function days($timestamp1, $timestamp2)
	{
		$datetime1 = new DateTime($timestamp1);
		$datetime2 = new DateTime($timestamp2)
		;
		$interval = $datetime1->diff($datetime2);
		return $interval->format('%R%a');
	}
}

/*
	// add 
	// sub
	// http://www.php.net/manual/en/datetime.diff.php

	function pluralize( $count, $text ) 
	{ 
	    return $count . ( ( $count == 1 ) ? ( " $text" ) : ( " ${text}s" ) );
	}

	function ago( $datetime )
	{
	    $interval = date_create('now')->diff( $datetime );
	    $suffix = ( $interval->invert ? '-' : '' );
	    if ( $v = $interval->y >= 1 ) return pluralize( $interval->y, 'year' ) . $suffix;
	    if ( $v = $interval->m >= 1 ) return pluralize( $interval->m, 'month' ) . $suffix;
	    if ( $v = $interval->d >= 1 ) return pluralize( $interval->d, 'day' ) . $suffix;
	    if ( $v = $interval->h >= 1 ) return pluralize( $interval->h, 'hour' ) . $suffix;
	    if ( $v = $interval->i >= 1 ) return pluralize( $interval->i, 'minute' ) . $suffix;
	    return pluralize( $interval->s, 'second' ) . $suffix;
	}
	?>
	
	*/
	
	/*
	
		You don't need to calculate the exact difference if you just want to know what date comes earlier:

		<?php

		date_default_timezone_set('Europe/Madrid');

		$d1 = new DateTime('1492-01-01');
		$d2 = new DateTime('1492-12-31');

		var_dump($d1 < $d2);
		var_dump($d1 > $d2);
		var_dump($d1 == $d2);
		
		*/