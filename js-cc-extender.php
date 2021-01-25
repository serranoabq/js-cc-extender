<?php
/**
 * Plugin Name: Church Content Extender
 * Description: Plugin to augment the Church Content plugin by adding additional features. Requires <strong>Church Content</strong> plugin.
 * Version: 0.361
 * Author: Justin Serrano
 * Text Domain: jsccx
 *
 * @package    	JSCCX
 * @copyright		Copyright (c) 2020, Justin Serrano
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

// No direct access
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main class
 *
 * @since 0.1
 */
class CCExtender {

	/**
	 * Plugin data from get_plugins()
	 *
	 * @since 0.1
	 * @var object
	 */
	public $plugin_data;

	/**
	 * Includes to load
	 *
	 * @since 0.1
	 * @var array
	 */
	public $includes;

	/**
	 * Constructor
	 *
	 * Add actions for methods that define constants and load includes.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function __construct() {

		// Church Content is REQUIRED
		if ( ! class_exists( 'Church_Theme_Content' ) ) { 
			add_action( 'admin_notices', array( $this, 'ctc_notice' ) );
			return;
		}

		// Set plugin data
		add_action( 'plugins_loaded', array( $this, 'set_plugin_data' ) );

		// Define constants
		add_action( 'plugins_loaded', array( $this, 'define_constants' ) );

		// Load language file
		//add_action( 'plugins_loaded', array( &$this, 'load_textdomain' ) );

		// Set includes
		add_action( 'plugins_loaded', array( $this, 'set_includes' ) );

		// Load includes
		add_action( 'plugins_loaded', array( $this, 'load_includes' ) );
		
		// Force CC support
		add_action( 'after_setup_theme', array( $this, 'add_theme_support' ), 12 );

	}

	/**
	 * Set plugin data
	 *
	 * This data is used by constants.
	 *
	 * @since 01
	 * @access public
	 */
	public function set_plugin_data() {

		// Load plugin.php if get_plugins() not available
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Get path to plugin's directory
		$plugin_dir = plugin_basename( dirname( __FILE__ ) );

		// Get plugin data
		$plugin_data = current( get_plugins( '/' . $plugin_dir ) );

		// Set plugin data
		$this->plugin_data = apply_filters( 'ccex_plugin_data', $plugin_data );

	}

	/**
	 * Define constants
	 *
	 * @since 0.1
	 * @access public
	 */
	public function define_constants() {
		// Plugin details
		define( 'CCX_VERSION', $this->plugin_data['Version'] ); 
		define( 'CCX_NAME', $this->plugin_data['Name'] ); 
		define( 'CCX_AUTHOR', strip_tags( $this->plugin_data['Author'] ) ); 
		define( 'CCX_INFO_URL', $this->plugin_data['PluginURI'] ); 
		define( 'CCX_FILE', __FILE__ ); 
		define( 'CCX_FILE_BASE', plugin_basename( CCX_FILE ) ); 
		define( 'CCX_DIR', dirname( CCX_FILE_BASE ) ); 
		define( 'CCX_PATH',	untrailingslashit( plugin_dir_path( CCX_FILE ) ) );	
		define( 'CCX_URL', untrailingslashit( plugin_dir_url( CCX_FILE ) ) );
		
		// Directories
		define( 'CCX_INC_DIR', 'includes' ); // includes directory
		define( 'CCX_ADMIN_DIR', CCX_INC_DIR . '/admin' ); 
		//define( 'CCX_CLASS_DIR', CCX_INC_DIR . '/classes' ); 
		//define( 'CCX_LIB_DIR', CCX_INC_DIR . '/libraries' ); 
		
		//define( 'CCX_CSS_DIR', 'css' ); // stylesheets directory
		//define( 'CCX_JS_DIR', 'js' );	// JavaScript directory
		//define( 'CCX_IMG_DIR', 'images' ); // images directory
		//define( 'CCX_LANG_DIR', 'languages' ); // languages directory
		
		if( ! defined( 'CCP_VERSION' ) )
			define( 'CCP_VERSION', 'CCX' );
		
	}

	/**
	 * Load language file
	 *
	 * @since 0.1
	 * @access public
	 */
	public function load_textdomain() {

		// Textdomain
		$domain = 'jsccx';

		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
		
		$languages_dir = CCX_DIR . '/' . trailingslashit( CCX_LANG_DIR ); 
		
		load_plugin_textdomain( $domain, false, $languages_dir );
		
	}

	/**
	 * Set includes
	 *
	 * @since 0.1
	 * @access public
	 */
	public function set_includes() {

		$this->includes = apply_filters( 'CCX_includes', array(

			// Frontend or admin
			'always' => array(

				// Functions
				CCX_INC_DIR . '/settings.php',
				CCX_INC_DIR . '/post_types.php',
				CCX_INC_DIR . '/post_images.php',
				CCX_INC_DIR . '/taxonomies.php',
				CCX_INC_DIR . '/helpers.php',
				CCX_INC_DIR . '/shortcodes.php',
				//CCX_INC_DIR . '/publics.php',
				//CCX_INC_DIR . '/support.php',

			),

			// Admin only
			'admin' => array(

				// Functions
				//CCX_ADMIN_DIR . '/admin-add-ons.php',
				
			),

			// Frontend only
			/*
			'frontend' => array (

			),
			*/

		) );

	}

	/**
	 * Load includes
	 *
 	 * Include files based on whether or not condition is met.
	 *
	 * @since 0.1
	 * @access public
	 */
	public function load_includes() {

		// Get includes
		$includes = $this->includes;

		// Loop conditions
		foreach ( $includes as $condition => $files ) {

			$do_includes = false;

			// Check condition
			switch( $condition ) {

				// Admin Only
				case 'admin':

					if ( is_admin() ) {
						$do_includes = true;
					}

					break;

				// Frontend Only
				case 'frontend':

					if ( ! is_admin() ) {
						$do_includes = true;
					}

					break;

				// Admin or Frontend (always)
				default:

					$do_includes = true;

					break;

			}

			// Loop files if condition met
			if ( $do_includes ) {

				foreach ( $files as $file ) {
					require_once trailingslashit( CCX_PATH ) . $file;
				}

			}

		}

	}

	/**
	 * Add CC support if it's not explicitly defined by the theme
	 *
	 * @since 0.3
	 * @access public
	 * @global $ccx_forced_cc Array of elements with forced support
	 * @global $ccx_theme_cc Array of elements supported by theme
	 */
	public function add_theme_support(){
		global $ccx_forced_cc; // feature forced by CCX
		global $ccx_theme_cc;  // feature defined by theme
		
		$ccx_forced_cc = array();
		$ccx_theme_cc = array();
		
		if( ! class_exists( 'Church_Theme_Content' ) ) return;
		
		if( ! current_theme_supports( 'responsive-embeds' ) )
			add_theme_support( 'responsive-embeds' );
		
		add_theme_support( 'church-theme-content' );
		$ccx_forced_cc = array( 'ctc' );
		
		// Available types and categories
		$avail_types = array(
			'ctc-sermons' => array( 
				'name' => 'sermon',
				'taxonomies' => array( 'ctc_sermon_series', 'ctc_sermon_book', 'ctc_sermon_speaker', 'ctc_sermon_tag', 'ctc_sermon_topic' )
			),
			'ctc-events' => array( 
				'name' => 'event',
				'taxonomies' => array( 'ctc_event_category' )
			),
			'ctc-locations' => array( 
				'name' => 'location',
				'taxonomies' => array()
			),
			'ctc-people' => array( 
				'name' => 'person',
				'taxonomies' => array( 'ctc_person_group' )
			),
		);
		
		// Get settings
		$ctc_settings = get_option( 'ctc_settings' );

		// Loop over every post to enable it
		$options = array();
		foreach( $avail_types as $post_type => $setting ){
			
			// If theme supports the type, add it to the theme_cc var
			$fields = array();
			$field_overrides = array();
			if( current_theme_supports( $post_type ) ) {
				
				$supports = get_theme_support( $post_type );
				if( isset( $supports[0]['fields'] ) ) { 
					$fields = $supports[0]['fields'];
				}
				
				if( isset( $supports[0]['field_overrides'] ) ) {
					$field_overrides = $supports[0]['field_overrides' ];
				}
				
				array_push( $ccx_theme_cc, $setting['name'] );
				remove_theme_support( $post_type );
				
				// force enable the setting
				$ctc_settings[ 'enable_' . $setting['name'] ] = 1;
				update_option( 'ctc_settings', $ctc_settings );
			} 
			
			$taxes = array();
			if( $ctc_settings[ 'enable_' . $setting['name'] ] ){
				
				// Post_type enabled in CCX settings 
				array_push( $ccx_forced_cc, $setting['name'] );
				
				// Enable categories
				foreach( $setting[ 'taxonomies'] as $tax ){
					if( $ctc_settings[ str_replace( 'ctc_', 'enable_', $tax ) ] ) {
						array_push( $taxes, $tax );
						array_push( $ccx_forced_cc, str_replace( 'ctc_', '', $tax ) );
					}
				}
				
				// Ensure other options defined by the theme are kept
				$options[ 'taxonomies' ] = $taxes;
				if( ! empty( $fields ) ) 
					$options[ 'fields' ] = $fields;
				if( ! empty( $field_overrides ) ) 
					$options[ 'field_overrides' ] = $field_overrides;
				
				// Add theme support
				add_theme_support( $post_type, $options );
			}
			
		}
		
	}
	
	/**
	 * Give notice of dependency on Church Content plugin
	 *
 	 * @since 0.1
	 * @access public
	 */
	public function ctc_notice(){
		
		// Show only if user has some control over plugins
		if ( ! current_user_can( 'activate_plugins' ) ) return;
		
		// Show only on relavent pages as not to overwhelm admin
		$screen = get_current_screen();
		if( $screen->base != 'plugins' ) return;
		
?>

		<div class="notice notice-warning is-dismissable">
			 <p><?php _e( 'Church Content Extender requires' , 'jsccx' ); ?> <a href="/wp-admin/plugin-install.php?tab=plugin-information&plugin=church-theme-content&TB_iframe=true&width=600&height=550" class="thickbox open-plugin-details-modal">Church Content Plugin</a></p>
		</div>
			
<?php		
	}
	
}

// Instantiate the main class
new CCExtender();

