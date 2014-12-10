<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$plugin_info = array(
	'pi_name'		=> 'Cartthrob Grid Image Fix',
	'pi_version'	=> '3.0',
	'pi_author'		=> 'Raj Sadh',
	'pi_author_url'	=> 'https://github.com/sajwal/cartthrob_gridimage_fix',
	'pi_description'=> 'Fix for cartthrob bug while fetching grid images thumb',
	'pi_usage'		=> Carthrob_gridimage_fix::usage()
);


class Carthrob_gridimage_fix {

	public $return_data;
    
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
	}
	
	
	public function image(){			
				
		// define the return data
		$this->return_data = $this->EE->TMPL->tagdata;		
		$entry_id = $this->EE->TMPL->fetch_param('entry_id');
		$grid_field_id = $this->EE->TMPL->fetch_param('grid_field_id');
		$grid_col_id = 'col_id_'.$this->EE->TMPL->fetch_param('grid_col_id');
		$type = $this->EE->TMPL->fetch_param('type');
		
		
		$this->EE->db->select($grid_col_id);	
		$this->EE->db->where('entry_id',$entry_id);	
		$this->EE->db->limit(1);
		$query = $this->EE->db->get('exp_channel_grid_field_'.$grid_field_id);									
		
		if ($query->num_rows() == 0)
		{
		return $this->EE->TMPL->no_results();
		}
		
		ee()->load->library('typography');	
		ee()->typography->parse_images = TRUE;
	
	
		
		foreach($query->result() as $row){
		
		$file = explode('}',$row->$grid_col_id);
		$folder = explode('{',$file[0]);
		
		$filedir = $folder[1];
		$file_name = $file[1];
		
		$str = ee()->typography->parse_file_paths(LD.$filedir.RD);
		
		$variables[] = array(
				'image' => $str.($type != '' ? '_'.$type.'/' : '').$file_name
			);
		}
		return $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $variables);	
	}
	
	


	
	// ----------------------------------------------------------------
	
	/**
	 * Plugin Usage
	 */
	public static function usage()
	{
		ob_start();
?>
// TAG :
{exp:carthrob_gridimage_fix:image 
entry_id='{entry_id}' 
grid_field_id ='your_grid_field_id' 
grid_col_id='your_grid_col_id' 
type='thumbs'
}

{image}

{/exp:carthrob_gridimage_fix:image}

// PARAMETERS :
# entry_id (Required)
The entry id of the entry to fetch the grid image data for


# grid_field_id (Required)
Your grid field id (You can get this from the control panel)

# grid_col_id (Required)
Your grid field col id (You can get this from the control panel)

# type (Optional)

If you are using different file manipulations..you may specify the name of the manipulated image here (Eg : thumbs, medium, large etc.)




<?php
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
}
