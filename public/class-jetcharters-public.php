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
		add_shortcode("jc_calculator", array("Jetcharters_Public", "jet_calculator"));
		add_shortcode( 'mapbox_airports', array('Jetcharters_Public', 'mapbox_airports') );
		add_shortcode( 'destination_airports', array('Jetcharters_Public', 'destination_airports') );
		add_shortcode( 'jetlist', array('Jetcharters_Public', 'jetlist') );
		add_shortcode( 'destination', array('Jetcharters_Public', 'filter_destination_table') );
	}
	public static function deque_jetpack()
	{
		if(get_query_var('fly'))
		{	
			remove_action( 'wp_head', 'rel_canonical' );
			return false;
		}
	}
	public static function jetlist($attr, $content = "")
	{
		ob_start();
		require_once(dirname( __FILE__ ) . '/partials/jet-archive.php');
		$content = ob_get_contents();
		ob_end_clean();	
		return $content;
	}	
	public static function mapbox_airports($attr, $content = "")
	{
		if(!isset($_GET['fl_builder']))
		{	
	
			ob_start();
			
			if(!wp_is_mobile())
			{
				?>
				<div class="pure-g">
					<div class="mapbox_form pure-u-1 pure-u-sm-1-1 pure-u-md-2-5">
				<?php
			}
			
			require_once(dirname( __FILE__ ) . '/partials/price-calculator.php');
					
			if(!wp_is_mobile())
			{
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
			}
			
			$content = ob_get_contents();
			ob_end_clean();		
		}
		else
		{
			 $content = '<h2 class="text-center">'.__('Mapbox preview not available in editing mode.', 'jetcharters').'</h2>';
		}
		
		return $content;
	}
	public static function destination_airports($attr, $content = "")
	{	
		$output = null;

		$output = '<div id="stats-container"></div><input id="search-airport" type="text" /><div id="container-airport"></div><div id="pagination-container"></div>';		
		
		return $output;
	}		
	public static function destination_js()
	{
		global $post;
		if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'destination_airports') )
		{
			ob_start();
			require_once(plugin_dir_path( __FILE__ ).'partials/airports_list.php');
			$output = ob_get_contents();
			ob_end_clean();		
			echo $output;			
		}
	}	
	public static function json_src_url()
	{
		global $post;
		global $typenow;
		
		if('jet' == $typenow || 'destinations' == $typenow || (is_a( $post, 'WP_Post' ) && (has_shortcode( $post->post_content, 'mapbox_airports') || has_shortcode( $post->post_content, 'destination_airports') || has_shortcode( $post->post_content, 'jc_calculator') || has_shortcode( $post->post_content, 'contact-form-7')) || Jetcharters_Public::shortcode_widget('mapbox_airports') || Jetcharters_Public::shortcode_widget('destination_airports') || Jetcharters_Public::shortcode_widget('jc_calculator') || Jetcharters_Public::shortcode_widget('contact-form-7') ) )
		{
			$output = 'function jsonsrc() { return "'.esc_url(plugin_dir_url( dirname(__FILE__) )).'";}';
			
			if(get_option('algolia_token'))
			{
				$algolia_token = get_option('algolia_token');
				$algolia_token = $algolia_token['text_field_jetcharters_8'];
				$output .= 'function get_algolia_token() { return "'.esc_html($algolia_token).'";}';
			}
			if(get_option('algolia_index'))
			{
				$algolia_index = get_option('algolia_index');
				$algolia_index = $algolia_index['text_field_jetcharters_9'];
				$output .= 'function get_algolia_index() { return "'.esc_html($algolia_index).'";}';
			}
			if(get_option('algolia_id'))
			{
				$algolia_id = get_option('algolia_id');
				$algolia_id = $algolia_id['text_field_jetcharters_10'];
				$output .= 'function get_algolia_id() { return "'.esc_html($algolia_id).'";}';
			}

			return $output;
		}
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
		elseif(Jetcharters_Public::valid_jet_quote())
		{
			return esc_html(__("Request Submitted", "jetcharters").' | '.esc_html(get_bloginfo('name')));
		}		
		elseif(Jetcharters_Public::valid_jet_search())
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
				$jet_type = Jetcharters_Public::jet_type($jet_type);
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
					$jet_type = Jetcharters_Public::jet_type(Charterflights_Meta_Box::jet_get_meta( 'jet_type' ));
					$title = '<span class="linkcolor">'.esc_html($jet_type).'</span> '.$title;
				}				
			}
			elseif(in_the_loop() && Jetcharters_Public::valid_jet_search())
			{
				$title = esc_html(__("Find an Aircraft", "jetcharters"));
			}
			elseif(in_the_loop() && Jetcharters_Public::valid_jet_quote())
			{
				$title = esc_html(__("Request Submitted", "jetcharters"));
			}			
			elseif(in_the_loop() && get_query_var( 'fly' ))
			{
				global $airport_array;
			
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
		return $title;
	}
	public static function modify_content($content)
	{	if(in_the_loop() && get_query_var( 'fly' ))
		{
			global $airport_array;
			$json = $airport_array;
			$output = null;

			if(count($json) > 0)
			{
								
				$output .= Jetcharters_Public::get_destination_table(esc_html($json['iata']));			
				$output .= wpautop(Jetcharters_Public::get_destination_content(esc_html($json['iata'])));			
				
				ob_start();
				require_once(plugin_dir_path( __FILE__ ).'partials/jetcharters-public-display.php');
				$output .= ob_get_contents();
				ob_end_clean();
			}			
			
			return $output;
		}
		elseif(Jetcharters_Public::valid_jet_quote())
		{
			if(wp_verify_nonce(get_query_var('request_submitted'), 'request_submitted'))
			{
				if(Jetcharters_Public::validate_recaptcha())
				{
					$data = $_POST;
					$data['lang'] = get_locale();
					
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
					
					Jetcharters_Public::webhook(json_encode($data));
					
					return '<p class="tp_alert">'.esc_html(__('Request received. Our sales team will be in touch with you soon.', 'jetcharters')).'</p>';
				}
				else
				{
					return '<p class="tp_alert">'.esc_html(__('Invalid Recaptcha', 'jetcharters')).'</p>';
				}
			}
			else
			{
				return '<p class="tp_alert">'.esc_html(__('Invalid Request', 'jetcharters')).'</p>';	
			}
		}		
		elseif(Jetcharters_Public::valid_jet_search())
		{
			if(wp_verify_nonce(get_query_var('instant_quote'), 'instant_quote'))
			{
				ob_start();
				require_once(plugin_dir_path( __FILE__ ).'partials/jet_search.php');
				$output = ob_get_contents();
				ob_end_clean();
				return $output;				
			}
			else
			{
				return '<p class="tp_alert">'.esc_html(__('Invalid Request', 'jetcharters')).'</p>';
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
	public static function valid_jet_quote()
	{
		if(get_query_var('request_submitted') && isset($_POST['lead_name']) && isset($_POST['lead_lastname']) && isset($_POST['lead_email']) && isset($_POST['lead_phone']) && isset($_POST['lead_country']) && isset($_POST['g-recaptcha-response']) && isset($_POST['jet_origin'])  && isset($_POST['jet_destination'])  && isset($_POST['jet_departure_date'])  && isset($_POST['jet_departure_hour'])  && isset($_POST['departure_itinerary']) && isset($_POST['jet_return_date']) && isset($_POST['jet_return_hour']) && isset($_POST['return_itinerary']))
		{
			return true;
		}
		else
		{
			return false;
		}		
	}
	public static function valid_jet_search()
	{
		if(get_query_var('instant_quote') && isset($_GET['jet_origin']) && isset($_GET['jet_destination']) && isset($_GET['jet_pax']) && isset($_GET['jet_flight']) && isset($_GET['jet_departure_date']) && isset($_GET['jet_departure_hour']) && isset($_GET['jet_return_date']) && isset($_GET['jet_return_hour']))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	public static function mapbox_vars()
	{
		$mapbox_token = get_option('mapbox_token');
		$mapbox_token = esc_html($mapbox_token['text_field_jetcharters_0']);
		//map name	
		$mapbox_map_id = get_option('mapbox_map_id');
		$mapbox_map_id = esc_html($mapbox_map_id['text_field_jetcharters_1']);
		//base zoom
		$mapbox_map_zoom = get_option('mapbox_map_zoom');
		$mapbox_map_zoom = esc_html($mapbox_map_zoom['text_field_jetcharters_4']);	
		//base lat
		$mapbox_base_lat = get_option('mapbox_base_lat');
		$mapbox_base_lat = esc_html($mapbox_base_lat['text_field_jetcharters_5']);
		//base lon
		$mapbox_base_lon = get_option('mapbox_base_lon');
		$mapbox_base_lon = esc_html($mapbox_base_lon['text_field_jetcharters_6']);

		$mapbox_vars = array();
		$mapbox_vars['mapbox_token'] = $mapbox_token;
		$mapbox_vars['mapbox_map_id'] = $mapbox_map_id;
		$mapbox_vars['mapbox_map_zoom'] = $mapbox_map_zoom;
		$mapbox_vars['mapbox_base_lat'] = $mapbox_base_lat;
		$mapbox_vars['mapbox_base_lon'] = $mapbox_base_lon;
		$mapbox_vars['home_url'] = home_lang();
		return 'function mapbox_vars(){return '.json_encode($mapbox_vars).';}';
	}
	public static function meta_tags()
	{	if(get_query_var( 'fly' ))
		{
			global $airport_array;
			
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
			$GLOBALS['airport_array'] = json_decode(Jetcharters_Public::return_json(), true); 
						
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
		elseif( Jetcharters_Public::valid_jet_search() || Jetcharters_Public::valid_jet_quote())
		{
			if($query->is_main_query())
			{
				$query->set('post_type', 'page');
				$query->set( 'posts_per_page', 1 );				
			}
		}
	}
	public static function airport_img_url()
	{
		global  $airport_array;
		return home_url('cacheimg/'.Jetcharters_Public::cleanURL($airport_array['airport']).'.jpg');
	}
	public static function redirect_cacheimg()
	{
		if(get_query_var( 'cacheimg' ) && !in_the_loop())
		{
			//map token	
			$mapbox_token = get_option('mapbox_token');
			$mapbox_token = esc_html($mapbox_token['text_field_jetcharters_0']);

			//json vars
			$json = json_decode(Jetcharters_Public::return_json(), true);
			$_geoloc = $json['_geoloc'];

			//map position

			$mapbox_zoom = 8;
			$mapbox_marker = 'pin-l-airport+dd3333('.$_geoloc['lng'].','.$_geoloc['lat'].')';
			$image_resolution = 'png256';
			$mapbox_width = 800;
			$mapbox_height = 450;
			$mapbox_mobile = null;

			if(wp_is_mobile())
			{
				$image_resolution = 'jpg';
				$mapbox_width = 600;
				$mapbox_height = 300;
				$mapbox_mobile = '&is_mobile=true';
			}

			//map id
			$mapbox_map_id = get_option('mapbox_map_id');
			$mapbox_map_id = esc_html($mapbox_map_id['text_field_jetcharters_1']);
			$static_map = 'https://api.mapbox.com/v4/'.$mapbox_map_id.'/'.$mapbox_marker.'/'.$_geoloc['lng'].','.$_geoloc['lat'].','.$mapbox_zoom.'/'.$mapbox_width.'x'.$mapbox_height.'.'.$image_resolution.'?access_token='.$mapbox_token.$mapbox_mobile;
			wp_redirect(esc_url($static_map));
			exit;
		}
	}	
	public static function unset_template($template)
	{
		if(isset($_GET['sitemap']))
		{
			if($_GET['sitemap'] == 'airports')
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
				$browse_json = Jetcharters_Public::return_json();
				$browse_json = $browse_json['hits'];
				
				for($x = 0; $x < count($browse_json); $x++)
				{
					$url = '<url>';
					$url .= '<loc>'.esc_url(home_url().'/fly/'.Jetcharters_Public::cleanURL($browse_json[$x]['airport'])).'/</loc>';
					$url .= '<image:image>';
					$url .= '<image:loc>'.esc_url(home_url().'/cacheimg/'.Jetcharters_Public::cleanURL($browse_json[$x]['airport'])).'.jpg</image:loc>';
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
						$pll_url .= '<loc>'.esc_url(home_url().'/'.$language_list[0].'/fly/'.Jetcharters_Public::cleanURL($browse_json[$y]['airport'])).'/</loc>';
						$pll_url .= '<image:image>';
						$pll_url .= '<image:loc>'.esc_url(home_url().'/cacheimg/'.Jetcharters_Public::cleanURL($browse_json[$y]['airport'])).'.jpg</image:loc>';
						$pll_url .= '</image:image>';
						$pll_url .= '<mobile:mobile/>';
						$pll_url .= '<changefreq>weekly</changefreq>';
						$pll_url .= '</url>';
						$urllist .= $pll_url;					
					}					
				}
				
					$output =  '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
					$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
					xmlns:mobile="http://www.google.com/schemas/sitemap-mobile/1.0"
					xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
					$output .= $urllist;
					$output .= '</urlset>';

					header('Content-type: application/xml');
					exit(ent2ncr($output));
			}
			else
			{
				return $template;
			}
			exit();
		}
		else
		{
			return $template;
		}
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
		if(Jetcharters_Public::valid_jet_quote())
		{
			$new_template = locate_template( array( 'page.php' ) );
			return $new_template;			
		}
		if(get_query_var( 'fly' ) || Jetcharters_Public::valid_jet_search() || is_singular('jet'))
		{
			$new_template = locate_template( array( 'page.php' ) );
			return $new_template;			
		}
		return $template;
	}
	
	public static function return_json() {
		
		$algolia_token = get_option('algolia_token');
		$algolia_token = $algolia_token['text_field_jetcharters_8'];
		$algolia_index = get_option('algolia_index');
		$algolia_index = $algolia_index['text_field_jetcharters_9'];
		$algolia_id = get_option('algolia_id');
		$algolia_id = $algolia_id['text_field_jetcharters_10'];
		
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
			
			for($x = 0; $x < count($json); $x++)
			{
				if($new_query_var === Jetcharters_Public::cleanURL($json[$x]["airport"]))
				{
					return json_encode($json[$x]);
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

		global $post;
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/jetcharters-public.css', array(), time(), 'all' );
		
		Jetcharters_Public::datepickerCSS();
		
		if( is_a( $post, 'WP_Post' ) && (Jetcharters_Public::shortcode_widget('destination_airports') || has_shortcode( $post->post_content, 'destination_airports'))  )
		{
			wp_enqueue_style( 'instantsearchCSS', plugin_dir_url( __FILE__ ).'css/instantsearch.min.css', array(), $this->version, 'all' );			
		}
		
		
		if(is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'mapbox_airports') && !wp_is_mobile() && !isset($_GET['fl_builder']))
			{
				wp_enqueue_style('mapbox', 'https://api.mapbox.com/mapbox.js/v3.1.0/mapbox.css', array(), $this->version, 'all' );
				
				wp_enqueue_style('markercluster', 'https://api.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v1.0.0/MarkerCluster.css', array(), $this->version, 'all' );
				
				wp_enqueue_style('markercluster_def', 'https://api.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v1.0.0/MarkerCluster.Default.css', array(), $this->version, 'all' );
			}
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
		wp_register_script('algolia', '//cdn.jsdelivr.net/algoliasearch/3/algoliasearch.min.js', array( 'jquery' ), $this->version, true );
		wp_register_script('algolia_autocomplete', 'https://cdn.jsdelivr.net/autocomplete.js/0/autocomplete.jquery.min.js', array( 'jquery' ), $this->version, true );		
		
		
		if(is_a( $post, 'WP_Post' ) && (has_shortcode( $post->post_content, 'mapbox_airports') || has_shortcode( $post->post_content, 'destination_airports') || has_shortcode( $post->post_content, 'jc_calculator') || has_shortcode( $post->post_content, 'contact-form-7') || Jetcharters_Public::shortcode_widget('jc_calculator') || Jetcharters_Public::shortcode_widget('mapbox_airports') || Jetcharters_Public::shortcode_widget('contact-form-7') || Jetcharters_Public::shortcode_widget('destination_airports')) )
		{
			wp_enqueue_script('algolia');
			wp_enqueue_script('algolia_autocomplete');
			
			$public_depen = array('jquery', 'algolia');

			if(has_shortcode( $post->post_content, 'mapbox_airports') && !wp_is_mobile() && !isset($_GET['fl_builder']))
			{
				array_push($public_depen, 'mapbox', 'markercluster');
			}
			
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/jetcharters-public.js', $public_depen, time(), true );	
		
			wp_add_inline_script('jetcharters', Jetcharters_Public::json_src_url());
			
			if(is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'mapbox_airports') && !wp_is_mobile() && !isset($_GET['fl_builder']))
			{							
					wp_enqueue_script( 'mapbox', 'https://api.mapbox.com/mapbox.js/v3.1.0/mapbox.js', array( 'jquery', 'algolia' ), $this->version, true );
					
					wp_enqueue_script( 'markercluster', 'https://api.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v1.0.0/leaflet.markercluster.js', array( 'jquery' ), $this->version, true );
					
					wp_enqueue_script( 'arc', plugin_dir_url( __FILE__ ).'js/jetcharters-arc.js', array( 'jquery' ), $this->version, true );
					
					wp_add_inline_script('mapbox', Jetcharters_Public::mapbox_vars());					
					
					wp_enqueue_script( 'mapbox_js', plugin_dir_url( __FILE__ ).'js/jetcharters-mapbox.js', array( 'jquery', 'mapbox', 'markercluster', 'algolia', 'algolia_autocomplete', 'jetcharters' ), $this->version, true );
			}		
		}

		
		if( is_a( $post, 'WP_Post' ) && (Jetcharters_Public::shortcode_widget('destination_airports') || has_shortcode( $post->post_content, 'destination_airports'))  )
		{
			wp_enqueue_script('algolia');
			wp_enqueue_script('algolia_autocomplete');
			wp_enqueue_script('instantsearchJS', '//cdn.jsdelivr.net/instantsearch.js/1/instantsearch.min.js', array( 'jquery' ), $this->version, true );
			wp_enqueue_script('hogan', '//cdn.jsdelivr.net/hogan.js/3.0.2/hogan.min.js', array( 'jquery' ), $this->version, true );		
		}		

		if(is_a( $post, 'WP_Post' ) && ( has_shortcode( $post->post_content, 'jc_calculator') || has_shortcode( $post->post_content, 'contact-form-7') || Jetcharters_Public::shortcode_widget('jc_calculator') || Jetcharters_Public::shortcode_widget('contact-form-7')) )
		{
			wp_enqueue_script('algolia');
			wp_enqueue_script('algolia_autocomplete');			
		}
		
		if(Jetcharters_Public::valid_jet_search())
		{
			if(wp_verify_nonce(get_query_var('instant_quote'), 'instant_quote'))
			{
				wp_dequeue_script('google-recaptcha');
				wp_enqueue_script('jetcharters-recaptcha', 'https://www.google.com/recaptcha/api.js', array('jquery', 'jetcharters'), $this->version, true );
				Jetcharters_Public::datepickerJS();
			}
		}		
		
	}
	
	public static function datepickerCSS()
	{
		wp_enqueue_style( 'picker-css', plugin_dir_url( __FILE__ ) . 'css/picker/default.css', array(), '3.5.6', 'all' );
		wp_enqueue_style( 'picker-date-css', plugin_dir_url( __FILE__ ) . 'css/picker/default.date.css', array('picker-css'), '3.5.6', 'all' );
		wp_enqueue_style( 'picker-time-css', plugin_dir_url( __FILE__ ) . 'css/picker/default.time.css', array('picker-css'), '3.5.6', 'all' );		
	}
	
	public static function datepickerJS()
	{
		//pikadate
		wp_enqueue_script( 'picker-js', plugin_dir_url( __FILE__ ) . 'js/picker/picker.js', array('jquery'), '3.5.6', true);
		wp_enqueue_script( 'picker-date-js', plugin_dir_url( __FILE__ ) . 'js/picker/picker.date.js', array('jquery', 'picker-js'), '3.5.6', true);
		wp_enqueue_script( 'picker-time-js', plugin_dir_url( __FILE__ ) . 'js/picker/picker.time.js',array('jquery', 'picker-js'), '3.5.6', true);	
		wp_enqueue_script( 'picker-legacy', plugin_dir_url( __FILE__ ) . 'js/picker/legacy.js', array('jquery', 'picker-js'), '3.5.6', true);

		$picker_translation = 'js/picker/translations/'.substr(get_locale(), 0, -3).'.js';

		if(file_exists(get_template_directory().$picker_translation))
		{
			wp_enqueue_script( 'picker-time-translation', plugin_dir_url( __FILE__ ). $picker_translation, array('jquery', 'picker-js'), '3.5.6', true);
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
		return Jetcharters_Public::lz($hours).":".Jetcharters_Public::lz($minutes);
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
		
		//destination
		$args21 = array('post_type' => 'destinations','posts_per_page' => 1, 'post_parent' => 0);
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
				
				if( current_user_can('editor') || current_user_can('administrator') )
				{
					ob_start();
					edit_post_link('<i class="fas fa-pencil-alt" ></i> '.__('Edit Destination'), '<p class="text-right">', '</p>', '', 'pure-button' );
					$new_content .= ob_get_contents();
					ob_end_clean();					
				}	
				
				$new_content .= '<div class="dynamic-related">';
				$new_content .= get_the_content();
				$new_content .= '</div>';
			}
			wp_reset_postdata();
		}
		
		$output .= $new_content;
		return $output;
		
	}
	
	public static function shortcode_widget($shortcode)
	{
		global $wp_registered_sidebars;
		$count = 0;
		
		
		//die(var_dump($wp_registered_sidebars));
		
		
		foreach($wp_registered_sidebars as $k => $v)
		{
			$sidebar = $v;
			$sidebar_id = $v['id'];
			
			ob_start();
			dynamic_sidebar($sidebar_id);
			$sidebar_content = ob_get_contents();
			ob_end_clean();
			
			return true;
			
			if(has_shortcode($sidebar_content, $shortcode))
			{
				$count++;
			}	
			
		}
		
		if($count > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public  static function validate_recaptcha()
	{
		if(isset($_POST['g-recaptcha-response']) && get_option('captcha_secret_key'))
		{
			$data = array();
			$data['secret'] = get_option('captcha_secret_key');
			$data['remoteip'] = $_SERVER['REMOTE_ADDR'];
			$data['response'] = sanitize_text_field($_POST['g-recaptcha-response']);
			$url = 'https://www.google.com/recaptcha/api/siteverify';
			$verify = curl_init();
			curl_setopt($verify, CURLOPT_URL, $url);
			curl_setopt($verify, CURLOPT_POST, true);
			curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
			curl_setopt($verify, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
			$verify_response = json_decode(curl_exec($verify), true);						
			if($verify_response['success'] == true)
			{
				return true;
			}
			else
			{
				return false;
			}			
		}
		else
		{
			return false;
		}
	}
	
	public static function webhook($data)
	{
		
		if(get_option( 'jet_webhook' ))
		{
			$webhook = get_option( 'jet_webhook' );
			$webhook = $webhook['text_field_jetcharters_11'];
			
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
				curl_close($ch);
				
				if (substr($result, 0, 5) != '[200]')
				{
					$admin_email = get_option( 'admin_email' );
					$time = current_time('timestamp', $gmt = 0 );
					$time = date_i18n(get_option('date_format'), $time);
					wp_mail( $admin_email, 'Webhook Error - '.$time, $result);
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
				$content = Jetcharters_Public::get_destination_table($attr['iata']);
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
		$args22 = array('post_type' => 'jet','posts_per_page' => 200, 'post_parent' => 0, 'meta_key' => 'jet_base_iata', 'orderby' => 'meta_value');
		
		if(is_singular('jet'))
		{
			$args22['p'] = get_the_ID();
		}
		
		$args22['meta_query'] = array();
		
		$meta_args = array(
			'key' => 'jet_base_iata',
			'value' => esc_html($iata),
			'compare' => '!='
		);
		
		$args22['meta_key'] = array();
		$wp_query22 = new WP_Query( $args22 );
		
		//aircraft
		if ( $wp_query22->have_posts() )
		{
			
			$algolia_full = Jetcharters_Public::algolia_full();
			
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
					
					if(($base_iata == $table_price[$x][0] || $base_iata == $table_price[$x][1]) &&($iata == $table_price[$x][0] || $iata == $table_price[$x][1]) && ($table_price[$x][0] != '' || $table_price[$x][1] != ''))
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

						$seats = $table_price[$x][6];
						$weight_pounds = $table_price[$x][7];
						$weight_kg = intval(intval($weight_pounds)*0.453592);
						$weight_allowed = esc_html($weight_pounds.' '.__('pounds', 'jetcharters').' | '.$weight_kg.__('kg', 'jetcharters'));
						$jet_type = Jetcharters_Public::jet_type(Charterflights_Meta_Box::jet_get_meta( 'jet_type' ));
						
						$route = __('Private Charter Flight', 'jetcharters').' '.$jet_type.' '.$post->post_title.' '.__('from', 'jetcharters').' '.$origin_airport.', '.$origin_city.' ('.$origin_iata.') '.__('to', 'jetcharters').' '.$destination_airport.', '.$destination_city.' ('.$iata.')';
						
						$table_row .= '<tr data-jet-type="'.esc_html(Charterflights_Meta_Box::jet_get_meta( 'jet_type' )).'" data-iata="'.esc_html($origin_iata).'" title="'.esc_html($route).'">';
						
						if(!is_singular('jet'))
						{
							if(Jetcharters_Public::is_commercial())
							{
								$table_row .= '<td><strong>'.esc_html(__('Commercial Flight', 'jetcharters')).'</strong></td>';
							}
							else
							{
								$table_row .= '<td><a class="strong" href="'.esc_url($aircraft_url).'/">'.esc_html($post->post_title).'</a> - <small>'.esc_html($jet_type).'</small><br/><i class="fas fa-male" ></i> '.esc_html($seats).' <small>('.$weight_allowed.')</small></td>';
							}
						}
						
						$table_row .= '<td><small class="text-muted">('.esc_html($origin_iata).')</small> <strong>'.esc_html($origin_city.', '.$origin_country_code).'</strong><br/><a href="'.esc_url(home_lang()).'fly/'.Jetcharters_Public::cleanURL($origin_airport).'/">'.esc_html($origin_airport).'</a></td>';
						
						if(!wp_is_mobile())
						{
							$table_row .= '<td><i class="fas fa-clock" ></i> '.esc_html(Jetcharters_Public::convertTime($table_price[$x][2])).'</td>';
						}
						

						$table_row .= '<td><strong>'.esc_html('$'.number_format($table_price[$x][3], 2, '.', ',')).'</strong><br/><span class="small text-muted">';

						if(Jetcharters_Public::is_commercial())
						{
							$table_row .= esc_html(__('Per Person', 'jetcharters'));
						}
						else
						{
							$table_row .= esc_html(__('Charter Flight', 'jetcharters'));
						}
						
						
						$table_row .= '</span></td></tr>';
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
			$table = null;
			
			if(is_singular('jet'))
			{
				$table .= '<h4>'.esc_html(__('Charter Flights', 'jetcharters').' '.Charterflights_Meta_Box::jet_get_meta( 'jet_base_name' ).' ('.Charterflights_Meta_Box::jet_get_meta( 'jet_base_iata' )).') '.Charterflights_Meta_Box::jet_get_meta( 'jet_base_city' ).'</h4>';
			}
			
			$table .= '<table id="dy_table" class="text-center small pure-table pure-table-bordered margin-bottom"><thead><tr>';
			
			
			$origin_label = __('Destination', 'jetcharters');
			
			if(!is_singular('jet'))
			{
				$origin_label = __('Origin', 'jetcharters');
				$table .= '<th>'.esc_html(__('Flights', 'jetcharters')).'</th>';
			}
			
			$table .= '<th>'.esc_html($origin_label).'</th>';	
			$table .= '<th>'.esc_html(__('Duration', 'jetcharters')).'</th>';
			$table .= '<th>'.esc_html(__('One Way', 'jetcharters')).'</th>';
			$table .= '</tr></thead><tbody>';
			$table .= $table_row;
			$table .= '</tbody></table>';
			$output .=  $table;
			return $output;
		}		
	}
	
	public static function algolia_full()
	{
		$query_param = 'browse?cursor=';
		$algolia_token = get_option('algolia_token');
		$algolia_token = $algolia_token['text_field_jetcharters_8'];
		$algolia_index = get_option('algolia_index');
		$algolia_index = $algolia_index['text_field_jetcharters_9'];
		$algolia_id = get_option('algolia_id');
		$algolia_id = $algolia_id['text_field_jetcharters_10'];
		
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
		$algolia_token = $algolia_token['text_field_jetcharters_8'];
		$algolia_index = get_option('algolia_index');
		$algolia_index = $algolia_index['text_field_jetcharters_9'];
		$algolia_id = get_option('algolia_id');
		$algolia_id = $algolia_id['text_field_jetcharters_10'];
		
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
		if(!empty(Charterflights_Meta_Box::jet_get_meta( 'jet_commercial' )))
		{
			return true;
		}
	}
	
	
}
