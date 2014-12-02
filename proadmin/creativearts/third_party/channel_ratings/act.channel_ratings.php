<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Channel Ratings ACT File
 *
 * @package			DevDemon_ChannelRatings
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 */
class ChannelRatings_ACT
{

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * Calls the parent constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->site_id = $this->EE->config->item('site_id');
		$this->EE->load->library('ratings_helper');
		$this->EE->load->model('ratings_model');
		$this->EE->lang->loadfile('channel_ratings');

		$this->EE->config->load('ratings');
		$this->TYPES = $this->EE->config->item('cr_rating_types');
		$this->TYPES_INV = array_flip($this->TYPES);

	}

	// ********************************************************************************* //

	public function new_rating()
	{
		$form_data = array();
		$IP = sprintf("%u", ip2long($this->EE->input->ip_address()));
		$ip_address = $this->EE->input->ip_address();
		$FDATA = @unserialize($this->EE->ratings_helper->decode_string($this->EE->input->post('FDATA')));

		// -----------------------------------------
		// Process Form Data
		// -----------------------------------------
		if ($FDATA != FALSE)
		{
			$form_data = $FDATA;

			// Same IP?
			if ($form_data['ip_address'] != $ip_address)
			{
				return $this->return_error('missing_data', $this->EE->lang->line('rating:error:missing_data') . '(DIFFERENT_IP)' );
			}
		}
		else
		{
			// FPID was not a number, quit
			return $this->return_error('missing_data', $this->EE->lang->line('rating:error:missing_data') . '(FORM_DATA_MISSING_OR_CORRUPT)' );
		}

		// -----------------------------------------
		// Check item_id, rating_type, channel_id
		// -----------------------------------------

		// We need Item ID
		if (isset($form_data['item_id']) == FALSE OR $form_data['item_id'] < 1)
		{
			return $this->return_error('missing_data', $this->EE->lang->line('rating:error:missing_data') . '(FORM_DATA: MISSING ITEM ID)' );
		}

		// We need Rating Type
		if (isset($form_data['rating_type']) == FALSE OR $form_data['rating_type'] < 1)
		{
			return $this->return_error('missing_data', $this->EE->lang->line('rating:error:missing_data') . '(FORM_DATA: MISSING RATING TYPE)' );
		}

		// -----------------------------------------
		// Do we need entry_id? (example:member and br_product)
		// -----------------------------------------
		$not_needed = array('member', 'br_product');
		$form_data['entry_id'] = 0;

		if (array_search($form_data['rating_type_name'], $not_needed) == FALSE)
		{
			switch ($form_data['rating_type_name'])
			{
				case 'entry':
					$form_data['entry_id'] = $form_data['item_id'];
					$form_data['item_id'] = 0;
					break;
				case 'comment_entry':
					$entryquery = $this->EE->db->select('entry_id')->from('exp_comments')->where('comment_id', $form_data['item_id'])->get();
					break;
				case 'channel_images':
					$entryquery = $this->EE->db->select('entry_id')->from('exp_channel_images')->where('image_id', $form_data['item_id'])->get();
					break;
				case 'channel_files':
					$entryquery = $this->EE->db->select('entry_id')->from('exp_channel_files')->where('file_id', $form_data['item_id'])->get();
					break;
				case 'channel_videos':
					$entryquery = $this->EE->db->select('entry_id')->from('exp_channel_videos')->where('video_id', $form_data['item_id'])->get();
					break;
			}

			if (isset($entryquery) != FALSE) $form_data['entry_id'] = $entryquery->row('entry_id');
		}

		// -----------------------------------------
		// Do we need channel_id? (example:member and br_product)
		// -----------------------------------------
		$not_needed = array('member', 'br_product');
		$form_data['channel_id'] = 0;

		if (array_search($form_data['rating_type_name'], $not_needed) == FALSE)
		{
			switch ($form_data['rating_type_name'])
			{
				case 'entry':
					$channelquery = $this->EE->db->select('channel_id')->from('exp_channel_titles')->where('entry_id', $form_data['entry_id'])->get();
					break;
				case 'comment_entry':
					$channelquery = $this->EE->db->select('channel_id')->from('exp_comments')->where('comment_id', $form_data['item_id'])->get();
					break;
				case 'channel_images':
					$channelquery = $this->EE->db->select('channel_id')->from('exp_channel_images')->where('image_id', $form_data['item_id'])->get();
					break;
				case 'channel_files':
					$channelquery = $this->EE->db->select('channel_id')->from('exp_channel_files')->where('file_id', $form_data['item_id'])->get();
					break;
				case 'channel_videos':
					$channelquery = $this->EE->db->select('channel_id')->from('exp_channel_videos')->where('video_id', $form_data['item_id'])->get();
					break;
			}

			if (isset($channelquery) != FALSE) $form_data['channel_id'] = $channelquery->row('channel_id');
		}

		// -----------------------------------------
		// Is the User Banned?
		// -----------------------------------------
		if ($this->EE->session->userdata['is_banned'] == TRUE)
		{
			return $this->return_error('not_authorized', $this->EE->lang->line('rating:error:not_authorized') . ' (BANNED)');
		}

		// -----------------------------------------
		// Is the IP address and User Agent required?
		// -----------------------------------------
		if ($this->EE->config->item('require_ip_for_posting') == 'y')
		{
			if ($ip_address == '0.0.0.0' OR $this->EE->session->userdata['user_agent'] == "")
			{
				return $this->return_error('not_authorized', $this->EE->lang->line('rating:error:not_authorized') . ' (NO_IP)');
			}
		}

		// -----------------------------------------
		// Is the nation of the user banend?
		// -----------------------------------------
		if ( $this->EE->session->nation_ban_check(FALSE) === FALSE && $this->EE->config->item('ip2nation') == 'y')
		{
			return $this->return_error('not_authorized', $this->EE->lang->line('rating:error:not_authorized') . ' (NATION)');
		}

		// -----------------------------------------
		// Blacklist/Whitelist Check
		// -----------------------------------------
		if ($this->EE->blacklist->blacklisted == 'y' && $this->EE->blacklist->whitelisted == 'n')
		{
			return $this->return_error('not_authorized', $this->EE->lang->line('rating:error:not_authorized') . ' (BLACKLIST)');
		}

		// -----------------------------------------
		// Captcha
		// -----------------------------------------
		if (isset($form_data['require_captcha']) == TRUE && $form_data['require_captcha'] == TRUE)
		{
			if ( $this->EE->input->post('captcha') == FALSE)
			{
				return $this->return_error('captcha_required', $this->EE->lang->line('rating:error:captcha_required') );
			}
			else
			{
				$this->EE->db->where('word', $this->EE->input->post('captcha'));
				$this->EE->db->where('ip_address', $this->EE->input->ip_address());
				$this->EE->db->where('date > UNIX_TIMESTAMP()-7200', NULL, FALSE);

				$result = $this->EE->db->count_all_results('captcha');

				if ($result == 0)
				{
					return $this->return_error('captcha_incorrect', $this->EE->lang->line('rating:error:captcha_incorrect') );
				}

				// Delete all old!
				$this->EE->db->query("DELETE FROM exp_captcha WHERE (word='".$this->EE->db->escape_str($_POST['captcha'])."' AND ip_address = '".$this->EE->input->ip_address()."') OR date < UNIX_TIMESTAMP()-7200");
			}
		}

		// -----------------------------------------
		// Allow Multiple?
		// -----------------------------------------
		$already_rated = $this->EE->ratings_model->if_already_rated($form_data['rating_type'], $form_data['item_id'], $form_data['collection_id']);

		if (isset($form_data['allow_multiple']) == TRUE && $form_data['allow_multiple'] == FALSE && $already_rated == TRUE)
		{
			return $this->return_error('duplicate_rating', $this->EE->lang->line('rating:error:duplicate_rating') );
		}

		// -----------------------------------------
		// Check Rating
		// -----------------------------------------
		$ratings = $this->EE->input->post('rating');

		// Always needs to be an array
		if (is_array($ratings) == FALSE)
		{
			return $this->return_error('missing_data', $this->EE->lang->line('rating:error:missing_rating_input') );
		}

		// Loop over all ratings and kill empty ones
		foreach ($ratings as $key => $val) if (trim($val) == FALSE) unset($ratings[$key]);

		// -----------------------------------------
		// Grab Rating Fields
		// -----------------------------------------
		$rating_fields = $this->EE->ratings_model->get_rating_fields($form_data['collection_id']);

		// No fields found?
		if (empty($rating_fields) == TRUE)
		{
			return $this->return_error('missing_data', $this->EE->lang->line('rating:error:field_notfound'));
		}

		// -----------------------------------------
		// Rating MetaData
		// -----------------------------------------
		$this->EE->ratings = array();
		$this->EE->rating_data = array();
		$this->EE->rating_data['site_id']		= $form_data['site_id'];
		$this->EE->rating_data['channel_id']	= $form_data['channel_id'];
		$this->EE->rating_data['ip_address']	= $IP;
		$this->EE->rating_data['collection_id']	= $form_data['collection_id'];
		$this->EE->rating_data['rating_author_id'] = $this->EE->session->userdata['member_id'];
		$this->EE->rating_data['rating_date']	= $this->EE->localize->now;
		$this->EE->rating_data['rating_type']	= $form_data['rating_type'];
		$this->EE->rating_data['rating_type_name']	= $form_data['rating_type_name'];
		$this->EE->rating_data['rating_status'] = 1;
		$this->EE->rating_data['entry_id'] = $form_data['entry_id'];
		$this->EE->rating_data['item_id'] = $form_data['item_id'];

		// -----------------------------------------
		// Loop over all Rating Fields
		// -----------------------------------------
		foreach ($rating_fields as $field)
		{
			// -----------------------------------------
			// Does fields exist? (REQUIRED FIELDS)
			// -----------------------------------------
			if ($field->required == 1 && isset($ratings[$field->short_name]) == FALSE)
			{
				return $this->return_error('missing_data', $this->EE->lang->line('rating:error:required_field') . $field->short_name);
			}

			// Not submitted? Then it Defaults to 0
			if (isset($ratings[$field->short_name]) == FALSE)
			{
				//$this->EE->ratings[$field->field_id] = 0;
				continue;
			}

			$rating = trim($ratings[$field->short_name]);

			// -----------------------------------------
			// Min/Max Rating
			// -----------------------------------------

			// Is the ratings NUMBER?
			$temp = trim($rating, '-.0123456789');
			if (empty($temp) == FALSE)
			{
				return $this->return_error('invalid_rating_format', $this->EE->lang->line('rating:error:invalid_rating_format'). $field->short_name );
			}

			// Too High or Too Low?
			if ( ($rating > $form_data['max_value']) OR ($rating < $form_data['min_value']))
			{
				return $this->return_error('out_of_range', $this->EE->lang->line('rating:error:out_of_range') . $field->short_name);
			}

			// -----------------------------------------
			// Insert!
			// -----------------------------------------
			$this->EE->ratings[$field->field_id] = $rating;
		}

		// Lets create a copy (for the AJAX response)
		$ratings = $this->EE->ratings;
		$ratings_data = $this->EE->rating_data;

		// Execute insert!
		$this->EE->ratings_model->insert_rating();

		// -----------------------------------------
		// Return Users
		// -----------------------------------------
		if (AJAX_REQUEST == FALSE)
		{
			$RET = $form_data['return'];

			// Redirect people
			$this->EE->load->helper('string');
			$RET = reduce_double_slashes($this->EE->functions->create_url(trim_slashes($RET)));
			$this->EE->functions->redirect($RET);
		}
		else
		{
			$this->new_rating_ajax_response($ratings_data, $ratings);
		}
	}

	// ********************************************************************************* //

	private function new_rating_ajax_response($ratings_data, $ratings)
	{
		$out = array();
		$out['success'] = 'yes';
		$out['overall'] = array();
		$out['fields'] = array();
		$out['field_labels'] = array();
		$out['your_ratings'] = array();

		// Disables Profiler
		$this->EE->output->enable_profiler(FALSE);

		// -----------------------------------------
		// Lets grab the stats for his entry
		// -----------------------------------------
		$this->EE->db->select('rs.*, rf.short_name, rf.title');
		$this->EE->db->from('exp_channel_ratings_stats rs');
		$this->EE->db->join('exp_channel_ratings_fields rf', 'rs.field_id = rf.field_id', 'left');
		if ($ratings_data['rating_type_name'] == 'entry') $this->EE->db->where('rs.entry_id', $ratings_data['entry_id']);
		else $this->EE->db->where('rs.item_id', $ratings_data['item_id']);
		$this->EE->db->where('rs.collection_id', $ratings_data['collection_id']);
		$query = $this->EE->db->get();

		// -----------------------------------------
		// Fill it in
		// -----------------------------------------
		foreach ($query->result() as $field)
		{
			// Check for the "MASTER" field which is 0
			if ($field->field_id == 0)
			{
				$field->short_name = 'overall';
				$out['overall']['avg'] = number_format($field->rating_avg, 2);
				$out['overall']['total'] = $field->rating_total;
				$out['overall']['sum'] = $field->rating_sum;
			}
			else
			{
				$out['fields'][$field->short_name]['avg'] = number_format($field->rating_avg, 2);
				$out['fields'][$field->short_name]['total'] = $field->rating_total;
				$out['fields'][$field->short_name]['sum'] = $field->rating_sum;

				$out['field_labels'][$field->short_name] = $field->title;
				$out['your_ratings'][$field->short_name] = $ratings[$field->field_id];
			}
		}


		// Send correct Headers
		if ($this->EE->config->item('send_headers') == 'y')
		{
			@header('Content-Type: application/json');
		}

		// Send Response
		exit($this->EE->ratings_helper->generate_json($out));
	}

	// ********************************************************************************* //

	/**
	 * RATING COMMENTS: (STEP 1)
	 * Create our Formparams & add our FPID to the comments form hidden fields
	 *
	 * @param array $hidden_fields
	 * @access public
	 * @return array - Array of hidden fields
	 */
	public function rating_comment_form_hidden_fields($hidden_fields)
	{
		// Variable prefix
		$prefix = $this->EE->TMPL->fetch_param('rating:prefix', 'rating') . ':';

		// -----------------------------------------
		// Get Collections!
		// -----------------------------------------
		$colls = $this->EE->ratings_model->get_collections();

		$this->collections = array();
		$this->default_collection = '';

		foreach ($colls as $col)
		{
			if ($col->default == 1) $this->default_collection = $col->collection_id;
			$this->collections[ $col->collection_name ] = $col->collection_id;
		}


		// Is rating enabled?? (Required if you want to enable ratings)
		if ( $this->EE->TMPL->fetch_param('rating:enabled') != 'yes')
		{
			$this->EE->TMPL->log_item('CHANNEL RATINGS: Ratings not enabled, use rating:enabled="yes" parameter.');
			return $hidden_fields;
		}

		// Some vars
		$form_data = array();
		$vars = array();

		/** ----------------------------------------
		/** Which Type?
		/** ----------------------------------------*/
		$rating_type = $this->TYPES['comment_review'];
		$rating_type_name = $this->TYPES_INV[$rating_type];

		// -----------------------------------------
		// What Collection
		// -----------------------------------------
		$collection_id = $this->default_collection;
		$coll_param = $this->EE->TMPL->fetch_param('rating:collection');
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
		/**  Form Data
		/** ----------------------------------------*/
		$form_data['allow_guests'] = FALSE;
		$form_data['require_captcha'] = FALSE;
		$form_data['require_rating'] = ($this->EE->TMPL->fetch_param('rating:required') == 'no') ? FALSE : TRUE;
		$form_data['allow_multiple'] = FALSE;
		$form_data['collection_id'] = $collection_id;
		$form_data['max_value'] = ($this->EE->TMPL->fetch_param('rating:max_value') > 2) ? $this->EE->TMPL->fetch_param('rating:max_value') : 5;
		$form_data['min_value'] = ($this->EE->TMPL->fetch_param('rating:min_value') >= 0) ? $this->EE->TMPL->fetch_param('rating:min_value') : 0;
		$form_data['return'] = ($this->EE->TMPL->fetch_param('rating:return') != FALSE) ? $this->EE->TMPL->fetch_param('rating:return') : '';
		$form_data['rating_type'] = $rating_type;
		$form_data['rating_type_name'] = $rating_type_name;
		$form_data['entry_id'] = $hidden_fields['entry_id'];
		$form_data['site_id'] = $this->EE->ratings_helper->getSiteId();

		$this->EE->session->cache['Channel_Ratings']['RateComment_Data'] = array('entry_id' => $form_data['entry_id'], 'collection_id' => $form_data['collection_id']);

		// Allow Guests?
		if ( $this->EE->TMPL->fetch_param('rating:allow_guests') != 'yes' && $this->EE->session->userdata['member_id'] == 0 )
		{
			$this->EE->TMPL->log_item('CHANNEL RATINGS: Guests are not allowed to rate.');
			return $hidden_fields;
		}
		elseif ($this->EE->TMPL->fetch_param('rating:allow_guests') == 'yes')
		{
			$form_data['allow_guests'] = TRUE;
		}

		// Allow Multiple
		if ( $this->EE->TMPL->fetch_param('rating:allow_multiple') == 'yes')
		{
			$form_data['allow_multiple'] = TRUE;
		}

		$hidden_fields['FDATA'] = $this->EE->ratings_helper->encrypt_string(serialize($form_data));

		return $hidden_fields;
	}

	// ********************************************************************************* //

	/**
	 * RATING COMMENTS: (STEP 2)
	 * Process the Tagdata (parse our variables in the comments form)
	 *
	 * @param string $tagdata
	 * @access public
	 * @return string - The parsed tagdata
	 */
	public function rating_comment_form_end($tagdata)
	{
		// Are we rating?
		if (isset($this->EE->session->cache['Channel_Ratings']['RateComment_Data']) == FALSE)
		{
			return $tagdata;
		}

		// Variable prefix
		$prefix = $this->EE->TMPL->fetch_param('rating:prefix', 'rating') . ':';

		$entry_id = $this->EE->session->cache['Channel_Ratings']['RateComment_Data']['entry_id'];
		$collection_id = $this->EE->session->cache['Channel_Ratings']['RateComment_Data']['collection_id'];

		$cond = array();

		// Allow Guests?
		if ( $this->EE->TMPL->fetch_param('rating:allow_guests') != 'yes' && $this->EE->session->userdata['member_id'] == 0 )
		{
			$this->EE->TMPL->log_item('CHANNEL RATINGS: Guests are not allowed to rate.');
			return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_access', $tagdata);
		}

		$cond[$prefix.'already_rated']	= true;
		$cond[$prefix.'not_rated']	= false;
		$cond[$prefix.'voted']	= true;
		$cond[$prefix.'not_voted']	= false;

		//----------------------------------------
		// Likes ? or Rating
		//----------------------------------------
		if (strpos($tagdata, $prefix.'already_rated') !== FALSE || strpos($tagdata, $prefix.'not_rated')) {
			// Has this user already rated?
			$already_rated = $this->EE->ratings_model->if_already_rated($this->TYPES['comment_review'], $entry_id, $collection_id);
			$cond[$prefix.'already_rated']	= $already_rated;
			$cond[$prefix.'not_rated']	= (($already_rated == FALSE) ? TRUE : FALSE);
		}

		if (strpos($tagdata, $prefix.'voted') !== FALSE || strpos($tagdata, $prefix.'not_voted')) {
			// Has this user already rated?
			$rlike_id = $this->EE->ratings_model->if_already_liked($this->TYPES['entry'], $entry_id, $collection_id);
			$cond[$prefix.'voted']	= ($rlike_id > 0) ? TRUE : FALSE;
			$cond[$prefix.'not_voted']	= ($rlike_id == 0) ? TRUE : FALSE;
		}

		if (strpos($tagdata, LD.$prefix.'fields'.RD) !== false) {
			/** ----------------------------------------
			/** Get Rating Fields
			/** ----------------------------------------*/
			$rating_fields = $this->EE->ratings_model->get_rating_fields($collection_id);

			if (empty($rating_fields) == TRUE)
			{
				$this->EE->TMPL->log_item('CHANNEL RATINGS: No rating fields found for this collection');
				return $this->EE->ratings_helper->custom_no_results_conditional($prefix.'no_access', $this->EE->TMPL->tagdata);
			}

			/** ----------------------------------------
			/** Parse Rating Fields
			/** ----------------------------------------*/
			$field_pair_data = $this->EE->ratings_helper->fetch_data_between_var_pairs($prefix.'fields', $tagdata);

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

			$tagdata = $this->EE->ratings_helper->swap_var_pairs($prefix.'fields', $field_pair_final, $tagdata);
		}


		// Parse Conditionals
		$tagdata = $this->EE->functions->prep_conditionals($tagdata, $cond);

		return $tagdata;
	}

	// ********************************************************************************* //

	/**
	 * RATING COMMENTS: (STEP 3)
	 * Check stuff before submitting the comment & cache the results so we can insert later
	 *
	 * @access public
	 * @return void
	 */
	public function rating_comment_precheck()
	{
		/** ----------------------------------------
		/**  Was the rating system enabled?
		/** ----------------------------------------*/
		if ($this->EE->input->post('FDATA') == FALSE)
		{
			return;
		}

		$this->EE->lang->loadfile('channel_ratings');
		$IP = sprintf("%u", ip2long($this->EE->input->ip_address()));
		$form_data = array();

		$FDATA = @unserialize($this->EE->ratings_helper->decode_string($this->EE->input->post('FDATA')));

		// -----------------------------------------
		// Process Form Data
		// -----------------------------------------
		if ($FDATA != FALSE)
		{
			$form_data = $FDATA;
		}
		else
		{
			// FPID was not a number, quit
			return $this->return_error('missing_data', $this->EE->lang->line('rating:error:missing_data') . '(FORM_DATA_MISSING_OR_CORRUPT)' );
		}

		/** ----------------------------------------
		/**  We need Channel ID
		/** ----------------------------------------*/
		if (isset($form_data['channel_id']) == FALSE)
		{
			$query = $this->EE->db->select('channel_id')->from('exp_channel_titles')->where('entry_id', $form_data['entry_id'])->limit(1)->get();
			$form_data['channel_id'] = (int) $query->row('channel_id');
		}

		/** ----------------------------------------
		/**  Allow Multiple?
		/** ----------------------------------------*/
		$like_submission = false;
		if (isset($_POST['like'])) {
			$rlike_id = $this->EE->ratings_model->if_already_liked($this->TYPES['entry'], $form_data['entry_id'], $form_data['collection_id']);
			$already_rated	= ($rlike_id > 0) ? TRUE : FALSE;
			$like_submission = true;
		} else {
			$already_rated = $this->EE->ratings_model->if_already_rated($form_data['rating_type'], $form_data['entry_id'], $form_data['collection_id']);
		}


		if (isset($form_data['allow_multiple']) == TRUE && $form_data['allow_multiple'] == FALSE && $already_rated == TRUE)
		{
			if ($form_data['require_rating'] == TRUE)
			{
				return $this->return_error('duplicate_rating', $this->EE->lang->line('rating:error:duplicate_rating') );
			}
			else
			{
				return;
			}
		}

		if ($like_submission) {
			if ($this->EE->input->post('like') == 'like') $form_data['liked'] = 1;
			elseif ($this->EE->input->post('like') == 'dislike') $form_data['disliked'] = 1;
			else return;
			$this->EE->session->cache['Channel_Ratings']['Like'] = $this->EE->input->post('like');
		} else {
			/** ----------------------------------------
			/**  Grab Rating Fields
			/** ----------------------------------------*/
			$rating_fields = $this->EE->ratings_model->get_rating_fields($form_data['collection_id']);

			// No fields found?
			if (empty($rating_fields) == TRUE)
			{
				return $this->return_error('missing_data', $this->EE->lang->line('rating:error:field_notfound'));
			}

			/** ----------------------------------------
			/**  Check Rating
			/** ----------------------------------------*/
			$ratings = $this->EE->input->post('rating');

			// Always needs to be an array
			if (is_array($ratings) == FALSE)
			{
				if ($form_data['require_rating'] == TRUE)
				{
					return $this->return_error('missing_data', $this->EE->lang->line('rating:error:missing_rating_input') );
				}
				else
				{
					return;
				}

			}

			// Loop over all ratings and kill empty ones
			foreach ($ratings as $key => $val) if (trim($val) == FALSE) unset($ratings[$key]);

			// Is rating required?
			if ($form_data['require_rating'] == TRUE && empty($ratings) == TRUE)
			{
				return $this->return_error('missing_data', $this->EE->lang->line('rating:error:rating_required'));
			}

			/** ----------------------------------------
			/**  Rating MetaData
			/** ----------------------------------------*/
			$this->EE->ratings = array();
			$this->EE->rating_data = array();
			$this->EE->rating_data['site_id']		= $form_data['site_id'];
			$this->EE->rating_data['channel_id']	= $form_data['channel_id'];
			$this->EE->rating_data['ip_address']	= $IP;
			$this->EE->rating_data['collection_id']	= $form_data['collection_id'];
			$this->EE->rating_data['rating_author_id'] = $this->EE->session->userdata['member_id'];
			$this->EE->rating_data['rating_date']	= $this->EE->localize->now;
			$this->EE->rating_data['rating_type']	= $form_data['rating_type'];
			$this->EE->rating_data['rating_type_name']	= $form_data['rating_type_name'];
			$this->EE->rating_data['rating_status'] = 1;
			$this->EE->rating_data['entry_id'] = $form_data['entry_id'];

			/** ----------------------------------------
			/**  Loop over all Rating Fields
			/** ----------------------------------------*/
			foreach ($rating_fields as $field)
			{
				/** ----------------------------------------
				/** Does fields exist? (REQUIRED FIELDS)
				/** ----------------------------------------*/
				if ($field->required == 1 && isset($ratings[$field->short_name]) == FALSE)
				{
					return $this->return_error('missing_data', $this->EE->lang->line('rating:error:required_field') . $field->title);
				}

				// Not submitted? Then it Defaults to 0
				if (isset($ratings[$field->short_name]) == FALSE)
				{
					//$this->EE->ratings[$field->field_id] = 0;
					continue;
				}

				$rating = trim($ratings[$field->short_name]);

				/** ----------------------------------------
				/**  Min/Max Rating
				/** ----------------------------------------*/

				// Is the ratings NUMBER?
				$temp = trim($rating, '-.0123456789');
				if (empty($temp) == FALSE)
				{
					return $this->return_error('invalid_rating_format', $this->EE->lang->line('rating:error:invalid_rating_format'). $field->short_name );
				}

				// Too High or Too Low?
				if ( ($rating > $form_data['max_value']) OR ($rating < $form_data['min_value']))
				{
					return $this->return_error('out_of_range', $this->EE->lang->line('rating:error:out_of_range') . $field->short_name);
				}

				/** ----------------------------------------
				/**  Insert!
				/** ----------------------------------------*/
				$this->EE->ratings[$field->field_id] = $rating;
			}

			// Store for later retrieval
			$this->EE->session->cache['Channel_Ratings']['RatingData'] = $this->EE->rating_data;
			$this->EE->session->cache['Channel_Ratings']['Ratings'] = $this->EE->ratings;
		}

		$this->EE->session->cache['Channel_Ratings']['FormData'] = $form_data;
	}

	// ********************************************************************************* //

	/**
	 * RATING COMMENTS: (STEP 4, Final Step?)
	 * Process the Tagdata (parse our variables in the comments form)
	 *
	 * @param array $data - Comments data
	 * @param bool $moderate Whether the comment is going to be moderated
	 * @param int $comment_id
	 * @access public
	 * @return void
	 */
	public function rating_comment_insert($data, $moderate, $comment_id)
	{
		if (isset($this->EE->session->cache['Channel_Ratings']['Ratings']) == TRUE)
		{
			// Retrieve!
			$this->EE->rating_data = $this->EE->session->cache['Channel_Ratings']['RatingData'];
			$this->EE->ratings = $this->EE->session->cache['Channel_Ratings']['Ratings'];

			$this->EE->rating_data['item_id'] = $comment_id;
			$this->EE->rating_data['rating_status'] = ($data['status'] == 'o') ? '1' : '0';
			$this->EE->ratings_model->insert_rating();
		}

		if (isset($this->EE->session->cache['Channel_Ratings']['Like']) == TRUE)
		{
			$form_data = $this->EE->session->cache['Channel_Ratings']['FormData'];
			$form_data['liked'] = 0;
			$form_data['disliked'] = 0;

			if ($this->EE->session->cache['Channel_Ratings']['Like'] == 'like') $form_data['liked'] = 1;
			elseif ($this->EE->session->cache['Channel_Ratings']['Like'] == 'dislike') $form_data['disliked'] = 1;

			/** ----------------------------------------
			/**  Lets insert!
			/** ----------------------------------------*/
			$data = array();
			$data['entry_id'] = $form_data['entry_id'];
			//$data['item_id'] = $comment_id;
			$data['item_id'] = 0;
			$data['channel_id'] = $form_data['channel_id'];
			$data['collection_id'] = $form_data['collection_id'];
			$data['liked'] = $form_data['liked'];
			$data['disliked'] = $form_data['disliked'];
			$data['ip_address'] = sprintf("%u", ip2long($this->EE->input->ip_address()));
			$data['like_author_id'] = $this->EE->session->userdata['member_id'];
			$data['like_date'] = $this->EE->localize->now;
			$data['like_type'] = $this->TYPES['entry'];
			$data['site_id'] = $this->site_id;
			$this->EE->db->insert('exp_channel_ratings_likes', $data);
			$like_id = $this->EE->db->insert_id();

			// EXTENSION DATA
			$EXTdata = $data;

			/** ----------------------------------------
			/**  Global Stats!
			/** ----------------------------------------*/
			// TODO: Move this to model!

			$this->EE->db->select("SUM(liked) as liked_sum, SUM(disliked) as disliked_sum, MAX(like_date) as like_last_date", FALSE);
			$this->EE->db->from('exp_channel_ratings_likes');
			$this->EE->db->where('like_type', $data['like_type']);
			$this->EE->db->where('entry_id', $data['entry_id']);
			$this->EE->db->where('item_id', $data['item_id']);
			$this->EE->db->where('collection_id', $form_data['collection_id']);
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
			$this->EE->db->where('like_type', $data['like_type']);
			$this->EE->db->where('entry_id', $data['entry_id']);
			$this->EE->db->where('item_id', $data['item_id']);
			$this->EE->db->where('collection_id', $data['collection_id']);
			$this->EE->db->where('stats_row', 1);
			$query = $this->EE->db->get();

			//----------------------------------------
			// Update Or Insert?
			//----------------------------------------
			if ($query->num_rows() == 0)
			{
				// new one Insert!
				$total['stats_row'] = 1;
				$total['like_type'] = $data['like_type'];
				$total['site_id']	= $this->site_id;
				$total['collection_id']= $data['collection_id'];
				$total['entry_id']	= $data['entry_id'];
				$total['channel_id']= $data['channel_id'];
				$total['item_id']	= $data['item_id'];
				$this->EE->db->insert( 'exp_channel_ratings_likes', $total);
			}
			else
			{
				// Update it!
				$this->EE->db->update( 'exp_channel_ratings_likes', $total, array('rlike_id' => $query->row('rlike_id') ) );
			}

			// -------------------------------------------
			// 'channelratings_insert_like_end' hook.
			//  - More emails, more processing, different redirect
			//
				$edata = $this->EE->extensions->call('channelratings_insert_like_end', 'entry', $EXTdata, $like_id);
				if ($this->EE->extensions->end_script === TRUE) return;
			//
			// -------------------------------------------
		}

		if (AJAX_REQUEST == TRUE)
		{
			$this->EE->session->cache['Channel_Ratings']['RatingData']['item_id'] = 0;
			$this->new_rating_ajax_response($this->EE->session->cache['Channel_Ratings']['RatingData'], $this->EE->session->cache['Channel_Ratings']['Ratings']);
		}

		/** ----------------------------------------
		/** Do we need to take over RETURN?
		/** ----------------------------------------*/
		if (isset($this->EE->session->cache['Channel_Ratings']['FormData']) !== FALSE)
		{
			if ($this->EE->session->cache['Channel_Ratings']['FormData']['return'] != FALSE)
			{
				$RET = $this->EE->session->cache['Channel_Ratings']['FormData']['return'];

				// Replace Comment ID
				$RET = str_replace('%COMMENT_ID%', $comment_id, $RET);

				$return_link = ( ! stristr($RET,'http://') && ! stristr($RET,'https://')) ? $this->EE->functions->create_url($RET) : $RET;
				$this->EE->functions->redirect(stripslashes($return_link));
			}
		}

		return;
	}

	// ********************************************************************************* //

	protected function return_error($type, $msg)
	{
		// Ajax Response?
		if (AJAX_REQUEST == TRUE)
		{
			$out = array();
			$out['success'] = 'no';
			$out['type'] = $type;
			$out['body'] = $msg;

			// Send correct Headers
			if ($this->EE->config->item('send_headers') == 'y')
			{
				@header('Content-Type: application/json');
			}

			// Send Response
			exit($this->EE->ratings_helper->generate_json($out));
		}
		else
		{
			return $this->EE->output->show_user_error('submission', $msg);
		}
	}

	// ********************************************************************************* //

	public function new_like()
	{
		// -----------------------------------------
		// Form Params
		// -----------------------------------------
		$form_data = array();

		if ($this->EE->input->get_post('data') == FALSE)
		{
			$this->new_like_response( $this->EE->lang->line('rating:error:not_authorized') );
		}

		$form_data = base64_decode($this->EE->input->get_post('data'));

		if (@unserialize($form_data) == FALSE)
		{
			$this->new_like_response( $this->EE->lang->line('rating:error:not_authorized') );
		}
		else
		{
			$form_data = unserialize($form_data);
		}

		// -----------------------------------------
		// Are We Deleting?
		// -----------------------------------------
		if ($form_data['action'] == 'delete')
		{
			// Get the author
			$query = $this->EE->db->select('*')->from('exp_channel_ratings_likes')->where('rlike_id', $form_data['rlike_id'])->get();
			if ($query->num_rows() == 0) $this->new_like_response( $this->EE->lang->line('rating:error:not_authorized') );
			if ($query->row('like_author_id') != $this->EE->session->userdata['member_id']) $this->new_like_response( $this->EE->lang->line('rating:error:not_authorized') );

			$this->EE->ratings_model->delete_like($form_data['rlike_id']);

			if (AJAX_REQUEST == TRUE)
			{
				$this->new_like_ajax_response($form_data['rlike_id'], $query->row_array(), 'del_like');
			}
			else
			{
				$REFERRED = $this->EE->input->server('HTTP_REFERER');
				if ($REFERRED == FALSE) $REFERRED = $this->EE->functions->fetch_site_index(0, 0);

				// We're done.
				$form_data = array(	'title' 	=> $this->EE->lang->line('thank_you'),
								'heading'	=> $this->EE->lang->line('thank_you'),
								'content'	=> $this->EE->lang->line('rating:success:vote_deleted'),
								'redirect'	=> $REFERRED,
								'link'		=> array($REFERRED, $this->EE->lang->line('back'))
								 );

				$this->EE->output->show_message($form_data);
				exit();
			}
		}

		// -----------------------------------------
		// Same IP?
		// -----------------------------------------
		if ($form_data['ip'] != sprintf("%u", ip2long($this->EE->input->ip_address())))
		{
			$this->new_like_response( $this->EE->lang->line('rating:error:not_authorized') );
		}

		/** ----------------------------------------
		/** Which Type?
		/** ----------------------------------------*/
		if (isset($form_data['rating_type']) == FALSE)
		{
			$this->new_like_response( $this->EE->lang->line('rating:error:not_authorized') );
		}

		// Easy to remember
		$item_id = $form_data['item_id'];
		$rating_type = $form_data['rating_type'];
		$rating_type_name = $this->TYPES_INV[$rating_type];

		if ($item_id < 1)
		{
			$this->new_like_response( $this->EE->lang->line('rating:error:not_authorized') );
		}


		/** ----------------------------------------
		/**  What action? Like or Dislike
		/** ----------------------------------------*/
		$form_data['liked'] = 0;
		$form_data['disliked'] = 0;

		if ($form_data['action'] == 'like') $form_data['liked'] = 1;
		elseif ($form_data['action'] == 'dislike') $form_data['disliked'] = 1;
		else $this->new_like_response( $this->EE->lang->line('rating:error:missing_data') . '(MISSING_ACTION)' );

		/** ----------------------------------------
		/**  Allow Guests?
		/** ----------------------------------------*/
		if (isset($form_data['allow_guests']) == TRUE && $form_data['allow_guests'] == FALSE && $this->EE->session->userdata['member_id'] == 0 )
		{
			$this->new_like_response( $this->EE->lang->line('rating:error:not_authorized') );
		}

		/** ----------------------------------------
		/**  Allow Multiple?
		/** ----------------------------------------*/
		$already_voted = $this->EE->ratings_model->if_already_liked($form_data['rating_type'], $form_data['item_id'], $form_data['collection_id']);

		if (isset($form_data['allow_multiple']) == TRUE && $form_data['allow_multiple'] == FALSE && $already_voted == TRUE)
		{
			return $this->return_error('duplicate_rating', $this->EE->lang->line('rating:error:duplicate_like') );
		}

		/** ----------------------------------------
		/** Do we need ENTRY_ID?
		/** ----------------------------------------*/
		$form_data['entry_id'] = 0;

		switch ($this->TYPES_INV[$form_data['rating_type']])
		{
			case 'entry':
				$form_data['entry_id'] = $form_data['item_id'];
				$form_data['item_id'] = 0;
				break;
			case 'comment_entry':
				$entryquery = $this->EE->db->select('entry_id')->from('exp_comments')->where('comment_id', $form_data['item_id'])->get();
				break;
			case 'channel_images':
				$entryquery = $this->EE->db->select('entry_id')->from('exp_channel_images')->where('image_id', $form_data['item_id'])->get();
				break;
			case 'channel_files':
				$entryquery = $this->EE->db->select('entry_id')->from('exp_channel_files')->where('file_id', $form_data['item_id'])->get();
				break;
			case 'channel_videos':
				$entryquery = $this->EE->db->select('entry_id')->from('exp_channel_videos')->where('video_id', $form_data['item_id'])->get();
				break;
		}

		if (isset($entryquery) != FALSE) $form_data['entry_id'] = $entryquery->row('entry_id');

		/** ----------------------------------------
		/** Do we need CHANNEL_ID?
		/** ----------------------------------------*/
		$form_data['channel_id'] = 0;

		switch ($this->TYPES_INV[$form_data['rating_type']])
		{
			case 'entry':
				$channelquery = $this->EE->db->select('channel_id')->from('exp_channel_titles')->where('entry_id', $form_data['entry_id'])->get();
				break;
			case 'comment_entry':
				$channelquery = $this->EE->db->select('channel_id')->from('exp_comments')->where('comment_id', $form_data['item_id'])->get();
				break;
			case 'channel_images':
				$channelquery = $this->EE->db->select('channel_id')->from('exp_channel_images')->where('image_id', $form_data['item_id'])->get();
				break;
			case 'channel_files':
				$channelquery = $this->EE->db->select('channel_id')->from('exp_channel_files')->where('file_id', $form_data['item_id'])->get();
				break;
			case 'channel_videos':
				$channelquery = $this->EE->db->select('channel_id')->from('exp_channel_videos')->where('video_id', $form_data['item_id'])->get();
				break;
		}

		if (isset($channelquery) != FALSE) $form_data['channel_id'] = $channelquery->row('channel_id');

		/** ----------------------------------------
		/**  Lets insert!
		/** ----------------------------------------*/
		$data = array();
		$data['entry_id'] = $form_data['entry_id'];
		$data['item_id'] = $form_data['item_id'];
		$data['channel_id'] = $form_data['channel_id'];
		$data['collection_id'] = $form_data['collection_id'];
		$data['liked'] = $form_data['liked'];
		$data['disliked'] = $form_data['disliked'];
		$data['ip_address'] = $form_data['ip'];
		$data['like_author_id'] = $this->EE->session->userdata['member_id'];
		$data['like_date'] = $this->EE->localize->now;
		$data['like_type'] = $rating_type;
		$data['site_id'] = $this->site_id;
		$this->EE->db->insert('exp_channel_ratings_likes', $data);
		$like_id = $this->EE->db->insert_id();

		// EXTENSION DATA
		$EXTdata = $data;

		/** ----------------------------------------
		/**  Global Stats!
		/** ----------------------------------------*/
		// TODO: Move this to model!

		$this->EE->db->select("SUM(liked) as liked_sum, SUM(disliked) as disliked_sum, MAX(like_date) as like_last_date", FALSE);
		$this->EE->db->from('exp_channel_ratings_likes');
		$this->EE->db->where('like_type', $data['like_type']);
		$this->EE->db->where('entry_id', $data['entry_id']);
		$this->EE->db->where('item_id', $data['item_id']);
		$this->EE->db->where('collection_id', $form_data['collection_id']);
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
		$this->EE->db->where('like_type', $data['like_type']);
		$this->EE->db->where('entry_id', $data['entry_id']);
		$this->EE->db->where('item_id', $data['item_id']);
		$this->EE->db->where('collection_id', $data['collection_id']);
		$this->EE->db->where('stats_row', 1);
		$query = $this->EE->db->get();

		//----------------------------------------
		// Update Or Insert?
		//----------------------------------------
		if ($query->num_rows() == 0)
		{
			// new one Insert!
			$total['stats_row'] = 1;
			$total['like_type'] = $data['like_type'];
			$total['site_id']	= $this->site_id;
			$total['collection_id']= $data['collection_id'];
			$total['entry_id']	= $data['entry_id'];
			$total['channel_id']= $data['channel_id'];
			$total['item_id']	= $data['item_id'];
			$this->EE->db->insert( 'exp_channel_ratings_likes', $total);
		}
		else
		{
			// Update it!
			$this->EE->db->update( 'exp_channel_ratings_likes', $total, array('rlike_id' => $query->row('rlike_id') ) );
		}

		// -------------------------------------------
		// 'channelratings_insert_like_end' hook.
		//  - More emails, more processing, different redirect
		//
			$edata = $this->EE->extensions->call('channelratings_insert_like_end', $rating_type_name, $EXTdata, $like_id);
			if ($this->EE->extensions->end_script === TRUE) return;
		//
		// -------------------------------------------

		/** ----------------------------------------
		/**  Back to the USER!
		/** ----------------------------------------*/
		$this->EE->load->helper('string');

		if (AJAX_REQUEST == TRUE)
		{
			$this->new_like_ajax_response($like_id, $data);
		}
		else
		{
			// Return goes first!
			if ($form_data['return'] != FALSE)
			{
				// Redirect people
				$form_data['return'] = $this->EE->functions->create_url(reduce_double_slashes(trim_slashes($form_data['return'])));
				$this->EE->functions->redirect($form_data['return']);
			}

			$REFERRED = $this->EE->input->server('HTTP_REFERER');
			if ($REFERRED == FALSE) $REFERRED = $this->EE->functions->fetch_site_index(0, 0);

			// We're done.
			$form_data = array(	'title' 	=> $this->EE->lang->line('thank_you'),
							'heading'	=> $this->EE->lang->line('thank_you'),
							'content'	=> $this->EE->lang->line('rating:success:new_like'),
							'redirect'	=> $REFERRED,
							'link'		=> array($REFERRED, $this->EE->lang->line('back'))
							 );

			$this->EE->output->show_message($form_data);
			exit();
		}
	}

	// ********************************************************************************* //

	private function new_like_response($response)
	{
		if (AJAX_REQUEST == TRUE)
		{
			// Disables Profiler
			$this->EE->output->enable_profiler(FALSE);

			$out = array();
			$out['success'] = 'no';
			$out['body'] = $response;

			// Send correct Headers
			if ($this->EE->config->item('send_headers') == 'y')
			{
				@header('Content-Type: application/json');
			}

			// Send Response
			exit($this->EE->ratings_helper->generate_json($out));
		}
		else
		{
			exit ($this->EE->output->show_user_error('submission', $response));
		}
	}

	// ********************************************************************************* //

	private function new_like_ajax_response($rlike_id, $data, $action='new_like')
	{
		// Disables Profiler
		$this->EE->output->enable_profiler(FALSE);

		$out = array();
		$out['success'] = 'yes';
		$out['body'] = $this->EE->lang->line('rating:success:new_like');
		$out['action'] = $action;
		$out['stats'] = array('likes' => 0, 'dislikes' => 0, 'total' => 0);
		$out['stats_percent'] = array('likes' => 0, 'dislikes' => 0);
		$out['like_url'] = '';
		$out['dislike_url'] = '';
		$out['delete_url'] = '';

		if ($action == 'del_like') $out['body'] = $this->EE->lang->line('rating:success:vote_deleted');

		if ($data == FALSE)
		{
			$query = $this->EE->db->select('*')->from('exp_channel_ratings_likes')->where('rlike_id', $rlike_id)->get();
			if ($query->num_rows() == 0)
			{
				// Send correct Headers
				if ($this->EE->config->item('send_headers') == 'y')
				{
					@header('Content-Type: application/json');
				}

				// Send Response
				exit($this->EE->ratings_helper->generate_json($out));
			}
			else
			{
				$data = $query->row_array();
			}
		}

		// -----------------------------------------
		// Lets grab the stats for his entry
		// -----------------------------------------
		$this->EE->db->select('*');
		$this->EE->db->from('exp_channel_ratings_likes');
		$this->EE->db->where('like_type', $data['like_type']);
		$this->EE->db->where('entry_id', $data['entry_id']);
		$this->EE->db->where('item_id', $data['item_id']);
		$this->EE->db->where('collection_id', $data['collection_id']);
		$this->EE->db->where('stats_row', 1);
		$query = $this->EE->db->get();

		//print_r($data);
		//print_r($row = $query->row());
		$row = $query->row();

		$liked = 0;
		$disliked = 0;
		if (isset($row->liked) != FALSE && $row->liked != FALSE) $liked = $row->liked;
		if (isset($row->disliked) != FALSE && $row->disliked != FALSE) $disliked = $row->disliked;

		$out['stats']['likes'] = $liked;
		$out['stats']['dislikes'] = $disliked;
		$out['stats']['total'] = $disliked + $liked;
		$out['stats']['score'] = $liked - $disliked;
		$out['stats_percent']['likes'] = ceil( @( $out['stats']['likes'] / $out['stats']['total'] ) * 100 );
		$out['stats_percent']['dislikes'] = ceil( @( $out['stats']['dislikes'] / $out['stats']['total'] ) * 100 );

		$ACT_URL = $this->EE->ratings_helper->get_router_url('url', 'insert_like');

		if ($action == 'new_like')
		{
			$arr = array('rlike_id' => $rlike_id, 'action' => 'delete');
			$out['delete_url'] = $ACT_URL . '&data=' . base64_encode(serialize($arr));
		}
		elseif ($action == 'del_like')
		{
			$out['like_url'] = '';
			$out['dislike_url'] = '';
		}

		// Send correct Headers
		if ($this->EE->config->item('send_headers') == 'y')
		{
			@header('Content-Type: application/json');
		}

		// Send Response
		exit($this->EE->ratings_helper->generate_json($out));
	}

	// ********************************************************************************* //

	public function CP_rating_comment_moderation()
	{
		if (isset($_SERVER['HTTP_REFERER']) == FALSE OR strpos($_SERVER['HTTP_REFERER'], 'module=comment') == FALSE)
		{
			return;
		}

		ob_start();
		?>

		var ChannelRatings = ChannelRatings ? ChannelRatings : {};
		ChannelRatings.CommentIDS = [];
		ChannelRatings.AJAX_URL = '%CHANNELRATINGS_ACT%';
		ChannelRatings.THEME_URL = '%CHANNELRATINGS_THEMEURL%';

		$(document).ready(function() {

			setTimeout(function(){

				if (typeof(oTable) == 'object'){
					oTable.dataTableSettings[0].aoDrawCallback.push({sName:'user', fn:ChannelRatings.AddRatingsToDataTable});
					oTable.fnDraw();
				}
			}, 700);

			if (Comment_cp.data) {
				$('#target .mainTable').bind('tableupdate', function(){
					ChannelRatings.AddRatingsToTable();
				});

				ChannelRatings.AddRatingsToTable();
			}

		});

		ChannelRatings.AddRatingsToDataTable = function(){

			ChannelRatings.CommentIDS = [];

			jQuery.each(oTable.dataTableSettings[0].aoData, function(index, objTR){
				ChannelRatings.CommentIDS.push( objTR._aData[1].match('comment_id=(.*?)\'>')[1] );
			});

			jQuery.post(ChannelRatings.AJAX_URL, {ajax_method:'CP_ratings_comment_moderate', ids:ChannelRatings.CommentIDS}, function(rData){
				if (rData.success != 'yes') {
					return false;
				}

				jQuery.each(oTable.dataTableSettings[0].aoData, function(index, objTR){
					var cmtid =  objTR._aData[1].match('comment_id=(.*?)\'>')[1];

					if (typeof(rData.ratings[cmtid]) != 'undefined'){
						objTR._aData[9] += '<p class="ratings">';
						jQuery.each(rData.ratings[cmtid], function(field, rating){
							objTR._aData[9] += '<strong>' + field + ': </strong>';
							objTR._aData[9] += rating.img.replace(new RegExp('%TURL%','g'), ChannelRatings.THEME_URL);
							objTR._aData[9] += '&nbsp;&nbsp;(' + rating.r + ')';
							objTR._aData[9] += '<br />';
						});
						objTR._aData[9] += '</p>';
					}
				});

			}, 'json');

		};


		ChannelRatings.AddRatingsToTable = function(){

			ChannelRatings.CommentIDS = [];

			for (var i = Comment_cp.data.length - 1; i >= 0; i--) {
				ChannelRatings.CommentIDS.push(Comment_cp.data[i].comment_id);
			}

			jQuery.post(ChannelRatings.AJAX_URL, {ajax_method:'CP_ratings_comment_moderate', ids:ChannelRatings.CommentIDS}, function(rData){
				if (rData.success != 'yes') {
					return false;
				}

				for (var i = Comment_cp.data.length - 1; i >= 0; i--) {
					var cmtid = Comment_cp.data[i].comment_id;

					if (typeof(rData.ratings[cmtid]) != 'undefined'){
						Comment_cp.data[i].comment.data += '<p class="ratings">';
						jQuery.each(rData.ratings[cmtid], function(field, rating){
							Comment_cp.data[i].comment.data += '<strong>' + field + ': </strong>';
							Comment_cp.data[i].comment.data += rating.img.replace(new RegExp('%TURL%','g'), ChannelRatings.THEME_URL);
							Comment_cp.data[i].comment.data += '&nbsp;&nbsp;(' + rating.r + ')';
							Comment_cp.data[i].comment.data += '<br />';
						});
						Comment_cp.data[i].comment.data += '</p>';
					}
				}

			}, 'json');

		};

		<?php
		$out = ob_get_contents();
		ob_end_clean();

		return $out;
	}

	// ********************************************************************************* //

	public function CP_rating_edit_comment()
	{
		if (isset($_SERVER['HTTP_REFERER']) == FALSE OR strpos($_SERVER['HTTP_REFERER'], 'module=comment&method=edit_comment_form&comment_id=') == FALSE)
		{
			return;
		}

		ob_start();
		?>

		var ChannelRatings = ChannelRatings ? ChannelRatings : {};
		ChannelRatings.AJAX_URL = '%CHANNELRATINGS_ACT%';
		ChannelRatings.THEME_URL = '%CHANNELRATINGS_THEMEURL%';

		function getUrlVars() {
			var map = {};
			var parts = window.location.search.replace(/[?&]+([^=&]+)(=[^&]*)?/gi, function(m,key,value) {
				map[key] = (value === undefined) ? true : value.substring(1);
			});
			return map;
		};

		$(document).ready(function() {
			var URLVars = getUrlVars();

			if (typeof(URLVars.comment_id) == 'undefined') return false;

			ChannelRatings.RatingsForEditComment(URLVars.comment_id);
		});

		ChannelRatings.RatingsForEditComment = function(comment_id){
			jQuery.post(ChannelRatings.AJAX_URL, {ajax_method:'CP_ratings_edit_comment', comment_id:comment_id}, function(rData){
				if (rData.success != 'yes') {
					return false;
				}

			var TR = '<tr class="odd">';
			TR += '<td>Ratings</td>';
			TR += '<td>';

			jQuery.each(rData.fields, function(field, rating){
				TR += '<strong>' + field + ': </strong>';
				TR += rating.img.replace(new RegExp('%TURL%','g'), ChannelRatings.THEME_URL);
				TR += '&nbsp;&nbsp;(' + rating.r + ')';
				TR += '<br />';
			});

			TR += '</td>';
			TR += '</tr>';


			$('.mainTable tbody').append(TR);

			}, 'json');
		};

		<?php
		$out = ob_get_contents();
		ob_end_clean();

		return $out;
	}

	// ********************************************************************************* //


} // END CLASS

/* End of file act.channel_ratings.php  */
/* Location: ./system/expressionengine/third_party/channel_ratings/act.channel_ratings.php */
