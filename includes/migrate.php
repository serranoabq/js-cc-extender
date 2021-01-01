<?php
/**
 * Migration Functions
 *
 * @package    	JSCCX
 * @author			Justin Serrano
 * @copyright		Copyright (c) 2020, Justin Serrano
 * @license    	http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @since      	0.4
 */ 

// What needs to migrate?
// Sermon images
//

/**
 * Add image to sermon series table entry 
 *
 * @since 0.4
 */
function ccx_migrate(){
	$ctc_settings = get_option( 'ctc_settings' );
	
	if( $ctc_settings[ 'ccx_migrated' ] ) return;
	
	ccx_series_migrate();
	
	ccx_sermon_migrate( $ctc_settings[ 'ccx_migrate_blanks' ] );
	
	
	$ctc_settings[ 'ccx_migrated' ] = 1;
	
	update_option( 'ctc_settings', $ctc_settings );
	
}
add_action( 'init', 'ccx_migrate', 12);


/**
 * Add image to sermon previous sermons 
 *
 * @since 0.4
 */
function ccx_sermon_migrate( $migrate_blanks ){
	
	$query = array(
		'post_type' => 'ctc_sermon'
	);
	
	$m_posts = new WP_Query( $query );
	if( $m_posts->have_posts()){
		while( $m_posts->have_posts() ){
			
			$m_posts->the_post();
			$post_id = get_the_ID();
			
			ccx_save_image( $post_id, $migrate_blanks );
			
		}
	}
	
	wp_reset_query();

}


/**
 * Migrate the sermon series image 
 *
 * @since 0.4
 */
function ccx_series_migrate(){
	
	$terms = get_terms( 'ctc_sermon_series' );
	
	foreach( $terms as $term ){
		$term_id = $term -> term_id;
		
		$ctcex_img = get_term_meta( $term_id, 'ctc_sermon_series_image', true );
		
		if( $ctcex_img ){
			
			$img_id = ccx_get_attachment_id( $ctcex_img );
			
			if( $img_id ){
				
				update_term_meta( $term_id, 'ccx_sermon_series_image_id', $img_id );
				delete_term_meta( $term_id, 'ctc_sermon_series_image' );
				
			}
		}
 	}
	
}