<div id="chart_div"></div>

<p id="chart_overview"><?=$overview?></p>

<script type="text/javascript" src="//www.google.com/jsapi"></script>

<script type="text/javascript">
var cartthrobChart = function(rows, chartTitle) {
	var data = new google.visualization.DataTable();
	data.addColumn("string", "");
	data.addColumn("number", "<?=lang('subtotal')?>");
	data.addColumn("number", "<?=lang('tax')?>");
	data.addColumn("number", "<?=lang('shipping')?>");
	data.addColumn("number", "<?=lang('discount')?>");
	data.addColumn("number", "<?=lang('total')?>");
	var count = $(rows).length;
	data.addRows(count);
	for (i in rows) {
		count--;
		data.setFormattedValue(count, 0, rows[i].name);
		data.setValue(count, 0, String(rows[i].date));
		data.setValue(count, 1, Number(rows[i].subtotal));
		data.setValue(count, 2, Number(rows[i].tax));
		data.setValue(count, 3, Number(rows[i].shipping));
		data.setValue(count, 4, Number(rows[i].discount));
		data.setValue(count, 5, Number(rows[i].total));
		if (rows[i].href != undefined) {
			data.setRowProperty(count, "href", rows[i].href);
		}
	}
	var chart = new google.visualization.LineChart(document.getElementById("chart_div"));
	var chartOpts = {
		height: 500,
		width: $("#chart_div").width()-14,
		title: chartTitle,
		hAxis: {direction: -1},
		colors: ['red', 'green', 'orange', 'purple', 'blue'],
		pointSize: 7
	};
	
	chart.draw(data, chartOpts);
	
	google.visualization.events.addListener(chart, "select", function(){
		selection = chart.getSelection();
		value = data.getRowProperty(selection[0].row, "href");
		if (value != null) {
			window.location.href = EE.BASE + '&C=addons_modules&M=show_module_cp&module=cartthrob_order_manager&method=om_sales_dashboard&' + value;
		}
	});
};
google.load("visualization", "1", {packages:["corechart"]});
</script>