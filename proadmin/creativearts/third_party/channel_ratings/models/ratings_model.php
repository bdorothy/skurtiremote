<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Ratings Model File
 *
 * @package			DevDemon_ChannelRatings
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 */
class Ratings_model
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
		$this->EE->load->library('ratings_helper');
		$this->site_id = $this->EE->ratings_helper->getSiteId();

		$this->EE->config->load('ratings');
		$this->TYPES = $this->EE->config->item('cr_rating_types');
		$this->TYPES_INV = array_flip($this->TYPES);
		$this->EE->load->helper('url');
	}

	// ********************************************************************************* //

	public function insert_rating()
	{
		// First check the ratings array
		if (isset($this->EE->ratings) == FALSE OR empty($this->EE->ratings) == TRUE) return FALSE;

		// -----------------------------------------
		// Calculate Average
		// -----------------------------------------
		$avg = 0;
		foreach ($this->EE->ratings as $rating) $avg += $rating;
		$avg = ($avg / count($this->EE->ratings));

		// Add to array
		$this->EE->ratings[0] = $avg;

		$rating_id = 0;

		foreach ($this->EE->ratings as $field_id => $rating)
		{
			$this->EE->db->set('site_id', $this->EE->rating_data['site_id']);
			$this->EE->db->set('entry_id', $this->EE->rating_data['entry_id']);
			$this->EE->db->set('item_id', $this->EE->rating_data['item_id']);
			$this->EE->db->set('channel_id', $this->EE->rating_data['channel_id']);
			$this->EE->db->set('field_id', $field_id);
			$this->EE->db->set('ip_address', $this->EE->rating_data['ip_address']);
			$this->EE->db->set('collection_id', $this->EE->rating_data['collection_id']);
			$this->EE->db->set('rating', $rating);
			$this->EE->db->set('rating_author_id', $this->EE->rating_data['rating_author_id']);
			$this->EE->db->set('rating_date', $this->EE->rating_data['rating_date']);
			$this->EE->db->set('rating_type', $this->EE->rating_data['rating_type']);
			$this->EE->db->set('rating_status', $this->EE->rating_data['rating_status']);
			$this->EE->db->insert('exp_channel_ratings');

			if ($field_id == 0) $rating_id = $this->EE->db->insert_id();
		}

		// Update Stats
		$this->update_stats($this->EE->rating_data);

		// Update Global Stats (only for entries for now)
		if ($this->TYPES_INV[$this->EE->rating_data['rating_type']] == 'entry' OR $this->TYPES_INV[$this->EE->rating_data['rating_type']] == 'comment_review')
		{
			$this->update_stats($this->EE->rating_data, TRUE);
		}

		// -------------------------------------------
		// 'channelratings_insert_rating_end' hook.
		//  - More emails, more processing, different redirect
		//
			$edata = $this->EE->extensions->call('channelratings_insert_rating_end', $this->EE->rating_data['rating_type_name'], $this->EE->rating_data, $this->EE->ratings, $rating_id);
			if ($this->EE->extensions->end_script === TRUE) return;
		//
		// -------------------------------------------

		unset($this->EE->ratings, $this->EE->rating_data);
	}

	// ********************************************************************************* //

	public function update_rating($rating_id=0, $data=array(), $fields=array())
	{
		// Grab all ratings
		$query = $this->EE->db->select('*')->from('exp_channel_ratings')->where('rating_id', $rating_id)->get();
		if ($query->num_rows() == 0) return FALSE;

		// Rating Data
		$ratingdata = $query->row_array();

		// What Rating Status?
		$rating_status = (isset($data['rating_status']) == TRUE) ? $data['rating_status'] : $ratingdata['rating_status'];

		// -------------------------------------------
		// 'channelratings_update_rating_start' hook.
		//  - Executes before updating the rating
		//
			$edata = $this->EE->extensions->call('channelratings_update_rating_start', $rating_id, $ratingdata, $fields);
			if ($this->EE->extensions->end_script === TRUE) return;
		//
		// -------------------------------------------

		// Quick status change?
		if (empty($fields) == TRUE)
		{
			$this->EE->db->set('rating_status', $rating_status);
			$this->EE->db->where('rating_date', $ratingdata['rating_date']);
			$this->EE->db->where('collection_id', $ratingdata['collection_id']);
			$this->EE->db->where('rating_author_id', $ratingdata['rating_author_id']);
			$this->EE->db->where('rating_type', $ratingdata['rating_type']);
			$this->EE->db->where('ip_address', $ratingdata['ip_address']);
			$this->EE->db->update('exp_channel_ratings');
		}
		else
		{
			// Calculate Average
			$avg = 0;
			foreach ($fields as $rating)
			{
				$avg += $rating;
			}

			$avg = $avg / count($fields);

			// Add Average
			$fields[0] = $avg;

			// Update all fields!
			foreach ($fields as $field_id => $rating)
			{
				if (is_numeric($rating) == FALSE) continue;

				$this->EE->db->set('rating', $rating);
				$this->EE->db->set('rating_status', $rating_status);
				$this->EE->db->where('rating_date', $ratingdata['rating_date']);
				$this->EE->db->where('collection_id', $ratingdata['collection_id']);
				$this->EE->db->where('rating_author_id', $ratingdata['rating_author_id']);
				$this->EE->db->where('rating_type', $ratingdata['rating_type']);
				$this->EE->db->where('ip_address', $ratingdata['ip_address']);

				$this->EE->db->where('field_id', $field_id);
				$this->EE->db->update('exp_channel_ratings');
			}
		}

		// For Comment Reviews, we also want to open/close the attached comment
		if ($this->TYPES['comment_review'] == $ratingdata['rating_type'])
		{
			$this->EE->db->set('status', (($rating_status == 1) ? 'o' : 'c') );
			$this->EE->db->where('comment_id', $ratingdata['item_id']);
			$this->EE->db->update('exp_comments');
		}

		// -------------------------------------------
		// 'channelratings_update_rating_end' hook.
		//  - Executes before updating stats
		//
			$edata = $this->EE->extensions->call('channelratings_update_rating_end', $rating_id, $ratingdata, $fields);
			if ($this->EE->extensions->end_script === TRUE) return;
		//
		// -------------------------------------------

		// Update Entry Stats
		$ratingdata['rating_type_name'] = $this->TYPES_INV[$ratingdata['rating_type']];
		$this->EE->ratings_model->update_stats($ratingdata);

		// -------------------------------------------
		// 'channelratings_update_rating_absolute_end' hook.
		//  - Executes after updating new stats
		//
			$edata = $this->EE->extensions->call('channelratings_update_rating_absolute_end', $rating_id, $ratingdata, $fields);
			if ($this->EE->extensions->end_script === TRUE) return;
		//
		// -------------------------------------------
	}

	// ********************************************************************************* //

	public function delete_rating($rating_id=0)
	{
		// Grab all ratings
		$query = $this->EE->db->select('*')->from('exp_channel_ratings')->where('rating_id', $rating_id)->get();
		if ($query->num_rows() == 0) return FALSE;

		// Rating Data
		$ratingdata = $query->row_array();

		// -------------------------------------------
		// 'channelratings_delete_rating_start' hook.
		//  - Executes before deleting the rating
		//
			$edata = $this->EE->extensions->call('channelratings_delete_rating_start', $rating_id, $ratingdata);
			if ($this->EE->extensions->end_script === TRUE) return;
		//
		// -------------------------------------------

		$this->EE->db->where('rating_date', $ratingdata['rating_date']);
		$this->EE->db->where('collection_id', $ratingdata['collection_id']);
		$this->EE->db->where('rating_author_id', $ratingdata['rating_author_id']);
		$this->EE->db->where('rating_type', $ratingdata['rating_type']);
		$this->EE->db->where('ip_address', $ratingdata['ip_address']);
		$this->EE->db->delete('exp_channel_ratings');

		// For Comment Reviews, we also are going to delete it!
		if ($this->TYPES['comment_review'] == $ratingdata['rating_type'])
		{
			$this->EE->db->where('comment_id', $ratingdata['item_id']);
			$this->EE->db->delete('exp_comments');
		}

		// -------------------------------------------
		// 'channelratings_delete_rating_end' hook.
		//  - Executes before updating stats
		//
			$edata = $this->EE->extensions->call('channelratings_delete_rating_end', $ratingdata);
			if ($this->EE->extensions->end_script === TRUE) return;
		//
		// -------------------------------------------

		// Update Entry Stats
		$ratingdata['rating_type_name'] = $this->TYPES_INV[$ratingdata['rating_type']];
		$this->EE->ratings_model->update_stats($ratingdata);

		// -------------------------------------------
		// 'channelratings_delete_rating_absolute_end' hook.
		//  - Executes after updating new stats
		//
			$edata = $this->EE->extensions->call('channelratings_delete_rating_absolute_end', $ratingdata);
			if ($this->EE->extensions->end_script === TRUE) return;
		//
		// -------------------------------------------
	}

	// ********************************************************************************* //

	public function update_like($rlike_id=0, $vote='')
	{
		// Grab all ratings
		$query = $this->EE->db->select('*')->from('exp_channel_ratings_likes')->where('rlike_id', $rlike_id)->get();
		if ($query->num_rows() == 0) return FALSE;

		// Like Data
		$likedata = $query->row_array();

		// What Vote?
		$column = 'like';
		if ($vote == 'dislike') $column = 'dislike';

		// -------------------------------------------
		// 'channelratings_update_like_start' hook.
		//  - Executes before updating the like
		//
			$edata = $this->EE->extensions->call('channelratings_update_like_start', $rlike_id, $vote, $likedata);
			if ($this->EE->extensions->end_script === TRUE) return;
		//
		// -------------------------------------------

		if ($vote == 'like')
		{
			$this->EE->db->set('liked', 1);
			$this->EE->db->set('disliked', 0);
		}
		else
		{
			$this->EE->db->set('liked', 0);
			$this->EE->db->set('disliked', 1);
		}

		$this->EE->db->where('rlike_id', $rlike_id);
		$this->EE->db->update('exp_channel_ratings_likes');

		// -------------------------------------------
		// 'channelratings_update_like_end' hook.
		//  - Executes before updating the like stats
		//
			$edata = $this->EE->extensions->call('channelratings_update_like_end', $rlike_id, $vote, $likedata);
			if ($this->EE->extensions->end_script === TRUE) return;
		//
		// -------------------------------------------
/*
		// Update Entry Stats
		$data['rating_type_name'] = $this->TYPES_INV[$ratingdata['rating_type']];
		$this->EE->ratings_model->update_stats($data);
*/

		// -------------------------------------------
		// 'channelratings_update_like_absolute_end' hook.
		//  - Executes after updating the like stats
		//
			$edata = $this->EE->extensions->call('channelratings_update_like_absolute_end', $rlike_id, $vote, $likedata);
			if ($this->EE->extensions->end_script === TRUE) return;
		//
		// -------------------------------------------

		return TRUE;
	}

	// ********************************************************************************* //

	public function delete_like($rlike_id=0)
	{
		// Grab all ratings
		$query = $this->EE->db->select('*')->from('exp_channel_ratings_likes')->where('rlike_id', $rlike_id)->get();
		if ($query->num_rows() == 0) return FALSE;

		// Like Data
		$likedata = $query->row_array();

		// -------------------------------------------
		// 'channelratings_delete_like_start' hook.
		//  - Executes before deleting the like
		//
			$edata = $this->EE->extensions->call('channelratings_delete_like_start', $rlike_id, $likedata);
			if ($this->EE->extensions->end_script === TRUE) return;
		//
		// -------------------------------------------

		$this->EE->db->where('rlike_id', $rlike_id);
		$this->EE->db->delete('exp_channel_ratings_likes');

		// -------------------------------------------
		// 'channelratings_delete_like_end' hook.
		//  - Executes before updating stats
		//
			$edata = $this->EE->extensions->call('channelratings_delete_like_end', $likedata);
			if ($this->EE->extensions->end_script === TRUE) return;
		//
		// -------------------------------------------


		// Update Entry Stats
		$data['like_type_name'] = $this->TYPES_INV[$likedata['like_type']];
		$this->EE->ratings_model->update_like_stats($likedata);


		// -------------------------------------------
		// 'channelratings_delete_like_absolute_end' hook.
		//  - Executes after updating new stats
		//
			$edata = $this->EE->extensions->call('channelratings_delete_like_absolute_end', $likedata);
			if ($this->EE->extensions->end_script === TRUE) return;
		//
		// -------------------------------------------
	}

	// ********************************************************************************* //

	/**
	 * Has user has already rated this entry?
	 *
	 * @param string $type (entry, comment)
	 * @param int $entry_id - The Entry
	 * @access public
	 * @return bool
	 */
	public function if_already_rated($rating_type=0, $item_id=0, $collection_id='0')
	{
		/** ----------------------------------------
		/** Standard Vars
		/** ----------------------------------------*/
		$rated = FALSE;
		$hash = $rating_type.'-'.$item_id.'/'.$collection_id;
		$IP = sprintf("%u", ip2long($this->EE->input->ip_address()));

		/** ----------------------------------------
		/** Multiple ?
		/** ----------------------------------------*/
		if (strpos($rating_type, '|') !== FALSE)
		{
			$types = explode('|', $rating_type);
		}

		if (isset($this->EE->session->cache['ChannelRatings']['AlreadyRated'][$hash]) == FALSE)
		{
			/** ----------------------------------------
			/** Start SQL
			/** ----------------------------------------*/
			$this->EE->db->select('rating_id, rating_date');
			$this->EE->db->from('exp_channel_ratings');
			$this->EE->db->where('rating_author_id', $this->EE->session->userdata['member_id']);
			$this->EE->db->where('collection_id', $collection_id);
			$this->EE->db->where('site_id', $this->site_id);

			/** ----------------------------------------
			/** Multiple Types?
			/** ----------------------------------------*/
			if (isset($types) == FALSE)
			{
				// Entries are handled different
				if ($this->TYPES_INV[$rating_type] == 'entry' OR $this->TYPES_INV[$rating_type] == 'comment_review') $this->EE->db->where('entry_id', $item_id);
				else  $this->EE->db->where('item_id', $item_id);

				$this->EE->db->where('rating_type', $rating_type);
			}
			else
			{
				// Entries are handled different
				$entry_type = FALSE;
				foreach ($types as $type)
				{
					if ($this->TYPES_INV[$type] == 'entry' OR $this->TYPES_INV[$type] == 'comment_review') $entry_type = TRUE;
				}

				if ($entry_type == TRUE) $this->EE->db->where('entry_id', $item_id);
				else  $this->EE->db->where('item_id', $item_id);

				$this->EE->db->where_in('rating_type', $types);
			}

			// If guest, check IP
			if ($this->EE->session->userdata['member_id'] == 0) $this->EE->db->where('ip_address', $IP);

			/** ----------------------------------------
			/** Grab it!
			/** ----------------------------------------*/
			$query = $this->EE->db->get();




			/** ----------------------------------------
			/** Found something?
			/** ----------------------------------------*/
			if ($query->num_rows() > 0)
			{
				$rated = TRUE;
				$row = $query->row();


				// Allow people to revote after certain time?
				$config = $this->EE->config->item('channel_ratings');

				if (isset($config['expire_rated_status_after']) === true) {
					if ($this->EE->localize->now > ($row->rating_date + $config['expire_rated_status_after']) ) {
						$rated = false;
					}
				}

				if ($rated) $this->EE->session->cache['ChannelRatings']['AlreadyRated'][$hash] = $rated;
			}

			$query->free_result();
		}
		else
		{
			$rated = $this->EE->session->cache['ChannelRatings']['AlreadyRated'][$hash];
		}

		return $rated;
	}

	// ********************************************************************************* //

	public function if_already_liked($rating_type=0, $item_id=0, $collection_id=0)
	{
		$liked = 0;

		// IP
		$IP = sprintf("%u", ip2long($this->EE->input->ip_address()));

		$this->EE->db->select('rlike_id');
		$this->EE->db->from('exp_channel_ratings_likes');
		$this->EE->db->where('collection_id', $collection_id);
		$this->EE->db->where('stats_row', 0);

		// Entries are handled different
		if ($this->TYPES_INV[$rating_type] == 'entry' OR $this->TYPES_INV[$rating_type] == 'comment_review')
		{
			$this->EE->db->where('entry_id', $item_id);
		}
		else  $this->EE->db->where('item_id', $item_id);

		if ($this->EE->session->userdata['member_id'] == 0)
		{
			$this->EE->db->where('ip_address', $IP);
			$this->EE->db->where('like_author_id', 0);
		}
		else $this->EE->db->where('like_author_id', $this->EE->session->userdata['member_id']);
		$this->EE->db->where('like_type', $rating_type);
		$this->EE->db->limit(1);
		$query = $this->EE->db->get();

		if ($query->num_rows() > 0)
		{
			$liked = $query->row('rlike_id');
		}

		$query->free_result();

		return $liked;
	}

	// ********************************************************************************* //

	/**
	 * Grab total unique records ratings in the DB
	 *
	 * @param int $rating_type
	 * @access public
	 * @return int - Amount of unique ratings
	 */
	public function total_unique_ratings($rating_type=0)
	{
		//$this->EE->db->save_queries = TRUE;

		/// ----------------------------------------
		/// Total Records in the DB
		/// ----------------------------------------
		$this->EE->db->select('COUNT(*) as total_records', FALSE);
		$this->EE->db->from('exp_channel_ratings');
		$this->EE->db->where('rating_type', $rating_type);
		$this->EE->db->where('field_id', 0);
		$query = $this->EE->db->get();

		//$this->EE->firephp->fb($this->EE->db->last_query());

		return $query->row('total_records');
	}

	// ********************************************************************************* //

	/**
	 * Grab total unique records ratings in the DB
	 *
	 * @param int $rating_type
	 * @access public
	 * @return int - Amount of unique ratings
	 */
	public function total_unique_likes($like_type=0)
	{
		//$this->EE->db->save_queries = TRUE;

		/// ----------------------------------------
		/// Total Records in the DB
		/// ----------------------------------------
		$this->EE->db->select('COUNT(*) as total_records', FALSE);
		$this->EE->db->from('exp_channel_ratings_likes');
		$this->EE->db->where('like_type', $like_type);
		$this->EE->db->where('stats_row', 0);
		$query = $this->EE->db->get();

		//$this->EE->firephp->fb($this->EE->db->last_query());

		return $query->row('total_records');
	}

	// ********************************************************************************* //

	/**
	 * Update Rating Stats
	 *
	 * @param string $type (entry, comment)
	 * @access public
	 * @return void
	 */
	public function update_stats($data, $global_type=FALSE)
	{
		// Global Stats
		$total = array('rating_last_date'=>0, 'rating_total'=>0, 'rating_avg'=> 0, 'rating_sum'=>0, 'num_fields' => 0);

		// Grab all fields
		$fields = $this->get_rating_fields($data['collection_id']);

		// Comment Reviews are a bit different!
		if ($data['rating_type_name'] == 'comment_review')
		{
			$data['item_id'] = 0;
		}

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
			$this->EE->db->where('entry_id', $data['entry_id']);
			if ($data['rating_type_name'] != 'comment_review' && $data['rating_type_name'] != 'entry') $this->EE->db->where('item_id', $data['item_id']);
			$this->EE->db->where('collection_id', $data['collection_id']);
			if ($global_type == FALSE)
			{
				$this->EE->db->where('rating_type', $data['rating_type']);
				$this->EE->db->group_by(array('entry_id', 'collection_id'));
			}
			else
			{
				$this->EE->db->group_by(array('entry_id', 'collection_id'));
			}

			$query = $this->EE->db->get();



			// Nothing? We still need to populate
			if ($query->num_rows() == 0)
			{
				$stats = array(	'rating_last_date' => 0,
								'rating_total' =>  0,
								'rating_avg' => 0,
								'rating_sum' =>  0,
					);
			}
			else
			{
				$row = $query->row();

				$stats = array(	'rating_last_date' => $row->rating_last_date,
							'rating_total' =>  $row->rating_total,
							'rating_avg' =>  $row->rating_avg,
							'rating_sum' =>  $row->rating_sum,
					);
			}

			//----------------------------------------
			// Calculate Bayesian
			//----------------------------------------

			// Does our stats entry exist for this entry?
			$this->EE->db->select('rstat_id');
			$this->EE->db->from('exp_channel_ratings_stats');
			$this->EE->db->where('entry_id', $data['entry_id']);
			$this->EE->db->where('item_id', $data['item_id']);
			$this->EE->db->where('field_id', $field->field_id);
			$this->EE->db->where('collection_id', $data['collection_id']);
			if ($global_type == FALSE) $this->EE->db->where('rating_type', $data['rating_type']);
			else $this->EE->db->where('rating_type', 0);
			$this->EE->db->limit(1);
			$query = $this->EE->db->get();

			//----------------------------------------
			// Update Or Insert?
			//----------------------------------------
			if ($query->num_rows() == 0)
			{
				// new one Insert!
				$stats['site_id']	= $this->site_id;
				$stats['entry_id']	= $data['entry_id'];
				$stats['item_id']	= $data['item_id'];
				$stats['channel_id']	= $data['channel_id'];
				$stats['collection_id']	= $data['collection_id'];
				$stats['field_id']	= $field->field_id;
				if ($global_type == FALSE) $stats['rating_type'] = $data['rating_type'];
				else $stats['rating_type'] = 0;
				$this->EE->db->insert( 'exp_channel_ratings_stats', $stats);
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

			$query->free_result();
		}

		/** ----------------------------------------
		/**  Global Stats
		/** ----------------------------------------*/
		//$total['rating_total'] = $total['rating_total'] / $total['num_fields'];
		$total['rating_total'] = $total['rating_total'];
		$total['rating_avg'] = $total['rating_avg'] / $total['num_fields'];
		//$total['rating_sum'] = $total['rating_sum'] / $total['num_fields'];
		$total['rating_sum'] = $total['rating_sum'];

		// Does our GLOBAL stats entry exist for this entry?
		$this->EE->db->select('rstat_id');
		$this->EE->db->from('exp_channel_ratings_stats');
		$this->EE->db->where('entry_id', $data['entry_id']);
		$this->EE->db->where('item_id', $data['item_id']);
		$this->EE->db->where('field_id', 0);
		$this->EE->db->where('collection_id', $data['collection_id']);
		if ($global_type == FALSE) $this->EE->db->where('rating_type', $data['rating_type']);
		else $this->EE->db->where('rating_type', 0);
		$this->EE->db->limit(1);
		$query = $this->EE->db->get();

		unset($total['num_fields']); // We don't want this one ;)

		//----------------------------------------
		// Update Or Insert?
		//----------------------------------------
		if ($query->num_rows() == 0)
		{
			// new one Insert!
			$total['site_id']	= $this->site_id;
			$total['entry_id'] = $data['entry_id'];
			$total['item_id'] = $data['item_id'];
			$total['channel_id'] = $data['channel_id'];
			$total['collection_id'] = $data['collection_id'];
			if ($global_type == FALSE) $total['rating_type'] = $data['rating_type'];
			else $total['rating_type'] = 0;
			$total['field_id'] = 0;

			$this->EE->db->insert( 'exp_channel_ratings_stats', $total);
		}
		else
		{
			// Update it!
			$this->EE->db->update( 'exp_channel_ratings_stats', $total, array('rstat_id' => $query->row('rstat_id') ) );
		}

		$query->free_result();

		// Delete all ZERO rating stats :)
		//$this->EE->db->query("DELETE FROM exp_channel_ratings_stats WHERE rating_total = 0");
		$this->EE->db->query("DELETE FROM exp_channel_ratings_stats WHERE rating_sum = 0");

		return;
	}

	// ********************************************************************************* //

	public function update_like_stats($likedata)
	{
		$this->EE->db->select("SUM(liked) as liked_sum, SUM(disliked) as disliked_sum, MAX(like_date) as like_last_date", FALSE);
		$this->EE->db->from('exp_channel_ratings_likes');
		$this->EE->db->where('like_type', $likedata['like_type']);
		$this->EE->db->where('entry_id', $likedata['entry_id']);
		$this->EE->db->where('item_id', $likedata['item_id']);
		$this->EE->db->where('collection_id', $likedata['collection_id']);
		$this->EE->db->where('stats_row', 0);
		$query = $this->EE->db->get();

		$total = array();
		$total['liked'] = $query->row('liked_sum');
		$total['disliked'] = $query->row('disliked_sum');
		$total['like_date'] = $query->row('like_last_date');

		$query->free_result();

		// Does our stats entry exist for this entry?
		$this->EE->db->select('rlike_id');
		$this->EE->db->from('exp_channel_ratings_likes');
		$this->EE->db->where('like_type', $likedata['like_type']);
		$this->EE->db->where('entry_id', $likedata['entry_id']);
		$this->EE->db->where('item_id', $likedata['item_id']);
		$this->EE->db->where('collection_id', $likedata['collection_id']);
		$this->EE->db->where('stats_row', 1);
		$query = $this->EE->db->get();

		//----------------------------------------
		// Update Or Insert?
		//----------------------------------------
		if ($query->num_rows() == 0)
		{
			// new one Insert!
			$total['stats_row'] = 1;
			$total['like_type'] = $likedata['like_type'];
			$total['site_id']	= $likedata['site_id'];
			$total['collection_id']= $likedata['collection_id'];
			$total['entry_id']	= $likedata['entry_id'];
			$total['channel_id']= $likedata['channel_id'];
			$total['item_id']	= $likedata['item_id'];
			$this->EE->db->insert( 'exp_channel_ratings_likes', $total);
		}
		else
		{
			// Update it!
			$this->EE->db->update( 'exp_channel_ratings_likes', $total, array('rlike_id' => $query->row('rlike_id') ) );
		}

		// Delete all ZERO rating stats :)
		$this->EE->db->query("DELETE FROM exp_channel_ratings_likes WHERE liked = 0 AND disliked = 0 AND stats_row = 1");

		return;
	}

	// ********************************************************************************* //

	public function get_rating_fields($collection_id=0)
	{
		$fields = array();

		if (isset($this->EE->session->cache['ChannelRatings']['Fields'][$collection_id]) == FALSE)
		{
			$query = $this->EE->db->select('*')->from('exp_channel_ratings_fields')->where('collection_id', $collection_id)->get();
			if ($query->num_rows() == 0) return $fields;

			foreach ($query->result() as $row)
			{
				$fields[$row->field_id] = $row;
			}

			$this->EE->session->cache['ChannelRatings']['Fields'][$collection_id] = $fields;
		}
		else
		{
			$fields = $this->EE->session->cache['ChannelRatings']['Fields'][$collection_id];
		}

		return $fields;
	}

	// ********************************************************************************* //

	public function get_collections()
	{
		$collections = array();

		if (isset($this->EE->session->cache['ChannelRatings']['Collections']) == FALSE)
		{
			$this->EE->session->cache['ChannelRatings']['Collections'] = array();

			// Get all collections
			$query = $this->EE->db->select('*')->from('exp_channel_ratings_collections')->order_by('default', 'DESC')->order_by('collection_label', 'ASC')->get();

			// Loop over all collections
			foreach ($query->result() as $row)
			{
				$collections[$row->site_id][$row->collection_id] = $row;
			}

			// Still no there? Lets make it
			if (isset($collections[$this->site_id]) == FALSE)
			{
				// Add the new collection
				$coll = (object) $this->create_update_collection(NULL, NULL, TRUE);
				$collections[$this->site_id][$coll->collection_id] = $coll;
			}
		}
		else
		{
			$collections = $this->EE->session->cache['ChannelRatings']['Collections'];

			// Still no there? Lets make it
			if (isset($collections[$this->site_id]) == FALSE)
			{
				// Add the new collection
				$coll = (object) $this->create_update_collection(NULL, NULL, TRUE);
				$collections[$this->site_id][$coll->collection_id] = $row;
			}
		}

		$this->EE->session->cache['ChannelRatings']['Collections'] = $collections;

		return $collections[$this->site_id];
	}

	// ********************************************************************************* //

	public function create_update_collection($data=array(), $collection_id=0, $create_default=FALSE)
	{
		if (REQ == 'CP')
		{
			$site_id = $this->EE->ratings_helper->getSiteId();
		}
		else
		{
			$site_id = $this->site_id;
		}


		if ($create_default === TRUE)
		{
			$data['site_id'] = $site_id;
			$data['collection_label'] = 'Default';
			$data['collection_name'] = 'default';
			$data['default'] = 1;
			$this->EE->db->insert('exp_channel_ratings_collections', $data);
			$data['collection_id'] = $this->EE->db->insert_id();
			return $data;
		}

		if (isset($data['default']) === TRUE)
		{
			if ($data['default'] == 1)
			{
				$this->EE->db->set('default', 0);
				$this->EE->db->where('site_id', $site_id);
				$this->EE->db->update('exp_channel_ratings_collections');
			}

			$this->EE->db->set('default', $data['default']);
		}

		if (isset($data['collection_label'])) $this->EE->db->set('collection_label', $data['collection_label']);
		if (isset($data['collection_name']) && $data['collection_name'] != FALSE) $this->EE->db->set('collection_name', $data['collection_name']);
		else $this->EE->db->set('collection_name', url_title($data['collection_name']));

		$this->EE->db->set('site_id', $site_id);

		// Are we updating?
		if ($collection_id >= 1)
		{
			$this->EE->db->where('collection_id', $collection_id);
			$this->EE->db->update('exp_channel_ratings_collections');
		}
		else
		{
			$this->EE->db->insert('exp_channel_ratings_collections');
			$collection_id = $this->EE->db->insert_id();
		}

		// Is there a default collection?
		$query = $this->EE->db->select('collection_id')->from('exp_channel_ratings_collections')->where('site_id', $site_id)->where('default', 1)->get();

		if ($query->num_rows() == 0)
		{
			// Lets make the first one default!
			$this->EE->db->set('default', 1);
			$this->EE->db->where('site_id', $site_id);
			$this->EE->db->limit(1);
			$this->EE->db->update('exp_channel_ratings_collections');
		}

		return $collection_id;
	}

	// ********************************************************************************* //

	public function delete_collection($collection_id=0)
	{
		if ($collection_id == 0) return FALSE;

		$this->EE->db->where('collection_id', $collection_id);
		$this->EE->db->delete('channel_ratings');

		$this->EE->db->where('collection_id', $collection_id);
		$this->EE->db->delete('channel_ratings_stats');

		$this->EE->db->where('collection_id', $collection_id);
		$this->EE->db->delete('channel_ratings_fields');

		$this->EE->db->where('collection_id', $collection_id);
		$this->EE->db->delete('exp_channel_ratings_likes');

		$this->EE->db->where('collection_id', $collection_id);
		$this->EE->db->delete('exp_channel_ratings_collections');

		return TRUE;
	}

	// ********************************************************************************* //


} // END CLASS

/* End of file ratings_model.php  */
/* Location: ./system/expressionengine/third_party/channel_ratings/models/ratings_model.php */
