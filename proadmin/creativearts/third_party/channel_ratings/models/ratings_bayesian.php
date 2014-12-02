<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Ratings Bayesian Model File
 *
 * @package			DevDemon_ChannelRatings
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 */
class Ratings_bayesian
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

		$this->EE->config->load('ratings');
		$this->TYPES = $this->EE->config->item('cr_rating_types');
		$this->TYPES_INV = array_flip($this->TYPES);

		$this->cache = array();
	}

	// ********************************************************************************* //

	public function start()
	{
		@set_time_limit(0);
		@ini_set('memory_limit', '64M');
		@ini_set('memory_limit', '96M');
		@ini_set('memory_limit', '128M');
		@ini_set('memory_limit', '160M');
		@ini_set('memory_limit', '192M');

		// Get the total amount of ratings
		$query = $this->EE->db->select('COUNT(*) as total')->from('exp_channel_ratings')->where('field_id', 0)->get();
		$grand_total = $query->row('total');
		//$grand_total = 1000;

		// ----------------------------------------
		//  Grab Average Rating
		// ----------------------------------------
		$query = $this->EE->db->query("SELECT	AVG(rating_total) as rating_total_avg,
												AVG(rating_avg) as rating_avg
										FROM exp_channel_ratings_stats
										");
		$this->global_avg_num_votes = $query->row('rating_total_avg');
		$this->global_avg_rating = $query->row('rating_avg');

		// Lets do 50 at a time!
		for ($i=0; $i <= $grand_total; $i+=50)
		{
			// Grab all ratings!
			$query = $this->EE->db->select('*')->from('exp_channel_ratings_stats')->where('field_id', 0)->where('rating_type >', 0)->limit(50, $i)->get();

			if ($query->num_rows() == 0)
			{
				echo 'DONE<br>';
				exit();
			}

			foreach ($query->result() as $row) $this->calculate_bayesian($row);
		}
	}

	// ********************************************************************************* //

	private function calculate_bayesian($rating)
	{
		$rating_type = $this->TYPES_INV[$rating->rating_type];
		$entry_types = array('entry', 'comment_review', 'channel_images', 'channel_files', 'channel_videos');

		// -----------------------------------------
		// Entry Type Rating? Different SQL
		// -----------------------------------------
		if (in_array($rating_type, $entry_types) == TRUE)
		{
			$hash = $rating->channel_id.'-'.$rating->rating_type.'-'.$rating->collection_id;

			// Did we grab these stats already?
			if (isset($this->cache['EntryBased'][$hash]) == FALSE)
			{
				$cache = array();

				// ----------------------------------------
				//  Grab Average Rating
				// ----------------------------------------
				$query = $this->EE->db->query("SELECT	AVG(rating_total) as rating_total_avg,
														AVG(rating_avg) as rating_avg
												FROM exp_channel_ratings_stats
												WHERE channel_id = {$rating->channel_id}
												AND rating_type = {$rating->rating_type}
												AND collection_id = {$rating->collection_id}
												AND field_id = 0
												");

				$cache['avg_num_votes'] = $query->row('rating_total_avg');
				$cache['avg_rating'] = $query->row('rating_avg');

				$query->free_result();

				$this->cache['EntryBased'][$hash] = $cache;
			}
			else
			{
				$cache = $this->cache['EntryBased'][$hash];
			}

			$avg_num_votes = $cache['avg_num_votes'];
			$avg_rating = $cache['avg_rating'];
			$this_num_votes = $rating->rating_total;
			$this_rating = $rating->rating_avg;

			/*  $bayesian = ( (avg_num_votes * avg_rating) + (this_num_votes * this_rating) ) / (avg_num_votes + this_num_votes)
			    * avg_num_votes: The average number of votes of all items that have num_votes>0
				* avg_rating: The average rating of each item (again, of those that have num_votes>0)
				* this_num_votes: number of votes for this item
				* this_rating: the rating of this item
			*/

			$bayesian = ( ($avg_num_votes / $avg_rating) + ($this_num_votes * $this_rating) ) / ($avg_num_votes + $this_num_votes);
			$bayesian_global = ( ($this->global_avg_num_votes / $this->global_avg_rating) + ($this_num_votes * $this_rating) ) / ($this->global_avg_num_votes + $this_num_votes);
			$this->EE->db->set('rating_bayesian', $bayesian);
			$this->EE->db->set('rating_bayesian', $bayesian_global);
			$this->EE->db->where('rstat_id', $rating->rstat_id);
			$this->EE->db->update('exp_channel_ratings_stats');
		}
		else
		{
			$hash = $rating->rating_type.'-'.$rating->collection_id;

			// Did we grab these stats already?
			if (isset($this->cache['ItemBased'][$hash]) == FALSE)
			{
				$cache = array();

				// ----------------------------------------
				//  Grab Average Rating
				// ----------------------------------------
				$query = $this->EE->db->query("SELECT	AVG(rating_total) as rating_total_avg,
														AVG(rating_avg) as rating_avg
												FROM exp_channel_ratings_stats
												WHERE rating_type = {$rating->rating_type}
												AND collection_id = {$rating->collection_id}
												AND field_id = 0
												");

				$cache['avg_num_votes'] = $query->row('rating_total_avg');
				$cache['avg_rating'] = $query->row('rating_avg');

				$query->free_result();

				$this->cache['ItemBased'][$hash] = $cache;
			}
			else
			{
				$cache = $this->cache['ItemBased'][$hash];
			}

			$avg_num_votes = $cache['avg_num_votes'];
			$avg_rating = $cache['avg_rating'];
			$this_num_votes = $rating->rating_total;
			$this_rating = $rating->rating_avg;

			/*  $bayesian = ( (avg_num_votes * avg_rating) + (this_num_votes * this_rating) ) / (avg_num_votes + this_num_votes)
			    * avg_num_votes: The average number of votes of all items that have num_votes>0
				* avg_rating: The average rating of each item (again, of those that have num_votes>0)
				* this_num_votes: number of votes for this item
				* this_rating: the rating of this item
			*/

			$bayesian = ( ($avg_num_votes / $avg_rating) + ($this_num_votes * $this_rating) ) / ($avg_num_votes + $this_num_votes);
			$bayesian_global = ( ($this->global_avg_num_votes / $this->global_avg_rating) + ($this_num_votes * $this_rating) ) / ($this->global_avg_num_votes + $this_num_votes);
			$this->EE->db->set('rating_bayesian', $bayesian);
			$this->EE->db->set('rating_bayesian', $bayesian_global);
			$this->EE->db->where('rstat_id', $rating->rstat_id);
			$this->EE->db->update('exp_channel_ratings_stats');
		}


	}

	// ********************************************************************************* //

} // END CLASS

/* End of file ratings_bayesian.php  */
/* Location: ./system/expressionengine/third_party/channel_ratings/models/ratings_bayesian.php */