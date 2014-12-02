<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Ratings Module Tags
 *
 * @package			DevDemon_ChannelRatings
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 * @see				http://expressionengine.com/user_guide/development/module_tutorial.html#core_module_file
 */
class Channel_ratings
{

	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		$this->EE =& get_instance();

		$this->EE->load->library('ratings_helper');
		$this->EE->load->model('ratings_model');
		$this->site_id = $this->EE->ratings_helper->getSiteId();

		$this->EE->config->load('ratings');
		$this->TYPES = $this->EE->config->item('cr_rating_types');
		$this->TYPES_INV = array_flip($this->TYPES);

		$this->get_collections();
	}

	// ********************************************************************************* //

	public function rating()
	{
		// Variable prefix
		$prefix = $this->EE->TMPL->fetch_param('prefix', 'rating') . ':';

		// -----------------------------------------
		// Which Rating Type?
		// -----------------------------------------
		$rating_type = $this->detect_rating_type_from_params();

		if ($rating_type['type'] < 1)
		{
			$this->EE->TMPL->log_item('CHANNEL RATINGS: Rating Type could not be determined');
			return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_rating', $this->EE->TMPL->tagdata);
		}

		// Easy to remember
		$item_id = $rating_type['item_id'];
		$rating_type = $rating_type['type'];
		$rating_type_name = $this->TYPES_INV[$rating_type];

		if ($item_id < 1) {
			$this->EE->TMPL->log_item('CHANNEL RATINGS: Item Id could not be determined');
			return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_rating', $this->EE->TMPL->tagdata);
		}

		// -----------------------------------------
		// Start SQL
		// -----------------------------------------
		// Lets grab the stats for his entry
		$this->EE->db->select('rs.*, rf.short_name');
		$this->EE->db->from('exp_channel_ratings_stats rs');
		$this->EE->db->join('exp_channel_ratings_fields rf', 'rs.field_id = rf.field_id', 'left');
		$this->EE->db->where('rs.site_id', $this->site_id);
		if ($rating_type_name == 'entry') $this->EE->db->where('rs.entry_id', $item_id);
		else $this->EE->db->where('rs.item_id', $item_id);

		if ($this->EE->TMPL->fetch_param('include_all_types') != 'yes')
		{
			$this->EE->db->where('rs.rating_type', $rating_type);
		}
		else
		{
			$this->EE->db->where('rs.rating_type', 0);
		}

		// -----------------------------------------
		// Collections
		// -----------------------------------------
		$collection = ($this->EE->TMPL->fetch_param('collection') != FALSE) ? $this->EE->TMPL->fetch_param('collection'): 'default';

		// Multiple Collections?
		if (strpos($collection, '|') !== FALSE)
		{
			$collection = explode('|', $collection);
			$cols = array();

			foreach ($collection as $col)
			{
				if (isset($this->collections[$col]) == TRUE) $cols[] = $this->collections[$col];
			}

			$this->EE->db->where_in('rs.collection_id', $cols);
		}
		else
		{
			if (isset($this->collections[$collection]) == TRUE) $this->EE->db->where('rs.collection_id', $this->collections[$collection]);
			else $this->EE->db->where('rs.collection_id', $this->default_collection);
		}

		// Execute the Query
		$query = $this->EE->db->get();

		// We need stats ofcourse
		if ($query->num_rows() == 0)
		{
			$this->EE->TMPL->log_item('CHANNEL RATINGS: No Stats found!');

			if (strpos($this->EE->TMPL->tagdata, $prefix.'no_rating')) {
				return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_rating', $this->EE->TMPL->tagdata);
			}
		}

		$results = $query->result();
		$query->free_result();

		// Some Defaults
		$precision		= ( $this->EE->TMPL->fetch_param('precision') !== FALSE) ? $this->EE->TMPL->fetch_param('precision'): 2;
		$g_precision	= ( $precision > 0 ) ? 2: 0;
		$thousands		= ( $this->EE->TMPL->fetch_param('thousands') ) ? $this->EE->TMPL->fetch_param('thousands'): ',';
		$fractions		= ($this->EE->TMPL->fetch_param('fractions') ) ? $this->EE->TMPL->fetch_param('fractions'): '.';
		$scale			= ( $this->EE->TMPL->fetch_param('scale') ) ? $this->EE->TMPL->fetch_param('scale'): 5;
		$theme			= ( $this->EE->TMPL->fetch_param('theme') ) ? $this->EE->TMPL->fetch_param('theme'): 'default';
		$theme_url		= $this->EE->ratings_helper->define_theme_url() . "themes/{$theme}/";

		// -----------------------------------------
		// Loop over all rating fields
		// -----------------------------------------
		$vars = array();

		// Default stats
		$vars[$prefix.'overall:avg'] = 0;
		$vars[$prefix.'overall:total'] = 0;
		$vars[$prefix.'overall:sum'] = 0;
		$vars[$prefix.'overall:latest_date'] = false;
		$vars[$prefix.'overall:stars'] = '';

		foreach ($results as $field)
		{
			// Check for the "MASTER" field which is 0
			if ($field->field_id == 0) $field->short_name = 'overall';

			// -----------------------------------------
			// Template Vars
			// -----------------------------------------
			$vars[$prefix.$field->short_name.':avg'] = number_format($field->rating_avg, $precision, $fractions, $thousands);
			$vars[$prefix.$field->short_name.':total'] = $field->rating_total;
			$vars[$prefix.$field->short_name.':sum'] = $field->rating_sum;
			$vars[$prefix.$field->short_name.':latest_date'] = $field->rating_last_date;

			// Special Theme per field?
			$theme_url_final = ($this->EE->TMPL->fetch_param('theme:'.$field->short_name) != FALSE) ? $this->EE->ratings_helper->define_theme_url() . 'themes/'.$this->EE->TMPL->fetch_param('theme:'.$field->short_name).'/' : $theme_url;

			// Parse Images
			$vars[$prefix.$field->short_name.':stars'] = $this->parse_star_images(number_format($field->rating_avg, $precision, '.', ''), $precision, $scale, $theme_url_final);

			// -----------------------------------------
			// AVG With Remainder
			// -----------------------------------------
			$number	= explode($fractions, number_format($field->rating_avg, $precision, $fractions, '' ));

			//	Handle Decimal (Remainder) //  This formats the remainder portion of a decimal number to 25, 20, 75
			if ( isset($number['1']) === FALSE ) $number['1'] = 0;
			elseif ( $number['1'] < 25 ) $number['1'] = 0;
			elseif ( $number['1'] >= 25 AND $number['1'] < 50 ) $number['1']	= 25;
			elseif ( $number['1'] >= 50 AND $number['1'] < 75 ) $number['1']	= 50;
			else $number['1']	= 75;

			$vars[$prefix.$field->short_name.':avg_dec'] = implode($fractions, $number);
		}

		// Parse variables
		$this->EE->TMPL->tagdata = $this->EE->TMPL->parse_variables_row($this->EE->TMPL->tagdata, $vars);

		return $this->EE->TMPL->tagdata;
	}

	// ********************************************************************************* //

	public function id()
	{
		// Variable prefix
		$prefix = $this->EE->TMPL->fetch_param('prefix', 'rating') . ':';

		// Rating
		$rating_id = $this->EE->TMPL->fetch_param('rating_id');

		// We need a Comment ID
		if ($rating_id == FALSE OR $this->EE->ratings_helper->is_natural_number($rating_id) == FALSE)
		{
			$this->EE->TMPL->log_item('CHANNEL RATINGS: Rating ID could not be resolved OR was malformatted');
			return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_rating', $this->EE->TMPL->tagdata);
		}

		// -----------------------------------------
		// Grab the rating
		// -----------------------------------------
		$query = $this->EE->db->select('*')->from('exp_channel_ratings')->where('rating_id', $rating_id)->get();
		if ($query->num_rows() == 0)
		{
			$this->EE->TMPL->log_item('CHANNEL RATINGS: Rating ID could not be found');
			return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_rating', $this->EE->TMPL->tagdata);
		}

		// -----------------------------------------
		// Start SQL
		// -----------------------------------------
		$this->EE->db->select("cs.*, rf.short_name");
		$this->EE->db->from('exp_channel_ratings cs');
		$this->EE->db->join('exp_channel_ratings_fields rf', 'cs.field_id = rf.field_id', 'left');
		$this->EE->db->where('cs.item_id', $query->row('item_id'));
		$this->EE->db->where('cs.entry_id', $query->row('entry_id'));
		$this->EE->db->where('cs.collection_id', $query->row('collection_id'));
		$this->EE->db->where('cs.rating_type', $query->row('rating_type'));
		$this->EE->db->where('cs.rating_author_id', $query->row('rating_author_id'));
		$this->EE->db->where('cs.rating_date', $query->row('rating_date'));
		$query = $this->EE->db->get();

		// We need stats ofcourse
		if ($query->num_rows() == 0)
		{
			$this->EE->TMPL->log_item('CHANNEL RATINGS: No Stats found!');
			return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_rating', $this->EE->TMPL->tagdata);
		}

		$results = $query->result();
		$query->free_result();

		// Some Defaults
		$precision		= ( $this->EE->TMPL->fetch_param('precision') !== FALSE ) ? $this->EE->TMPL->fetch_param('precision'): 2;
		$g_precision	= ( $precision > 0 ) ? 2: 0;
		$thousands		= ( $this->EE->TMPL->fetch_param('thousands') ) ? $this->EE->TMPL->fetch_param('thousands'): ',';
		$fractions		= ($this->EE->TMPL->fetch_param('fractions') ) ? $this->EE->TMPL->fetch_param('fractions'): '.';
		$scale			= ( $this->EE->TMPL->fetch_param('scale') ) ? $this->EE->TMPL->fetch_param('scale'): 5;
		$theme			= ( $this->EE->TMPL->fetch_param('theme') ) ? $this->EE->TMPL->fetch_param('theme'): 'default';
		$theme_url		= $this->EE->ratings_helper->define_theme_url() . "themes/{$theme}/";

		// -----------------------------------------
		// Loop over all rating fields!
		// -----------------------------------------
		$vars = array();
		foreach ($results as $field)
		{
			// Check for the "MASTER" field which is 0
			if ($field->field_id == 0)
			{
				$field->short_name = 'overall';
				$vars[$prefix.$field->short_name.':rating'] = number_format($field->rating, $precision, $fractions, $thousands);
			}
			else
			{
				$vars[$prefix.$field->short_name.':rating'] = $field->rating;
			}

			$vars[$prefix.$field->short_name.':date'] = $field->rating_date;

			// Special Theme per field?
			$theme_url_final = ($this->EE->TMPL->fetch_param('theme:'.$field->short_name) != FALSE) ? $this->EE->ratings_helper->define_theme_url() . 'themes/'.$this->EE->TMPL->fetch_param('theme:'.$field->short_name).'/' : $theme_url;

			// Parse Images
			$vars[$prefix.$field->short_name.':stars'] = $this->parse_star_images(number_format($field->rating, $precision, '.', ''), $precision, $scale, $theme_url_final);
		}

		// Parse variables
		$this->EE->TMPL->tagdata = $this->EE->TMPL->parse_variables_row($this->EE->TMPL->tagdata, $vars);

		return $this->EE->TMPL->tagdata;
	}

	// ********************************************************************************* //

	public function rating_comment()
	{
		// Variable prefix
		$prefix = $this->EE->TMPL->fetch_param('prefix', 'rating') . ':';

		/** ----------------------------------------
		/** Comment ID
		/** ----------------------------------------*/
		$comment_id = $this->EE->TMPL->fetch_param('comment_id');

		// We need a Comment ID
		if ($comment_id == FALSE OR $this->EE->ratings_helper->is_natural_number($comment_id) == FALSE)
		{
			$this->EE->TMPL->log_item('CHANNEL RATINGS: Comment ID could not be resolved OR was malformatted');
			return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_rating', $this->EE->TMPL->tagdata);
		}

		/** ----------------------------------------
		/** Start SQL
		/** ----------------------------------------*/
		$this->EE->db->select("cs.*, rf.short_name");
		$this->EE->db->from('exp_channel_ratings cs');
		$this->EE->db->join('exp_channel_ratings_fields rf', 'cs.field_id = rf.field_id', 'left');
		$this->EE->db->where('cs.item_id', $comment_id);
		$this->EE->db->where('cs.rating_status', 1);
		$this->EE->db->where('cs.rating_type', $this->TYPES['comment_review']);

		// -----------------------------------------
		// Collections
		// -----------------------------------------
		$collection = ($this->EE->TMPL->fetch_param('collection') != FALSE) ? $this->EE->TMPL->fetch_param('collection'): 'default';

		// Multiple Collections?
		if (strpos($collection, '|') !== FALSE)
		{
			$collection = explode('|', $collection);
			$cols = array();

			foreach ($collection as $col)
			{
				if (isset($this->collections[$col]) == TRUE) $cols[] = $this->collections[$col];
			}

			$this->EE->db->where_in('cs.collection_id', $cols);
		}
		else
		{
			if (isset($this->collections[$collection]) == TRUE) $this->EE->db->where('cs.collection_id', $this->collections[$collection]);
			else $this->EE->db->where('cs.collection_id', $this->default_collection);
		}

		/** ----------------------------------------
		/** Grab Ratings
		/** ----------------------------------------*/
		$query = $this->EE->db->get();

		// We need stats ofcourse
		if ($query->num_rows() == 0)
		{
			$this->EE->TMPL->log_item('CHANNEL RATINGS: No Stats found!');
			return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_rating', $this->EE->TMPL->tagdata);
		}

		$results = $query->result();
		$query->free_result();

		// Some Defaults
		$precision		= ( $this->EE->TMPL->fetch_param('precision') !== FALSE ) ? $this->EE->TMPL->fetch_param('precision'): 2;
		$g_precision	= ( $precision > 0 ) ? 2: 0;
		$thousands		= ( $this->EE->TMPL->fetch_param('thousands') ) ? $this->EE->TMPL->fetch_param('thousands'): ',';
		$fractions		= ($this->EE->TMPL->fetch_param('fractions') ) ? $this->EE->TMPL->fetch_param('fractions'): '.';
		$scale			= ( $this->EE->TMPL->fetch_param('scale') ) ? $this->EE->TMPL->fetch_param('scale'): 5;
		$theme			= ( $this->EE->TMPL->fetch_param('theme') ) ? $this->EE->TMPL->fetch_param('theme'): 'default';
		$theme_url		= $this->EE->ratings_helper->define_theme_url() . "themes/{$theme}/";

		/** ----------------------------------------
		/** Loop over all rating fields!
		/** ----------------------------------------*/
		$vars = array();
		foreach ($results as $field)
		{
			// Check for the "MASTER" field which is 0
			if ($field->field_id == 0)
			{
				$field->short_name = 'overall';
				$vars[$prefix.$field->short_name.':rating'] = number_format($field->rating, $precision, $fractions, $thousands);
			}
			else
			{
				$vars[$prefix.$field->short_name.':rating'] = $field->rating;
			}

			$vars[$prefix.$field->short_name.':date'] = $field->rating_date;

			// Special Theme per field?
			$theme_url_final = ($this->EE->TMPL->fetch_param('theme:'.$field->short_name) != FALSE) ? $this->EE->ratings_helper->define_theme_url() . 'themes/'.$this->EE->TMPL->fetch_param('theme:'.$field->short_name).'/' : $theme_url;

			// Parse Images
			$vars[$prefix.$field->short_name.':stars'] = $this->parse_star_images(number_format($field->rating, $precision, '.', ''), $precision, $scale, $theme_url_final);
		}

		// Parse variables
		$this->EE->TMPL->tagdata = $this->EE->TMPL->parse_variables_row($this->EE->TMPL->tagdata, $vars);

		return $this->EE->TMPL->tagdata;
	}

	// ********************************************************************************* //

	public function rating_comment_avg()
	{
		// Variable prefix
		$prefix = $this->EE->TMPL->fetch_param('prefix', 'rating') . ':';

		// Entry ID
		$entry_id = $this->EE->ratings_helper->get_entry_id_from_param();

		// We need an entry_id
		if ($entry_id == FALSE)
		{
			$this->EE->TMPL->log_item('CHANNEL RATINGS: Entry ID could not be resolved');
			return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_rating', $this->EE->TMPL->tagdata);
		}

		// -----------------------------------------
		// What Collection
		// -----------------------------------------
		$collection_id = $this->default_collection;
		$coll_param = $this->EE->TMPL->fetch_param('collection');
		if ($coll_param != FALSE)
		{
			if (isset($this->collections[$coll_param]) == TRUE) $collection_id = $this->collections[$coll_param];
			else
			{
				$this->EE->TMPL->log_item('CHANNEL RATINGS: Collection "'.$coll_param.'" does not exist!');
				return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_access', $this->EE->TMPL->tagdata);
			}
		}

		// Lets grab the stats for his entry
		$query = $this->EE->db->select("rs.*, rf.short_name")
				->from('exp_channel_ratings_stats rs')->join('exp_channel_ratings_fields rf', 'rs.field_id = rf.field_id', 'left')
				->where('rs.entry_id', $entry_id)->where('rs.collection_id', $collection_id)->where('rs.rating_type', $this->TYPES['comment_review'])->get();

		// We need stats ofcourse
		if ($query->num_rows() == 0)
		{
			$this->EE->TMPL->log_item('CHANNEL RATINGS: No Stats found!');
			return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_rating', $this->EE->TMPL->tagdata);
		}

		$results = $query->result();
		$query->free_result();

		// Some Defaults
		$precision		= ( $this->EE->TMPL->fetch_param('precision') !== FALSE ) ? $this->EE->TMPL->fetch_param('precision'): 2;
		$g_precision	= ( $precision > 0 ) ? 2: 0;
		$thousands		= ( $this->EE->TMPL->fetch_param('thousands') ) ? $this->EE->TMPL->fetch_param('thousands'): ',';
		$fractions		= ($this->EE->TMPL->fetch_param('fractions') ) ? $this->EE->TMPL->fetch_param('fractions'): '.';
		$scale			= ( $this->EE->TMPL->fetch_param('scale') ) ? $this->EE->TMPL->fetch_param('scale'): 5;
		$theme			= ( $this->EE->TMPL->fetch_param('theme') ) ? $this->EE->TMPL->fetch_param('theme'): 'default';
		$theme_url		= $this->EE->ratings_helper->define_theme_url() . "themes/{$theme}/";

		/** ----------------------------------------
		/** Loop over all rating fields!
		/** ----------------------------------------*/
		$vars = array();
		foreach ($results as $field)
		{
			// Check for the "MASTER" field which is 0
			if ($field->field_id == 0) $field->short_name = 'overall';

			// Vars
			$vars[$prefix.$field->short_name.':avg'] = number_format($field->rating_avg, $precision, $fractions, $thousands);
			$vars[$prefix.$field->short_name.':total'] = $field->rating_total;
			$vars[$prefix.$field->short_name.':sum'] = $field->rating_sum;
			$vars[$prefix.$field->short_name.':latest_date'] = $field->rating_last_date;

			// Special Theme per field?
			$theme_url_final = ($this->EE->TMPL->fetch_param('theme:'.$field->short_name) != FALSE) ? $this->EE->ratings_helper->define_theme_url() . 'themes/'.$this->EE->TMPL->fetch_param('theme:'.$field->short_name).'/' : $theme_url;


			// Parse Images
			$vars[$prefix.$field->short_name.':stars'] = $this->parse_star_images(number_format($field->rating_avg, $precision, '.', ''), $precision, $scale, $theme_url_final);
		}

		// Parse variables
		$this->EE->TMPL->tagdata = $this->EE->TMPL->parse_variables_row($this->EE->TMPL->tagdata, $vars);

		return $this->EE->TMPL->tagdata;
	}

	// ********************************************************************************* //

	public function rating_status()
	{
		// Variable prefix
		$prefix = $this->EE->TMPL->fetch_param('prefix', 'rating') . ':';

		// -----------------------------------------
		// What Collection
		// -----------------------------------------
		$collection_id = $this->default_collection;
		$coll_param = $this->EE->TMPL->fetch_param('collection');
		if ($coll_param != FALSE)
		{
			if (isset($this->collections[$coll_param]) == TRUE) $collection_id = $this->collections[$coll_param];
			else
			{
				$this->EE->TMPL->log_item('CHANNEL RATINGS: Collection "'.$coll_param.'" does not exist!');
				return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_access', $this->EE->TMPL->tagdata);
			}
		}

		/** ----------------------------------------
		/** Which Type?
		/** ----------------------------------------*/
		$rating_type = $this->detect_rating_type_from_params();

		if ($rating_type['type'] < 1)
		{
			$this->EE->TMPL->log_item('CHANNEL RATINGS: Rating Type could not be determined');
			return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_access', $this->EE->TMPL->tagdata);
		}

		// Easy to remember
		$item_id = $rating_type['item_id'];
		$rating_type = $rating_type['type'];

		/** ----------------------------------------
		/** Has this user already rated?
		/** ----------------------------------------*/
		$already_rated = $this->EE->ratings_model->if_already_rated($rating_type, $item_id, $collection_id);
		$cond[$prefix.'already_rated']	= $already_rated;
		$cond[$prefix.'not_rated']	= (($already_rated == FALSE) ? TRUE : FALSE);

		// Parse Conditionals
		$this->EE->TMPL->tagdata = $this->EE->functions->prep_conditionals($this->EE->TMPL->tagdata, $cond);

		return $this->EE->TMPL->tagdata;
	}

	// ********************************************************************************* //

	public function total_ratings()
	{
		// Variable prefix
		$prefix = $this->EE->TMPL->fetch_param('prefix', 'rating') . ':';

		// Entry ID
		$entry_id = $this->EE->ratings_helper->get_entry_id_from_param();

		// We need an entry_id
		if ($entry_id == FALSE)
		{
			$this->EE->TMPL->log_item('CHANNEL RATINGS: Entry ID could not be resolved');
			return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_rating', $this->EE->TMPL->tagdata);
		}

		// -----------------------------------------
		// What Collection
		// -----------------------------------------
		$collection_id = $this->default_collection;
		$coll_param = $this->EE->TMPL->fetch_param('collection');
		if ($coll_param != FALSE)
		{
			if (isset($this->collections[$coll_param]) == TRUE) $collection_id = $this->collections[$coll_param];
			else
			{
				$this->EE->TMPL->log_item('CHANNEL RATINGS: Collection "'.$coll_param.'" does not exist!');
				return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_access', $this->EE->TMPL->tagdata);
			}
		}

		// Lets grab the stats for his entry
		$query = $this->EE->db->select('rating_total')->from('exp_channel_ratings_stats rs')->where('rs.entry_id', $entry_id)->where('rs.collection_id', $collection_id)->where('field_id', 0)->get();

		// We need stats ofcourse
		if ($query->num_rows() == 0)
		{
			$total_ratings = 0;
		}
		else
		{
			$total_ratings = $query->row('rating_total');
		}

		$query->free_result();

		$vars = array();
		$vars[$prefix.'total'] = $total_ratings;

		// Parse variables
		$this->EE->TMPL->tagdata = $this->EE->TMPL->parse_variables_row($this->EE->TMPL->tagdata, $vars);

		return $this->EE->TMPL->tagdata;
	}

	// ********************************************************************************* //

	public function my_ratings()
	{
		// Variable prefix
		$prefix = $this->EE->TMPL->fetch_param('prefix', 'rating') . ':';

		// -----------------------------------------
		// Some Defaults
		// -----------------------------------------
		$limit = ($this->EE->ratings_helper->is_natural_number($this->EE->TMPL->fetch_param('limit')) != FALSE) ? $this->EE->TMPL->fetch_param('limit') : 30;

		// -----------------------------------------
		// Start SQL
		// -----------------------------------------
		$this->EE->db->select("cs.*, rf.short_name, rf.title AS field_title");
		$this->EE->db->from('exp_channel_ratings cs');
		$this->EE->db->join('exp_channel_ratings_fields rf', 'cs.field_id = rf.field_id', 'left');

		// -----------------------------------------
		// Rating Author ID?
		// -----------------------------------------
		$rating_author_id = $this->EE->TMPL->fetch_param('rating_author_id');
		if ($rating_author_id == 'CURRENT_USER')
		{
			$this->EE->db->where('cs.rating_author_id', $this->EE->session->userdata['member_id']);
		}
		elseif ($rating_author_id != FALSE)
		{
			// Multiple Authors?
			if (strpos($rating_author_id, '|') !== FALSE)
			{
				$cols = explode('|', $rating_author_id);
				$this->EE->db->where_in('cs.rating_author_id', $cols);
			}
			else
			{
				$this->EE->db->where('cs.rating_author_id', $rating_author_id);
			}
		}

		// -----------------------------------------
		// Rating Type
		// -----------------------------------------
		$rating_type = ($this->EE->TMPL->fetch_param('type') != FALSE) ? $this->EE->TMPL->fetch_param('type'): FALSE;

		if ($rating_type != FALSE OR isset($this->TYPES[$rating_type]) != FALSE)
		{
			$this->EE->db->where('cs.rating_type', $this->TYPES[$rating_type]);
		}


		// -----------------------------------------
		// Item ID
		// -----------------------------------------
		$rating_type = $this->detect_rating_type_from_params();

		if ($rating_type['type'] >= 1)
		{
			// Easy to remember
			$item_id = $rating_type['item_id'];
			$rating_type = $rating_type['type'];

			// Entries are handled different
			if ($this->TYPES_INV[$rating_type] == 'entry' OR $this->TYPES_INV[$rating_type] == 'comment_review')
			{
				$this->EE->db->where('cs.entry_id', $item_id);
			}
			else  $this->EE->db->where('cs.item_id', $item_id);

			$this->EE->db->where('cs.rating_type', $rating_type);
		}

		// -----------------------------------------
		// Collections
		// -----------------------------------------
		$collection = ($this->EE->TMPL->fetch_param('collection') != FALSE) ? $this->EE->TMPL->fetch_param('collection'): 'default';

		// Multiple Collections?
		if (strpos($collection, '|') !== FALSE)
		{
			$collection = explode('|', $collection);
			$cols = array();

			foreach ($collection as $col)
			{
				if (isset($this->collections[$col]) == TRUE) $cols[] = $this->collections[$col];
			}

			$this->EE->db->where_in('cs.collection_id', $cols);
		}
		else
		{
			if (isset($this->collections[$collection]) == TRUE) $this->EE->db->where('cs.collection_id', $this->collections[$collection]);
			else $this->EE->db->where('cs.collection_id', $this->default_collection);
		}

		// -----------------------------------------
		// Grab Ratings
		// -----------------------------------------
		$this->EE->db->limit($limit);
		$query = $this->EE->db->get();

		// We need stats ofcourse
		if ($query->num_rows() == 0)
		{
			$this->EE->TMPL->log_item('CHANNEL RATINGS: No Ratings found!');
			return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_ratings', $this->EE->TMPL->tagdata);
		}

		$results = $query->result();
		$query->free_result();

		// -----------------------------------------
		// Some Defaults
		// -----------------------------------------
		$precision		= ( $this->EE->TMPL->fetch_param('precision') !== FALSE ) ? $this->EE->TMPL->fetch_param('precision'): 2;
		$g_precision	= ( $precision > 0 ) ? 2: 0;
		$thousands		= ( $this->EE->TMPL->fetch_param('thousands') ) ? $this->EE->TMPL->fetch_param('thousands'): ',';
		$fractions		= ($this->EE->TMPL->fetch_param('fractions') ) ? $this->EE->TMPL->fetch_param('fractions'): '.';
		$scale			= ( $this->EE->TMPL->fetch_param('scale') ) ? $this->EE->TMPL->fetch_param('scale'): 5;
		$theme			= ( $this->EE->TMPL->fetch_param('theme') ) ? $this->EE->TMPL->fetch_param('theme'): 'default';
		$theme_url		= $this->EE->ratings_helper->define_theme_url() . "themes/{$theme}/";

		// -----------------------------------------
		// Loop over all results and categorize!
		// -----------------------------------------
		$ratings = array();

		foreach ($results as $row)
		{
			$ratings[$row->rating_type.'-'.$row->rating_date][] = $row;
		}

		unset($results);

		// -----------------------------------------
		// Loop over all individual Ratings
		// -----------------------------------------
		$final = '';
		$count = 0;
		$field_pair_data = $this->EE->ratings_helper->fetch_data_between_var_pairs($prefix.'fields', $this->EE->TMPL->tagdata);

		foreach ($ratings as $rating)
		{
			// -----------------------------------------
			// Reset
			// -----------------------------------------
			$count++;
			$vars = array();
			$tempfinal = $this->EE->TMPL->tagdata;
			$field_pair_final = '';

			// -----------------------------------------
			// Loop over all fields
			// -----------------------------------------
			foreach ($rating as $field)
			{
				$tVars = array();

				// Check for the "MASTER" field which is 0
				if ($field->field_id == 0)
				{
					$field->short_name = 'overall';
					$field->field_title = 'Overall';
					$tVars[$prefix.'rating'] = number_format($field->rating, $precision, $fractions, $thousands);
				}
				else
				{
					$tVars[$prefix.'rating'] = $field->rating;
				}


				$tVars[$prefix.'field_title'] = $field->field_title;
				$tVars[$prefix.'field_name'] = $field->short_name;
				$tVars[$prefix.'field_id'] = $field->field_id;

				// Special Theme per field?
				$theme_url_final = ($this->EE->TMPL->fetch_param('theme:'.$field->short_name) != FALSE) ? $this->EE->ratings_helper->define_theme_url() . 'themes/'.$this->EE->TMPL->fetch_param('theme:'.$field->short_name).'/' : $theme_url;

				// Parse Images
				$tVars[$prefix.'stars'] = $this->parse_star_images(number_format($field->rating, $precision, '.', ''), $precision, $scale, $theme_url_final);

				$temp = $this->EE->TMPL->parse_variables_row($field_pair_data, $tVars);
				$field_pair_final .= $temp;
			}

			$tempfinal = $this->EE->ratings_helper->swap_var_pairs($prefix.'fields', $field_pair_final, $tempfinal);

			// -----------------------------------------
			// Add Vars
			// -----------------------------------------
			$vars[$prefix.'date'] = $rating[0]->rating_date;
			$vars[$prefix.'item_id'] = $rating[0]->rating_id;
			$vars[$prefix.'entry_id'] = $rating[0]->entry_id;

			// -----------------------------------------
			// Parse Variables
			// -----------------------------------------
			$final .= $this->EE->TMPL->parse_variables_row($tempfinal, $vars);
		}

		return $final;
	}

	// ********************************************************************************* //

	public function new_rating()
	{
		// Variable prefix
		$prefix = $this->EE->TMPL->fetch_param('prefix', 'rating') . ':';

		// Some vars
		$form_data = array();
		$vars = array();
		$cond = array();
		$cond[$prefix.'captcha'] = FALSE;

		// -----------------------------------------
		// Which Type?
		// -----------------------------------------
		$rating_type = $this->detect_rating_type_from_params();

		if ($rating_type['type'] < 1)
		{
			$this->EE->TMPL->log_item('CHANNEL RATINGS: Rating Type could not be determined');
			return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_access', $this->EE->TMPL->tagdata);
		}

		// Easy to remember
		$item_id = $rating_type['item_id'];
		$rating_type = $rating_type['type'];
		$rating_type_name = $this->TYPES_INV[$rating_type];

		if ($item_id < 1)
		{
			$this->EE->TMPL->log_item('CHANNEL RATINGS: Item Id could not be determined');
			return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_access', $this->EE->TMPL->tagdata);
		}

		// -----------------------------------------
		// What Collection
		// -----------------------------------------
		$collection_id = $this->default_collection;
		$coll_param = $this->EE->TMPL->fetch_param('collection');
		if ($coll_param != FALSE)
		{
			if (isset($this->collections[$coll_param]) == TRUE) $collection_id = $this->collections[$coll_param];
			else
			{
				$this->EE->TMPL->log_item('CHANNEL RATINGS: Collection "'.$coll_param.'" does not exist!');
				return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_access', $this->EE->TMPL->tagdata);
			}
		}

		// -----------------------------------------
		// Form Data
		// -----------------------------------------
		$form_data['allow_guests'] = FALSE;
		$form_data['require_captcha'] = FALSE;
		$form_data['allow_multiple'] = FALSE;
		$form_data['collection_id'] = $collection_id;
		$form_data['max_value'] = ($this->EE->TMPL->fetch_param('max_value') > 2) ? $this->EE->TMPL->fetch_param('max_value') : 5;
		$form_data['min_value'] = ($this->EE->TMPL->fetch_param('min_value') >= 0) ? $this->EE->TMPL->fetch_param('min_value') : 0;
		$form_data['return'] = (isset($this->EE->TMPL->tagparams['return']) == FALSE) ? $this->EE->uri->uri_string().'/' : $this->EE->TMPL->tagparams['return'];
		$form_data['rating_type'] = $rating_type;
		$form_data['rating_type_name'] = $rating_type_name;
		$form_data['item_id'] = $item_id;
		$form_data['ip_address'] = $this->EE->input->ip_address();
		$form_data['site_id'] = $this->site_id;

		// -----------------------------------------
		// Allow Guests
		// -----------------------------------------
		if ($this->EE->TMPL->fetch_param('allow_guests') != 'yes' && $this->EE->session->userdata['member_id'] == 0 )
		{
			$this->EE->TMPL->log_item('CHANNEL RATINGS: Guests are not allowed to rate.');
			return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_access', $this->EE->TMPL->tagdata);
		}
		elseif ($this->EE->TMPL->fetch_param('allow_guests') == 'yes')
		{
			$form_data['allow_guests'] = TRUE;
		}

		// -----------------------------------------
		// Allow Multiple
		// -----------------------------------------
		if ( $this->EE->TMPL->fetch_param('allow_multiple') == 'yes')
		{
			$form_data['allow_multiple'] = TRUE;
		}

		// -----------------------------------------
		// Has this already been rated
		// -----------------------------------------
		$already_rated = $this->EE->ratings_model->if_already_rated($rating_type, $item_id, $form_data['collection_id']);
		$cond[$prefix.'already_rated']	= $already_rated;
		$cond[$prefix.'not_rated']	= (($already_rated == FALSE) ? TRUE : FALSE);

		// -----------------------------------------
		// Do we need captcha
		// -----------------------------------------
		if ( $this->EE->TMPL->fetch_param('captcha') == 'yes')
		{
			// Create the captcha
			$vars[$prefix.'captcha'] = $this->EE->functions->create_captcha();

			// Did we get an captcha back? (conditional)
			$cond[$prefix.'captcha'] = (($vars[$prefix.'captcha'] != FALSE) ? TRUE : FALSE);

			// Pass it on
			$form_data['require_captcha'] = (($vars[$prefix.'captcha'] != FALSE) ? TRUE : FALSE);
		}

		// XID
		$XID = (isset($_POST['XID']) == TRUE) ? $_POST['XID'] : '';

		// -----------------------------------------
		// Hidden Fields & Form Data
		// -----------------------------------------
		$hidden_fields = array();
		$hidden_fields['ACT'] = $this->EE->ratings_helper->get_router_url('act_id', 'insert_rating');
		$hidden_fields['FDATA'] = $this->EE->ratings_helper->encrypt_string(serialize($form_data));
		$hidden_fields['XID'] = $XID;

		$formdata = array();
		$formdata['hidden_fields'] = $hidden_fields;
		$formdata['action']	= $this->EE->TMPL->fetch_param('attr:action', $this->EE->functions->fetch_current_uri());
		$formdata['name']	= ($this->EE->TMPL->fetch_param('form_name') != FALSE) ? $this->EE->TMPL->fetch_param('form_name'): 'new_rating';
		$formdata['id']		= ($this->EE->TMPL->fetch_param('form_id') != FALSE) ? $this->EE->TMPL->fetch_param('form_id'): 'new_rating';
		$formdata['class']	= ($this->EE->TMPL->fetch_param('form_class') != FALSE) ? $this->EE->TMPL->fetch_param('form_class'): '';
		$formdata['onsubmit'] = ($this->EE->TMPL->fetch_param('onsubmit') != FALSE) ? $this->EE->TMPL->fetch_param('onsubmit'): '';

		// -----------------------------------------
		// Get Rating Fields
		// -----------------------------------------
		$rating_fields = $this->EE->ratings_model->get_rating_fields($collection_id);

		if (empty($rating_fields) == TRUE)
		{
			$this->EE->TMPL->log_item('CHANNEL RATINGS: No rating fields found for this collection');
			return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_access', $this->EE->TMPL->tagdata);
		}

		// -----------------------------------------
		// Parse Rating Fields
		// -----------------------------------------
		$field_pair_data = $this->EE->ratings_helper->fetch_data_between_var_pairs($prefix.'fields', $this->EE->TMPL->tagdata);

		$field_pair_final = '';

		foreach ($rating_fields as $row)
		{
			$tVars = array();
			$tVars[$prefix.'form_name'] = 'rating['.$row->short_name.']';
			$tVars[$prefix.'field_title'] = $row->title;
			$tVars[$prefix.'field_name'] = $row->short_name;

			$temp = $this->EE->TMPL->parse_variables_row($field_pair_data, $tVars);
			$field_pair_final .= $temp;
		}

		$this->EE->TMPL->tagdata = $this->EE->ratings_helper->swap_var_pairs($prefix.'fields', $field_pair_final, $this->EE->TMPL->tagdata);

		// Parse variables
		$this->EE->TMPL->tagdata = $this->EE->TMPL->parse_variables_row($this->EE->TMPL->tagdata, $vars);

		// Parse Conditionals
		$this->EE->TMPL->tagdata = $this->EE->functions->prep_conditionals($this->EE->TMPL->tagdata, $cond);

		$OUT = $this->EE->functions->form_declaration($formdata);
		$OUT .= $this->EE->TMPL->tagdata;
		$OUT .= '</form>';

		return $OUT;
	}

	// ********************************************************************************* //

	public function likes()
	{
		// Variable prefix
		$prefix = $this->EE->TMPL->fetch_param('prefix', 'rating') . ':';

		/** ----------------------------------------
		/** Which Type?
		/** ----------------------------------------*/
		$rating_type = $this->detect_rating_type_from_params();

		if ($rating_type['type'] < 1)
		{
			$this->EE->TMPL->log_item('CHANNEL RATINGS: Rating Type could not be determined');
			return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_rating', $this->EE->TMPL->tagdata);
		}

		// Easy to remember
		$item_id = $rating_type['item_id'];
		$rating_type = $rating_type['type'];
		$rating_type_name = $this->TYPES_INV[$rating_type];

		if ($item_id < 1)
		{
			$this->EE->TMPL->log_item('CHANNEL RATINGS: Item Id could not be determined');
			return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_rating', $this->EE->TMPL->tagdata);
		}

		// -----------------------------------------
		// What Collection
		// -----------------------------------------
		$collection_id = $this->default_collection;
		$coll_param = $this->EE->TMPL->fetch_param('collection');
		if ($coll_param != FALSE)
		{
			if (isset($this->collections[$coll_param]) == TRUE) $collection_id = $this->collections[$coll_param];
			else
			{
				$this->EE->TMPL->log_item('CHANNEL RATINGS: Collection "'.$coll_param.'" does not exist!');
				return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_access', $this->EE->TMPL->tagdata);
			}
		}

		/** ----------------------------------------
		/** Lets grab the stats for his entry
		/** ----------------------------------------*/
		$this->EE->db->select('*');
		$this->EE->db->from('exp_channel_ratings_likes');
		if ($rating_type_name == 'entry') $this->EE->db->where('entry_id', $item_id);
		else $this->EE->db->where('item_id', $item_id);
		$this->EE->db->where('collection_id', $collection_id);
		$this->EE->db->where('like_type', $rating_type);
		$this->EE->db->where('stats_row', 1);
		$query = $this->EE->db->get();

		$cond = array();
		$vars = array();
		$cond[$prefix.'no_votes'] = FALSE;
		$cond[$prefix.'voted'] = FALSE;
		$cond[$prefix.'not_voted'] = TRUE;

		// We need stats ofcourse
		if ($query->num_rows() == 0)
		{
			$this->EE->TMPL->log_item('CHANNEL RATINGS: No Likes Stats found!');
			$cond[$prefix.'no_votes'] = TRUE;
		}

		$row = $query->row();
		$query->free_result();

		$liked = 0;
		$disliked = 0;
		$latest_date = 0;

		if (isset($row->liked) != FALSE && $row->liked != FALSE) $liked = $row->liked;
		if (isset($row->disliked) != FALSE && $row->disliked != FALSE) $disliked = $row->disliked;
		if (isset($row->like_date) != FALSE && $row->like_date != FALSE) $latest_date = $row->like_date;

		/** ----------------------------------------
		/**  Vars
		/** ----------------------------------------*/
		$vars[$prefix.'liked'] = $liked;
		$vars[$prefix.'disliked'] = $disliked;
		$vars[$prefix.'total'] = $disliked + $liked;
		$vars[$prefix.'score'] = $liked - $disliked;
		$vars[$prefix.'liked_p'] = ceil( @( $vars[$prefix.'liked'] / $vars[$prefix.'total'] ) * 100 );
		$vars[$prefix.'disliked_p'] = ceil( @( $vars[$prefix.'disliked'] / $vars[$prefix.'total'] ) * 100 );
		$vars[$prefix.'latest_vote'] = $latest_date;
		$vars[$prefix.'delete_url'] = '';

		/** ----------------------------------------
		/**  Already Liked?
		/** ----------------------------------------*/
		$rlike_id = $this->EE->ratings_model->if_already_liked($rating_type, $item_id, $collection_id);
		$cond[$prefix.'voted']	= ($rlike_id > 0) ? TRUE : FALSE;
		$cond[$prefix.'not_voted']	= ($rlike_id == 0) ? TRUE : FALSE;

		if ($rlike_id > 0)
		{
			$ACT_URL = $this->EE->ratings_helper->get_router_url('url', 'insert_like');
			$arr = array('rlike_id' => $rlike_id, 'action' => 'delete');
			$vars[$prefix.'delete_url'] = $ACT_URL . '&amp;data=' . base64_encode(serialize($arr));
		}

		// Parse variables
		$this->EE->TMPL->tagdata = $this->EE->TMPL->parse_variables_row($this->EE->TMPL->tagdata, $vars);
		$this->EE->TMPL->tagdata = $this->EE->functions->prep_conditionals($this->EE->TMPL->tagdata, $cond);

		$query->free_result();

		return $this->EE->TMPL->tagdata;
	}

	// ********************************************************************************* //

	public function new_like()
	{
		// Variable prefix
		$prefix = $this->EE->TMPL->fetch_param('prefix', 'rating') . ':';

		// Some vars
		$form_data = array();
		$vars = array();
		$cond = array();
		$cond[$prefix.'captcha'] = FALSE;

		// -----------------------------------------
		// Which Type?
		// -----------------------------------------
		$rating_type = $this->detect_rating_type_from_params();

		if ($rating_type['type'] < 1)
		{
			$this->EE->TMPL->log_item('CHANNEL RATINGS: Rating Type could not be determined');
			return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_access', $this->EE->TMPL->tagdata);
		}

		// Easy to remember
		$item_id = $rating_type['item_id'];
		$rating_type = $rating_type['type'];
		$rating_type_name = $this->TYPES_INV[$rating_type];

		if ($item_id < 1)
		{
			$this->EE->TMPL->log_item('CHANNEL RATINGS: Item Id could not be determined');
			return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_access', $this->EE->TMPL->tagdata);
		}

		// What collection?
		$collection_id = $this->default_collection;
		$coll_param = $this->EE->TMPL->fetch_param('collection');
		if ($coll_param != FALSE)
		{
			if (isset($this->collections[$coll_param]) == TRUE) $collection_id = $this->collections[$coll_param];
			else
			{
				$this->EE->TMPL->log_item('CHANNEL RATINGS: Collection "'.$coll_param.'" does not exist!');
				return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_access', $this->EE->TMPL->tagdata);
			}
		}

		// -----------------------------------------
		// Form Data
		// -----------------------------------------
		$form_data['ip'] = sprintf("%u", ip2long($this->EE->input->ip_address()));
		$form_data['allow_guests'] = FALSE;
		$form_data['allow_multiple'] = FALSE;
		$form_data['collection_id'] = $collection_id;
		$form_data['return'] = (isset($this->EE->TMPL->tagparams['return']) == FALSE) ? $this->EE->uri->uri_string().'/' : $this->EE->TMPL->tagparams['return'];
		$form_data['rating_type'] = $rating_type;
		$form_data['item_id'] = $item_id;

		// -----------------------------------------
		// Allow Guests?
		// -----------------------------------------
		if ($this->EE->TMPL->fetch_param('allow_guests') != 'yes' && $this->EE->session->userdata['member_id'] == 0 )
		{
			$this->EE->TMPL->log_item('CHANNEL RATINGS: Guests are not allowed to rate.');
			return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_access', $this->EE->TMPL->tagdata);
		}
		elseif ($this->EE->TMPL->fetch_param('allow_guests') == 'yes')
		{
			$form_data['allow_guests'] = TRUE;
		}

		// -----------------------------------------
		// Allow Multiple?
		// -----------------------------------------
		if ( $this->EE->TMPL->fetch_param('allow_multiple') == 'yes')
		{
			$form_data['allow_multiple'] = TRUE;
		}

		// -----------------------------------------
		// Has this user already liked?
		// -----------------------------------------
		$rlike_id = $this->EE->ratings_model->if_already_liked($rating_type, $item_id, $form_data['collection_id']);
		$cond[$prefix.'voted']	= ($rlike_id > 0) ? TRUE : FALSE;
		$cond[$prefix.'not_voted']	= ($rlike_id == 0) ? TRUE : FALSE;

		// -----------------------------------------
		// Build URLS
		// -----------------------------------------
		$ACT_URL = $this->EE->ratings_helper->get_router_url('url', 'insert_like');
		$delete_url = '';

		$form_data['action'] = 'like';
		$like_url = $ACT_URL . '&amp;data=' . base64_encode(serialize($form_data));

		$form_data['action'] = 'dislike';
		$dislike_url = $ACT_URL . '&amp;data=' . base64_encode(serialize($form_data));

		if ($rlike_id > 0)
		{
			$arr = array('rlike_id' => $rlike_id, 'action' => 'delete');
			$vars[$prefix.'delete_url'] = $ACT_URL . '&amp;data=' . base64_encode(serialize($arr));
		}

		$vars[$prefix.'like_url'] = $like_url;
		$vars[$prefix.'dislike_url'] = $dislike_url;

		// Parse variables
		$this->EE->TMPL->tagdata = $this->EE->TMPL->parse_variables_row($this->EE->TMPL->tagdata, $vars);
		$this->EE->TMPL->tagdata = $this->EE->functions->prep_conditionals($this->EE->TMPL->tagdata, $cond);

		return $this->EE->TMPL->tagdata;
	}

	// ********************************************************************************* //

	public function my_likes()
	{
		// Variable prefix
		$prefix = $this->EE->TMPL->fetch_param('prefix', 'rating') . ':';

		// Limit
		$limit = ($this->EE->ratings_helper->is_natural_number($this->EE->TMPL->fetch_param('limit')) != FALSE) ? $this->EE->TMPL->fetch_param('limit') : 30;

		// -----------------------------------------
		// Start SQL
		// -----------------------------------------
		$this->EE->db->select("rl.*");
		$this->EE->db->from('exp_channel_ratings_likes rl');

		// -----------------------------------------
		// Rating Author ID?
		// -----------------------------------------
		$like_author_id = $this->EE->TMPL->fetch_param('like_author_id');
		if ($like_author_id == 'CURRENT_USER')
		{
			$this->EE->db->where('rl.like_author_id', $this->EE->session->userdata['member_id']);
		}
		elseif ($like_author_id != FALSE)
		{
			// Multiple Authors?
			if (strpos($like_author_id, '|') !== FALSE)
			{
				$cols = explode('|', $like_author_id);
				$this->EE->db->where_in('rl.like_author_id', $cols);
			}
			else
			{
				$this->EE->db->where('rl.like_author_id', $like_author_id);
			}
		}

		// -----------------------------------------
		// Like Type
		// -----------------------------------------
		$like_type = ($this->EE->TMPL->fetch_param('type') != FALSE) ? $this->EE->TMPL->fetch_param('type'): FALSE;

		if ($like_type != FALSE OR isset($this->TYPES[$like_type]) != FALSE)
		{
			$this->EE->db->where('rl.like_type', $this->TYPES[$like_type]);
		}

		// -----------------------------------------
		// Item ID
		// -----------------------------------------
		$like_type = $this->detect_rating_type_from_params();

		if ($like_type['type'] >= 1)
		{
			// Easy to remember
			$item_id = $like_type['item_id'];
			$like_type = $like_type['type'];

			if ($item_id > 0) {
				// Entries are handled different
				if ($this->TYPES_INV[$like_type] == 'entry' OR $this->TYPES_INV[$like_type] == 'comment_review')
				{
					$this->EE->db->where('rl.entry_id', $item_id);
				}
				else  $this->EE->db->where('rl.item_id', $item_id);
			}

			$this->EE->db->where('rl.like_type', $like_type);
		}

		// -----------------------------------------
		// Collections
		// -----------------------------------------
		$collection = ($this->EE->TMPL->fetch_param('collection') != FALSE) ? $this->EE->TMPL->fetch_param('collection'): 'default';

		// Multiple Collections?
		if (strpos($collection, '|') !== FALSE)
		{
			$collection = explode('|', $collection);
			$cols = array();

			foreach ($collection as $col)
			{
				if (isset($this->collections[$col]) == TRUE) $cols[] = $this->collections[$col];
			}

			$this->EE->db->where_in('rl.collection_id', $cols);
		}
		else
		{
			if (isset($this->collections[$collection]) == TRUE) $this->EE->db->where('rl.collection_id', $this->collections[$collection]);
			else $this->EE->db->where('rl.collection_id', $this->default_collection);
		}

		// -----------------------------------------
		// Grab Ratings
		// -----------------------------------------
		$this->EE->db->where('stats_row', 0);
		$this->EE->db->limit($limit);
		$query = $this->EE->db->get();

		// We need stats ofcourse
		if ($query->num_rows() == 0)
		{
			$this->EE->TMPL->log_item('CHANNEL RATINGS: No likes found!');
			return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_likes', $this->EE->TMPL->tagdata);
		}

		$results = $query->result();
		$query->free_result();

		$ACT_URL = $this->EE->ratings_helper->get_router_url('url', 'insert_like');

		$final = '';
		$total = count($results);
		foreach ($results as $count => $row)
		{
			$vars = array();
			$vars[$prefix.'count'] = $count;
			$vars[$prefix.'total'] = $total;
			$vars[$prefix.'item_id'] = $row->item_id;
			$vars[$prefix.'entry_id'] = $row->entry_id;
			$vars[$prefix.'channel_id'] = $row->channel_id;
			$vars[$prefix.'liked'] = $row->liked;
			$vars[$prefix.'disliked'] = $row->disliked;
			$vars[$prefix.'date'] = $row->like_date;
			$vars[$prefix.'type'] = $this->TYPES_INV[$row->like_type];

			$arr = array('rlike_id' => $row->rlike_id, 'action' => 'delete');
			$vars[$prefix.'delete_url'] = $ACT_URL . '&amp;data=' . base64_encode(serialize($arr));

			$final .= $this->EE->TMPL->parse_variables_row($this->EE->TMPL->tagdata, $vars);
		}

		return $final;
	}

	// ********************************************************************************* //

	public function top_items()
	{
		// Variable prefix
		$prefix = $this->EE->TMPL->fetch_param('prefix', 'rating') . ':';

		// -----------------------------------------
		// What Collection
		// -----------------------------------------
		$collection_id = $this->default_collection;
		$coll_param = $this->EE->TMPL->fetch_param('collection');
		if ($coll_param != FALSE)
		{
			if (isset($this->collections[$coll_param]) == TRUE) $collection_id = $this->collections[$coll_param];
			else
			{
				$this->EE->TMPL->log_item('CHANNEL RATINGS: Collection "'.$coll_param.'" does not exist!');
				return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_access', $this->EE->TMPL->tagdata);
			}
		}

		$channel =  ($this->EE->TMPL->fetch_param('channel') != FALSE) ? $this->EE->TMPL->fetch_param('channel'): FALSE;
		$channel_id =  ($this->EE->TMPL->fetch_param('channel_id') != FALSE) ? $this->EE->TMPL->fetch_param('channel_id'): FALSE;
		$limit = ($this->EE->ratings_helper->is_natural_number($this->EE->TMPL->fetch_param('limit')) != FALSE) ? $this->EE->TMPL->fetch_param('limit') : 30;
		$backspace = ($this->EE->ratings_helper->is_natural_number($this->EE->TMPL->fetch_param('backspace')) === TRUE) ? $this->EE->TMPL->fetch_param('backspace') : 0;
		$bayesian = ($this->EE->TMPL->fetch_param('bayesian') == 'yes') ? 'rating_bayesian ASC,' : '';

		// Some Defaults
		$precision		= ( $this->EE->TMPL->fetch_param('precision') !== FALSE ) ? $this->EE->TMPL->fetch_param('precision'): 2;
		$g_precision	= ( $precision > 0 ) ? 2: 0;
		$thousands		= ( $this->EE->TMPL->fetch_param('thousands') ) ? $this->EE->TMPL->fetch_param('thousands'): ',';
		$fractions		= ($this->EE->TMPL->fetch_param('fractions') ) ? $this->EE->TMPL->fetch_param('fractions'): '.';
		$scale			= ( $this->EE->TMPL->fetch_param('scale') ) ? $this->EE->TMPL->fetch_param('scale'): 5;
		$theme			= ( $this->EE->TMPL->fetch_param('theme') ) ? $this->EE->TMPL->fetch_param('theme'): 'default';
		$theme_url		= $this->EE->ratings_helper->define_theme_url() . "themes/{$theme}/";


		/** ----------------------------------------
		/** Rating Type
		/** ----------------------------------------*/
		$rating_type = ($this->EE->TMPL->fetch_param('type') != FALSE) ? $this->EE->TMPL->fetch_param('type'): FALSE;

		if ($rating_type == FALSE OR isset($this->TYPES[$rating_type]) == FALSE)
		{
			$this->EE->TMPL->log_item('CHANNEL RATINGS: Rating Type Error');
			return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_items', $this->EE->TMPL->tagdata);
		}

		$rating_type = $this->TYPES[$rating_type];

		/** ----------------------------------------
		/** Grab Channel
		/** ----------------------------------------*/
		if ($channel != FALSE)
		{
			$query = $this->EE->db->select('channel_id')->from('exp_channels')->where('channel_name', $channel)->get();
			if ($query->num_rows() == 0) $channel_id = 0;
			else $channel_id = $query->row('channel_id');
		}

		/** ----------------------------------------
		/** Grab All Items
		/** ----------------------------------------*/
		$this->EE->db->select('entry_id, item_id, rating_avg, rating_last_date, rating_sum, rating_total, rating_bayesian');
		$this->EE->db->from('exp_channel_ratings_stats');
		$this->EE->db->where('collection_id', $collection_id);
		$this->EE->db->where('rating_type', $rating_type);
		if ($channel_id != FALSE) $this->EE->db->where('channel_id', $channel_id);
		$this->EE->db->where('field_id', 0);
		$this->EE->db->order_by("{$bayesian} rating_avg DESC, rating_total DESC, rating_last_date DESC");
		$this->EE->db->limit($limit);
		$query = $this->EE->db->get();

		// Did we find anything
		if ($query->num_rows() == 0)
		{
			$this->EE->TMPL->log_item('CHANNEL RATINGS: No Items Found!');
			return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_items', $this->EE->TMPL->tagdata);
		}

		/** ----------------------------------------
		/** Loop Through Results
		/** ----------------------------------------*/
		$out = '';
		$count = 0;
		$total = $query->num_rows();

		foreach ($query->result() as $row)
		{
			$count++;

			$vars = array(	$prefix.'item_id'		=> $row->item_id,
							$prefix.'entry_id'		=> $row->entry_id,
							$prefix.'count'			=> $count,
							$prefix.'total_items'	=> $total,
						);

			// Rating Vars
			$vars[$prefix.'overall:avg'] = number_format($row->rating_avg, $precision, $fractions, $thousands);
			$vars[$prefix.'overall:total'] = $row->rating_total;
			$vars[$prefix.'overall:sum'] = $row->rating_sum;
			$vars[$prefix.'overall:bayesian'] = $row->rating_bayesian;
			$vars[$prefix.'overall:latest_date'] = $row->rating_last_date;

			// Parse Images
			$vars[$prefix.'overall:stars'] = $this->parse_star_images(number_format($row->rating_avg, $precision, '.', ''), $precision, $scale, $theme_url);


			$out .= $this->EE->TMPL->parse_variables_row($this->EE->TMPL->tagdata, $vars);
		}

		// Apply Backspace
		$out = ($backspace > 0) ? substr($out, 0, - $backspace): $out;

		return $out;
	}

	// ********************************************************************************* //

	public function top_entries()
	{
		// Variable prefix
		$prefix = $this->EE->TMPL->fetch_param('prefix', 'rating') . ':';

		// -----------------------------------------
		// What Collection
		// -----------------------------------------
		$collection_id = $this->default_collection;
		$coll_param = $this->EE->TMPL->fetch_param('collection');
		if ($coll_param != FALSE)
		{
			if (isset($this->collections[$coll_param]) == TRUE) $collection_id = $this->collections[$coll_param];
			else
			{
				$this->EE->TMPL->log_item('CHANNEL RATINGS: Collection "'.$coll_param.'" does not exist!');
				return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_access', $this->EE->TMPL->tagdata);
			}
		}

		/** ----------------------------------------
		/** Parameters
		/** ----------------------------------------*/
		$channel =  ($this->EE->TMPL->fetch_param('channel') != FALSE) ? $this->EE->TMPL->fetch_param('channel'): FALSE;
		$channel_id =  ($this->EE->TMPL->fetch_param('channel_id') != FALSE) ? $this->EE->TMPL->fetch_param('channel_id'): FALSE;
		$limit = ($this->EE->ratings_helper->is_natural_number($this->EE->TMPL->fetch_param('limit')) != FALSE) ? $this->EE->TMPL->fetch_param('limit') : 30;
		$offset = ($this->EE->ratings_helper->is_natural_number($this->EE->TMPL->fetch_param('offset')) != FALSE) ? $this->EE->TMPL->fetch_param('offset') : 0;
		$backspace = ($this->EE->ratings_helper->is_natural_number($this->EE->TMPL->fetch_param('backspace')) === TRUE) ? $this->EE->TMPL->fetch_param('backspace') : 0;
		$bayesian = ($this->EE->TMPL->fetch_param('bayesian') == 'yes') ? 'cs.rating_bayesian DESC,' : '';
		$orderby = ($this->EE->TMPL->fetch_param('orderby') != FALSE) ? $this->EE->TMPL->fetch_param('orderby'): FALSE;

		/** ----------------------------------------
		/** More Parameters
		/** ----------------------------------------*/
		$precision		= ( $this->EE->TMPL->fetch_param('precision') !== FALSE ) ? $this->EE->TMPL->fetch_param('precision'): 2;
		$g_precision	= ( $precision > 0 ) ? 2: 0;
		$thousands		= ( $this->EE->TMPL->fetch_param('thousands') ) ? $this->EE->TMPL->fetch_param('thousands'): ',';
		$fractions		= ($this->EE->TMPL->fetch_param('fractions') ) ? $this->EE->TMPL->fetch_param('fractions'): '.';
		$scale			= ( $this->EE->TMPL->fetch_param('scale') ) ? $this->EE->TMPL->fetch_param('scale'): 5;
		$theme			= ( $this->EE->TMPL->fetch_param('theme') ) ? $this->EE->TMPL->fetch_param('theme'): 'default';
		$theme_url		= $this->EE->ratings_helper->define_theme_url() . "themes/{$theme}/";

		//----------------------------------------
		// Pagination Enabled?
		//----------------------------------------
		if (preg_match('/'.LD."{$prefix}paginate(.*?)".RD."(.+?)".LD.'\/'."{$prefix}paginate".RD."/s", $this->EE->TMPL->tagdata, $match))
		{
			$paginate = TRUE;
		}
		else
		{
			$paginate = FALSE;
		}

		/** ----------------------------------------
		/** Get All channels
		/** ----------------------------------------*/
		$channels = array();
		$channels_lite = array();
		$query = $this->EE->db->select('*')->from('exp_channels')->where('site_id', $this->site_id)->get();

		foreach ($query->result() as $row)
		{
			$channels[$row->channel_id] = $row;
			$channels_lite[$row->channel_id] = $row->channel_name;
		}

		// Do we need to grab a specific channel?
		if ($channel != FALSE)
		{
			// Lets find the id
			if (array_search($channel, $channels_lite) != FALSE)
			{
				$channel_id = array_search($channel, $channels_lite);
			}
		}

		$query->free_result();

		/** ----------------------------------------
		/** Custom Fields?
		/** ----------------------------------------*/
		if ($this->EE->TMPL->fetch_param('custom_fields') != FALSE)
		{
			$fields	= explode( '|', $this->EE->TMPL->fetch_param('custom_fields') );
			$query = $this->EE->db->select('field_id, field_name')->from('exp_channel_fields')->where_in('field_name', $fields)->get();

			$fields	= array();

			foreach ($query->result() as $row)
			{
				$fields[ $row->field_id ] = $row->field_name;
			}
		}

		/** ----------------------------------------
		/** Start SQl
		/** ----------------------------------------*/
		$this->EE->db->select('cs.entry_id, cs.rating_avg, cs.rating_last_date, cs.rating_sum, cs.rating_total, cs.rating_bayesian, ct.title, ct.url_title, ct.entry_date, ct.channel_id');
		$this->EE->db->from('exp_channel_ratings_stats cs');
		$this->EE->db->join('exp_channel_titles ct', 'ct.entry_id = cs.entry_id', 'left');

		/** ----------------------------------------
		/** Any Fields?
		/** ----------------------------------------*/
		if ( isset($fields) == TRUE AND is_array($fields) == TRUE )
		{
			$this->EE->db->join('exp_channel_data cd', 'cd.entry_id = ct.entry_id', 'left');

			foreach ($fields as $key => $val)
			{
				$this->EE->db->select("cd.field_id_{$key}");
			}
		}

		// -----------------------------------------
		// Category ID?
		// -----------------------------------------
		if ($this->EE->TMPL->fetch_param('category') != FALSE)
		{
			$this->EE->db->join('exp_category_posts cp', 'cp.entry_id = cs.entry_id', 'left');
			$category = $this->EE->TMPL->fetch_param('category');

			// Multiple Collections?
			if (strpos($category, '|') !== FALSE)
			{
				$category = explode('|', $category);
				$this->EE->db->where_in('cp.cat_id', $category);
			}
			else
			{
				$this->EE->db->where('cp.cat_id', $category);
			}
		}

		/** ----------------------------------------
		/** Which Entry Status?
		/** ----------------------------------------*/
		if ($this->EE->TMPL->fetch_param('status') != FALSE)
		{
			$status = explode('|', $this->EE->TMPL->fetch_param('status'));
			$this->EE->db->where_in('ct.status', $status);
		}
		else
		{
			$this->EE->db->where('ct.status', 'open');
		}

		// Collection?
		$this->EE->db->where('cs.collection_id', $collection_id);

		/** ----------------------------------------
		/** Rating Type
		/** ----------------------------------------*/
		if ($this->EE->TMPL->fetch_param('include_review') != 'yes')
		{
			$this->EE->db->where('cs.rating_type', $this->TYPES['entry']);
		}
		else
		{
			$this->EE->db->where('cs.rating_type', 0);
		}

		// Channel?
		if ($channel_id != FALSE) $this->EE->db->where('cs.channel_id', $channel_id);

		// FIeld? for now 0
		$this->EE->db->where('cs.field_id', 0);

		/** ----------------------------------------
		/** Order By & Limit
		/** ----------------------------------------*/
		switch ($orderby) {
			case 'avg':
				$this->EE->db->order_by("{$bayesian} cs.rating_avg DESC, cs.rating_total DESC, cs.rating_last_date DESC");
				break;
			case 'total':
				$this->EE->db->order_by("{$bayesian} cs.rating_total DESC, cs.rating_last_date DESC");
				break;
			case 'sum':
				$this->EE->db->order_by("{$bayesian} cs.rating_sum DESC, cs.rating_total DESC, cs.rating_last_date DESC");
				break;
			case 'date':
				$this->EE->db->order_by("{$bayesian} cs.rating_last_date DESC");
				break;
			default:
				$this->EE->db->order_by("{$bayesian} cs.rating_avg DESC, cs.rating_total DESC, cs.rating_last_date DESC");
				break;
		}


		//----------------------------------------
		// Pagination
		//----------------------------------------
		if ($paginate == TRUE)
		{
			// Pagination variables
			$paginate_data	= $match['2'];
			$current_page	= 0;
			$total_pages	= 1;
			$qstring		= $this->EE->uri->query_string;
			$uristr			= $this->EE->uri->uri_string;
			$pagination_links = '';
			$page_previous = '';
			$page_next = '';

			// Get total Count!
			$sql = $this->EE->db->query($this->EE->db->_compile_select('SELECT COUNT(*) as total_count'));
			$total = $sql->row('total_count');
			$sql->free_result(); unset($sql);


			// We need to strip the page number from the URL for two reasons:
			// 1. So we can create pagination links
			// 2. So it won't confuse the query with an improper proper ID

			if (preg_match("#(^|/)CR(\d+)(/|$)#", $qstring, $match))
			{
				$current_page = $match['2'];
				$uristr  = reduce_double_slashes(str_replace($match['0'], '/', $uristr));
				$qstring = trim(reduce_double_slashes(str_replace($match['0'], '/', $qstring)), '/');
			}

			// Remove the {paginate}
			$this->EE->TMPL->tagdata = preg_replace("/".LD."{$prefix}paginate.*?".RD.".+?".LD.'\/'."{$prefix}paginate".RD."/s", "", $this->EE->TMPL->tagdata);

			// What is the current page?
			$current_page = ($current_page == '' OR ($limit > 1 AND $current_page == 1)) ? 0 : $current_page;

			if ($current_page > $total)
			{
				$current_page = 0;
			}

			$t_current_page = floor(($current_page / $limit) + 1);
			$total_pages	= intval(floor($total / $limit));

			if ($total % $limit) $total_pages++;

			if ($total > $limit)
			{
				$this->EE->load->library('pagination');

				$deft_tmpl = '';

				if ($uristr == '')
				{
					if ($this->EE->config->item('template_group') == '')
					{
						$query = $this->EE->db->query("SELECT group_name FROM template_groups WHERE is_site_default = 'y' ");
						$deft_tmpl = $query->row('group_name') .'/index';
					}
					else
					{
						$deft_tmpl  = $this->EE->config->item('template_group').'/';
						$deft_tmpl .= ($this->EE->config->item('template') == '') ? 'index' : $this->EE->config->item('template');
					}
				}

				$basepath = reduce_double_slashes($this->EE->functions->create_url($uristr, FALSE).'/'.$deft_tmpl);

				if ($this->EE->TMPL->fetch_param('paginate_base'))
				{
					// Load the string helper
					$this->EE->load->helper('string');

					$pbase = trim_slashes($this->EE->TMPL->fetch_param('paginate_base'));

					$pbase = str_replace("/index", "/", $pbase);

					if ( ! strstr($basepath, $pbase))
					{
						$basepath = reduce_double_slashes($basepath.'/'.$pbase);
					}
				}

				// Load Language
				//$this->EE->lang->loadfile('tagger');

				$config['first_url'] 	= rtrim($basepath, '/');
				$config['base_url']		= $basepath;
				$config['prefix']		= 'CR';
				$config['total_rows'] 	= $total;
				$config['per_page']		= $limit;
				$config['cur_page']		= $current_page;
				$config['suffix']		= '';
				$config['first_link'] 	= '&lsaquo; First';
				$config['last_link'] 	= 'Last &rsaquo;';
				$config['full_tag_open']		= '<span class="tg_paginate_links">';
				$config['full_tag_close']		= '</span>';
				$config['first_tag_open']		= '<span class="tg_paginate_first">';
				$config['first_tag_close']		= '</span>&nbsp;';
				$config['last_tag_open']		= '&nbsp;<span class="tg_paginate_last">';
				$config['last_tag_close']		= '</span>';
				$config['cur_tag_open']			= '&nbsp;<strong class="tg_paginate_current">';
				$config['cur_tag_close']		= '</strong>';
				$config['next_tag_open']		= '&nbsp;<span class="tg_paginate_next">';
				$config['next_tag_close']		= '</span>';
				$config['prev_tag_open']		= '&nbsp;<span class="tg_paginate_prev">';
				$config['prev_tag_close']		= '</span>';
				$config['num_tag_open']			= '&nbsp;<span class="tg_paginate_num">';
				$config['num_tag_close']		= '</span>';

				// Allows $config['cur_page'] to override
				$config['uri_segment'] = 0;

				$this->EE->pagination->initialize($config);
				$pagination_links = $this->EE->pagination->create_links();

				if ((($total_pages * $limit) - $limit) > $current_page)
				{
					$page_next = $basepath.$config['prefix'].($current_page + $limit).'/';
				}

				if (($current_page - $limit ) >= 0)
				{
					$page_previous = $basepath.$config['prefix'].($current_page - $limit).'/';
				}
			}
			else
			{
				$current_page = 0;
			}

			//$entries = array_slice($entries, $current_page, $limit);
		}

		//----------------------------------------
		// Limit
		//----------------------------------------
		if ($paginate == TRUE)
		{
			$this->EE->db->limit($limit, $current_page);
		}
		else
		{
			$this->EE->db->limit($limit, $offset);
		}

		/** ----------------------------------------
		/** Grab!
		/** ----------------------------------------*/
		$query = $this->EE->db->get();

		/** ----------------------------------------
		/** Did We Find Anything?
		/** ----------------------------------------*/
		if ($query->num_rows() == 0)
		{
			$this->EE->TMPL->log_item('CHANNEL RATINGS: No channel entries found');
			return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_entries', $this->EE->TMPL->tagdata);
		}

		// Loop through the results
		$out = '';
		$count = 0;
		$total = $query->num_rows();
		$entries = $query->result();
		$query->free_result();


		/** ----------------------------------------
		/** Loop Through all entries
		/** ----------------------------------------*/
		foreach ($entries as $row)
		{
			$count++;
			$vars = array(	$prefix.'channel_id'	=> $row->channel_id,
							$prefix.'entry_id'		=> $row->entry_id,
							$prefix.'entry_title'	=> $row->title,
							$prefix.'entry_url_title' => $row->url_title,
							$prefix.'entry_date'	=> $row->entry_date,
							$prefix.'count'			=> $count,
							$prefix.'total_entries'	=> $total,
						);


			// Channel Specific Data
			$vars[$prefix.'channel_name'] = $channels[$row->channel_id]->channel_name;
			$vars[$prefix.'channel_title'] = $channels[$row->channel_id]->channel_title;
			$vars[$prefix.'channel_url'] = $channels[$row->channel_id]->channel_url;
			$vars[$prefix.'channel_search_result_url'] = $channels[$row->channel_id]->search_results_url;
			$vars[$prefix.'channel_comment_url'] = $channels[$row->channel_id]->comment_url;

			// Rating Vars
			$vars[$prefix.'overall:avg'] = number_format($row->rating_avg, $precision, $fractions, $thousands);
			$vars[$prefix.'overall:total'] = $row->rating_total;
			$vars[$prefix.'overall:sum'] = $row->rating_sum;
			$vars[$prefix.'overall:bayesian'] = $row->rating_bayesian;
			$vars[$prefix.'overall:latest_date'] = $row->rating_last_date;

			// Parse Images
			$vars[$prefix.'overall:stars'] = $this->parse_star_images(number_format($row->rating_avg, $precision, '.', ''), $precision, $scale, $theme_url);


			// Any Custom Field?
			if ( isset($fields) == TRUE AND is_array($fields) == TRUE )
			{
				foreach ($fields as $field_id => $field_name)
				{
					$field_id = 'field_id_'.$field_id;
					$field_data = $row->$field_id;
					$field_data = $this->_parse_file_variables($field_data);

					$vars[$prefix.$field_name]  = $field_data;
				}
			}

			$out .= $this->EE->TMPL->parse_variables_row($this->EE->TMPL->tagdata, $vars);
		}

		//----------------------------------------
		// Add pagination to result
		//----------------------------------------
		if ($paginate == TRUE)
		{
			$paginate_data = str_replace(LD.$prefix.'current_page'.RD, 	$t_current_page, 	$paginate_data);
			$paginate_data = str_replace(LD.$prefix.'total_pages'.RD,		$total_pages,  		$paginate_data);
			$paginate_data = str_replace(LD.$prefix.'pagination_links'.RD,	$pagination_links,	$paginate_data);

			if (preg_match("/".LD."if {$prefix}previous_page".RD."(.+?)".LD.'\/'."if".RD."/s", $paginate_data, $match))
			{
				if ($page_previous == '')
				{
					 $paginate_data = preg_replace("/".LD."if {$prefix}previous_page".RD.".+?".LD.'\/'."if".RD."/s", '', $paginate_data);
				}
				else
				{
					$match['1'] = str_replace(array(LD."{$prefix}path".RD, LD."{$prefix}auto_path".RD), $page_previous, $match['1']);

					$paginate_data = str_replace($match['0'], $match['1'], $paginate_data);
				}
			}

			if (preg_match("/".LD."if {$prefix}next_page".RD."(.+?)".LD.'\/'."if".RD."/s", $paginate_data, $match))
			{
				if ($page_next == '')
				{
					 $paginate_data = preg_replace("/".LD."if {$prefix}next_page".RD.".+?".LD.'\/'."if".RD."/s", '', $paginate_data);
				}
				else
				{
					$match['1'] = str_replace(array(LD."{$prefix}path".RD, LD."{$prefix}auto_path".RD), $page_next, $match['1']);

					$paginate_data = str_replace($match['0'], $match['1'], $paginate_data);
				}
			}

			$position = ( ! $this->EE->TMPL->fetch_param('paginate')) ? '' : $this->EE->TMPL->fetch_param('paginate');

			switch ($position)
			{
				case "top"	: $out  = $paginate_data.$out;
					break;
				case "both"	: $out  = $paginate_data.$out.$paginate_data;
					break;
				default		: $out .= $paginate_data;
					break;
			}
		}

		// Apply Backspace
		$out = ($backspace > 0) ? substr($out, 0, - $backspace): $out;

		// Resources are not free
		unset($entries);

		return $out;
	}

	// ********************************************************************************* //

	public function top_liked_entries()
	{
		// Variable prefix
		$prefix = $this->EE->TMPL->fetch_param('prefix', 'rating') . ':';

		// -----------------------------------------
		// What Collection
		// -----------------------------------------
		$collection_id = $this->default_collection;
		$coll_param = $this->EE->TMPL->fetch_param('collection');
		if ($coll_param != FALSE)
		{
			if (isset($this->collections[$coll_param]) == TRUE) $collection_id = $this->collections[$coll_param];
			else
			{
				$this->EE->TMPL->log_item('CHANNEL RATINGS: Collection "'.$coll_param.'" does not exist!');
				return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_access', $this->EE->TMPL->tagdata);
			}
		}

		/** ----------------------------------------
		/** Parameters
		/** ----------------------------------------*/
		$channel =  ($this->EE->TMPL->fetch_param('channel') != FALSE) ? $this->EE->TMPL->fetch_param('channel'): FALSE;
		$channel_id =  ($this->EE->TMPL->fetch_param('channel_id') != FALSE) ? $this->EE->TMPL->fetch_param('channel_id'): FALSE;
		$limit = ($this->EE->ratings_helper->is_natural_number($this->EE->TMPL->fetch_param('limit')) != FALSE) ? $this->EE->TMPL->fetch_param('limit') : 30;
		$offset = ($this->EE->ratings_helper->is_natural_number($this->EE->TMPL->fetch_param('offset')) != FALSE) ? $this->EE->TMPL->fetch_param('offset') : 0;
		$backspace = ($this->EE->ratings_helper->is_natural_number($this->EE->TMPL->fetch_param('backspace')) === TRUE) ? $this->EE->TMPL->fetch_param('backspace') : 0;

		/** ----------------------------------------
		/** More Parameters
		/** ----------------------------------------*/
		$precision		= ( $this->EE->TMPL->fetch_param('precision') !== FALSE ) ? $this->EE->TMPL->fetch_param('precision'): 2;
		$g_precision	= ( $precision > 0 ) ? 2: 0;
		$thousands		= ( $this->EE->TMPL->fetch_param('thousands') ) ? $this->EE->TMPL->fetch_param('thousands'): ',';
		$fractions		= ($this->EE->TMPL->fetch_param('fractions') ) ? $this->EE->TMPL->fetch_param('fractions'): '.';

		//----------------------------------------
		// Pagination Enabled?
		//----------------------------------------
		if (preg_match('/'.LD."{$prefix}paginate(.*?)".RD."(.+?)".LD.'\/'."{$prefix}paginate".RD."/s", $this->EE->TMPL->tagdata, $match))
		{
			$paginate = TRUE;
		}
		else
		{
			$paginate = FALSE;
		}

		/** ----------------------------------------
		/** Get All channels
		/** ----------------------------------------*/
		$channels = array();
		$channels_lite = array();
		$query = $this->EE->db->select('*')->from('exp_channels')->where('site_id', $this->site_id)->get();

		foreach ($query->result() as $row)
		{
			$channels[$row->channel_id] = $row;
			$channels_lite[$row->channel_id] = $row->channel_name;
		}

		// Do we need to grab a specific channel?
		if ($channel != FALSE)
		{
			// Lets find the id
			if (array_search($channel, $channels_lite) != FALSE)
			{
				$channel_id = array_search($channel, $channels_lite);
			}
		}

		$query->free_result();

		/** ----------------------------------------
		/** Custom Fields?
		/** ----------------------------------------*/
		if ($this->EE->TMPL->fetch_param('custom_fields') != FALSE)
		{
			$fields	= explode( '|', $this->EE->TMPL->fetch_param('custom_fields') );
			$query = $this->EE->db->select('field_id, field_name')->from('exp_channel_fields')->where_in('field_name', $fields)->get();

			$fields	= array();

			foreach ($query->result() as $row)
			{
				$fields[ $row->field_id ] = $row->field_name;
			}
		}

		/** ----------------------------------------
		/** Start SQl
		/** ----------------------------------------*/
		$this->EE->db->select('cl.entry_id, cl.liked, cl.disliked, cl.like_date, ct.title, ct.url_title, ct.entry_date, ct.channel_id');
		$this->EE->db->from('exp_channel_ratings_likes cl');
		$this->EE->db->join('exp_channel_titles ct', 'ct.entry_id = cl.entry_id', 'left');

		/** ----------------------------------------
		/** Any Fields?
		/** ----------------------------------------*/
		if ( isset($fields) == TRUE AND is_array($fields) == TRUE )
		{
			$this->EE->db->join('exp_channel_data cd', 'cd.entry_id = ct.entry_id', 'left');

			foreach ($fields as $key => $val)
			{
				$this->EE->db->select("cd.field_id_{$key}");
			}
		}

		// -----------------------------------------
		// Category ID?
		// -----------------------------------------
		if ($this->EE->TMPL->fetch_param('category') != FALSE)
		{
			$this->EE->db->join('exp_category_posts cp', 'cp.entry_id = cl.entry_id', 'left');
			$category = $this->EE->TMPL->fetch_param('category');

			// Multiple Collections?
			if (strpos($category, '|') !== FALSE)
			{
				$category = explode('|', $category);
				$this->EE->db->where_in('cp.cat_id', $category);
			}
			else
			{
				$this->EE->db->where('cp.cat_id', $category);
			}
		}

		/** ----------------------------------------
		/** Which Entry Status?
		/** ----------------------------------------*/
		if ($this->EE->TMPL->fetch_param('status') != FALSE)
		{
			$status = explode('|', $this->EE->TMPL->fetch_param('status'));
			$this->EE->db->where_in('ct.status', $status);
		}
		else
		{
			$this->EE->db->where('ct.status', 'open');
		}

		// Collection?
		$this->EE->db->where('cl.collection_id', $collection_id);

		/** ----------------------------------------
		/** Rating Type
		/** ----------------------------------------*/
		if ($this->EE->TMPL->fetch_param('include_review') != 'yes')
		{
			$this->EE->db->where('cl.like_type', $this->TYPES['entry']);
		}
		else
		{
			$this->EE->db->where('cl.like_type', 0);
		}

		// Channel?
		if ($channel_id != FALSE) $this->EE->db->where('cl.channel_id', $channel_id);

		// FIeld? for now 0
		$this->EE->db->where('cl.stats_row', 1);

		/** ----------------------------------------
		/** Order By & Limit
		/** ----------------------------------------*/
		$this->EE->db->order_by("liked DESC");

		//----------------------------------------
		// Pagination
		//----------------------------------------
		if ($paginate == TRUE)
		{
			// Pagination variables
			$paginate_data	= $match['2'];
			$current_page	= 0;
			$total_pages	= 1;
			$qstring		= $this->EE->uri->query_string;
			$uristr			= $this->EE->uri->uri_string;
			$pagination_links = '';
			$page_previous = '';
			$page_next = '';

			// Get total Count!
			$sql = $this->EE->db->query($this->EE->db->_compile_select('SELECT COUNT(*) as total_count'));
			$total = $sql->row('total_count');
			$sql->free_result(); unset($sql);


			// We need to strip the page number from the URL for two reasons:
			// 1. So we can create pagination links
			// 2. So it won't confuse the query with an improper proper ID

			if (preg_match("#(^|/)CR(\d+)(/|$)#", $qstring, $match))
			{
				$current_page = $match['2'];
				$uristr  = reduce_double_slashes(str_replace($match['0'], '/', $uristr));
				$qstring = trim(reduce_double_slashes(str_replace($match['0'], '/', $qstring)), '/');
			}

			// Remove the {paginate}
			$this->EE->TMPL->tagdata = preg_replace("/".LD."{$prefix}paginate.*?".RD.".+?".LD.'\/'."{$prefix}paginate".RD."/s", "", $this->EE->TMPL->tagdata);

			// What is the current page?
			$current_page = ($current_page == '' OR ($limit > 1 AND $current_page == 1)) ? 0 : $current_page;

			if ($current_page > $total)
			{
				$current_page = 0;
			}

			$t_current_page = floor(($current_page / $limit) + 1);
			$total_pages	= intval(floor($total / $limit));

			if ($total % $limit) $total_pages++;

			if ($total > $limit)
			{
				$this->EE->load->library('pagination');

				$deft_tmpl = '';

				if ($uristr == '')
				{
					if ($this->EE->config->item('template_group') == '')
					{
						$query = $this->EE->db->query("SELECT group_name FROM template_groups WHERE is_site_default = 'y' ");
						$deft_tmpl = $query->row('group_name') .'/index';
					}
					else
					{
						$deft_tmpl  = $this->EE->config->item('template_group').'/';
						$deft_tmpl .= ($this->EE->config->item('template') == '') ? 'index' : $this->EE->config->item('template');
					}
				}

				$basepath = reduce_double_slashes($this->EE->functions->create_url($uristr, FALSE).'/'.$deft_tmpl);

				if ($this->EE->TMPL->fetch_param('paginate_base'))
				{
					// Load the string helper
					$this->EE->load->helper('string');

					$pbase = trim_slashes($this->EE->TMPL->fetch_param('paginate_base'));

					$pbase = str_replace("/index", "/", $pbase);

					if ( ! strstr($basepath, $pbase))
					{
						$basepath = reduce_double_slashes($basepath.'/'.$pbase);
					}
				}

				// Load Language
				//$this->EE->lang->loadfile('tagger');

				$config['first_url'] 	= rtrim($basepath, '/');
				$config['base_url']		= $basepath;
				$config['prefix']		= 'CR';
				$config['total_rows'] 	= $total;
				$config['per_page']		= $limit;
				$config['cur_page']		= $current_page;
				$config['suffix']		= '';
				$config['first_link'] 	= '&lsaquo; First';
				$config['last_link'] 	= 'Last &rsaquo;';
				$config['full_tag_open']		= '<span class="tg_paginate_links">';
				$config['full_tag_close']		= '</span>';
				$config['first_tag_open']		= '<span class="tg_paginate_first">';
				$config['first_tag_close']		= '</span>&nbsp;';
				$config['last_tag_open']		= '&nbsp;<span class="tg_paginate_last">';
				$config['last_tag_close']		= '</span>';
				$config['cur_tag_open']			= '&nbsp;<strong class="tg_paginate_current">';
				$config['cur_tag_close']		= '</strong>';
				$config['next_tag_open']		= '&nbsp;<span class="tg_paginate_next">';
				$config['next_tag_close']		= '</span>';
				$config['prev_tag_open']		= '&nbsp;<span class="tg_paginate_prev">';
				$config['prev_tag_close']		= '</span>';
				$config['num_tag_open']			= '&nbsp;<span class="tg_paginate_num">';
				$config['num_tag_close']		= '</span>';

				// Allows $config['cur_page'] to override
				$config['uri_segment'] = 0;

				$this->EE->pagination->initialize($config);
				$pagination_links = $this->EE->pagination->create_links();

				if ((($total_pages * $limit) - $limit) > $current_page)
				{
					$page_next = $basepath.$config['prefix'].($current_page + $limit).'/';
				}

				if (($current_page - $limit ) >= 0)
				{
					$page_previous = $basepath.$config['prefix'].($current_page - $limit).'/';
				}
			}
			else
			{
				$current_page = 0;
			}

			//$entries = array_slice($entries, $current_page, $limit);
		}

		//----------------------------------------
		// Limit
		//----------------------------------------
		if ($paginate == TRUE)
		{
			$this->EE->db->limit($limit, $current_page);
		}
		else
		{
			$this->EE->db->limit($limit, $offset);
		}

		/** ----------------------------------------
		/** Grab!
		/** ----------------------------------------*/
		$query = $this->EE->db->get();

		/** ----------------------------------------
		/** Did We Find Anything?
		/** ----------------------------------------*/
		if ($query->num_rows() == 0)
		{
			$this->EE->TMPL->log_item('CHANNEL RATINGS: No channel entries found');
			return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_entries', $this->EE->TMPL->tagdata);
		}

		// Loop through the results
		$out = '';
		$count = 0;
		$total = $query->num_rows();
		$entries = $query->result();
		$query->free_result();


		/** ----------------------------------------
		/** Loop Through all entries
		/** ----------------------------------------*/
		foreach ($entries as $row)
		{
			$count++;
			$vars = array(	$prefix.'channel_id'	=> $row->channel_id,
							$prefix.'entry_id'		=> $row->entry_id,
							$prefix.'entry_title'	=> $row->title,
							$prefix.'entry_url_title' => $row->url_title,
							$prefix.'entry_date'	=> $row->entry_date,
							$prefix.'count'			=> $count,
							$prefix.'total_entries'	=> $total,
						);


			// Channel Specific Data
			$vars[$prefix.'channel_name'] = $channels[$row->channel_id]->channel_name;
			$vars[$prefix.'channel_title'] = $channels[$row->channel_id]->channel_title;
			$vars[$prefix.'channel_url'] = $channels[$row->channel_id]->channel_url;
			$vars[$prefix.'channel_search_result_url'] = $channels[$row->channel_id]->search_results_url;
			$vars[$prefix.'channel_comment_url'] = $channels[$row->channel_id]->comment_url;

			$vars[$prefix.'likes'] = $row->liked;
			$vars[$prefix.'dislikes'] = $row->disliked;
			$vars[$prefix.'total_votes'] = $row->liked + $row->disliked;

			// Any Custom Field?
			if ( isset($fields) == TRUE AND is_array($fields) == TRUE )
			{
				foreach ($fields as $field_id => $field_name)
				{
					$field_id = 'field_id_'.$field_id;
					$field_data = $row->$field_id;
					$field_data = $this->_parse_file_variables($field_data);

					$vars[$prefix.$field_name]  = $field_data;
				}
			}

			$out .= $this->EE->TMPL->parse_variables_row($this->EE->TMPL->tagdata, $vars);
		}

		//----------------------------------------
		// Add pagination to result
		//----------------------------------------
		if ($paginate == TRUE)
		{
			$paginate_data = str_replace(LD.$prefix.'current_page'.RD, 	$t_current_page, 	$paginate_data);
			$paginate_data = str_replace(LD.$prefix.'total_pages'.RD,		$total_pages,  		$paginate_data);
			$paginate_data = str_replace(LD.$prefix.'pagination_links'.RD,	$pagination_links,	$paginate_data);

			if (preg_match("/".LD."if {$prefix}previous_page".RD."(.+?)".LD.'\/'."if".RD."/s", $paginate_data, $match))
			{
				if ($page_previous == '')
				{
					 $paginate_data = preg_replace("/".LD."if {$prefix}previous_page".RD.".+?".LD.'\/'."if".RD."/s", '', $paginate_data);
				}
				else
				{
					$match['1'] = str_replace(array(LD."{$prefix}path".RD, LD."{$prefix}auto_path".RD), $page_previous, $match['1']);

					$paginate_data = str_replace($match['0'], $match['1'], $paginate_data);
				}
			}

			if (preg_match("/".LD."if {$prefix}next_page".RD."(.+?)".LD.'\/'."if".RD."/s", $paginate_data, $match))
			{
				if ($page_next == '')
				{
					 $paginate_data = preg_replace("/".LD."if {$prefix}next_page".RD.".+?".LD.'\/'."if".RD."/s", '', $paginate_data);
				}
				else
				{
					$match['1'] = str_replace(array(LD."{$prefix}path".RD, LD."{$prefix}auto_path".RD), $page_next, $match['1']);

					$paginate_data = str_replace($match['0'], $match['1'], $paginate_data);
				}
			}

			$position = ( ! $this->EE->TMPL->fetch_param('paginate')) ? '' : $this->EE->TMPL->fetch_param('paginate');

			switch ($position)
			{
				case "top"	: $out  = $paginate_data.$out;
					break;
				case "both"	: $out  = $paginate_data.$out.$paginate_data;
					break;
				default		: $out .= $paginate_data;
					break;
			}
		}

		// Apply Backspace
		$out = ($backspace > 0) ? substr($out, 0, - $backspace): $out;

		// Resources are not free
		unset($entries);

		return $out;
	}

	// ********************************************************************************* //

	private function parse_star_images($rating, $precision = '2', $scale = '5', $image_url = '')
	{
		$OUT = '';

		if ($rating > $scale) {
			$rating = $scale;
		}

		//	Get array
		$number	= explode('.', number_format($rating, $precision, '.', '') );

		$css_bg = ($this->EE->TMPL->fetch_param('css_bg') == 'yes') ? true : false;

		// ----------------------------------------
		//	Handle Decimal (Remainder)
		//  This formats the remainder portion of a decimal number to 25, 20, 75
		// ----------------------------------------
		if ( isset($number['1']) === FALSE ) $number['1'] = 0;
		elseif ( $number['1'] < 25 ) $number['1'] = 0;
		elseif ( $number['1'] >= 25 AND $number['1'] < 50 ) $number['1']	= 25;
		elseif ( $number['1'] >= 50 AND $number['1'] < 75 ) $number['1']	= 50;
		else $number['1']	= 75;

		// ----------------------------------------
		//	Handle Filler
		//  This gives the number of empty stars given a remainder
		// ----------------------------------------
		$filler	= 0;

		if ( is_numeric($scale) == FALSE OR is_array($number) === FALSE ) $filler = 0;

		if ($number['1'] == 0)
		{
			$filler	 = $scale - $number['0'];
		}
		else
		{
			$filler	 = $scale - 1 - $number['0'];
		}

		// ----------------------------------------
		//	Image Data
		// ----------------------------------------

		$extension = '.png';

		$data	= array('filler'	=> $filler,
						'urlfull'	=> $image_url.'rating-100'.$extension,
						'urlrem'	=> $image_url.'rating-'.$number['1'].$extension,
						'urlfill'	=> $image_url.'rating-0'.$extension,
				);

		// ----------------------------------------
		//	Loop over all FULL Stars
		// ----------------------------------------
		for ($i = $number['0']; $i > 0; $i-- )
		{
			if ($css_bg) {
				$OUT .=	"<span class='crstar star-100' title='100'></span>";
			} else {
				$OUT .=	"<img src='{$data['urlfull']}' alt='{$i}' class='star100'/>";
			}
		}

		// ----------------------------------------
		//	Add the remainder
		// ----------------------------------------
		if ($number['1'] != 0) {
			if ($css_bg) {
				$OUT .=	"<span class='crstar star-{$number['1']}' title='{$number['1']}'></span>";
			} else {
				$OUT .=	"<img src='{$data['urlrem']}' alt='{$number['1']}' class='star{$number['1']}'/>";
			}
		}

		//$OUT	.= ( $number['1'] == 0 ) ? '': ;

		// ----------------------------------------
		//	Add the fillers (the empty ones)
		// ----------------------------------------
		for ( $i = $data['filler']; $i > 0; $i-- )
		{
			if ($css_bg) {
				$OUT .=	"<span class='crstar star-0' title='0'></span>";
			} else {
				$OUT .= "<img src='{$data['urlfill']}' alt='{$i}' class='star00'/>";
			}
		}


		/*
		 EXAMPLE CSS:
		 .crstar {
			display: inline-block;
			width: 16px;
			height: 16px;
			background: url(/themes/third_party/channel_ratings/themes/default/sprite.png) no-repeat;
		 }

		.star-100 {background-position: 0px 0px;}
		.star-75 {background-position: 0px -26px;}
		.star-50 {background-position: 0px -52px;}
		.star-25 {background-position: 0px -78px;}
		.star-0 {background-position: 0px -104px}
		*/


		return $OUT;
	}

	// ********************************************************************************* //

	private function detect_rating_type_from_params()
	{
		$type = array('type' => 0, 'item_id' => 0);

		// -----------------------------------------
		// Entry?
		// -----------------------------------------
		if (isset($this->EE->TMPL->tagparams['entry_id']) !== FALSE OR isset($this->EE->TMPL->tagparams['url_title']) !== FALSE)
		{
			if (isset($this->EE->TMPL->tagparams['entry_id']) == TRUE)
			{
				$entry_id = $this->EE->TMPL->tagparams['entry_id'];
			}
			elseif (isset($this->EE->TMPL->tagparams['url_title']) == TRUE)
			{
				$query = $this->EE->db->select('entry_id')->from('exp_channel_titles')->where('url_title', $this->EE->TMPL->tagparams['url_title'])->get();
				if ($query->num_rows() == 0) return $type;

				$entry_id = $query->row('entry_id');
			}

			$type['type'] = $this->TYPES['entry'];
			$type['item_id'] = $entry_id;
		}

		// -----------------------------------------
		// Comment Entry
		// -----------------------------------------
		elseif (isset($this->EE->TMPL->tagparams['comment_id']) !== FALSE)
		{
			$type['type'] = $this->TYPES['comment_entry'];
			$type['item_id'] = $this->EE->TMPL->tagparams['comment_id'];
		}

		// -----------------------------------------
		// Member
		// -----------------------------------------
		elseif (isset($this->EE->TMPL->tagparams['member_id']) !== FALSE OR isset($this->EE->TMPL->tagparams['username']) !== FALSE)
		{
			if (isset($this->EE->TMPL->tagparams['member_id']) == TRUE)
			{
				$member_id = $this->EE->TMPL->tagparams['member_id'];
			}
			elseif (isset($this->EE->TMPL->tagparams['username']) == TRUE)
			{
				$query = $this->EE->db->select('member_id')->from('exp_channel_titles')->where('username', $this->EE->TMPL->tagparams['username'])->get();
				if ($query->num_rows() == 0) return $type;

				$member_id = $query->row('member_id');
			}

			$type['type'] = $this->TYPES['member'];
			$type['item_id'] = $member_id;
		}

		// -----------------------------------------
		// Channel Images
		// -----------------------------------------
		elseif (isset($this->EE->TMPL->tagparams['ci_id']) !== FALSE)
		{
			$type['type'] = $this->TYPES['channel_images'];
			$type['item_id'] = $this->EE->TMPL->tagparams['ci_id'];
		}

		// -----------------------------------------
		// Channel Files
		// -----------------------------------------
		elseif (isset($this->EE->TMPL->tagparams['cf_id']) !== FALSE)
		{
			$type['type'] = $this->TYPES['channel_files'];
			$type['item_id'] = $this->EE->TMPL->tagparams['cf_id'];
		}

		// -----------------------------------------
		// Channel Videos
		// -----------------------------------------
		elseif (isset($this->EE->TMPL->tagparams['cv_id']) !== FALSE)
		{
			$type['type'] = $this->TYPES['channel_videos'];
			$type['item_id'] = $this->EE->TMPL->tagparams['cv_id'];
		}

		// -----------------------------------------
		// BrilliantRetail Products
		// -----------------------------------------
		elseif (isset($this->EE->TMPL->tagparams['br_id']) !== FALSE)
		{
			$type['type'] = $this->TYPES['br_product'];
			$type['item_id'] = $this->EE->TMPL->tagparams['br_id'];
		}

		// We only want numbers
		if ($this->EE->ratings_helper->is_natural_number($type['item_id']) != TRUE)
		{
			$type['item_id'] = 0;
		}

		// -----------------------------------------
		// Type="" param?
		// -----------------------------------------
		if ($this->EE->TMPL->fetch_param('type') != FALSE)
		{
			$types = $this->EE->TMPL->fetch_param('type');

			// Multiple?
			if (strpos($types, '|') !== FALSE)
			{
				$types = explode('|', $types);
				$typesArr = array();

				foreach ($types as $i)
				{
					if (isset($this->TYPES[$i]) != FALSE) $typesArr[] = $this->TYPES[$i];
				}

				if (empty($typesArr) == TRUE) $type['type'] = 0;
				else $type['type'] = implode('|', $typesArr);
			}
			else
			{
				if (isset($this->TYPES[$types]) != FALSE) $type['type'] = $this->TYPES[$types];
				else $type['type'] = 0;
			}
		}

		return $type;
	}

	// ********************************************************************************* //

	function channel_ratings_router()
	{
		// -----------------------------------------
		// Ajax Request?
		// -----------------------------------------
		if ( $this->EE->input->get_post('ajax_method') != FALSE OR (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') )
		{
			// Load Library
			if (class_exists('Channel_Ratings_AJAX') != TRUE) include 'ajax.channel_ratings.php';

			$AJAX = new Channel_Ratings_AJAX();

			// Shoot the requested method
			$method = $this->EE->input->get_post('ajax_method');
			echo $AJAX->$method();
			exit();
		}

		// -----------------------------------------
		// Normal Request
		// -----------------------------------------
		if ($this->EE->input->get_post('MET') != FALSE)
		{
			// Load Library
			if (class_exists('ChannelRatings_ACT') != TRUE) include 'act.channel_ratings.php';

			$AJAX = new ChannelRatings_ACT();


		}

	}

	// ********************************************************************************* //

	public function insert_rating()
	{
		// Load Library
		if (class_exists('ChannelRatings_ACT') != TRUE) include 'act.channel_ratings.php';

		$ACT = new ChannelRatings_ACT();

		$ACT->new_rating();
	}

	// ********************************************************************************* //

	public function insert_like()
	{
		// Load Library
		if (class_exists('ChannelRatings_ACT') != TRUE) include 'act.channel_ratings.php';

		$ACT = new ChannelRatings_ACT();

		$ACT->new_like();
	}

	// ********************************************************************************* //

	public function bayesian()
	{
		$this->EE->load->model('ratings_bayesian');
		$this->EE->ratings_bayesian->start();
	}

	// ********************************************************************************* //

	private function get_collections()
	{
		$colls = $this->EE->ratings_model->get_collections();

		$this->collections = array();
		$this->default_collection = '';

		foreach ($colls as $col)
		{
			if ($col->default == 1) $this->default_collection = $col->collection_id;
			$this->collections[ $col->collection_name ] = $col->collection_id;
		}
	}

	// ********************************************************************************* //

	private function _parse_file_variables($data='')
	{
		if (strpos($data, LD.'filedir_') !== FALSE)
		{
			$vars = $this->_fetch_file_variables();

			foreach ($vars as $variable => $url)
			{
				$data = str_replace($variable, $url, $data);
			}
		}

		return $data;
	}

	// ********************************************************************************* //

	private function _fetch_file_variables($sort=FALSE)
	{
		if (! isset($this->file_variables))
		{
			$this->file_variables = array();
			$file_paths = $this->EE->functions->fetch_file_paths();

			foreach ($file_paths as $id => $url)
			{
				// ignore "/" URLs
				if ($url == '/') continue;

				$this->file_variables[LD.'filedir_'.$id.RD] = $url;
			}
		}

		return $this->file_variables;
	}

	// ********************************************************************************* //

} // END CLASS

/* End of file mod.channel_ratings.php */
/* Location: ./system/expressionengine/third_party/channel_ratings/mod.channel_ratings.php */
