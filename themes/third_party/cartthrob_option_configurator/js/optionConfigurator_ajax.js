jQuery(document).ready(function($){
 	var configurator_location = "sub_hold_field_" + configurator_id; 
 	
 	var CT_CP = EE.BASE +"&C=addons_modules&M=show_module_cp&module=cartthrob&method=configurator_ajax";
	
	$('.cartthrobOptionConfiguratorOptions').live('blur', function(){
		update_configuration();
	}); 
	var loading_html = '<div class="ct_loader_bar">loading...</div>'; 
	$(".cartthrobOptionConfiguratorOptions .ct_add_field").live("click",function(){
		// gotta wait for this to load
		if ($(".cartthrobOptionConfiguratorOptions").html() != loading_html)
		{
			var template = $(this).parents('tbody:first').find('.group_option_template').clone(); 
			var new_template = template.clone(); 
	 		var temporary_id = Math.floor(Math.random()*10000000000); 

	 		$(new_template).css("display",""); 
			$(new_template).attr("class", "group_option"); 

			new_template.find('[name]').each(function() {
				var name_attr = $(this).attr('name'); 
				name_attr = name_attr.replace("option_template", "option"); 
				name_attr = name_attr.replace("price_template", "price"); 

				$(this).attr('name', name_attr+"["+temporary_id+"]");
			});

	 		$(this).parents('tr:first').after(new_template); 
			update_configuration();
		}
 	}); 
	
	$(".cartthrobOptionConfiguratorOptions .ct_delete_field").live("click",function(){
		if ($(".cartthrobOptionConfiguratorOptions").html() != loading_html)
		{
	 		row_count = $(this).parents('tbody:first').children(':visible').length; 
			if (row_count > 1)
			{
		 		$(this).parents('tr:first').remove();
			}
			update_configuration(); 
		}
	}); 
	
	// when the type changes, show or hide the options as needed. the values remain, but they're hidden. 
	$("select.cartthrob_configurator_field_type").live('change', function(){

		var option_group = $(this).closest("td").next().find(".cartthrobOptionConfiguratorOptions"); 
		var first_tr = $(option_group).find('tr.group_option:first');
		
		if ($(this).val() == "options")
		{
			set_to_option(first_tr);
		}
		else
		{
			set_to_text(first_tr);
 		}
		update_configuration();

	}); 
	
	init = function(){
		$('select.cartthrob_configurator_field_type').each(function(){
			
			var option_group = $(this).closest("td").next().find(".cartthrobOptionConfiguratorOptions"); 
			var first_tr = $(option_group).find('tr.group_option:first');
			
			if ($(this).val() == "options")
			{
				set_to_option(first_tr);
			}
			else
			{
				set_to_text(first_tr);
	 		}
		}); 
	}
	set_to_option = function(first_tr){
		// remove the word dynamic
		first_tr.find("td div").remove(); 
		// set the input to be editable
		first_tr.find("td input:first").attr("readOnly",false).show(); 
		// show the input
		first_tr.find("td:eq(1) input").attr('readonly', false).show();
 		first_tr.find("td:eq(1)").remove("div"); 
		first_tr.find("td:eq(2)").show();
	}
	set_to_text = function(first_tr)
	{
		// set the input to readonly and hide it. 
		first_tr.find("td input:first").val("text").attr('readonly', true).hide();
		// add the word dynamic
		first_tr.find("td:first").append("<div class='dynamic_text'>dynamic</div>"); 
		// hide the plus / minus grpahics
  		first_tr.find("td:eq(1)").append("<div class='dynamic_text'>--</div>"); 
		first_tr.find("td:eq(1) input").val("0").attr('readonly', true).hide();
		first_tr.find("td:eq(2)").hide();
		// remove all other options
		first_tr.nextAll("tr.group_option").remove();
	}
	update_configuration = function(){
		var queryString = $("#" + configurator_location + ' *').fieldSerialize() + "&XID="+EE.XID+"&show_inventory="+show_inventory;
		
		var jqxhr = $.ajax({
			type: 'POST',
			url: CT_CP,
			cache: false,
			data: queryString,
 		});
		
		jqxhr.fail(function(jqXHR, textStatus) {
			alert( "Request failed: " + textStatus );
		});
		
		jqxhr.success(function(data, textStatus, jqXHR) {
			if (data.success == undefined)
			{
				alert("Error " + data); 
			}
			else
			{
				//alert(data.success); 
				if (data.success){
					$(".cartthrobPriceModifiersConfigurator").eq(1).replaceWith(data.success); 
				}
				EE.XID = data.XID; 
				
			}

		});
	}
	init(); 
});