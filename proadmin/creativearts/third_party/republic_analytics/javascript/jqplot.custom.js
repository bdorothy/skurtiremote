/*jslint vars: true, undef: true, browser: true */
/*global jQuery, $, Modernizr, Placeholder, window, Lectric, helpers, Spinner, EE */
/*
* Retrieve total pageviews and visits for 30 days.
*/

var graphType = "",
	graphTitle = "",
	dateFirst = "",
	dateLast = "",
	lineGooglePageViews = "",
	lineGoogleVisitors = "",
	tickersGoogleVisits = "",
	tickeIntervalGooglePageViews = "",
	tickeIntervalGoogleVisitors = "",
	lineGoogleVisits = "",
	tickeIntervalGoogleVisits = "",
	labelVisits = "",
	labelVisitors = "",
	labelPageViews = "",
	visitsColor = "",
	visitorsColor = "",
	pageViewsColor = "",
	plot = null,
	showPagesViewInTable = false;

function plotGraphLines(sLines, sSeries, sTickInterval) {
	$("#analytics_data").empty();
	plot = $.jqplot('analytics_data', sLines, {
		seriesColors: (showPagesViewInTable === true) ? [visitorsColor, visitsColor, pageViewsColor] : [visitorsColor, visitsColor],
		// title: graphTitle,

		legend: {show: true, location: 'nw'},
		series: sSeries,
		seriesDefaults: {
			showMarker: true,
			pointLabels: { show: false},
			lineWidth: 1.0, // Width of the line in pixels.
			shadow: false,
			markerOptions: {
				show: true,             // wether to show data point markers.
				style: 'filledCircle',  // circle, diamond, square, filledCircle.
				lineWidth: 1,       // width of the stroke drawing the marker.
				size: 5,            // size (diameter, edge length, etc.) of the marker.
				shadow: false,
				color: visitorsColor
			}
		},
		grid: {
			drawGridLines: true,        // wether to draw lines across the grid or not.
			gridLineColor: '#EEEEEE',    // *Color of the grid lines.
			background: '#ffffff',
			shadow: false,
			borderWidth: 0.5
		},
		axes: {
			xaxis: {
				renderer: $.jqplot.DateAxisRenderer,
				rendererOptions: {tickRenderer: $.jqplot.CanvasAxisTickRenderer},
				tickOptions: {
					angle: 0,
					formatString: '%#d %b',
					fontSize: '10px'
				},
				tickInterval: '7 days',
				min: dateFirst,
				max: dateLast
			},
			yaxis: {
				min: 0,
				tickOptions: {formatString: '%.0f'},
				tickInterval: sTickInterval
			}
		},
		highlighter: {
			show: true,
			showTooltip: true,      // show a tooltip with data point values.
			tooltipLocation: 'n',  // location of tooltip: n, ne, e, se, s, sw, w, nw.
			tooltipAxes: 'xy',
			showMarker: false,
			tooltipOffset: "20"
		},
		cursor: {show: false}
	});
}

function plotGraphBar(sLines, sSeries, sTickInterval) {
	$("#analytics_data").empty();
	plot = $.jqplot('analytics_data', sLines, {
		seriesColors: (showPagesViewInTable === true) ? [visitorsColor, visitsColor, pageViewsColor] : [visitorsColor, visitsColor],
		// title: graphTitle,
		series: sSeries,
		legend: {show: true, location: 'nw'},
		stackSeries: true,
		seriesDefaults: {
			renderer: $.jqplot.BarRenderer,
			rendererOptions: {
				// Put a 30 pixel margin between bars.
				barMargin: 1,
				barPadding: 0
			},
			pointLabels: {show: false},
			shadow: false
		},
		grid: {
			drawGridLines: true,        // wether to draw lines across the grid or not.
			gridLineColor: '#EEEEEE',    // *Color of the grid lines.
			background: '#ffffff',
			shadow: false,
			borderWidth: 0.5
		},
		axes: {
			xaxis: {
				renderer: $.jqplot.DateAxisRenderer,
				rendererOptions: {tickRenderer: $.jqplot.CanvasAxisTickRenderer},
				tickOptions: {
					angle: 0,
					formatString: '%#d %b',
					fontSize: '10px'
				},
				tickInterval: '7 days',
				min: dateFirst,
				max: dateLast
			},
			yaxis: {
				min: 0,
				//max: sTickInterval*10,
				tickOptions: {formatString: '%.0f'},
				tickInterval: sTickInterval,
				padMin: 0
			}
		},
		highlighter: {
			show: true,
			showTooltip: true,      // show a tooltip with data point values.
			tooltipLocation: 'n',  // location of tooltip: n, ne, e, se, s, sw, w, nw.
			tooltipAxes: 'xy',
			showMarker: false,
			tooltipOffset: "20"
		},
		cursor: {show: false }
	});
}

function stringToArray(strArray, compareArray) {
	var visitsArray = [];
	visitsArray = strArray.split(';');
	if (compareArray.length > 0) {
		compareArray = compareArray.split(';');
	}
	var str = [];
	for (var i = 0; i < visitsArray.length; i++) {
		var tmp = visitsArray[i].split(',');
		var tmp_array = [];
		tmp_array[0] = tmp[0];
		tmp_array[1] = (tmp[1] === '') ? 0 : parseInt(tmp[1], 10);
		if (compareArray.length > 0) {
			var cmp_tmp = compareArray[i].split(',');
			tmp_array[1] = parseInt(tmp_array[1] - cmp_tmp[1], 10);
		}
		str[i] = tmp_array;
	}
	return str;
}

function initPlot() {

	var lines    = (showPagesViewInTable === true) ? [lineGoogleVisitors, lineGoogleVisits, lineGooglePageViews] : [lineGoogleVisitors, lineGoogleVisits];
	var labels   = (showPagesViewInTable === true) ? [{label: labelVisitors}, {label: labelVisits}, {label: labelPageViews}] : [{label: labelVisitors}, {label: labelVisits}];
	var interval = (showPagesViewInTable === true) ? tickeIntervalGooglePageViews : tickeIntervalGoogleVisits;

	if (graphType === "lines") {
		plotGraphLines(lines, labels, interval);
	} else {
		plotGraphBar(lines, labels, interval);
	}
}

function republic_analytics_plot(graphTitle1, dateFirst1, dateLast1, lineGoogleVisits1, lineGoogleVisitors1, lineGooglePageViews1, tickersGoogleVisits1, tickeIntervalGoogleVisits1, tickeIntervalGoogleVisitors1, tickeIntervalGooglePageViews1, labelVisits1, labelVisitors1, labelPageViews1, graphType1, visitsColor1, visitorsColor1, pageViewsColor1, showPagesViewInTable1) {
	graphTitle         = graphTitle1;
	dateFirst          = dateFirst1;
	dateLast           = dateLast1;
	lineGoogleVisitors = stringToArray(lineGoogleVisitors1, []);

	if (graphType1 === 'lines') {
		lineGoogleVisits = stringToArray(lineGoogleVisits1, []);
	} else {
		lineGoogleVisits = stringToArray(lineGoogleVisits1, lineGoogleVisitors1);
	}

	showPagesViewInTable = (showPagesViewInTable1 === 'n') ? false : true;

	if (showPagesViewInTable) {
		if (graphType1 === 'lines') {
			lineGooglePageViews = stringToArray(lineGooglePageViews1, []);
		} else {
			lineGooglePageViews = stringToArray(lineGooglePageViews1, lineGoogleVisits1 + ";" + lineGoogleVisitors1);
		}
	}

	tickersGoogleVisits          = tickersGoogleVisits1;
	tickeIntervalGoogleVisitors  = tickeIntervalGoogleVisitors1;
	tickeIntervalGooglePageViews = tickeIntervalGooglePageViews1;
	tickeIntervalGoogleVisits    = tickeIntervalGoogleVisits1;
	labelVisits                  = labelVisits1;
	labelVisitors                = labelVisitors1;
	labelPageViews               = labelPageViews1;
	graphType                    = graphType1;
	visitsColor                  = visitsColor1;
	visitorsColor                = visitorsColor1;
	pageViewsColor               = pageViewsColor1;
	initPlot();
}

$(".showMoreResults").live('click', function () {
	var counter = 0;

	$(this).closest('tbody').find('tr:not(:visible)').each(function () {
		var row = $(this);
		if (counter++ < 10) {
			row.show();
		}
	});

	var size = $(this).closest('tbody').find('tr:not(:visible)').size();
	if (size === 0) {
		$(this).closest('.more').hide();
	}

	return false;
});

$.jqplot.config.enablePlugins = true;


// Draw plot wit visits/page views/unique page views

$(window).resize(function () {
	if (plot) {
		initPlot();
	}
});


jQuery.event.special.mainContentResize = {
	setup: function () {
		var self = this,
		$this = $(this),
		$originalWidth = $this.width();
		if ($originalWidth != $this.width()) {
			if (plot) {
				initPlot();
			}
			jQuery.event.handle.call(self, {type: 'mainContentResize'});
		}
	},
	teardown: function () {

	}
};

$('#hideSidebarLink').click(function () {
	$("#mainContent").stop();
	$("#mainContent").animate({width: '100%'}, function () {
		if (plot) {
			initPlot();
		}
	});
});

$('#revealSidebarLink').click(function () {
	$("#mainContent").stop();
	$("#mainContent").animate({width: '77%'}, function () {
		if (plot) {
			initPlot();
		}
	});

});

$(document).ready(function () {

	$("#google_accounts").live('change', function(){
		window.location = EE.BASE + '&C=addons_modules&M=show_module_cp&module=republic_analytics&profile=' + $(this).val();
	})

	$(".expandable a").live("click", function () {
		var id = $(this).attr('id');
		$("tr." + id).toggle();
		return false;
	});

	// spinner animation
	var opts = {
		lines: 12, // The number of lines to draw
		length: 30, // The length of each line
		width: 10, // The line thickness
		radius: 34, // The radius of the inner circle
		color: '#97A9B4', // #rgb or #rrggbb
		speed: 0.5, // Rounds per second
		trail: 50, // Afterglow percentage
		shadow: false // Whether to render a shadow
	};
	var target = document.getElementById('spinner');
	var spinner = new Spinner(opts).spin(target);

	// loading asyncronus data from Google Analytics
	var profileId = "";
	if ($("#profile_id").html() != "") {
		profileId = '&profile=' + $("#profile_id").html();
	}
	$('#result').load(EE.BASE + '&C=addons_modules&M=show_module_cp&module=republic_analytics&method=load_data' + profileId + ' #analytics_container', function (response, status, xhr) {

		if ($('#data_for_graph').length > 0) {

			republic_analytics_plot(
				$('.graphTitle').html(),
				$('.dateFirst').html(),
				$('.dateLast').html(),
				$('.lineGoogleVisits').html(),
				$('.lineGoogleVisitors').html(),
				$('.lineGooglePageViews').html(),
				$('.tickersGoogleVisits').html(),
				$('.tickeIntervalGoogleVisits').html(),
				$('.tickeIntervalGoogleVisitors').html(),
				$('.tickeIntervalGooglePageViews').html(),
				$('.labelVisits').html(),
				$('.labelVisitors').html(),
				$('.labelPageViews').html(),
				$('.graphType').html(),
				$('.graphVisitsColor').html(),
				$('.graphVisitorsColor').html(),
				$('.graphPageViewsColor').html(),
				$('.showPagesViewInTable').html()
			);
			$('#data_for_graph').remove();
		}
	});
});
