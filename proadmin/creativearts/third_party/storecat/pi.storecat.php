<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$plugin_info = array(
	'pi_name'		=> 'StoreCat',
	'pi_version'	=> '3.2.0',
	'pi_author'		=> 'Liveshop',
	'pi_author_url'	=> 'http://liveshop.in',
	'pi_description'=> 'Get all children categories of a given category',
	'pi_usage'		=> Storecat::usage()
);


class Storecat {

	public $return_data;
    
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->EE->load->library('typography');
		$this->EE->typography->initialize();
		$this->EE->typography->parse_images = TRUE;
		
		$this->EE->typography->allow_headings = FALSE;
	}
	
	

	public function subcategory(){	
		// define the return data
		$this->return_data = $this->EE->TMPL->tagdata;	
		
		$parent = $this->EE->TMPL->fetch_param('parent');	
		
		$not = $this->EE->TMPL->fetch_param('not');
		
		
		/*$query = $this->EE->db->query('
									SELECT DISTINCT cat_name,cat_url_title
									FROM ls_categories
									WHERE parent_id = '.$parent.'
									');
			
									*/
									
		ee()->db->distinct();							
		$this->EE->db->select('exp_categories.cat_name,exp_categories.cat_id,exp_categories.cat_url_title');	
		$this->EE->db->from('exp_categories');
		$this->EE->db->where('parent_id',$parent);	
		
		if($not != ""){
		$this->EE->db->where('exp_categories.parent_id != ',$not);
		}
		// DO NOT SHOW EMPTY CATEGORIES
		ee()->db->join('exp_category_posts', 'exp_category_posts.cat_id = exp_categories.cat_id');
		$query = ee()->db->get();
		
									
		
		if ($query->num_rows() == 0)
		{
		return $this->EE->TMPL->no_results();
		}
		
		// loading the typos
		
		
		
	 
		foreach($query->result() as $row){
		$variables[] = array(
				'child_category' => $row->cat_name,
				'child_category_url' => $row->cat_url_title,
				'child_cat_id' => $row->cat_id,
				'total_rows'	=> $query->num_rows()
			);
		}
		return $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $variables);	
	}
	
	
	public function total($parent = ""){
	$parent = ($parent != "" ? $parent : $this->EE->TMPL->fetch_param('parent'));
	
	$count = $this->EE->db->where('parent_id',$parent)
	->count_all_results('exp_categories');	
	return $count;		
	}
	
	
	public function needlehaystack(){
	$needle = ee()->TMPL->fetch_param('needle');
	$haystack = explode('|',ee()->TMPL->fetch_param('haystack'));
	if (in_array($needle, $haystack)) {
    return "checked";
	}
	}
	
	public function category_heading(){
	$cat_id = ee()->TMPL->fetch_param('cat_id');
	$cat_id = explode('|',$cat_id);
	ee()->db->select('cat_id,cat_name,cat_url_title,cat_description,cat_image,parent_id');
	ee()->db->where_in('cat_id',$cat_id);
	$query = ee()->db->get('exp_categories');
	if ($query->num_rows() > 0){ 
	foreach ($query->result() as $row){
	$row = $query->row();
	$variables[] = array(
				'cat_name' => $row->cat_name,
				'cat_description' => $row->cat_description,
				'cat_url_title' => $row->cat_url_title,
				'cat_image'	=> ee()->typography->parse_file_paths($row->cat_image),
				'cat_parent_id' => $row->parent_id
			);
	}		
	return $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $variables);		
			
	} 
	}
	
	
	public function get_cat_name(){
	$cat_id = ee()->TMPL->fetch_param('cat_id');
	ee()->db->select('cat_name');
	ee()->db->where('cat_id',$cat_id);
	$query = ee()->db->get('exp_categories');
	if ($query->num_rows() > 0){ 
	$row = $query->row();	
	return $row->cat_name;
	} 
	}

	
	public function filter()
	{
	$filter = ee()->input->get_post('filter');
	if ((isset($filter)) && ($filter != "")){
	$filter = explode('|',$filter);
	$variables[] = array(
				'orderby' => $filter[0],
				'sort' => $filter[1]
				);
	return $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $variables);
	}
	}
	
	
	public function limit()
	{
	$limit = ee()->input->get_post('limit');
	if ((isset($limit)) && ($limit != "")){
	return $limit;
	}else{
	return 32;
	}
		
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Plugin Usage
	 */
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
