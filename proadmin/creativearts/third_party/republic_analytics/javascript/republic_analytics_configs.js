/*jslint vars: true, undef: true, browser: true */
/*global jQuery, $, Modernizr, Placeholder, window, Lectric, helpers, methods, console */

$(document).ready(function () {
	"use strict";
	$('#another_account').click(function () {
		$("#google_username_tr").show();
		$("#google_password_tr").show();
		$("#already_logged_in").hide();
		$("#google_account_tr").hide();
		return false;
	});

	$('#visits_color').jPicker({
		images : {
			clientPath: $("#theme_url").html() + 'images/' // Path to image files
		}
	});

	$('#visitors_color').jPicker({
		images : {
			clientPath: $("#theme_url").html() + 'images/' // Path to image files
		}
	});

	$('#pages_view_color').jPicker({
		images : {
			clientPath: $("#theme_url").html() + 'images/' // Path to image files
		}
	});

});
