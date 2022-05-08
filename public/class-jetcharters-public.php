<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://jaimelias.com
 * @since      1.0.0
 *
 * @package    Jetcharters
 * @subpackage Jetcharters/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Jetcharters
 * @subpackage Jetcharters/public
 * @author     Jaimelías <jaimelias@about.me>
 */
class Jetcharters_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		add_shortcode( 'mapbox_airports', array('Jetcharters_Public', 'mapbox_airports') );
		add_shortcode( 'jetlist', array('Jetcharters_Public', 'jetlist') );
		add_shortcode( 'destination', array('Jetcharters_Public', 'filter_destination_table') );
		add_action( 'parse_query', array( &$this, 'on_quote_submit' ), 1);
		add_filter('minimal_sitemap', array(&$this, 'sitemap'), 10);
	}
	
	public static function on_quote_submit()
	{
		global $VALID_JET_RECAPTCHA;
		
		if(!isset($VALID_JET_RECAPTCHA))
		{
			if(Jetcharters_Validators::valid_jet_quote())
			{
				if(Jetcharters_Validators::validate_recaptcha())
				{
					$data = $_POST;
					$data['lang'] = substr( get_locale(), 0, 2 );
					
					$args50 = array('post_type' => 'jet','posts_per_page' => 1, 'p' => intval($data['aircraft_id']));	
					$wp_query50 = new WP_Query( $args50 );
					
					if($wp_query50->have_posts())
					{
						while ($wp_query50->have_posts())
						{
							$wp_query50->the_post();
							$data['operator'] = Charterflights_Meta_Box::jet_get_meta('operator');
							$data['operator_email'] = Charterflights_Meta_Box::jet_get_meta('operator_email');
							$data['operator_tel'] = Charterflights_Meta_Box::jet_get_meta('operator_tel');
							$data['operator_location'] = Charterflights_Meta_Box::jet_get_meta('operator_location');
						}
					}
					
					$subject = sprintf(__('%s, Your request was Sent to our Charter Experts!', 'jetcharters'), $data['lead_name']);

					require_once('email_template.php');
					
					$args = array(
						'subject' => $subject,
						'to' => sanitize_email($_POST['lead_email']),
						'message' => $email_template
					);
					
					sg_mail($args);

					self::webhook(json_encode($data));
					$GLOBALS['VALID_JET_RECAPTCHA'] = true;
				}
			}			
		}
	}	
	
	public static function deque_jetpack()
	{
		if(get_query_var('fly'))
		{	
			remove_action( 'wp_head', 'rel_canonical' );
			return false;
		}
	}
	public static function ld_json($arr)
	{
		if(get_query_var('fly'))
		{
			global $airport_array;

			if(is_object($airport_array) || is_array($airport_array))
			{
				
				[
					'city' => $city, 
					'iata' => $iata, 
					'city' => $city, 
					'airport' => $airport,
					'country_names' => $country_names
				] = $airport_array;
				
				
				$lang = substr(get_locale(), 0, -3);
				$prices = array();
				
				if($lang)
				{
					if(array_key_exists($lang, $country_names))
					{
						$country_lang = $country_names[$lang];
					}
					else
					{
						$country_lang = $country_names['en'];
					}
				}
				
				$addressArray = array(($airport.' ('.$iata.')'), $city, $country_lang);
				$address = implode(', ', $addressArray);		
				
				$args23 = array(
					'post_type' => 'jet',
					'posts_per_page' => 200,
					'post_parent' => 0,
					'meta_key' => 'jet_base_iata',
					'meta_query' => array(
						'key' => 'jet_base_iata',
						'value' => esc_html($iata),
						'compare' => '!='
					),
					'orderby' => 'meta_value'
				);
				
				$wp_query23 = new WP_Query( $args23 );

				if ($wp_query23->have_posts())
				{	
					while ( $wp_query23->have_posts() )
					{
						$wp_query23->the_post();
						$table_price = html_entity_decode(Charterflights_Meta_Box::jet_get_meta( 'jet_rates' ));
						$table_price = json_decode($table_price, true);

						if(is_array($table_price))
						{
							for($x = 0; $x < count($table_price); $x++)
							{
								$tp = $table_price[$x];
								
								if(($iata == $tp[0] || $iata == $tp[1]) && ($tp[0] != '' || $tp[1] != ''))
								{
									array_push($prices, floatval($tp[3]));
								}
							}							
						}
					}

					wp_reset_postdata();				
					
				}

				if(count($prices) > 0)
				{
					
					$arr = array(
						'@context' => 'http://schema.org/',
						'@type' => 'Product',
						'brand' => array(
							'@type' => 'Thing',
							'name' => esc_html(get_bloginfo('name'))
						),
						'category' => esc_html(__('Charter Flights', 'jetcharters')),
						'name' => esc_html(__('Private Charter Flight', 'jetcharters').' '.$airport),
						'description' => esc_html(__('Private Charter Flight', 'jetcharters').' '.$address.'. '.__('Airplanes and helicopter rides in', 'jetcharters').' '.$airport.', '.$city),
						'image' => esc_url(Jetcharters_Public::airport_img_url($airport_array, true)),
						'sku' => md5($iata),
						'gtin8' => substr(md5($iata), 0, 8)
					);

					$offers = array(
						'priceCurrency' => 'USD',
						'priceValidUntil' => esc_html(date('Y-m-d', strtotime('+1 year'))),
						'availability' => 'http://schema.org/InStock',
						'url' => esc_url(get_the_permalink())
					);
					
					if(count($prices) == 1)
					{
						$offers['@type'] = 'Offer';
						$offers['@type'] = 'Offer';
						$offers['price'] = number_format($prices[0], 2, '.', '');					
					}
					else
					{
						$offers['@type'] = 'AggregateOffer';
						$offers['lowPrice'] = number_format(min($prices), 2, '.', '');
						$offers['highPrice'] = number_format(max($prices), 2, '.', '');					
					}
					
					$arr['offers'] = $offers;				
				}				
			}
		}
		
		return $arr;
	}
	public static function jetlist($attr, $content = "")
	{
		ob_start();
		$GLOBALS['jet_attr'] = $attr;
		require_once(dirname( __FILE__ ) . '/partials/jet-archive.php');
		$content = ob_get_contents();
		ob_end_clean();	
		return $content;
	}
	public static function price_calculator()
	{
		ob_start();
		require_once(dirname( __FILE__ ) . '/partials/price-calculator.php');
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	public static function mapbox_airports($attr, $content = "")
	{
		if(!isset($_GET['fl_builder']))
		{	
	
			ob_start();
			
			?>
			<div class="pure-g">
				<div class="mapbox_form pure-u-1 pure-u-sm-1-1 pure-u-md-2-5">
			<?php
			
			require_once(dirname( __FILE__ ) . '/partials/price-calculator.php');
					
			?>
				</div>
					<div class="pure-u-1 pure-u-sm-1-1 pure-u-md-3-5">
						<div class="map-container">
							<div class="map" id="mapbox_airports">
							</div>
						</div>
					</div>
			</div>
			<?php
			
			$content = ob_get_contents();
			ob_end_clean();		
		}
		else
		{
			 $content = '<h2 class="text-center">'.__('Mapbox preview not available in editing mode.', 'jetcharters').'</h2>';
		}
		
		return $content;
	}
	public static function algoliasearch_after()
	{
		$output = null;
		if(get_option('algolia_token') && get_option('algolia_index') && get_option('algolia_id'))
		{
			$output .= 'const algoliaClient = algoliasearch(getAlgoliaId, getAlgoliaToken);';
			$output .= 'const algoliaIndex = algoliaClient.initIndex(getAlgoliaIndex);';
		}
		return $output;
	}
	public static function algoliasearch_before()
	{
		$output = null;
		$algolia_token = get_option('algolia_token');
		$algolia_index = get_option('algolia_index');
		$algolia_id = get_option('algolia_id');
		
		if($algolia_token && $algolia_index && $algolia_id)
		{
			$output .= 'const getAlgoliaToken = "'.esc_html($algolia_token).'";';	
			$output .= 'const getAlgoliaIndex = "'.esc_html($algolia_index).'";';
			$output .= 'const getAlgoliaId = "'.esc_html($algolia_id).'";';
		}
		return $output;
	}
	public static function json_src_url()
	{
		return 'const jsonsrc = () => { return "'.esc_url(plugin_dir_url( __FILE__ )).'";}';
	}

	public static function jet_calculator()
	{
			ob_start();
			require(plugin_dir_path( __FILE__ ).'partials/price-calculator.php');
			$output = ob_get_contents();
			ob_end_clean();
			return $output;
	}
	public static function modify_wp_title($title)
	{	 
		if(get_query_var( 'fly' ))
		{
			global $airport_array;
			//jaimelias
			
			if(is_array($airport_array))
			{
				if(count($airport_array) > 0)
				{
					$title = __("Private Charter Flight", "jetcharters").' '.$airport_array['airport'];

					if($airport_array['iata'] != null && $airport_array['icao'] != null)
					{
						$title .= ' ['.$airport_array['iata'].']';
					}
					
					$title .= ' '.$airport_array['city'].' | '.get_bloginfo('name');
					$title =  esc_html($title);
				}
				else
				{
					return esc_html(__('Destination Not Found', 'jetcharters'));
				}				
			}
			else
			{
				return esc_html(__('Destination Not Found', 'jetcharters'));
			}			
		}
		elseif(Jetcharters_Validators::valid_jet_quote())
		{
			return esc_html(__("Request Submitted", "jetcharters").' | '.esc_html(get_bloginfo('name')));
		}		
		elseif(Jetcharters_Validators::valid_jet_search())
		{
			$output = null;
			$output .= esc_html(__("Find an Aircraft", "jetcharters")).' ';			
			$output .= sanitize_text_field($_GET['jet_origin']).'-'.sanitize_text_field($_GET['jet_destination']);
			$output .= ' | '.esc_html(get_bloginfo('name'));
			return $output;
			
		}		
		elseif(is_singular('jet'))
		{			
			if(Charterflights_Meta_Box::jet_get_meta( 'jet_type' ))
			{
				$jet_type = Charterflights_Meta_Box::jet_get_meta( 'jet_type' );
				$jet_type = self::jet_type($jet_type);
				$title .= $jet_type .' '.get_the_title().' | '.get_bloginfo( 'name', 'display' );
				return $title;
			}
		}
		return $title;
	}
	public static function modify_title($title)
	{	
			if(in_the_loop() && is_singular('jet'))
			{
				if(Charterflights_Meta_Box::jet_get_meta( 'jet_type' ))
				{
					$jet_type = self::jet_type(Charterflights_Meta_Box::jet_get_meta( 'jet_type' ));
					$title = '<span class="linkcolor">'.esc_html($jet_type).'</span> '.$title;
				}				
			}
			elseif(in_the_loop() && Jetcharters_Validators::valid_jet_search())
			{
				$title = esc_html(__("Find an Aircraft", "jetcharters"));
			}
			elseif(in_the_loop() && Jetcharters_Validators::valid_jet_quote())
			{
				$title = esc_html(__("Request Submitted", "jetcharters"));
			}			
			elseif(in_the_loop() && get_query_var( 'fly' ))
			{
				global $airport_array;
				//jaimelias
				
				if(is_array($airport_array))
				{
					if(count($airport_array) > 0)
					{
						$json = $airport_array;
						$title = '<span class="linkcolor">'.esc_html(__('Charter Flights','jetcharters')).'</span> '.esc_html($json['airport']).' <span class="linkcolor">'.esc_html($json['city']).'</span>';						
					}
					else
					{
						$title = esc_html(__('Destination Not Found', 'jetcharters'));
					}				
				}
				else
				{
					$title = esc_html(__('Destination Not Found', 'jetcharters'));
				}					
			}
		return $title;
	}
	public static function modify_content($content)
	{	if(in_the_loop() && get_query_var( 'fly' ))
		{
			global $airport_array;
			$json = $airport_array;
			$output = null;

			if(is_array($json))
			{
				if(count($json) > 0)
				{
		
					$output .= self::get_destination_table(esc_html($json['iata']));		$output .= self::get_destination_content(esc_html($json['iata']));
					ob_start();
					require_once(plugin_dir_path( __FILE__ ).'partials/jetcharters-public-display.php');
					$output .= ob_get_contents();
					ob_end_clean();
				}				
			}
			
			return $output;
		}
		elseif(Jetcharters_Validators::valid_jet_quote())
		{
			global $VALID_JET_RECAPTCHA;
			
			if(isset($VALID_JET_RECAPTCHA))
			{				
				return '<p class="minimal_success">'.esc_html(__('Request received. Our sales team will be in touch with you soon.', 'jetcharters')).'</p>';
			}
			else
			{
				return '<p class="minimal_alert">'.esc_html(__('Invalid Recaptcha', 'jetcharters')).'</p>';
			}
		}		
		elseif(Jetcharters_Validators::valid_jet_search())
		{
			if(Jetcharters_Validators::validate_hash())
			{
				ob_start();
				require_once(plugin_dir_path( __FILE__ ).'partials/jet_search.php');
				$output = ob_get_contents();
				ob_end_clean();
				return $output;				
			}
			else
			{
				return '<p class="minimal_alert">'.esc_html(__('Invalid Request', 'jetcharters')).'</p>';
			}
		}
		elseif(in_the_loop() && is_singular('jet'))
		{
			ob_start();
			require_once(plugin_dir_path( __FILE__ ).'partials/jetcharters-jet-single.php');
			$output = ob_get_contents();
			ob_end_clean();
			return $output;			
		}
		return $content;
	}


	public static function mapbox_vars()
	{
		$mapbox_vars = array(
			'mapbox_token' => esc_html(get_option('mapbox_token')),
			'mapbox_map_id' => esc_html(get_option('mapbox_map_id')),
			'mapbox_map_zoom' => intval(get_option('mapbox_map_zoom')),
			'mapbox_base_lat' => floatval(get_option('mapbox_base_lat')),
			'mapbox_base_lon' => floatval(get_option('mapbox_base_lon')),
			'home_url' => home_lang(),
		);

		return 'function mapbox_vars(){return '.json_encode($mapbox_vars).';}';
	}
	public static function meta_tags()
	{	if(get_query_var( 'fly' ))
		{
			global $airport_array;
			//jaimelias
			
			if(is_array($airport_array))
			{
				if(count($airport_array) > 0)
				{
					ob_start();
					require_once(plugin_dir_path( __FILE__ ).'partials/metatags-fly.php');
					$output = ob_get_contents();
					ob_end_clean();
					echo $output;				
				}
				else
				{
					$output = null;
				}				
			}
			else
			{
				$output = null;
			}
		}
		if(is_singular('jet'))
		{
			ob_start();
			require_once(plugin_dir_path( __FILE__ ).'partials/metatags-jet.php');
			$output = ob_get_contents();
			ob_end_clean();
			echo $output;			
		}
	}	
	public static function main_wp_query($query)
	{
		if(get_query_var( 'fly' ) && $query->is_main_query())
		{
			$GLOBALS['airport_array'] = json_decode(self::return_json(), true); 
						
			global $polylang;
			//removes alternate to home
			if($polylang)
			{
				remove_filter('wp_head', array($polylang->links, 'wp_head'));
			}
			
			//add main query to bypass not found error
			$query->set('post_type', 'page');
			$query->set( 'posts_per_page', 1 );
		}
		elseif( Jetcharters_Validators::valid_jet_search() || Jetcharters_Validators::valid_jet_quote())
		{
			if($query->is_main_query())
			{
				$query->set('post_type', 'page');
				$query->set( 'posts_per_page', 1 );				
			}
		}
	}
	public static function airport_img_url($json, $redirect_mobile)
	{
		//$json, $redirect_mobile
		$airport = $json['airport'];
		$url = home_url('cacheimg/'.self::cleanURL($airport).'.jpg');		
		return $url;
		
	}
	
	public static function airport_url_string($json)
	{
		//json
		$_geoloc = $json['_geoloc'];
		
		//mapbox options
		$mapbox_token = get_option('mapbox_token');
		
		//map position
		$mapbox_marker = 'pin-l-airport+dd3333('.$_geoloc['lng'].','.$_geoloc['lat'].')';

		return 'https://api.mapbox.com/styles/v1/mapbox/streets-v11/static/'.esc_html($mapbox_marker).'/'.esc_html($_geoloc['lng']).','.esc_html($_geoloc['lat']).',8/600x400?access_token='.esc_html($mapbox_token);				
	}
	
	public static function redirect_cacheimg()
	{
		if(get_query_var( 'cacheimg' ) && !in_the_loop())
		{
			$json = json_decode(self::return_json(), true);
			$static_map = self::airport_url_string($json);
			wp_redirect(esc_url($static_map));
			exit;
		}
	}	
	public static function sitemap($sitemap)
	{
		if(isset($_GET['minimal-sitemap']))
		{
			if($_GET['minimal-sitemap'] == 'airports')
			{
				global $polylang;
				if(isset($polylang))
				{
					$languages = PLL()->model->get_languages_list();
					$language_list = array();
					
					for($x = 0; $x < count($languages); $x++)
					{
						foreach($languages[$x] as $key => $value)
						{
							if($key == 'slug' && $value != pll_default_language())
							{
								array_push($language_list, $value);
							}
						}	
					}					
				}
				
				$urllist = null;
				$browse_json = self::return_json();
				$browse_json = $browse_json['hits'];
				
				for($x = 0; $x < count($browse_json); $x++)
				{
					$url = '<url>';
					$url .= '<loc>'.esc_url(home_url().'/fly/'.self::cleanURL($browse_json[$x]['airport'])).'/</loc>';
					$url .= '<image:image>';
					$url .= '<image:loc>'.esc_url(home_url().'/cacheimg/'.self::cleanURL($browse_json[$x]['airport'])).'.jpg</image:loc>';
					$url .= '</image:image>';
					$url .= '<mobile:mobile/>';
					$url .= '<changefreq>weekly</changefreq>';
					$url .= '</url>';
					$urllist .= $url;					
				}
				
				if(count($language_list) > 0)
				{
					for($y = 0; $y < count($browse_json); $y++)
					{
						$pll_url = '<url>';
						$pll_url .= '<loc>'.esc_url(home_url().'/'.$language_list[0].'/fly/'.self::cleanURL($browse_json[$y]['airport'])).'/</loc>';
						$pll_url .= '<image:image>';
						$pll_url .= '<image:loc>'.esc_url(home_url().'/cacheimg/'.self::cleanURL($browse_json[$y]['airport'])).'.jpg</image:loc>';
						$pll_url .= '</image:image>';
						$pll_url .= '<mobile:mobile/>';
						$pll_url .= '<changefreq>weekly</changefreq>';
						$pll_url .= '</url>';
						$urllist .= $pll_url;					
					}					
				}
				
				$sitemap =  '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
				$sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
				xmlns:mobile="http://www.google.com/schemas/sitemap-mobile/1.0"
				xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
				$sitemap .= $urllist;
				$sitemap .= '</urlset>';
			}
		}
		return $sitemap;
	}
	public static function cleanURL($url)
	{
		// Lowercase the URL
		$url = strtolower($url);
		// Additional Swedish filters
		
		$unwanted_array = array('Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E','Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
		
		$url = strtr( $url, $unwanted_array );
		
		// Remove any character that is not alphanumeric, white-space, or a hyphen 
		$url = preg_replace("/[^a-z0-9\s\-]/i", "", $url);
		// Replace multiple instances of white-space with a single space
		$url = preg_replace("/\s\s+/", " ", $url);
		// Replace all spaces with hyphens
		$url = preg_replace("/\s/", "-", $url);
		// Replace multiple hyphens with a single hyphen
		$url = preg_replace("/\-\-+/", "-", $url);
		// Remove leading and trailing hyphens
		$url = trim($url, "-");

		return $url;
	}
	public function package_template($template)
	{
		if(Jetcharters_Validators::valid_jet_quote())
		{
			$new_template = locate_template( array( 'page.php' ) );
			return $new_template;			
		}
		if(get_query_var( 'fly' ) || Jetcharters_Validators::valid_jet_search() || is_singular('jet'))
		{
			$new_template = locate_template( array( 'page.php' ) );
			return $new_template;			
		}
		return $template;
	}
	
	public static function return_json() {
		
		$algolia_token = get_option('algolia_token');
		$algolia_index = get_option('algolia_index');
		$algolia_id = get_option('algolia_id');
		
		$curl = curl_init();
		
		$headers = array();
		$headers[] = 'X-Algolia-API-Key: '.$algolia_token;
		$headers[] = 'X-Algolia-Application-Id: '.$algolia_id;

		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		
		if(get_query_var( 'fly' ) != '')
		{
			$new_query_var = get_query_var( 'fly' );
			$query_param = '?query='.$new_query_var.'&hitsPerPage=1';
		}
		if(get_query_var( 'cacheimg' ) != '')
		{
			$new_query_var = get_query_var( 'cacheimg' );
			$query_param = '?query='.$new_query_var.'&hitsPerPage=1';
		}
		else
		{
			$query_param = 'browse?cursor=';
		}
		
		curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_REFERER => esc_url(home_url()),
		CURLOPT_URL => 'https://'.$algolia_id.'-dsn.algolia.net/1/indexes/'.$algolia_index.'/'.$query_param,
		));
		$resp = curl_exec($curl);
		$resp = json_decode($resp, true);
			
		
		if(get_query_var( 'fly' ) != '' || get_query_var( 'cacheimg' ) != '')
		{
			$json = $resp['hits'];
			
			if(is_array($json))
			{
				for($x = 0; $x < count($json); $x++)
				{
					if($new_query_var === self::cleanURL($json[$x]["airport"]))
					{
						return json_encode($json[$x]);
					}
				}			
				
			}	
		}
		else
		{
			return $resp;
		}
		
	}
	
	

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Jetcharters_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Jetcharters_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		
		self::css();
		self::datepickerCSS();
		self::mapboxCSS();
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Jetcharters_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Jetcharters_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		global $post;
		self::cf7_dequeue_recaptcha();
		$dep = array('jquery', 'landing-cookies');
		wp_enqueue_script( 'landing-cookies', plugin_dir_url( __FILE__ ).'js/cookies.js', array('jquery'), $this->version, true );		
		
		if(((is_a($post, 'WP_Post') && has_shortcode( $post->post_content, 'mapbox_airports')) || is_singular('jet')) && !isset($_GET['fl_builder']))
		{
			array_push($dep, 'algolia', 'mapbox', 'markercluster', 'sha512', 'picker-date-js', 'picker-time-js');
			
			wp_enqueue_script('algolia', plugin_dir_url( __FILE__ ).'js/algoliasearch.min.js', array( 'jquery' ), '3.32.0', true );
			wp_add_inline_script('algolia', self::json_src_url(), 'before');
			wp_add_inline_script('algolia', self::algoliasearch_before(), 'before');
			wp_add_inline_script('algolia', self::algoliasearch_after(), 'after');
			wp_enqueue_script('algolia_autocomplete', plugin_dir_url( __FILE__ ).'js/autocomplete.jquery.min.js', array( 'jquery' ), '0.36.0', true );
			
			wp_enqueue_script( 'mapbox', 'https://api.mapbox.com/mapbox.js/v3.3.1/mapbox.js', array( 'jquery', 'algolia'), '3.3.1', true );
			
			wp_enqueue_script( 'markercluster', 'https://api.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v1.0.0/leaflet.markercluster.js', array( 'jquery', 'mapbox' ), $this->version, true );		
			wp_add_inline_script('mapbox', self::get_inline_js('jetcharters-arc'), 'after');
			wp_add_inline_script('mapbox', self::mapbox_vars(), 'after');
			wp_add_inline_script('mapbox', self::get_inline_js('jetcharters-mapbox'), 'after');
			wp_enqueue_script('sha512', plugin_dir_url( __FILE__ ) . 'js/sha512.js', array(), 'async_defer', true );
			self::datepickerJS();			
			wp_enqueue_script($this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/jetcharters-public.js', $dep, '', true );
		}
		
		if(Jetcharters_Validators::valid_jet_search())
		{
			$recap = false;
			
			if(!function_exists('is_booking_page'))
			{
				$recap = true;
			}
			else
			{
				if(!is_booking_page())
				{
					$recap = true;
				}
			}

			if($recap === true)
			{
				//recaptcha
				wp_enqueue_script('invisible-recaptcha', 'https://www.google.com/recaptcha/api.js', '', 'async_defer_jetcharters', true );	
				array_push($dep, 'invisible-recaptcha');
			}
			
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/jetcharters-public.js', $dep, $this->version, true );
			wp_add_inline_script($this->plugin_name, self::json_src_url(), 'before');
		}
	}
	
	public static function cf7_dequeue_recaptcha()
	{
		$dequeu = true;
		
		if(is_singular())
		{
			global $post;
			
			if(has_shortcode($post->post_content, 'contact-form-7'))
			{
				$dequeu = false;
			}
		}
		
		if($dequeu === true)
		{
			wp_dequeue_script('google-recaptcha-js');
		}
	}	
	
	public static function css()
	{
		global $post;

		wp_enqueue_style('minimalLayout', plugin_dir_url( __FILE__ ) . 'css/minimal-layout.css', array(), '', 'all' );
		
		
		if(get_query_var('fly'))
		{
			wp_add_inline_style('minimalLayout', self::get_inline_css('dynamicpackages-public'));
		}
		if(is_singular('jet') || (is_a( $post, 'WP_Post' ) && (has_shortcode( $post->post_content, 'mapbox_airports') || has_shortcode( $post->post_content, 'jc_calculator') || has_shortcode( $post->post_content, 'jetlist'))))
		{
			wp_add_inline_style('minimalLayout', self::get_inline_css('jetcharters-public'));
		}
	}
	
	public static function datepickerCSS()
	{
		global $post;
		
		if(is_a( $post, 'WP_Post' ) && (has_shortcode( $post->post_content, 'mapbox_airports') || has_shortcode( $post->post_content, 'jc_calculator')) || is_singular('jet'))
		{
			wp_enqueue_style( 'picker-css', plugin_dir_url( __FILE__ ) . 'css/picker/default.css', array(), 'jetcharters', 'all' );
			wp_add_inline_style('picker-css', self::get_inline_css('picker/default.date'));
			wp_add_inline_style('picker-css', self::get_inline_css('picker/default.time'));				
		}		
	}
	
	public static function mapboxCSS()
	{
		global $post;
		
		if(is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'mapbox_airports') && !isset($_GET['fl_builder']))
		{
			wp_enqueue_style('mapbox', 'https://api.mapbox.com/mapbox.js/v3.3.1/mapbox.css', array(), '3.1.1', 'all' );
			wp_add_inline_style('mapbox', self::get_inline_css('MarkerCluster'));
			wp_add_inline_style('mapbox', self::get_inline_css('MarkerCluster.Default'));
		}		
	}
	
	public static function datepickerJS()
	{
		//pikadate
		wp_enqueue_script( 'picker-js', plugin_dir_url( __FILE__ ) . 'js/picker/picker.js', array('jquery'), '3.5.6', true);
		wp_enqueue_script( 'picker-date-js', plugin_dir_url( __FILE__ ) . 'js/picker/picker.date.js', array('jquery', 'picker-js'), '3.5.6', true);
		wp_enqueue_script( 'picker-time-js', plugin_dir_url( __FILE__ ) . 'js/picker/picker.time.js',array('jquery', 'picker-js'), '3.5.6', true);	
		wp_enqueue_script( 'picker-legacy', plugin_dir_url( __FILE__ ) . 'js/picker/legacy.js', array('jquery', 'picker-js'), '3.5.6', true);

		$picker_translation = 'js/picker/translations/'.substr(get_locale(), 0, -3).'.js';
				
		if(file_exists(dirname( __FILE__ ).'/'.$picker_translation))
		{
			wp_enqueue_script( 'picker-time-translation', plugin_dir_url( __FILE__ ).$picker_translation, array('jquery', 'picker-js'), '3.5.6', true);
		}		
	}
	
	public static function distance($lat1, $lon1, $lat2, $lon2, $unit) {

	  $theta = $lon1 - $lon2;
	  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
	  $dist = acos($dist);
	  $dist = rad2deg($dist);
	  $miles = $dist * 60 * 1.1515;
	  $unit = strtoupper($unit);

	  if ($unit == "K") {
		return ($miles * 1.609344);
	  } elseif ($unit == "N") {
		  return ($miles * 0.8684);
		} else {
			return $miles;
		  }
	}
	public static function convertTime($dec)
	{
		// start by converting to seconds
		$seconds = ($dec * 3600);
		// we're given hours, so let's get those the easy way
		$hours = floor($dec);
		// since we've "calculated" hours, let's remove them from the seconds variable
		$seconds -= $hours * 3600;
		// calculate minutes left
		$minutes = floor($seconds / 60);
		// return the time formatted HH:MM
		return self::lz($hours).":".self::lz($minutes);
	}	
	public static function lz($num)
	{
		return (strlen($num) < 2) ? "0{$num}" : $num;
	}
	public static function jet_type($type)
	{
		if($type == 0)
		{
			return __('Turbo Prop', 'jetcharters');
		}
		elseif($type == 1)
		{
			return __('Light Jet', 'jetcharters');			
		}
		elseif($type == 2)
		{
			return __('Mid-size Jet', 'jetcharters');			
		}
		elseif($type == 3)
		{
			return __('Heavy Jet', 'jetcharters');			
		}
		elseif($type == 4)
		{
			return __('Airliner', 'jetcharters');		
		}
		elseif($type == 5)
		{
			return __('Helicopter', 'jetcharters');		
		}		
	}
	
	public static function get_destination_content($iata)
	{
		
		$output = null;
		$new_content = null;
		global $polylang;
		
		//destination
		$args21 = array('post_type' => 'destinations','posts_per_page' => 1, 'post_parent' => 0);
		
		if($polylang)
		{
			$args21['lang'] = pll_current_language();
		}		
		
		$args21['meta_query'] = array();
		
		$meta_args = array(
			'key' => 'jet_base_iata',
			'value' => esc_html($iata),
			'compare' => '='
		);
		
		$args21['meta_key'] = array();
		array_push($args21['meta_query'], $meta_args);
		$wp_query21 = new WP_Query( $args21 );

		//destination	
		if ( $wp_query21->have_posts() )
		{
			while ( $wp_query21->have_posts() )
			{
				$wp_query21->the_post();	
				
				$new_content .= '<div class="dynamic-related">';
				$new_content .= get_the_content();
				$new_content .= '</div>';
				
				if( current_user_can('editor') || current_user_can('administrator') )
				{
					ob_start();
					edit_post_link('<i class="fas fa-pencil-alt" ></i> '.__('Edit Destination'), '<p class="small">', '</p>', '', 'pure-button' );
					$new_content .= ob_get_contents();
					ob_end_clean();					
				}				
			}
			wp_reset_postdata();
		}
		
		$output .= $new_content;
		return $output;
		
	}
	
	public static function webhook($data)
	{
		
		if(get_option('jet_webhook'))
		{
			$webhook = get_option('jet_webhook');
			
			if(!filter_var($webhook, FILTER_VALIDATE_URL) === false)
			{
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $webhook);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($data)));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch,CURLOPT_TIMEOUT, 20);
				$result = curl_exec($ch);
				$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
				
				if (intval($httpCode) === 200)
				{
					//do nothing
				}
				else
				{
					$admin_email = get_option( 'admin_email' );
					$time = current_time('timestamp', $gmt = 0 );
					$time = date_i18n(get_option('date_format'), $time);
					write_log('Jetcharters Webhook Error - '.$time.': '.$result);
					wp_mail( $admin_email, 'Jetcharters Webhook Error - '.$time, $result);	
				}
			}
		}
	}
	public static function filter_destination_table($attr, $content = "")
	{
		if($attr)
		{
			if(array_key_exists("iata", $attr))
			{
				$content = self::get_destination_table($attr['iata']);
			}
		}
		return $content;
	}
	public static function get_destination_table($iata)
	{
		$output = null;
		$filter = null;
		$aircraft_count = 0;
		$table_row = null;
		global $airport_array;
		
		$iata_list = array();
		
		//aircrafts
		$args22 = array('post_type' => 'jet','posts_per_page' => 200, 'post_parent' => 0, 'meta_key' => 'jet_commercial', 'orderby' => 'meta_value', 'order' => 'ASC');
		
		if(is_singular('jet'))
		{
			$args22['p'] = get_the_ID();
		}
		
		$wp_query22 = new WP_Query( $args22 );
		
		//aircraft
		if ( $wp_query22->have_posts() )
		{
			
			$algolia_full = self::algolia_full();
			
			while ( $wp_query22->have_posts() )
			{
				$wp_query22->the_post();
				global $post;
				$base_iata = Charterflights_Meta_Box::jet_get_meta( 'jet_base_iata' );
				$table_price = Charterflights_Meta_Box::jet_get_meta( 'jet_rates' );
				$table_price = json_decode(html_entity_decode($table_price), true);
				
				for($x = 0; $x < count($algolia_full); $x++)
				{
					if($iata == $algolia_full[$x]['iata'])
					{
						$destination_airport = $algolia_full[$x]['airport'];
						$destination_city = $algolia_full[$x]['city'];
						$destination_country_code = $algolia_full[$x]['country_code'];
					}
				}
				
				$aircraft_url = home_lang().esc_html($post->post_type).'/'.esc_html($post->post_name);
				
				$limit = 5;
				
				for($x = 0; $x < count($table_price); $x++)
				{
					
					$origin_iata = $table_price[$x][1];
					
					if($iata == $table_price[$x][1])
					{
						$origin_iata = $table_price[$x][0];
					}
					
					if(($iata == $table_price[$x][0] || $iata == $table_price[$x][1]) && ($table_price[$x][0] != '' || $table_price[$x][1] != '') && is_array($algolia_full))
					{
						
						for($y = 0; $y < count($algolia_full); $y++)
						{
							if($origin_iata == $algolia_full[$y]['iata'])
							{
								$origin_airport = $algolia_full[$y]['airport'];
								$origin_city = $algolia_full[$y]['city'];
								$origin_country_code = $algolia_full[$y]['country_code'];
							}
						}

						$fees = $table_price[$x][4];
						$seats = $table_price[$x][6];
						$weight_pounds = $table_price[$x][7];
						$weight_kg = intval(intval($weight_pounds)*0.453592);
						$weight_allowed = esc_html($weight_pounds.' '.__('pounds', 'jetcharters').' | '.$weight_kg.__('kg', 'jetcharters'));
						$jet_type = self::jet_type(Charterflights_Meta_Box::jet_get_meta( 'jet_type' ));
						
						$route = __('Private Charter Flight', 'jetcharters').' '.$jet_type.' '.$post->post_title.' '.__('from', 'jetcharters').' '.$origin_airport.', '.$origin_city.' ('.$origin_iata.') '.__('to', 'jetcharters').' '.$destination_airport.', '.$destination_city.' ('.$iata.')';
						
						$table_row .= '<tr data-jet-type="'.esc_html(Charterflights_Meta_Box::jet_get_meta( 'jet_type' )).'" data-iata="'.esc_html($origin_iata).'" title="'.esc_html($route).'">';
						
						if(!is_singular('jet'))
						{
							if(self::is_commercial())
							{
								$table_row .= '<td><strong>'.esc_html(__('Commercial Flight', 'jetcharters')).'</strong></td>';
							}
							else if(self::ferry())
							{
								$table_row .= '<td colspan="2"><strong>'.esc_html(__('Ferry', 'jetcharters')).'</strong></td>';
							}
							else if(self::ground())
							{
								$table_row .= '<td colspan="2"><strong>'.esc_html(__('Ground Transport', 'jetcharters')).'</strong></td>';
							}
							else
							{
								$table_row .= '<td><a class="strong" href="'.esc_url($aircraft_url).'/">'.esc_html($post->post_title).'</a> - <small>'.esc_html($jet_type).'</small><br/><i class="fas fa-male" ></i> '.esc_html($seats).' <small>('.$weight_allowed.')</small></td>';
							}
						}
						
						if(!self::ferry() && !self::ground())
						{
							$table_row .= '<td><small class="text-muted">('.esc_html($origin_iata).')</small> <strong>'.esc_html($origin_city.', '.$origin_country_code).'</strong><br/>'.esc_html($origin_airport).'</td>';							
						}

						
						

						$table_row .= '<td><strong>'.esc_html('$'.number_format($table_price[$x][3], 2, '.', ',')).'</strong><br/><span class="text-muted">';

						if(self::is_commercial() || self::ferry() ||self::ground())
						{
							$table_row .= esc_html(__('Per Person', 'jetcharters'));
						}
						else
						{
							$table_row .= esc_html(__('Charter Flight', 'jetcharters'));
						}
						$table_row .= '</span>';
						
						if(floatval($fees) > 0)
						{
							$table_row .= '<br/><span class="text-muted">';
							$table_row .= esc_html(__('Fees per pers.', 'jetcharters').' '.'$'.number_format($fees, 2, '.', ','));
							$table_row .= '</span>';
						}						
						$table_row .= '<br/><span class="small text-muted"><i class="fas fa-clock" ></i> '.esc_html(self::convertTime($table_price[$x][2])).'</span>';
						
						$table_row .= '</td></tr>';
						$aircraft_count++;	
					}
				}
			}
			wp_reset_postdata();
		}	

		if($aircraft_count > 0)
		{
			$airport_options = null;
			$jet_type_list = array();
			$jet_list_option = null;	
			$table = '';
			
			if(is_singular('jet'))
			{
				$table .= '<div itemscope itemtype="http://schema.org/Table"><h4 itemprop="about">'.esc_html(__('Charter Flights', 'jetcharters').' '.Charterflights_Meta_Box::jet_get_meta( 'jet_base_name' ).' ('.Charterflights_Meta_Box::jet_get_meta( 'jet_base_iata' )).') '.Charterflights_Meta_Box::jet_get_meta( 'jet_base_city' ).'</h4>';
			}
			else if(get_query_var('fly'))
			{
				//do nothing
			}
			else
			{
				$table .= '<div itemscope itemtype="http://schema.org/Table"><h3 itemprop="about">'.esc_html(__('Flights to ', 'jetcharters')).' '.esc_html($destination_airport).' ('.esc_html($iata).'), '.esc_html($destination_city).', '.esc_html($destination_country_code).'</h3>';
			}
			
			$table .= '<table id="dy_table" class="text-center small pure-table pure-table-bordered margin-bottom"><thead><tr>';
			
			
			$origin_label = __('Destination', 'jetcharters');
			
			if(!is_singular('jet'))
			{
				$origin_label = __('Origin', 'jetcharters');
				$table .= '<th>'.esc_html(__('Flights', 'jetcharters')).'</th>';
			}
			
			$table .= '<th>'.esc_html($origin_label).'</th>';
			$table .= '<th>'.esc_html(__('One Way', 'jetcharters')).'</th>';
			$table .= '</tr></thead><tbody>';
			$table .= $table_row;
			$table .= '</tbody></table>';
			
			if(!get_query_var('fly'))
			{
				$table .= '</div>';
			}
			
			$output .=  $table;
			return $output;
		}		
	}
	
	public static function algolia_full()
	{
		$query_param = 'browse?cursor=';
		$algolia_token = get_option('algolia_token');
		$algolia_index = get_option('algolia_index');
		$algolia_id = get_option('algolia_id');
		
		$curl = curl_init();
		
		$headers = array();
		$headers[] = 'X-Algolia-API-Key: '.$algolia_token;
		$headers[] = 'X-Algolia-Application-Id: '.$algolia_id;

		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);	

		curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_REFERER => esc_url(home_url()),
		CURLOPT_URL => 'https://'.$algolia_id.'-dsn.algolia.net/1/indexes/'.$algolia_index.'/'.$query_param,
		));
		$resp = curl_exec($curl);
		$resp = json_decode($resp, true);
		$resp = $resp['hits'];
		return $resp;
	}

	public static function algolia_one($string)
	{
		$new_query_var = $string;
		$query_param = '?query='.$new_query_var.'&hitsPerPage=1';
		$algolia_token = get_option('algolia_token');
		$algolia_index = get_option('algolia_index');
		$algolia_id = get_option('algolia_id');
		
		$curl = curl_init();
		$headers = array();
		$headers[] = 'X-Algolia-API-Key: '.$algolia_token;
		$headers[] = 'X-Algolia-Application-Id: '.$algolia_id;
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);	

		curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_REFERER => esc_url(home_url()),
		CURLOPT_URL => 'https://'.$algolia_id.'-dsn.algolia.net/1/indexes/'.$algolia_index.'/'.$query_param,
		));
		$resp = curl_exec($curl);
		$resp = json_decode($resp, true);
		$resp = $resp['hits'];
		return $resp;
	}	

	public static function is_commercial()
	{
		if(Charterflights_Meta_Box::jet_get_meta( 'jet_commercial' ) == 1)
		{
			return true;
		}
	}
	public static function ferry()
	{
		if(Charterflights_Meta_Box::jet_get_meta( 'jet_commercial' ) == 2)
		{
			return true;
		}
	}
	public static function ground()
	{
		if(Charterflights_Meta_Box::jet_get_meta( 'jet_commercial' ) == 3)
		{
			return true;
		}
	}

	public static function get_inline_js($file)
	{
		ob_start();
		require_once(dirname( __FILE__ ) . '/js/'.$file.'.js');
		$output = ob_get_contents();
		ob_end_clean();
		return $output;			
	}
	
	public static function get_inline_css($file)
	{
		ob_start();
		require_once(dirname( __FILE__ ) . '/css/'.$file.'.css');
		$output = ob_get_contents();
		ob_end_clean();
		return $output;			
	}
	
	public static function remove_body_class($classes)
	{
		if(get_query_var('fly') || get_query_var('instant_quote') || get_query_var('request_submitted'))
		{
			if(in_array('blog', $classes))
			{
				unset($classes[array_search('blog', $classes)]);
			}
		}
		
		return $classes;
	}
	
}