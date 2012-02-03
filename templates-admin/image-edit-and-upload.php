<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<?php wp_nonce_field( 'category_images_ii', '_ciii_nonce' ); ?>
<table class="form-table" id="category_images_ii">
	<?php if ( $term_image ) { ?>
		<tr class="form-field current-category-image">
			<th scope="row" valign="top"><?php _e( 'Current Category Image', 'category-images-ii' ) ?></th>
			<td>
				<a href="<?php echo esc_attr( $term_image ); ?>" target="_blank"><img src="<?php echo esc_attr( $term_image_thumb ); ?>" alt="<?php _e( 'current category image (select to view larger size)', 'category-images-iii' ) ?>" /></a>
				<p><?php submit_button( __( "Delete This Image", 'category-images-ii' ), 'delete', 'ciii_delete' ); ?></p>
			</td>
		</tr>
	<?php } ?>
	<tr class="form-field upload-category-image">
		<th scope="row" valign="top"><label for="category_image_ii"><?php _e( 'Upload a Category Image', 'category-images-ii' ) ?></label></th>
		<td>
			<input name="category_images_ii" id="category_images_ii" type="file" /><br />
			<span class="description"><?php printf( __( "Will replace any previous image. The maximum uploadable file size is %s.", 'category-images-ii' ), $max_upload_size ); ?></span>
		</td>
	</tr>
</table>
