<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once PATH_THIRD.'cartthrob/config.php';
require_once PATH_THIRD.'cartthrob/fieldtypes/ft.cartthrob_matrix.php';

/**
 * @property CI_Controller $EE
 * @property Cartthrob_cart $cart
 * @property Cartthrob_store $store
 */
class Cartthrob_price_by_member_group_ft extends Cartthrob_matrix_ft
{
	public $info = array(
		'name' => 'CartThrob Price - By Member Group',
		'version' => CARTTHROB_VERSION,
	);
	
	public $default_row = array(
		'member_group' => '',
		'price' => '',
	);
	
	public function __construct()
	{
		parent::__construct();
		
		unset($this->buttons['add_column']);
	}
	
	public function pre_process($data)
	{
		$data = parent::pre_process($data);
		
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		
		$this->EE->load->library('cartthrob_loader');
		
		$this->EE->load->library('number');
		
		foreach ($data as &$row)
		{
			if (isset($row['price']) && $row['price'] !== '')
			{	
				$row['price_plus_tax']  = $row['price'];
 				
				if ($plugin = $this->EE->cartthrob->store->plugin($this->EE->cartthrob->store->config('tax_plugin')))
				{
					$row['price_plus_tax'] = $plugin->get_tax($row['price']) + $row['price'];
 				}
				
				$row['price_numeric'] = $row['price'];
				$row['price_plus_tax_numeric'] = $row['price:plus_tax_numeric'] = $row['price_numeric:plus_tax'] = $row['price_plus_tax'];
				
				$row['price'] = $this->EE->number->format($row['price']);
				$row['price_plus_tax'] = $row['price:plus_tax'] = $this->EE->number->format($row['price_plus_tax']);
			}
		}
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		
		return $data;
	}
	
	public function display_field_member_group($name, $value, $row, $index, $blank = FALSE)
	{
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		static $member_groups;
		
		if (is_null($member_groups))
		{
			$member_groups[''] = lang('cartthrob_price_by_member_group_global');
			
			$this->EE->load->model('member_model');
			
			$query = $this->EE->member_model->get_member_groups(array(), array(array('group_id !=' => 2), array('group_id !=' => 3), array('group_id !=' => 4)));
			
			foreach ($query->result() as $row)
			{
				$member_groups[$row->group_id] = $row->group_title;
			}
		}
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		
		return form_dropdown($name, $member_groups, $value);
	}
	
	public function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		if ( ! $tagdata)
		{
			return $this->replace_price($data, $params, $tagdata);
		}
		
		return parent::replace_tag($data, $params, $tagdata);
	}
	
	public function replace_price($data, $params= array(), $tagdata = FALSE)
	{
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		$this->EE->load->library('number');
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		
		return $this->EE->number->format($this->cartthrob_price($data));
	}
	public function replace_plus_tax($data, $params = array(), $tagdata = '')
	{
  		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		$this->EE->load->library('number');
	
		$this->EE->load->library('cartthrob_loader');
		$data = $this->EE->cartthrob->sanitize_number($this->cartthrob_price($data));
 		if ($plugin = $this->EE->cartthrob->store->plugin($this->EE->cartthrob->store->config('tax_plugin')))
		{		
  			$data = $data + $plugin->get_tax($data);
 		}

		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		return $this->EE->number->format($data);
	}
	public function replace_plus_tax_numeric($data, $params = '', $tagdata = '')
	{
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		$this->EE->load->library('number');
 		
		$this->EE->load->library('cartthrob_loader');
		$data = $this->EE->cartthrob->sanitize_number($this->cartthrob_price($data));
		
 		if ($plugin = $this->EE->cartthrob->store->plugin($this->EE->cartthrob->store->config('tax_plugin')))
		{		
  			$data = $data + $plugin->get_tax($data);
 		}
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');

		return $data; 
	}
	public function replace_numeric($data, $params = '', $tagdata = '')
	{
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		$this->EE->load->library('number');
		$this->EE->load->remove_package_path(PATH_THIRD.'cartthrob/');
		
		return $this->EE->cartthrob->sanitize_number($this->cartthrob_price($data));
	}
	public function cartthrob_price($data, $item = NULL)
	{
		if ( ! is_array($data))
		{
			$serialized = $data;
			
			if ( ! isset($this->EE->session->cache['cartthrob']['price_by_member_group']['cartthrob_price'][$serialized]))
			{
				$this->EE->session->cache['cartthrob']['price_by_member_group']['cartthrob_price'][$serialized] = _unserialize($data, TRUE);
			}
			
			$data = $this->EE->session->cache['cartthrob']['price_by_member_group']['cartthrob_price'][$serialized];
		}
		
		$price = NULL;
		
		$default_price = NULL;
		
		//loop through the rows and grab the price for current user's member group
		//or grab the default global price if no price is explicitly set for this member group
		foreach ($data as $row)
		{
			if (is_null($price) && ! empty($row['member_group']) && $this->EE->session->userdata('group_id') == $row['member_group'])
			{
				$price = $row['price'];
			}
			
			if (is_null($default_price) && ! $row['member_group'])
			{
				$default_price = $row['price'];
			}
		}
		
		if (is_null($price) && ! is_null($default_price))
		{
			$price = $default_price;
		}

		return (is_null($price)) ? 0 : $price;
	}
}

/* End of file ft.cartthrob_price_by_member_group.php */
/* Location: ./system/expressionengine/third_party/cartthrob_price_by_member_group/ft.cartthrob_price_by_member_group.php */