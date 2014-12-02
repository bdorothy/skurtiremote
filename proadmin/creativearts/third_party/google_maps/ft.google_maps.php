<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Google_maps_ft extends EE_Fieldtype {
	
	var $info = array(
		'name'		=> 'Google Maps',
		'version'	=> '1.0'
	);
	
	// --------------------------------------------------------------------
	
	/**
	 * Display Field on Publish
	 *
	 * @access	public
	 * @param	existing data
	 * @return	field html
	 *
	 */
	function display_field($data)
	{
		$data_points = array('latitude', 'longitude', 'zoom');
		
		if ($data)
		{
			list($latitude, $longitude, $zoom) = explode('|', $data);
		}
		else
		{
			foreach($data_points as $key)
			{
				$$key = $this->settings[$key];
			}
		}
		
		$zoom = (int) $zoom;
		$options = compact($data_points);

		$this->EE->cp->add_to_head('<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>');

		$this->EE->javascript->set_global('gmaps.'.$this->field_name.'.settings', $options);
		$this->EE->javascript->output('
			var fieldOpts = EE.gmaps.'.$this->field_name.'.settings,
				myLatlng = new google.maps.LatLng(fieldOpts.latitude, fieldOpts.longitude),
				myZoom = fieldOpts.zoom,
				hidden_field = $("#'.$this->field_name.'");

			var myOptions = {
				zoom: myZoom,
				center: myLatlng,
				scrollwheel: false,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			}
			
			map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
			update_hidden = function() {
				var latlng = map.getCenter();
				
				hidden_field.val(latlng.lat()+"|"+latlng.lng()+"|"+map.getZoom());
			}
			
			google.maps.event.addListener(map, "center_changed", update_hidden);
			google.maps.event.addListener(map, "zoom_changed", update_hidden);
		');
		
		$value = implode('|', array_values($options));
		$hidden_input = form_input($this->field_name, $value, 'id="'.$this->field_name.'" style="display: none;"');
		
		return $hidden_input.'<div style="height: 500px;"><div id="map_canvas" style="width: 100%; height: 100%"></div></div>';
	}
	
	// --------------------------------------------------------------------
		
	/**
	 * Replace tag
	 *
	 * @access	public
	 * @param	field contents
	 * @return	replacement text
	 *
	 */
	function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		static $script_on_page = FALSE;
		$ret = '';

		list($latitude, $longitude, $zoom) = explode('|', $data);
		
		if ( ! $script_on_page)
		{
			$ret .= '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>';
			$script_on_page = TRUE;
		}
		
		// this javascript is for demonstration purposes only
		// you should not assign window.onload directly
		
		$ret .= '<script type="text/javascript">
			function initialize() {
			    var latlng = new google.maps.LatLng('.$latitude.', '.$longitude.');
			    var myOptions = {
			      zoom: '.$zoom.',
			      center: latlng,
			      mapTypeId: google.maps.MapTypeId.ROADMAP
			    };
			    var map = new google.maps.Map(document.getElementById("map_canvas_'.$this->field_id.'"), myOptions);
			}
			window.onload = initialize;
		</script>';
		
		return $ret.'<div style="height: 500px;"><div id="map_canvas_'.$this->field_id.'" style="width: 100%; height: 100%"></div></div>';
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Display Global Settings
	 *
	 * @access	public
	 * @return	form contents
	 *
	 */
	function display_global_settings()
	{
		$val = array_merge($this->settings, $_POST);
		
		// Add script tags
		$this->_cp_js();
		$this->EE->javascript->output('$(window).load(gmaps);');
		
		$form = '';
		
		$form .= '<h3>Default Map</h3>';
		$form .= '<div style="height: 500px;"><div id="map_canvas" style="width: 100%; height: 100%"></div></div>';
		
		$form .= '<br /><h4>Manual Override</h4>';
		$form .= form_label('latitude', 'latitude').NBS.form_input('latitude', $val['latitude']).NBS.NBS.NBS.' ';
		$form .= form_label('longitude', 'longitude').NBS.form_input('longitude', $val['longitude']).NBS.NBS.NBS.' ';
		$form .= form_label('zoom', 'zoom').NBS.form_dropdown('zoom', range(1, 20), $val['zoom']);

		return $form;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Save Global Settings
	 *
	 * @access	public
	 * @return	global settings
	 *
	 */
	function save_global_settings()
	{
		return array_merge($this->settings, $_POST);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Display Settings Screen
	 *
	 * @access	public
	 * @return	default global settings
	 *
	 */
	function display_settings($data)
	{
		$latitude	= isset($data['latitude']) ? $data['latitude'] : $this->settings['latitude'];
		$longitude	= isset($data['longitude']) ? $data['longitude'] : $this->settings['longitude'];
		$zoom		= isset($data['zoom']) ? $data['zoom'] : $this->settings['zoom'];

		$this->EE->table->add_row(
			lang('latitude', 'latitude'),
			form_input('latitude', $latitude)
		);
		
		$this->EE->table->add_row(
			lang('longitude', 'longitude'),
			form_input('longitude', $longitude)
		);
		
		$this->EE->table->add_row(
			lang('zoom', 'zoom'),
			form_dropdown('zoom', range(1, 20), $zoom)
		);
		
		// Map preview
		$this->_cp_js();
		$this->EE->javascript->output(
			// Map container needs to be visible when you create
			// the map, so we'll wait for activate to fire once
			'$("#ft_google_maps").one("activate", gmaps);'
		);
		
		$this->EE->table->add_row(
			lang('preview'),
			'<div style="height: 300px;"><div id="map_canvas" style="width: 100%; height: 100%"></div></div>'
		);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Save Settings
	 *
	 * @access	public
	 * @return	field settings
	 *
	 */
	function save_settings($data)
	{
		return array(
			'latitude'	=> $this->EE->input->post('latitude'),
			'longitude'	=> $this->EE->input->post('longitude'),
			'zoom'		=> $this->EE->input->post('zoom')
		);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Install Fieldtype
	 *
	 * @access	public
	 * @return	default global settings
	 *
	 */
	function install()
	{
		return array(
			'latitude'	=> '44.06193297865348',
			'longitude'	=> '-121.27584457397461',
			'zoom'		=> 13
		);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Control Panel Javascript
	 *
	 * @access	public
	 * @return	void
	 *
	 */
	function _cp_js()
	{
		// This js is used on the global and regular settings
		// pages, but on the global screen the map takes up almost
		// the entire screen. So scroll wheel zooming becomes a hindrance.
		
		$this->EE->javascript->set_global('gmaps.scroll', ($_GET['C'] == 'content_admin'));
		
		$this->EE->cp->add_to_head('<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>');
		$this->EE->cp->load_package_js('cp');
	}
}

/* End of file ft.google_maps.php */
/* Location: ./system/expressionengine/third_party/google_maps/ft.google_maps.php */