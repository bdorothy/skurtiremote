<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Channel Ratings AJAX File
 *
 * @package			DevDemon_ChannelRatings
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 */
class Channel_Ratings_AJAX
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
		$this->EE->load->library('ratings_helper');
		$this->EE->lang->loadfile('channel_ratings');
		$this->EE->load->model('ratings_model');
		$this->EE->load->helper('form');
		$this->site_id = $this->EE->ratings_helper->getSiteId();

		// Load Rating Types
		$this->EE->load->add_package_path(PATH_THIRD . 'channel_ratings/');
		$this->EE->config->load('ratings');
		$this->TYPES = $this->EE->config->item('cr_rating_types');
		$this->TYPES_INV = array_flip($this->TYPES);

		$this->current_collection = $this->EE->ratings_helper->get_current_collection();
	}

	// ********************************************************************************* //

	public function rating_type_toggler()
	{
		$vData = array();
		$out = array('body' => '', 'filters' =>'', 'columns' =>'');
		$type = $this->EE->input->post('rating_type');

		//----------------------------------------
		// Columns
		//----------------------------------------
		$this->EE->config->load('rating_type_columns');
		$columns = $this->EE->config->item('cr_columns');
		$out['columns'] = $columns[ $type ];

		//----------------------------------------
		// Get All Channels
		//----------------------------------------
		$vData['channels'] = array();

		$query = $this->EE->db->select('channel_id, channel_title')->from('exp_channels')->where('site_id', $this->site_id)->order_by('channel_title', 'ASC')->get();
		foreach ($query->result() as $row) $vData['channels'][ $row->channel_id ] = $row->channel_title;

		//----------------------------------------
		// VData
		//----------------------------------------
		$vData['rating_type'] = $type;

		//----------------------------------------
		// Likes
		//----------------------------------------
		if ($this->EE->input->post('section') == 'likes')
		{
			$vData['rating_type'] = 'likes_'.$type;

			unset($out['columns']['standard']['rating'], $out['columns']['standard']['rating_date'], $out['columns']['standard']['rating_author'], $out['columns']['standard']['rating_status']);

			$out['columns']['standard']['vote'] = array('name' => $this->EE->lang->line('cr:vote'), 'sortable' => 'true');
			$out['columns']['standard']['like_date'] = array('name' => $this->EE->lang->line('cr:like_date'), 'sortable' => 'true');
			$out['columns']['standard']['like_author'] = array('name' => $this->EE->lang->line('cr:like_author'), 'sortable' => 'true');
		}

		$out['table_name'] = $vData['rating_type'];
		$vData['columns'] = $out['columns'];

		//----------------------------------------
		// Filters & Body
		//----------------------------------------
		$out['filters'] = $this->EE->load->view('mcp/rating_types/filters_' . $type, $vData, TRUE);
		$out['body'] = $this->EE->load->view('mcp/rating_types/datatable', $vData, TRUE);

		exit( $this->EE->ratings_helper->generate_json($out) );
	}

	// ********************************************************************************* //

	public function ajax_datatable()
	{
		$this->EE->load->model('ratings_mcp_model');

		$method = 'datatable_'.$this->EE->input->get_post('datatable');
		$this->EE->ratings_mcp_model->{$method}();
	}

	// ********************************************************************************* //

	public function edit_rating_modal()
	{
		$rating_id = $this->EE->input->post('rating_id');

		$query = $this->EE->db->select('rating_date, rating_author_id, rating_type, ip_address, rating_status')->from('exp_channel_ratings')->where('rating_id', $rating_id)->get();
		if ($query->num_rows() == 0) exit('RATING NOT FOUND');
		$member = $this->EE->db->select('*')->from('exp_members')->where('member_id', $query->row('rating_author_id'))->get();

		$this->EE->db->select('cr.rating, cr.field_id, cf.title');
		$this->EE->db->from('exp_channel_ratings cr');
		$this->EE->db->join('exp_channel_ratings_fields cf', 'cf.field_id = cr.field_id', 'left');
		$this->EE->db->where('cr.rating_date', $query->row('rating_date'));
		$this->EE->db->where('cr.rating_author_id', $query->row('rating_author_id'));
		$this->EE->db->where('cr.rating_type', $query->row('rating_type'));
		$this->EE->db->where('cr.ip_address', $query->row('ip_address'));
		$this->EE->db->where('cr.field_id !=', '0');
		$rating = $this->EE->db->get();

		$data = array();
		$data['member'] = $member->row();
		$data['fields'] = $rating->result();
		$data['rating'] = $query->row();
		$data['rating_type'] = array_search($query->row('rating_type'), $this->TYPES);
		$data['rating_id'] = $rating_id;

		exit( $this->EE->load->view('mcp/ajax/edit_rating_modal', $data, TRUE) );
	}

	// ********************************************************************************* //

	public function edit_rating_save()
	{
		$rating_id = $this->EE->input->get_post('rating_id');
		if ($rating_id == FALSE) exit('MISSING RATING');

		$data = array();

		// Rating Status
		$data['rating_status'] = ($this->EE->input->get_post('rating_status') == '1') ? 1 : 0;

		$this->EE->ratings_model->update_rating($rating_id, $data, $_POST['ratingfield']);
	}

	// ********************************************************************************* //

	public function rating_action()
	{
		$action = $this->EE->input->post('action');
		$type = $this->EE->input->post('type');
		$ids = $this->EE->input->post('ids');

		if (is_array($ids) == FALSE) $ids = array();

		foreach ($ids as $id)
		{
			$data = array();

			// Rating: Close?
			if ($action == 'close' && $type == 'rating') { $this->EE->ratings_model->update_rating($id, array('rating_status'=>0)); continue; }

			// Rating: Open?
			if ($action == 'open' && $type == 'rating') { $this->EE->ratings_model->update_rating($id, array('rating_status'=>1)); continue; }

			// Rating: Delete :O
			if ($action == 'delete' && $type == 'rating') { $this->EE->ratings_model->delete_rating($id); continue; }


			// Likes: Like
			if ($action == 'like' && $type == 'likes') { $this->EE->ratings_model->update_like($id, 'like'); continue; }

			// Likes: Dislike
			if ($action == 'dislike' && $type == 'likes') { $this->EE->ratings_model->update_like($id, 'dislike'); continue; }

			// Likes: Delete :O
			if ($action == 'delete' && $type == 'likes') { $this->EE->ratings_model->delete_like($id); continue; }
		}

		exit('Done');
	}

	// ********************************************************************************* //


















	public function recount_mcp()
	{
		$data = array('success' => 'no', 'body' => '');
		$this->EE->load->model('ratings_recount');

		// Execute!
		$ret = $this->EE->ratings_recount->recount_items($this->EE->input->get_post('type'), $this->EE->input->get_post('data'));

		// Did we have a success?
		if ($ret != FALSE)
		{
			$data['success'] = 'yes';
		}

		exit( $this->EE->ratings_helper->generate_json($data) );
	}

	// ********************************************************************************* //

	public function CP_ratings_comment_moderate()
	{
		$out = array('success' => 'no');

		if (isset($_POST['ids']) == FALSE OR empty($_POST['ids']) == TRUE)
		{
			exit( $this->EE->ratings_helper->generate_json($out) );
		}

		$out['ratings'] = array();

		foreach ($_POST['ids'] as $item_id)
		{
			// Grab ratings
			$query = $this->EE->db->select('cr.rating, cr.field_id, rf.title AS field_title')
					->from('exp_channel_ratings cr')
					->join('exp_channel_ratings_fields rf', 'cr.field_id = rf.field_id', 'left')
					->where('item_id', $item_id)
					->where('rating_type', $this->TYPES['comment_review'])
					->order_by('field_id', 'desc')->get();

			if ($query->num_rows() == 0) continue;

			$ratings = array();

			foreach ($query->result() as $row)
			{
				if ($row->field_id == 0) $row->field_title = 'Overall';
				$ratings[$row->field_title] = array('r'=>$row->rating, 'img' => $this->parse_star_images($row->rating, 2, 5, '%TURL%'));
			}

			$out['ratings'][$item_id] = $ratings;
		}

		$out['success'] = 'yes';
		exit( $this->EE->ratings_helper->generate_json($out) );
	}

	// ********************************************************************************* //

	public function CP_ratings_edit_comment()
	{
		$out = array('success' => 'no');

		if (isset($_POST['comment_id']) == FALSE OR empty($_POST['comment_id']) == TRUE)
		{
			exit( $this->EE->ratings_helper->generate_json($out) );
		}

		$item_id = $_POST['comment_id'];

		$out['fields'] = array();

		// Grab ratings
		$query = $this->EE->db->select('cr.rating, cr.field_id, rf.title AS field_title')
				->from('exp_channel_ratings cr')
				->join('exp_channel_ratings_fields rf', 'cr.field_id = rf.field_id', 'left')
				->where('item_id', $item_id)
				->where('rating_type', $this->TYPES['comment_review'])
				->order_by('field_id', 'desc')->get();

		if ($query->num_rows() == 0) continue;

		$ratings = array();

		foreach ($query->result() as $row)
		{
			if ($row->field_id == 0) $row->field_title = 'Overall';
			$ratings[$row->field_title] = array('r'=>$row->rating, 'img' => $this->parse_star_images($row->rating, 2, 5, '%TURL%'));
		}

		$out['fields'] = $ratings;


		$out['success'] = 'yes';
		exit( $this->EE->ratings_helper->generate_json($out) );
	}

	// ********************************************************************************* //

	public function rating_search()
	{
		if ($this->EE->input->get_post('type') == FALSE) exit('');

		$this->rating_status = array('0' => '<span class="closed">'.$this->EE->lang->line('rating:closed').'</span>', '1' => '<span class="open">'.$this->EE->lang->line('rating:open').'</span>');
		$this->rating_actions = '<a href="#" class="editrating" rel="%RATING_ID%"></a> <a href="#" class="%OPENCLOSE%" rel="%RATING_ID%"></a> <a href="#" class="delrating" rel="%RATING_ID%"></a>';

		$type = $this->EE->input->get_post('type');
		$data = $this->{$type}();
		exit( $this->EE->ratings_helper->generate_json($data) );
	}

	// ********************************************************************************* //



	private function parse_star_images($rating, $precision = '2', $scale = '5', $image_url = '')
	{
		$OUT = '';

		//	Get array
		$number	= explode( ".", number_format( $rating, $precision, '.', '' ) );

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
		//	Parse images
		// ----------------------------------------

		// Loop over all FULL Stars
		for ($i = $number['0']; $i > 0; $i-- )
		{
			$OUT .=	"<img src='{$data['urlfull']}' alt='{$i}'/>";
		}

		// Add the remainder
		$OUT	.= ( $number['1'] == 0 ) ? '': "<img src='{$data['urlrem']}' alt='{$i}'/>";

		// Add the fillers (the empty ones)
		for ( $i = $data['filler']; $i > 0; $i-- )
		{
			$OUT	.= "<img src='{$data['urlfill']}' alt='{$i}'/>";
		}


		return $OUT;
	}


} // END CLASS

/* End of file ajax.channel_ratings.php  */
/* Location: ./system/expressionengine/third_party/channel_ratings/ajax.channel_ratings.php */
