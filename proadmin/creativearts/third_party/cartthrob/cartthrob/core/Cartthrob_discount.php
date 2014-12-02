<?php if ( ! defined('CARTTHROB_PATH')) Cartthrob_core::core_error('No direct script access allowed');

abstract class Cartthrob_discount extends Cartthrob_child
{
	public $title = '';
	public $settings = array();
	public $plugin_settings = array();
	
	protected $error;
	protected $coupon_code = FALSE;
	
	public static $global_settings = array(
		array(
			'type' => 'textarea',
			'short_name' => 'used_by',
			'name' => 'discount_redeemed_by',
			'note' => 'discount_redeemed_by_note',
		),
		array(
			'type' => 'text',
			'short_name' => 'per_user_limit',
			'name' => 'discount_per_user_limit',
			'note' => 'discount_per_user_limit_note',
			'size' => '50px',
		),
		array(
			'type' => 'text',
			'short_name' => 'discount_limit',
			'name' => 'discount_limit',
			'note' => 'discount_limit_note',
			'size' => '50px',
		),
		array(
			'type' => 'text',
			'short_name' => 'member_groups',
			'name' => 'discount_limit_by_member_group',
			'note' => 'discount_limit_by_member_group_note',
		),
		array(
			'type' => 'text',
			'short_name' => 'member_ids',
			'name' => 'discount_limit_by_member_id',
			'note' => 'discount_limit_by_member_id_note',
		)
	);
	public function initialize($plugin_settings = array(), $defaults = array())
	{
		if (is_array($plugin_settings))
		{
			$this->plugin_settings = $plugin_settings;
		}
		
		$this->type = Cartthrob_core::get_class($this);
		
		return $this;
	}
	
	public function plugin_settings($key, $default = FALSE)
	{
		if ($key === FALSE)
		{
			return $this->plugin_settings;
		}
		
		return (isset($this->plugin_settings[$key])) ? $this->plugin_settings[$key] : $default;
	}
	
	public function set_plugin_settings($plugin_settings)
	{
		$this->plugin_settings = $plugin_settings;
		
		return $this;
	}

	public function coupon_code()
	{
		return $this->coupon_code;
	}
	
	public function set_coupon_code($coupon_code)
	{
		if (is_string($coupon_code))
		{
			$this->coupon_code = $coupon_code;
		}
		
		return $this;
	}
	
	public function set_error($error)
	{
		if (is_string($error))
		{
			$this->error = $error;
		}
		
		return $this;
	}
	
	public function error()
	{
		return $this->error;
	}
	
	abstract function get_discount();
}
