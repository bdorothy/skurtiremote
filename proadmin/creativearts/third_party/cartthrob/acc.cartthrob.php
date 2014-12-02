<?php
class Cartthrob_acc {

    var $name       = 'CartThrob Accessory';
    var $id         = 'ct_support';
    var $version        = '1.0';
    var $description    = 'Outputs CartThrob Sales & Support related information.';
    var $sections       = array();

	var $support_expires = 24; 
	var $helpspot_url = NULL; 
    /**
     * Constructor
     */
    function __construct()
    {
        $this->EE =& get_instance();

		$this->EE->lang->loadfile('cartthrob');
    	
		include_once PATH_THIRD.'cartthrob/config.php';
		
		$this->name = lang('cartthrob_accessory_name'); 
		
		if (!defined("CARTTHROB_HELPSPOT_URL"))
		{
			$this->helpspot_url = "https://mightyliverobot.com/support/api/index.php"; 
		}
		else
		{
			$this->helpspot_url = CARTTHROB_HELPSPOT_URL; 
		}
	}

	function set_sections()
	{
		if ( $this->section_support($status="1") )
		{
			$this->name = "<span style='color:red'>".$this->name."</span>";  
 		    $this->sections[lang('cartthrob_support_notifications')] = '<a href="'.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cartthrob'.AMP.'method=support'.'">'.lang('cartthrob_support_new_notifications').'</a>'; 
		}
	}
	
	function section_support($status="1")
	{
		$this->EE->load->helper("array");
		include_once PATH_THIRD.'cartthrob/libraries/HelpSpotAPI'.EXT;

		$query = $this->EE->db->get('helpspot_support'); 
 	
 		$hsapi = new HelpSpotAPI(array("helpSpotApiURL" => $this->helpspot_url)); 

 		$requests = array(); 
		if ($query->result() && $query->num_rows() > 0)
		{
			$q = $query->result_array(); 
			$q = array_reverse($q); 
			reset($q); 
			foreach ($q as $row)
			{
				$result = $hsapi->requestGet(array(
								'output'=> 'PHP',
								'accesskey'		=> (string) $row['access_key'],
 							));
  				
				if (!empty($result['request']))
				{
					if ($status)
					{
						if ($status == element('fOpen', $result['request']))
						{
							if (element('request_history', $result['request']))
							{
								foreach (element('request_history', $result['request']) as $it)
								{
									$current_time = @time(); 
									if (element('dtGMTChange', $it) > ($current_time - ($this->support_expires *60 *60 )) )
									{
										$result['request']['accesskey']  = (string) $row['access_key']; 
										#$requests[]= $result['request'];
										return $result['request'];
										continue 2;
									}
								}
							}
						}
 					}
				}
 			}

			return $requests; 
		}
		
		return FALSE;
	}
}
// END CLASS