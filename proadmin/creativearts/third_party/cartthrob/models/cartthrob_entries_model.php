<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cartthrob_entries_model extends CI_Model
{
	/**
	 * @var array cache of entries that have been requested
	 */
	protected $entries = array();
	
	/**
	 * @var array cache of the entries from the last request
	 */
	protected $last_entries = array();
	
	/**
	 * @var array cache of category-to-entry relationships
	 */
	protected $category_posts = array();
	
	/**
	 * @var array cache of categories that have been requested
	 */
	protected $categories = array();
	
	/**
	 * @var array log of errors encountered
	 */
	public $errors = array();
	
	public function __construct()
	{
		parent::__construct();
		
		$this->load->helper('array');
	}
	
	/**
	 * Get an entry by its entry_id
	 * 
	 * @param int|string $entry_id
	 * 
	 * @return array|FALSE    an array of the entry's channel_titles, channel_data (w/ field_name aliases) and channels data or FALSE if there is no entry
	 */
	public function entry($entry_id)
	{
		if ( ! $entry_id)
		{
			return FALSE;
		}
		
		$this->load_entries_by_entry_id(array($entry_id));
		
		return element($entry_id, $this->entries);
	}
	
	/**
	 * Get multiple entries by their entry_id
	 * 
	 * @param array $entry_ids
	 * 
	 * @return array
	 */
	public function entries(array $entry_ids)
	{
		$this->load_entries_by_entry_id($entry_ids);
		
		$entries = array();
		
		foreach ($entry_ids as $entry_id)
		{
			if (isset($this->entries[$entry_id]))
			{
				$entries[] =& $this->entries[$entry_id];
			}
		}
		
		return $entries;
	}
	
	/**
	 * Get the entries from the last request
	 * 
	 * @return array
	 */
	public function last_entries()
	{
		return $this->last_entries;
	}
	
	/**
	 * Get the entries based on a data filter
	 * 
	 * @param array $data see load_entries() method
	 *
	 * @see load_entries()
	 * @return Type    Description
	 */
	public function find_entries($data = array())
	{
		$this->load_entries($data);
		
		$entries = array();
		
		foreach ($this->last_entries as $entry_id)
		{
			if (isset($this->entries[$entry_id]))
			{
				$entries[$entry_id] = $this->entries[$entry_id];
			}
		}
		
		return $entries;
	}
	
	/**
	 * Load entries into the cache by entry_id
	 * 
	 * @param array $entry_ids 
	 * 
	 * @return $this
	 */
	public function load_entries_by_entry_id($entry_ids)
	{
		foreach ($entry_ids as $i => $entry_id)
		{
			if (isset($this->entries[$entry_id]))
			{
				unset($entry_ids[$i]);
			}
		}
		
		if ($entry_ids)
		{
			return $this->load_entries(array('channel_titles.entry_id' => $entry_ids));
		}
		
		return $this;
	}
	
	/**
	 * Load entries into the cache based on a where filter
	 * 
	 * @param array $data ex.
	 * 	array(
	 *		'channel_titles.url_title' => 'foo',// where
	 *		'channels.channel_name' => array('foo', 'bar'),// where_in
	 *		'channel_data.field_id_1' => '%foo%' // like
	 *	)
	 * 
	 * @return $this
	 */
	public function load_entries($data = array())
	{
		$this->load->model('cartthrob_field_model');
		
		foreach ($data as $key => $value)
		{
			if (is_array($value))
			{
				$this->db->where_in($key, $value);
			}
			else
			{
				if (strncmp($value, '%', 1) === 0 || substr($value, -1, 1) === '%')
				{
					$this->db->like($key, $value);
				}
				else
				{
					$this->db->where($key, $value);
				}
			}
		}
		
		$query = $this->db->select('channel_titles.*, channel_data.*, channels.*')
			->from('channel_titles')
			->join('channel_data', 'channel_titles.entry_id = channel_data.entry_id')
			->join('channels', 'channels.channel_id = channel_titles.channel_id')
			->get();
		
		$this->last_entries = array();
		
		foreach ($query->result_array() as $row)
		{
			if (isset($row['channel_name']))
			{
				$row['channel'] = $row['channel_name']; 
			}
			
			foreach ($this->cartthrob_field_model->get_fields(array('group_id' => $row['field_group'])) as $field)
			{
				$row[$field['field_name']] = $row['field_id_'.$field['field_id']];
			}
			
			$this->last_entries[] = $row['entry_id'];
			
			$this->entries[$row['entry_id']] = $row;
		}
		
		$query->free_result();
		
		return $this;
	}
	
	/**
	 * Create a new entry based on data
	 * 
	 * @param array $data data matching columns in channel_titles or channel_data or channel_fields "field_name"s, requires 'channel_id'
	 * 
	 * @return Type    Description
	 */
	public function create_entry(array $data)
	{
		$this->load->model('cartthrob_field_model');
		
		if ( ! isset($data['channel_id']))
		{
			$this->errors[] = 'no_channel_id';
			return FALSE;
		}
		
		$fields = $this->cartthrob_field_model->get_fields_by_channel($data['channel_id']);
		
		$title_fields = $this->db->list_fields('channel_titles');
		
		$site_id = $this->config->item('site_id'); 
		if (isset($data['site_id']))
		{
			$site_id = $data['site_id'];
		}
		$title_defaults = array(
			'author_id' => $this->session->userdata('member_id'),
			'site_id' => $site_id,
			'ip_address' => $this->input->ip_address(),
			'entry_date' => $this->localize->now - 60, // subtracting a minute to keep this entry from accidentally being a "future" entry
			'edit_date' => date("YmdHis"),
			'versioning_enabled' => 'y',
			'status' => 'open',
			'forum_topic_id' => 0,
		);
		
		$channel_titles = array();
		
		foreach ($title_fields as $key)
		{
			if (isset($data[$key]) && $key != 'entry_id')
			{
				$channel_titles[$key] = $data[$key];
			}
			else if (isset($title_defaults[$key]))
			{
				$channel_titles[$key] = $title_defaults[$key];
			}
		}
		
		$channel_titles['year'] = date('Y', $channel_titles['entry_date']);
		$channel_titles['month'] = date('m', $channel_titles['entry_date']);
		$channel_titles['day'] = date('d', $channel_titles['entry_date']);
 
		if (empty($data['author_id']))
		{
			$this->errors[] = 'no_author_id';
			return FALSE;
		}

		$this->db->insert('channel_titles', $channel_titles);
		
		unset($channel_titles, $title_fields, $title_defaults);
		
		$entry_id = $this->db->insert_id();

		$channel_data = array(
			'entry_id' => $entry_id,
			'channel_id' => $data['channel_id'],
			'site_id' => $site_id
		);
		
		foreach ($fields as $field)
		{
			if (isset($data['field_id_'.$field['field_id']]))
			{
				// accounting for items that are passed in as arrays
				$field_data = $data['field_id_'.$field['field_id']];
				if (is_array($field_data))
				{
					$field_data = implode('|', $field_data);
				}
				$data['field_id_'.$field['field_id']] = $field_data;

				$channel_data['field_id_'.$field['field_id']] = $data['field_id_'.$field['field_id']];
			}
			else
			{
				// accounting for items that are passed in as arrays
				$field_data = (isset($data[$field['field_name']])) ? $data[$field['field_name']] : '';
				if (is_array($field_data))
				{
					$field_data = implode('|', $field_data);
				}
				
				$channel_data['field_id_'.$field['field_id']] = $field_data; 
			}
			
			if (isset($data['field_ft_'.$field['field_id']]))
			{
				$channel_data['field_ft_'.$field['field_id']] = $data['field_ft_'.$field['field_id']];
			}
			else
			{
				$channel_data['field_ft_'.$field['field_id']] = $field['field_fmt'];
			}
		}

		$this->db->insert('channel_data', $channel_data);
		
		unset($channel_data);
		
		$total_entries = 1;
		
		$query = $this->db->select('total_entries')
				  ->where('member_id', $data['author_id'])
				  ->get('members');
		
		if ($query->row('total_entries'))
		{
			$total_entries = (int) $query->row('total_entries') + 1;
		}
		
		$this->db->update('members', array('total_entries' => $total_entries), array('member_id' => $data['author_id']));

		$this->stats->update_channel_stats($data['channel_id']);

		if ($this->config->item('new_posts_clear_caches') == 'y')
		{
			$this->functions->clear_caching('all');
		}
		else
		{
			$this->functions->clear_caching('sql');
		}

		return $entry_id;
	}
	
	/**
	 * Update an existing entry
	 * 
	 * @param int|string $entry_id
	 * @param array $data channel_titles or channel_data or channel_fields field_name'd columns
	 * 
	 * @return Type    Description
	 */
	public function update_entry($entry_id, $data)
	{
		$this->load->model('cartthrob_field_model');
		
		if (isset($data['channel_id']))
		{
			$channel_id = $data['channel_id'];
		}
		else
		{
			$query = $this->db->select('channel_id')
					  ->where('entry_id', $entry_id)
					  ->get('channel_titles');
			
			$channel_id = $query->row('channel_id');
			
			$query->free_result();
		}
		
		$fields = ($channel_id) ? $this->cartthrob_field_model->get_fields_by_channel($channel_id) : array();
		
		$title_fields = $this->db->list_fields('channel_titles');
		
		$channel_titles = array();
		
		foreach ($title_fields as $key)
		{
			if (isset($data[$key]))
			{
				$channel_titles[$key] = $data[$key];
			}
		}
		
 		$channel_titles['edit_date'] = date("YmdHis");
		
		$this->db->update('channel_titles', $channel_titles, array('entry_id' => $entry_id));
		
		
		$channel_data = array();
		
		foreach ($fields as $field)
		{
			if (isset($data['field_id_'.$field['field_id']]))
			{
				// accounting for data that's set as an array
				$field_data = $data['field_id_'.$field['field_id']];
				if (is_array($field_data))
				{
					$field_data = implode('|', $field_data);
				}
				$data['field_id_'.$field['field_id']] = $field_data;
				
				$channel_data['field_id_'.$field['field_id']] = $data['field_id_'.$field['field_id']];
			}
			else if (isset($data[$field['field_name']]))
			{
				$field_data = (isset($data[$field['field_name']])) ? $data[$field['field_name']] : '';
				if (is_array($field_data))
				{
					$field_data = implode('|', $field_data);
				}
				
				$channel_data['field_id_'.$field['field_id']] = $field_data; 
			}
		}
		
		if ($channel_data)
		{
			$this->db->update('channel_data', $channel_data, array('entry_id' => $entry_id));
		}
		
		return $entry_id;
	}
	
	/**
	 * Remove entries from cache
	 * 
	 * @param array|int|string $entry_ids
	 * 
	 * @return void
	 */
	public function clear_cache($entry_ids)
	{
		if ( ! is_array($entry_ids))
		{
			$entry_ids = array($entry_ids);
		}
		
		foreach ($entry_ids as $entry_id)
		{
			unset($this->entries[$entry_id]);
		}
	}
	
	/**
	 * Simulate an {exp:channel:entries} tag
	 * 
	 * @param array $params a list of parameters, usually use $this->EE->TMPL->tagparams
	 * @param bool $return_query return the SQL query instead of the parsed tagdata	
	 * 
	 * @return CI_DB_mysql_result|string|FALSE    the parsed tagdata (default) or the SQL query object or FALSE
	 */
	public function channel_entries($params = array(), $return_query = FALSE)
	{
		require_once PATH_MOD.'channel/mod.channel'.EXT;
		
		$channel = new Channel;
		
		if (isset($params['channel_id']) && ! isset($params['channel']))
		{
			if ( ! is_array($params['channel_id']))
			{
				if (strpos($params['channel_id'], '|') !== FALSE)
				{
					$params['channel_id'] = explode('|', $params['channel_id']);
				}
				else
				{
					$params['channel_id'] = array($params['channel_id']);
				}
			}
			
			$params['channel'] = '';
			
			foreach ($params['channel_id'] as $i => $channel_id)
			{
				if (isset($this->session->cache['cartthrob']['channel_names'][$channel_id]))
				{
					$params['channel'] = ($params['channel']) ? '|'.$this->session->cache['cartthrob']['channel_names'][$channel_id] : $this->session->cache['cartthrob']['channel_names'][$channel_id];
					
					unset($params['channel_id'][$i]);
				}
			}
			
			if (count($params['channel_id']) > 0)
			{
				$query = $this->db->select('channel_id, channel_name')
						  ->where_in('channel_id', $params['channel_id'])
						  ->get('channels');
				
				foreach ($query->result() as $row)
				{
					$this->session->cache['cartthrob']['channel_names'][$row->channel_id] = $row->channel_name;
					
					$params['channel'] .= ($params['channel']) ? '|'.$row->channel_name : $row->channel_name;
				}
				
				$query->free_result();
				
				unset($query);
			}
		}
		
		if (isset($this->TMPL->tagparams) && is_array($this->TMPL->tagparams))
		{
 			$this->TMPL->tagparams = array_merge($this->TMPL->tagparams, $params);
		}
		else
		{
			$this->TMPL->tagparams =  $params; 
		}
		
		if ( ! $return_query)
		{
			$this->TMPL->tagdata = $this->TMPL->assign_relationship_data($this->TMPL->tagdata);
			
			if (count($this->TMPL->related_markers) > 0)
			{
				foreach ($this->TMPL->related_markers as $marker)
				{
					if ( ! isset($this->TMPL->var_single[$marker]))
					{
						$this->TMPL->var_single[$marker] = $marker;
					}
				}
			}
	
			if ($this->TMPL->related_id)
			{
				$this->TMPL->var_single[$this->TMPL->related_id] = $this->TMPL->related_id;
				
				$this->TMPL->related_id = '';
			}
			
			return $channel->entries();
		}
	
		$channel->uri = ($channel->query_string) ? $channel->query_string : 'index.php';
		
		
		$channel->fetch_custom_channel_fields();
		
		$save_cache = ($this->config->item('enable_sql_caching') === 'y' && ! ($channel->sql = $channel->fetch_cache()));
		
		if ( ! $channel->sql)
		{
			$channel->build_sql_query();
		}
		
		if ($channel->sql)
		{
			if ($save_cache)
			{
				$channel->save_cache($channel->sql);
			}
			
			return $this->db->query($channel->sql);
		}
		
		return FALSE;
	}
	
	/**
	 * Grab the entry vars to pass along to TMPL->parse_variables, simulating a {exp:channel:entries} tag
	 * 
	 * @param array|string|int $entry      either the entry array from this class's entry() method or an entry_id
	 * @param string|false $tagdata
	 * @param string $prefix the variable prefix
	 * 
	 * @return array
	 */
	public function entry_vars($entry, $tagdata = FALSE, $prefix = '')
	{
		static $channels = array();
		
		if ($tagdata === FALSE)
		{
			$tagdata = $this->TMPL->tagdata;
			
			$var_single = $this->TMPL->var_single;
			
			$var_pair = $this->TMPL->var_pair;
		}
		else
		{
			$assign_variables = $this->functions->assign_variables($tagdata);
			
			$var_single = $assign_variables['var_single'];
			
			$var_pair = $assign_variables['var_pair'];
		}
		
		//use the $entry as entry_id
		if ( ! is_array($entry))
		{
			$entry = $this->entry($entry);
		}
		
		if ( ! $entry)
		{
			return array();
		}
		
		$this->load->library('api');
		
		$this->api->instantiate('channel_fields');
		
		$this->load->library('typography');
		
		$this->load->add_package_path(PATH_THIRD.'cartthrob'); 
		
		$this->load->model('cartthrob_field_model');
		
		$this->load->library('data_filter');
		
		$row = array();
		
		preg_match_all('/'.LD.'('.preg_quote($prefix).')?entry_id_path=([\042\047]?)(.*)\\2'.RD.'/', $tagdata, $entry_id_paths);
		preg_match_all('/'.LD.'('.preg_quote($prefix).')?(url_title_path|title_permalink)=([\042\047]?)(.*)\\3'.RD.'/', $tagdata, $url_title_paths);
		
		if (($site_pages = $this->config->item('site_pages')) !== FALSE && isset($site_pages[$entry['site_id']]['uris'][$entry['entry_id']]))
		{
			$row[$prefix.'page_uri'] = $row['page_uri'] = $site_pages[$entry['site_id']]['uris'][$entry['entry_id']];
			$row[$prefix.'page_url'] = $row['page_url'] = $this->functions->create_page_url($site_pages[$entry['site_id']]['url'], $site_pages[$entry['site_id']]['uris'][$entry['entry_id']]);
		}
		else
		{
			$row[$prefix.'page_uri'] = $row['page_uri'] = '';
			$row[$prefix.'page_url'] = $row['page_url'] = '';
		}
		
		//set up all the fieldtypes in api_channel_fields
		if ( ! in_array($entry['channel_id'], $channels))
		{
 			$channels[] = $entry['channel_id'];
			
			foreach ($this->cartthrob_field_model->get_fields_by_channel($entry['channel_id']) as $field)
			{
				if ( ! isset($this->api_channel_fields->settings[$field['field_id']]))
				{
					$this->api_channel_fields->set_settings(
						$field['field_id'],
						array_merge(
							$field,
							$this->cartthrob_field_model->get_field_settings($field['field_id'])
						)
					);
				}
			}
		}
		
		$categories = (strstr($tagdata, '{categories')) ? $this->entry_categories($entry['entry_id']) : FALSE;
		
		foreach ($var_pair as $var_full => $var_params)
		{
			$var_name = $var_full;
			
			if (($pos = strpos($var_full, ' ')) !== FALSE)
			{
				$var_name = substr($var_full, 0, $pos);
			}
			
			if ($prefix && strpos($var_name, $prefix) === 0)
			{
				$var_name = substr($var_name, strlen($prefix));
			}
			
			if ($var_name === 'categories')
			{
				if ( ! empty($var_params['show']))
				{
					if (strncmp($var_params['show'], 'not ', 4) === 0)
					{
						$this->data_filter->filter($categories, 'cat_id', substr($var_params['show'], 4), 'not_in_array');
					}
					else
					{
						$this->data_filter->filter($categories, 'cat_id', $var_params['show'], 'in_array');
					}
				}
				
				if ( ! empty($var_params['show_group']))
				{
					if (strncmp($var_params['show_group'], 'not ', 4) === 0)
					{
						$this->data_filter->filter($categories, 'group_id', substr($var_params['show_group'], 4), 'not_in_array');
					}
					else
					{
						$this->data_filter->filter($categories, 'group_id', $var_params['show_group'], 'in_array');
					}
				}
				
				if (isset($var_params['limit']))
				{
					$this->data_filter->limit($categories, $var_params['limit']);
				}
				
				if (preg_match_all('#'.LD.'('.preg_quote($prefix).')?'.preg_quote($var_full).RD.'(.*?)'.LD.'/'.'('.preg_quote($prefix).')?'.preg_quote($var_name).RD.'#s', $tagdata, $matches))
				{
					foreach ($matches[2] as $i => $pair_tagdata)
					{
						$pair_var = substr($matches[0][$i], 1, -1);
						
						if ( ! $categories)
						{
							$row[$pair_var] = $row[$prefix.$pair_var] = '';
							
							continue;
						}
						
						if (preg_match_all('/{path=(.*?)}/', $pair_tagdata, $path_matches))
						{
							foreach ($path_matches[1] as $j => $path_match)
							{
								$path_var = substr($path_matches[0][$j], 1, -1);
								
								foreach ($categories as &$category)
								{
									if ($this->config->item('use_category_name'))
									{
										$category[$path_var] = $this->functions->create_url($path_match).'/'.$this->config->item('reserved_category_word').'/'.$category['cat_url_title'];
									}
									else
									{
										$category[$path_var] = $this->functions->create_url($path_match).'/'.$this->config->item('reserved_category_word').'/C'.$category['cat_id'];
									}
									
								}
							}
						}
						
						$row[$pair_var] = $row[$prefix.$pair_var] = $this->TMPL->parse_variables($pair_tagdata, $categories);
						
						if (isset($var_params['backspace']))
						{
							$row[$pair_var] = $row[$prefix.$pair_var] = substr($row[$pair_var], 0, strlen($row[$pair_var]) - $var_params['backspace'] -1);
						}
					}
				}
				
				continue;
			}
			
			$field = $this->cartthrob_field_model->get_field_by_name($var_name);
			
			//make sure this field is actually in the entry's channel
			if ($field && in_array($field, $this->cartthrob_field_model->get_fields_by_channel($entry['channel_id'])))
			{
				$field_id = $field['field_id'];
				
				//@TODO test this new regex
				if (preg_match_all('#'.LD.'('.preg_quote($prefix).')?'.preg_quote($var_full).RD.'(.*?)'.LD.'/'.'('.preg_quote($prefix).')?'.preg_quote($var_name).RD.'#s', $tagdata, $matches))
				{
					foreach ($matches[2] as $i => $pair_tagdata)
					{
						$var_key = substr($matches[0][$i], 1, -1);
						
						if (isset($row[$var_key]))
						{
							continue;
						}
					
						if ( ! isset($entry['field_id_'.$field_id]))
						{
							$row[$var_key] = '';
						}
						else if ($this->api_channel_fields->setup_handler($field_id))
						{
							$this->api_channel_fields->apply('_init', array(array('row' => $entry)));
							
							$var_data = $this->api_channel_fields->apply('pre_process', array($entry['field_id_'.$field_id]));
							
							$row[$var_key] = $this->api_channel_fields->apply('replace_tag', array($var_data, $var_params, $pair_tagdata));
						}
						else
						{
							$row[$var_key] = $entry['field_id_'.$field_id];
						}
					}
				}
			}
		}
		
		foreach ($var_single as $var_full)
		{
			$var_params = array();
			$var_name = $var_full;
			
			if (($pos = strpos($var_name, ' ') !== FALSE))
			{
				$var_params = $this->functions->assign_parameters(substr($var_full, $pos));
				$var_name = substr($var_full, 0, $pos);
			}
			
			if ($prefix && strpos($var_name, $prefix) === 0)
			{
				$var_name = substr($var_name, strlen($prefix));
			}
			
			$method = 'replace_tag';
			
			if (($pos = strpos($var_name, ':')) !== FALSE)
			{
				$sub_method = substr($var_name, $pos + 1);
				$method = 'replace_'.$sub_method;
				$var_name = substr($var_name, 0, $pos);
			}
			
			$field = $this->cartthrob_field_model->get_field_by_name($var_name);
			
			//make sure the field is actually in this entry's channel
			if ($field && in_array($field, $this->cartthrob_field_model->get_fields_by_channel($entry['channel_id'])))
			{
				$field_id = $field['field_id'];
				
				if (empty($entry['field_id_'.$field_id]))
				{
					$row[$var_full] = '';
				}
				else if ($this->api_channel_fields->setup_handler($field_id))
				{
					$this->api_channel_fields->apply('_init', array(array('row' => $entry)));
					
					$var_data = $this->api_channel_fields->apply('pre_process', array($entry['field_id_'.$field_id]));
					
					if ($this->api_channel_fields->check_method_exists($method))
					{
					$row[$var_full] = $this->api_channel_fields->apply($method, array($var_data, $var_params, FALSE));
				}
					else if ($this->api_channel_fields->check_method_exists('replace_tag_catchall'))
					{
						$row[$var_full] = $this->api_channel_fields->apply('replace_tag_catchall', array($var_data, $var_params, FALSE, $sub_method));
					}
				}
				else
				{
					$row[$var_full] = $entry['field_id_'.$field_id];
				}
			}
		}
		
		foreach ($entry as $key => $value)
		{
			if ( ! isset($row[$prefix.$key]))
			{
				$row[$prefix.$key] = $value;
			}
			
			if ( ! isset($row[$key]))
			{
				$row[$key] = $value;
			}
		}
		
		foreach ($entry_id_paths[0] as $i => $match)
		{
			$row[substr($match, 1, -1)] = ( ! empty($entry['entry_id'])) ? $this->functions->create_url($entry_id_paths[3][$i].'/'.$entry['entry_id']) : '';
		}
		
		foreach ($url_title_paths[0] as $i => $match)
		{
			$row[substr($match, 1, -1)] = ( ! empty($entry['url_title'])) ? $this->functions->create_url($url_title_paths[4][$i].'/'.$entry['url_title']) : '';
		}
		
		return $row;
	}
	
	public function entry_categories($entry_id)
	{
		if ( ! isset($this->category_posts[$entry_id]))
		{
			$this->load_categories_by_entry_id($entry_id);
		}
		
		return $this->categories($this->category_posts[$entry_id]);
	}
	
	public function load_categories_by_entry_id($entry_ids)
	{
		if ( ! is_array($entry_ids))
		{
			$entry_ids = array($entry_ids);
		}
		
		$all_entry_ids = $entry_ids;
		
		foreach ($entry_ids as $i => $entry_id)
		{
			if (isset($this->category_posts[$entry_id]))
			{
				unset($entry_ids[$i]);
			}
		}
		
		if ($entry_ids)
		{
			$this->load_category_posts($entry_ids);
		}
		
		$cat_ids = array();
		
		foreach ($all_entry_ids as $entry_id)
		{
			//if (isset($this->category_posts[$entry_id]))
			//{
				$cat_ids = array_merge($cat_ids, $this->category_posts[$entry_id]);
			//}
		}
		
		$cat_ids = array_unique($cat_ids);
		
		$this->load_categories_by_cat_id($cat_ids);
	}
	
	public function load_category_posts($entry_ids)
	{
		foreach ($entry_ids as $entry_id)
		{
			if ( ! isset($this->category_posts[$entry_id]))
			{
				$this->category_posts[$entry_id] = array();
			}
		}
		
		$query = $this->db->where_in('entry_id', $entry_ids)
				  ->get('category_posts');
		
		foreach ($query->result() as $row)
		{
			$this->category_posts[$row->entry_id][] = $row->cat_id;
		}
		
		$query->free_result();
	}
	
	public function load_categories_by_cat_id($cat_ids)
	{
		if ( ! is_array($cat_ids))
		{
			$cat_ids = array($cat_ids);
		}
		
		foreach ($cat_ids as $i => $cat_id)
		{
			if (isset($this->categories[$cat_id]))
			{
				unset($cat_ids[$i]);
			}
		}
		
		if ($cat_ids)
		{
			$this->load_categories(array('categories.cat_id' => $cat_ids));
		}
	}
	
	public function categories($cat_ids)
	{
		$this->load_categories_by_cat_id($cat_ids);
		
		$categories = array();
		
		foreach ($cat_ids as $cat_id)
		{
			if (isset($this->categories[$cat_id]))
			{
				$categories[] = $this->categories[$cat_id];
			}
		}
		
		return $categories;
	}
	
	public function load_categories($data)
	{
		foreach ($data as $key => $value)
		{
			if (is_array($value))
			{
				$this->db->where_in($key, $value);
			}
			else
			{
				if (strncmp($value, '%', 1) === 0 || substr($value, -1, 1) === '%')
				{
					$this->db->like($key, $value);
				}
				else
				{
					$this->db->where($key, $value);
				}
			}
		}
		
		$query = $this->db->join('category_field_data', 'category_field_data.cat_id = categories.cat_id')
				  ->get('categories');
		
		$this->load->model('cartthrob_field_model');
		
		foreach ($query->result_array() as $row)
		{
			if (isset($this->categories[$row['cat_id']]))
			{
				continue;
			}
			
			foreach ($row as $key => $value)
			{
				if (strncmp($key, 'cat_', 4) === 0)
				{
					$row['category_'.substr($key, 4)] = $value;
				}
			}
			
			foreach ($this->cartthrob_field_model->get_category_fields() as $field)
			{
				$row[$field['field_name']] = $row['field_id_'.$field['field_id']];
			}
			
			$this->categories[$row['cat_id']] = $row;
		}
		
		$query->free_result();
	}
}
