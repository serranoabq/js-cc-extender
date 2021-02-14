<?php
/**
 * Shortcode Functions
 *
 * @package    	JSCCX
 * @author			Justin Serrano
 * @copyright		Copyright (c) 2020, Justin Serrano
 * @license    	http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @since      	0.5
 */

define( 'CCX_SHORTCODE_DETAILS', 1 );
define( 'CCX_SHORTCODE_MEDIA', 2 );
define( 'CCX_SHORTCODE_FULL', CCX_SHORTCODE_DETAILS + CCX_SHORTCODE_MEDIA );

/**
 * Add a metabox with the shortcodes available for the post
 *
 * @since 0.5
 */
function ccx_shortcode_metabox(){
	
	$post_types = array( 
		'ctc_sermon', 
		'ctc_event', 
		'ctc_location', 
		'ctc_person' 
	);
	
	foreach( $post_types as $post_type ) {
		add_meta_box(
			$post_type . '_shortcode',
			__( 'Available Shortcodes', 'jsccx' ),
			'ccx_shortcode_metabox_render',
			$post_type,
			'normal',
			'high',
			array(
				'__block_editor_compatible_meta_box' => true,
			) 
		);
		
	}
}
add_action( 'admin_init', 'ccx_shortcode_metabox' );

/**
 * Render the shortcode metabox
 *
 * @since 0.5
 */
function ccx_shortcode_metabox_render( $args ){
	
	$post_type = str_replace( 'ctc_', 'ccx_', $args->post_type );
	
	echo "<pre>";
	echo sprintf('[%s id="%s" size="full|large|medium|thumbnail" %s show_title] </br>', $post_type, $args->ID, "type=image|audio|video" );
	echo sprintf('[%s_details id="%s" show_title]</br>', $post_type, $args->ID );
	echo sprintf('[%s_media id="%s" size="full|large|medium|thumbnail" show_title]', $post_type, $args->ID );
	echo "</pre>";
}

/**
 * Get shortcode classes
 *
 * @since 0.5
 * @return array Filtered array of classes
 */
function ccx_shortcode_classes(){
	$classes = array(
		'name'       			=> 'ccx-name',
		'img'        			=> 'ccx-img',
		'container'  			=> 'ccx-container',
		'media'      			=> 'ccx-media',
		'media_link'      => 'ccx-media-link',
		'details'    			=> 'ccx-details',
		'sermon_date'     => 'ccx-sermon-date',
		'sermon_speaker'  => 'ccx-sermon-speaker',
		'sermon_series'   => 'ccx-sermon-series',
		'sermon_topic'    => 'ccx-sermon-topic',
		'audio-link' 			=> 'ccx-sermon-audio-link',
		'audio'      			=> 'ccx-sermon-audio',
		'video'      			=> 'ccx-sermon-video',
		'event_date'      => 'ccx-event-date',
		'event_time'      => 'ccx-event-time',
		'event_venue'     => 'ccx-event-venue',
		'event_category'	=> 'ccx-event-category',
		'person_position'	=> 'ccx-person-position',
		'person_email'		=> 'ccx-person-email',
		'person_phone'		=> 'ccx-person-phone',
		'person_url'			=> 'ccx-person-url',
		'person_group'		=> 'ccx-person-group',
		'location_address'=> 'ccx-location-address',
		'location_phone'	=> 'ccx-location-phone',
		'location_email'	=> 'ccx-location-email',
		'location_times'	=> 'ccx-location-times',
		'location_pastor'	=> 'ccx-location-pastor',
	);
	
	$classes = apply_filters( 'ccx_classes', $classes );
	
	return $classes;
}

/*** Shortcode Handlers ***/
/**
 * Global handler for shortcodes
 *
 * @since 0.53
 * @params $attr 						Shortcode attributes
 * @params $post_type				Post type
 * @params $shortcode_type  Shortcode type (CCX_SHORTCODE_DETAILS, CCX_SHORTCODE_MEDIA or CCX_SHORTCODE_FULL)
 */
function ccx_shortcode_handler( $attr, $post_type, $shortcode_type ){
	
	// Valid post_types
	$post_types = array( 
		'ctc_sermon', 
		'ctc_event', 
		'ctc_location', 
		'ctc_person' 
	);
	
	if( ! in_array( $post_type, $post_types ) ) return;
	
	extract( shortcode_atts( array(
		'id' 	=>  null, 	// ID of post to show 
		'size' => 'full',	// Named size of image. Can be WP default full|large|medium|thumbnail or a different size defined by the theme
		'type' => null,		// Force a specific type of media (image|audio|video; sermon only)
		), $attr ) );
	
	if( is_string( $attr ) && empty( $attr ) )
		$attr = array();
	
	if( ! $id ) {
		// If no ID given...
		if( $post_type == get_post_type() ){
			// ...and the current post is of the right type, use its ID
			$id = get_the_ID();
		} else {
			// ...or get the latest post of the right post type
			$m_post = get_posts("post_type=" . $post_type . "&numberposts=1");
			$id = $m_post[0]->ID;
		}
	}
	
	$show_title = in_array( 'show_title', $attr );
	
	$data = ccx_get_CTC_data( $post_type, $id );
	$classes = ccx_shortcode_classes();
	$name = sprintf( '<h2 class="%s">%s</h2>', $classes[ 'name' ], $data[ 'name' ] );
	
	$media = ccx_get_media_output( $data, $post_type, $size, $type );
	switch( $post_type ){
		case( 'ctc_sermon' ):
			$details = ccx_get_sermon_details( $data );
			break;
		case( 'ctc_event' ):
			$details = ccx_get_event_details( $data );
			break;
		case( 'ctc_location' ):
			$details = ccx_get_location_details( $data );
			break;
		case( 'ctc_person' ):
			$details = ccx_get_person_details( $data );
			break;
	}
	
	$output = sprintf(
		'<div class="%s">
			%s
			%s
			%s
		</div>
		',
		$classes[ 'container' ],
		( $shortcode_type == CCX_SHORTCODE_MEDIA || $shortcode_type == CCX_SHORTCODE_FULL ) ? $media : '',
		$show_title ? $name : '',
		( $shortcode_type == CCX_SHORTCODE_DETAILS || $shortcode_type == CCX_SHORTCODE_FULL ) ? $details : ''
	);
	
	return $output;
}

/**
 * Sermon shortcode handler
 *
 * @since 0.53
 * @return string Sermon output
*/
function ccx_sermon_shortcode( $attr ){
	ob_start();
	echo ccx_shortcode_handler( $attr, 'ctc_sermon', CCX_SHORTCODE_FULL);
	return ob_get_clean();
}
add_shortcode( 'ccx_sermon', 'ccx_sermon_shortcode' );

/**
 * Sermon details shortcode handler
 *
 * @since 0.5
 * @return string Sermon details output
 */
function ccx_sermon_detail_shortcode( $attr ){
	ob_start();
	echo ccx_shortcode_handler( $attr, 'ctc_sermon', CCX_SHORTCODE_DETAILS );
	return ob_get_clean();
}
add_shortcode( 'ccx_sermon_details', 'ccx_sermon_detail_shortcode' );

/**
 * Sermon media shortcode handler
 *
 * @since 0.53
 * @return string Sermon media output
*/
function ccx_sermon_media_shortcode( $attr ){
	ob_start();
	echo ccx_shortcode_handler( $attr, 'ctc_sermon', CCX_SHORTCODE_MEDIA );
	return ob_get_clean();
}
add_shortcode( 'ccx_sermon_media', 'ccx_sermon_media_shortcode' );

/**
 * Event shortcode handler
 *
 * @since 0.53
 * @return string Event output
*/
function ccx_event_shortcode( $attr ){
	ob_start();
	echo ccx_shortcode_handler( $attr, 'ctc_event', CCX_SHORTCODE_FULL);
	return ob_get_clean();
}
add_shortcode( 'ccx_event', 'ccx_event_shortcode' );

/**
 * Event details shortcode handler
 *
 * @since 0.51
 * @return string Event details output
 */
function ccx_event_detail_shortcode( $attr ){
	ob_start();
	echo ccx_shortcode_handler( $attr, 'ctc_event', CCX_SHORTCODE_DETAILS );
	return ob_get_clean();
}
add_shortcode( 'ccx_event_details', 'ccx_event_detail_shortcode' );

/**
 * Event media shortcode handler
 *
 * @since 0.53
 * @return string Event media output
*/
function ccx_event_media_shortcode( $attr ){
	ob_start();
	echo ccx_shortcode_handler( $attr, 'ctc_event', CCX_SHORTCODE_MEDIA );
	return ob_get_clean();
}
add_shortcode( 'ccx_event_media', 'ccx_event_media_shortcode' );

/**
 * Location shortcode handler
 *
 * @since 0.53
 * @return string Location output
*/
function ccx_location_shortcode( $attr ){
	ob_start();
	echo ccx_shortcode_handler( $attr, 'ctc_location', CCX_SHORTCODE_FULL);
	return ob_get_clean();
}
add_shortcode( 'ccx_location', 'ccx_location_shortcode' );

/**
 * Location details shortcode
 *
 * @since 0.53
 */
function ccx_location_detail_shortcode( $attr ){
	ob_start();
	echo ccx_shortcode_handler( $attr, 'ctc_location', CCX_SHORTCODE_DETAILS );
	return ob_get_clean();
}
add_shortcode( 'ccx_location_details', 'ccx_location_detail_shortcode' );

/**
 * Location media shortcode handler
 *
 * @since 0.53
 * @return string Location media output
*/
function ccx_location_media_shortcode( $attr ){
	ob_start();
	echo ccx_shortcode_handler( $attr, 'ctc_location', CCX_SHORTCODE_MEDIA );
	return ob_get_clean();
}
add_shortcode( 'ccx_location_media', 'ccx_location_media_shortcode' );

/**
 * Person shortcode handler
 *
 * @since 0.53
 * @return string Person output
*/
function ccx_person_shortcode( $attr ){
	ob_start();
	echo ccx_shortcode_handler( $attr, 'ctc_person', CCX_SHORTCODE_FULL);
	return ob_get_clean();
}
add_shortcode( 'ccx_person', 'ccx_person_shortcode' );

/**
 * Person details shortcode handler
 *
 * @since 0.52
 * @return string Person details output
 */
function ccx_person_detail_shortcode( $attr ){
	ob_start();
	echo ccx_shortcode_handler( $attr, 'ctc_person', CCX_SHORTCODE_DETAILS );
	return ob_get_clean();
}
add_shortcode( 'ccx_person_details', 'ccx_person_detail_shortcode' );

/**
 * Person media shortcode handler
 *
 * @since 0.53
 * @return string Person details output
*/
function ccx_person_media_shortcode( $attr ){
	ob_start();
	echo ccx_shortcode_handler( $attr, 'ctc_person', CCX_SHORTCODE_MEDIA );
	return ob_get_clean();
}
add_shortcode( 'ccx_person_media', 'ccx_person_media_shortcode' );

/*** Detail outputs ***/
/**
 * Get Sermon details output
 *
 * @since 0.5
 * @return string Filtered output with the details
 */
function ccx_get_sermon_details( $data ){
	
	// Use the name as a failsafe
	if( !isset( $data[ 'name' ] ) ) return;
	
	// Prep some stuff
	$classes = ccx_shortcode_classes();
	$name_mask = '<h2 class="%s">%s</h2>';
	$detail_mask = '<div class="%s"><b>%s:</b> %s</div>';
	$link_mask = '<a href="%s">%s</a>';
	
	// Get date
	$date_src = sprintf( $detail_mask, $classes[ 'sermon_date' ], __( 'Date', 'jsccx' ), $data[ 'date' ] );
	
	// Get speaker
	$speaker_src = isset( $data[ 'speakers' ] ) ? sprintf( $detail_mask, $classes[ 'sermon_speaker' ], __( 'Speaker', 'jsccx' ), $data[ 'speakers' ] ) : '';
	
	// Get series
	$series = isset( $data[ 'series' ] ) ? $data[ 'series' ] : '';
	$series_link = $series && isset( $data[ 'series_link' ] ) ? sprintf( $link_mask, $data[ 'series_link' ], $series ) : $series;
	$series_src = $series ?	sprintf( $detail_mask, $classes[ 'sermon_series' ],  __( 'Series', 'jsccx' ), $series_link ) : '';
	
	// Get topics
	$topic = isset( $data[ 'topic' ] ) ? $data[ 'topic' ] : '';
	$topic_link = $topic && isset( $data[ 'topic_link' ] ) ? sprintf( $link_mask, $data[ 'topic_link' ], $topic ) : $topic;
	$topic_src = $topic ? sprintf( $detail_mask, $classes[ 'sermon_topic' ], __( 'Topic', 'jsccx' ), $topic_link ) : '';

	// Get output
	$output = sprintf(' 
		<div class="%s">
			%s
			%s
			%s
			%s
		</div>',
		$classes[ 'details' ],
		$date_src,
		$speaker_src,
		$series_src,
		$topic_src,
	);
	$output = apply_filters( 'ccx_sermon_details', $output, $data );
	
	return $output;
}

/**
 * Get Event details output
 *
 * @since 0.51
 * @return string Filtered event details
 */
function ccx_get_event_details( $data ){
	
	// Use the name as a failsafe
	if( !isset( $data[ 'name' ] ) ) return;
	
	// Prep some stuff
	$classes = ccx_shortcode_classes();
	$name_mask = '<h2 class="%s">%s</h2>';
	$detail_mask = '<div class="%s"><b>%s:</b> %s</div>';
	$link_mask = '<a href="%s">%s</a>';
	
	// Get times
	$start = isset( $data[ 'start' ] ) ? $data[ 'start' ] : '';
	$end = isset( $data[ 'end' ] ) ? $data[ 'end' ] : '';
	$date_str = $start ? sprintf( '%s%s',  date_i18n( 'l, F j', strtotime( $start ) ), $start != $end ? ' - '. date_i18n( 'l, F j', strtotime( $end ) ) : '' ) : '';
	$date_src = sprintf( $detail_mask, $classes[ 'event_date' ], __( 'Date', 'jsccx' ), $date_str );
	
	// Get time
	$time = isset( $data[ 'time' ] ) ? $data[ 'time' ] : '';
	$endtime = isset( $data[ 'endtime' ] ) ? $data[ 'endtime' ] : '';	
	$time_str = $time ? sprintf( '%s%s',  $time, $endtime ? ' - '. $endtime : '' ) : '';
	$time_src = $time_str ? sprintf( $detail_mask, $classes[ 'event_time' ], __( 'Time', 'jsccx' ), $time_str ) : '';
	
	// Get recurrence
	$recurrence = isset( $data[ 'recurrence_note' ] ) ? $data[ 'recurrence_note' ] : '';
	
	// Get venue
	$venue = isset( $data[ 'venue' ] ) ? $data[ 'venue' ] : '';
	$address = isset( $data[ 'address' ] ) ? $data[ 'address' ] : '';
	$location_str = $venue ? $venue : $address;
	$location_src = $location_str ? sprintf( $detail_mask, $classes[ 'event_venue' ] , __( 'Location', 'jsccx' ), $location_str ) : '';
	
	// Get category
	$category_src = isset( $data[ 'categories' ] ) ? sprintf( $detail_mask, $classes[ 'event_category' ], __( 'Category', 'jsccx' ), $data[ 'categories' ] ) : '';
	
	// Get output
	$output = sprintf(' 
		<div class="%s">
			%s
			%s
			%s
			%s
			%s
		</div>',
		$classes[ 'details' ],
		$date_src,
		$recurrence,
		$time_src,
		$location_src,
		$category_src,
	);
	$output = apply_filters( 'ccx_event_details', $output, $data );
	
	return $output;
}

/**
 * Get Location details output
 *
 * @since 0.53
 * @return string Filtered location details
 * @params array $data Location post data
 */
function ccx_get_location_details( $data ){
	
	// Use the name as a failsafe
	if( !isset( $data[ 'name' ] ) ) return;
	
	// Prep some stuff
	$classes = ccx_shortcode_classes();
	$name_mask = '<h2 class="%s">%s</h2>';
	$detail_mask = '<div class="%s"><b>%s:</b> %s</div>';
	$link_mask = '<a href="%s">%s</a>';
	
	// Get address
	$address = !empty( $data[ 'address' ] ) ? sprintf( $detail_mask, $classes[ 'location_address' ], __( 'Address', 'jsccx' ), $data[ 'address' ] ) : '';
	
	// Get phone
	$phone = !empty( $data[ 'phone' ] ) ? sprintf( $detail_mask, $classes[ 'location_phone' ], __( 'Phone', 'jsccx' ), $data[ 'phone' ] ) : '';
	
	// Get email
	$email = !empty( $data[ 'email' ] ) ? sprintf( $detail_mask, $classes[ 'location_email' ], __( 'Email', 'jsccx' ), $data[ 'email' ] ) : '';
	
	// Get times
	$times = !empty( $data[ 'times' ] ) ? sprintf( $detail_mask, $classes[ 'location_times' ], __( 'Times', 'jsccx' ), $data[ 'times' ] ) : '';
	
	// Get pastor
	$pastor = !empty( $data[ 'pastor' ] ) ? sprintf( $detail_mask, $classes[ 'location_pastor' ], __( 'Pastor', 'jsccx' ), $data[ 'pastor' ] ) : '';	
	
	// Get output
	$output = sprintf(' 
		<div class="%s">
			%s
			%s
			%s
			%s
			%s
		</div>',
		$classes[ 'details' ],
		$address,
		$times,
		$phone,
		$email,
		$pastor,
	);
	$output = apply_filters( 'ccx_location_details', $output, $data );
	
	return $output;
}

/**
 * Get person details output
 *
 * @since 0.52
 * @return string Filtered CPT definition arguments
 */
function ccx_get_person_details( $data ){
	
	// Use the name as a failsafe
	if( !isset( $data[ 'name' ] ) ) return;
	
	// Prep some stuff
	$classes = ccx_shortcode_classes();
	$name_mask = '<h2 class="%s">%s</h2>';
	$detail_mask = '<div class="%s"><b>%s:</b> %s</div>';
	$list_mask = '<ul class="%s">%s</ul>'; 
	$list_item_mask = '<li><a href="%s">%s</a></li>'; 
	$link_mask = '<a href="%s">%s</a>';
	
	// Get position
	$position_src = isset( $data[ 'position' ] ) ? sprintf( $detail_mask, $classes[ 'person_position' ], __( 'Position', 'jsccx' ), $data[ 'position' ] ) : '';
	
	// Get phone
	$phone_src = $data[ 'phone' ] ? sprintf( $detail_mask, $classes[ 'person_phone' ], __( 'Phone', 'jsccx' ), $data[ 'phone' ] ) : '';

	// Get email
	$email_src = $data[ 'email' ] ? sprintf( $detail_mask, $classes[ 'person_email' ], __( 'Email', 'jsccx' ), $data[ 'email' ] ) : '';
	
	// Get URLs, adding email to the list
	$url_src = '';
	$urls = $data[ 'url' ] ? explode( "\r\n", $data[ 'url' ] ): array() ;
	foreach( $urls as $url ){
		$url_src .= sprintf( $list_item_mask, $url, $url );
	}
	$url_src = $url_src ? sprintf( $list_mask, $classes[ 'person_url' ], $url_src ) : '';
	
	// Get group
	$group = $data[ 'groups' ] ? $data[ 'groups' ] : '';
	$group_link = $group && isset( $data[ 'groups_link' ] ) ? sprintf( $link_mask, $data[ 'groups_link' ], $group ) : $group;
	$group_src = $group ? sprintf( $detail_mask, $classes[ 'group_link' ], __( 'Group', 'jsccx' ), $group_link ) : '';
	
	// Get output
	$output = sprintf(' 
		<div class="%s">
			%s
			%s
			%s
			%s
		</div>',
		$classes[ 'details' ],
		$position_src,
		$phone_src,
		$email_src,
		$url_src,
	);
	$output = apply_filters( 'ccx_person_details', $output, $data );
	
	return $output;
	
}

/**
 * Get media output
 *
 * @since 0.53
 * @return string Media output
 */
function ccx_get_media_output( $data, $post_type, $size = 'full', $media_type = '' ){
	
	$has_image = !empty( $data[ 'img_id'] );
	$has_audio = isset( $data[ 'audio' ] ) && !empty( $data[ 'audio' ] );
	$has_video = isset( $data[ 'video' ] ) && !empty( $data[ 'video' ] );
	$classes = ccx_shortcode_classes();
	
	$img_src = '';
	$img = '';
	$w = 1280; 
	if( $has_image ) {
		$img_src = wp_get_attachment_image( $data[ 'img_id' ], $size, '', ['class' => $classes[ 'img' ] ] );
		$img = wp_get_attachment_image_src( $data[ 'img_id' ], $size );
		$img = $img[0];
		
		// Need to figure out the width
		$raw_w = wp_get_attachment_metadata( $data[ 'img_id' ] );
		$raw_w = $raw_w[ 'width' ];			
		if( $size != 'full' ){
			// Figure out if the size is a defined size
			$image_sizes = ccx_get_all_image_sizes();
			if( isset( $image_sizes[ $size ] ) ){
				$w = min( $raw_w, $image_sizes[ $size ][ 'width' ] );
			}
		}
	}
	
	$audio_src = '';
	if( $has_audio ){
		$audio_args = array( 
			'src' 	=> $data[ 'audio' ], 
			'class'	=> $classes[ 'audio' ] . ' wp-audio-shortcode'
		);
		if( $has_image ){
			$audio_args[ 'style' ] = "width:100%; max-width:{$w}px";
		}
		$audio_src = wp_audio_shortcode( $audio_args );
		$audio_src .= "\n<style>.{$classes['audio']} { max-width:{$w}px !important;}</style>"; 
	}
	
	$video_src = '';
	if( $has_video ){
		$video = esc_url( $data[ 'video' ] );
		$video_args = array(
			'src'      => $video,
			'height'   => intval( $w * 9 /16 ),
			'width'    => $w, 
			'autoplay' => true,
			'class'		 => $classes[ 'video' ] . ' wp-video-shortcode'
		);
		
		if( $has_image ){
			$video_args[ 'poster' ]  = $img;
		}
		
		$video_src = str_replace( "'video'", '"video"', wp_video_shortcode( $video_args ) ); 
		$video_src = preg_replace( '~\R~u', '', $video_src ); 	
	}
	
	$get_media = isset( $_GET[ 'media' ] ) ? $_GET[ 'media' ] : '';
	$get_media = empty( $get_media ) ? $media_type : $get_media;
	
	$media_output = '';
	if( $has_video && ( 'video' == $get_media || empty( $get_media ) ) ){
		// Show video
		$media_output = $video_src;
		if( $has_audio ){
			$media_output .= sprintf( '<p style="text-align: center"><a href="%s" class="%s">AUDIO</a></p>', 
			esc_url( add_query_arg( 'media', 'audio' ) ),
			$classes[ 'media_link' ] );
		}
	}elseif( $has_audio && ( 'audio' == $get_media || empty( $get_media ) ) ){
		// Show audio
		if( $has_image && empty( $media_type ) ){
			$media_output = $img_src;
		}
		$media_output .= $audio_src;
		if( $has_video ){
			$media_output .= sprintf( '<p style="text-align: center"><a href="%s" class="%s">VIDEO</a></p>', 
			esc_url( add_query_arg( 'media', 'video' ) ),
			$classes['media_link' ] );
		}
	}elseif( $has_image && ( 'image' == $get_media || empty( $get_media ) )){
		$media_output = $img_src;
	}
	
	$media_output = sprintf('
		<div class = "%s">
			%s
		</div>
	',
	$classes[ 'media' ],
	$media_output
	);

	return $media_output;
}





////////////////////
function ccx_shortcode_style(){
	$css = '
	video {
		position: relative;
		padding-bottom: 56.25%;
		padding-top: 20px;
		height: 0;
		overflow: hidden;
	}
	.video iframe,  
	.video object,  
	.video embed {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
	}';
	
}