(function($) {
    'use strict';

	$(function(){
		
		
		$(window).on('load', function (e){
			one_way_round_trip();
			algolia_execute();
			validate_jet_form();
			validate_instant_quote();
			country_dropdown();
			jetcharters_cookies();
			responsive_datepicker();
			responsive_timepicker();
		});		
	});

})(jQuery);

	function responsive_timepicker()
	{
		var args = {};
		
		$('form.jet_calculator').find('input.timepicker').each(function(){
			$(this).pickatime(args);
		});
	}
	
	function responsive_datepicker()
	{
		
		var args = {};
		args.format = 'yyyy-mm-dd';
		args.min = true;
		
		$('form.jet_calculator').find('input.datepicker').each(function(){
			
			if($(this).attr('type') == 'text')
			{
				$(this).pickadate(args);
			}
			else if($(this).attr('type') == 'date')
			{
				$(this).attr({'type': 'text'});
				$(this).pickadate(args);
			}	
		});
	}
	
	function country_dropdown()
	{
		if(typeof jsonsrc !== typeof undefined)
		{
			if($('form#jet_booking_request').find('.countrylist').length > 0)
			{
				jet_country_dropdown(jsonsrc(), $("html").attr("lang").slice(0, -3));
			}
		}	
	}
	
	function jetcharters_cookies()
	{
		var this_form = $('#jet_booking_request');
		var landing = ['channel', 'device', 'landing_domain', 'landing_path'];
		var warnings = 0;			
		
		$(this_form).each(function(){
			
			for(var x = 0; x < landing.length; x++)
			{
				
				$(this_form).find('input.'+landing[x]).each(function(){
					$(this).val(getCookie(landing[x]));
				});
				
				if($(this_form).find('input.'+landing[x]).length == 0)
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



	function validate_request_quote()
	{
		var count = 0;
		
		$('#jet_booking_request').find('input').add('select').add('textarea').each(function(){
			
			//console.log($(this).attr('name'));
			
			if($(this).val() == '' && $(this).attr('name') != 'g-recaptcha-response')
			{
				if(getUrlParameter('jet_flight') == 0)
				{
					if($(this).attr('name') == 'jet_return_date' || $(this).attr('name') == 'jet_return_hour' || $(this).attr('name') == 'return_itinerary')
					{
						$(this).removeClass('invalid_field');
						console.log($(this).attr('name'));
					}
					else
					{
						$(this).addClass('invalid_field');
						console.log($(this).attr('name'));
						count++;
					}
				}
				else
				{
					$(this).addClass('invalid_field');
					console.log($(this).attr('name'));
					count++;
				}
			}
			else
			{
				if($(this).val() == '--')
				{
					$(this).addClass('invalid_field');
					console.log($(this).attr('name'));
					count++;
				}
				else
				{
					$(this).removeClass('invalid_field');
					console.log($(this).attr('name'));
				}
			}
		});
		
		console.log( $('#jet_booking_request').serializeArray() );
		
		if(count == 0 && $('#jet_booking_request').attr('data-form-ready') == 'true')
		{
			$('#jet_booking_request').submit();
		}
		else
		{
			grecaptcha.reset();
		}
	}

	function validate_instant_quote()
	{
		$('button[data-aircraft]').click(function(){
			
			
			$('#jet_booking_container').removeClass('hidden');
			$('.instant_quote_table').addClass('hidden');
						
			var aircraft_fields = $('#jet_booking_request').find('#aircraft_fields');
			var json_inputs = $(this).attr('data-aircraft');
			
			json_inputs = JSON.parse(json_inputs);
			$(aircraft_fields).text('');
			
			for(k in json_inputs)
			{
				$(aircraft_fields).append($('<input>').attr({'type': 'text', 'name': k, 'value': json_inputs[k]}));
			}
			
			$('#jet_booking_request').attr({'data-form-ready': 'true'});
			console.log(json_inputs);
			
		});
		
		$('#jet_booking_container').find('.close').click(function(){
			$('#jet_booking_container').addClass('hidden');
			$('.instant_quote_table').removeClass('hidden');
		});
		
		
	}
	function validate_jet_form()
	{
			
		$('.jet_calculator').submit(function(event){
			
			event.preventDefault();
			var invalid_field = 0;
			var this_form = $(this);

			$(this_form).find('input').each(function(){
				
				if($(this).val() == '')
				{
					if($('#jet_flight').val() == 0 && ($(this).attr('name') == 'jet_return_date' || $(this).attr('name') == 'jet_return_hour' || $(this).attr('name') == 'jet_return_date_submit' || $(this).attr('name') == 'jet_return_hour_submit'))
					{
						$(this).removeClass('invalid_field');
					}
					else
					{
						invalid_field++;
						console.log($(this));
						$(this).addClass('invalid_field');
					}
				}
				else
				{
					if($(this).hasClass('jet_list'))
					{
						if(!$(this).hasClass('jet_selected'))
						{
							invalid_field++;
							console.log($(this));
							$(this).addClass('invalid_field');
						}
						else
						{
							$(this).removeClass('invalid_field');
						}
					}
					else
					{
						$(this).removeClass('invalid_field');
					}
				}
				
				
			});

			if(invalid_field == 0)
			{
				var departure = Date.parse($('input[name="jet_departure_date"]').val());
				var today = new Date();
				today.setDate(today.getDate() - 2);
				today = Date.parse(today);
				var days_between = Math.round((departure-today)/(1000*60*60*24));				
				var eventAction = $('#jet_origin').val()+'/'+$('#jet_destination').val();
				var eventLabel = days_between+'/'+$('#jet_departure_date').val()+'/'+$('#jet_pax').val();
				
				if(typeof ga !== typeof undefined)
				{	
					var eventArgs = {};
					eventArgs.eventCategory = 'Flight';
					eventArgs.eventAction = eventAction;
					eventArgs.eventLabel = eventLabel;
					ga('send', 'event', eventArgs);
					console.log(eventArgs);
				}
				else
				{
					console.log('jetcharters: GA not defined');
				}
				$('.jet_calculator').unbind('submit').submit();
			}
			else
			{
				console.log(invalid_field);
			}			
		});
	}
	function convertToSlug(Text)
	{
		Text = Text.toLowerCase();
		Text = Text.replace(/á/gi,"a");
		Text = Text.replace(/é/gi,"e");
		Text = Text.replace(/í/gi,"i");
		Text = Text.replace(/ó/gi,"o");
		Text = Text.replace(/ú/gi,"u");
		Text = Text.replace(/ñ/gi,"n");		
		Text = Text.replace(/ +/g,'-');
		Text = Text.replace(/[`~!@#$%^&*()_|+\=?;:'",.<>\{\}\[\]\\\/]/gi, '');
		Text = Text.replace(/\-\-/gi,"-");		
		return Text;
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
		if($('#jet_flight').val() == 1)
		{
			$('.jet_return').fadeIn();
		}
		$('#jet_flight').change(function(){
			if($(this).val() == 1)
			{
				$('.jet_return').fadeIn();
			}
			else
			{
				$('.jet_return').fadeOut();
				$('#jet_return_date').val('');
				$('#jet_return_hour').val('');
			}
		});		
	}
	
	function algolia_execute()
	{
		var client = algoliasearch(get_algolia_id(), get_algolia_token());
		var index = client.initIndex(get_algolia_index());

	$('.jet_calculator').each(function(){
		
		var this_form = $(this);
		
		$(this).find('.jet_list').each(function(){
			
			var this_field = $(this);
			
			$(this_field).autocomplete({
				hint: false
			},[{
				source: $.fn.autocomplete.sources.hits(index, {
					hitsPerPage: 4
				}),
				displayKey: 'airport',
				templates: {
					suggestion: function(suggestion) {

						var htmllang = $("html").attr("lang");
						htmllang = htmllang.slice(0, 2);
						htmllang.toLowerCase();
						var country_names = suggestion.country_names;
						var country_flag = suggestion.country_code;
						var flag_url = jsonsrc() + "img/flags/" + country_flag + '.svg';
						flag_url = flag_url.toLowerCase();
						var result = $('<div class="algolia_airport clearfix"><div class="sflag pull-left"><img width="45" height="33.75" /></div><div class="sdata"><div class="sairport"><span class="airport"></span> <strong class="iata"></strong></div><div class="slocation"><span class="city"></span>, <span class="country"></span></div></div></div>');
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

				$(this_field).attr({
					'data-iata': suggestion.iata,
					'data-lat': suggestion._geoloc.lat,
					'data-lon': suggestion._geoloc.lng
				}).addClass('jet_selected').val(selected_airport);	

				$(this_field).blur(function()
				{
					if ($(this_field).hasClass('jet_selected'))
					{
						$(this_field).val(selected_airport);
					}
					else
					{
						$(this_field).val('');
						$(this_field).removeClass('jet_selected');
						$(this_field).addClass('invalid_field');
						$(this_field).removeAttr('data-iata');
						$(this_field).removeAttr('data-lat');
						$(this_field).removeAttr('data-lon');						
					}
				});
					
				$(this_field).focus(function() {
					$(this_field).val('');
					$(this_field).removeClass('jet_selected');
					$(this_field).removeClass('invalid_field');
					$(this_field).removeAttr('data-iata');
					$(this_field).removeAttr('data-lat');
					$(this_field).removeAttr('data-lon');
				});					
						
				if($(this_form).find('.jet_selected').length == 1)
				{
					$('.jet_list').not('.jet_selected').focus();
				}
				if($(this_form).find('.jet_selected').length == 2)
				{
					$(this_form).find('input[name="jet_pax"]').focus();
				}
				else
				{
					$(this_field).blur();
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
		$('.countrylist').each(function() {
			for (var x = 0; x < data.length; x++) 
			{
				$(this).append('<option value=' + data[x][0] + '>' + data[x][1] + '</option>');
			}
		});		
	}