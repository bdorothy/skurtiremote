<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tax_model extends CI_Model
{
	public $cartthrob, $store, $cart;
	private $table = "cartthrob_tax"; 
	
	public function __construct()
	{
		$this->load->model('cartthrob_field_model');
		$this->load->model('cartthrob_entries_model');
		$this->load->library('cartthrob_loader');
		$this->load->helper('data_formatting');
		$this->cartthrob_loader->setup($this);
	}
	
	public function create($sent_data = array(), $id = NULL)
	{
 		$fields = $this->db->list_fields($this->table);
		foreach ($fields as $field)
		{
		 	$db_keys[$field]	= TRUE; 
		}
		$data =array_intersect_key($sent_data, $db_keys); 
		if (isset($data['percentage']))
		{
			// adding zero = lazy number casting
			$data['percent'] +=0; 

	  		if ($data['percent'] > 100)
			{
				$data['percent'] = 100; 
			}
			if ($data['percent'] < 0)
			{
				$data['percent'] = 0; 
			}
		}
		
		if ($id)
		{
			$this->db->where('id', $id)->update($this->table,$data );
		}
		else
		{
			$this->db->insert($this->table, $data);
		}
		return TRUE;
	}
	public function delete($id= NULL)
	{
		if ($id)
		{
			$this->db->delete($this->table, array('id' => $id)); 
		}
		return TRUE;
	}
	
 	public function update($data = array(), $id)
	{
		return $this->create($data, $id); 
	}
	public function get_by_location($location_data = array(), $limit="100", $order_by = NULL)
	{
		$db_keys = array(); 
 		$fields = $this->db->list_fields($this->table);
		foreach ($fields as $field)
		{
		 	$db_keys[$field]	= TRUE; 
		}
		$search_fields =array_intersect_key($location_data, $db_keys);
		
 		foreach ($search_fields as $key => $data)
		{
			if (!isset($or))
			{
				$this->db->where($key, $data); 
				$or =TRUE; 
			}
			else
			{
				$this->db->or_where($key, $data); 
			}
		}
 		if ($order_by)
		{
			$this->db->order_by($order_by); 
		} 
		
		$this->db->limit($limit); 
		$this->db->select("*"); 
		$query = $this->db->get($this->table); 

 		return $query->result_array(); 
	}
	public function read($id = NULL, $limit=100, $offset = 0, $order_by="country" )
	{
		if ($id===FALSE){$id=NULL;}
		if ($limit===FALSE){$limit=100;}
		if ($order_by===FALSE){$order_by="country";}
		if ($offset===FALSE){$offset=0;}

		if($id)
		{
			$query = $this->db->select('*')->
						limit(1)->
						where('id' , $id)->
						get($this->table);
		}
		else
		{
			$query = $this->db->select('*')->limit($limit)->offset($offset)->order_by($order_by)->get($this->table);
		}
		$data = array();

		$fields = $this->db->list_fields($this->table);
		foreach ($query->result() as $row)
		{
			foreach ($fields as $field)
			{
				if ($field=="percent")
				{
				 	$tax[$field]	=  (double) $row->$field;
				}
				else
				{
					$tax[$field]	=  $row->$field;
				}
			}
			
			$data[] = $tax;
 		}
		return $data; 
	}
}
