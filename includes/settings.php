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

// Settings needed
// Sermon series image

/**
 * Create a default image setting
 *
 * Custom setting for a default upload image
 *
 * @since 0.1
 * @access public
 */
	function ccx_default_image( $post_type ){
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
 * Force enable the 
 *
 * @since 0.1
 * @access public
 */
function ccx_enable_cpt_in_theme( $post_type ){
	$avail_types = array( 'sermon', 'event', 'location', 'person' );
	$supports = array( 'sermon' => 'ctc-sermons', 'event' => 'ctc-events', 'location' => 'ctc-locations', 'person' => 'people' );
	
	if( ! in_array( $post_type, $avail_types ) ) return;
	
	// If CC support is enabled by theme, this setting is readonly (i.e., it's hardcoded, so we won't disable support)
	$cc_enabled_by_theme = (bool) get_theme_support( 'church-theme-content' );
	$is_enabled_by_theme = (bool) get_theme_support( 'church-theme-content' ) && (bool) get_theme_support( $supports[ $post_type ] );
	$attr = array();
	if( $is_enabled_by_theme ){
		$attr = array( 'readonly' => 'readonly' );
	}
	$desc = ucfirst( sprintf(  __( '%s posts already enabled by current theme', 'jsccx' ), $post_type ) );
	
	$cpt_setting = array(
		'name' 							=> sprintf( __( 'Enable %s posts', 'jsccx' ), $post_type ),
		'after_name'  			=> '',
		'desc'       			 	=> $is_enabled_by_theme ? $desc : '',
		'type'        			=> 'checkbox', 
		'checkbox_label'    => sprintf( __( 'Enable %s posts for current theme', 'jsccx' ), $post_type ), 
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
		'class'             => $is_enabled_by_theme ? 'ctc-setting-readonly':'', 
		'content'           => '',
		'custom_sanitize'   => '', 
		'custom_content'    => '', 
		'pro'               => false, 
		'unsupported'       => false, 
	);
		
	return $cpt_setting;
}


/**
 * Setup CCX settings page within the CT settings
 *
 * @since 0.1
 * @access public
 */
function ccx_settings_setup(){
	
	$ccx_settings = array(
		'title' => __( 'CCX', 'jsccx' ),
		'desc' => __( "Church Content Extender provides additional options to the <b>Church Content</b> plugin", 'jsccx' ),
		'fields' => array(
			//'enable_sermon' => ccx_enable_cpt_in_theme( 'sermon' ),
			//'enable_event' => ccx_enable_cpt_in_theme( 'event' ),
			//'enable_location' => ccx_enable_cpt_in_theme( 'location' ),
			//'enable_person' => ccx_enable_cpt_in_theme( 'person' ),
			'default_sermon_image' => ccx_default_image( 'sermon' ),
			'default_event_image' => ccx_default_image( 'event' ),
			'default_location_image' => ccx_default_image( 'location' ),
			'default_person_image' => ccx_default_image( 'person' ),
		)
	);
	
	return $ccx_settings;
}

function ctc_settings_filter( $config ){
	
	if( 'CCX' == CCP_VERSION ) {
		// Eliminate some of CT's settings if we want to replace with custom versions
		unset( $config['sections']['licenses'] );
		unset( $config['sections']['podcast'] );
				
		// Clear pro flags and fake theme support (TODO)
		foreach( $config['sections'] as $section_id => $section ){
			if( isset( $section['fields'] ) ){
				foreach ( $section['fields'] as $field_id => $field ) {
					$config['sections'][ $section_id ]['fields'][ $field_id ]['unsupported'] = false; 
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

