<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$plugin_info = array(
	'pi_name'		=> 'StoreCat',
	'pi_version'	=> '3.2.0',
	'pi_author'		=> 'Liveshop',
	'pi_author_url'	=> 'http://liveshop.in',
	'pi_description'=> 'Filterable Attributes on layered navigation on frontend',
	'pi_usage'		=> Ls_layered_navigation::usage()
);


class Ls_layered_navigation {

	public $return_data;
    
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->EE->load->library('typography');
		$this->EE->typography->initialize();		
	}
	
	

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
	
	
	
	public function grid_attribute(){	
	// what filter to produce ?
	// what is the matrix attribute_name col_id  & attribute_value col_id of this filter ?
	
	$grid_field  = 'channel_grid_field_'.ee()->TMPL->fetch_param('grid_field_id').' a';
	$col_id   = 'col_id_'.ee()->TMPL->fetch_param('col_id');	
	
	$entry_id = ee()->TMPL->fetch_param('entry_id');
	$main_category = ee()->TMPL->fetch_param('main_category');
	
	$variables[] = array(				
	'filter_option_value' => ""
	);
		
	// select all attributes values for given attribute title for this entry where the attribute value belongs to this entry's main category.
	$query = ee()->db->distinct();
	ee()->db->select('a.'.$col_id);	
	ee()->db->from($grid_field);
	if((isset($main_category)) && ($main_category != "")){	
	ee()->db->join('exp_category_posts b','a.entry_id = b.entry_id');	
	ee()->db->where('b.cat_id',$main_category);
	}
	$query = ee()->db->get();
	
	if ($query->num_rows() > 0){
	
	foreach ($query->result() as $row)
	{
    $variables[] = array(
				'attribute_value' => $row->$col_id,
				'attribute_name' =>  ucfirst($row->$col_id),
				);	   
	}
	return $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $variables);	
	}
	
	}	 
	
	public function get_limit(){
	$limit = ee()->input->get('limit');
	if (isset($limit)){
	$limit = ee()->input->get('limit');
	}else{
	$limit = 32;
	}
	return $limit;
	}
	
	
	public function get_colors(){	
	// what filter to produce ?
	// what is the matrix attribute_name col_id  & attribute_value col_id of this filter ?
	
	$grid_field  = 'channel_grid_field_'.ee()->TMPL->fetch_param('grid_field_id').' a';
	$col_id   = 'col_id_'.ee()->TMPL->fetch_param('col_id');	
	
	$entry_id = ee()->TMPL->fetch_param('entry_id');
	$main_category = ee()->TMPL->fetch_param('main_category');
	
	
		
	// select all colors
	$query = ee()->db->distinct();
	ee()->db->select('a.'.$col_id);	
	ee()->db->from($grid_field);
	if((isset($main_category)) && ($main_category != "")){	
	ee()->db->join('exp_category_posts b','a.entry_id = b.entry_id');	
	ee()->db->where('b.cat_id',$main_category);
	}
	$query = ee()->db->get();
	
	if ($query->num_rows() > 0){
	
	foreach ($query->result() as $row)
	{
	$colors[] = $row->$col_id;
       
	}
	$colors = implode('|',$colors);
	$colors = explode('|',$colors);
	$colors = array_unique($colors);
	
	foreach ($colors as $color)
	{
    $variables[] = array(
				'attribute_value' => $color,
				'attribute_name' =>  ucfirst($color),
				);	   
	}
	
	return $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $variables);	
	}
	
	}
	 
	 
	public static function usage()
	{
		ob_start();
?>

{storecat parent="X"}
<?php
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
}
