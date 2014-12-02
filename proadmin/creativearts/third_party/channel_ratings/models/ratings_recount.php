<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Ratings Recount Model File
 *
 * @package			DevDemon_ChannelRatings
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 */
class Ratings_recount
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
		$this->site_id = $this->EE->ratings_helper->getSiteId();

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
	public function get_recount_candidates()
	{
		$data= array();

		/** ----------------------------------------
		/** Entry
		/** ----------------------------------------*/
		$entries = array();
		$query = $this->EE->db->select('DISTINCT(entry_id)', FALSE)->from('exp_channel_ratings')->where('field_id', 0)->where('rating_type', $this->TYPES['entry'])->get();
		foreach($query->result() as $row) $entries[] = $row->entry_id;
		$query->free_result();
		sort($entries);

		// Split the array in pieces
		$pieces = floor(count($entries) / 50);
		$entries = $this->array_split($entries, $pieces);

		// Loop over all arrays and insert data
		foreach ($entries as $block)
		{
			$data['entry'][] = array( 1 => reset($block), 2 => end($block) );
		}

		/** ----------------------------------------
		/** Comment "Review"
		/** ----------------------------------------*/
		$entries = array();
		$query = $this->EE->db->select('DISTINCT(entry_id)', FALSE)->from('exp_channel_ratings')->where('field_id', 0)->where('rating_type', $this->TYPES['comment_review'])->get();
		foreach($query->result() as $row) $entries[] = $row->entry_id;
		$query->free_result();
		sort($entries);

		// Split the array in pieces
		$pieces = floor(count($entries) / 50);
		$entries = $this->array_split($entries, $pieces);

		// Loop over all arrays and insert data
		foreach ($entries as $block)
		{
			$data['comment_review'][] = array( 1 => reset($block), 2 => end($block) );
		}

		/** ----------------------------------------
		/** Comment "Review"
		/** ----------------------------------------*/
		$entries = array();
		$query = $this->EE->db->select('item_id')->from('exp_channel_ratings')->where('field_id', 0)->where('rating_type', $this->TYPES['comment_entry'])->get();
		foreach($query->result() as $row) $entries[] = $row->item_id;
		$query->free_result();
		sort($entries);

		// Split the array in pieces
		$pieces = floor(count($entries) / 50);
		$entries = $this->array_split($entries, $pieces);

		// Loop over all arrays and insert data
		foreach ($entries as $block)
		{
			$data['comment_entry'][] = array( 1 => reset($block), 2 => end($block) );
		}

		/** ----------------------------------------
		/** Members
		/** ----------------------------------------*/
		$entries = array();
		$query = $this->EE->db->select('item_id')->from('exp_channel_ratings')->where('field_id', 0)->where('rating_type', $this->TYPES['member'])->get();
		foreach($query->result() as $row) $entries[] = $row->item_id;
		$query->free_result();
		sort($entries);

		// Split the array in pieces
		$pieces = floor(count($entries) / 50);
		$entries = $this->array_split($entries, $pieces);

		// Loop over all arrays and insert data
		foreach ($entries as $block)
		{
			$data['member'][] = array( 1 => reset($block), 2 => end($block) );
		}

		/** ----------------------------------------
		/** Channel Images
		/** ----------------------------------------*/
		$entries = array();
		$query = $this->EE->db->select('item_id')->from('exp_channel_ratings')->where('field_id', 0)->where('rating_type', $this->TYPES['channel_images'])->get();
		foreach($query->result() as $row) $entries[] = $row->item_id;
		$query->free_result();
		sort($entries);

		// Split the array in pieces
		$pieces = floor(count($entries) / 50);
		$entries = $this->array_split($entries, $pieces);

		// Loop over all arrays and insert data
		foreach ($entries as $block)
		{
			$data['channel_images'][] = array( 1 => reset($block), 2 => end($block) );
		}

		/** ----------------------------------------
		/** Channel Files
		/** ----------------------------------------*/
		$entries = array();
		$query = $this->EE->db->select('item_id')->from('exp_channel_ratings')->where('field_id', 0)->where('rating_type', $this->TYPES['channel_files'])->get();
		foreach($query->result() as $row) $entries[] = $row->item_id;
		$query->free_result();
		sort($entries);

		// Split the array in pieces
		$pieces = floor(count($entries) / 50);
		$entries = $this->array_split($entries, $pieces);

		// Loop over all arrays and insert data
		foreach ($entries as $block)
		{
			$data['channel_files'][] = array( 1 => reset($block), 2 => end($block) );
		}

		/** ----------------------------------------
		/** Channel Videos
		/** ----------------------------------------*/
		$entries = array();
		$query = $this->EE->db->select('item_id')->from('exp_channel_ratings')->where('field_id', 0)->where('rating_type', $this->TYPES['channel_videos'])->get();
		foreach($query->result() as $row) $entries[] = $row->item_id;
		$query->free_result();
		sort($entries);

		// Split the array in pieces
		$pieces = floor(count($entries) / 50);
		$entries = $this->array_split($entries, $pieces);

		// Loop over all arrays and insert data
		foreach ($entries as $block)
		{
			$data['channel_videos'][] = array( 1 => reset($block), 2 => end($block) );
		}

		/** ----------------------------------------
		/** Brilliant Retail Products
		/** ----------------------------------------*/
		$entries = array();
		$query = $this->EE->db->select('item_id')->from('exp_channel_ratings')->where('field_id', 0)->where('rating_type', $this->TYPES['br_product'])->get();
		foreach($query->result() as $row) $entries[] = $row->item_id;
		$query->free_result();
		sort($entries);

		// Split the array in pieces
		$pieces = floor(count($entries) / 50);
		$entries = $this->array_split($entries, $pieces);

		// Loop over all arrays and insert data
		foreach ($entries as $block)
		{
			$data['br_product'][] = array( 1 => reset($block), 2 => end($block) );
		}

		return $data;
	}

	// ********************************************************************************* //

	public function recount_items($rating_type, $data)
	{
		$this->EE->db->save_queries = TRUE;

		$this->EE->load->model('ratings_model');

		/** ----------------------------------------
		/** Get START/END Item IDS
		/** ----------------------------------------*/
		$data = explode('|', $data);
		$START = (isset($data[0]) != FALSE) ? $data[0] : FALSE;
		$END = (isset($data[1]) != FALSE) ? $data[1] : FALSE;

		// We need START
		if ($START === FALSE) return FALSe;

		// We also need rating type
		if (isset($this->TYPES[$rating_type]) == FALSE) return FALSE;

		// Store it
		$rating_type = $this->TYPES[$rating_type];
		$rating_type_name = $this->TYPES_INV[$rating_type];

		// Is this rating type a ENTRY TYPE?
		$entry_type = FALSE;
		if ($rating_type_name == 'entry' OR $rating_type_name == 'comment_review') $entry_type = TRUE;

		/** ----------------------------------------
		/** Grab all collections
		/** ----------------------------------------*/
		$collections = array();
		$this->EE->db->select('DISTINCT(collection)', FALSE);
		$this->EE->db->from('exp_channel_ratings');
		$this->EE->db->where('field_id', 0);
		$this->EE->db->where('rating_type', $rating_type);
		if ($entry_type) $this->EE->db->where('entry_id >=', $START);
		else $this->EE->db->where('item_id >=', $START);
		if (isset($END) == TRUE && $entry_type == TRUE) $this->EE->db->where('entry_id <=', $END);
		elseif (isset($END) == TRUE && $entry_type == FALSE) $this->EE->db->where('item_id <=', $END);
		$query = $this->EE->db->get();

		foreach($query->result() as $row)
		{
			$collections[] = $row->collection;
		}

		$query->free_result();


		/** ----------------------------------------
		/** Grab all items
		/** ----------------------------------------*/
		$items = array();
		if ($entry_type) $this->EE->db->select('DISTINCT(entry_id) AS item_id', FALSE);
		else $this->EE->db->select('DISTINCT(item_id)', FALSE);
		$this->EE->db->from('exp_channel_ratings');
		$this->EE->db->where('field_id', 0);
		$this->EE->db->where('rating_type', $rating_type);
		if ($entry_type) $this->EE->db->where('entry_id >=', $START);
		else $this->EE->db->where('item_id >=', $START);
		if (isset($END) == TRUE && $entry_type == TRUE) $this->EE->db->where('entry_id <=', $END);
		elseif (isset($END) == TRUE && $entry_type == FALSE) $this->EE->db->where('item_id <=', $END);
		$query = $this->EE->db->get();

		foreach($query->result() as $row)
		{
			$items[] = $row->item_id;
		}

		$query->free_result();

		/** ----------------------------------------
		/** Loop over all collections and then items and recount!
		/** ----------------------------------------*/
		foreach ($collections as $collection)
		{
			foreach ($items as $item_id)
			{
				$this->update_stats($item_id, $collection, $rating_type);
			}
		}

		//exit(print_r($this->EE->db->queries));
		return TRUE;

	}

	// ********************************************************************************* //

	public function update_stats($item_id, $collection, $rating_type)
	{
		// Grab all Fields
		$fields = $this->EE->ratings_model->get_rating_fields($collection);

		// Global Stats
		$total = array('rating_last_date'=>0, 'rating_total'=>0, 'rating_avg'=> 0, 'rating_sum'=>0, 'num_fields' => 0);

		// Pretty Ratign Type
		$rating_type_name = $this->TYPES_INV[$rating_type];

		// Is this rating type a ENTRY TYPE?
		$entry_type = FALSE;
		if ($rating_type_name == 'entry' OR $rating_type_name == 'comment_review') $entry_type = TRUE;

		/** ----------------------------------------
		/** Do we need ENTRY_ID?
		/** ----------------------------------------*/
		$entry_id = 0;

		switch ($rating_type_name)
		{
			case 'entry':
				$entry_id = $item_id;
				$item_id = 0;
				break;
			case 'comment_review':
				$entryquery = $this->EE->db->select('entry_id')->from('exp_channel_titles')->where('entry_id', $item_id)->get();
				$item_id = 0;
				break;
			case 'comment_entry':
				$entryquery = $this->EE->db->select('entry_id')->from('exp_comments')->where('comment_id', $item_id)->get();
				break;
			case 'channel_images':
				$entryquery = $this->EE->db->select('entry_id')->from('exp_channel_images')->where('image_id', $item_id)->get();
				break;
			case 'channel_files':
				$entryquery = $this->EE->db->select('entry_id')->from('exp_channel_files')->where('file_id', $item_id)->get();
				break;
			case 'channel_videos':
				$entryquery = $this->EE->db->select('entry_id')->from('exp_channel_videos')->where('video_id', $item_id)->get();
				break;
		}

		if (isset($entryquery) != FALSE) $entry_id = $entryquery->row('entry_id');

		/** ----------------------------------------
		/** Do we need CHANNEL_ID?
		/** ----------------------------------------*/
		$channel_id = 0;

		switch ($rating_type_name)
		{
			case 'entry':
				$channelquery = $this->EE->db->select('channel_id')->from('exp_channel_titles')->where('entry_id', $entry_id)->get();
				break;
			case 'comment_review':
				$channelquery = $this->EE->db->select('channel_id')->from('exp_channel_titles')->where('entry_id', $entry_id)->get();
				break;
			case 'comment_entry':
				$channelquery = $this->EE->db->select('channel_id')->from('exp_comments')->where('comment_id', $item_id)->get();
				break;
			case 'channel_images':
				$channelquery = $this->EE->db->select('channel_id')->from('exp_channel_images')->where('image_id', $item_id)->get();
				break;
			case 'channel_files':
				$channelquery = $this->EE->db->select('channel_id')->from('exp_channel_files')->where('file_id', $item_id)->get();
				break;
			case 'channel_videos':
				$channelquery = $this->EE->db->select('channel_id')->from('exp_channel_videos')->where('video_id', $item_id)->get();
				break;
		}

		if (isset($channelquery) != FALSE) $channel_id = $channelquery->row('channel_id');


		// Loop over all fields
		foreach ($fields as $field)
		{
			/** ----------------------------------------
			/** Grab Stats for this Field
			/** ----------------------------------------*/
			$this->EE->db->select('COUNT(*) as rating_total, AVG(rating) as rating_avg, SUM(rating) as rating_sum, MAX(rating_date) as rating_last_date', FALSE);
			$this->EE->db->from('exp_channel_ratings');
			$this->EE->db->where('rating_status', 1);
			$this->EE->db->where('site_id', $this->site_id);
			$this->EE->db->where('field_id', $field->field_id);
			if ($entry_type) $this->EE->db->where('entry_id', $entry_id);
			else $this->EE->db->where('item_id', $item_id);
			$this->EE->db->where('collection', $collection);
			$this->EE->db->where('rating_type', $rating_type);
			$this->EE->db->group_by(array('entry_id', 'collection'));
			$query = $this->EE->db->get();


			// Nothing? We still need to populate
			if ($query->num_rows() == 0)
			{
				$stats = array();
				$stats['rating_last_date'] = 0;
				$stats['rating_total'] = 0;
				$stats['rating_avg'] = 0;
				$stats['rating_sum'] = 0;
			}
			else
			{
				$row = $query->row();
				$stats = array();
				$stats['rating_last_date'] = $row->rating_last_date;
				$stats['rating_total'] = $row->rating_total;
				$stats['rating_avg'] = $row->rating_avg;
				$stats['rating_sum'] = $row->rating_sum;
			}

			/** ----------------------------------------
			/** Does our stats entry exist for this entry?
			/** ----------------------------------------*/
			$this->EE->db->select('rstat_id');
			$this->EE->db->from('exp_channel_ratings_stats');
			$this->EE->db->where('field_id', $field->field_id);
			if ($entry_type) $this->EE->db->where('entry_id', $entry_id);
			else $this->EE->db->where('item_id', $item_id);
			$this->EE->db->where('collection', $collection);
			$this->EE->db->where('rating_type', $rating_type);
			$query = $this->EE->db->get();

			//----------------------------------------
			// Update Or Insert?
			//----------------------------------------
			if ($query->num_rows() == 0)
			{
				// new one Insert!
				$stats['site_id']	= $this->site_id;
				$stats['entry_id']	= $entry_id;
				$stats['item_id']	= $item_id;
				$stats['channel_id']= $channel_id;
				$stats['collection']= $collection;
				$stats['field_id']	= $field->field_id;
				$stats['rating_type'] = $rating_type;
				$this->EE->db->insert('exp_channel_ratings_stats', $stats);
			}
			else
			{
				// Update it!
				$this->EE->db->update( 'exp_channel_ratings_stats', $stats, array('rstat_id' => $query->row('rstat_id') ) );
			}

			//----------------------------------------
			// Lets count for global stats!
			//----------------------------------------
			$total['num_fields']++;
			$total['rating_last_date'] = ($stats['rating_last_date'] > $total['rating_last_date']) ? $stats['rating_last_date'] : $total['rating_last_date'];
			$total['rating_total'] += $stats['rating_total'];
			$total['rating_avg'] += $stats['rating_avg'];
			$total['rating_sum'] += $stats['rating_sum'];
		}

		/** ----------------------------------------
		/**  Global Stats
		/** ----------------------------------------*/
		$total['rating_total'] = $total['rating_total'] / $total['num_fields'];
		$total['rating_avg'] = $total['rating_avg'] / $total['num_fields'];
		$total['rating_sum'] = $total['rating_sum'] / $total['num_fields'];

		// Does our GLOBAL stats entry exist for this entry?
		$this->EE->db->select('rstat_id');
		$this->EE->db->from('exp_channel_ratings_stats');
		$this->EE->db->where('field_id', 0);
		if ($entry_type) $this->EE->db->where('entry_id', $entry_id);
		else $this->EE->db->where('item_id', $item_id);
		$this->EE->db->where('collection', $collection);
		$this->EE->db->where('rating_type', $rating_type);
		$query = $this->EE->db->get();


		unset($total['num_fields']); // We don't want this one ;)

		//----------------------------------------
		// Update Or Insert?
		//----------------------------------------
		if ($query->num_rows() == 0)
		{
			// new one Insert!
			$total['site_id']	= $this->site_id;
			$total['entry_id']	= $entry_id;
			$total['item_id']	= $item_id;
			$total['channel_id']= $channel_id;
			$total['collection']= $collection;
			$total['rating_type'] = $rating_type;
			$total['field_id'] = 0;

			$this->EE->db->insert('exp_channel_ratings_stats', $total);
		}
		else
		{
			// Update it!
			$this->EE->db->update('exp_channel_ratings_stats', $total, array('rstat_id' => $query->row('rstat_id') ) );
		}


		return TRUE;
	}

	// ********************************************************************************* //

	// split the given array into n number of pieces
	private function array_split($array, $pieces=2)
	{
	    if ($pieces < 2)
	        return array($array);
	    $newCount = ceil(count($array)/$pieces);
	    $a = array_slice($array, 0, $newCount);
	    $b = $this->array_split(array_slice($array, $newCount), $pieces-1);
	    return array_merge(array($a),$b);
	}

	// ********************************************************************************* //


	// TEMP SOLUTION FOR EE 2.1.1 SIGH!!!
	public function _assign_libraries()
	{

	}


} // END CLASS

/* End of file ratings_recount.php  */
/* Location: ./system/expressionengine/third_party/channel_ratings/models/ratings_recount.php */
