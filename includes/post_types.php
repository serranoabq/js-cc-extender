<?php
/**
 * Custom post type modifications
 *
 * @package    	JSCCX
 * @author			Justin Serrano
 * @copyright		Copyright (c) 2020, Justin Serrano
 * @license    	http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @since      	0.2
 */
 
// No direct access
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Filter the singular name for sermons
 *
 * @since 0.2
 * @return string Filtered singular name
 */
function ccx_sermon_singular( $singular ){
	
	$ctc_settings = get_option( 'ctc_settings' );
	
	if( ! empty( $ctc_settings['sermon_word_singular'] ) ) {
		return $ctc_settings['sermon_word_singular'];
	}
	
	return $singular;
}
add_filter( 'ctc_post_type_sermon_singular', 'ccx_sermon_singular' );

/**
 * Filter plural name for sermons
 *
 * @since 0.2
 * @return string Filtered plural name
 */
function ccx_sermon_plural( $plural ){
	
	$ctc_settings = get_option( 'ctc_settings' );
	
	if( ! empty( $ctc_settings['sermon_word_plural'] ) ) {
		return $ctc_settings['sermon_word_plural'];
	}
	
	return $plural;
}
add_filter( 'ctc_post_type_sermon_plural', 'ccx_sermon_plural' );

/**
 * Change slug of post types according to settings
 *
 * @since 0.2
 * @return string Filtered CPT definition arguments
 */
function ccx_change_slug( $args ){
	
	$ctc_settings = get_option( 'ctc_settings' );

	// Use the old slug to pull up the correct option
	$old_slug = $args['rewrite']['slug'];
	$old_slug = 'people' == $old_slug ? 'person' : substr( $old_slug, 0, -1 );
	$new_slug = $ctc_settings[ $old_slug . '_url_slug' ];
	
	if( empty( $new_slug ) ) return $args;

	// Change the slug
	$args['rewrite']['slug'] = $new_slug;
		
	return $args;
} 
add_filter( 'ctc_post_type_sermon_args', 'ccx_change_slug' );
add_filter( 'ctc_post_type_location_args', 'ccx_change_slug' );
add_filter( 'ctc_post_type_event_args', 'ccx_change_slug' );
add_filter( 'ctc_post_type_person_args', 'ccx_change_slug' );