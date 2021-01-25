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
	$recurrence_note = $this->get_recurrence_note( get_post( $post_id ) );
	
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
	$phone = get_post_meta( $post_id, '_ctc_location_phone' , true ); 
	$times = get_post_meta( $post_id, '_ctc_location_times' , true ); 
	$pastor = get_post_meta( $post_id, '_ctc_location_pastor' , true );  
	
	$data = array(
		'post_id'     => $post_id,
		'name'        => get_the_title( $post_id ),
		'permalink'   => $permalink,
		'img_id'      => $img_id,
		'slider'      => $slider,
		'address'     => $address,
		'phone'       => $phone,
		'times'       => $times,
		'pastor'      => $pastor,
		'map_url'		  => $map_url,
		'map_img_url'	=> $map_img_url,
		'map_used'    => $map_used,
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
	$img = get_post_meta( $post_id, '_ctc_image' , true ); 
	$img_id = get_post_meta( $post_id, '_ctc_image_id' , true ); 
	
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
		'img'       => $img,
		'img_id'    => $img_id,
		'position'  => $position,
		'email'     => $email,
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