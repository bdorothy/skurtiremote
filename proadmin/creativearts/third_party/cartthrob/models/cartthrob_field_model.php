<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Cartthrob_field_model extends CI_Model
{
	protected $fields = array();
	protected $channels;
	protected $matrix_cols;
	protected $matrix_rows;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->load->helper('array');
		
		$site_id = $this->config->item('site_id'); 
		
		if ($this->config->item('cartthrob:msm_show_all'))
		{
			$site_id= "all";
		}
		$this->load_fields($site_id);
	}
	
	/**
	 * By default the model only loads channels/fields from the current site
	 * Use this to fetch the channel/field data from another site
	 * 
	 * @param mixed $site_id either the numeric site_id or the string "all"
	 * 
	 * @return void
	 */
	public function load_fields($site_id)
	{
		$fields = array();
		
		if ($site_id !== 'all')
		{
			$this->db->where('site_id', $site_id);
		}
		
		$query = $this->db->select('field_id, field_name, field_label, group_id, field_type, field_settings, field_fmt')
			->from('channel_fields')
			->order_by('group_id, field_order')
			->get();
		
		foreach ($query->result_array() as $row)
		{
			$fields[$row['field_id']] = $row;
		}
		
		$query->free_result();
		
		// cannot use array merge because it will reindex, and we don't want that
		$this->fields = array_diff_key($this->fields, $fields) + $fields;
	}
	
	public function channel_has_fieldtype($channel_id, $fieldtype, $return_field_id = FALSE)
	{
		return $this->group_has_fieldtype($this->get_field_group($channel_id), $fieldtype, $return_field_id);
	}
	
	public function group_has_fieldtype($group_id, $fieldtype, $return_field_id = FALSE)
	{
		$fields = $this->get_fields_by_group($group_id);
		$this->load->add_package_path(PATH_THIRD.'cartthrob'); 
		
		$this->load->library('data_filter');
		
		$this->data_filter->filter($fields, 'field_type', $fieldtype);
		
		if ($return_field_id === TRUE)
		{
			$field = current($fields);
			
			return ($field) ? $field['field_id'] : FALSE;
		}
		
		return (count($fields) > 0);
	}
	
	public function get_matrix_cols($field_id)
	{
		if ( ! $field_id)
		{
			return array();
		}
		
		$settings = $this->get_field_settings($field_id);
		
		if ( ! isset($this->matrix_cols[$field_id]))
		{
			$this->matrix_cols[$field_id] = (empty($settings['col_ids']))
							? array()
							: $this->db->where_in('col_id', $settings['col_ids'])
								->get('matrix_cols')
								->result_array();
		}
		
		return $this->matrix_cols[$field_id];
	}
	
	public function get_matrix_rows($entry_id, $field_id)
	{
		if ( ! $entry_id || ! $field_id)
		{
			return array();
		}
		
		if ( ! isset($this->matrix_rows[$entry_id][$field_id]))
		{
			$this->matrix_rows[$entry_id][$field_id] = $this->db->where('entry_id', $entry_id)
										->where('field_id', $field_id)
										->order_by('row_order')
										->get('matrix_data')
										->result_array();
		}
		
		return $this->matrix_rows[$entry_id][$field_id];
	}
	
	public function get_fields($params = array(), $limit = FALSE)
	{
		$this->load->add_package_path(PATH_THIRD.'cartthrob'); 
		
		$this->load->library('data_filter');
		
		$fields = $this->fields ? $this->fields : array();
		
		foreach ($params as $key => $value)
		{
			$this->data_filter->filter($fields, $key, $value);
		}
		
		if ($limit !== FALSE)
		{
			$this->data_filter->limit($fields, $limit);
		}

		return $fields;
	}

	public function get_field_by_id($field_id)
	{
		return element($field_id, $this->fields);
	}
	
	public function get_field_by_name($field_name)
	{
		return current($this->get_fields(array('field_name' => $field_name), 1));
	}
	public function get_fields_by_type($field_type)
	{
		return $this->get_fields(array('field_type' => $field_type));
	}

	public function get_field_id($field_name)
	{
		return element('field_id', $this->get_field_by_name($field_name));
	}

	public function get_field_name($field_id)
	{
		return element('field_name', $this->get_field_by_id($field_id));
	}

	public function get_field_label($field_id)
	{
		return element('field_label', $this->get_field_by_id($field_id));
	}

	public function get_field_fmt($field_id)
	{
		return element('field_fmt', $this->get_field_by_id($field_id));
	}

	public function get_field_group($channel_id)
	{
		if (is_null($this->channels))
		{
			$query = $this->db->select('field_group, channel_id')
				->from('channels')
				->get();
			
			foreach ($query->result() as $row)
			{
				$this->channels[$row->channel_id] = $row->field_group;
			}
			
			$query->free_result();
		}

		return element($channel_id, $this->channels);
	}

	public function get_fields_by_group($group_id)
	{
		return $this->get_fields(array('group_id' => $group_id));
	}

	public function get_fields_by_channel($channel_id)
	{
		return $this->get_fields_by_group($this->get_field_group($channel_id));
	}

	public function get_field_type($field_id)
	{
		return element('field_type', $this->get_field_by_id($field_id));
	}

	public function get_field_settings($field_id)
	{
		if ( ! isset($this->fields[$field_id]))
		{
			return FALSE;
		}
		
		if ($this->fields[$field_id]['field_settings'] !== FALSE || ! is_array($this->fields[$field_id]['field_settings']))
		{
			$this->fields[$field_id]['field_settings'] = _unserialize($this->fields[$field_id]['field_settings'], TRUE);
		}
		
		return $this->fields[$field_id]['field_settings'];
	}
	
	public function get_category_fields($where = FALSE, $value = FALSE, $key = FALSE)
	{
		static $cache;
		$this->load->add_package_path(PATH_THIRD.'cartthrob'); 
		
		$this->load->library('data_filter');
		
		if (is_null($cache))
		{
			$query = $this->db->get('category_fields');
			
			$cache = $query->result_array();
			
			$query->free_result();
		}
		
		$category_fields = $cache;
		
		switch(func_num_args())
		{
			case 0:
				return $category_fields;
			case 1:
				$this->data_filter->filter($category_fields, 'field_id', $where);
				return current($category_fields);
			case 2:
				$this->data_filter->filter($category_fields, $where, $value);
				return $category_fields;
			case 3:
			default:
				$this->data_filter->filter($category_fields, $where, $value);
				return element($key, current($category_fields));
		}
	}
}

/* End of file field_model.php */
/* Location: ./system/expressionengine/models/field_model.php */
