<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://jaimelias.com
 * @since      1.0.0
 *
 * @package    Jetcharters
 * @subpackage Jetcharters/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Jetcharters
 * @subpackage Jetcharters/includes
 * @author     Jaimelías <jaimelias@about.me>
 */
class Jetcharters {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Jetcharters_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'jetcharters';
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Jetcharters_Loader. Orchestrates the hooks of the plugin.
	 * - Jetcharters_i18n. Defines internationalization functionality.
	 * - Jetcharters_Admin. Defines all hooks for the admin area.
	 * - Jetcharters_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-jetcharters-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-jetcharters-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-jetcharters-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-jetcharters-public.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/validators.php';
		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-jetcharters-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-jetcharters-post-type.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-jetcharters-meta-box.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-jetcharters-sidebar.php';
		//fix yoastseo
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-jetcharters-yoastseo.php';
		
		$this->loader = new Jetcharters_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Jetcharters_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Jetcharters_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Jetcharters_Admin( $this->get_plugin_name(), $this->get_version() );
		$plugin_public = new Jetcharters_Public( $this->get_plugin_name(), $this->get_version() );
		
		$plugin_settings = new Jetcharter_Settings();
		$plugin_post_type = new Charterflights_Post_Type();
		$plugin_meta_box = new Charterflights_Meta_Box();
		
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'init', $plugin_post_type, 'fly_post_type', 0);
		$this->loader->add_action( 'init', $plugin_post_type, 'jet_post_type', 0 );			
		$this->loader->add_action( 'admin_menu', $plugin_settings, 'add_settings_page' );
		$this->loader->add_action( 'admin_init', $plugin_settings, 'settings_init' );	
		$this->loader->add_action( 'save_post', $plugin_meta_box, 'jet_save' );
		$this->loader->add_action( 'add_meta_boxes',$plugin_meta_box, 'jet_add_meta_box' );
		$this->loader->add_action( 'add_meta_boxes',$plugin_meta_box, 'destinations_add_meta_box' );

		//rewrite url
		$this->loader->add_action('init', $plugin_admin, 'custom_rewrite_basic');
		$this->loader->add_action('init', $plugin_admin, 'custom_rewrite_tag', 10, 0);	

		//stop bad queries with first hook
		
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_pll_strings' );		
		
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		global $wp_version;

		$plugin_public = new Jetcharters_Public( $this->get_plugin_name(), $this->get_version() );
		$plugin_yoast =  new Jetcharters_YoastSEO_Fix();

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		
		//add later to admin
		$this->loader->add_filter("wp_head", $plugin_public, 'meta_tags');
		
		$this->loader->add_action('pre_get_posts', $plugin_public, 'main_wp_query', 100);		
		
		if($wp_version >= 4.4)
		{
			//new filter outputs the title before the query
			$this->loader->add_filter( 'pre_get_document_title', $plugin_public, 'modify_wp_title', 100);
		}

		$this->loader->add_filter( 'wp_title', $plugin_public, 'modify_wp_title', 100);
		$this->loader->add_filter("the_content", $plugin_public, 'modify_content');
		$this->loader->add_filter("the_title", $plugin_public, 'modify_title');
		
		$this->loader->add_filter( 'jetpack_enable_open_graph', $plugin_public, 'deque_jetpack' );
		
		//include template
		$this->loader->add_filter( 'template_include', $plugin_public, 'package_template', 10 );
		
		//unset template
		$this->loader->add_filter('template_include', $plugin_public, 'unset_template', 11);
		$this->loader->add_filter('template_redirect', $plugin_public, 'redirect_cacheimg', 11);

		//yoast fix
		$this->loader->add_action('init', $plugin_yoast, 'yoast_fixes');

	
	}


	
	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Jetcharters_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
