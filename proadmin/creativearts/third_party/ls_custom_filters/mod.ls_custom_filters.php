<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * LS Custom Filters Module Front End File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Raj Sadh
 * @link		http://www.sixth.co.in
 */

class Ls_custom_filters {
	
	public $return_data;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
	}
	
	// ----------------------------------------------------------------

	public function attributes(){
	$query = ee()->db->distinct()->select('col_id_24')->from('exp_channel_grid_field_88')->get();
	if ($query->num_rows() > 0){
	foreach ($query->result() as $row)
	{
    $variables[] = array(
				'attribute_title' => $row->col_id_24,
				);	   
	}
	return $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $variables);
	}	
	}
	
	
	
	public function attributes_options(){
	$attribute = ee()->TMPL->fetch_param('attribute');
	$entry_id = ee()->TMPL->fetch_param('entry_id');
	$main_category = ee()->TMPL->fetch_param('main_category');
	
	// select all attributes values for given attribute title for this entry where the attribute value belongs to this entry's main category.
	$query = ee()->db->distinct();
	ee()->db->select('a.col_id_25');
	ee()->db->where('col_id_24',$attribute);
	ee()->db->from('exp_channel_grid_field_88 a');
	if((isset($main_category)) && ($main_category != "")){	
	ee()->db->join('exp_category_posts b','a.entry_id = b.entry_id');	
	ee()->db->where('b.cat_id',$main_category);
	}
	$query = ee()->db->get();
	
	if ($query->num_rows() > 0){
	foreach ($query->result() as $row)
	{
    $variables[] = array(
				'attribute_value' => $row->col_id_25,
				);	   
	}
	return $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $variables);
	}	
	}
	
}
/* End of file mod.ls_custom_filters.php */
/* Location: /system/expressionengine/third_party/ls_custom_filters/mod.ls_custom_filters.php */