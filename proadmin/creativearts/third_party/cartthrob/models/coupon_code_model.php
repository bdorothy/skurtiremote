<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Coupon_code_model extends CI_Model
{
	private $coupon_code_data = array();
	
	public function __construct()
	{
		$this->load->library('cartthrob_loader');
		
		$this->load->model('cartthrob_entries_model');
	}
	
	public function validate_coupon_code($coupon_code)
	{
		$data = $this->get_coupon_code_data($coupon_code);
		
		if ( ! $data['metadata']['valid'])
		{
			$msg = $this->lang->line('coupon_default_error_msg');

			if ( ! empty($data['metadata']['invalid']))
			{
				$msg = $this->lang->line('coupon_invalid_msg');
			}
			elseif ( ! empty($data['metadata']['expired']))
			{
				$msg = $this->lang->line('coupon_expired_msg');
			}
			elseif ( ! empty($data['metadata']['user_limit']))
			{
				$msg = $this->lang->line('coupon_user_limit_msg');
			}
			elseif ( ! empty($data['metadata']['discount_limit']))
			{
				$msg = $this->lang->line('coupon_coupon_limit_msg');
			}
			elseif ( ! empty($data['metadata']['no_access']))
			{
				$msg = $this->lang->line('coupon_no_access_msg');
			}
			elseif ( ! empty($data['metadata']['global_limit']))
			{
				$msg = sprintf($this->lang->line('coupon_global_limit_msg'), $this->cartthrob->store->config('global_coupon_limit'));
			}
			elseif ( ! empty($data['metadata']['inactive']))
			{
				$msg = $this->lang->line('coupon_inactive_msg');
			}
			elseif (isset($data['metadata']['msg']))
			{
				$msg = $data['metadata']['msg'];
			}
			
			$this->cartthrob->set_error($msg);
		}
		
		return $data['metadata']['valid'];
	}
	
	public function get_coupon_code_data($coupon_code)
	{
		if (isset($this->coupon_code_data[$coupon_code]))
		{
			return $this->coupon_code_data[$coupon_code];
		}
		
		$this->load->library('api/api_cartthrob_discount_plugins');

		$data = array(
			'metadata' => array(
				'valid' => FALSE
			),
			'type' => ''
		);

		//@TODO fix bug if you have a coupon channel, but haven't configured the type field, and then you create a coupon
		if ($this->cartthrob->store->config('coupon_code_channel') && $this->cartthrob->store->config('coupon_code_type'))
		{
			$coupon_field = 't.title';

			if ($this->cartthrob->store->config('coupon_code_field') && $this->cartthrob->store->config('coupon_code_field') != 'title')
			{
				$coupon_field = 'd.field_id_'.$this->cartthrob->store->config('coupon_code_field');
			}
			
			$this->db->from('channel_titles t')
					->join('channel_data d', 't.entry_id = d.entry_id')
					->where('t.channel_id', $this->cartthrob->store->config('coupon_code_channel'))
					->where('t.status !=', 'closed')
					->where($coupon_field, $coupon_code)
					->limit(1);
					
			$query = $this->db->get();

			$data['metadata']['entry_id'] = '';
			$data['metadata']['entry_date'] = '';
			$data['metadata']['expiration_date'] = '';
			$data['metadata']['inactive'] = FALSE;
			$data['metadata']['expired'] = FALSE;
			$data['metadata']['user_limit'] = FALSE;
			$data['metadata']['discount_limit'] = FALSE;
			$data['metadata']['global_limit'] = FALSE;
			$data['metadata']['invalid'] = FALSE;

			if ($query->num_rows())
			{
				$data = _unserialize($query->row('field_id_'.$this->cartthrob->store->config('coupon_code_type')), TRUE);

				$data['metadata']['entry_id'] = $query->row('entry_id');
				$data['metadata']['entry_date'] = $query->row('entry_date');
				$data['metadata']['expiration_date'] = $query->row('expiration_date');
				$data['metadata']['inactive'] = ($query->row('entry_date') > $this->localize->now);
				$data['metadata']['expired'] = ($query->row('expiration_date') && $query->row('expiration_date') < $this->localize->now);
				$data['metadata']['user_limit'] = FALSE;
				$data['metadata']['discount_limit'] = FALSE;
				$data['metadata']['invalid'] = FALSE;
				$data['metadata']['no_access'] = FALSE;
				$data['metadata']['invalid'] = FALSE;
				$data['metadata']['global_limit'] = ($this->cartthrob->store->config('global_coupon_limit') > 1 && count($this->cartthrob->cart->coupon_codes()) > $this->cartthrob->store->config('global_coupon_limit'));
				$data['metadata']['valid'] = TRUE;

				$used_by = ( ! empty($data['used_by'])) ? array_count_values(preg_split('#\s*[,|]\s*#', trim($data['used_by']))) : array();

				if ( ! empty($data['per_user_limit']) && isset($used_by[$this->session->userdata('member_id')]) && ($used_by[$this->session->userdata('member_id')] >= $data['per_user_limit']))
				{
					$data['metadata']['user_limit'] = TRUE;
				}

				if (isset($data['discount_limit']) && $data['discount_limit'] !== '' && $data['discount_limit'] <= 0)
				{
					$data['metadata']['discount_limit'] = TRUE;
				}
				
				if ( ! empty($data['member_groups']) && ! in_array($this->session->userdata('group_id'), preg_split('#\s*[,|]\s*#', trim($data['member_groups']))))
				{
					$data['metadata']['no_access'] = TRUE;
				}
				
				if ( ! empty($data['member_ids']) && ! in_array($this->session->userdata('member_id'), preg_split('#\s*[,|]\s*#', trim($data['member_ids']))))
				{
					$data['metadata']['no_access'] = TRUE;
				}

				foreach ($data['metadata'] as $cond => $value)
				{
					if ( ! in_array($cond, array('entry_id', 'entry_date', 'expiration_date', 'valid')) && $value === TRUE)
					{
						$data['metadata']['valid'] = FALSE;
						break;
					}
				}
				
				$plugin = $this->api_cartthrob_discount_plugins->set_plugin($data['type'])->plugin();

				if ($data['metadata']['valid'] && method_exists($plugin, 'validate') && is_callable(array($plugin, 'validate')))
				{
					if ( ! $data['metadata']['valid'] = $plugin->set_plugin_settings($data)->validate())
					{
						$data['metadata']['msg'] = $plugin->error();
					}
				}
			}
			else
			{
				$data['metadata']['invalid'] = TRUE;
			}
		}
		
		$this->coupon_code_data[$coupon_code] = $data;
		return $data;
	}
	
	public function process_coupon_codes()
	{
		if (! $this->cartthrob->cart->coupon_codes())
		{
			return $this; 
		}
		foreach ($this->cartthrob->cart->coupon_codes() as $coupon_code)
		{
			$data = $this->coupon_code_model->get_coupon_code_data($coupon_code);

			$entry_id = (isset($data['metadata']['entry_id'])) ? $data['metadata']['entry_id'] : FALSE;

			if ($entry_id && $this->cartthrob->store->config('coupon_code_type'))
			{
				unset($data['metadata']);
				
				$data['used_by'] = (isset($data['used_by'])) ? $data['used_by'].'|'.$this->session->userdata('member_id') : $this->session->userdata('member_id');
				
				$data['discount_limit'] = (isset($data['discount_limit']) && strlen($data['discount_limit']) > 0) ? $data['discount_limit'] - 1 : '';
				
				$this->db->update('channel_data', array('field_id_'.$this->cartthrob->store->config('coupon_code_type') => base64_encode(serialize($data))), array('entry_id' => $entry_id));
			}
		}
		
		return $this;
	}
}
