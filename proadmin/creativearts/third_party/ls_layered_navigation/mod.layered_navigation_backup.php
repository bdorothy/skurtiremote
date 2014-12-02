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
 * Layered Navigation Module Front End File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Sixthsense
 * @link		http://www.sixth.co.in
 */

class Layered_navigation {
	
	public $return_data;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
		ee()->load->library('typography');
		ee()->typography->initialize();		
	}
	
	// ----------------------------------------------------------------

	public function attribute(){
	
	// what filter to produce ?
	
	// what is the matrix attribute_name col_id  & attribute_value col_id of this filter ?
	$field_id = ee()->TMPL->fetch_param('field_id');
	$col_id   = 'col_id_'.ee()->TMPL->fetch_param('col_id');
	
	$variables[] = array(				
	'filter_option_value' => ""
	);
		
	//  GET ALL THE VALUES AVAILABLE FOR THIS FILTER TITLE.		
	ee()->db->distinct();
	ee()->db->select($col_id);
	ee()->db->where($col_id.' != ',"");
//	ee()->db->where('field_id',$field_id); will use this later
	$query = ee()->db->get('exp_matrix_data');

	if ($query->num_rows() > 0){
		
	foreach ($query->result() as $row)
	{
	// generate a checkbox
	$variables[] = array(				
	'filter_option_value' => $row->$col_id
    );
	}
		
	}
	
	return ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $variables);
	
	}
	
	// check if fabrics exists for this category
	public function check(){
	
	$main_cat = ee()->TMPL->fetch_param('main_cat');
	
	// check if fabric exists
	$query = ee()->db->query("select distinct(ls_channel_grid_field_89.col_id_24), ls_category_posts.cat_id 
	From ls_channel_grid_field_89,ls_category_posts
	Where 
	ls_channel_grid_field_89.entry_id = ls_category_posts.entry_id AND
	ls_category_posts.cat_id = '.$main_cat.'
	group by ls_category_posts.cat_id");
	
	if ($query->num_rows() > 0){
	return 'y';
	}else{
	return 'n';
	}
	}
	
	public function grid_attribute(){
	
	// what filter to produce ?
	
	// what is the matrix attribute_name col_id  & attribute_value col_id of this filter ?
	$field_id = ee()->TMPL->fetch_param('field_id');
	$grid_field  = 'channel_grid_field_'.ee()->TMPL->fetch_param('grid_field_id');
	$col_id   = 'col_id_'.ee()->TMPL->fetch_param('col_id');
	
	$variables[] = array(				
	'filter_option_value' => ""
	);
		
	//  GET ALL THE VALUES AVAILABLE FOR THIS FILTER TITLE.		
	ee()->db->distinct();
	ee()->db->select($col_id);
	ee()->db->where($col_id.' != ',"");
//	ee()->db->where('field_id',$field_id); will use this later
	$query = ee()->db->get($grid_field);

	if ($query->num_rows() > 0){
		
	foreach ($query->result() as $row)
	{
	
	// generate a checkbox
	$variables[] = array(				
	'filter_option_value' => $row->$col_id
    );
	}
		
	}
	
	return ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $variables);
	
	}
	
	
	
	
	
	public function brand(){
	$field_id   = 'field_id_'.ee()->TMPL->fetch_param('field_id');
	ee()->db->distinct();
	ee()->db->select($field_id);
	ee()->db->where($field_id.' != ',"");
	$query = ee()->db->get('exp_channel_data');

	if ($query->num_rows() > 0){
		
	foreach ($query->result() as $row)
	{
	// generate a checkbox
	$variables[] = array(				
	'option_value' => $row->$field_id
    );
	}
		
	}
	return ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $variables);
	}
		
		
		
}
/* End of file mod.layered_navigation.php */
/* Location: /system/expressionengine/third_party/layered_navigation/mod.layered_navigation.php */