<?php
/**
 * Taxonomy Functions
 *
 * @package    	JSCCX
 * @author			Justin Serrano
 * @copyright		Copyright (c) 2020, Justin Serrano
 * @license    	http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @since      	0.2
 */

/**
 * Change slug of post types according to settings
 *
 * @since 0.2
 * @return string Filtered taxonomy definition arguments
 */
function ccx_change_taxonomy_slug( $args ){
	
	$ctc_settings = get_option( 'ctc_settings' );

	// Use the old slug to pull up the correct option
	$old_slug = $args['rewrite']['slug'];
	$old_slug = 'group' == $old_slug ? 'person_group' : str_replace( '-', '_', $old_slug );
	$new_slug = $ctc_settings[ $old_slug . '_url_slug' ];
	
	if( empty( $new_slug ) ) return $args;

	// Change the slug
	$args['rewrite']['slug'] = $new_slug;
		
	return $args;
} 
add_filter( 'ctc_taxonomy_sermon_series_args', 'ccx_change_taxonomy_slug' );
add_filter( 'ctc_taxonomy_sermon_speaker_args', 'ccx_change_taxonomy_slug' );
add_filter( 'ctc_taxonomy_sermon_topic_args', 'ccx_change_taxonomy_slug' );
add_filter( 'ctc_taxonomy_sermon_book_args', 'ccx_change_taxonomy_slug' );
add_filter( 'ctc_taxonomy_sermon_topic_args', 'ccx_change_taxonomy_slug' );
add_filter( 'ctc_taxonomy_event_category_args', 'ccx_change_taxonomy_slug' );
add_filter( 'ctc_taxonomy_person_group_args', 'ccx_change_taxonomy_slug' );