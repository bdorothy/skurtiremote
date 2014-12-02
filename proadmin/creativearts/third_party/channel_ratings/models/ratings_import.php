<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Ratings Import Model File
 *
 * @package			DevDemon_ChannelRatings
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 */
class Ratings_import
{
	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		// Creat EE Instance
		$this->EE =& get_instance();
		$this->site_id = $this->EE->config->item('site_id');

		$this->EE->load->helper('url');

		$this->EE->config->load('ratings');
		$this->TYPES = $this->EE->config->item('cr_rating_types');
		$this->TYPES_INV = array_flip($this->TYPES);
	}

	// ********************************************************************************* //

	/**
	 * Get Import Candidates
	 *
	 * @access public
	 * @return array
	 */
	public function get_import_candidates()
	{
		$data= array();

		//----------------------------------------
		// Grab all channels
		//----------------------------------------
		$channels = array();
		$query = $this->EE->db->query("SELECT channel_id, channel_title FROM exp_channels WHERE site_id = {$this->site_id}");
		foreach ($query->result() as $row) $channels[$row->channel_id] = $row->channel_title;

		/** ----------------------------------------
		/** Solspace Rating
		/** ----------------------------------------*/
		if ($this->EE->db->table_exists('exp_ratings') !== FALSE)
		{
			$data['solspace_rating'] = array();

			//----------------------------------------
			// How Many Ratings?
			//----------------------------------------
			$query = $this->EE->db->query("SELECT COUNT(*) as total_ratings FROM exp_ratings WHERE status = 'open'");
			$data['solspace_rating']['totals']['ratings'] = $query->row('total_ratings');

			//----------------------------------------
			// How Many Fields?
			//----------------------------------------
			$query = $this->EE->db->query("SELECT COUNT(*) as total_fields FROM exp_rating_fields WHERE field_type = 'number'");
			$data['solspace_rating']['totals']['fields'] = $query->row('total_fields');

			//----------------------------------------
			// How Many Reviews?
			//----------------------------------------
			$query = $this->EE->db->query("SELECT COUNT(*) as total_reviews FROM exp_ratings WHERE status = 'o' AND review != '' ");
			$data['solspace_rating']['totals']['reviews'] = $query->row('total_reviews');

			//----------------------------------------
			// Collect all channels from exp_ratings
			//----------------------------------------
			$data['solspace_rating']['ratings_channels'] = array();
			$query = $this->EE->db->query("SELECT DISTINCT(weblog_id) FROM exp_ratings WHERE status = 'open' AND review = ''");

			foreach ($query->result() as $channel)
			{
				$data['solspace_rating']['ratings_channels'][$channel->weblog_id] = $channels[$channel->weblog_id];
			}

		}

		return $data;
	}

	// ********************************************************************************* //

	/**
	 * Import Solspace Rating Fields!
	 *
	 * @access public
	 * @return void
	 */
	public function import_ss_rating_fields()
	{
		//----------------------------------------
		// Grab all form_name
		//----------------------------------------
		$collections = array();
		$query = $this->EE->db->query("SELECT DISTINCT(form_name) FROM exp_ratings WHERE status = 'open' AND review = ''");

		foreach ($query->result() as $row) $collections[] = $row->form_name;
		$query->free_result();

		//----------------------------------------
		// Grab all fields
		//----------------------------------------
		$this->EE->db->select('field_name, field_label, field_required');
		$this->EE->db->from('exp_rating_fields');
		$this->EE->db->where('field_type', 'number');
		$this->EE->db->order_by('field_order', 'asc');
		$query = $this->EE->db->get();


		//----------------------------------------
		// Loop over all results! and Import
		//----------------------------------------
		foreach ($collections as $coll)
		{
			foreach ($query->result() as $row)
			{
				$this->EE->db->set('site_id', $this->site_id);
				$this->EE->db->set('title', $row->field_label);
				$this->EE->db->set('short_name', ($row->field_name != FALSE) ? url_title(trim($row->field_name), 'underscore', TRUE) : url_title(trim($row->field_label), 'underscore', TRUE)   );
				$this->EE->db->set('collection', $coll);
				$this->EE->db->set('required', (($row->field_required == 'n') ? 0 : 1) );
				$this->EE->db->insert('exp_channel_ratings_fields');
			}
		}
	}

	// ********************************************************************************* //

	public function import_ss_ratings($channel_id)
	{
		$this->EE->load->model('ratings_model');

		set_time_limit(0);
		@ini_set('memory_limit', '64M');
		@ini_set('memory_limit', '96M');
		@ini_set('memory_limit', '128M');
		@ini_set('memory_limit', '160M');
		@ini_set('memory_limit', '192M');

		/** ----------------------------------------
		/** Grab All Form Names!
		/** ----------------------------------------*/
		$collections = array();
		$query = $this->EE->db->query("SELECT DISTINCT(form_name) FROM exp_ratings WHERE status = 'open' AND review = ''");

		foreach ($query->result() as $row) $collections[] = $row->form_name;
		$query->free_result();


		/** ----------------------------------------
		/** Loop over all Form_Names/Collections
		/** And grab all fields
		/** ----------------------------------------*/
		foreach ($collections as $coll)
		{
			//----------------------------------------
			// Grab all fields from this
			//----------------------------------------
			$fields = array();
			$this->EE->db->select('short_name, field_id');
			$this->EE->db->from('exp_channel_ratings_fields');
			$this->EE->db->where('collection', $coll);
			$query = $this->EE->db->get();

			foreach ($query->result() as $row) $fields[$row->field_id] = $row->short_name;
			$query->free_result();

			//----------------------------------------
			// Grab all ratings (lets hope it can manage it!)
			//----------------------------------------
			$this->EE->db->select('rating, entry_id, ip_address, rating_date, rating_author_id, ' . implode(',', $fields) );
			$this->EE->db->from('exp_ratings');
			$this->EE->db->where('form_name', $coll);
			$this->EE->db->where('weblog_id', $channel_id);
			$query = $this->EE->db->get();


			//----------------------------------------
			// Create new rating!!
			//----------------------------------------
			foreach ($query->result() as $row)
			{
				// Average user review!
				$total = array();
				$total['num_fields'] = 0;
				$total['ratings'] = 0;

				// Loop over all Fields!
				foreach ($fields as $field_id => $field_name)
				{
					$data = array(	'site_id'		=>	$this->site_id,
								'entry_id'		=>	$row->entry_id,
								'item_id'		=>	0,
								'channel_id'	=>	$channel_id,
								'ip_address'	=>	sprintf("%u", ip2long($row->ip_address)),
								'collection'	=>	$coll,
								'field_id'		=>	$field_id,
								'rating'		=>	$row->$field_name,
								'rating_author_id' => $row->rating_author_id,
								'rating_date'	=> $row->rating_date,
								'rating_type'	=>	$this->TYPES['entry'], // Entry
								'rating_status'	=>	1, // Open
						);

					$this->EE->db->insert('exp_channel_ratings', $data);

					$total['ratings'] += $data['rating'];
					$total['num_fields']++;
				}

				//----------------------------------------
				// Insert Average Stats
				//----------------------------------------
				$data = array(	'site_id'		=>	$this->site_id,
								'entry_id'		=>	$row->entry_id,
								'item_id'		=>	0,
								'channel_id'	=>	$channel_id,
								'ip_address'	=>	sprintf("%u", ip2long($row->ip_address)),
								'collection'	=>	$coll,
								'field_id'		=>	0,
								'rating'		=> ($total['ratings'] / $total['num_fields']),
								'rating_author_id' => $row->rating_author_id,
								'rating_date'	=> $row->rating_date,
								'rating_type'	=>	$this->TYPES['entry'], // Entry
								'rating_status'	=>	1, // Open
				);

				$this->EE->db->insert('exp_channel_ratings', $data);
			}



			$query->free_result();
		}




		exit('Done?');
	}

	// ********************************************************************************* //

	// TEMP SOLUTION FOR EE 2.1.1 SIGH!!!
	public function _assign_libraries()
	{

	}


} // END CLASS

/* End of file ratings_import.php  */
/* Location: ./system/expressionengine/third_party/channel_ratings/models/ratings_import.php */