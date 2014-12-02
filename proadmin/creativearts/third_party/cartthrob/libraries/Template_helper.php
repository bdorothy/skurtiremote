<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (class_exists(basename(__FILE__, '.php'))) return;

/**
 * Template Helper
 *
 * @property $EE CI_Controller
 *
 * @package CartThrob2
 */
class Template_helper
{
	public $template_key;
	
	public $base_url;
	
	public function __construct()
	{
		$this->EE =& get_instance();
		
		$this->reset();
	}
	
	public function reset($params = array())
	{
		$this->base_url = (isset($params['base_url'])) ? $params['base_url'] : $this->EE->config->item('site_url');
		
		$this->template_key = (isset($params['template_key'])) ? $params['template_key'] : 'template';
		
		return $this;
	}
	
	public function cp_render($template = NULL)
	{
		if (is_null($template))
		{
			$template = $this->EE->input->get_post($this->template_key);
		}
		
		$this->EE->config->config['site_url'] = $this->base_url;
		
		$this->EE->config->config['site_index'] = '';
		
		if ($this->EE->input->post('ACT'))
		{
			$this->EE->db->flush_cache();
			
			//@TODO this is broke. for now don't do forms in your reports templates
			
			return $this->EE->core->generate_action(TRUE);
		}
		
		$this->EE->cp->cp_page_title =  $template; 
		
		$this->EE->load->library('template', NULL, 'TMPL');
		
		$this->EE->uri->uri_string = $template;
		
		$this->EE->uri->segments = explode('/', $template);
		
		$this->EE->uri->rsegments = array_reverse($this->EE->uri->segments);
		
		$this->EE->uri->_reindex_segments();
		
		$this->load_snippets();
		
		$this->EE->TMPL->run_template_engine($this->EE->uri->segment(1), $this->EE->uri->segment(2));
		
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		
		$this->return_data = $this->EE->output->get_output();
		
		/*
		$this->return_data = str_replace(LD.'total_queries'.RD, $this->EE->db->query_count, $this->return_data);

		global $BM;
		
		$this->return_data = str_replace(LD.'elapsed_time'.RD, $BM->elapsed_time('total_execution_time_start', 'total_execution_time_end'), $this->return_data);
		
		$this->return_data = str_replace(LD.'memory_usage'.RD, (( ! function_exists('memory_get_usage')) ? '0' : round(memory_get_usage()/1024/1024, 2).'MB'), $this->return_data);
		
		if (0 && preg_match_all('/<input(.*)name=([\042\047])ACT\\2(.*)value=([\042\047])(\d+)\\4(.*)>/', $this->return_data, $matches))
		{
			foreach ($matches[0] as $i => $match)
			{
				$this->return_data = str_replace($match, '<input'.$matches[1][$i].'name='.$matches[2][$i].'CPT_ACT'.$matches[2][$i].$matches[3][$i].'value='.$matches[4][$i].$matches[5][$i].$matches[4][$i].$matches[6][$i].'>'.$action_input, $this->return_data);
			}
		}
		*/
		
		$this->EE->output->set_output('');
		
		foreach($this->EE->cp->js_files as $type => $files)
		{
			if ( ! is_array($files))
			{
				$this->EE->cp->js_files[$type] = explode(',', $files);
			}
		}
		
		return $this->return_data;
	}
	
	private function load_snippets()
	{
		// load up any Snippets
		$query = $this->EE->db->select('snippet_name, snippet_contents')
				->where('(site_id = '.$this->EE->db->escape_str($this->EE->config->item('site_id')).' OR site_id = 0)')
				->get('snippets');

		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$this->EE->config->_global_vars[$row->snippet_name] = $row->snippet_contents;
			}
		}
		
		$query->free_result();
	}
	
	/**
	 * creates a template_group/template string from various possibilties:
	 * 	http://site.com/template_group/template/
	 *	/template_group/template
	 *	template_group/template
	 *	template_group/template/
	 *	{path=template_group/template}
	 *	{site_url}template_group/template
	 * 
	 * @param string $path a template path
	 * 
	 * @return string
	 */
	public function parse_template_path($path)
	{
		$remove = array(
			'/',
			$this->EE->functions->fetch_site_index(TRUE, TRUE),
			$this->EE->functions->fetch_site_index(),
			$this->EE->functions->fetch_site_index(TRUE, FALSE),
			$this->EE->functions->fetch_site_index(FALSE, FALSE),
			'{site_url}',
		);
		
		foreach ($remove as $starts_with)
		{
			$length = strlen($starts_with);
			
			if (strncmp($path, $starts_with, $length) === 0)
			{
				$path = substr($path, $length);
				
				break;
			}
		}
		
		if (strstr($path, '{path=') && preg_match('/{path=([\042\047]?)(.*?)\\1}/', $path, $match))
		{
			$path = $match[2];
		}
		
		$path = rtrim($path, '/');
		
		return $path;
	}
	
	/**
	 * fetch a template from the database/file structure
	 * 
	 * @param string $template          "template_group/template" format
	 * @param bool $get_template_info   see return below
	 * 
	 * @return string|array    either a string of the template_data or an array containing info about the template
	 */
	public function fetch_template($template, $get_template_info = FALSE)
	{
		$template = $this->parse_template_path($template);
		
		$template = explode('/', $template);

		$template_group = $template[0];

		$template_name = (isset($template[1])) ? $template[1] : 'index';

		$query = $this->EE->db->select('template_data, template_type, save_template_file, allow_php, php_parse_location')
				      ->join('template_groups', 'templates.group_id = template_groups.group_id')
				      ->where('group_name', $template_group)
				      ->where('template_name', $template_name)
				      ->where('templates.site_id', $this->EE->config->item('site_id'))
				      ->get('templates');
		
		$data = array(
			'template_data' => '',
			'parse_php' => FALSE,
			'php_parse_location' => 'output',
			'template_type' => 'webpage',
		);
		
		if ($query->num_rows() !== 0)
		{
			$data['parse_php'] = $query->row('allow_php') === 'y';
			
			$data['php_parse_location'] = ($query->row('php_parse_location') === 'i') ? 'input' : 'output';
			
			$data['template_type'] = $query->row('template_type');
			
			$data['template_data'] = $query->row('template_data');
			
			if ($query->row('save_template_file') === 'y'
			    && $this->EE->config->item('tmpl_file_basepath')
			    && $this->EE->config->item('save_tmpl_files') === 'y')
			{
				$this->EE->load->library('api');
				
				$this->EE->api->instantiate('template_structure');
				
				$file = $this->EE->config->slash_item('tmpl_file_basepath')
					.$this->EE->config->item('site_short_name').'/'
					.$template_group.'.group/'.$template_name
					.$this->EE->api_template_structure->file_extensions($query->row('template_type'));
				
				if (file_exists($file))
				{
					$data['template_data'] = file_get_contents($file);	
				}
			}
			
			$data['template_data'] = str_replace(array("\r\n", "\r"), "\n", $data['template_data']);
			
			$query->free_result();
		}
 
		if ($this->EE->extensions->active_hook('template_fetch_template'))
		{
			$this->EE->extensions->call('template_fetch_template', $data);
		}
		
		return ($get_template_info) ? $data : $data['template_data'];
	}

	public function apply_search_filters(&$data)
	{
		$this->EE->load->library('data_filter');

		foreach ((array) $this->EE->TMPL->tagparams as $key => $value)
		{
			if (strncmp($key, 'search:', 7) === 0 && $value)
			{
				$key = substr($key, 7);

				$exact = FALSE;

				$operator = NULL;

				if ($value && in_array($value[0], array('=', '>', '>=', '<', '<=')))
				{
					$exact = $value[0] === '=';

					$operator = $value[0];

					$value = substr($value, 1);
				}

				$not = FALSE;

				if (strncmp('not ', $value, 4) === 0)
				{
					$not = TRUE;

					$value = substr($value, 4);
				}

				$and = FALSE;

				$array = FALSE;

				if (strstr($value, '&&'))
				{
					$and = TRUE;

					$array = TRUE;
				}
				else if (strstr($value, '|'))
				{
					$array = TRUE;
				}

				if ($array)
				{
					if ($exact)
					{
						if ($not)
						{
							$this->EE->data_filter->filter($data, $key, $value, 'NOT_IN', TRUE);
						}
						else
						{
							$this->EE->data_filter->filter($data, $key, $value, 'IN', TRUE);
						}
					}
					else
					{
						if ($not)
						{
							if ($and)
							{
								$this->EE->data_filter->filter($data, $key, explode('&&', $value), 'DOES_NOT_CONTAIN_ALL_OF', TRUE);
							}
							else
							{
								$this->EE->data_filter->filter($data, $key, $value, 'DOES_NOT_CONTAIN_ONE_OF', TRUE);
							}
						}
						else
						{
							if ($and)
							{
								$this->EE->data_filter->filter($data, $key, explode('&&', $value), 'CONTAINS_ALL_OF', TRUE);
							}
							else
							{
								$this->EE->data_filter->filter($data, $key, $value, 'CONTAINS_ONE_OF', TRUE);
							}
						}
					}
				}
				else
				{
					if ($operator)
					{
						if ($exact)
						{
							$this->EE->data_filter->filter($data, $key, $value, '==', TRUE);
						}
						else
						{
							$this->EE->data_filter->filter($data, $key, $value, $operator, TRUE);
						}
					}
					else
					{
						$this->EE->data_filter->filter($data, $key, $value, 'CONTAINS', TRUE);
					}
				}
			}
		}
	}
	
	public function fetch_and_parse($template, $vars = array())
	{
		$template_info = $this->fetch_template($template, TRUE);
		
		return $this->parse_template($template_info['template_data'], $vars, $template_info['parse_php'], $template_info['php_parse_location'], $template_info['template_type']);
	}
	
	public function parse_template($template, $vars = array(), $parse_php = FALSE, $php_parse_location = 'output', $template_type = 'webpage')
	{
		if ( ! isset($this->EE->TMPL))
		{
			$this->EE->load->library('template', NULL, 'TMPL');
		}
		
		if ($this->EE->extensions->active_hook('template_fetch_template'))
		{
			$this->EE->extensions->call('template_fetch_template', array('template_data' => $template));
		}

		$this->EE->TMPL->parse_php = $parse_php;
		
		$this->EE->TMPL->php_parse_location = $php_parse_location;
		
		$this->EE->TMPL->template_type = $this->EE->functions->template_type = $template_type;
		
		if ($vars)
		{
			$template = $this->EE->TMPL->parse_variables($template, array($vars));
		}
		
		$this->EE->TMPL->parse($template);
		
		$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
		
		return $this->EE->TMPL->parse_globals($this->EE->TMPL->final_template);
	}
	
	public function tag_redirect($location = FALSE)
	{
		$this->EE->load->library('paths');
	
		if ($location)
		{
			$this->EE->load->library('javascript');
			
			$this->EE->functions->redirect($this->EE->paths->parse_url_path($location));
		}
	}

	public function parse_variables_row($row)
	{
		if ( ! isset($this->EE->TMPL))
		{
			$this->EE->load->library('template', NULL, 'TMPL');
		}

		$this->EE->load->helper('data_formatting');
		
		if ($prefix = $this->EE->TMPL->fetch_param('variable_prefix'))
		{
			$row = array_merge($row, array_key_prefix($row, $prefix));
		}

		return $this->EE->TMPL->parse_variables_row($this->EE->TMPL->tagdata, $row);
	}
	
	public function parse_variables($variables = array())
	{
		if ( ! isset($this->EE->TMPL))
		{
			$this->EE->load->library('template', NULL, 'TMPL');
		}

		$this->EE->load->helper('data_formatting');
		
		if ($prefix = $this->EE->TMPL->fetch_param('variable_prefix'))
		{
			foreach ($variables as &$row)
			{
				$row = array_merge($row, array_key_prefix($row, $prefix));
			}
		}
		
		reset($variables);
		
		if ( ! $variables || (count($variables) === 1 && ! current($variables)))
		{
			if ($prefix && preg_match('#{if\s+'.preg_quote($prefix).'no_results}(.*?){/if}#s', $this->EE->TMPL->tagdata, $match))
			{
				$this->EE->TMPL->tagdata = str_replace($match[0], '', $this->EE->TMPL->tagdata);
				
				$this->EE->TMPL->no_results = $match[1];
			}
			
			return $this->EE->TMPL->no_results();
		}

		return $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $variables);
	}
}