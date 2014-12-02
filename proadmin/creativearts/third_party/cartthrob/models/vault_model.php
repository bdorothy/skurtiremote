<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Vault_model extends CI_Model
{
	protected $columns = array(
		'customer_id',
		'token',
		'order_id',
		'member_id',
		'gateway',
		'last_four'
	);
	
	public function __construct()
	{
		$this->load->model('cartthrob_field_model');
		$this->load->model('cartthrob_entries_model');
		$this->load->helper('data_formatting');
	}

    public function update_vault($data, $id = NULL)
    {
        return $this->update($data, $id);
    }
	
	public function update($data, $id = NULL)
	{
		foreach ($this->columns as $key)
		{
			//  gotta use array key exists to allow NULL values to pass in
			if (array_key_exists($key, $data))
			{
				if ($key =="gateway")
				{
					if (strncmp($data[$key], 'Cartthrob_', 10) === 0)
					{
						
					}
					else
					{
						$data[$key] = 'Cartthrob_'. $data[$key]; 
					}
				}
				$insert[$key] = $data[$key]; 
			}
		}
		
		if ($id)  
		{     
			$this->db->update('cartthrob_vault', $insert, array('id' => $id));
		} 
		else
		{
		   	$this->db->insert('cartthrob_vault', $insert);
			
			$id = $this->db->insert_id(); 
		}
		
		return $id; 
	}
	
	public function delete($id = NULL, $order_id = NULL, $member_id = NULL)
	{
		if ($order_id)
		{
			$this->db->delete('cartthrob_vault', array('order_id' => $order_id));
		}
		else if ($member_id)
		{
			$this->db->delete('cartthrob_vault', array('member_id' => $member_id));
		}
		else if ($id)
		{
			$this->db->delete('cartthrob_vault', array('id' => $id));
		}
		// @TODO error
	}
	
	public function get_member_vault($member_id, $gateway = NULL)
	{
		$params = array('member_id' => $member_id);
		
		if ( ! is_null($gateway))
		{
			$params['gateway'] = $gateway;
		}
		
		$vaults = $this->get_vaults($params, 1);
		
		$member_vault = array_shift($vaults);
		return  $member_vault ? $member_vault : FALSE;
	}
	
	public function get_member_vault_id($member_id, $gateway = NULL)
	{
		$vault = $this->get_member_vault($member_id, $gateway);
		
		return isset($vault['id']) ? $vault['id'] : FALSE;
	}
	
	public function get_member_vaults($member_id, $limit = NULL, $offset = 0)
	{
		return $this->get_vaults(array('member_id' => $member_id), $limit, $offset);
	}

	public function get_vault($id)
	{
		$vaults = $this->get_vaults($id);

		return $vaults ? array_shift($vaults) : FALSE;
	}
	
	public function get_vaults($params = array(), $limit = NULL, $offset = 0)
	{
		//get by id
		if ( ! is_array($params))
		{
			if ( ! $params)
			{
				return array();
			}
			
			$params = array('id' => $params);
		}

		foreach (array('id', 'order_id', 'member_id', 'gateway') as $field)
		{
			if (isset($params[$field]))
			{
				if ( ! is_array($params[$field]))
				{
					$this->db->where($field, $params[$field]);
				}
				else
				{
					$this->db->where_in($field, $params[$field]);
				}
			}
		}

		if (isset($params['limit']))
		{
			$limit = $params['limit'];
			
			if (isset($params['offset']))
			{
				$offset = $params['offset'];
			}
		}
		
		if ( ! is_null($limit))
		{
			$this->db->limit((int) $limit, (int) $offset);
		}
		
		$query = $this->db->order_by('member_id', 'asc')
				  ->order_by('order_id', 'desc')
				  ->order_by('id', 'desc')
				  ->get('cartthrob_vault');
		
		$vaults = $query->result_array();
		
		$query->free_result();

		return $vaults;
	}
	
	/**
	 * get_members_with_vaults
	 *
	 * gets an array containing member_id => screen_name list of all users with vaults. 
	 * 
	 * @return array
	 * @author Chris Newton
	 */
	public function get_members_with_vaults()
	{
		$members = $this->db->select("members.member_id, members.screen_name")
		   						->from('members')
								->order_by('members.screen_name', 'ASC')
								->order_by('members.username', 'ASC')
		   						->join('cartthrob_vault', 'cartthrob_vault.member_id = members.member_id')
		   						->where_not_in('members.group_id', array(2,3,4))
								->get();
	
		$member_list = array();
		if ($members->result_array())
		{
			if ($memb = $members->result_array() )
			{
				foreach ($memb as $member)
				{
					$member_list[$member['member_id']] = $member['screen_name']; 
				}
			}
		}
		return $member_list; 
	}
}
