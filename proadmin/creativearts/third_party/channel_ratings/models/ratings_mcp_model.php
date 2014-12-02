<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Ratings MCP Model File
 *
 * @package			DevDemon_ChannelRatings
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 */
class Ratings_mcp_model
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

		$this->EE->load->library('ratings_helper');
		$this->EE->load->model('ratings_model');

		$this->site_id = $this->EE->input->post('site_id') ? $this->EE->input->post('site_id') : $this->EE->config->item('site_id');

		$this->rating_status = array('0' => '<span class="label label-important">'.$this->EE->lang->line('cr:closed').'</span>', '1' => '<span class="label label-success">'.$this->EE->lang->line('cr:open').'</span>');
		$this->like_label = array('0' => '<span class="label label-important">'.$this->EE->lang->line('cr:dislike').'</span>', '1' => '<span class="label label-success">'.$this->EE->lang->line('cr:like').'</span>');

		$this->current_collection = $this->EE->ratings_helper->get_current_collection();

		$this->dt_columns();
		$this->guest_label = $this->EE->lang->line('cr:guest');
	}

	// ********************************************************************************* //

	public function get_rating_types()
	{
		$types = array();

		// Channel Entries
		$types[] = 'entry';

		// Channel Comments
		if ($this->EE->db->table_exists('exp_comments') == TRUE)
		{
			$types[] = 'comment_review';
			$types[] = 'comment_entry';
		}

		// Site Members
		$types[] = 'member';

		// Channel Images
		if ($this->EE->db->table_exists('exp_channel_images') == TRUE)
		{
			$types[] = 'channel_images';
		}

		// Channel Files
		if ($this->EE->db->table_exists('exp_channel_files') == TRUE)
		{
			$types[] = 'channel_files';
		}

		// Channel Videos
		if ($this->EE->db->table_exists('exp_channel_videos') == TRUE)
		{
			$types[] = 'channel_videos';
		}

		// Brilliant Retail
		if ($this->EE->db->table_exists('exp_br_product') == TRUE)
		{
			$types[] = 'br_product';
		}

		return $types;
	}

	// ********************************************************************************* //

	public function datatable_entry()
	{
		$this->EE->db->save_queries = TRUE;

		// What Rating Type
		$rating_type = $this->TYPES['entry'];

		//----------------------------------------
		// Prepare Data Array
		//----------------------------------------
		$data = array();
		$data['aaData'] = array();
		$data['sEcho'] = $this->EE->input->get_post('sEcho');
		$data['iTotalRecords'] = $this->EE->ratings_model->total_unique_ratings($rating_type);

		//----------------------------------------
		// Select & Join
		//----------------------------------------
		$this->EE->db->select('ct.title, ct.url_title, ch.channel_title, cr.rating, cr.rating_status, cr.rating_date, cr.rating_author_id, cr.rating_id, mb.screen_name AS rating_author_screen_name');
		$this->EE->db->from('exp_channel_ratings cr');
		$this->EE->db->join('exp_members mb', 'mb.member_id = cr.rating_author_id', 'left');
		$this->EE->db->join('exp_channel_titles ct', 'ct.entry_id = cr.entry_id', 'left');
		$this->EE->db->join('exp_channels ch', 'ch.channel_id = cr.channel_id', 'left');

		//----------------------------------------
		// WHERE/LIKE
		//----------------------------------------
		$this->EE->db->where('cr.site_id', $this->site_id);
		$this->EE->db->where('cr.collection_id', $this->current_collection);
		$this->EE->db->where('cr.rating_type', $rating_type);
		$this->EE->db->where('cr.field_id', 0);

		if ($this->EE->input->post('date_from') != FALSE) $this->EE->db->where('cr.rating_date >', strtotime($_POST['date_from'] . ' 00:01:00'));
		if ($this->EE->input->post('date_to') != FALSE) $this->EE->db->where('cr.rating_date <', strtotime($_POST['date_to'] . ' 23:59:00'));

		if ($this->EE->input->post('entry_title') != FALSE) $this->EE->db->like('ct.title', $this->EE->input->post('entry_title'), 'both');

		if (isset($_POST['channels']) == TRUE && empty($_POST['channels']) == FALSE)
		{
			$this->EE->db->where_in('cr.channel_id', $_POST['channels']);
		}

		//----------------------------------------
		// Sort By
		//----------------------------------------
		$sort_cols = $this->EE->input->get_post('iSortingCols');

		for ($i = 0; $i < $sort_cols; $i++)
		{
			$col = $this->EE->input->get_post('iSortCol_'.$i);
			$sort =  $this->EE->input->get_post('sSortDir_'.$i);

			// Translate to column name
			$col = $this->cols_inv[$col];

			switch ($col)
			{
				case 'entry_title':
					$this->EE->db->order_by('ct.title', $sort);
					break;
				case 'entry_url_title':
					$this->EE->db->order_by('ct.url_title', $sort);
					break;
				case 'entry_channel':
					$this->EE->db->order_by('ch.channel_title', $sort);
					break;
				case 'rating':
					$this->EE->db->order_by('cr.rating', $sort);
					break;
				case 'rating_date':
					$this->EE->db->order_by('cr.rating_date', $sort);
					break;
				case 'rating_author':
					$this->EE->db->order_by('rating_author_screen_name', $sort);
					break;
				case 'rating_status':
					$this->EE->db->order_by('cr.rating_status', $sort);
					break;
				default:
					$this->EE->db->order_by('cr.rating_date', 'DESC');
					break;
			}

		}

		//----------------------------------------
		// Execute SQL
		//----------------------------------------
		$this->dt_limit_offset();
		$sql = $this->EE->db->_compile_select();
		$data['iTotalDisplayRecords'] = $this->get_display_amount($sql);
		$query = $this->EE->db->query($sql);

		//----------------------------------------
		// Loop Over all
		//----------------------------------------
		foreach ($query->result() as $row)
		{
			$trow = array();
			$trow['rating_id']     = "<a href='#' class='EditRating' data-rid='{$row->rating_id}'>{$row->rating_id}</a>";
			$trow['rating']        = $row->rating;
			$trow['rating_date']   = $this->EE->ratings_helper->formatDate('%d-%M-%Y %g:%i %A', $row->rating_date);
			$trow['rating_author'] = $row->rating_author_screen_name ? $row->rating_author_screen_name : $this->guest_label;
			$trow['rating_status'] = $this->rating_status[$row->rating_status];

			$trow['entry_title']   = $row->title;
			$trow['entry_channel'] = $row->channel_title;
			$trow['entry_url_title']   = $row->url_title;

			// Add to data
			$data['aaData'][] = $trow;
		}

		//print_r($this->EE->db->queries);

		exit( $this->EE->ratings_helper->generate_json($data) );
	}

	// ********************************************************************************* //

	public function datatable_comment_review()
	{
		$this->EE->db->save_queries = TRUE;

		// What Rating Type
		$rating_type = $this->TYPES['comment_review'];

		//----------------------------------------
		// Prepare Data Array
		//----------------------------------------
		$data = array();
		$data['aaData'] = array();
		$data['sEcho'] = $this->EE->input->get_post('sEcho');
		$data['iTotalRecords'] = $this->EE->ratings_model->total_unique_ratings($rating_type);

		//----------------------------------------
		// Select & Join
		//----------------------------------------
		$this->EE->db->select('ct.title, ct.url_title, ch.channel_title, cmt.comment, cr.rating, cr.rating_status, cr.rating_date, cr.rating_author_id, cr.rating_id, mb.screen_name AS rating_author_screen_name');
		$this->EE->db->from('exp_channel_ratings cr');
		$this->EE->db->join('exp_comments cmt', 'cmt.comment_id = cr.item_id', 'left');
		$this->EE->db->join('exp_members mb', 'mb.member_id = cr.rating_author_id', 'left');
		$this->EE->db->join('exp_channel_titles ct', 'ct.entry_id = cr.entry_id', 'left');
		$this->EE->db->join('exp_channels ch', 'ch.channel_id = cr.channel_id', 'left');

		//----------------------------------------
		// WHERE/LIKE
		//----------------------------------------
		$this->EE->db->where('cr.site_id', $this->site_id);
		$this->EE->db->where('cr.collection_id', $this->current_collection);
		$this->EE->db->where('cr.rating_type', $rating_type);
		$this->EE->db->where('cr.field_id', 0);
		if ($this->EE->input->post('date_from') != FALSE) $this->EE->db->where('cr.rating_date >', strtotime($_POST['date_from'] . ' 00:01:00'));
		if ($this->EE->input->post('date_to') != FALSE) $this->EE->db->where('cr.rating_date <', strtotime($_POST['date_to'] . ' 23:59:00'));

		if ($this->EE->input->post('entry_title') != FALSE) $this->EE->db->like('ct.title', $this->EE->input->post('entry_title'), 'both');

		if (isset($_POST['channels']) == TRUE && empty($_POST['channels']) == FALSE)
		{
			$this->EE->db->where_in('cr.channel_id', $_POST['channels']);
		}

		//----------------------------------------
		// Sort By
		//----------------------------------------
		$sort_cols = $this->EE->input->get_post('iSortingCols');

		for ($i = 0; $i < $sort_cols; $i++)
		{
			$col = $this->EE->input->get_post('iSortCol_'.$i);
			$sort =  $this->EE->input->get_post('sSortDir_'.$i);

			// Translate to column name
			$col = $this->cols_inv[$col];

			switch ($col)
			{
				case 'entry_title':
					$this->EE->db->order_by('ct.title', $sort);
					break;
				case 'entry_url_title':
					$this->EE->db->order_by('ct.url_title', $sort);
					break;
				case 'entry_channel':
					$this->EE->db->order_by('ch.channel_title', $sort);
					break;
				case 'comment':
					$this->EE->db->order_by('cmt.comment', $sort);
					break;
				case 'rating':
					$this->EE->db->order_by('cr.rating', $sort);
					break;
				case 'rating_date':
					$this->EE->db->order_by('cr.rating_date', $sort);
					break;
				case 'rating_author':
					$this->EE->db->order_by('rating_author_screen_name', $sort);
					break;
				case 'rating_status':
					$this->EE->db->order_by('cr.rating_status', $sort);
					break;
				default:
					$this->EE->db->order_by('cr.rating_date', 'DESC');
					break;
			}

		}

		//----------------------------------------
		// Execute SQL
		//----------------------------------------
		$this->dt_limit_offset();
		$sql = $this->EE->db->_compile_select();
		$data['iTotalDisplayRecords'] = $this->get_display_amount($sql);
		$query = $this->EE->db->query($sql);

		//----------------------------------------
		// Loop Over all
		//----------------------------------------
		foreach ($query->result() as $row)
		{
			$trow = array();
			$trow['rating_id']     = "<a href='#' class='EditRating' data-rid='{$row->rating_id}'>{$row->rating_id}</a>";
			$trow['rating']        = $row->rating;
			$trow['rating_date']   = $this->EE->ratings_helper->formatDate('%d-%M-%Y %g:%i %A', $row->rating_date);
			$trow['rating_author'] = $row->rating_author_screen_name ? $row->rating_author_screen_name : $this->guest_label;
			$trow['rating_status'] = $this->rating_status[$row->rating_status];

			$trow['entry_title']   = $row->title;
			$trow['entry_channel'] = $row->channel_title;
			$trow['entry_url_title'] = $row->url_title;
			$trow['comment'] = $row->comment;

			// Add to data
			$data['aaData'][] = $trow;
		}

		//print_r($this->EE->db->queries);

		exit( $this->EE->ratings_helper->generate_json($data) );
	}

	// ********************************************************************************* //

	public function datatable_comment_entry()
	{
		$this->EE->db->save_queries = TRUE;

		// What Rating Type
		$rating_type = $this->TYPES['comment_entry'];

		//----------------------------------------
		// Prepare Data Array
		//----------------------------------------
		$data = array();
		$data['aaData'] = array();
		$data['sEcho'] = $this->EE->input->get_post('sEcho');
		$data['iTotalRecords'] = $this->EE->ratings_model->total_unique_ratings($rating_type);

		//----------------------------------------
		// Select & Join
		//----------------------------------------
		$this->EE->db->select('ct.title, ct.url_title, ch.channel_title, cmt.comment, cr.rating, cr.rating_status, cr.rating_date, cr.rating_author_id, cr.rating_id, mb.screen_name AS rating_author_screen_name');
		$this->EE->db->from('exp_channel_ratings cr');
		$this->EE->db->join('exp_comments cmt', 'cmt.comment_id = cr.item_id', 'left');
		$this->EE->db->join('exp_members mb', 'mb.member_id = cr.rating_author_id', 'left');
		$this->EE->db->join('exp_channel_titles ct', 'ct.entry_id = cr.entry_id', 'left');
		$this->EE->db->join('exp_channels ch', 'ch.channel_id = cr.channel_id', 'left');

		//----------------------------------------
		// WHERE/LIKE
		//----------------------------------------
		$this->EE->db->where('cr.site_id', $this->site_id);
		$this->EE->db->where('cr.collection_id', $this->current_collection);
		$this->EE->db->where('cr.rating_type', $rating_type);
		$this->EE->db->where('cr.field_id', 0);
		if ($this->EE->input->post('date_from') != FALSE) $this->EE->db->where('cr.rating_date >', strtotime($_POST['date_from'] . ' 00:01:00'));
		if ($this->EE->input->post('date_to') != FALSE) $this->EE->db->where('cr.rating_date <', strtotime($_POST['date_to'] . ' 23:59:00'));

		if ($this->EE->input->post('entry_title') != FALSE) $this->EE->db->like('ct.title', $this->EE->input->post('entry_title'), 'both');

		if (isset($_POST['channels']) == TRUE && empty($_POST['channels']) == FALSE)
		{
			$this->EE->db->where_in('cr.channel_id', $_POST['channels']);
		}

		//----------------------------------------
		// Sort By
		//----------------------------------------
		$sort_cols = $this->EE->input->get_post('iSortingCols');

		for ($i = 0; $i < $sort_cols; $i++)
		{
			$col = $this->EE->input->get_post('iSortCol_'.$i);
			$sort =  $this->EE->input->get_post('sSortDir_'.$i);

			// Translate to column name
			$col = $this->cols_inv[$col];

			switch ($col)
			{
				case 'entry_title':
					$this->EE->db->order_by('ct.title', $sort);
					break;
				case 'entry_url_title':
					$this->EE->db->order_by('ct.url_title', $sort);
					break;
				case 'entry_channel':
					$this->EE->db->order_by('ch.channel_title', $sort);
					break;
				case 'comment':
					$this->EE->db->order_by('cmt.comment', $sort);
					break;
				case 'rating':
					$this->EE->db->order_by('cr.rating', $sort);
					break;
				case 'rating_date':
					$this->EE->db->order_by('cr.rating_date', $sort);
					break;
				case 'rating_author':
					$this->EE->db->order_by('rating_author_screen_name', $sort);
					break;
				case 'rating_status':
					$this->EE->db->order_by('cr.rating_status', $sort);
					break;
				default:
					$this->EE->db->order_by('cr.rating_date', 'DESC');
					break;
			}

		}

		//----------------------------------------
		// Execute SQL
		//----------------------------------------
		$this->dt_limit_offset();
		$sql = $this->EE->db->_compile_select();
		$data['iTotalDisplayRecords'] = $this->get_display_amount($sql);
		$query = $this->EE->db->query($sql);

		//----------------------------------------
		// Loop Over all
		//----------------------------------------
		foreach ($query->result() as $row)
		{
			$trow = array();
			$trow['rating_id']     = "<a href='#' class='EditRating' data-rid='{$row->rating_id}'>{$row->rating_id}</a>";
			$trow['rating']        = $row->rating;
			$trow['rating_date']   = $this->EE->ratings_helper->formatDate('%d-%M-%Y %g:%i %A', $row->rating_date);
			$trow['rating_author'] = $row->rating_author_screen_name ? $row->rating_author_screen_name : $this->guest_label;
			$trow['rating_status'] = $this->rating_status[$row->rating_status];

			$trow['entry_title']   = $row->title;
			$trow['entry_channel'] = $row->channel_title;
			$trow['entry_url_title'] = $row->url_title;
			$trow['comment'] = $row->comment;

			// Add to data
			$data['aaData'][] = $trow;
		}

		//print_r($this->EE->db->queries);

		exit( $this->EE->ratings_helper->generate_json($data) );
	}

	// ********************************************************************************* //

	public function datatable_member()
	{
		$this->EE->db->save_queries = TRUE;

		// What Rating Type
		$rating_type = $this->TYPES['member'];

		//----------------------------------------
		// Prepare Data Array
		//----------------------------------------
		$data = array();
		$data['aaData'] = array();
		$data['sEcho'] = $this->EE->input->get_post('sEcho');
		$data['iTotalRecords'] = $this->EE->ratings_model->total_unique_ratings($rating_type);

		//----------------------------------------
		// Select & Join
		//----------------------------------------
		$this->EE->db->select('user.screen_name, user.username, user.email, mg.group_title, cr.rating, cr.rating_status, cr.rating_date, cr.rating_author_id, cr.rating_id, mb.screen_name AS rating_author_screen_name');
		$this->EE->db->from('exp_channel_ratings cr');
		$this->EE->db->join('exp_members mb', 'mb.member_id = cr.rating_author_id', 'left');
		$this->EE->db->join('exp_members user', 'user.member_id = cr.item_id', 'left');
		$this->EE->db->join('exp_member_groups mg', 'mg.group_id = user.group_id', 'left');

		//----------------------------------------
		// WHERE/LIKE
		//----------------------------------------
		$this->EE->db->where('cr.site_id', $this->site_id);
		$this->EE->db->where('cr.collection_id', $this->current_collection);
		$this->EE->db->where('cr.rating_type', $rating_type);
		$this->EE->db->where('cr.field_id', 0);
		if ($this->EE->input->post('date_from') != FALSE) $this->EE->db->where('cr.rating_date >', strtotime($_POST['date_from'] . ' 00:01:00'));
		if ($this->EE->input->post('date_to') != FALSE) $this->EE->db->where('cr.rating_date <', strtotime($_POST['date_to'] . ' 23:59:00'));
		if ($this->EE->input->post('username') != FALSE) $this->EE->db->like('user.username', $_POST['username'], 'both');
		if ($this->EE->input->post('screen_name') != FALSE) $this->EE->db->like('user.screen_name', $_POST['screen_name'], 'both');
		if ($this->EE->input->post('email') != FALSE) $this->EE->db->like('user.email', $_POST['email'], 'both');

		//----------------------------------------
		// Sort By
		//----------------------------------------
		$sort_cols = $this->EE->input->get_post('iSortingCols');

		for ($i = 0; $i < $sort_cols; $i++)
		{
			$col = $this->EE->input->get_post('iSortCol_'.$i);
			$sort =  $this->EE->input->get_post('sSortDir_'.$i);

			// Translate to column name
			$col = $this->cols_inv[$col];

			switch ($col)
			{
				case 'username':
					$this->EE->db->order_by('user.username', $sort);
					break;
				case 'screen_name':
					$this->EE->db->order_by('user.screen_name', $sort);
					break;
				case 'email':
					$this->EE->db->order_by('user.email', $sort);
					break;
				case 'member_group':
					$this->EE->db->order_by('mg.group_title', $sort);
					break;
				case 'rating':
					$this->EE->db->order_by('cr.rating', $sort);
					break;
				case 'rating_date':
					$this->EE->db->order_by('cr.rating_date', $sort);
					break;
				case 'rating_author':
					$this->EE->db->order_by('rating_author_screen_name', $sort);
					break;
				case 'rating_status':
					$this->EE->db->order_by('cr.rating_status', $sort);
					break;
				default:
					$this->EE->db->order_by('cr.rating_date', 'DESC');
					break;
			}

		}

		//----------------------------------------
		// Execute SQL
		//----------------------------------------
		$this->dt_limit_offset();
		$sql = $this->EE->db->_compile_select();
		$data['iTotalDisplayRecords'] = $this->get_display_amount($sql);
		$query = $this->EE->db->query($sql);

		//----------------------------------------
		// Loop Over all
		//----------------------------------------
		foreach ($query->result() as $row)
		{
			$trow = array();
			$trow['rating_id']     = "<a href='#' class='EditRating' data-rid='{$row->rating_id}'>{$row->rating_id}</a>";
			$trow['rating']        = $row->rating;
			$trow['rating_date']   = $this->EE->ratings_helper->formatDate('%d-%M-%Y %g:%i %A', $row->rating_date);
			$trow['rating_author'] = $row->rating_author_screen_name ? $row->rating_author_screen_name : $this->guest_label;
			$trow['rating_status'] = $this->rating_status[$row->rating_status];

			$trow['username']     = $row->username;
			$trow['screen_name']  = $row->screen_name;
			$trow['email']        = $row->email;
			$trow['member_group'] = $row->group_title;

			// Add to data
			$data['aaData'][] = $trow;
		}

		//print_r($this->EE->db->queries);

		exit( $this->EE->ratings_helper->generate_json($data) );
	}

	// ********************************************************************************* //

	public function datatable_channel_images()
	{
		$this->EE->db->save_queries = TRUE;

		// What Rating Type
		$rating_type = $this->TYPES['channel_images'];

		//----------------------------------------
		// Prepare Data Array
		//----------------------------------------
		$data = array();
		$data['aaData'] = array();
		$data['sEcho'] = $this->EE->input->get_post('sEcho');
		$data['iTotalRecords'] = $this->EE->ratings_model->total_unique_ratings($rating_type);

		//----------------------------------------
		// Real Query
		//----------------------------------------
		$this->EE->db->select('ct.title, ct.url_title, ch.channel_title, ci.title AS image_title, ci.filename, cr.rating, cr.rating_status, cr.rating_date, cr.rating_author_id, cr.rating_id, mb.screen_name AS rating_author_screen_name');
		$this->EE->db->from('exp_channel_ratings cr');
		$this->EE->db->join('exp_channel_images ci', 'ci.image_id = cr.item_id', 'left');
		$this->EE->db->join('exp_members mb', 'mb.member_id = cr.rating_author_id', 'left');
		$this->EE->db->join('exp_channel_titles ct', 'ct.entry_id = cr.entry_id', 'left');
		$this->EE->db->join('exp_channels ch', 'ch.channel_id = cr.channel_id', 'left');

		//----------------------------------------
		// WHERE/LIKE
		//----------------------------------------
		$this->EE->db->where('cr.site_id', $this->site_id);
		$this->EE->db->where('cr.collection_id', $this->current_collection);
		$this->EE->db->where('cr.rating_type', $rating_type);
		$this->EE->db->where('cr.field_id', 0);
		if ($this->EE->input->post('date_from') != FALSE) $this->EE->db->where('cr.rating_date >', strtotime($_POST['date_from'] . ' 00:01:00'));
		if ($this->EE->input->post('date_to') != FALSE) $this->EE->db->where('cr.rating_date <', strtotime($_POST['date_to'] . ' 23:59:00'));
		if ($this->EE->input->post('entry_title') != FALSE) $this->EE->db->like('ct.title', $_POST['entry_title'], 'both');
		if ($this->EE->input->post('image_title') != FALSE) $this->EE->db->like('ci.title', $_POST['image_title'], 'both');
		if ($this->EE->input->post('filename') != FALSE) $this->EE->db->like('ci.filename', $_POST['filename'], 'both');

		if (isset($_POST['channels']) == TRUE && empty($_POST['channels']) == FALSE)
		{
			$this->EE->db->where_in('cr.channel_id', $_POST['channels']);
		}

		//----------------------------------------
		// Sort By
		//----------------------------------------
		$sort_cols = $this->EE->input->get_post('iSortingCols');

		for ($i = 0; $i < $sort_cols; $i++)
		{
			$col = $this->EE->input->get_post('iSortCol_'.$i);
			$sort =  $this->EE->input->get_post('sSortDir_'.$i);

			// Translate to column name
			$col = $this->cols_inv[$col];

			switch ($col)
			{
				case 'entry_title':
					$this->EE->db->order_by('ct.title', $sort);
					break;
				case 'entry_url_title':
					$this->EE->db->order_by('ct.url_title', $sort);
					break;
				case 'entry_channel':
					$this->EE->db->order_by('ch.channel_title', $sort);
					break;
				case 'filename':
					$this->EE->db->order_by('ci.filename', $sort);
					break;
				case 'image_title':
					$this->EE->db->order_by('ci.title', $sort);
					break;
				case 'rating':
					$this->EE->db->order_by('cr.rating', $sort);
					break;
				case 'rating_date':
					$this->EE->db->order_by('cr.rating_date', $sort);
					break;
				case 'rating_author':
					$this->EE->db->order_by('rating_author_screen_name', $sort);
					break;
				case 'rating_status':
					$this->EE->db->order_by('cr.rating_status', $sort);
					break;
				default:
					$this->EE->db->order_by('cr.rating_date', 'DESC');
					break;
			}

		}

		//----------------------------------------
		// Execute SQL
		//----------------------------------------
		$this->dt_limit_offset();
		$sql = $this->EE->db->_compile_select();
		$data['iTotalDisplayRecords'] = $this->get_display_amount($sql);
		$query = $this->EE->db->query($sql);

		//----------------------------------------
		// Loop Over all
		//----------------------------------------
		foreach ($query->result() as $row)
		{
			$trow = array();
			$trow['rating_id']     = "<a href='#' class='EditRating' data-rid='{$row->rating_id}'>{$row->rating_id}</a>";
			$trow['rating']        = $row->rating;
			$trow['rating_date']   = $this->EE->ratings_helper->formatDate('%d-%M-%Y %g:%i %A', $row->rating_date);
			$trow['rating_author'] = $row->rating_author_screen_name ? $row->rating_author_screen_name : $this->guest_label;
			$trow['rating_status'] = $this->rating_status[$row->rating_status];

			$trow['filename']   = $row->filename;
			$trow['image_title']   = $row->image_title;
			$trow['entry_title']   = $row->title;
			$trow['entry_channel'] = $row->channel_title;
			$trow['entry_url_title'] = $row->url_title;

			// Add to data
			$data['aaData'][] = $trow;
		}

		//print_r($this->EE->db->queries);

		exit( $this->EE->ratings_helper->generate_json($data) );
	}

	// ********************************************************************************* //

	public function datatable_channel_files()
	{
		$this->EE->db->save_queries = TRUE;

		// What Rating Type
		$rating_type = $this->TYPES['channel_files'];

		//----------------------------------------
		// Prepare Data Array
		//----------------------------------------
		$data = array();
		$data['aaData'] = array();
		$data['sEcho'] = $this->EE->input->get_post('sEcho');
		$data['iTotalRecords'] = $this->EE->ratings_model->total_unique_ratings($rating_type);

		//----------------------------------------
		// Real Query
		//----------------------------------------
		$this->EE->db->select('ct.title, ct.url_title, ch.channel_title, cf.title AS file_title, cf.filename, cr.rating, cr.rating_status, cr.rating_date, cr.rating_author_id, cr.rating_id, mb.screen_name AS rating_author_screen_name');
		$this->EE->db->from('exp_channel_ratings cr');
		$this->EE->db->join('exp_channel_files cf', 'cf.file_id = cr.item_id', 'left');
		$this->EE->db->join('exp_members mb', 'mb.member_id = cr.rating_author_id', 'left');
		$this->EE->db->join('exp_channel_titles ct', 'ct.entry_id = cr.entry_id', 'left');
		$this->EE->db->join('exp_channels ch', 'ch.channel_id = cr.channel_id', 'left');

		//----------------------------------------
		// WHERE/LIKE
		//----------------------------------------
		$this->EE->db->where('cr.site_id', $this->site_id);
		$this->EE->db->where('cr.collection_id', $this->current_collection);
		$this->EE->db->where('cr.rating_type', $rating_type);
		$this->EE->db->where('cr.field_id', 0);
		if ($this->EE->input->post('date_from') != FALSE) $this->EE->db->where('cr.rating_date >', strtotime($_POST['date_from'] . ' 00:01:00'));
		if ($this->EE->input->post('date_to') != FALSE) $this->EE->db->where('cr.rating_date <', strtotime($_POST['date_to'] . ' 23:59:00'));
		if ($this->EE->input->post('entry_title') != FALSE) $this->EE->db->like('ct.title', $_POST['entry_title'], 'both');
		if ($this->EE->input->post('file_title') != FALSE) $this->EE->db->like('cf.title', $_POST['file_title'], 'both');
		if ($this->EE->input->post('filename') != FALSE) $this->EE->db->like('cf.filename', $_POST['filename'], 'both');

		if (isset($_POST['channels']) == TRUE && empty($_POST['channels']) == FALSE)
		{
			$this->EE->db->where_in('cr.channel_id', $_POST['channels']);
		}

		//----------------------------------------
		// Sort By
		//----------------------------------------
		$sort_cols = $this->EE->input->get_post('iSortingCols');

		for ($i = 0; $i < $sort_cols; $i++)
		{
			$col = $this->EE->input->get_post('iSortCol_'.$i);
			$sort =  $this->EE->input->get_post('sSortDir_'.$i);

			// Translate to column name
			$col = $this->cols_inv[$col];

			switch ($col)
			{
				case 'entry_title':
					$this->EE->db->order_by('ct.title', $sort);
					break;
				case 'entry_url_title':
					$this->EE->db->order_by('ct.url_title', $sort);
					break;
				case 'entry_channel':
					$this->EE->db->order_by('ch.channel_title', $sort);
					break;
				case 'filename':
					$this->EE->db->order_by('cf.filename', $sort);
					break;
				case 'file_title':
					$this->EE->db->order_by('cf.title', $sort);
					break;
				case 'rating':
					$this->EE->db->order_by('cr.rating', $sort);
					break;
				case 'rating_date':
					$this->EE->db->order_by('cr.rating_date', $sort);
					break;
				case 'rating_author':
					$this->EE->db->order_by('rating_author_screen_name', $sort);
					break;
				case 'rating_status':
					$this->EE->db->order_by('cr.rating_status', $sort);
					break;
				default:
					$this->EE->db->order_by('cr.rating_date', 'DESC');
					break;
			}

		}

		//----------------------------------------
		// Execute SQL
		//----------------------------------------
		$this->dt_limit_offset();
		$sql = $this->EE->db->_compile_select();
		$data['iTotalDisplayRecords'] = $this->get_display_amount($sql);
		$query = $this->EE->db->query($sql);

		//----------------------------------------
		// Loop Over all
		//----------------------------------------
		foreach ($query->result() as $row)
		{
			$trow = array();
			$trow['rating_id']     = "<a href='#' class='EditRating' data-rid='{$row->rating_id}'>{$row->rating_id}</a>";
			$trow['rating']        = $row->rating;
			$trow['rating_date']   = $this->EE->ratings_helper->formatDate('%d-%M-%Y %g:%i %A', $row->rating_date);
			$trow['rating_author'] = $row->rating_author_screen_name ? $row->rating_author_screen_name : $this->guest_label;
			$trow['rating_status'] = $this->rating_status[$row->rating_status];

			$trow['filename']   = $row->filename;
			$trow['file_title']   = $row->file_title;
			$trow['entry_title']   = $row->title;
			$trow['entry_channel'] = $row->channel_title;
			$trow['entry_url_title'] = $row->url_title;

			// Add to data
			$data['aaData'][] = $trow;
		}

		//print_r($this->EE->db->queries);

		exit( $this->EE->ratings_helper->generate_json($data) );
	}

	// ********************************************************************************* //

	public function datatable_channel_videos()
	{
		$this->EE->db->save_queries = TRUE;

		// What Rating Type
		$rating_type = $this->TYPES['channel_videos'];

		//----------------------------------------
		// Prepare Data Array
		//----------------------------------------
		$data = array();
		$data['aaData'] = array();
		$data['sEcho'] = $this->EE->input->get_post('sEcho');
		$data['iTotalRecords'] = $this->EE->ratings_model->total_unique_ratings($rating_type);

		//----------------------------------------
		// Real Query
		//----------------------------------------
		$this->EE->db->select('ct.title, ct.url_title, ch.channel_title, cv.video_title, cv.service, cr.rating, cr.rating_status, cr.rating_date, cr.rating_author_id, cr.rating_id, mb.screen_name AS rating_author_screen_name');
		$this->EE->db->from('exp_channel_ratings cr');
		$this->EE->db->join('exp_channel_videos cv', 'cv.video_id = cr.item_id', 'left');
		$this->EE->db->join('exp_members mb', 'mb.member_id = cr.rating_author_id', 'left');
		$this->EE->db->join('exp_channel_titles ct', 'ct.entry_id = cr.entry_id', 'left');
		$this->EE->db->join('exp_channels ch', 'ch.channel_id = cr.channel_id', 'left');

		//----------------------------------------
		// WHERE/LIKE
		//----------------------------------------
		$this->EE->db->where('cr.site_id', $this->site_id);
		$this->EE->db->where('cr.collection_id', $this->current_collection);
		$this->EE->db->where('cr.rating_type', $rating_type);
		$this->EE->db->where('cr.field_id', 0);
		if ($this->EE->input->post('date_from') != FALSE) $this->EE->db->where('cr.rating_date >', strtotime($_POST['date_from'] . ' 00:01:00'));
		if ($this->EE->input->post('date_to') != FALSE) $this->EE->db->where('cr.rating_date <', strtotime($_POST['date_to'] . ' 23:59:00'));
		if ($this->EE->input->post('entry_title') != FALSE) $this->EE->db->like('ct.title', $_POST['entry_title'], 'both');
		if ($this->EE->input->post('video_title') != FALSE) $this->EE->db->like('cv.video_title', $_POST['video_title'], 'both');

		if (isset($_POST['channels']) == TRUE && empty($_POST['channels']) == FALSE)
		{
			$this->EE->db->where_in('cr.channel_id', $_POST['channels']);
		}

		if (isset($_POST['video_service']) == TRUE && empty($_POST['video_service']) == FALSE)
		{
			$this->EE->db->where_in('cv.service', $_POST['video_service']);
		}

		//----------------------------------------
		// Sort By
		//----------------------------------------
		$sort_cols = $this->EE->input->get_post('iSortingCols');

		for ($i = 0; $i < $sort_cols; $i++)
		{
			$col = $this->EE->input->get_post('iSortCol_'.$i);
			$sort =  $this->EE->input->get_post('sSortDir_'.$i);

			// Translate to column name
			$col = $this->cols_inv[$col];

			switch ($col)
			{
				case 'entry_title':
					$this->EE->db->order_by('ct.title', $sort);
					break;
				case 'entry_url_title':
					$this->EE->db->order_by('ct.url_title', $sort);
					break;
				case 'entry_channel':
					$this->EE->db->order_by('ch.channel_title', $sort);
					break;
				case 'service':
					$this->EE->db->order_by('cv.service', $sort);
					break;
				case 'video_title':
					$this->EE->db->order_by('cv.video_title', $sort);
					break;
				case 'rating':
					$this->EE->db->order_by('cr.rating', $sort);
					break;
				case 'rating_date':
					$this->EE->db->order_by('cr.rating_date', $sort);
					break;
				case 'rating_author':
					$this->EE->db->order_by('rating_author_screen_name', $sort);
					break;
				case 'rating_status':
					$this->EE->db->order_by('cr.rating_status', $sort);
					break;
				default:
					$this->EE->db->order_by('cr.rating_date', 'DESC');
					break;
			}

		}

		//----------------------------------------
		// Execute SQL
		//----------------------------------------
		$this->dt_limit_offset();
		$sql = $this->EE->db->_compile_select();
		$data['iTotalDisplayRecords'] = $this->get_display_amount($sql);
		$query = $this->EE->db->query($sql);

		//----------------------------------------
		// Loop Over all
		//----------------------------------------
		foreach ($query->result() as $row)
		{
			$trow = array();
			$trow['rating_id']     = "<a href='#' class='EditRating' data-rid='{$row->rating_id}'>{$row->rating_id}</a>";
			$trow['rating']        = $row->rating;
			$trow['rating_date']   = $this->EE->ratings_helper->formatDate('%d-%M-%Y %g:%i %A', $row->rating_date);
			$trow['rating_author'] = $row->rating_author_screen_name ? $row->rating_author_screen_name : $this->guest_label;
			$trow['rating_status'] = $this->rating_status[$row->rating_status];

			$trow['video_service']   = ucfirst($row->service);
			$trow['video_title']   = $row->video_title;
			$trow['entry_title']   = $row->title;
			$trow['entry_channel'] = $row->channel_title;
			$trow['entry_url_title'] = $row->url_title;

			// Add to data
			$data['aaData'][] = $trow;
		}

		//print_r($this->EE->db->queries);

		exit( $this->EE->ratings_helper->generate_json($data) );
	}

	// ********************************************************************************* //

	public function datatable_br_product()
	{
		$this->EE->db->save_queries = TRUE;

		// What Rating Type
		$rating_type = $this->TYPES['br_product'];

		//----------------------------------------
		// Prepare Data Array
		//----------------------------------------
		$data = array();
		$data['aaData'] = array();
		$data['sEcho'] = $this->EE->input->get_post('sEcho');
		$data['iTotalRecords'] = $this->EE->ratings_model->total_unique_ratings($rating_type);

		//----------------------------------------
		// Real Query
		//----------------------------------------
		$this->EE->db->select('bp.title, bp.sku, cr.rating, cr.rating_status, cr.rating_date, cr.rating_author_id, cr.rating_id, mb.screen_name AS rating_author_screen_name');
		$this->EE->db->from('exp_channel_ratings cr');
		$this->EE->db->join('exp_br_product bp', 'bp.product_id = cr.item_id', 'left');
		$this->EE->db->join('exp_members mb', 'mb.member_id = cr.rating_author_id', 'left');

		//----------------------------------------
		// WHERE/LIKE
		//----------------------------------------
		$this->EE->db->where('cr.site_id', $this->site_id);
		$this->EE->db->where('cr.collection_id', $this->current_collection);
		$this->EE->db->where('cr.rating_type', $rating_type);
		$this->EE->db->where('cr.field_id', 0);
		if ($this->EE->input->post('date_from') != FALSE) $this->EE->db->where('cr.rating_date >', strtotime($_POST['date_from'] . ' 00:01:00'));
		if ($this->EE->input->post('date_to') != FALSE) $this->EE->db->where('cr.rating_date <', strtotime($_POST['date_to'] . ' 23:59:00'));
		if ($this->EE->input->post('product') != FALSE) $this->EE->db->like('bp.title', $_POST['product'], 'both');
		if ($this->EE->input->post('sku') != FALSE) $this->EE->db->like('bp.sku', $_POST['sku'], 'both');

		//----------------------------------------
		// Sort By
		//----------------------------------------
		$sort_cols = $this->EE->input->get_post('iSortingCols');

		for ($i = 0; $i < $sort_cols; $i++)
		{
			$col = $this->EE->input->get_post('iSortCol_'.$i);
			$sort =  $this->EE->input->get_post('sSortDir_'.$i);

			// Translate to column name
			$col = $this->cols_inv[$col];

			switch ($col)
			{
				case 'product':
					$this->EE->db->order_by('bp.title', $sort);
					break;
				case 'sku':
					$this->EE->db->order_by('bp.sku', $sort);
					break;
				case 'rating':
					$this->EE->db->order_by('cr.rating', $sort);
					break;
				case 'rating_date':
					$this->EE->db->order_by('cr.rating_date', $sort);
					break;
				case 'rating_author':
					$this->EE->db->order_by('rating_author_screen_name', $sort);
					break;
				case 'rating_status':
					$this->EE->db->order_by('cr.rating_status', $sort);
					break;
				default:
					$this->EE->db->order_by('cr.rating_date', 'DESC');
					break;
			}

		}

		//----------------------------------------
		// Execute SQL
		//----------------------------------------
		$this->dt_limit_offset();
		$sql = $this->EE->db->_compile_select();
		$data['iTotalDisplayRecords'] = $this->get_display_amount($sql);
		$query = $this->EE->db->query($sql);

		//----------------------------------------
		// Loop Over all
		//----------------------------------------
		foreach ($query->result() as $row)
		{
			$trow = array();
			$trow['rating_id']     = "<a href='#' class='EditRating' data-rid='{$row->rating_id}'>{$row->rating_id}</a>";
			$trow['rating']        = $row->rating;
			$trow['rating_date']   = $this->EE->ratings_helper->formatDate('%d-%M-%Y %g:%i %A', $row->rating_date);
			$trow['rating_author'] = $row->rating_author_screen_name ? $row->rating_author_screen_name : $this->guest_label;
			$trow['rating_status'] = $this->rating_status[$row->rating_status];

			$trow['product'] = $row->title;
			$trow['sku']     = $row->sku;

			// Add to data
			$data['aaData'][] = $trow;
		}

		//print_r($this->EE->db->queries);

		exit( $this->EE->ratings_helper->generate_json($data) );
	}

	// ********************************************************************************* //






	public function datatable_likes_entry()
	{
		$this->EE->db->save_queries = TRUE;

		// What Like Type
		$like_type = $this->TYPES['entry'];

		//----------------------------------------
		// Prepare Data Array
		//----------------------------------------
		$data = array();
		$data['aaData'] = array();
		$data['sEcho'] = $this->EE->input->get_post('sEcho');
		$data['iTotalRecords'] = $this->EE->ratings_model->total_unique_likes($like_type);

		//----------------------------------------
		// Select & Join
		//----------------------------------------
		$this->EE->db->select('ct.title, ct.url_title, ch.channel_title, cr.liked, cr.disliked, cr.like_date, cr.like_author_id, cr.rlike_id, mb.screen_name AS like_author_screen_name');
		$this->EE->db->from('exp_channel_ratings_likes cr');
		$this->EE->db->join('exp_members mb', 'mb.member_id = cr.like_author_id', 'left');
		$this->EE->db->join('exp_channel_titles ct', 'ct.entry_id = cr.entry_id', 'left');
		$this->EE->db->join('exp_channels ch', 'ch.channel_id = cr.channel_id', 'left');

		//----------------------------------------
		// WHERE/LIKE
		//----------------------------------------
		$this->EE->db->where('cr.site_id', $this->site_id);
		$this->EE->db->where('cr.collection_id', $this->current_collection);
		$this->EE->db->where('cr.like_type', $like_type);
		$this->EE->db->where('cr.stats_row', 0);
		if ($this->EE->input->post('date_from') != FALSE) $this->EE->db->where('cr.like_date >', strtotime($_POST['date_from'] . ' 00:01:00'));
		if ($this->EE->input->post('date_to') != FALSE) $this->EE->db->where('cr.like_date <', strtotime($_POST['date_to'] . ' 23:59:00'));

		if ($this->EE->input->post('entry_title') != FALSE) $this->EE->db->like('ct.title', $this->EE->input->post('entry_title'), 'both');

		if (isset($_POST['channels']) == TRUE && empty($_POST['channels']) == FALSE)
		{
			$this->EE->db->where_in('cr.channel_id', $_POST['channels']);
		}

		//----------------------------------------
		// Sort By
		//----------------------------------------
		$sort_cols = $this->EE->input->get_post('iSortingCols');

		for ($i = 0; $i < $sort_cols; $i++)
		{
			$col = $this->EE->input->get_post('iSortCol_'.$i);
			$sort =  $this->EE->input->get_post('sSortDir_'.$i);

			// Translate to column name
			$col = $this->cols_inv[$col];

			switch ($col)
			{
				case 'entry_title':
					$this->EE->db->order_by('ct.title', $sort);
					break;
				case 'entry_url_title':
					$this->EE->db->order_by('ct.url_title', $sort);
					break;
				case 'entry_channel':
					$this->EE->db->order_by('ch.channel_title', $sort);
					break;
				case 'like':
					if ($sort == 'asc') $this->EE->db->order_by('cr.liked', 'DESC');
					if ($sort == 'desc') $this->EE->db->order_by('cr.disliked', 'DESC');
					break;
				case 'like_date':
					$this->EE->db->order_by('cr.like_date', $sort);
					break;
				case 'like_author':
					$this->EE->db->order_by('like_author_screen_name', $sort);
					break;
				default:
					$this->EE->db->order_by('cr.like_date', 'DESC');
					break;
			}

		}

		//----------------------------------------
		// Execute SQL
		//----------------------------------------
		$this->dt_limit_offset();
		$sql = $this->EE->db->_compile_select();
		$data['iTotalDisplayRecords'] = $this->get_display_amount($sql);
		$query = $this->EE->db->query($sql);

		//----------------------------------------
		// Loop Over all
		//----------------------------------------
		foreach ($query->result() as $row)
		{
			$trow = array();
			$trow['rating_id']     = $row->rlike_id;
			$trow['vote']      = ($row->liked == 0) ? $this->like_label[0] : $this->like_label[1];
			$trow['like_date']   = $this->EE->ratings_helper->formatDate('%d-%M-%Y %g:%i %A', $row->like_date);
			$trow['like_author'] = $row->like_author_screen_name ? $row->like_author_screen_name : $this->guest_label;

			$trow['entry_title']   = $row->title;
			$trow['entry_channel'] = $row->channel_title;
			$trow['entry_url_title']   = $row->url_title;

			// Add to data
			$data['aaData'][] = $trow;
		}

		//print_r($this->EE->db->queries);

		exit( $this->EE->ratings_helper->generate_json($data) );
	}

	// ********************************************************************************* //

	public function datatable_likes_comment_review()
	{
		$this->EE->db->save_queries = TRUE;

		// What Like Type
		$like_type = $this->TYPES['comment_review'];

		//----------------------------------------
		// Prepare Data Array
		//----------------------------------------
		$data = array();
		$data['aaData'] = array();
		$data['sEcho'] = $this->EE->input->get_post('sEcho');
		$data['iTotalRecords'] = $this->EE->ratings_model->total_unique_likes($like_type);

		//----------------------------------------
		// Select & Join
		//----------------------------------------
		$this->EE->db->select('ct.title, ct.url_title, ch.channel_title, cmt.comment, cr.liked, cr.disliked, cr.like_date, cr.like_author_id, cr.rlike_id, mb.screen_name AS like_author_screen_name');
		$this->EE->db->from('exp_channel_ratings_likes cr');
		$this->EE->db->join('exp_comments cmt', 'cmt.comment_id = cr.item_id', 'left');
		$this->EE->db->join('exp_members mb', 'mb.member_id = cr.like_author_id', 'left');
		$this->EE->db->join('exp_channel_titles ct', 'ct.entry_id = cr.entry_id', 'left');
		$this->EE->db->join('exp_channels ch', 'ch.channel_id = cr.channel_id', 'left');

		//----------------------------------------
		// WHERE/LIKE
		//----------------------------------------
		$this->EE->db->where('cr.site_id', $this->site_id);
		$this->EE->db->where('cr.collection_id', $this->current_collection);
		$this->EE->db->where('cr.like_type', $like_type);
		$this->EE->db->where('cr.stats_row', 0);
		if ($this->EE->input->post('date_from') != FALSE) $this->EE->db->where('cr.like_date >', strtotime($_POST['date_from'] . ' 00:01:00'));
		if ($this->EE->input->post('date_to') != FALSE) $this->EE->db->where('cr.like_date <', strtotime($_POST['date_to'] . ' 23:59:00'));

		if ($this->EE->input->post('entry_title') != FALSE) $this->EE->db->like('ct.title', $this->EE->input->post('entry_title'), 'both');
		if ($this->EE->input->post('comment') != FALSE) $this->EE->db->like('cmt.comment', $this->EE->input->post('comment'), 'both');

		if (isset($_POST['channels']) == TRUE && empty($_POST['channels']) == FALSE)
		{
			$this->EE->db->where_in('cr.channel_id', $_POST['channels']);
		}

		//----------------------------------------
		// Sort By
		//----------------------------------------
		$sort_cols = $this->EE->input->get_post('iSortingCols');

		for ($i = 0; $i < $sort_cols; $i++)
		{
			$col = $this->EE->input->get_post('iSortCol_'.$i);
			$sort =  $this->EE->input->get_post('sSortDir_'.$i);

			// Translate to column name
			$col = $this->cols_inv[$col];

			switch ($col)
			{
				case 'entry_title':
					$this->EE->db->order_by('ct.title', $sort);
					break;
				case 'entry_url_title':
					$this->EE->db->order_by('ct.url_title', $sort);
					break;
				case 'entry_channel':
					$this->EE->db->order_by('ch.channel_title', $sort);
					break;
				case 'comment':
					$this->EE->db->order_by('cmt.comment', $sort);
					break;
				case 'like':
					if ($sort == 'asc') $this->EE->db->order_by('cr.liked', 'DESC');
					if ($sort == 'desc') $this->EE->db->order_by('cr.disliked', 'DESC');
					break;
				case 'like_date':
					$this->EE->db->order_by('cr.like_date', $sort);
					break;
				case 'like_author':
					$this->EE->db->order_by('like_author_screen_name', $sort);
					break;
				default:
					$this->EE->db->order_by('cr.like_date', 'DESC');
					break;
			}

		}

		//----------------------------------------
		// Execute SQL
		//----------------------------------------
		$this->dt_limit_offset();
		$sql = $this->EE->db->_compile_select();
		$data['iTotalDisplayRecords'] = $this->get_display_amount($sql);
		$query = $this->EE->db->query($sql);

		//----------------------------------------
		// Loop Over all
		//----------------------------------------
		foreach ($query->result() as $row)
		{
			$trow = array();
			$trow['rating_id']     = $row->rlike_id;
			$trow['vote']      = ($row->liked == 0) ? $this->like_label[0] : $this->like_label[1];
			$trow['like_date']   = $this->EE->ratings_helper->formatDate('%d-%M-%Y %g:%i %A', $row->like_date);
			$trow['like_author'] = $row->like_author_screen_name ? $row->like_author_screen_name : $this->guest_label;

			$trow['entry_title']   = $row->title;
			$trow['entry_channel'] = $row->channel_title;
			$trow['entry_url_title'] = $row->url_title;
			$trow['comment'] = $row->comment;

			// Add to data
			$data['aaData'][] = $trow;
		}

		//print_r($this->EE->db->queries);

		exit( $this->EE->ratings_helper->generate_json($data) );
	}

	// ********************************************************************************* //

	public function datatable_likes_comment_entry()
	{
		$this->EE->db->save_queries = TRUE;

		// What Like Type
		$like_type = $this->TYPES['comment_entry'];

		//----------------------------------------
		// Prepare Data Array
		//----------------------------------------
		$data = array();
		$data['aaData'] = array();
		$data['sEcho'] = $this->EE->input->get_post('sEcho');
		$data['iTotalRecords'] = $this->EE->ratings_model->total_unique_likes($like_type);

		//----------------------------------------
		// Select & Join
		//----------------------------------------
		$this->EE->db->select('ct.title, ct.url_title, ch.channel_title, cmt.comment, cr.liked, cr.disliked, cr.like_date, cr.like_author_id, cr.rlike_id, mb.screen_name AS like_author_screen_name');
		$this->EE->db->from('exp_channel_ratings_likes cr');
		$this->EE->db->join('exp_comments cmt', 'cmt.comment_id = cr.item_id', 'left');
		$this->EE->db->join('exp_members mb', 'mb.member_id = cr.like_author_id', 'left');
		$this->EE->db->join('exp_channel_titles ct', 'ct.entry_id = cr.entry_id', 'left');
		$this->EE->db->join('exp_channels ch', 'ch.channel_id = cr.channel_id', 'left');

		//----------------------------------------
		// WHERE/LIKE
		//----------------------------------------
		$this->EE->db->where('cr.site_id', $this->site_id);
		$this->EE->db->where('cr.collection_id', $this->current_collection);
		$this->EE->db->where('cr.like_type', $like_type);
		$this->EE->db->where('cr.stats_row', 0);
		if ($this->EE->input->post('date_from') != FALSE) $this->EE->db->where('cr.like_date >', strtotime($_POST['date_from'] . ' 00:01:00'));
		if ($this->EE->input->post('date_to') != FALSE) $this->EE->db->where('cr.like_date <', strtotime($_POST['date_to'] . ' 23:59:00'));

		if ($this->EE->input->post('entry_title') != FALSE) $this->EE->db->like('ct.title', $this->EE->input->post('entry_title'), 'both');

		if (isset($_POST['channels']) == TRUE && empty($_POST['channels']) == FALSE)
		{
			$this->EE->db->where_in('cr.channel_id', $_POST['channels']);
		}

		//----------------------------------------
		// Sort By
		//----------------------------------------
		$sort_cols = $this->EE->input->get_post('iSortingCols');

		for ($i = 0; $i < $sort_cols; $i++)
		{
			$col = $this->EE->input->get_post('iSortCol_'.$i);
			$sort =  $this->EE->input->get_post('sSortDir_'.$i);

			// Translate to column name
			$col = $this->cols_inv[$col];

			switch ($col)
			{
				case 'entry_title':
					$this->EE->db->order_by('ct.title', $sort);
					break;
				case 'entry_url_title':
					$this->EE->db->order_by('ct.url_title', $sort);
					break;
				case 'entry_channel':
					$this->EE->db->order_by('ch.channel_title', $sort);
					break;
				case 'comment':
					$this->EE->db->order_by('cmt.comment', $sort);
					break;
				case 'like':
					if ($sort == 'asc') $this->EE->db->order_by('cr.liked', 'DESC');
					if ($sort == 'desc') $this->EE->db->order_by('cr.disliked', 'DESC');
					break;
				case 'like_date':
					$this->EE->db->order_by('cr.like_date', $sort);
					break;
				case 'like_author':
					$this->EE->db->order_by('like_author_screen_name', $sort);
					break;
				default:
					$this->EE->db->order_by('cr.like_date', 'DESC');
					break;
			}

		}

		//----------------------------------------
		// Execute SQL
		//----------------------------------------
		$this->dt_limit_offset();
		$sql = $this->EE->db->_compile_select();
		$data['iTotalDisplayRecords'] = $this->get_display_amount($sql);
		$query = $this->EE->db->query($sql);

		//----------------------------------------
		// Loop Over all
		//----------------------------------------
		foreach ($query->result() as $row)
		{
			$trow = array();
			$trow['rating_id']     = $row->rlike_id;
			$trow['vote']      = ($row->liked == 0) ? $this->like_label[0] : $this->like_label[1];
			$trow['like_date']   = $this->EE->ratings_helper->formatDate('%d-%M-%Y %g:%i %A', $row->like_date);
			$trow['like_author'] = $row->like_author_screen_name ? $row->like_author_screen_name : $this->guest_label;


			$trow['entry_title']   = $row->title;
			$trow['entry_channel'] = $row->channel_title;
			$trow['entry_url_title'] = $row->url_title;
			$trow['comment'] = $row->comment;

			// Add to data
			$data['aaData'][] = $trow;
		}

		//print_r($this->EE->db->queries);

		exit( $this->EE->ratings_helper->generate_json($data) );
	}

	// ********************************************************************************* //

	public function datatable_likes_member()
	{
		$this->EE->db->save_queries = TRUE;

		// What Like Type
		$like_type = $this->TYPES['member'];

		//----------------------------------------
		// Prepare Data Array
		//----------------------------------------
		$data = array();
		$data['aaData'] = array();
		$data['sEcho'] = $this->EE->input->get_post('sEcho');
		$data['iTotalRecords'] = $this->EE->ratings_model->total_unique_likes($like_type);

		//----------------------------------------
		// Select & Join
		//----------------------------------------
		$this->EE->db->select('user.screen_name, user.username, user.email, mg.group_title, cr.liked, cr.disliked, cr.like_date, cr.like_author_id, cr.rlike_id, mb.screen_name AS like_author_screen_name');
		$this->EE->db->from('exp_channel_ratings_likes cr');
		$this->EE->db->join('exp_members mb', 'mb.member_id = cr.like_author_id', 'left');
		$this->EE->db->join('exp_members user', 'user.member_id = cr.item_id', 'left');
		$this->EE->db->join('exp_member_groups mg', 'mg.group_id = user.group_id', 'left');

		//----------------------------------------
		// WHERE/LIKE
		//----------------------------------------
		$this->EE->db->where('cr.site_id', $this->site_id);
		$this->EE->db->where('cr.collection_id', $this->current_collection);
		$this->EE->db->where('cr.like_type', $like_type);
		$this->EE->db->where('cr.stats_row', 0);
		if ($this->EE->input->post('date_from') != FALSE) $this->EE->db->where('cr.like_date >', strtotime($_POST['date_from'] . ' 00:01:00'));
		if ($this->EE->input->post('date_to') != FALSE) $this->EE->db->where('cr.like_date <', strtotime($_POST['date_to'] . ' 23:59:00'));
		if ($this->EE->input->post('username') != FALSE) $this->EE->db->like('user.username', $_POST['username'], 'both');
		if ($this->EE->input->post('screen_name') != FALSE) $this->EE->db->like('user.screen_name', $_POST['screen_name'], 'both');
		if ($this->EE->input->post('email') != FALSE) $this->EE->db->like('user.email', $_POST['email'], 'both');

		//----------------------------------------
		// Sort By
		//----------------------------------------
		$sort_cols = $this->EE->input->get_post('iSortingCols');

		for ($i = 0; $i < $sort_cols; $i++)
		{
			$col = $this->EE->input->get_post('iSortCol_'.$i);
			$sort =  $this->EE->input->get_post('sSortDir_'.$i);

			// Translate to column name
			$col = $this->cols_inv[$col];

			switch ($col)
			{
				case 'username':
					$this->EE->db->order_by('user.username', $sort);
					break;
				case 'screen_name':
					$this->EE->db->order_by('user.screen_name', $sort);
					break;
				case 'email':
					$this->EE->db->order_by('user.email', $sort);
					break;
				case 'member_group':
					$this->EE->db->order_by('mg.group_title', $sort);
					break;
				case 'like':
					if ($sort == 'asc') $this->EE->db->order_by('cr.liked', 'DESC');
					if ($sort == 'desc') $this->EE->db->order_by('cr.disliked', 'DESC');
					break;
				case 'like_date':
					$this->EE->db->order_by('cr.like_date', $sort);
					break;
				case 'like_author':
					$this->EE->db->order_by('like_author_screen_name', $sort);
					break;
				default:
					$this->EE->db->order_by('cr.like_date', 'DESC');
					break;
			}

		}

		//----------------------------------------
		// Execute SQL
		//----------------------------------------
		$this->dt_limit_offset();
		$sql = $this->EE->db->_compile_select();
		$data['iTotalDisplayRecords'] = $this->get_display_amount($sql);
		$query = $this->EE->db->query($sql);

		//----------------------------------------
		// Loop Over all
		//----------------------------------------
		foreach ($query->result() as $row)
		{
			$trow = array();
			$trow['rating_id']     = $row->rlike_id;
			$trow['vote']      = ($row->liked == 0) ? $this->like_label[0] : $this->like_label[1];
			$trow['like_date']   = $this->EE->ratings_helper->formatDate('%d-%M-%Y %g:%i %A', $row->like_date);
			$trow['like_author'] = $row->like_author_screen_name ? $row->like_author_screen_name : $this->guest_label;

			$trow['username']     = $row->username;
			$trow['screen_name']  = $row->screen_name;
			$trow['email']        = $row->email;
			$trow['member_group'] = $row->group_title;

			// Add to data
			$data['aaData'][] = $trow;
		}

		//print_r($this->EE->db->queries);

		exit( $this->EE->ratings_helper->generate_json($data) );
	}

	// ********************************************************************************* //

	public function datatable_likes_channel_images()
	{
		$this->EE->db->save_queries = TRUE;

		// What Like Type
		$like_type = $this->TYPES['channel_images'];

		//----------------------------------------
		// Prepare Data Array
		//----------------------------------------
		$data = array();
		$data['aaData'] = array();
		$data['sEcho'] = $this->EE->input->get_post('sEcho');
		$data['iTotalRecords'] = $this->EE->ratings_model->total_unique_likes($like_type);

		//----------------------------------------
		// Real Query
		//----------------------------------------
		$this->EE->db->select('ct.title, ct.url_title, ch.channel_title, ci.title AS image_title, ci.filename, cr.liked, cr.disliked, cr.like_date, cr.like_author_id, cr.rlike_id, mb.screen_name AS like_author_screen_name');
		$this->EE->db->from('exp_channel_ratings_likes cr');
		$this->EE->db->join('exp_channel_images ci', 'ci.image_id = cr.item_id', 'left');
		$this->EE->db->join('exp_members mb', 'mb.member_id = cr.like_author_id', 'left');
		$this->EE->db->join('exp_channel_titles ct', 'ct.entry_id = cr.entry_id', 'left');
		$this->EE->db->join('exp_channels ch', 'ch.channel_id = cr.channel_id', 'left');

		//----------------------------------------
		// WHERE/LIKE
		//----------------------------------------
		$this->EE->db->where('cr.site_id', $this->site_id);
		$this->EE->db->where('cr.collection_id', $this->current_collection);
		$this->EE->db->where('cr.like_type', $like_type);
		$this->EE->db->where('cr.stats_row', 0);
		if ($this->EE->input->post('date_from') != FALSE) $this->EE->db->where('cr.like_date >', strtotime($_POST['date_from'] . ' 00:01:00'));
		if ($this->EE->input->post('date_to') != FALSE) $this->EE->db->where('cr.like_date <', strtotime($_POST['date_to'] . ' 23:59:00'));
		if ($this->EE->input->post('entry_title') != FALSE) $this->EE->db->like('ct.title', $_POST['entry_title'], 'both');
		if ($this->EE->input->post('image_title') != FALSE) $this->EE->db->like('ci.title', $_POST['image_title'], 'both');
		if ($this->EE->input->post('filename') != FALSE) $this->EE->db->like('ci.filename', $_POST['filename'], 'both');

		if (isset($_POST['channels']) == TRUE && empty($_POST['channels']) == FALSE)
		{
			$this->EE->db->where_in('cr.channel_id', $_POST['channels']);
		}

		//----------------------------------------
		// Sort By
		//----------------------------------------
		$sort_cols = $this->EE->input->get_post('iSortingCols');

		for ($i = 0; $i < $sort_cols; $i++)
		{
			$col = $this->EE->input->get_post('iSortCol_'.$i);
			$sort =  $this->EE->input->get_post('sSortDir_'.$i);

			// Translate to column name
			$col = $this->cols_inv[$col];

			switch ($col)
			{
				case 'entry_title':
					$this->EE->db->order_by('ct.title', $sort);
					break;
				case 'entry_url_title':
					$this->EE->db->order_by('ct.url_title', $sort);
					break;
				case 'entry_channel':
					$this->EE->db->order_by('ch.channel_title', $sort);
					break;
				case 'filename':
					$this->EE->db->order_by('ci.filename', $sort);
					break;
				case 'image_title':
					$this->EE->db->order_by('ci.title', $sort);
					break;
				case 'like':
					if ($sort == 'asc') $this->EE->db->order_by('cr.liked', 'DESC');
					if ($sort == 'desc') $this->EE->db->order_by('cr.disliked', 'DESC');
					break;
				case 'like_date':
					$this->EE->db->order_by('cr.like_date', $sort);
					break;
				case 'like_author':
					$this->EE->db->order_by('like_author_screen_name', $sort);
					break;
				default:
					$this->EE->db->order_by('cr.like_date', 'DESC');
					break;
			}

		}

		//----------------------------------------
		// Execute SQL
		//----------------------------------------
		$this->dt_limit_offset();
		$sql = $this->EE->db->_compile_select();
		$data['iTotalDisplayRecords'] = $this->get_display_amount($sql);
		$query = $this->EE->db->query($sql);

		//----------------------------------------
		// Loop Over all
		//----------------------------------------
		foreach ($query->result() as $row)
		{
			$trow = array();
			$trow['rating_id']     = $row->rlike_id;
			$trow['vote']      = ($row->liked == 0) ? $this->like_label[0] : $this->like_label[1];
			$trow['like_date']   = $this->EE->ratings_helper->formatDate('%d-%M-%Y %g:%i %A', $row->like_date);
			$trow['like_author'] = $row->like_author_screen_name ? $row->like_author_screen_name : $this->guest_label;

			$trow['filename']   = $row->filename;
			$trow['image_title']   = $row->image_title;
			$trow['entry_title']   = $row->title;
			$trow['entry_channel'] = $row->channel_title;
			$trow['entry_url_title'] = $row->url_title;

			// Add to data
			$data['aaData'][] = $trow;
		}

		//print_r($this->EE->db->queries);

		exit( $this->EE->ratings_helper->generate_json($data) );
	}

	// ********************************************************************************* //

	public function datatable_likes_channel_files()
	{
		$this->EE->db->save_queries = TRUE;

		// What Like Type
		$like_type = $this->TYPES['channel_files'];

		//----------------------------------------
		// Prepare Data Array
		//----------------------------------------
		$data = array();
		$data['aaData'] = array();
		$data['sEcho'] = $this->EE->input->get_post('sEcho');
		$data['iTotalRecords'] = $this->EE->ratings_model->total_unique_likes($like_type);

		//----------------------------------------
		// Real Query
		//----------------------------------------
		$this->EE->db->select('ct.title, ct.url_title, ch.channel_title, cf.title AS file_title, cf.filename, cr.liked, cr.disliked, cr.like_date, cr.like_author_id, cr.rlike_id, mb.screen_name AS like_author_screen_name');
		$this->EE->db->from('exp_channel_ratings_likes cr');
		$this->EE->db->join('exp_channel_files cf', 'cf.file_id = cr.item_id', 'left');
		$this->EE->db->join('exp_members mb', 'mb.member_id = cr.like_author_id', 'left');
		$this->EE->db->join('exp_channel_titles ct', 'ct.entry_id = cr.entry_id', 'left');
		$this->EE->db->join('exp_channels ch', 'ch.channel_id = cr.channel_id', 'left');

		//----------------------------------------
		// WHERE/LIKE
		//----------------------------------------
		$this->EE->db->where('cr.site_id', $this->site_id);
		$this->EE->db->where('cr.collection_id', $this->current_collection);
		$this->EE->db->where('cr.like_type', $like_type);
		$this->EE->db->where('cr.stats_row', 0);
		if ($this->EE->input->post('date_from') != FALSE) $this->EE->db->where('cr.like_date >', strtotime($_POST['date_from'] . ' 00:01:00'));
		if ($this->EE->input->post('date_to') != FALSE) $this->EE->db->where('cr.like_date <', strtotime($_POST['date_to'] . ' 23:59:00'));
		if ($this->EE->input->post('entry_title') != FALSE) $this->EE->db->like('ct.title', $_POST['entry_title'], 'both');
		if ($this->EE->input->post('file_title') != FALSE) $this->EE->db->like('cf.title', $_POST['file_title'], 'both');
		if ($this->EE->input->post('filename') != FALSE) $this->EE->db->like('cf.filename', $_POST['filename'], 'both');

		if (isset($_POST['channels']) == TRUE && empty($_POST['channels']) == FALSE)
		{
			$this->EE->db->where_in('cr.channel_id', $_POST['channels']);
		}

		//----------------------------------------
		// Sort By
		//----------------------------------------
		$sort_cols = $this->EE->input->get_post('iSortingCols');

		for ($i = 0; $i < $sort_cols; $i++)
		{
			$col = $this->EE->input->get_post('iSortCol_'.$i);
			$sort =  $this->EE->input->get_post('sSortDir_'.$i);

			// Translate to column name
			$col = $this->cols_inv[$col];

			switch ($col)
			{
				case 'entry_title':
					$this->EE->db->order_by('ct.title', $sort);
					break;
				case 'entry_url_title':
					$this->EE->db->order_by('ct.url_title', $sort);
					break;
				case 'entry_channel':
					$this->EE->db->order_by('ch.channel_title', $sort);
					break;
				case 'filename':
					$this->EE->db->order_by('cf.filename', $sort);
					break;
				case 'file_title':
					$this->EE->db->order_by('cf.title', $sort);
					break;
				case 'like':
					if ($sort == 'asc') $this->EE->db->order_by('cr.liked', 'DESC');
					if ($sort == 'desc') $this->EE->db->order_by('cr.disliked', 'DESC');
					break;
				case 'like_date':
					$this->EE->db->order_by('cr.like_date', $sort);
					break;
				case 'like_author':
					$this->EE->db->order_by('like_author_screen_name', $sort);
					break;
				default:
					$this->EE->db->order_by('cr.like_date', 'DESC');
					break;
			}

		}

		//----------------------------------------
		// Execute SQL
		//----------------------------------------
		$this->dt_limit_offset();
		$sql = $this->EE->db->_compile_select();
		$data['iTotalDisplayRecords'] = $this->get_display_amount($sql);
		$query = $this->EE->db->query($sql);

		//----------------------------------------
		// Loop Over all
		//----------------------------------------
		foreach ($query->result() as $row)
		{
			$trow = array();
			$trow['rating_id']     = $row->rlike_id;
			$trow['vote']      = ($row->liked == 0) ? $this->like_label[0] : $this->like_label[1];
			$trow['like_date']   = $this->EE->ratings_helper->formatDate('%d-%M-%Y %g:%i %A', $row->like_date);
			$trow['like_author'] = $row->like_author_screen_name ? $row->like_author_screen_name : $this->guest_label;

			$trow['filename']   = $row->filename;
			$trow['file_title']   = $row->file_title;
			$trow['entry_title']   = $row->title;
			$trow['entry_channel'] = $row->channel_title;
			$trow['entry_url_title'] = $row->url_title;

			// Add to data
			$data['aaData'][] = $trow;
		}

		//print_r($this->EE->db->queries);

		exit( $this->EE->ratings_helper->generate_json($data) );
	}

	// ********************************************************************************* //

	public function datatable_likes_channel_videos()
	{
		$this->EE->db->save_queries = TRUE;

		// What Like Type
		$like_type = $this->TYPES['channel_videos'];

		//----------------------------------------
		// Prepare Data Array
		//----------------------------------------
		$data = array();
		$data['aaData'] = array();
		$data['sEcho'] = $this->EE->input->get_post('sEcho');
		$data['iTotalRecords'] = $this->EE->ratings_model->total_unique_likes($like_type);

		//----------------------------------------
		// Real Query
		//----------------------------------------
		$this->EE->db->select('ct.title, ct.url_title, ch.channel_title, cv.video_title, cv.service, cr.liked, cr.disliked, cr.like_date, cr.like_author_id, cr.rlike_id, mb.screen_name AS like_author_screen_name');
		$this->EE->db->from('exp_channel_ratings_likes cr');
		$this->EE->db->join('exp_channel_videos cv', 'cv.video_id = cr.item_id', 'left');
		$this->EE->db->join('exp_members mb', 'mb.member_id = cr.like_author_id', 'left');
		$this->EE->db->join('exp_channel_titles ct', 'ct.entry_id = cr.entry_id', 'left');
		$this->EE->db->join('exp_channels ch', 'ch.channel_id = cr.channel_id', 'left');

		//----------------------------------------
		// WHERE/LIKE
		//----------------------------------------
		$this->EE->db->where('cr.site_id', $this->site_id);
		$this->EE->db->where('cr.collection_id', $this->current_collection);
		$this->EE->db->where('cr.like_type', $like_type);
		$this->EE->db->where('cr.stats_row', 0);
		if ($this->EE->input->post('date_from') != FALSE) $this->EE->db->where('cr.like_date >', strtotime($_POST['date_from'] . ' 00:01:00'));
		if ($this->EE->input->post('date_to') != FALSE) $this->EE->db->where('cr.like_date <', strtotime($_POST['date_to'] . ' 23:59:00'));
		if ($this->EE->input->post('entry_title') != FALSE) $this->EE->db->like('ct.title', $_POST['entry_title'], 'both');
		if ($this->EE->input->post('video_title') != FALSE) $this->EE->db->like('cv.video_title', $_POST['video_title'], 'both');

		if (isset($_POST['channels']) == TRUE && empty($_POST['channels']) == FALSE)
		{
			$this->EE->db->where_in('cr.channel_id', $_POST['channels']);
		}

		if (isset($_POST['video_service']) == TRUE && empty($_POST['video_service']) == FALSE)
		{
			$this->EE->db->where_in('cv.service', $_POST['video_service']);
		}

		//----------------------------------------
		// Sort By
		//----------------------------------------
		$sort_cols = $this->EE->input->get_post('iSortingCols');

		for ($i = 0; $i < $sort_cols; $i++)
		{
			$col = $this->EE->input->get_post('iSortCol_'.$i);
			$sort =  $this->EE->input->get_post('sSortDir_'.$i);

			// Translate to column name
			$col = $this->cols_inv[$col];

			switch ($col)
			{
				case 'entry_title':
					$this->EE->db->order_by('ct.title', $sort);
					break;
				case 'entry_url_title':
					$this->EE->db->order_by('ct.url_title', $sort);
					break;
				case 'entry_channel':
					$this->EE->db->order_by('ch.channel_title', $sort);
					break;
				case 'service':
					$this->EE->db->order_by('cv.service', $sort);
					break;
				case 'video_title':
					$this->EE->db->order_by('cv.video_title', $sort);
					break;
				case 'like':
					if ($sort == 'asc') $this->EE->db->order_by('cr.liked', 'DESC');
					if ($sort == 'desc') $this->EE->db->order_by('cr.disliked', 'DESC');
					break;
				case 'like_date':
					$this->EE->db->order_by('cr.like_date', $sort);
					break;
				case 'like_author':
					$this->EE->db->order_by('like_author_screen_name', $sort);
					break;
				default:
					$this->EE->db->order_by('cr.like_date', 'DESC');
					break;
			}

		}

		//----------------------------------------
		// Execute SQL
		//----------------------------------------
		$this->dt_limit_offset();
		$sql = $this->EE->db->_compile_select();
		$data['iTotalDisplayRecords'] = $this->get_display_amount($sql);
		$query = $this->EE->db->query($sql);

		//----------------------------------------
		// Loop Over all
		//----------------------------------------
		foreach ($query->result() as $row)
		{
			$trow = array();
			$trow['rating_id']     = $row->rlike_id;
			$trow['vote']      = ($row->liked == 0) ? $this->like_label[0] : $this->like_label[1];
			$trow['like_date']   = $this->EE->ratings_helper->formatDate('%d-%M-%Y %g:%i %A', $row->like_date);
			$trow['like_author'] = $row->like_author_screen_name ? $row->like_author_screen_name : $this->guest_label;

			$trow['video_service']   = ucfirst($row->service);
			$trow['video_title']   = $row->video_title;
			$trow['entry_title']   = $row->title;
			$trow['entry_channel'] = $row->channel_title;
			$trow['entry_url_title'] = $row->url_title;

			// Add to data
			$data['aaData'][] = $trow;
		}

		//print_r($this->EE->db->queries);

		exit( $this->EE->ratings_helper->generate_json($data) );
	}

	// ********************************************************************************* //

	public function datatable_likes_br_product()
	{
		$this->EE->db->save_queries = TRUE;

		// What Like Type
		$like_type = $this->TYPES['br_product'];

		//----------------------------------------
		// Prepare Data Array
		//----------------------------------------
		$data = array();
		$data['aaData'] = array();
		$data['sEcho'] = $this->EE->input->get_post('sEcho');
		$data['iTotalRecords'] = $this->EE->ratings_model->total_unique_likes($like_type);

		//----------------------------------------
		// Real Query
		//----------------------------------------
		$this->EE->db->select('bp.title, bp.sku, cr.liked, cr.disliked, cr.like_date, cr.like_author_id, cr.rlike_id, mb.screen_name AS like_author_screen_name');
		$this->EE->db->from('exp_channel_ratings_likes cr');
		$this->EE->db->join('exp_br_product bp', 'bp.product_id = cr.item_id', 'left');
		$this->EE->db->join('exp_members mb', 'mb.member_id = cr.like_author_id', 'left');

		//----------------------------------------
		// WHERE/LIKE
		//----------------------------------------
		$this->EE->db->where('cr.site_id', $this->site_id);
		$this->EE->db->where('cr.collection_id', $this->current_collection);
		$this->EE->db->where('cr.like_type', $like_type);
		$this->EE->db->where('cr.stats_row', 0);
		if ($this->EE->input->post('date_from') != FALSE) $this->EE->db->where('cr.like_date >', strtotime($_POST['date_from'] . ' 00:01:00'));
		if ($this->EE->input->post('date_to') != FALSE) $this->EE->db->where('cr.like_date <', strtotime($_POST['date_to'] . ' 23:59:00'));
		if ($this->EE->input->post('product') != FALSE) $this->EE->db->like('bp.title', $_POST['product'], 'both');
		if ($this->EE->input->post('sku') != FALSE) $this->EE->db->like('bp.sku', $_POST['sku'], 'both');

		//----------------------------------------
		// Sort By
		//----------------------------------------
		$sort_cols = $this->EE->input->get_post('iSortingCols');

		for ($i = 0; $i < $sort_cols; $i++)
		{
			$col = $this->EE->input->get_post('iSortCol_'.$i);
			$sort =  $this->EE->input->get_post('sSortDir_'.$i);

			// Translate to column name
			$col = $this->cols_inv[$col];

			switch ($col)
			{
				case 'product':
					$this->EE->db->order_by('bp.title', $sort);
					break;
				case 'sku':
					$this->EE->db->order_by('bp.sku', $sort);
					break;
				case 'like':
					if ($sort == 'asc') $this->EE->db->order_by('cr.liked', 'DESC');
					if ($sort == 'desc') $this->EE->db->order_by('cr.disliked', 'DESC');
					break;
				case 'like_date':
					$this->EE->db->order_by('cr.like_date', $sort);
					break;
				case 'like_author':
					$this->EE->db->order_by('like_author_screen_name', $sort);
					break;
				default:
					$this->EE->db->order_by('cr.like_date', 'DESC');
					break;
			}

		}

		//----------------------------------------
		// Execute SQL
		//----------------------------------------
		$this->dt_limit_offset();
		$sql = $this->EE->db->_compile_select();
		$data['iTotalDisplayRecords'] = $this->get_display_amount($sql);
		$query = $this->EE->db->query($sql);

		//----------------------------------------
		// Loop Over all
		//----------------------------------------
		foreach ($query->result() as $row)
		{
			$trow = array();
			$trow['rating_id']     = $row->rlike_id;
			$trow['vote']      = ($row->liked == 0) ? $this->like_label[0] : $this->like_label[1];
			$trow['like_date']   = $this->EE->ratings_helper->formatDate('%d-%M-%Y %g:%i %A', $row->like_date);
			$trow['like_author'] = $row->like_author_screen_name ? $row->like_author_screen_name : $this->guest_label;

			$trow['product'] = $row->title;
			$trow['sku']     = $row->sku;

			// Add to data
			$data['aaData'][] = $trow;
		}

		//print_r($this->EE->db->queries);

		exit( $this->EE->ratings_helper->generate_json($data) );
	}

	// ********************************************************************************* //

	private function dt_columns()
	{
		if (isset($_POST['iColumns']) == FALSE) return;

		$this->cols = array();
		$this->cols_inv = array();

		for ($i = 0; $i < $_POST['iColumns']; $i++)
		{
			$this->cols[ $_POST['mDataProp_'.$i] ] = $i;
		}

		$this->cols_inv = array_flip($this->cols);
	}

	// ********************************************************************************* //

	private function dt_limit_offset()
	{
		$limit = 15;
		if ($this->EE->input->post('iDisplayLength') !== FALSE)
		{
			$limit = $this->EE->input->post('iDisplayLength');
			if ($limit < 1) $limit = 999999;
		}

		$offset = 0;
		if ($this->EE->input->post('iDisplayStart') !== FALSE)
		{
			$offset = $this->EE->input->post('iDisplayStart');
		}

		$this->EE->db->limit($limit, $offset);
	}

	// ********************************************************************************* //

	private function get_display_amount($sql)
	{
		//----------------------------------------
		// Get total before real SELECT
		//----------------------------------------
		preg_match('/SELECT(.*)FROM/s', $sql, $temp);
		$quick_sql = str_replace($temp[1], ' COUNT(*) as total ', $sql);
		$quick_sql = preg_replace('/ORDER BY.*/s', '', $quick_sql);
		$quick_sql = preg_replace('/LIMIT.*/s', '', $quick_sql);
		$query = $this->EE->db->query($quick_sql);
		return $query->row('total');
	}

	// ********************************************************************************* //

} // END CLASS

/* End of file ratings_model.php  */
/* Location: ./system/expressionengine/third_party/channel_ratings/models/ratings_model.php */
