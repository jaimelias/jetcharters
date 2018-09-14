<script type="text/javascript">

(function($) {
	'use strict';
	$(document).ready(function() {
		
		if($('#stats-container').length && $('#search-airport').length && $('#container-airport').length)
		{
			var search = instantsearch({
			  appId: get_algolia_id(),
			  apiKey: get_algolia_token(),
			  indexName: get_algolia_index()
			  });		
			
			search.addWidget(
			  instantsearch.widgets.searchBox({
				container: '#search-airport',
				autofocus: false,
				placeholder: '<?php echo esc_html(__('Enter airport / city / country', 'jetcharters')); ?>',
				})
			);
			
			
			var noResultsTemplate = '<div class="text-center">No results found matching <strong>{{query}}</strong>.</div>';
					
			search.addWidget(
			  instantsearch.widgets.hits({
				container: '#container-airport',
				hitsPerPage: 5,
				templates: {
				  empty: noResultsTemplate,
				  item: function render_template(data) {
					  
						var htmllang = $("html").attr("lang");
						htmllang = htmllang.slice(0, 2);
						htmllang.toLowerCase();
						var country_lang = String('country_names.'+htmllang);
						
						var country_flag = data.country_code;
						var flag_url = jsonsrc() + "public/img/flags/" + country_flag + '.svg';
						flag_url = flag_url.toLowerCase();
						
						var template = $('<div></div>').append($('<span></span>').addClass('sflag').append($('<img>').attr({'class': 'sflag pull-left', 'src': flag_url, 'width': 80, 'height': 60})));
						template.append($('<div></div>').addClass('pull-left sdescription'));
						template.find('.sdescription').append($('<h4></h4>').append($('<a></a>').text(data.airport).attr({'href': '<?php echo esc_url(home_lang()); ?>fly/'+convertToSlug(data.airport)+'/'})));
						template.find('.sdescription').append($('<div></div>').html(data.city+', '+country_name(htmllang, data.country_names, data.country_code)));
						template.append($('<a></a>').html('<?php echo esc_html(__('Fly here', 'jetcharters')); ?>').attr({'class': 'btn sbutton pull-right', 'href': '<?php echo esc_url(home_lang()); ?>fly/'+convertToSlug(data.airport)+'/'}));
						return String(template.html());
					  }
				}
			  })
			);
			
			search.addWidget(
			  instantsearch.widgets.stats({
				container: '#stats-container',
				templates: {
				  body: function(data) {
					if(data.nbHits > 1)
					{
						var dest = '<?php echo esc_html(__('destinations', 'jetcharters')); ?>';
					}
					else
					{
						var dest = '<?php echo esc_html(__('destination', 'jetcharters')); ?>';
					}
					return data.nbHits + ' ' + dest;
				  }
				}
			  })
			);		
			
			search.addWidget(
			  instantsearch.widgets.pagination({
				container: '#pagination-container',
				maxPages: 20,
				scrollTo: false
			  })
			);
						
			search.start();
			
		}
				
	});	
	
})(jQuery);
</script>