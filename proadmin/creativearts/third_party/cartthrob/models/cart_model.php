<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cart_model extends CI_Model
{
	/**
	 * @var int The percent chance that garbage collection will occur
	 */
	protected $garbage_collection_probability = 5;
	
	public function create_cart($cart = array())
	{
 		return $this->update_cart(NULL, $cart);
	}
	
	public function read_cart($id)
	{
		if ( ! $this->config->item('cartthrob:garbage_collection_cron') && rand(1, 100) <= $this->garbage_collection_probability)
		{
			$this->garbage_collection();
		}
		
		$this->load->helper('data_formatting');
		
		$query = $this->db->select('cart')
				->from('cartthrob_cart')
				->where('id', $id)
				->limit(1)
				->get();

		if ($query->row('cart'))
		{
			$this->load->library('encrypt');
			
			$cart = _unserialize($this->encrypt->decode($query->row('cart')));
			
			$cart['id'] = $id;
			
			return $cart;
 		}
		
		return NULL;		
	}
	
	public function update_cart($id = NULL, $cart = array(), $url = NULL)
	{
		$this->load->library('encrypt');
		
		$data = array(
			'cart' => $this->encrypt->encode(serialize($cart)),
			'timestamp' => time(),
		);
		
		if ($url)
		{
			$data['url'] = $url; 
		}
		
		if ($id)
		{
			$query = $this->db->select('cart')
					->from('cartthrob_cart')
					->where('id', $id)
					->limit(1)
					->get();

			if ($query->num_rows())
			{
				$this->db->update('cartthrob_cart', $data, array('id' => $id));
			}
			else
			{
				$this->db->insert('cartthrob_cart', $data);
				
				$id = $this->db->insert_id(); 
			}
		}
		else
		{
			$this->db->insert('cartthrob_cart', $data);
			
			$id = $this->db->insert_id(); 
		}
		
		return $id; 
	}
	
	// deletes the entire cart, including customer information
 	public function delete_cart($id)
	{
		$this->db->delete('cartthrob_cart', array('id' => $id));
	}
	
	/**
	 * Deletes carts no longer associated with a session
	 * 
	 * @return void
	 */
	protected function garbage_collection()
	{
		$this->db->query('DELETE `'.$this->db->dbprefix('cartthrob_cart').'`
				  FROM `'.$this->db->dbprefix('cartthrob_cart').'`
				  LEFT OUTER JOIN `'.$this->db->dbprefix('cartthrob_sessions').'`
				  ON `'.$this->db->dbprefix('cartthrob_cart').'`.`id` = `'.$this->db->dbprefix('cartthrob_sessions').'`.`cart_id`
				  WHERE `'.$this->db->dbprefix('cartthrob_sessions').'`.`cart_id` IS NULL');
	}
}
