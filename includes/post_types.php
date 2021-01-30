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

/**
 * Add new recurrence settings to event post types
 *
 * @since 0.51
 * @return object Filtered meta box
 */
function ccx_metabox_filter_event_date( $meta_box ) {
	
	// With the exception of daily recurrence, the other settings 
	// are included in the CTC plugin, but are not exposed by default. 
	
	// Add daily recurrence 
	$options = $meta_box['fields']['_ctc_event_recurrence']['options'];
	
	$meta_box['fields']['_ctc_event_recurrence']['options'] = ctc_array_merge_after_key(
		$options, 
		array( 'daily' => _x( 'Daily', 'event meta box', 'jsccx' ) ),
		'none'	
	);
	
	// Add recurrence period
	$recurrence_period = array(
		'name'	=> __( 'Recurrence Period', 'jsccx' ),
		'after_name'	=> '',
		'after_input'	=> '',
		'desc'	=> _x( 'Recur every N days/weeks/months/years', 'event meta box', 'jsccx' ),
		'type'	=> 'select', 
		'options'	=> array_combine( range(1,12), range(1,12) ) ,
		'default'	=> '1', 
		'no_empty'	=> true, 
		'allow_html'	=> false, 
		'visibility' 		=> array( 
			'_ctc_event_recurrence' => array( 'none', '!=' ),
		)
	);
	$meta_box['fields'] = ctc_array_merge_after_key(
		$meta_box['fields'], 
		array( '_ctc_event_recurrence_period' => $recurrence_period ),
		'_ctc_event_recurrence'	
	);
	
	// Add recurrence monthly type
	$recurrence_monthly_type = array(
		'name'	=> __( 'Monthly Recurrence Type', 'jsccx' ),
		'desc'	=> '',
		'type'	=> 'radio', 
		'options'	=> array( 
			'day'   => _x( 'On the same day of the month', 'monthly recurrence type', 'jsccx' ),
			'week'  => _x( 'On a specific week of the month', 'monthly recurrence type','jsccx' ),
		),
		'default'	=> 'day', 
		'no_empty'	=> true, 
		'allow_html'	=> false, 
		'visibility' 		=> array( 
			'_ctc_event_recurrence' => 'monthly',
		)
	);
	$meta_box['fields'] = ctc_array_merge_after_key(
		$meta_box['fields'], 
		array( '_ctc_event_recurrence_monthly_type' => $recurrence_monthly_type ),
		'_ctc_event_recurrence_period'	
	);
	
	// Add recurrence monthly week
	$recurrence_monthly_week = array(
		'name'	=> __( 'Monthly Recurrence Week', 'jsccx' ),
		'desc'	=> _x( 'Day of the week is the same as Start Date', 'event meta box', 'jsccx' ),
		'type'	=> 'select', 
		'options'	=> array( 
			'1' 		=> 'First Week',
			'2' 		=> 'Second Week',
			'3'		 	=> 'Third Week',
			'4' 		=> 'Fourth Week',
			'last' 	=> 'Last Week',
		) ,
		'default'	=> '', 
		'no_empty'	=> true, 
		'custom_field'	=> '', 
		'visibility' 		=> array( 
			'_ctc_event_recurrence_monthly_type' => 'week',
		)
	);
	$meta_box['fields'] = ctc_array_merge_after_key(
		$meta_box['fields'], 
		array( '_ctc_event_recurrence_monthly_week' => $recurrence_monthly_week ),
		'_ctc_event_recurrence_monthly_type'	
	);
	
	return $meta_box;
}
add_filter( 'ctmb_meta_box-ctc_event_date',  'ccx_metabox_filter_event_date' );