<?php
/**
 * Post images
 *
 * @package    	JSCCX
 * @author			Justin Serrano
 * @copyright		Copyright (c) 2020, Justin Serrano
 * @license    	http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @since      	0.2
 */

/**
 * Add default image 
 * 
 * Add default image during post save if post doesn't a featured image 
 *
 * @since 0.2
 * @param $post_id Post ID
 */
function ccx_save_image( $post_id ){
	
	// Do nothing if the post already has an image
	$thumbnail_id = get_post_thumbnail_id( $post_id );
	if( $thumbnail_id ) return;
	
	// Use the post type to get the right setting 
	$ctc_settings = get_option( 'ctc_settings' );
	$post_type = str_replace( 'ctc_', '', get_post_type( $post_id ) );
	$new_image = $ctc_settings[ 'default_' . $post_type . '_image' ];
	
	// Get image id
	$new_image_id = ccx_get_attachment_id( $new_image );
	if( ! $new_image_id ) return;	
	
	// Add image to post
	set_post_thumbnail( $post_id, $new_image_id );
}	
add_action( 'save_post_ctc_sermon', 'ccx_save_image', 13);
add_action( 'save_post_ctc_event', 'ccx_save_image', 13);
add_action( 'save_post_ctc_location', 'ccx_save_image', 13);
add_action( 'save_post_ctc_person', 'ccx_save_image', 13);

/**
 * Get attachment id from url 
 * 
 * @since 0.2
 * @param string $url URL of attachment
 */
function ccx_get_attachment_id( $url ) {
	$attachment_id = 0;
	$dir = wp_upload_dir();

	// Is URL in uploads directory?
	if ( false !== strpos( $url, $dir['baseurl'] . '/' ) ) { 			
		$file = basename( $url );

		$query_args = array(
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'fields'      => 'ids',
			'meta_query'  => array(
				array(
					'value'   => $file,
					'compare' => 'LIKE',
					'key'     => '_wp_attachment_metadata',
				),
			)
		);

		$query = new WP_Query( $query_args );
		if ( $query->have_posts() ) {
			foreach ( $query->posts as $post_id ) {
				$meta = wp_get_attachment_metadata( $post_id );
				$original_file       = basename( $meta['file'] );
				$cropped_image_files = wp_list_pluck( $meta['sizes'], 'file' );

				if ( $original_file === $file || in_array( $file, $cropped_image_files ) ) {
					$attachment_id = $post_id;
					break;
				}

			}
		}
	}
	return $attachment_id;
}

	 