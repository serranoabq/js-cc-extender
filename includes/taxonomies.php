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
add_filter( 'ctc_taxonomy_sermon_tag_args', 'ccx_change_taxonomy_slug' );
add_filter( 'ctc_taxonomy_sermon_book_args', 'ccx_change_taxonomy_slug' );
add_filter( 'ctc_taxonomy_sermon_topic_args', 'ccx_change_taxonomy_slug' );
add_filter( 'ctc_taxonomy_event_category_args', 'ccx_change_taxonomy_slug' );
add_filter( 'ctc_taxonomy_person_group_args', 'ccx_change_taxonomy_slug' );

/**********************************
 * SERMON SERIES IMAGE SETTINGS
 **********************************/

/**
 * Form to add/update/remove sermon series image
 *
 * @since 0.35
 */
function ccx_category_form( $value = null ){
	$add_replace_string = $value ? __( 'Replace Image', 'jsccx' ) : __( 'Add Image', 'jsccx' );
	$hidden = $value ? '' : 'hidden'; 
?>

	<tr class="form-field term-group term-featured-image">
		<th>
			<label for="category-image-id"><?php _e('Image', 'jsccx'); ?></label>
		</th>
		<td>
			<input type="hidden" id="ccx_sermon_series_image_id" name="ccx_sermon_series_image_id" class="custom_media_url" value=" <?php echo $value; ?>">
			<div id="ccx-sermon-image-wrapper">
				<?php if( $value ) echo wp_get_attachment_image( $value, array( '200', '200' ) ); ?>
			</div>
			<p>
				<input type="button" class="button button-secondary ccx_tax_media_add" id="ccx_tax_media_add" name="ccx_tax_media_add" value="<?php echo $add_replace_string; ?>" />
				<input type="button" class="button button-secondary ccx_tax_media_remove <?php echo $hidden; ?>" id="ccx_tax_media_remove" name="ccx_tax_media_remove" value="<?php _e( 'Remove Image', 'jsccx' ); ?>" />
			</p>
		</td>
	</tr>

<?php	
}


/**
 * Hook to display sermon series image form element on 
 * the add category screen
 *
 * @since 0.35
 */
function ccx_add_category_image( $taxonomy ){
	ccx_category_form();
}
add_action( 'ctc_sermon_series_add_form_fields',  'ccx_add_category_image' );

/**
 * Hook to display sermon series image form element on the 
 * edit category screen
 *
 * @since 0.35
 */
function ccx_edit_category_image( $term, $taxonomy ){
	$value = get_term_meta( $term -> term_id, 'ccx_sermon_series_image_id', true );
	ccx_category_form( $value );
}
add_action( 'ctc_sermon_series_edit_form_fields',  'ccx_edit_category_image', 10, 2 );

/**
 * Save the image to the database
 *
 * @since 0.35
 */
function ccx_save_category_image( $term_id ){
	if( ! isset( $_POST['ccx_sermon_series_image_id'] ) ) return;
	$image =  $_POST['ccx_sermon_series_image_id'];
	
	if( '' != $image ){
		update_term_meta( $term_id, 'ccx_sermon_series_image_id', $image );
	} else {
		delete_term_meta( $term_id, 'ccx_sermon_series_image_id' );
	}
}
add_action( 'created_ctc_sermon_series', 'ccx_save_category_image' );
add_action( 'edited_ctc_sermon_series', 'ccx_save_category_image' );


/**
 * Add appropriate scripting
 *
 * @since 0.35
 */
function ccx_category_image_script(){
?>
	<script>
	jQuery( document ).ready( function( $ ){ 
		// Uploading files
		var media_frame;
		
		$('body').on( 'click', '#ccx_tax_media_add', function( event ) {
			event.preventDefault();
			
			if( media_frame ){
				media_frame.open();
				return;
			}
			
			media_frame = wp.media({
				title: '<?php _e( 'Select Series Image', 'jsccx' ); ?>',
				button: { text: '<?php _e( 'Select Image', 'jsccx' ); ?>' },
				library: { type: 'image' },
				multiple: false
			});
			
			// Handle media selection
			media_frame.on( 'select', function() {
				var attachment = media_frame.state().get('selection').first().toJSON();
				
				// Add values to setting
				$('#ccx_sermon_series_image_id').val( attachment.id );
				$('#ccx-sermon-image-wrapper').html( '<img src="'+attachment.url+'" alt="" style="max-width:200px; max-height:200px;"/>' );

				// Replace buttons
				$('#ccx_tax_media_remove' ).removeClass( 'hidden' );
				$('#ccx_tax_media_add' ).val( '<?php _e( 'Replace Image', 'jsccx' ); ?>' );
				
			});
			
			// Finally, open the modal.
			media_frame.open();
		});
		
		// Clear image
		$('#ccx_tax_media_remove').click( function( event ){
			event.preventDefault();
			
			// Remove setting and image display
			$('#ccx_sermon_series_image_id').val( '' );
			$('#ccx-sermon-image-wrapper').html( '' );
			
			// Replace buttons
			$('#ccx_tax_media_add' ).val( '<?php _e( 'Add Image', 'jsccx' ); ?>' );
			$('#ccx_tax_media_remove' ).addClass( 'hidden' );
			
		});
		
		// Update the form elements upon adding category
		$(document).ajaxSuccess( function( e, request, settings ){
			var data = settings.data.split('&');
			
			if( $.inArray( 'action=add-tag', data ) !== -1 && $.inArray( 'screen=edit-ctc_sermon_series', data ) !== -1 && $.inArray( 'taxonomy=ctc_sermon_series', data ) !== -1 ){
				// Remove setting and image display
				$('#ccx_sermon_series_image_id').val( '' );
				$('#ccx-sermon-image-wrapper').html( '' );
				
				// Replace buttons
				$('#ccx_tax_media_add' ).val( '<?php _e( 'Add Image', 'jsccx' ); ?>' );
				$('#ccx_tax_media_remove' ).addClass( 'hidden' );
			}
			
		});

	});
	</script>
<?php
}
add_action( 'admin_footer', 'ccx_category_image_script' );


/**
 * Add media library js
 *
 * @since 0.35
 */
function ccx_load_media(){
	wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'ccx_load_media' );


/**
 * Add image column for sermon series table
 *
 * @since 0.36
 */
function ccx_add_image_column( $columns ){
	$col = 1; // position of new column
	
	$columns = array_slice( $columns, 0, $col, true ) + array('image' => __( 'Image', 'jsccx' ) ) + array_slice( $columns, $col, count( $columns) - 1, true );
	
	return $columns;
}
add_filter('manage_edit-ctc_sermon_series_columns' , 'ccx_add_image_column');


/**
 * Add image to sermon series table entry 
 *
 * @since 0.36
 */
function ccx_add_column_image( $content, $column_name, $term_id ){
		$value = get_term_meta( $term_id, 'ccx_sermon_series_image_id', true );
		
		if( 'image' != $column_name ) return $content;
		if( $value ){
			$content = wp_get_attachment_image( $value, array('60','60' ) );
    }
	return $content;
}
add_filter( 'manage_ctc_sermon_series_custom_column', 'ccx_add_column_image', 10, 3 );
