<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Order_management_model extends CI_Model
{
	public $cartthrob;
	
	public function __construct()
	{
		$this->load->library('cartthrob_loader');
		$this->cartthrob =& get_instance()->cartthrob;
		$this->load->model('cartthrob_entries_model');
	}
	
	public function get_related_orders_by_item($entry_id)
	{
		$this->db->select("order_id");
		$this->db->from("cartthrob_order_items"); 
		$this->db->where("entry_id", $entry_id);
		$query = $this->db->get(); 
		if ($query->result() && $query->num_rows() > 0)
		{
			return $query->result_array(); 
		}
		return array();
	} 
	
	public function is_member($member_id= FALSE)
	{
		$oldest_superadmin = $this->db->select('member_id')
					      ->where('group_id', 1)
					      ->order_by('member_id', 'asc')
					      ->limit(1)
					      ->get('members')
					      ->row('member_id');
		
		$default_id = $this->cartthrob->store->config('default_member_id'); 
		
		if ($member_id && $member_id != $default_id && $member_id != $oldest_superadmin)	
		{
			return TRUE; 
		}		
		return FALSE; 
					
	}
	public function get_purchased_products($where =array(), $order_by= "total_sales", $sort="DESC", $limit=NULL, $offset=NULL, $like = array(), $status = NULL)
	{
		if ($limit)
		{
			$this->db->limit($limit,$offset); 
		}
		$this->db->select_sum($this->db->dbprefix."cartthrob_order_items.price * ". $this->db->dbprefix."cartthrob_order_items.quantity"  , "total_sales");
		$this->db->select_sum($this->db->dbprefix."cartthrob_order_items.quantity", "total_quantity");
		$this->db->select("cartthrob_order_items.*");
		
		$this->db->from("cartthrob_order_items"); 
		// need to get channel data for this item
		$this->db->from("channel_titles AS ct"); 
		$this->db->where("ct.entry_id", $this->db->dbprefix."cartthrob_order_items.entry_id", FALSE);
		
		if (! $status)
		{
	 		$status = $this->config->item('cartthrob:orders_default_status') ? $this->config->item('cartthrob:orders_default_status')  : 'open';
		}
		elseif (strtolower($status) == "any")
		{
			$status = NULL; 
		} 
		// now we need to ONLY get the completed items... again from channel_titles, which is why we aliased it previously a few lines above.
		$this->db->join('channel_titles',  $this->db->dbprefix."channel_titles.entry_id = ".$this->db->dbprefix."cartthrob_order_items.order_id");		
		
		if ($status)
		{
			$this->db->where($this->db->dbprefix.'channel_titles.status', $status);	
		}

		if (!empty($where))
		{
			$this->db->where($where); 
		}
		elseif (!empty($like))
		{
			//@NOTE: if $like is an array, we may need to modify this to loop through and use LIKE on the first item and OR_LIKE on additional ones. 
			$this->db->like($like); 
		}
		if ($order_by)
		{
			$this->db->order_by($order_by, $sort); 
		}
		
		$this->db->group_by("cartthrob_order_items.entry_id"); 
		$this->db->group_by("cartthrob_order_items.price"); 
 		
 		$query = $this->db->get();
		
		if ($query->result() && $query->num_rows() > 0)
		{
			return $query->result_array(); 
		}
		return array();
	}
	public function get_purchased_items_by_order($order_id)
	{
		if ( ! $this->cartthrob->store->config('purchased_items_channel'))
		{
			return FALSE;
		}
		
		if ($this->db->field_exists('field_id_'.$this->cartthrob->store->config('purchased_items_order_id_field'), 'channel_data'))
		{
			$query = $this->db->select("entry_id")
		    			->where('field_id_'.$this->cartthrob->store->config('purchased_items_order_id_field'), $order_id)
					  	->get('channel_data');

			$entry_ids = array(); 
			foreach ($query->result() as $row)
			{
				$entry_ids[] = $row->entry_id; 
			}

			return $entry_ids;
		}
		else
		{
			return FALSE; 
		}

	}
	
	public function get_customer_count()
	{
		if ( ! $this->cartthrob->store->config('orders_channel') ||  ! $this->cartthrob->store->config('orders_customer_email') )
		{
			return FALSE;
		}
		
		if ($this->db->field_exists('field_id_'.$this->cartthrob->store->config('orders_customer_email'), 'channel_data'))
		{
			// this returns potentially more customers than are output, because some of the author ids might also contain different email addresses. get_customers is more accurate
			$this->db->select('COUNT(DISTINCT(field_id_'.$this->cartthrob->store->config('orders_customer_email').')) AS member_count', TRUE);
			$this->db->from('channel_data'); 
			$this->db->join('channel_titles', 'channel_titles.entry_id = channel_data.entry_id');
			$this->db->where('channel_titles.channel_id', $this->cartthrob->store->config('orders_channel'));

			$data = $this->db->get()->row_array();

			return $data['member_count']; 
			
		}
		return FALSE; 
	}
	
	public function get_customers($where =array(), $order_by= "entry_date", $sort="DESC", $limit=NULL, $offset=NULL)
	{
		if ($this->db->field_exists('field_id_'.$this->cartthrob->store->config('orders_total_field'), 'channel_data'))
		{
			$this->db->select("COUNT(".$this->db->dbprefix."channel_data.entry_id) AS order_count"); 
			$this->db->select_sum('channel_data.field_id_'.$this->cartthrob->store->config('orders_total_field'), "order_total");
			$this->db->select("channel_data.*", FALSE); 
			$this->db->select("channel_titles.author_id", "author_id"); 
			$this->db->select_min("channel_titles.entry_date", "order_first"); 
			$this->db->select_max("channel_titles.entry_date", "order_last"); 
			$this->db->where('channel_titles.channel_id', $this->cartthrob->store->config('orders_channel'));

	  		if ($where)
			{
				$this->db->where($where); 
			}

			if ($order_by)
			{
				if (is_array($order_by))
				{
					foreach($order_by as $key => $order_value)
					{
						$sort_item = "asc"; 
						if (!empty($sort[$key]))
						{
							$sort_item = $sort[$key]; 
						}
						$this->db->order_by($order_value, $sort_item); 
					}
				}
				else
				{
					$this->db->order_by($order_by, $sort); 
				}
			}

			if ($limit)
			{
				$this->db->limit($limit, $offset); 
			}

			$this->db->where($this->db->dbprefix."channel_data.entry_id", $this->db->dbprefix."channel_titles.entry_id", FALSE);
			
			if ($this->db->field_exists('field_id_'.$this->cartthrob->store->config('orders_customer_email'), 'channel_data'))
			{
				$this->db->where($this->db->dbprefix."channel_data.field_id_".$this->cartthrob->store->config('orders_customer_email'). " !=", ""); 
			}

			$this->db->from("channel_data"); 
			$this->db->from("channel_titles"); 

			// group by email address
			$this->db->group_by("channel_data.field_id_".$this->cartthrob->store->config('orders_customer_email'),'author_id'); 

	 		$query = $this->db->get();

			if ($query->result() && $query->num_rows() > 0)
			{
				return $query->result_array(); 
			}
		}

		return array(); 
	}
	
	
	public function get_orders($where =array(), $order_by= "entry_date", $sort="DESC", $limit=NULL, $offset=NULL)
	{
		$this->db->select("COUNT(".$this->db->dbprefix."channel_data.entry_id) AS order_count"); 
		
		if ($this->db->field_exists('field_id_'.$this->cartthrob->store->config('orders_total_field'), 'channel_data'))
		{
			$this->db->select_sum('channel_data.field_id_'.$this->cartthrob->store->config('orders_total_field'), "order_total");
		}
		$this->db->select("channel_data.*"); 
		$this->db->select("channel_titles.*"); 
 		$this->db->where('channel_titles.channel_id', $this->cartthrob->store->config('orders_channel'));

		$this->db->select_min("channel_titles.entry_date", "order_first"); 
		$this->db->select_max("channel_titles.entry_date", "order_last"); 
  		if ($where)
		{
			$where_in = array(); 
			foreach ($where as $key => $value)
			{
				if (is_array($value))
				{
					$where_in[$key] = $value; 
					unset($where[$key]); 
				}
			}
			$this->db->where($where); 
			
			foreach ($where_in as $key => $value)
			{
				$this->db->where_in($key, $value); 
			}
		}

		if ($order_by)
		{
			if (is_array($order_by))
			{
				foreach($order_by as $key => $order_value)
				{
					$sort_item = "asc"; 
					if (!empty($sort[$key]))
					{
						$sort_item = $sort[$key]; 
					}
					$this->db->order_by($order_value, $sort_item); 
				}
			}
			else
			{
				$this->db->order_by($order_by, $sort); 
			}
		}
 
		if ($limit)
		{
			$this->db->limit($limit, $offset); 
		}
		$this->db->where($this->db->dbprefix."channel_data.entry_id", $this->db->dbprefix."channel_titles.entry_id", FALSE);
		// this is just about the only difference from the customer item above
		//$this->db->where($this->db->dbprefix."channel_data.field_id_".$this->cartthrob->store->config('orders_customer_email'). " !=", ""); 
		$this->db->group_by("channel_titles.title"); 
		
		$this->db->from("channel_data"); 
		$this->db->from("channel_titles"); 
		
 		$query = $this->db->get();

 		if ($query->result() && $query->num_rows() > 0)
		{
			return $query->result_array(); 
		}
		return array(); 
	}
}
