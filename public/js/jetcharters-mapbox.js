(function($) {

    'use strict';
		
	
	if(!is_mobile())
	{
		var jet_mapbox_vars = mapbox_vars();
		
		//mapbox & algolia vars
		var client = algoliasearch(get_algolia_id(), get_algolia_token());
		var index = client.initIndex(get_algolia_index());
		var set_lat = jet_mapbox_vars.mapbox_base_lat;
		var set_lon = jet_mapbox_vars.mapbox_base_lon;
		L.mapbox.accessToken = jet_mapbox_vars.mapbox_token;
		//map
		var map = L.mapbox.map('mapbox_airports', jet_mapbox_vars.mapbox_map_id, {
			zoomControl: false,
			minZoom: 4,
			maxZoom: 16
		}).setView([set_lat, set_lon], jet_mapbox_vars.mapbox_map_zoom);
		map.touchZoom.disable();
		map.doubleClickZoom.disable();
		map.scrollWheelZoom.disable();
		new L.Control.Zoom({
			position: 'bottomright'
		}).addTo(map);

		var overlays = L.layerGroup().addTo(map);

		$(window).on("load", function() {
			//load map
			load_mapbox(index, true);

			map.on('moveend zoomend', function() {
				load_mapbox(index, false);
			});
			
			$('.mapbox_form').each(function(){
				
				var this_mapbox_form = $(this);
				
				$(this_mapbox_form).find('.jet_list').blur(function(){			
					
					if($(this).hasClass('jet_selected') && $(this_mapbox_form).find('.jet_selected').length == 1)
					{
						var getLatLng = [$(this).attr('data-lat'), $(this).attr('data-lon')];
						map.fitBounds([getLatLng, getLatLng]);
						map.setZoom(13);
					}
					else if($(this_mapbox_form).find('.jet_selected').length == 2)
					{
						var line_points = [];
						
						$(this_mapbox_form).find('.jet_selected').each(function(){
							var row = [];
							row.push($(this).attr('data-lat'));
							row.push($(this).attr('data-lon'));
							line_points.push(obj(row));
						});
						
						map.eachLayer(function(layer){
							if(layer.hasOwnProperty('_path'))
							{
								map.removeLayer(layer);
							}
						});	

						var generator = new arc.GreatCircle(line_points[0], line_points[1]);
						var line = generator.Arc(100, { offset: 10 });
						var arc_line = L.polyline(line.geometries[0].coords.map(function(c) {
							return c.reverse();
						}), {
							color: '#ff6d33',
							weight: 5
						})
						.addTo(map);
						
						map.fitBounds(arc_line.getBounds(), {padding: [20,20]});
					}				
				});			
			});


		});		
	}


    function load_mapbox(index, viaIP) {

        var latlon = [map.getCenter().lat, map.getCenter().lng];
        latlon = latlon.join(',');

        if (viaIP === true) {
            index.search({
                hitsPerPage: 1000,
                aroundLatLngViaIP: true,
                minimumAroundRadius: 20000,
            }, seachByIp);
        } else {
            index.search({
                hitsPerPage: 1000,
                aroundLatLng: String(latlon),
                minimumAroundRadius: 20000,
            }, seachByIp);
        }

    }

    function seachByIp(err, content) {

        if (err) {
            console.error(err);
            return;
        }

        var json_array = [];
        overlays.clearLayers();
        var markers = new L.MarkerClusterGroup();

        markers.eachLayer(function(layer) {
            markers.removeLayer(layer);
        });


        for (var i = 0; i < content.hits.length; i++) {
            var json_obj = {};
            var lng = content.hits[i]._geoloc["lng"];
            var lat = content.hits[i]._geoloc["lat"];
            var city = content.hits[i]["city"];
            var airport = content.hits[i]["airport"];
            var iata = content.hits[i]["iata"];

            json_obj.type = 'Feature';
            json_obj.properties = {
                'marker-symbol': 'airport',
                'marker-color': '#9ACD32',
                'marker-size': 'large'
            };
            json_obj.geometry = {
                type: 'Point',
                coordinates: []
            };
            json_obj.geometry.coordinates.push(parseFloat(lng), parseFloat(lat));
            json_array.push(json_obj);

            var title = city + ' - ' + airport;
			
			if(iata != null)
			{
				title += ' (' + iata + ')';
			}

            var marker = L.marker(new L.LatLng(parseFloat(lat), parseFloat(lng)), {
                icon: L.mapbox.marker.icon({
                    'marker-symbol': 'airport',
                    'marker-color': '#dd3333',
                    'marker-size': 'large'
                }),
                title: title
            });
           marker.bindPopup('<div class="text-center"><a target="_top" class="large" href="'+jet_mapbox_vars.home_url+'fly/' + convertToSlug(content.hits[i]["airport"]) + '/">' + title + '</a></div>');

            markers.addLayer(marker);
        }
        overlays.addLayer(markers);
    }
	
	function obj(ll) {
		return { y: ll[0], x: ll[1] }; 
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
	

})(jQuery);
