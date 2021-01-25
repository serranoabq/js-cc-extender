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
 

/**
 What do we want the shortcodes to be?
 [ccx_sermon] =  Full sermon details
 [ccx_sermon_details] = Sermon details (title, speakers, book, topic, series, etc.)
 [ccx_sermon_media] = Semon media (image, video audio)
 [ccx_event] = Full event details
**/

/**
 * Add a metabox with the shortcodes available for the post
 *
 * @since 0.5
 */
function ccx_shortcode_metabox(){
	add_meta_box(
		'ccx_sermon_shortcode',
		__( 'Sermon Shortcodes', 'jsccx' ),
		'ccx_shortcode_metabox_render',
		'ctc_sermon',
		'normal',
		'high',
		array(
			'__block_editor_compatible_meta_box' => true,
		) 
	);
}
add_action( 'admin_init', 'ccx_shortcode_metabox' );

/**
 * Render the shortcode metabox
 *
 * @since 0.5
 */
function ccx_shortcode_metabox_render( $args ){
	
	echo sprintf('[ccx_sermon_details id="%s" hide_title]', $args->ID );
	
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
		'details'    			=> 'ccx-details',
		'sermon_date'     => 'ccx-sermon-date',
		'sermon_speaker'  => 'ccx-sermon-speaker',
		'sermon_series'   => 'ccx-sermon-series',
		'sermon_topic'    => 'ccx-sermon-topic',
		'audio-link' 			=> 'ccx-sermon-audio-link',
		'audio'      			=> 'ccx-sermon-audio',
		'video'      			=> 'ccx-sermon-video',
	);
	
	$classes = apply_filters( 'ccx_classes', $classes );
	
	return $classes;
}


/**
 * Sermon details shortcode handler
 *
 * @since 0.5
 * @return string Sermon details output
 */
function ccx_sermon_detail_shortcode( $attr ){
	
	extract( shortcode_atts( array(
		'id' 	=>  null,
		), $attr ) );
	
	// If no ID given, get the latest post
	if( ! $id ) {
		$m_post = get_posts("post_type=ctc_sermon&numberposts=1");
		$id = $m_post[0]->ID;
	}
	
	$hide_title = false;
	if( isset( $attr['0'] ) ){
		$hide_title = ( 'hide_title' == $attr[ '0' ] );
	}
	
	$data = ccx_get_CTC_data( 'ctc_sermon', $id );
	$classes = ccx_shortcode_classes();
	$name = sprintf( '<h2 class="%s">%s</h2>', $classes[ 'name' ], $data[ 'name' ] );
	
	ob_start();
	
	$output = sprintf(
		'<div class="%s">
			%s
			%s
		</div>
		',
		$classes[ 'container' ],
		$hide_title ? '' : $name,
		ccx_get_sermon_details( $data )
	);
	echo $output;
	return ob_get_clean();
}
add_shortcode( 'ccx_sermon_details', 'ccx_sermon_detail_shortcode' );

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
	//$name_src,
	$date_src,
	$speaker_src,
	$series_src,
	$topic_src,
	);
	$output = apply_filters( 'ccx_sermon_details', $output );
	
	return $output;
}


/*
function ccx_sermon_shortcode( $attr ){
	extract( shortcode_atts( array(
		'id' 	=>  ''
		), $attr ) );
	
	$data = ccx_shortcode_query( 'ctc_sermon', $id );
	
	// classes
	$classes = array(
		'container'  => 'ctcex-sermon-container',
		'media'      => 'ctcex-sermon-media',
		'details'    => 'ctcex-sermon-details',
		'name'       => 'ctcex-sermon-name',
		'date'       => 'ctcex-sermon-date',
		'speaker'    => 'ctcex-sermon-speaker',
		'series'     => 'ctcex-sermon-series',
		'topic'      => 'ctcex-sermon-topic',
		'audio-link' => 'ctcex-sermon-audio-link',
		'audio'      => 'ctcex-sermon-audio',
		'video'      => 'ctcex-sermon-video',
		'img'        => 'ctcex-sermon-img'
	);
	
	$output = ''; 
			
	$name = isset( $data[ 'name' ] ) ? $data[ 'name' ] : '';
	$permalink = isset( $data[ 'permalink' ] ) ? $data[ 'permalink' ] : '';
	
	// Get date
	$date_src = sprintf( '<div class="%s"><b> %s:</b> %s</div>', $classes[ 'date' ], __( 'Date', 'ctcex' ), get_the_date() );
	
	// Get speaker
	$speaker_src = isset( $data[ 'speakers' ] ) ? sprintf( '<div class="%s"><b>%s:</b> %s</div>', $classes[ 'speaker' ], __( 'Speaker', 'ctcex' ), $data[ 'speakers' ] ) : '';
	
	// Get series
	$series = isset( $data[ 'series' ] ) ? $data[ 'series' ] : '';
	$series_link = $series && isset( $data[ 'series_link' ] ) ? sprintf( '<a href="%s">%s</a>', $data[ 'series_link' ], $series ) : $series;
	$series_src = $series ?	sprintf( '<div class="%s"><b>%s:</b> %s</div>', $classes[ 'series' ],  __( 'Series', 'ctcex' ), $series_link ) : '';
	
	// Get topics
	// Topic name
	$topic_name = explode( '/', ctcex_get_option( 'ctc-sermon-topic' , __( 'Topic', 'ctcex') ) );
	$topic_name = ucfirst( array_pop(  $topic_name ) );
	$topic = isset( $data[ 'topic' ] ) ? $data[ 'topic' ] : '';
	$topic_link = $topic && isset( $data[ 'topic_link' ] ) ? sprintf( '<a href="%s">%s</a>', $data[ 'topic_link' ], $topic ) : $topic;
	$topic_src = $topic ? sprintf( '<div class="%s"><b>%s:</b> %s</div>', $classes[ 'topic' ], $topic_name, $topic_link ) : '';

	// Get audio link
	$audio = isset( $data[ 'audio' ] ) ? $data[ 'audio' ] : '';
	$audio_link_src = $audio ? sprintf( '<div class="%s"><b>%s:</b> <a href="%s">%s</a></div>', $classes[ 'audio-link' ], __( 'Audio', 'ctcex' ), $audio, __( 'Download audio', 'ctcex' ) ) : '';
	
	// Get audio display
	$audio_src = $audio ? sprintf( '<div class="%s">%s</div>', $classes[ 'audio' ], wp_audio_shortcode( array( 'src' => $audio ) ) ) : '';
	
	// Get video display
	$video = isset( $data[ 'video' ] ) ? $data[ 'video' ] : '';
	$video_iframe_class = strripos( $video, 'iframe' ) ? 'iframe-container' : '';
	$video_src = $video ? sprintf( '<div class="%s %s">%s</div>', $classes[ 'video' ], $video_iframe_class, $video_iframe_class ? $video : wp_video_shortcode( array( 'src' => $video ) ) ) : '';

	// Use the image as a placeholder for the video
	$img = isset( $data[ 'img' ] ) ? $data[ 'img' ] : '';
	$img_overlay_class = $video && $img ? 'ctcex-overlay' : '';
	$img_overlay_js = $img_overlay_class ? sprintf(
		'<div class="ctcex-overlay">
			<i class="' . ( $glyph === 'gi' ? 'genericon genericon-play' : 'fa fa-play' ) . '"></i>
		</div><script>
			jQuery(document).ready( function($) {
				$( ".%s" ).css( "position", "relative" );
				$( ".ctcex-overlay" ).css( "cursor", "pointer" );
				var vid_src = \'%s\';
				vid_src = vid_src.replace( "autoPlay=false", "autoPlay=true" );
				$( ".ctcex-overlay" ).click( function(){
					$( this ).hide();
					$( ".ctcex-sermon-img" ).fadeOut( 200, function() {
						$( this ).replaceWith( vid_src );
						$( ".%s").addClass( "video_loaded" );
					});
				} );
			})
		</script>', 
		$classes[ 'media' ],
		$video_src, 
		$classes[ 'media' ]
		) : '' ;
		
	// Get image
	$img_src = $img ? sprintf( '%s<img class="%s" src="%s" alt="%s" width="960"/>', $img_overlay_js, $classes[ 'img' ], $img, $name ) : '';
	$video_src = $img_overlay_class ? $img_src : $video_src;
	
	$img_video_output = $video_src ? $video_src : $img_src . $audio_src;
	
	// Prepare output
	$output = sprintf(
		'<div class="%s">
			<div class="%s">%s</div>
			<div class="%s">
				<h3><a href="%s">%s</a></h3>
				%s
				%s
				%s
				%s
				%s
			</div>
		', 
		$classes[ 'container' ],
		$classes[ 'media' ],
		$img_video_output,
		$classes[ 'details' ],
		$permalink,
		$name,
		$date_src,
		$speaker_src,
		$series_src,
		$topic_src,
		$audio_link_src
	);
	


	return $output;
	
}


function ccx_sermon_media_shortcode( $attr ){
	extract( shortcode_atts( array(
		'id' 	=>  ''
		), $attr ) );
	
	
}
*/


/*
function ccx_sermon_media( $data, $classes ){
	
	// Image
	$img = isset( $data[ 'img_id' ] ) ? wp_get_attachment_image_url( $data[ 'img_id' ], 'full' ) : '';
	
	$img_src = $img ? sprintf( '%s<img class="%s" src="%s" alt="%s" width="960"/>', $img_overlay_js, $classes[ 'img' ], $img, $data[ 'name' ] ) : '';
			
	// Get audio link
	$audio = isset( $data[ 'audio' ] ) ? $data[ 'audio' ] : '';
	$audio_link_src = $audio ? sprintf( '<div class="%s"><b>%s:</b> <a href="%s">%s</a></div>', $classes[ 'audio-link' ], __( 'Audio', 'jsccx' ), $audio, __( 'Download audio', 'jsccx' ) ) : '';
	
	// Get audio display
	$audio_src = $audio ? sprintf( '<div class="%s">%s</div>', $classes[ 'audio' ], wp_audio_shortcode( array( 'src' => $audio ) ) ) : '';
	
	// Get video display
	if( isset( $data[ 'video' ] ) ){
		$video = $data[ 'video' ];
		$video_attr = array( 'src' => $video );
		if( $img ) $video_attr[ 'poster' ] = $img_src;
		$video_class = 
	}
	$video = isset( $data[ 'video' ] ) ? $data[ 'video' ] : '';
	
	$video_attr = $video ? array( 'src' => $video ) : ;
	
	
	$video_iframe_class = strripos( $video, 'iframe' ) ? 'iframe-container' : '';
	$video_src = $video ? sprintf( '<div class="%s %s">%s</div>', $classes[ 'video' ], $video_iframe_class, $video_iframe_class ? $video : wp_video_shortcode( array( 'src' => $video ) ) ) : '';
	
	$output = '
	<div class="ccx-sermon-media">' . 
		
	'</div>';
		
	
}
*/



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