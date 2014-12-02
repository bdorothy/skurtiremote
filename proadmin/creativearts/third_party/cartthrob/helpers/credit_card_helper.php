<?php

if ( ! function_exists('card_type'))
{
	/**
	 * card_type
	 *
	 * @access public
	 * @param string $ccn
	 * @return string credit card type, ex. amex, visa, mc, discover
	 * @author Chris Newton
	 * @since 1.0.0
	 */
	function card_type($ccn=NULL)
	{
		$cc=str_replace(' ', '', $ccn);

		$cctype = "Unknown Card Type";

		$length = strlen($cc);
		if ($length == 15 && substr($length, 0, 1) == '3' )   
		{ 
			$cctype = "amex"; 
		}
		elseif ( $length == 16 && substr($length, 0, 1) == '6' )			 
	 	{ 
			$cctype = "discover"; 
		}
		elseif ( $length == 16 && substr($length, 0, 1) == '5'  )
		{ 
			$cctype = "mc"; 
		}
		elseif ( ($length == 16 || $length == 13) && substr($cc, 0, 1) == '4' ) 
		{ 
			$cctype = "visa"; 
		}
	   return $cctype;
	} //END
}//END FUNCTION EXISTS

if ( ! function_exists('modulus_10_check'))
{
	/**
	 * modulus 10 check
	 *
	 * a modulus 10 checker
	 * @param string $ccn credit card number
	 * @return bool (true if number is good)
	 * @author Chris Newton
	 *
	 **/
	/* This takes each digit, from right to left and multiplies each second
	digit by two. If the multiple is two-digits long (i.e.: 6 * 2 = 12) the two digits of
	the multiple are then added together for a new number (1 + 2 = 3). You then add up the 
	string of numbers, both unaltered and new values and get a total sum. This sum is then
	divided by 10 and the remainder should be zero if it is a valid credit card. 
	*/
	function modulus_10_check($ccn)
	{
		$cc				= str_replace(' ', '', $ccn);
		$char_array 	= str_split($cc); 
		$digit_count	= sizeof ($char_array); 
		$double			= array();

	   $j 				= 0; 
		for ($i=($digit_count-2); $i>=0; $i-=2)
		{ 
			$double[$j] = $char_array[$i] * 2; 
			$j++; 
		}	 
		$size_of_double 	= sizeof($double); 
		$num_for_validation	= 0; 

		for ($i=0;$i<$size_of_double;$i++)
		{ 
			$double_count = str_split($double[$i]); 
			for ($j=0;$j<sizeof($double_count);$j++)
			{ 
				$num_for_validation += $double_count[$j]; 
			} 
			$double_count = ''; 
		} 

		for ($i=($digit_count-1); $i>=0; $i-=2)
		{ 
			$num_for_validation += $char_array[$i]; 
		} 

		if (substr($num_for_validation, -1, 1) == '0') 
		{ 
			return TRUE;  
		}
		else 
		{ 
			return FALSE; 
		}
	}
	// END
}//END FUNCTION EXISTS

if ( ! function_exists('sanitize_credit_card_number'))
{

	/**
	 * Strips all non-numeric formatting from a string
	 *
	 * @access public
	 * @param string $credit_card_number
	 * @return int|string
	 */
	function sanitize_credit_card_number($credit_card_number = NULL)
	{
		if ( ! $credit_card_number)
		{
			return '';
		}

		return (string) preg_replace('/[^0-9]/', '', $credit_card_number);
	}//END
}//END FUNCTION EXISTS

if ( ! function_exists('validate_credit_card'))
{
	/**
	 * validate_credit_card
	 * 
	 * @param string $ccn this is the credit card number to verify
	 * @param string $stated_card_type. Matches the stated card type against the actual card type requirements
	 * @return array 'valid' (bool), 'card_type' (string), error_code (int)
	 * error_code 1: card type not found. 
	 * error_code 2: card type mismatch
	 * error_code 3: invalid card number
	 * error_code 4: incorrect number length for card type
	 */	
	function validate_credit_card($ccn, $stated_card_type=NULL)
	{
		// cleaning the number
		$credit_card_number = sanitize_credit_card_number($ccn); 
		
		// setting response data defaults
		$response = array(
			'valid'				=> FALSE,
			'card_type'			=> NULL, 
			'error_code'		=> NULL
			);
		
		// information about credit cards, 
		// ordered roughly by popularity to reduce processor usage (common cards are frontloaded)
		$credit_cards = array(
			'visa'		=> array(
				'name'	=> 'Visa',
				'length' => array(13,16),
				'prefix' => array(4),
				'mod10'	=> TRUE
				),
			'amex'		=> array(
				'name'	=> 'AmericanExpress',
				'length' => array(15),
				'prefix' => array(34,37),
				'mod10'	=> TRUE
				),
			'discover'		=> array(
				'name'	=> 'Discover',
				'length' => array(16),
				'prefix' => array(6011,622,64,65),
				'mod10'	=> TRUE
				),				
			'mc'		=> array(
				'name'	=> 'MasterCard',
				'length' => array(16),
				'prefix' => array(51,52,53,54,55),
				'mod10'	=> TRUE
				),
			'diners'		=> array(
				'name'	=> 'DinersClub',
				'length' => array(14,16),
				'prefix' => array(305,36,38,54,55),
				'mod10'	=> TRUE
				),				
			'jcb'		=> array(
				'name'	=> 'JCB',
				'length' => array(16),
				'prefix' => array(35),
				'mod10'	=> TRUE
				),
			'laser'		=> array(
				'name'	=> 'Laser',
				'length' => array(16,17,18,19),
				'prefix' => array(6304,6706,6771,6709),
				'mod10'	=> TRUE
				),
			'maestro'		=> array(
				'name'	=> 'Maestro',
				'length' => array(12,13,14,15,16,18,19),
				'prefix' => array(5018,5020,5038,6304,6759,6761),
				'mod10'	=> TRUE
				),
			'solo'		=> array(
				'name'	=> 'Solo',
				'length' => array(16,18,19),
				'prefix' => array(6334,6767),
				'mod10'	=> TRUE
				),
			'switch'		=> array(
				'name'	=> 'Switch',
				'length' => array(16,18,19),
				'prefix' => array(4903,4905,4911,4936,564182,633110,6333,6759),
				'mod10'	=> TRUE
				),
			'carteblanche'		=> array(
				'name'	=> 'CarteBlanche',
				'length' => array(14),
				'prefix' => array(300,301,302,303,304,305),
				'mod10'	=> TRUE
				),
			'electron'		=> array(
				'name'	=> 'VisaElectron',
				'length' => array(16),
				'prefix' => array(417500,4917,4913,4508,4844),
				'mod10'	=> TRUE
				),
			'enroute'		=> array(
				'name'	=> 'enRoute',
				'length' => array(15),
				'prefix' => array(2014,2149),
				'mod10'	=> TRUE
				)
			); 
		// finding the type of card by its ccnumber
		foreach($credit_cards as $key => $card_data)
		{
			foreach ($card_data['prefix'] as $prefix)
			{
				if (strpos($credit_card_number, $prefix) === 0)
				{
					$response['card_type'] = $key; 
					continue 3; 
				}
			}
		}
		// checking to see if the type's set now that we've examined the card number
		if (!$response['card_type'])
		{
			// ERROR card not found
			$response['error_code'] = 1; 
		}
		// checking to see if we expect the credit card to be of a certain type
		if ($stated_card_type != NULL && $stated_card_type != $response['card_type'])
		{
			// ERROR card type mismatch.
			$response['error_code'] = 2; 
		}
		// checking mod10
		if ($credit_cards[$response['card_type']]['mod10'])
		{
			if (! modulus_10_check($credit_card_number))
			{
				// ERROR mod 10 problem. 
				$response['error_code'] = 3; 
			}
		}
		// checking card length
		$card_length = strlen($credit_card_number); 
		$match = FALSE; 
		foreach ($credit_cards[$response['card_type']]['length'] as $expected_length)
		{
			if ($card_length == $expected_length)
			{
				$match = TRUE; 
				continue 2; 
			}
		}
		if (!$match)
		{
			// ERROR card length error
			$response['error_code'] = 4; 
		}

		if(empty($response['error_code']) && $response['card_type'])
		{
			$response['valid'] = TRUE; 
		}
		return $response; 
	}
}
