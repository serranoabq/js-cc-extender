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
 * @since 0.3
 * @return array CCX-specific settings
 */
function ccx_settings_setup(){
	
	$ccx_settings = array(
		'title' => __( 'CCX', 'jsccx' ),
		'desc' => __( "Church Content Extender (CCX) provides additional options to the <b>Church Content</b> plugin", 'jsccx' ),
		'fields' => array(
			// Post types
			'enable_sermon' => ccx_settings_enable_cpt( 'sermon' , __( 'Enable Church Content post types for theme' , 'jsccx' ) ),
			'enable_event' => ccx_settings_enable_cpt( 'event', '' ),
			'enable_location' => ccx_settings_enable_cpt( 'location', '' ),
			'enable_person' => ccx_settings_enable_cpt( 'person', '' ),
			
			// Categories
			'enable_sermon_speaker' => ccx_settings_enable_category( 'sermon_speaker', __( 'Enable categories for theme', 'jsccx') ),
			'enable_sermon_topic' => ccx_settings_enable_category( 'sermon_topic', '' ),
			'enable_sermon_series' => ccx_settings_enable_category( 'sermon_series', '' ),
			'enable_sermon_tag' => ccx_settings_enable_category( 'sermon_tag', '' ),
			'enable_sermon_book' => ccx_settings_enable_category( 'sermon_book', '' ),
			'enable_event_category' => ccx_settings_enable_category( 'event_category', '' ),
			'enable_event_category' => ccx_settings_enable_category( 'person_group', '' ),
			
			// Images
			'default_sermon_image' => ccx_settings_default_image( 'sermon' ),
			'default_event_image' => ccx_settings_default_image( 'event' ),
			'default_location_image' => ccx_settings_default_image( 'location' ),
			'default_person_image' => ccx_settings_default_image( 'person' ),
		)
	);
	
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
	$avail_types = array( 'sermon', 'event', 'location', 'person' );
	
	if( ! in_array( $post_type, $avail_types ) ) return;
	
	$default_image_setting = array(
		'name' 							=> sprintf( __( 'Default %s image','jsccx' ), $post_type ),
		'after_name'  			=> '',
		'desc'       			 	=> sprintf( __( 'Default featured image to attach to %s posts', 'jsccx' ), $post_type ),
		'type'        			=> 'upload', 
		'checkbox_label'    => '', 
		'inline'            => false, 
		'options'           => array(),
		'upload_button'     => 'Choose image', 
		'upload_title'      => __( 'Default image', 'jsccx'), 
		'upload_type'       => 'image', 
		'upload_show_image' => 200, 
		'default'           => '', 
		'no_empty'          => false, 
		'allow_html'        => false, 
		'attributes'        => array(), 
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
 * @since 0.3
 * @global $ccx_forced_cc
 * @param string $post_type Post type for setting
 * @param string $name_field String for field name
 * @return array Setting definition
 */
function ccx_settings_enable_cpt( $post_type, $name_field = null ){
	global $ccx_forced_cc;
	
	$avail_types = array( 'sermon', 'event', 'location', 'person' );
	$supports = array( 'sermon' => 'ctc-sermons', 'event' => 'ctc-events', 'location' => 'ctc-locations', 'person' => 'people' );
	
	if( ! in_array( $post_type, $avail_types ) ) return;
	
	// Check if CC support is theme-defined
	$is_enabled_by_theme = false;
	if( ! empty( $ccx_forced_cc ) ){
		$featured_enabled = (bool) get_theme_support( $supports[ $post_type ] );
		$is_enabled_by_theme = $featured_enabled && ! in_array( $post_type, $ccx_forced_cc );
	}
	
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
		'checkbox_label'    => sprintf( __( 'Enable %s posts', 'jsccx' ), $post_type ), 
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
function ccx_settings_content( $content ){
	$display = array(
		'name' 							=> '',
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
 * @since 0.3
 * @global $ccx_forced_cc
 * @param string $category Category for setting
 * @param string $name_field String for field name
 * @return array Setting definition
 */
function ccx_settings_enable_category( $category, $name_field = null ){
	global $ccx_forced_cc;
	
	// categories
	$avail_cats = array( 'sermon_series', 'sermon_speaker', 'sermon_topic', 'sermon_book', 'sermon_tag', 'event_category', 'person_group' );
	
	if( ! in_array( $category, $avail_cats ) ) return;
	
	//$supports = array( 'sermon' => 'ctc-sermons', 'event' => 'ctc-events', 'location' => 'ctc-locations', 'person' => 'people' );
	
	// Check if CC support is theme-defined
	$is_enabled_by_theme = false;
	/*
	if( ! empty( $ccx_forced_cc ) ){
		$featured_enabled = (bool) get_theme_support( 'ctc_' . $category );
		$is_enabled_by_theme = $featured_enabled && ! in_array( $category, $ccx_forced_cc );
	}
	*/
	$attr = array();
	if( $is_enabled_by_theme ){
		$attr = array( 'readonly' => 'readonly' );
	}
	$name = str_replace( '_', ' ', $category );
	$desc = ucfirst( sprintf(  __( '%s category already enabled by current theme', 'jsccx' ), $name ) );
	
	if( is_null( $name_field ) )
		$name_field = sprintf( __( 'Enable %s category', 'jsccx' ), $name );
		
	$cat_setting = array(
		'name' 							=> $name_field,
		'after_name'  			=> '',
		'desc'       			 	=> $is_enabled_by_theme ? $desc : '',
		'type'        			=> 'checkbox', 
		'checkbox_label'    => sprintf( __( 'Enable %s', 'jsccx' ), $name ), 
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
		
	return $cat_setting;
}
