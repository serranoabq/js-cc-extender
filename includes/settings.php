<?php
/**
 * CCX Settings
 *
 * @package    	JSCCX
 * @author			Justin Serrano
 * @copyright		Copyright (c) 2020, Justin Serrano
 * @license    	http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @since      	0.1
 */

// No direct access
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Setup CCX settings page within the CT settings
 *
 * @since 0.1
 * @return array CCX-specific settings
 */
function ccx_settings_setup(){
	
	$ccx_settings = array(
		'title' => __( 'CCX', 'jsccx' ),
		'desc' => __( "Church Content Extender (CCX) provides additional options to the <b>Church Content</b> plugin", 'jsccx' ),
		'fields' => array(
		
			// Post types		
			'post_type_note' => ccx_settings_content( __( 'Enable or disable the Church Content posts for the current theme. Some may be enabled by the theme already,', 'jsccx' ), __( 'Enable Church Content post types' , 'jsccx' ) ),
			'enable_sermon' => ccx_settings_enable_cpt( 'sermon' ,'' ),
			'enable_event' => ccx_settings_enable_cpt( 'event', '' ),
			'enable_location' => ccx_settings_enable_cpt( 'location', '' ),
			'enable_person' => ccx_settings_enable_cpt( 'person', '' ),
			
			// Categories
			'category_note' => ccx_settings_content( __( 'Enable or disable categories for Church Content posts.,', 'jsccx' ), __( 'Enable Church Content categories' , 'jsccx' ) ),
			'enable_sermon_speaker' => ccx_settings_enable_category( 'sermon_speaker', ' ' ),
			'enable_sermon_series' => ccx_settings_enable_category( 'sermon_series', ' ' ),
			'enable_sermon_book' => ccx_settings_enable_category( 'sermon_book', ' ' ),
			'enable_sermon_topic' => ccx_settings_enable_category( 'sermon_topic', ' ' ),
			'enable_sermon_tag' => ccx_settings_enable_category( 'sermon_tag', ' ' ),
			'enable_event_category' => ccx_settings_enable_category( 'event_category', ' ' ),
			'enable_person_group' => ccx_settings_enable_category( 'person_group', ' ' ),
			
			//Images			
			'default_sermon_image' => ccx_settings_default_image( 'sermon' ),
			'default_event_image' => ccx_settings_default_image( 'event' ),
			'default_location_image' => ccx_settings_default_image( 'location' ),
			'default_person_image' => ccx_settings_default_image( 'person' ),
			
		)
	);
	
	$ccx_settings = ccx_migration_setting( $ccx_settings );
	
	return $ccx_settings;
}

/**
 * Filter the original CC settings
 *
 * @since 0.1
 * @param array $config Settings array
 * @return array Filtered settings array
 */
function ctc_settings_filter( $config ){
	
	if( 'CCX' == CCP_VERSION ) {
		// Eliminate some of CC's settings if we want to replace with custom versions
		unset( $config['sections']['licenses'] );
		unset( $config['sections']['podcast'] );
				
		// Clear pro flags
		foreach( $config['sections'] as $section_id => $section ){
			if( isset( $section['fields'] ) ){
				foreach ( $section['fields'] as $field_id => $field ) {
					if( ! empty( $field['pro'] ) ) {
						$config['sections'][ $section_id ]['fields'][ $field_id ]['pro'] = false; 
					}
				}
			}
		}
	}
	
	$config['sections']['ccx'] = ccx_settings_setup();
	return $config;
}
add_filter( 'ctc_settings_config', 'ctc_settings_filter', 2 );

/**********************************
 * SETTINGS SHORTCUTS
 **********************************/
 
/**
 * Shortcut to setting to add a default image for CC post types
 *
 * @since 0.1
 * @return array Setting definition  
 */
function ccx_settings_default_image( $post_type ){
	global $ccx_forced_cc;
	
	$avail_types = array( 'sermon', 'event', 'location', 'person' );
	
	// Get appropriate wording
	$ctc_settings = get_option( 'ctc_settings' );
	$sermon_word_singular = strtolower( $ctc_settings[ 'sermon_word_singular' ] );
	
	if( ! in_array( $post_type, $avail_types ) ) return;
	
	$is_enabled = in_array( $post_type, $ccx_forced_cc );
	$attr = array();
	if( ! $is_enabled ){
		$attr = array( 'readonly' => 'readonly' );
	}
	
	$post_type = 'sermon' == $post_type ? $sermon_word_singular : $post_type;
	
	$default_image_setting = array(
		'name' 							=> sprintf( __( 'Default %s image','jsccx' ), $post_type ),
		'after_name'  			=> '',
		'desc'       			 	=> ' ',
		'type'        			=> 'upload', 
		'checkbox_label'    => '', 
		'inline'            => false, 
		'options'           => array(),
		'upload_button'     => __( 'Choose image', 'jsccx' ),
		'upload_title'      => __( 'Default image', 'jsccx'), 
		'upload_type'       => 'image', 
		'upload_show_image' => 200, 
		'default'           => '', 
		'no_empty'          => false, 
		'allow_html'        => false, 
		'attributes'        => $attr, 
		'class'             => '', 
		'content'           => '',
		'custom_sanitize'   => '', 
		'custom_content'    => '', 
		'pro'               => false, 
		'unsupported'       => false, 
	); 
	
	return $default_image_setting;
}

/**
 * Shortcut for setting to enable of the CC custom post types and associated options 
 *
 * @since 0.1
 * @global $ccx_theme_cc
 * @return array Setting definition
 */
function ccx_settings_enable_cpt( $post_type, $name_field = null ){
	global $ccx_theme_cc;
	
	// Get appropriate wording
	$ctc_settings = get_option( 'ctc_settings' );
	$sermon_word_singular = $ctc_settings[ 'sermon_word_singular' ];
	
	$avail_types = array( 'sermon', 'event', 'location', 'person' );
	$supports = array( 'sermon' => 'ctc-sermons', 'event' => 'ctc-events', 'location' => 'ctc-locations', 'person' => 'ctc-people' );
	
	if( ! in_array( $post_type, $avail_types ) ) return;
	
	// Check if CC support is theme-defined
	$is_enabled_by_theme = in_array( $post_type, $ccx_theme_cc );
	
	$post_type = 'sermon' == $post_type ? $sermon_word_singular : $post_type;
	
	$attr = array();
	if( $is_enabled_by_theme ){
		$attr = array( 'readonly' => 'readonly' );
	}
	$desc = ucfirst( sprintf(  __( '%s posts already enabled by current theme', 'jsccx' ), $post_type ) );
	
	if( is_null( $name_field ) )
		$name_field = sprintf( __( 'Enable %s posts', 'jsccx' ), $post_type );
	
	$cpt_setting = array(
		'name' 							=> $name_field,
		'after_name'  			=> '',
		'desc'       			 	=> $is_enabled_by_theme ? $desc : '',
		'type'        			=> 'checkbox', 
		'checkbox_label'    => ucfirst( sprintf( __( '%s posts', 'jsccx' ), $post_type ) ), 
		'inline'            => false, 
		'options'           => array(),
		'upload_button'     => '', 
		'upload_title'      => '', 
		'upload_type'       => '', 
		'upload_show_image' => false, 
		'default'           => $is_enabled_by_theme, 
		'no_empty'          => false, 
		'allow_html'        => false, 
		'attributes'        => $attr, 
		'class'             => $is_enabled_by_theme ? 'ctc-setting-readonly' : '', 
		'content'           => '',
		'custom_sanitize'   => '', 
		'custom_content'    => '', 
		'pro'               => false, 
		'unsupported'       => false, 
	);
		
	return $cpt_setting;
}

/**
 * Shortcut to create a content block
 *
 * @since 0.1
 * @param $content string Display content
 * @return array Setting definition
 */
function ccx_settings_content( $content, $name_field = null ){
	$display = array(
		'name' 							=> $name_field,
		'after_name'  			=> '',
		'desc'       			 	=> '',
		'type'        			=> 'content', 
		'checkbox_label'    => '', 
		'inline'            => false, 
		'options'           => array(),
		'upload_button'     => '', 
		'upload_title'      => '', 
		'upload_type'       => '', 
		'upload_show_image' => false, 
		'default'           => '', 
		'no_empty'          => false, 
		'allow_html'        => false, 
		'attributes'        => '', 
		'class'             => '', 
		'content'           => $content,
		'custom_sanitize'   => '', 
		'custom_content'    => '', 
		'pro'               => false, 
		'unsupported'       => false, 
	);
	return $display;
}

/**
 * Shortcut for setting to enable of the CC categories 
 *
 * @since 0.1
 * @return array Setting definition
 */
function ccx_settings_enable_category( $category, $name_field = null ){
	
	// Get appropriate wording
	$ctc_settings = get_option( 'ctc_settings' );
	$sermon_word_singular = $ctc_settings[ 'sermon_word_singular' ];
	
	// categories
	$avail_cats = array( 'sermon_series', 'sermon_speaker', 'sermon_topic', 'sermon_book', 'sermon_tag', 'event_category', 'person_group' );
	
	if( ! in_array( $category, $avail_cats ) ) return;
	
	$name = str_replace( '_', ' ', $category );
	$name = str_replace( 'sermon', $sermon_word_singular, $name );
	
	if( is_null( $name_field ) )
		$name_field = sprintf( __( 'Enable %s category', 'jsccx' ), $name );
		
	$cat_setting = array(
		'name' 							=> $name_field,
		'after_name'  			=> '',
		'desc'       			 	=> '',
		'type'        			=> 'checkbox', 
		'checkbox_label'    => ucfirst( $name ), 
		'inline'            => false, 
		'options'           => array(),
		'upload_button'     => '', 
		'upload_title'      => '', 
		'upload_type'       => '', 
		'upload_show_image' => false, 
		'default'           => false, 
		'no_empty'          => false, 
		'allow_html'        => false, 
		'attributes'        => '', 
		'class'             => '', 
		'content'           => '',
		'custom_sanitize'   => '', 
		'custom_content'    => '', 
		'pro'               => false, 
		'unsupported'       => false, 
	);
		
	return $cat_setting;
}

/**
 * Add migration setting
 *
 * @since 0.4
 */
function ccx_migration_setting( $ccx_settings ){
 
	$ctc_settings = get_option( 'ctc_settings' );
	
	// Skip the setting if we've already migrated
	if( $ctc_settings[ 'ccx_migrated' ] ) return $ccx_settings;
		
	$ccx_settings[ 'fields' ][ 'ccx_migrate' ] = array(
		'name' 							=> __( 'Migrate from CTCEX', 'jsccx' ),
		'after_name'  			=> '',
		'desc'       			 	=> __( 'Migrate sermon images from CTC Extender to Church Content Extender, including sermon series images and sermon images.', 'jsccx' ),
		'type'        			=> 'checkbox', 
		'checkbox_label'    => __( 'Migrate from CTC Extender', 'jsccx' ), 
		'inline'            => false, 
		'options'           => array(),
		'upload_button'     => '', 
		'upload_title'      => '', 
		'upload_type'       => '', 
		'upload_show_image' => false, 
		'default'           => false, 
		'no_empty'          => false, 
		'allow_html'        => false, 
		'attributes'        => '', 
		'class'             => '', 
		'content'           => '',
		'custom_sanitize'   => '', 
		'custom_content'    => '', 
		'pro'               => false, 
		'unsupported'       => false, 
	);
		
	$ccx_settings[ 'fields' ][ 'ccx_migrate_blanks' ] = array(
		'name' 							=> ' ',
		'after_name'  			=> '',
		'desc'       			 	=> __( 'This will apply the default image (if set above) to posts that don\'t have a featured image. Default setting is to NOT apply it. Use caution as it\'s not reversible. ', 'jsccx' ),
		'type'        			=> 'checkbox', 
		'checkbox_label'    => __( 'Apply default images to sermon posts without a CTC Extender image', 'jsccx' ), 
		'inline'            => false, 
		'options'           => array(),
		'upload_button'     => '', 
		'upload_title'      => '', 
		'upload_type'       => '', 
		'upload_show_image' => false, 
		'default'           => false, 
		'no_empty'          => false, 
		'allow_html'        => false, 
		'attributes'        => '', 
		'class'             => '', 
		'content'           => '',
		'custom_sanitize'   => '', 
		'custom_content'    => '', 
		'pro'               => false, 
		'unsupported'       => false, 
	);
	
	return $ccx_settings;
}
