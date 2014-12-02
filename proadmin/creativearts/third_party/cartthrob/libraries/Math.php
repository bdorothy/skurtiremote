<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! class_exists('EvalMath'))
{
	require_once PATH_THIRD.'cartthrob/libraries/EvalMath.php';
}

/**
 * CartThrob Math Class
 *
 * Uses EvalMath library to evalute arithmetic expressions, this lib is just a CI wrapper for the main lib below
 */
class Math extends EvalMath
{
    private $errors = array();
    
    public function __construct()
    {
        parent::__construct();
        
        $this->suppress_errors = TRUE;
    }
    
    public function evaluate($expression)
    {
        if (preg_match('#{.*?}#', $expression))
        {
            return $this->trigger('Unparsed EE tags in expression, check parse order.');
        }
        
        return parent::evaluate($expression);
    }
    
    public function arithmetic($num1, $num2 = 0, $operator = FALSE)
    {
        if ( ! $operator)
        {
            $operator = '+';
        }
        
        $valid_operators = array(
            '+',
            '-',
            '*',
            '/',
            '%',
            '++',
            '--'
        );
        
        if ( ! in_array($operator, $valid_operators))
        {
            return $this->trigger(sprintf('Invalid Operator: %s', xss_clean($operator)));
        }
        
        if ($operator === '++')
        {
            $num2 = 1;
            $operator = '+';
        }
        elseif ($operator === '--')
        {
            $num2 = 1;
            $operator = '-';
        }
        
        if ($num1 === FALSE || $num1 === '')
        {
            return $this->trigger('Missing/invalid num1');
        }
        
        if ($num2 === FALSE || $num2 === '')
        {
            return $this->trigger('Missing/invalid num1');
        }
        
        $num1 = sanitize_number($num1, TRUE);
        
        $num2 = sanitize_number($num2, TRUE);
        
        return $this->evaluate($num1.$operator.$num2);
    }
    
    public function trigger($msg)
    {
        parent::trigger($msg);
        
        $this->errors[] = $this->last_error;
        
        return FALSE;
    }
    
    public function errors()
    {
        return $this->errors;
    }
    
    public function last_error()
    {
        return $this->last_error;
    }
}
