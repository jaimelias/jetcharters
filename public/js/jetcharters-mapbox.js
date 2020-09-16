jQuery(() =>{

    'use strict';

	if(!is_mobile() && jQuery('.mapbox_form').length)
	{
		var jet_mapbox_vars = mapbox_vars();		
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

		jQuery(window).on("load", function() {
			//load map
			load_mapbox(index, true);

			map.on('moveend zoomend', function() {
				load_mapbox(index, false);
			});
			
			jQuery('.mapbox_form').each(function(){
				
				var this_mapbox_form = jQuery(this);
				
				jQuery(this_mapbox_form).find('.jet_list').blur(function(){			
					
					if(jQuery(this).hasClass('jet_selected') && jQuery(this_mapbox_form).find('.jet_selected').length == 1)
					{
						var getLatLng = [jQuery(this).attr('data-lat'), jQuery(this).attr('data-lon')];
						map.fitBounds([getLatLng, getLatLng]);
						map.setZoom(13);
					}
					else if(jQuery(this_mapbox_form).find('.jet_selected').length == 2)
					{
						var line_points = [];
						
						jQuery(this_mapbox_form).find('.jet_selected').each(function(){
							var row = [];
							row.push(jQuery(this).attr('data-lat'));
							row.push(jQuery(this).attr('data-lon'));
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


    const load_mapbox = (index, viaIP) => {

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

    const seachByIp = (err, content) => {

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
	

});

const obj = (ll) => {
	return { y: ll[0], x: ll[1] }; 
}

const convertToSlug = (Text) => {
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


const is_mobile = () => {
let isMobile = false; //initiate as false
// device detection
	if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent) 
		|| /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4))) { 
		isMobile = true;
	}
	return isMobile;
}	
	

