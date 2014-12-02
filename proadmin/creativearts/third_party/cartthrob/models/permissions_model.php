<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Permissions_model extends CI_Model
{
	protected $columns = array(
		'member_id',
		'sub_id',
		'permission',
		'item_id',
		'order_id',
	);
	
	public function __construct()
	{
		$this->load->model('cartthrob_field_model');
		$this->load->model('cartthrob_entries_model');
		$this->load->helper('data_formatting');
	}
	
	public function update($data, $id = NULL)
	{
		$insert = array();
		
		foreach ($this->columns as $key)
		{
			// check if set... and not totally blank. 0 values are OK. As are 000 values
			if (isset($data[$key]) && $data[$key] !== NULL)
			{
				$insert[$key] = $data[$key]; 
			}
		}
		
		if ($id)  
		{     
			$this->db->update('cartthrob_permissions', $insert, array('id' => $id));
		} 
		else
		{
			$this->db->insert('cartthrob_permissions', $insert);
			
			$id = $this->db->insert_id(); 
		}
		
		return $id; 
	}
	
	public function delete($id, $sub_id = NULL, $member_id = NULL, $item_id = NULL, $order_id = NULL)
	{
		if ($order_id)
		{
			$this->db->delete('cartthrob_permissions', array('order_id' => $order_id));
		}
		
		if ($item_id)
		{
			$this->db->delete('cartthrob_permissions', array('item_id' => $item_id));
		}
		
		if ($sub_id)
		{
			$this->db->delete('cartthrob_permissions', array('sub_id' => $sub_id));
		}
		else if ($member_id)
		{
			$this->db->delete('cartthrob_permissions', array('member_id' => $member_id));
		}
		else
		{
			$this->db->delete('cartthrob_permissions', array('id' => $id));
		}
	}
	
	public function get($params = array(), $limit = NULL, $offset = 0, $exclude_subs = FALSE)
	{
		$this->load->helper('array');

		if ($id = element('id', $params))
		{
			if ( ! is_array($id))
			{
				$this->db->where('cartthrob_permissions.id', $id);
			}
			else
			{
				$this->db->where_in('cartthrob_permissions.id', $id);
			}
		}
		if ($permission_id = element('permission', $params))
		{
			if ( ! is_array($permission_id))
			{
				$this->db->where('cartthrob_permissions.permission', $permission_id);
			}
			else
			{
				$this->db->where_in('cartthrob_permissions.permission', $permission_id);
			}
		}
		if ($order_id = element('order_id', $params))
		{
			if ( ! is_array($order_id))
			{
				$this->db->where('cartthrob_permissions.order_id', $order_id);
			}
			else
			{
				$this->db->where_in('cartthrob_permissions.order_id', $order_id);
			}
		}
		
		if ($item_id = element('item_id', $params))
		{
			if ( ! is_array($item_id))
			{
				$this->db->where('cartthrob_permissions.item_id', $item_id);
			}
			else
			{
				$this->db->where_in('cartthrob_permissions.item_id', $item_id);
			}
		}
		
		if ($sub_id = element('sub_id', $params))
		{
			if ( ! is_array($sub_id))
			{
				$this->db->where('cartthrob_permissions.sub_id', $sub_id);
			}
			else
			{
				$this->db->where_in('cartthrob_permissions.sub_id', $sub_id);
			}
			# making it possible to exclude the subs data from the returned data
			if(!$exclude_subs)
			{
				$this->db->join('cartthrob_subscriptions', 'cartthrob_subscriptions.id = cartthrob_permissions.sub_id', 'right');			
			}
		}
 
		if ($member_id = element('member_id', $params))
		{
			if ( ! is_array($member_id))
			{
				$this->db->where('cartthrob_permissions.member_id', $member_id);
			}
			else
			{
				$this->db->where_in('cartthrob_permissions.member_id', $member_id);
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
		
		$query = $this->db->order_by('cartthrob_permissions.member_id', 'asc')
				  ->order_by('cartthrob_permissions.sub_id', 'desc')
				  ->order_by('cartthrob_permissions.id', 'desc')
				  ->get('cartthrob_permissions');
		
 
		$data = $query->result_array();
		
		$query->free_result();

		return $data;
	}
 }
