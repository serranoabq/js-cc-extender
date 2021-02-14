<?php
/**
 * Helper Functions
 *
 * @package    	JSCCX
 * @author			Justin Serrano
 * @copyright		Copyright (c) 2020, Justin Serrano
 * @license    	http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @since      	0.5
 */
 
/**
 * Get sermon data
 *
 * @since 0.5
 * @params $post_id Post ID
 */
function ccx_get_sermon_data( $post_id ){
	if( empty( $post_id ) ) return;
			
	$permalink = get_permalink( $post_id );
	$img_id = get_post_thumbnail_id( $post_id ); 
	
	// Sermon data
	$video = get_post_meta( $post_id, '_ctc_sermon_video' , true ); 
	$audio = get_post_meta( $post_id, '_ctc_sermon_audio' , true ); 
	
	// Series
	$ser_series = '';
	$ser_series_slug = '';
	$ser_series_link = '';
	$series = get_the_terms( $post_id, 'ctc_sermon_series');
	if( $series && ! is_wp_error( $series ) ) {
		$series = array_values ( $series );
		$series = array_shift( $series );
		$ser_series = $series -> name;
		$ser_series_slug = $series -> slug;
		$ser_series_link = get_term_link( intval( $series->term_id ), 'ctc_sermon_series' );
	}
	
	// Speakers
	$ser_speakers = '';
	$speakers = get_the_terms( $post_id, 'ctc_sermon_speaker' );
	if( $speakers && ! is_wp_error( $speakers ) ) {
		$speakers_A = array();
		foreach ( $speakers as $speaker ) { $speakers_A[] = $speaker -> name; }
		$last = array_pop( $speakers_A );
		if( $speakers_A ) {
			$last = implode(', ', $speakers_A). ", and " . $last;
		}
		$ser_speakers = $last;
	}
	
	// Topic
	$ser_topic = '';
	$ser_topic_slug = '';
	$ser_topic_link = '';
	$topics = get_the_terms( $post_id, 'ctc_sermon_topic' );
	if( $topics && ! is_wp_error( $topics ) ) {
		$topics = array_values ( $topics );
		$topics = array_shift( $topics );
		$ser_topic = $topics -> name;
		$ser_topic_slug = $topics -> slug;
		$ser_topic_link = get_term_link( intval( $topics->term_id ), 'ctc_sermon_topic' );
	}
	
	// Book
	$ser_book = '';
	$ser_book_slug = '';
	$ser_book_link = '';
	$books = get_the_terms( $post_id, 'ctc_sermon_book' );
	if( $books && ! is_wp_error( $books ) ) {
		$books = array_values ( $books );
		$books = array_shift( $books );
		$ser_book = $books -> name;
		$ser_book_slug = $topics -> slug;
		$ser_book_link = get_term_link( intval( $books->term_id ), 'ctc_sermon_book' );
	}
	
	$data = array(
		'post_id'     => $post_id,
		'name'        => get_the_title( $post_id ),
		'date'        => get_the_date( '', $post_id ),
		'permalink'   => $permalink,
		'img_id'      => $img_id,
		'series'      => $ser_series,
		'series_slug' => $ser_series_slug,
		'series_link' => $ser_series_link,
		'speakers'    => $ser_speakers,
		'topic'       => $ser_topic,
		'topic_slug'  => $ser_topic_slug,
		'topic_link'  => $ser_topic_link,
		'book'        => $ser_book,
		'book_slug'   => $ser_book_slug,
		'book_link'   => $ser_book_link,
		'audio'       => $audio,
		'video'       => $video,
	);
	
	$data = apply_filters( 'ccx_sermon_data', $data );
	
	return $data;
} 

/**
 * Get event data
 *
 * @since 0.5
 * @params $post_id Post ID
 */
function ccx_get_event_data( $post_id ){
	if( empty( $post_id ) ) return;
	
	$permalink = get_permalink( $post_id );
	$img_id = get_post_thumbnail_id( $post_id ); 
	
	// Event data
	$start = get_post_meta( $post_id, '_ctc_event_start_date' , true ); 
	$end = get_post_meta( $post_id, '_ctc_event_end_date' , true ); 
	$time = get_post_meta( $post_id, '_ctc_event_start_time' , true );
	$endtime = get_post_meta( $post_id, '_ctc_event_end_time' , true );
	if( $time ) $time = date('g:ia', strtotime( $time ) );
	if( $endtime && $time) $endtime = date('g:ia', strtotime( $endtime ) );
	$recurrence = get_post_meta( $post_id, '_ctc_event_recurrence' , true ); 
	$recurrence_note = ccx_get_recurrence_note( get_post( $post_id ) );
	
	$venue = get_post_meta( $post_id, '_ctc_event_venue' , true ); 
	$address = get_post_meta( $post_id, '_ctc_event_address' , true ); 
	
	$cats = get_the_terms( $post_id, 'ctc_event_category');
	if( $cats && ! is_wp_error( $cats ) ) {
		$cats_A = array();
		foreach( $cats as $cat ){
			$cats_A[] = sprintf('<a href="%s">%s</a>', get_term_link( intval( $cat->term_id ), 'ctc_event_category' ), $cat->name );
		}
		$categories = implode('; ', $cats_A );
	} else {
		$categories = '';
	}
	
	$data = array(
		'post_id'          => $post_id,
		'name'             => get_the_title( $post_id ),
		'permalink'        => $permalink,
		'img_id'           => $img_id,
		'address'          => $address,
		'venue'            => $venue,
		'categories'       => $categories,
		'start'            => $start,
		'end'              => $end,
		'time'             => $time,
		'endtime'          => $endtime,
		'recurrence'       => $recurrence,
		'recurrence_note'  => $recurrence_note,
	);
	
	return $data;
}

/**
 * Get location data
 *
 * @since 0.5
 * @params $post_id Post ID
 */
function ccx_get_location_data( $post_id ){
	if( empty( $post_id ) ) return;		
	
	$permalink = get_permalink( $post_id );
	$img_id = get_post_thumbnail_id( $post_id ); 
	
	// Location data
	$address = get_post_meta( $post_id, '_ctc_location_address' , true ); 
	$show_directions = get_post_meta( $post_id, '_ctc_location_show_directions_link' , true ); 
	$phone = get_post_meta( $post_id, '_ctc_location_phone' , true ); 
	$email = get_post_meta( $post_id, '_ctc_location_email' , true ); 
	$times = get_post_meta( $post_id, '_ctc_location_times' , true ); 
	$pastor = get_post_meta( $post_id, '_ctc_location_pastor' , true );  
	
	$data = array(
		'post_id'     => $post_id,
		'name'        => get_the_title( $post_id ),
		'permalink'   => $permalink,
		'img_id'      => $img_id,
		'address'     => $address,
		'phone'       => $phone,
		'email'       => $email,
		'times'       => $times,
		'pastor'      => $pastor,
		'order'			  => get_post_field( 'menu_order', $post_id),
	);
	
	return $data;
}

/**
 * Get person data
 *
 * @since 0.5
 * @params $post_id Post ID
 */
function ccx_get_person_data( $post_id ){
	if( empty( $post_id ) ) return;
	
	$permalink = get_permalink( $post_id );
	$img_id = get_post_thumbnail_id( $post_id ); 
	
	// Person data
	$position = get_post_meta( $post_id, '_ctc_person_position' , true ); 
	$email = get_post_meta( $post_id, '_ctc_person_email' , true ); 
	$phone = get_post_meta( $post_id, '_ctc_person_phone' , true ); 
	$url = get_post_meta( $post_id, '_ctc_person_urls' , true ); 
	$gender = get_post_meta( $post_id, '_ctc_person_gender' , true ); 
	
	$per_groups = '';
	$groups_slug = '';
	$groups = get_the_terms( $post_id, 'ctc_person_group');
	if( $groups && ! is_wp_error( $groups ) ) {
		$groups_A = array();
		$groups_S = array();
		foreach ( $groups as $group ) { 
			$groups_A[] = $group -> name; 
			$groups_S[] = $group -> slug; 
		}
		$per_groups = implode('; ', $groups_A);
		$groups_slug = implode('; ', $groups_S);
	}
	
	$data = array(
		'post_id'     => $post_id,
		'name'      => get_the_title( $post_id ),
		'permalink' => $permalink,
		'img_id'    => $img_id,
		'position'  => $position,
		'email'     => $email,
		'phone'    	=> $phone,
		'url'       => $url,
		'gender'    => $gender,
		'groups'    => $per_groups,
		'groups_slug'    => $groups_slug,
		'order'     => get_post_field( 'menu_order', $post_id),
	);
	
	return $data;
}

/**
 * Get CTC post data by post type and post IF
 *
 * @since 0.5
 * @params $post_type Post Type
 * @params $post_id Post ID
 */
function ccx_get_CTC_data( $post_type, $post_id ){
	switch( $post_type ) {
		case 'ctc_sermon':
			return ccx_get_sermon_data( $post_id );
			break;
		case 'ctc_event':
			return ccx_get_event_data( $post_id );
			break;
		case 'ctc_location':
			return ccx_get_location_data( $post_id );
			break;
		case 'ctc_person':
			return ccx_get_person_data( $post_id );
			break;
	}
}

/**
 * Get recurrence string from event posts
 *
 * @since 0.51
 * @param $post_obj Post object for event post
 * @return string Recurrence string
 */
function ccx_get_recurrence_note( $post_obj ) {
	if( !isset( $post_obj ) )
		global $post;
	else
		$post = $post_obj;
	
	$start_date = trim( get_post_meta( $post->ID , '_ctc_event_start_date' , true ) );
	$recurrence = get_post_meta( $post->ID , '_ctc_event_recurrence' , true );
	if( $recurrence == 'none' ) return '';
	
	$recurrence_end_date = get_post_meta( $post->ID, '_ctc_event_recurrence_end_date', true );
	$recurrence_period = get_post_meta( $post->ID , '_ctc_event_recurrence_period' , true );
	$recurrence_monthly_type = get_post_meta( $post->ID , '_ctc_event_recurrence_monthly_type' , true );
	$recurrence_monthly_week = get_post_meta( $post->ID , '_ctc_event_recurrence_monthly_week' , true );
	$recurrence_note = '';
	
	// Frequency
	switch ( $recurrence ) {

		case 'daily' :
			$recurrence_note = sprintf( 
				_n( 'Every day','Every %d days', (int)$recurrence_period, 'ctcex' ), 
				(int)$recurrence_period 
			);
			break;
			
		case 'weekly' :
			$recurrence_note = sprintf( 
				_n( 'Every %s', '%ss every %d weeks', (int)$recurrence_period, 'ctcex' ), date_i18n( 'l' , strtotime( $start_date ) ),
				(int)$recurrence_period 
			);
			break;

		case 'monthly' :
			$recurrence_note = sprintf( 
				_n( 'Every month','Every %d months', (int)$recurrence_period, 'ctcex' ), 
				(int)$recurrence_period 
			);
			break;

		case 'yearly' :
			$recurrence_note = sprintf( 
				_n( 'Every year','Every %d years', (int)$recurrence_period, 'ctcex' ), 
				(int)$recurrence_period 
			);
			break;

	}
	
	if( 'monthly' == $recurrence && $recurrence_monthly_type && $recurrence_monthly_week ) {
		if( 'day' == $recurrence_monthly_type ) {
			$recurrence_note .= sprintf( _x(' on the %s', 'Date expression. As in " on the 1st/2nd...31st of the month". Note the space before.', 'ctcex'), date_i18n( 'jS' , strtotime( $start_date ) ) );
		} else {
			$ends = array( 
				'1' => _x( '1st', 'As in "1st Sun/Mon... of the month"', 'ctcex'), 
				'2' => _x( '2nd', 'As in "2nd Sun/Mon... of the month"', 'ctcex'), 
				'3' => _x( '3rd', 'As in "3rd Sun/Mon... of the month"', 'ctcex'), 
				'4' => _x( '4th', 'As in "4th Sun/Mon... of the month"', 'ctcex') 
			);
			if( $recurrence_monthly_week != 'last' )
				$recurrence_monthly_week = $ends[ $recurrence_monthly_week ];
			else
				$recurrence_monthly_week = _x( 'last', 'As in "last Sun/Mon... of the month"', 'ctcex');
				
			$recurrence_note .= sprintf( _x(' on the %s %s', 'Date expression. As in " on the 1st/2nd... Sun/Mon...". Note the space before.', 'ctcex'), $recurrence_monthly_week, date_i18n( 'l' , strtotime( $start_date ) ) );
		}
	}
	
	if( $recurrence_end_date ) {
		$recurrence_note .= sprintf( ' until %s', date_i18n( 'D, M jS' , strtotime( $recurrence_end_date ) ) );
	}
	return $recurrence_note;
}