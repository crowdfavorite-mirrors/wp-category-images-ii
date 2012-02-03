<div class="wrap">
	<h2><?php _e( "Category Images II" ); ?></h2>
	<form method="post" action="">
		<?php wp_nonce_field( 'ciii_options', '_ciii_nonce' ); ?>
		<table class='form-table'>
			<tr valign="top">
				<th scope="row">
					<label for="ciii_max_side"><?php _e( "Maximum side dimension of thumbnail", 'category-images-ii' ); ?></label>
				</th>
				<td>
					<input name="ciii_max_side" type="text" id="ciii_max_side" value="<?php echo $max_side; ?>" size="6" /><br />
					<span class="field-hint"><?php _e( "(Max height or width of the thumbnail image. Created when the image is uploaded, so you'll need to re-upload any previous images.)", 'category-images-ii' ); ?></span>
				</td>
			<tr>
			<tr>
				<th scope="row">Taxonomies to allow images for</th>
				<td>
					<fieldset><legend class="screen-reader-text"><span>Taxonomies</span></legend>
					<?php foreach ( $taxonomies as $tax ) : ?>
						<label><input type="checkbox" name="taxonomies[]" value="<?php echo esc_attr( $tax->name ); ?>" <?php checked( in_array( $tax->name, $selected_taxonomies ) ); ?> /> <span><?php echo esc_html( $tax->labels->name ); ?></span></label><br>
					<?php endforeach; ?>
					</fieldset>
				</td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="Submit" value="<?php _e( 'Save Changes', 'category-images-ii' ); ?>" /></p>
	</form>
</div>