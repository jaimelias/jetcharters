<?php

global $airport_array;
$json = $airport_array;
$airport = $json['airport'];
$iata  = $json['iata'];
$icao = $json['icao'];
$codes = '('.$iata.')';
$city = $json['city'];
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

$addressArray = array(($airport.' '.$codes), $city, $country_lang);
$address = implode(', ', $addressArray);

$translations = pll_the_languages(array('raw'=>1));

foreach ($translations as $k => $v)
{
	if($v['slug'] == pll_default_language())
	{
		$hreflang = $v['slug'].'" href="'.$v['url'].'fly/'.Jetcharters_Public::cleanURL($airport);
		echo '<link rel="alternate" hreflang="'.($hreflang).'/" />';		
	}
	else
	{
		$hreflang = $v['slug'].'" href="'.home_url('/').$v['slug'].'/fly/'.Jetcharters_Public::cleanURL($airport);
		echo '<link rel="alternate" hreflang="'.($hreflang).'/" />';				
	}
}


?>

<meta name="description" content="<?php echo esc_html(__('Private Charter Flight', 'jetcharters')); ?> <?php echo esc_html($address); ?>. <?php echo esc_html(__('Airplanes and helicopter rides in', 'jetcharters')); ?> <?php echo esc_html($airport.', '.$city); ?>." />

<link rel="canonical" href="<?php echo esc_url(home_lang()); ?>fly/<?php echo esc_html(Jetcharters_Public::cleanURL($airport)); ?>/" />


<?php

$prices = array();
$ld_json = array();
$ld_json['@context'] = 'http://schema.org/';
$ld_json['@type'] = 'Product';
$ld_json['category'] = esc_html(__('Charter Flights', 'jetcharters'));
$ld_json['name'] = esc_html(__('Private Charter Flight', 'jetcharters').' '.$airport);
$ld_json['description'] = esc_html(__('Private Charter Flight', 'jetcharters').' '.$address.'. '.__('Airplanes and helicopter rides in', 'jetcharters').' '.$airport.', '.$city);
$ld_json['image'] = esc_url(Jetcharters_Public::airport_img_url());
$ld_json['brand'] = esc_html(get_bloginfo('name'));

if(get_theme_mod('minimalizr_large_icon'))
{
	$ld_json['logo'] = esc_url(get_theme_mod('minimalizr_large_icon'));
}

$args23 = array('post_type' => 'jet','posts_per_page' => 200, 'post_parent' => 0, 'meta_key' => 'jet_base_iata', 'orderby' => 'meta_value');
$args23['meta_query'] = array();

$meta_args = array(
	'key' => 'jet_base_iata',
	'value' => esc_html($iata),
	'compare' => '!='
);

$args23['meta_key'] = array();
array_push($args23['meta_query'], $meta_args);
$wp_query23 = new WP_Query( $args23 );

if ( $wp_query23->have_posts() )
{
	while ( $wp_query23->have_posts() )
	{
		$wp_query23->the_post();
		$table_price = Charterflights_Meta_Box::jet_get_meta( 'jet_rates' );
		$table_price = json_decode(html_entity_decode($table_price), true);
			
		
		for($x = 0; $x < count($table_price); $x++)
		{
			if(($iata == $table_price[$x][0] || $iata == $table_price[$x][1]) && ($table_price[$x][0] != '' || $table_price[$x][1] != ''))
			{
				array_push($prices, floatval($table_price[$x][3]));
			}
		}
	}
	wp_reset_postdata();
}

$offers = array();
$offers['@type'] = 'AggregateOffer';
$offers['priceCurrency'] = 'USD';

if(count($prices) > 1)
{
	$offers['lowPrice'] = number_format(min($prices), 2, '.', '');
	$offers['highPrice'] = number_format(max($prices), 2, '.', '');
	$ld_json['offers'] = $offers;
}
?>

<script type="application/ld+json"><?php echo json_encode($ld_json); ?></script> 