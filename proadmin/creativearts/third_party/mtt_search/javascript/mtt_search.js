/**
 * Mtt Search JS file
 *
 * @package        mtt_search
 * @author         MyTopTeacher <contact@mytopteacher.com>
 * @link           http://mytopteacher.com/addons/mtt-search
 * @copyright      Copyright (c) 2014, Mtt
 */

// Make sure LOW namespace is valid
if (typeof LOW == 'undefined') var LOW = new Object;

(function($){

// --------------------------------------
// Language lines
// --------------------------------------

var lang = function(str) {
	return (typeof $.MTT_lang[str] == 'undefined') ? str : $.MTT_lang[str];
}

// --------------------------------------
// Create Mtt Index object
// --------------------------------------

LOW.Index = function(cell) {

	var self  = this,
		$cell = $(cell),
		$link = $cell.find('a'),
		$bar  = $cell.find('.index-progress-bar'),
		url   = $link.attr('href'),
		vars  = {start: 0},
		EE280 = (typeof EE.CSRF_TOKEN != 'undefined'),
		csrf_token_name   = (EE280 ? 'CSRF_TOKEN' : 'XID'),
		csrf_token_header = (EE280 ? 'X-CSRF-TOKEN' : 'X-EEXID');

	// Add csrf_token to vars
	vars[csrf_token_name] = EE[csrf_token_name];

	// Function to execute after each Ajax call
	var update = function(data, status, xhr) {
		// Set new csrf_token and start values
		var csrf_token_value = xhr.getResponseHeader(csrf_token_header) || null;
		if (csrf_token_value) vars[csrf_token_name] = csrf_token_value;
		vars.start = data.start;
		// Get new progress bar width and text
		var w = (data.status == 'done')     ? 'auto' : (data.start / data.total_entries * 100) + '%';
		var t = (data.status == 'building') ? data.start+' / '+data.total_entries : lang(data.status);
		$bar.css('width', w).text(t);
		// Add status to parent cell
		$cell.addClass(data.status);
		// Not done? Build again. Done? Trigger event.
		if (data.status != 'done') {
			buildBatch(false);
		} else {
			self.oncomplete();
		}
	};

	// Perform Ajax Call for this batch
	var buildBatch = function(rebuild) {
		// Set rebuild var accordingly
		vars.rebuild = rebuild ? 'yes' : false;
		// Data to post
		$.post(url, vars, update, 'json').error(function(){
			$bar.text('An error occurred rebuilding the index. Try again later.');
		});
	};

	// Callable build function to trigger the build
	this.build = function(rebuild){
		if ( ! $cell.hasClass('ready')) return self.oncomplete();
		// Remove tick, display feedback message
		$cell.removeClass('ready').addClass('loading');
		// Call function
		buildBatch(rebuild);
	};

	// Add event to link to trigger rebuild
	$link.click(function(event){
		event.preventDefault();
		self.oncomplete = function(){};
		self.build(event.altKey);
	});

	this.oncomplete = function(){};

	return this;
};

// --------------------------------------
// Controller for collections/indexes
// --------------------------------------

LOW.Collections = function() {

	var index = [];

	$('td.-index').each(function(){
		if ($(this).find('a').length) {
			index.push(new LOW.Index(this));
		}
	});

	$('a#build-all-indexes').click(function(event){
		event.preventDefault();
		var $cell = $(this).parent();
		$cell.text(lang('working'));
		$(index).each(function(i){
			var next = index[i + 1];
			index[i].oncomplete = function(){
				if (next) {
					next.build();
				} else {
					$cell.text(lang('done'));
				}
			};
		});
		index[0].build();
	});
};

$(LOW.Collections);

// --------------------------------------
// Collection Settings
// --------------------------------------

LOW.CollectionSettings = function() {

	// Show search fields in Edit Collection screen
	var show_fields = function() {
		var val = $('#collection_channel').val();
		$('.mtt-search-collection-settings').addClass('hidden');
		if (val) {
			$('#mtt-search-channel-'+val).removeClass('hidden');
			if ( ! $('#collection_id').val()) {
				$('#collection_label').val(EE.mtt_search_channels[val].channel_title);
				$('#collection_name').val(EE.mtt_search_channels[val].channel_name);
			}
		}
	};

	show_fields();
	$('#collection_channel').change(show_fields);
};

$(LOW.CollectionSettings);

// ------------------------------------------
// Sortable shortcuts
// ------------------------------------------

LOW.Sortcuts = function(){
	var $table = $('#mtt-search-shortcuts');

	if ( ! $table.length) return;

	var $cols = $table.find('col'),
		$tbody = $table.find('tbody');

	// Set styles for TDs, so row stays same wit
	$tbody.find('tr').each(function(){
		$(this).find('td').each(function(i){
			var w = $($cols.get(i)).css('width');
			$(this).css('width', w);
		});
	});

	// Callback function after sorting
	var sorted = function(event, ui) {
		var order = [];

		$tbody.find('tr').each(function(i){
			var $tr = $(this),
				i = i + 1;

			// Correct zebra striping
			$tr.removeClass((i % 2 ? 'even' : 'odd'));
			$tr.addClass((i % 2 ? 'odd' : 'even'));

			// Add to orders
			order.push($tr.data('id'));
		});

		// Compose URL for Ajax call
		var url = location.href.replace('method=shortcuts', 'method=order_shortcuts');

		// Post it
		$.post(url, {
			'XID': EE.XID,
			'order': order
		});
	};

	// Make the rows sortable
	$tbody.sortable({
		axis: 'y',
		containment: $('#mainContent'),
		items: 'tr',
		handle: '.drag-handle',
		update: sorted
	});
};

$(LOW.Sortcuts);

// ------------------------------------------
// Shortcut Parameters
// ------------------------------------------

LOW.Params = function(){
	var $tmpl = $('#parameter-template'),
		$add  = $('#parameters button');

	var addFilter = function(event, key, val) {
		// Clone the filter template and remove the id
		var $newFilter = $tmpl.clone().removeAttr('id');

		// If a key is given, set it
		if (key) $newFilter.find('.param-key').val(key);

		// If a val is given, set it
		if (val) $newFilter.find('.param-val').val(val);

		// Add it just above the add-button
		$add.before($newFilter);

		// If it's a click event, slide down the new filter,
		// Otherwise just show it
		if (event) {
			event.preventDefault();
			$newFilter.slideDown(100);
		} else {
			$newFilter.show();
		}

		$newFilter.find('.param-key').focus();
	};

	// If we have reorder fields pre-defined, add them to the list
	if (typeof MTT_Search_parameters != 'undefined') {
		for (var i in MTT_Search_parameters) {
			addFilter(null, i, MTT_Search_parameters[i]);
		}
	}

	// Enable the add-button
	$add.click(addFilter);

	// Enable all future remove-buttons
	$('#parameters').delegate('button.remove', 'click', function(event){
		event.preventDefault();
		$(this).parent().remove();
	});
};

$(LOW.Params);

// ------------------------------------------
// Search Log
// ------------------------------------------

LOW.SearchLog = function() {
	var $cells = $('td.params'),
		$th = $('#params-header'),
		open = false;

	$cells.each(function(){
		var $td   = $(this),
			$more = $('<span>&hellip;</span>')
			$lis  = $td.find('li');

		if ($lis.length > 1) {
			$lis.first().append($more);
			$td.on('click', function(){
				$td.toggleClass('open');
			}).addClass('has-more');
		}
	});

	$th.on('click', function(){
		var method = open ? 'removeClass' : 'addClass';
		$cells.filter('.has-more')[method]('open');
		open = ! open;
	});
};

$(LOW.SearchLog);

// ------------------------------------------
// Find & Replace functions
// ------------------------------------------

$(function(){

	// Tabs
	$('#-tabs a').click(function(event){
		event.preventDefault();
		$('#-tabs li').removeClass('active');
		$('fieldset.tab').removeClass('active');
		$(this).parent().addClass('active');
		$($(this).attr('href')).addClass('active');
	});

	// Remember preview element
	var $preview  = $('#-preview');

	// Get dialog element
	var $dialog   = $('#-dialog');

	// Channel / field selection options
	$('#-filters fieldset').each(function(){

		// Define local variables
		var $self      = $(this),
			$sections  = $self.find('div.-boxes'),
			$allBoxes  = $self.find('input[name]'),
			$selectAll = $self.find('input.-select-all');

		// Define channel object: to (de)select all fields that belong to the channel
		var Section = function(el) {
			var $el     = $(el),
				$boxes  = $el.find(':checkbox'),
				$toggle = $el.find('h4 span');

			// Add toggle function to channel header
			$toggle.click(function(event){
				event.preventDefault();
				var $unchecked = $el.find('input:not(:checked)');

				if ($unchecked.length) {
					$unchecked.attr('checked', true);
				} else {
					$boxes.attr('checked', false);
				}
			});
		};

		// Init channel object per one channel found in main element
		$sections.each(function(){
			new Section(this);
		});

		// Enable the (de)select all checkbox
		$selectAll.change(function(){
			var check = ($selectAll.attr('checked') ? true : false);
			$allBoxes.attr('checked', check);
		});
	});



	// Show preview of find & replace action
	$('#-find-replace').submit(function(event){

		// Don't believe the hype!
		event.preventDefault();

		// Set local variables
		var $form = $(this),
			$keywords = $('#-keywords');

		// Validate keywords
		if ( ! $keywords.val()) {
			$.ee_notice(lang('no_keywords_given'),{type:"error",open:true});
			return;
		}

		// Validate field selection
		if ( ! $('#-channel-fields :checked').length) {
			$.ee_notice(lang('no_fields_selected'),{type:"error",open:true});
			return;
		}

		// Turn on throbber, empty out preview
		$.ee_notice.destroy();

		$preview.html(lang('working'));

		// Submit form via Ajax, show result in Preview
		$.post(
			this.action,
			$(this).serialize(),
			function(data){
				$preview.html(data);
		});
	});

	// (de)select all checkboxes in preview table
	$preview.delegate('#-select-all', 'change', function(){
		var $tbody = $(this).parents('table').find('tbody');
		$tbody.find(':checkbox').attr('checked', this.checked);
	});

	// Form submission after previewing
	$preview.delegate('#-previewed-entries', 'submit', function(event){

		// Don't believe the hype!
		event.preventDefault();

		// Set local vars
		var $form = $(this);

		// Validate checked entries, destroy notice if okay
		if ( ! $form.find('tbody :checked').length) {
			$.ee_notice(lang('no_entries_selected'),{type:"alert",open:true});
			return;
		}

		// Show message in preview
		$.ee_notice.destroy();

		$preview.html(lang('working'));

		// Submit form via Ajax, show result in Preview
		$.post(
			this.action,
			$form.serialize(),
			function(data){
				$preview.html(data);
		});
	});

	// Replace log: open details in dialog
	$('.-show-dialog').click(function(event){

		// Don't fol the link
		event.preventDefault();

		// Load details via Ajax, then show in dialog
		$dialog.load(this.href, function(){
			$dialog.dialog({
				modal: true,
				title: $('#breadCrumb .last').text(),
				width: '50%'
			});
		});
	});

	// Toggle hilite title settings
	$('#excerpt_hilite').change(function(){
		var method = $(this).val() ? 'slideDown' : 'slideUp';
		$('#title_hilite')[method](150);
	});

});

})(jQuery);

// --------------------------------------
