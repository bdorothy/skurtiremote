<?php

/**
 * Config file for Channel Ratings
 *
 * @package			DevDemon_ChannelRatings
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_ratings/
 * @see				http://ee-garage.com/nsm-addon-updater/developers
 */

if ( ! defined('CHANNELRATINGS_NAME'))
{
	define('CHANNELRATINGS_NAME',         'Channel Ratings');
	define('CHANNELRATINGS_CLASS_NAME',   'channel_ratings');
	define('CHANNELRATINGS_VERSION',      '4.0.8');
}

$config['name'] 	= CHANNELRATINGS_NAME;
$config["version"] 	= CHANNELRATINGS_VERSION;
$config['nsm_addon_updater']['versions_xml'] = 'http://www.devdemon.com/'.CHANNELRATINGS_CLASS_NAME.'/versions_feed/';

/* End of file config.php */
/* Location: ./system/expressionengine/third_party/channel_ratings/config.php */
