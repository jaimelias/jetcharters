jQuery(function(){
	
	
	jQuery(window).on('load', function (e){
		one_way_round_trip();
		
		if(typeof algoliasearch === 'function')
		{
			algolia_execute();
		}
		
		validate_instant_quote();
		country_dropdown();
		jetcharters_cookies();
		responsive_datepicker();
		responsive_timepicker();
		validate_jet_form();
	});		
});

	function responsive_timepicker()
	{
		var args = {};
		
		jQuery('form.jet_calculator').find('input.timepicker').each(function(){
			jQuery(this).pickatime(args);
		});
	}
	
	function responsive_datepicker()
	{
		
		var args = {};
		args.format = 'yyyy-mm-dd';
		args.min = true;
		
		jQuery('form.jet_calculator').find('input.datepicker').each(function(){
			
			if(jQuery(this).attr('type') == 'text')
			{
				jQuery(this).pickadate(args);
			}
			else if(jQuery(this).attr('type') == 'date')
			{
				jQuery(this).attr({'type': 'text'});
				jQuery(this).pickadate(args);
			}	
		});
	}
	
	function country_dropdown()
	{
		if(typeof jsonsrc !== typeof undefined)
		{
			if(jQuery('form#jet_booking_request').find('.countrylist').length > 0)
			{
				jet_country_dropdown(jsonsrc(), jQuery("html").attr("lang").slice(0, -3));
			}
		}	
	}
	
	function jetcharters_cookies()
	{
		var this_form = jQuery('#jet_booking_request');
		var landing = ['channel', 'device', 'landing_domain', 'landing_path'];
		var warnings = 0;			
		
		jQuery(this_form).each(function(){
			
			for(var x = 0; x < landing.length; x++)
			{
				
				jQuery(this_form).find('input.'+landing[x]).each(function(){
					jQuery(this).val(getCookie(landing[x]));
				});
				
				if(jQuery(this_form).find('input.'+landing[x]).length == 0)
				{
					console.warn('input.'+landing[x]+' not found');
					warnings++;
				}
				
			}
			
			if(warnings > 0)
			{
				console.warn('You can create custom fields with Pipedrive and track metrics.');
			}
			else
			{
				console.log('Pipedrive metric fields found.');
			}
			
		});			
	}		



	function validate_request_quote(token)
	{
		var count = 0;
		
		var this_form = jQuery('#jet_booking_request');
		
		jQuery(this_form).find('input').add('select').add('textarea').each(function(){
			
			//console.log(jQuery(this).attr('name'));
			
			if(jQuery(this).val() == '' && jQuery(this).attr('name') != 'g-recaptcha-response')
			{
				if(getUrlParameter('jet_flight') == 0)
				{
					if(jQuery(this).attr('name') == 'jet_return_date' || jQuery(this).attr('name') == 'jet_return_hour' || jQuery(this).attr('name') == 'return_itinerary')
					{
						jQuery(this).removeClass('invalid_field');
						console.log(jQuery(this).attr('name'));
					}
					else
					{
						jQuery(this).addClass('invalid_field');
						console.log(jQuery(this).attr('name'));
						count++;
					}
				}
				else
				{
					jQuery(this).addClass('invalid_field');
					console.log(jQuery(this).attr('name'));
					count++;
				}
			}
			else
			{
				if(jQuery(this).val() == '--')
				{
					jQuery(this).addClass('invalid_field');
					console.log(jQuery(this).attr('name'));
					count++;
				}
				else
				{
					jQuery(this).removeClass('invalid_field');
					console.log(jQuery(this).attr('name'));
				}
			}
		});
		
		console.log( jQuery(this_form).serializeArray() );
		
		if(count == 0 && jQuery(this_form).attr('data-form-ready') == 'true')
		{
			jQuery(this_form).attr({'action': jQuery(this_form).attr('action')+token});
			jQuery(this_form).submit();
		}
		else
		{
			grecaptcha.reset();
		}
	}

	function validate_instant_quote()
	{
		jQuery('button[data-aircraft]').click(function(){
			
			var aircraft_fields = jQuery('#jet_booking_request').find('#aircraft_fields');
			var json_inputs = jQuery(this).attr('data-aircraft');
			
			json_inputs = JSON.parse(json_inputs);
			jQuery(aircraft_fields).text('');
			
			for(k in json_inputs)
			{
				jQuery(aircraft_fields).append(jQuery('<input>').attr({'type': 'text', 'name': k, 'value': json_inputs[k]}));
			}
			
			jQuery('#jet_booking_container').removeClass('hidden');
			jQuery('.instant_quote_table').addClass('hidden');			
			jQuery('#jet_booking_request').attr({'data-form-ready': 'true'});
			jQuery('#jet_booking_request').find('input[name="lead_name"]').focus();
		});
		
		jQuery('#jet_booking_container').find('.close').click(function(){
			jQuery('#jet_booking_container').addClass('hidden');
			jQuery('.instant_quote_table').removeClass('hidden');
		});
		
		
	}
	function validate_jet_form()
	{
		jQuery('.jet_calculator').each(function(){
			
			var this_form = jQuery(this);
			
			jQuery(this_form).find('#jet_submit').click(function(){
				
				var invalid_field = 0;
				
				jQuery(this_form).find('input').each(function(){
					
					if(jQuery(this).val() == '')
					{
						if(jQuery('#jet_flight').val() == 0 && (jQuery(this).attr('name') == 'jet_return_date' || jQuery(this).attr('name') == 'jet_return_hour' || jQuery(this).attr('name') == 'jet_return_date_submit' || jQuery(this).attr('name') == 'jet_return_hour_submit'))
						{
							jQuery(this).removeClass('invalid_field');
						}
						else
						{
							invalid_field++;
							jQuery(this).addClass('invalid_field');
						}
					}
					else
					{
						if(jQuery(this).hasClass('jet_list'))
						{
							if(!jQuery(this).hasClass('jet_selected'))
							{
								invalid_field++;
								jQuery(this).addClass('invalid_field');
							}
							else
							{
								jQuery(this).removeClass('invalid_field');
							}
						}
						else
						{
							jQuery(this).removeClass('invalid_field');
						}
					}
				});

				if(invalid_field == 0)
				{
					var hash = sha512(jQuery(this_form).find('input[name="jet_pax"]').val()+jQuery(this_form).find('input[name="jet_departure_date"]').val());
					var departure = Date.parse(jQuery('input[name="jet_departure_date"]').val());
					var today = new Date();
					today.setDate(today.getDate() - 2);
					today = Date.parse(today);
					var days_between = Math.round((departure-today)/(1000*60*60*24));				
					var eventAction = jQuery('#jet_origin').val()+'/'+jQuery('#jet_destination').val();
					var eventLabel = days_between+'/'+jQuery('#jet_departure_date').val()+'/'+jQuery('#jet_pax').val();
					
					if(typeof ga !== typeof undefined)
					{	
						var eventArgs = {};
						eventArgs.eventCategory = 'Flight';
						eventArgs.eventAction = eventAction;
						eventArgs.eventLabel = eventLabel;
						ga('send', 'event', eventArgs);
					}
					else
					{
						console.log('jetcharters: GA not defined');
					}
					jQuery(this_form).attr({'action': jQuery(this_form).attr('action')+hash});
					jQuery(this_form).submit();
				}
			});			
		});
	}

	
	function country_name(lang, country_name, country_code)
	{
		if(country_code)
		{
			if(country_name.hasOwnProperty(lang))
			{
				return country_name[lang];
			}
			else
			{
				return country_code;
			}
		}
	}
	
	function one_way_round_trip()
	{
		if(jQuery('#jet_flight').val() == 1)
		{
			jQuery('.jet_return').fadeIn();
		}
		jQuery('#jet_flight').change(function(){
			if(jQuery(this).val() == 1)
			{
				jQuery('.jet_return').fadeIn();
			}
			else
			{
				jQuery('.jet_return').fadeOut();
				jQuery('#jet_return_date').val('');
				jQuery('#jet_return_hour').val('');
			}
		});		
	}
	
	function algolia_execute()
	{
		var client = algoliasearch(get_algolia_id(), get_algolia_token());
		var index = client.initIndex(get_algolia_index());

	jQuery('.jet_calculator').each(function(){
		
		var this_form = jQuery(this);
		
		jQuery(this).find('.jet_list').each(function(){
			
			var this_field = jQuery(this);
			
			jQuery(this_field).autocomplete({
				hint: false
			},[{
				source: $.fn.autocomplete.sources.hits(index, {
					hitsPerPage: 4
				}),
				displayKey: 'airport',
				templates: {
					suggestion: function(suggestion) {

						var htmllang = jQuery("html").attr("lang");
						htmllang = htmllang.slice(0, 2);
						htmllang.toLowerCase();
						var country_names = suggestion.country_names;
						var country_flag = suggestion.country_code;
						var flag_url = jsonsrc() + "img/flags/" + country_flag + '.svg';
						flag_url = flag_url.toLowerCase();
						var result = jQuery('<div class="algolia_airport clearfix"><div class="sflag pull-left"><img width="45" height="33.75" /></div><div class="sdata"><div class="sairport"><span class="airport"></span> <strong class="iata"></strong></div><div class="slocation"><span class="city"></span>, <span class="country"></span></div></div></div>');
						result.find('.sairport > .airport').html(suggestion._highlightResult.airport.value);
						
						if(suggestion._highlightResult.hasOwnProperty('iata'))
						{
							result.find('.sairport > .iata').html(suggestion._highlightResult.iata.value);
						}
						
						result.find('.slocation > .city').html(suggestion._highlightResult.city.value);
						result.find('.slocation > .country').html(country_name(htmllang, country_names, suggestion.country_code));
						result.find('.sflag > img').attr({
							'src': flag_url
						});
						return result.html();
					}
				}
			}]).on('autocomplete:selected', function(event, suggestion) {
				
				if(suggestion.hasOwnProperty('iata'))
				{
					if(suggestion.iata != null)
					{
						var selected_airport = suggestion.iata;
					}
					else
					{
						 var selected_airport = 'IATA missing... '+suggestion.airport;
					}
				}
				
				jQuery(this_form).find('#'+jQuery(this_field).attr('id')+'_l').val(suggestion.airport+' ('+suggestion.iata+'), '+suggestion.city+' ('+suggestion.country_code+')');
				

				jQuery(this_field).attr({
					'data-iata': suggestion.iata,
					'data-lat': suggestion._geoloc.lat,
					'data-lon': suggestion._geoloc.lng
				}).addClass('jet_selected').val(selected_airport);	

				jQuery(this_field).blur(function()
				{
					if (jQuery(this_field).hasClass('jet_selected'))
					{
						jQuery(this_field).val(selected_airport);
					}
					else
					{
						jQuery(this_field).val('');
						jQuery(this_field).removeClass('jet_selected');
						jQuery(this_field).addClass('invalid_field');
						jQuery(this_field).removeAttr('data-iata');
						jQuery(this_field).removeAttr('data-lat');
						jQuery(this_field).removeAttr('data-lon');						
					}
				});
					
				jQuery(this_field).focus(function() {
					jQuery(this_field).val('');
					jQuery(this_field).removeClass('jet_selected');
					jQuery(this_field).removeClass('invalid_field');
					jQuery(this_field).removeAttr('data-iata');
					jQuery(this_field).removeAttr('data-lat');
					jQuery(this_field).removeAttr('data-lon');
				});					
						
				if(jQuery(this_form).find('.jet_selected').length == 1)
				{
					jQuery('.jet_list').not('.jet_selected').focus();
				}
				if(jQuery(this_form).find('.jet_selected').length == 2)
				{
					jQuery(this_form).find('input[name="jet_pax"]').focus();
				}
				else
				{
					jQuery(this_field).blur();
				}
				
			});
		});
	});
	
	}
	
	function getUrlParameter(sParam) {
		var sPageURL = decodeURIComponent(window.location.search.substring(1)),
			sURLVariables = sPageURL.split('&'),
			sParameterName,
			i;

		for (i = 0; i < sURLVariables.length; i++) {
			sParameterName = sURLVariables[i].split('=');

			if (sParameterName[0] === sParam) {
				return sParameterName[1] === undefined ? true : sParameterName[1];
			}
		}
	}

	function jet_country_dropdown(pluginurl, htmllang)
	{
		$.getJSON( pluginurl + 'countries/'+htmllang+'.json')
			.done(function(data) 
			{
				jet_country_options(data);
			})
			.fail(function()
			{
				$.getJSON(pluginurl + 'countries/en.json', function(data) {

					jet_country_options(data);
				});				
			});			
	}	

	function jet_country_options(data)
	{
		jQuery('.countrylist').each(function() {
			for (var x = 0; x < data.length; x++) 
			{
				jQuery(this).append('<option value=' + data[x][0] + '>' + data[x][1] + '</option>');
			}
		});		
	}