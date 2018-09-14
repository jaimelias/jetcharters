<?php

//map token	
$mapbox_token = get_option('mapbox_token');
$mapbox_token = esc_html($mapbox_token['text_field_jetcharters_0']);

//pre_get_post
global $airport_array;
$json = $airport_array;
$iata  = $json['iata'];
$icao = $json['icao'];
$city = $json['city'];
$utc = $json['utc'];
$_geoloc = $json['_geoloc'];

//map position
$mapbox_width = 600;
$mapbox_height = 400;
$mapbox_zoom = 12;
$mapbox_marker = 'pin-l-airport+dd3333('.$_geoloc['lng'].','.$_geoloc['lat'].')';

//map id
$mapbox_map_id = get_option('mapbox_map_id');
$mapbox_map_id = esc_html($mapbox_map_id['text_field_jetcharters_1']);

$airport = $json['airport'];;

if($iata != null && $icao != null)
{
	$airport .= " ".__('Airport', 'jetcharters');
}


$static_map = Jetcharters_Public::airport_img_url();

$country_name = $json['country_names'];
$lang = substr(get_locale(), 0, -3);
if($lang)
{
	if(array_key_exists($lang, $country_name))
	{
		$country_lang = $country_name[$lang];
	}
	else
	{
		$country_lang = $country_name['en'];
	}
}

?>


<div class="pure-g gutters">

	<div class="pure-u-1 pure-u-sm-1-1 pure-u-md-2-3">
	<img class="mapbox_img" src="<?php echo esc_url($static_map); ?>" alt="<?php echo esc_html($airport).", ".esc_html($city); ?>" title="<?php echo esc_html($airport); ?>"/>
	
	</div>
	<div class="pure-u-1 pure-u-sm-1-1 pure-u-md-1-3">
		<table class="airport_description pure-table pure-table-striped">
			<?php if($iata != null && $icao != null): ?>
				<thead><tr><th colspan="2"><i class="fa fa-plane" aria-hidden="true"></i> <?php echo esc_html($airport); ?></th></tr></thead>
				<?php if($iata != null): ?>
				<tr><td>IATA</td><td><?php echo esc_html($iata); ?></td></tr>
				<?php endif;?>
				<?php if($icao != null): ?>
				<tr><td>ICAO</td><td><?php echo esc_html($icao); ?></td></tr>
				<?php endif; ?>
			<?php endif; ?>	
			<tbody>
				<tr><td><?php echo esc_html(__('City', 'jetcharters')); ?></td><td><?php echo esc_html($city); ?></td></tr>
				<tr><td><?php echo esc_html(__('Country', 'jetcharters')); ?></td><td><?php echo esc_html($country_lang); ?></td></tr>	
				<tr><td><?php echo esc_html(__('Longitude', 'jetcharters')); ?></td> <td><?php echo esc_html(round($_geoloc['lng'], 4)); ?></td></tr>
				<tr><td><?php echo esc_html(__('Latitude', 'jetcharters')); ?></td> <td><?php echo esc_html(round($_geoloc['lat'], 4)); ?></td></tr>	
				<tr><td><?php echo esc_html(__('Timezone', 'jetcharters')); ?></td> <td><?php echo esc_html($utc).' (UTC)'; ?></td></tr>
			</tbody>
		</table>
	</div>
	
</div>


<?php if(is_active_sidebar( 'quote-sidebar' )): ?>
	<h2><span class="linkcolor"><?php echo esc_html(__('Quote Charter Flight to', 'jetcharters'));?></span> <?php echo esc_html($airport); ?><span class="linkcolor">, <?php echo esc_html($city);?></span></h2>
	<ul id="quote-sidebar"><?php dynamic_sidebar('quote-sidebar'); ?></ul>
<?php endif; ?>