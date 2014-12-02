// ********************************************************************************* //
var ChannelRatings = ChannelRatings ? ChannelRatings : {};
ChannelRatings.DataTables = {};
ChannelRatings.DatatablesCols = {};
ChannelRatings.Timeout = 0;
ChannelRatings.IDS = [];
//********************************************************************************* //

$(document).ready(function() {

	// Store for later use
	ChannelRatings.SideBar = jQuery('#crsidebar');
	ChannelRatings.TitleBlock = jQuery('#title_block');
	ChannelRatings.ContentBlock = jQuery('#crcontent');
	ChannelRatings.SideBarColumns = ChannelRatings.SideBar.find('#SideBarColumns');
	ChannelRatings.SideBarFilters = ChannelRatings.SideBar.find('#SideBarFilters');

	// Add Placeholder everywhere
	if ( ('placeholder' in document.createElement('input')) == false){
		$('input[placeholder]').inputHint({fadeOutSpeed: 200, fontFamily:'Helvetica, Arial, sans-serif', fontSize:'12px', hintColor:'#888', padding:'4px'});
	}

	// Activate Tooltips
	jQuery('.bs-tooltip').tooltip({placement:'right'});

	//----------------------------------------
	// MCP: Ratings
	//----------------------------------------
	if (document.getElementById('mcp-ratings') != null)	{

		// Toggle Rating Type
		ChannelRatings.RatingTypeToggler = ChannelRatings.SideBar.find('div.rating_type_toggler');
		ChannelRatings.RatingTypeToggler.delegate('span', 'click', ChannelRatings.ToggleRatingType);

		// Rating State Saved?
		if (localStorage) {
			var RatingType = localStorage.getItem('ChannelRatings_MCP_Rating_Type');
			ChannelRatings.RatingTypeToggler.find('span[data-type='+RatingType+']').trigger('click');
		} else {
			ChannelRatings.RatingTypeToggler.find('span:first').trigger('click');
		}

		ChannelRatings.ContentBlock.delegate('.datatable .CheckAll', 'click', ChannelRatings.ToggleCheckAll);
		ChannelRatings.ContentBlock.delegate('.datatable tbody tr', 'click', ChannelRatings.SelectTableTR);
		ChannelRatings.ContentBlock.delegate('a.EditRating', 'click', ChannelRatings.EditRatingModal);
		ChannelRatings.TitleBlock.delegate('.RatingAction', 'click', ChannelRatings.ExecRatingAction);

		ChannelRatings.SideBar.delegate('.reset', 'click', ChannelRatings.ResetTable);

		ChannelRatings.Timeout = 0;
		ChannelRatings.ContentBlock.delegate('.datatable tbody tr', 'click', ChannelRatings.ToggleTableActions);
	}

	jQuery('#fbody').delegate('.DelIcon', 'click', function(){
		var answer = confirm(ChannelRatings.JSON.Alerts.delete);
		if (!answer) return false;
	});

	jQuery("#slugsource").stringToSlug({
		setEvents: 'keyup keydown blur',
		getPut: '#slugdest',
		space: '_'
	});


});

//********************************************************************************* //

ChannelRatings.ToggleRatingType = function(Event){
	Event.preventDefault();
	var Target = jQuery(Event.target);
	var RatingType = Target.data('type');

	// Store the selection
	if (localStorage) localStorage.setItem('ChannelRatings_MCP_Rating_Type', RatingType);

	if (!Event.force_toggle){
		// Already selected? Fine!
		if (Target.hasClass('label-success') == true) return;
	}

	// Remove the indicator
	ChannelRatings.RatingTypeToggler.find('span').removeClass('label-success');

	// Add it!
	Target.addClass('label-success');

	// Change the page title
	ChannelRatings.TitleBlock.find('h2').html( Target.data('original-title') );

	for (dt in ChannelRatings.DataTables){
		ChannelRatings.DataTables[dt].fnDestroy();
		delete ChannelRatings.DataTables[dt];
	}

	// Params
	var Params = {};
	Params.XID = EE.XID;
	Params.ajax_method = 'rating_type_toggler';
	Params.rating_type = RatingType;
	Params.section = Target.data('section');
	Params.site_id = ChannelRatings.site_id;

	// Send the AJAX request
	jQuery.post(ChannelRatings.AJAX_URL, Params, function(rData){

		// Store the columns
		ChannelRatings.SideBarColumns.empty();
		ChannelRatings.DatatablesCols[ rData.table_name ] = [];
		ChannelRatings.DatatablesCols[ rData.table_name ].push({mDataProp:'rating_id', bSortable: false});

		// Loop over the standard columns
		for (col in rData.columns.standard){
			ChannelRatings.DatatablesCols[ rData.table_name ].push({ mDataProp:col, bSortable:rData.columns.standard[col].sortable });
			ChannelRatings.SideBarColumns.append('<span data-column="'+col+'" class="label">'+rData.columns.standard[col]['name']+'</span>');
		}

		// Loop over the extra columns
		for (col in rData.columns.extra){
			ChannelRatings.DatatablesCols[ rData.table_name ].push({ mDataProp:col, bVisible:false, bSortable:rData.columns.extra[col].sortable });
			ChannelRatings.SideBarColumns.append('<span data-column="'+col+'" class="label">'+rData.columns.extra[col]['name']+'</span>');
		}

		ChannelRatings.SideBarFilters.html(rData.filters);
		ChannelRatings.ContentBlock.html(rData.body);

		// Initialize Datatables
		ChannelRatings.DatatablesInit();

		ChannelRatings.SideBarColumns.delegate('span', 'click', ChannelRatings.DTColumnToggler);
		ChannelRatings.ActivateSidebarFilters();

	}, 'json');

};

//********************************************************************************* //

ChannelRatings.DatatablesInit = function(){

	$('table.datatable').each(function(index, elem){

		// Store, for quick access
		var DTE = $(elem);

		if (DTE.data('disabled') == 'yes') {
			return;
		}

		// Initialize the datatable
		ChannelRatings.DataTables[DTE.data('name')] = DTE.dataTable({
			sPaginationType: 'full_numbers',
			sDom: 'R<"toptable"rl>t<"bottomtable" ip>',
			sAjaxSource: ChannelRatings.AJAX_URL,
			aoColumns: ChannelRatings.DatatablesCols[DTE.data('name')],
			iDisplayLength: 15,
			bProcessing: true,
			fnServerData: function ( sSource, aoData, fnCallback ) {

				// Add XID
				aoData.push( {name: 'ajax_method', value:'ajax_datatable'} );
				aoData.push( {name: 'datatable', value:$(this).data('name') } );
				aoData.push( {name: 'site_id', value: ChannelRatings.site_id } );

				if (EE.CSRF_TOKEN) {
					aoData.push( {name: 'CSRF_TOKEN', value:EE.CSRF_TOKEN } );
				} else {
					aoData.push( {name: 'XID', value:EE.XID } );
				}

				var DT = ChannelRatings.DataTables[ $(this).data('name') ];

				// Add all filters to the POST
				var Filters = ChannelRatings.SideBarFilters.find(':input').serializeArray();
				for (var attrname in Filters) {
					aoData.push( {name: Filters[attrname]['name'], value:Filters[attrname]['value'] } );
				}

				// Send the AJAX request
				$.ajax({dataType:'json', type:'POST', url:sSource, data:aoData, success:function(rData){

					// Give it back
					fnCallback(rData);

					// Recalculate column sizes, if it's not the first time
					if (DT) DT.fnAdjustColumnSizing(false);

					/*

					// If it's the first time, lets do some magic
					else setTimeout(function(){

						// Find all datatables
						$('table.datatable').each(function(i, e){

							if ($(e).data('disabled') == 'yes') return;

							// And Resize their columns!
							ChannelRatings.Datatables[ $(e).data('name') ].fnAdjustColumnSizing(false);
						});
					}, 200);
					*/

				}});

			},
			fnDrawCallback: function(){
				var DT = jQuery(this);
				DT.find('a.EditRating').tooltip({title:ChannelRatings.JSON.Alerts.edit_rating});

				ChannelRatings.IDS = {};
				ChannelRatings.TitleBlock.find('.linkbtn').addClass('disabled');
			},
			fnInitComplete : function(oSettings, json){

				// Remove all column classes
				ChannelRatings.SideBarColumns.find('span').removeClass('label-success');

				// Loop over all columns
				for (col in oSettings.aoColumns) {

					// Is it visible?
					if (oSettings.aoColumns[col].bVisible == true) {
						// Add the class
						ChannelRatings.SideBarColumns.find('span[data-column='+oSettings.aoColumns[col].mDataProp+']').addClass('label-success');
					}
				}
			},
			bServerSide: true,
			oLanguage: {
				sLengthMenu: 'Display <select>'+
					'<option value="15">15</option>'+
					'<option value="25">25</option>'+
					'<option value="50">50</option>'+
					'<option value="100">100</option>'+
					'<option value="-1">All</option>'+
					'</select> records'
			},
			oColReorder: {iFixedColumns:1},
			bStateSave: ((DTE.data('savestate') == 'no') ? false : true),
			fnStateSave: function (oSettings, oData) {
				if (localStorage) localStorage.setItem( 'CR_DataTables_'+jQuery(oSettings.nTable).data('name'), JSON.stringify(oData) );
	        },
	        fnStateLoad: function (oSettings) {
	           if (localStorage) return JSON.parse( localStorage.getItem('CR_DataTables_'+jQuery(oSettings.nTable).data('name')) );
	        },
	        aaSorting: []
		});

		/*

		// Global Filter?
		if (DTE.find('.global_filter input').length > 0){
			DTE.find('.global_filter input').keyup(function(EV){
				clearTimeout(CRM.Timeout);
				CRM.Timeout = setTimeout(function(){
					CRM.Datatables[ DTE.data('name') ].fnFilter($(EV.target).val());
				}, 300);
			});
		}
		*/

	});
};

//********************************************************************************* //

ChannelRatings.DTColumnToggler = function(Event){

	var Target = $(Event.target);
	var ToggledColumn = Target.data('column');

	// Lets grab the first Datatable
	for (D in ChannelRatings.DataTables) {
		var DT = ChannelRatings.DataTables[D];
		break;
	}

	if ( typeof(DT.fnSettings().aoData[0]) != 'undefined' ){

		// Create local var
		var Cols = DT.fnSettings().aoColumns;

		// Loop over all cols
		for(col in Cols){
			if (ToggledColumn == Cols[col].mDataProp){

				// Is the column already visible?
				if (Target.hasClass('label-success') == true) {

					// Make it hidden
					DT.fnSetColumnVis(col, false, false);

					// Re-Calculate the column sizes (and don't fetch new data)
					DT.fnAdjustColumnSizing(false);

					// Remove the class of course
					Target.removeClass('label-success');
				}

				// The column was hidden
				else {
					// Mark it visible!
					DT.fnSetColumnVis(col, true);

					// Re-Calculate the column sizes (and don't fetch new data)
					DT.fnAdjustColumnSizing(false);

					// Add the class of course
					Target.addClass('label-success');
				}

			}
		}

	}

};

//********************************************************************************* //

ChannelRatings.ActivateSidebarFilters = function(){

	jQuery('body > .inputHintOverlay').remove();

	// Add Placeholder everywhere
	if ( ('placeholder' in document.createElement('input')) == false){
		ChannelRatings.SideBarFilters.find('input[placeholder]').inputHint({fadeOutSpeed: 200, fontFamily:'Helvetica, Arial, sans-serif', fontSize:'12px', hintColor:'#888', padding:'4px'});
	}

	// Active Chosen
	ChannelRatings.SideBarFilters.find('.chosen').chosen();

	// Normal Text Inputs
	var TextInput = 0;
	ChannelRatings.SideBarFilters.find('input[type=text]').keyup(function(Event){
		if (Event.target.name == false) return;

		// Clear the timeout
		clearTimeout(TextInput);

		// Trigger a new drawing
		TextInput = setTimeout(function(){
			for (DT in ChannelRatings.DataTables) {
				ChannelRatings.DataTables[DT].fnDraw();
			}
		}, 300);
	});

	// Dropdowns
	ChannelRatings.SideBarFilters.find('select').change(function(Event){
		for (DT in ChannelRatings.DataTables) {
			ChannelRatings.DataTables[DT].fnDraw();
		}
	});

	// Activate Datepickers
	ChannelRatings.SideBarFilters.find('.datepicker').datepicker({dateFormat:'yy-mm-dd', changeYear: true, changeMonth: true, yearRange: '1940:2020', onSelect:function(){
		$(this).trigger('keyup');
	}});


};

//********************************************************************************* //

ChannelRatings.ToggleCheckAll = function(Event){

	// Grab all TR's
	var TRS = jQuery(Event.target).closest('table').find('tbody tr');

	// Is it Checked?
	if (Event.target.checked == true){

		TRS.each(function(i, elem){
			var TR = $(elem);

			// Is it NOT Checked?
			if (TR.hasClass('Checked') == false){
				TR.click();
			}

		});
	}
	else {

		TRS.each(function(i, elem){
			var TR = $(elem);

			// Is it NOT Checked?
			if (TR.hasClass('Checked') == true){
				TR.click();
			}

		});
	}

	delete TR;
	delete TempCheckBox;
};

//********************************************************************************* //

ChannelRatings.SelectTableTR = function(Event){

	var TR = $(this);

	// Only if we can do it
	if (! TR.closest('table').data('checkable')) return;

	// Is it Checked?
	if ( TR.hasClass('Checked') == false ){
		TR.addClass('Checked');
	}
	else {
		TR.removeClass('Checked');
	}
};

//********************************************************************************* //

ChannelRatings.ResetTable = function(Event){

	if (jQuery(Event.target).data('type') == 'columns'){
		var Name = ChannelRatings.ContentBlock.find('.datatable').data('name');

		if (localStorage) localStorage.removeItem('CR_DataTables_'+Name);

		ChannelRatings.RatingTypeToggler.find('span.label-success').trigger({type:"click", force_toggle:'yes'});
	}
};

//********************************************************************************* //

ChannelRatings.EditRatingModal = function(Event){
	Event.preventDefault();

	// Cache the element who called this
	var Target = jQuery(Event.target);

	// Store the Modal Wrapper
	var ModalWrapper = $('#ModalWrapper');

	var Params = {};
	Params.rating_id = Target.data('rid');
	Params.XID = EE.XID;
	Params.ajax_method = 'edit_rating_modal';

	// Open the modal and get it's content
	$.post(ChannelRatings.AJAX_URL, Params, function(rData){

		ModalWrapper.modal().empty().html(rData);

		// Activate Chosen!
		ModalWrapper.find('select.chosen').chosen();

		// Find the first input and focus on it!
		ModalWrapper.find(':input:first').focus();

		// Find the first input and focus on it!
		ModalWrapper.find('.btn-primary').click(ChannelRatings.EditRating);
	});

	// Remove the style attribute, so when we recall it, the fade effect happens
	ModalWrapper.bind('hidden', function () {
		$('#ModalWrapper').removeAttr('style');
	});
};

//********************************************************************************* //

ChannelRatings.EditRating = function(Event){
	Event.preventDefault();

	// Store the Modal Wrapper
	var ModalWrapper = $('#ModalWrapper');

	// Gather all form fields
	var Params = ModalWrapper.find('.modal-body').find(':input').serializeArray();
	Params.push({name:'XID', value:EE.XID});
	Params.push({name:'ajax_method', value:'edit_rating_save'});

	ModalWrapper.find('.btn-primary').addClass('disabled').html(ChannelRatings.JSON.Alerts.saving);

	// Execute the POST
	$.post(ChannelRatings.AJAX_URL, Params, function(rData){

		// Hide the modal!
		ModalWrapper.modal('hide');

		// Loop over all datatables as refresh?
		for (i in ChannelRatings.DataTables) {
			ChannelRatings.DataTables[i].fnDraw();
		};

	}, 'text');
};

//********************************************************************************* //

ChannelRatings.ToggleTableActions = function(Event){

	clearTimeout(ChannelRatings.Timeout);

	var DT = ChannelRatings.ContentBlock.find('.datatable:first');

	ChannelRatings.Timeout = setTimeout(function(){
		if (DT.find('tbody').find('tr.Checked').length > 0){
			ChannelRatings.TitleBlock.find('.linkbtn').removeClass('disabled');
		}
		else {
			ChannelRatings.TitleBlock.find('.linkbtn').addClass('disabled');
		}
	}, 200);

	// Get The Current Index
	var Index = $(this).index();

	// Get Current Row Data
	var Data = ChannelRatings.DataTables[ DT.data('name') ].fnGetData(Index);
	var ELEM = Data.rating_id.match("rid\='(.*?)'");

	if (ELEM != null) var ID = ELEM[1];
	else var ID = Data.rating_id;

	if ( $(this).hasClass('Checked') == true ){
		ChannelRatings.IDS['ID-'+ID] = ID;
	}
	else {
		delete ChannelRatings.IDS['ID-'+ID];
	}

};

//********************************************************************************* //

ChannelRatings.ExecRatingAction = function(Event){
	Event.preventDefault();
	var Target = jQuery(Event.target).closest('a');
	if (Target.hasClass('disabled')) return;

	var Params = {};
	Params.type        =	Target.data('type');
	Params.action      =	Target.data('action');
	Params.ids         =	[];
	Params.XID         =	EE.XID;
	Params.ajax_method =	'rating_action';

	if (Params.action == 'delete'){
		var answer = confirm(ChannelRatings.JSON.Alerts.delete);
		if (!answer) return;
	}

	for (ID in ChannelRatings.IDS){
		Params.ids.push(ChannelRatings.IDS[ID]);
	}

	jQuery('#fbody .dataTables_processing').css({visibility:'visible'});

	jQuery.post(ChannelRatings.AJAX_URL, Params, function(rData){

		// Loop over all datatables as refresh?
		for (i in ChannelRatings.DataTables) {
			ChannelRatings.DataTables[i].fnDraw();
		};

	});
};

//********************************************************************************* //