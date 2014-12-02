/////////////////  CARTTHROB JS //////////////////////////	
// holding text used when updating totals
var updating = '<div class="progress progress-striped active"><div class="bar" style="width: 100%;"></div></div>';

// if set to true, hidden forms will be shown, and all json data will be printed to your javascript console log (if available)
var ct_debug = false;
// list of all CT fields that might change
var ct_billing_fields = new   Array('first_name','last_name','address','address2','city','state','zip','country_code','company','phone','email_address','use_billing_info','card_type','expiration_month','expiration_year','begin_month','begin_year','currency_code','language','region', 'gateway');

var ct_shipping_fields = new   Array('shipping_first_name','shipping_last_name','shipping_address','shipping_address2','shipping_city','shipping_state','shipping_zip','shipping_country_code','shipping_company','shipping_option');

// setting all customer information into a variables. 
{exp:cartthrob:customer_info}
	var first_name = '{customer_first_name}'; 
	var last_name = '{customer_last_name}'; 
	var address = '{customer_address}'; 
	var address2 = '{customer_address2}'; 
	var city = '{customer_city}'; 
	var state = '{customer_state}'; 
	var zip = '{customer_zip}'; 
	var country_code = '{customer_country_code}'; 
	var company = '{customer_company}'; 
	var phone = '{customer_phone}'; 
	var email_address = '{customer_email_address}'; 
	var use_billing_info = '{customer_use_billing_info}'; 
	var shipping_first_name = '{customer_shipping_first_name}'; 
	var shipping_last_name = '{customer_shipping_last_name}'; 
	var shipping_address = '{customer_shipping_address}'; 
	var shipping_address2 = '{customer_shipping_address2}'; 
	var shipping_city = '{customer_shipping_city}'; 
	var shipping_state = '{customer_shipping_state}'; 
	var shipping_zip = '{customer_shipping_zip}'; 
	var shipping_country_code = '{customer_shipping_country_code}'; 
	var shipping_company = '{customer_shipping_company}'; 
	var card_type = '{customer_card_type}'; 
	var expiration_month = '{customer_expiration_month}'; 
	var expiration_year = '{customer_expiration_year}'; 
	var begin_month = '{customer_begin_month}'; 
	var begin_year = '{customer_begin_year}'; 
	var currency_code = '{customer_currency_code}'; 
	var language = '{customer_language}'; 
	var shipping_option = '{customer_shipping_option}'; 
	var region = '{customer_region}';
	var gateway_name =""; 
{/exp:cartthrob:customer_info}

// cartthrob form options
{!-- <!-- see this article for more about using AJAX with CartThrob  https://vimeo.com/37499431	-->  --} 
var cart_form_options = { 
	success: update_cart,  // post-submit callback
	dataType: 'json' 
};

// cartthrob updating function
function update_cart(data, statusText, xhr, $form)  {   
    if (data.success) {                                                   

		// update the CSRF_TOKEN hash so we don't run afoul of EE's secure forms
		//jQuery("input[name=csrf_token]").val(data.CSRF_TOKEN);
		// using the json data object's data to update various totals
		jQuery('.cart_tax')			.html( data.cart_tax );
		jQuery('.cart_total')		.html( data.cart_total );
		jQuery('.cart_shipping')		.html( data.cart_shipping );
		jQuery('.cart_subtotal')		.html( data.cart_subtotal );
		jQuery('.cart_discount')		.html( data.cart_discount );

		// if debugging is turned on, all data will be output to the js console log
		if (ct_debug)
		{
			jQuery.each(data, function(index, item){
			  console.log(index + ": " +item);
			});  
			jQuery.each(data.errors, function(index, item){
			  console.log(index + ": " +item);
			});
		}
    }  
	return true; 
}

jQuery(document).ready(function($){

	// add 'use billing info' to checkout fields. 
	// using JS to do this so that it's easier to track whether this is set or not and output the appropriately clicked
	// set of radio buttons
	// do not use a CHECKBOX to set this, as there will then be no method to UNCHECK this selection
	// when a checkbox is sent via a form, an unchecked state is the same as sending nothing, so CT won't see that it's unchecked, 
	// and will therefore never notice that the value should be unset
	function add_billing_info()
	{
		// add use_billing_info box if it doesn't exist. hide and show shipping fields
		if ($("fieldset.billing").length > 0 && $("fieldset.shipping").length > 0 && $("fieldset.billing > label.use_billing_info").length == 0)
		{
			ht = '<div class="control-group">'; 
			ht += '<label class="use_billing_info radio">Copy billing info to shipping'; 
			ht += '<div class="controls">';
			if (use_billing_info == "1" || use_billing_info =="yes")
			{
				ht += '<label class="radio">Yes <input type="radio" name="use_billing_info" value="yes" checked="checked"/></label> ';
				ht += '<label class="radio">No <input type="radio" name="use_billing_info" value="no"  /></label>' ; 
				$("fieldset.shipping").hide(); 
			}
			else
			{
				ht += '<label class="radio">Yes <input type="radio" name="use_billing_info" value="yes" /></label> ';
				ht += '<label class="radio">No <input type="radio" name="use_billing_info" value="no" checked="checked" /></label> ' ; 
				$("fieldset.shipping").show(); 
			}
			ht+='</label></div></div>';
			$('fieldset.billing').append( ht );
		}
	}
	add_billing_info(); 
	
	// showing a login form when the login button is clicked. 
	$("#login_bttn").click(function(){
		$("#login_form").show();
		return false;
	});
	
	// if you allow someone to select a gateway, it's very possible that the gateway fields will need to 
	// change to support the other gateway. THis function loads in gateway fields dynamically
	$("#gateway").live('change', function(){
 		$.post("{path=store/ajax_cart_form/}", { gateway: $(this).val(), csrf_token : $(this).closest("form").find("input[name=csrf_token]").val()  },
			function(data) {
				// $(this).closest("form").find("input[name=csrf_token]").val(data.CSRF_TOKEN); 
				$( "#checkout_form_gateway_fields" ).empty().append( $(data) );
	   }).complete(function() { add_billing_info();  });
	}); 
	
	
	// when any field is updated, check to see if it's a CT form field and dynamically update 
	$("input[type=text], input[type=radio], select").live('change', function(){
		
		var input_name = $(this).attr("name");    
		var input_val = $(this).val(); 
		
		if ( $.inArray(input_name, ct_billing_fields) != -1 || $.inArray( input_name , ct_shipping_fields)!= -1  )
		{
 			if (input_name =="use_billing_info")
			{
				input_val = $('input[name='+input_name+']:checked').val(); 
				if (input_val== "yes" || input_val == "1")
				{
					$(ct_shipping_fields).each(function(index, data) {  
						var new_value = null;
						new_value = $("input[name="+ data.replace("shipping_", "") +"]").val(); 
						$("input[name="+ data +"]").val( new_value ); 
					});
					use_billing_info = 1; 
					$("fieldset.shipping").hide(); 
				}
				else
				{
					use_billing_info = 0; 
					$("fieldset.shipping").show();
					$(ct_shipping_fields).each(function(index, data) {  
						var new_value = null;
						new_value = (new Function("return " + data ))();
						$("input[name="+ data +"]").val( new_value ); 
					});
				}
			}  

			form_name = "#hidden_save_customer_info_form";
			var form = $(form_name); 
			var closest_form = $(this).closest("form"); 

			// generate a new input 
			new_input = "<input type='text' name='"+ input_name +"' value='"+ input_val +"' />" 

			if ($("input[name=create_user]").val() != "yes")
			{
				// add this field to the form without classes, ids, etc. don't need em.
				$(form_name + ' > .cart_data').empty().append(new_input); 
			}
			else
			{
				// we do not want to ajax update any create_user related fields
				var ct_create_user_fields = new   Array('email_address','username','password','password_confirm','screen_name');
				if ( $.inArray(input_name, ct_create_user_fields) == -1)
				{
					$(form_name + ' > .cart_data').empty().append(new_input); 
				}
				
			}
			 
			$(form).ajaxForm(cart_form_options);
			$(form).submit(); 
			
			/*
			// alternate method... ajax submit any form but checkout.
			// this is too dangerous, but this is a good example of how easy it is to ajax submit forms
			
			var closest_form = $(this).closest("form"); 
			
			if ($(closest_form).attr("id") != "checkout_form")
			{
				$(form).ajaxForm(cart_form_options);
                $(form).submit(); 
			}
			*/ 
		}
		
	});    
	
	if (ct_debug)
	{
 		 $("#hidden_save_customer_info_form_wrapper").css("display", "inline"); 
	}
 	// disable the checkout button on click
	$('#checkout_form').submit(function(){
   		 $('input[type=submit]', this).attr('disabled', 'disabled').addClass('disabled').val("Submitting...");
	});
	// reenable the checkout button once the customer has gone offsite
	// otherwise on a back click, the button's unclickable
 	$(window).unload(function() {
   		 $('#complete_checkout').removeAttr('disabled').removeClass('disabled').val("Complete Checkout");
	});
	/////////////////  END CARTTHROB JS //////////////////////////
	
	// Twitter Bootstrap
	    // side bar
    var $window = $(window)

    $('.bs-docs-sidenav').affix({
      offset: {
        top: function () { return $window.width() <= 980 ? 290 : 120 }
      , bottom: 230
      }
    });
}); 
