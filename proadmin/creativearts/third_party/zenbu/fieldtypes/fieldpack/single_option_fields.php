<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
*	ZENBU THIRD-PARTY FIELDTYPE SUPPORT
*	============================================
*	Pixel&Tonic's Field Pack fields
*	@author	Pixel&tonic http://pixelandtonic.com
*	@link	http://pixelandtonic.com/ee
*	============================================
*	File single_option_fields.php
*
* 	This file is an attempt to cover P&T Fieldpack 
* 	fieldtypes that act similarly in terms of their
* 	data content and presentation in Zenbu.
*	
*/

class Zenbu_fieldpack_single_option_fields
{
	/**
	*	Constructor
	*
	*	@access	public
	*/
	function __construct()
	{
		$this->EE =& get_instance();
	}
	
	/**
	*	======================
	*	function zenbu_display
	*	======================
	*	Set up display in entry result cell
	*
	*	@param	$entry_id			int		The entry ID of this single result entry
	*	@param	$channel_id			int		The channel ID associated to this single result entry
	*	@param	$data				array	Raw data as found in database cell in exp_channel_data
	*	@param	$table_data			array	Data array usually retrieved from other table than exp_channel_data
	*	@param	$field_id			int		The ID of this field
	*	@param	$settings			array	The settings array, containing saved field order, display, extra options etc settings
	*	@param	$rules				array	An array of entry filtering rules 
	*	@param	$upload_prefs		array	An array of upload preferences (optional)
	*	@param 	$installed_addons	array	An array of installed addons and their version numbers (optional)
	*	@param	$fieldtypes			array	Fieldtype of available fieldtypes: id, name, etc (optional)
	*	@return	$output		The HTML used to display data
	*/
	function zenbu_display($entry_id, $channel_id, $data, $table_data = array(), $field_id, $settings, $rules = array(), $upload_prefs = array(), $installed_addons, $fieldtypes)
	{
		$output = (empty($data)) ? '&nbsp;' : '';
		$keyword = "";
		
		foreach($rules as $rule)
		{
			if($rule['field'] == 'field_'.$field_id)
			{
				$keyword = $rule['val'];
			}
		}
		
		if(empty($data))
		{
			return $output;
		}
		
		$field_settings = $fieldtypes['settings'][$field_id];
		$field_setting = $field_settings['options'];
		$field_data = explode("\n", $data);

		foreach($field_data as $key => $value)
		{
			$output .= (isset($field_setting[$value])) ? $field_setting[$value].', ' : '';
		}
		
		$output = substr($output, 0, -2);
		$output = highlight($output, $rules, 'field_'.$field_id);

		return $output;
	}
	
	/**
	*	===================================
	*	function zenbu_result_query
	*	===================================
	*	Extra queries to be intergrated into main entry result query
	*
	*	@param	$rules				int		An array of entry filtering rules 
	*	@param	$field_id			array	The ID of this field
	*	@param	$fieldtypes			array	$fieldtype data
	*	@param	$already_queried	bool	Used to avoid using a FROM statement for the same field twice
	*	@param	$installed_addons	array	An array of installed addons and their version numbers (optional)
	*	@return					A query to be integrated with entry results. Should be in CI Active Record format ($this->EE->db->…)
	*/
	function zenbu_result_query($rules = array(), $field_id = "", $fieldtypes)
	{
		if(empty($rules))
		{
			return;
		}
		
		$field_settings = (isset($fieldtypes['settings'][$field_id])) ? $fieldtypes['settings'][$field_id] : '';
		
		if(isset($field_settings['options']))
		{
			foreach($rules as $rule)
			{
				if(strncmp($rule['field'], 'field_', 6) == 0 && substr($rule['field'], 6) == $field_id)
				{
					
					$keyword_in_db = "";
					$keyword = $rule['val'];

					// No need to run any of this if keyword is empty

					if(empty($keyword))
					{
						return;
					}

					// Get the keywords stored in db field, based on label

					foreach($field_settings['options'] as $key => $val)
					{
						if(stripos($field_settings['options'][$key], $keyword) !== FALSE)
						{
							$keyword_in_db[] = $key;
						}
					}
					
					// Build query to get entries with or without the keyword stored in db field
					
					switch ($rule['cond'])
					{
						case "contains" :
							if(empty($keyword_in_db))
							{
								$like_query = "entry_id = 0";
							} else {
								$like_query = implode($keyword_in_db, '%" OR field_id_'.$field_id.' LIKE "%');
								$like_query = 'field_id_'.$field_id.' LIKE "%'.$like_query.'%"';
							}
						break;
						case "doesnotcontain" :
							if( ! empty($keyword_in_db))
							{
								$like_query = implode($keyword_in_db, '%" AND field_id_'.$field_id.' NOT LIKE "%');
								$like_query = 'field_id_'.$field_id.' NOT LIKE "%'.$like_query.'%" OR field_id_'.$field_id.' IS NULL';
							} else {
								return;
							}
						break;
					}

					//	Run the final query that will be added to Zenbu
					
					if( isset($like_query) )
					{
						$query = $this->EE->db->query("/* fieldpack_pill.php, zenbu_result_query() */ SELECT entry_id FROM exp_channel_data WHERE ".$like_query);
						if($query->num_rows() > 0)
						{
							foreach($query->result_array() as $row)
							{
								$entries[] = $row['entry_id'];
							}
						} else {
							$entries[] = 0;
						}
						
						// Filter by entry IDs within the above results
						$this->EE->db->where_in("exp_channel_titles.entry_id", $entries);

						unset($entries);
					}

				}

			} // foreach($rules as $rule)
						
		}
		
		
	}
	
	
} // END CLASS

/* End of file single_option_fields.php */
/* Location: ./system/expressionengine/third_party/zenbu/fieldtypes/single_option_fields.php */
?>