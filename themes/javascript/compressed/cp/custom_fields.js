/*!
 * Creativearts - by Creativelab
 *
 * @package		Creativearts
 * @author		Creativelab Dev Team
 * @copyright	Copyright (c) 2003 - 2014, Creativelab, Inc.
 * @license		http://creativelab.com/creativearts/user-guide/license.html
 * @link		http://creativelab.com
 * @since		Version 2.0
 * @filesource
 */
"use strict";$.ee_custom_field_select=function(){$("input.input-copy").change(function(){$(this).val($(this).data("original"))}),$("input.input-copy").click(function(){var t=$(this);setTimeout(function(){t.select()},1)})},$(document).ready(function(){$.ee_custom_field_select()});