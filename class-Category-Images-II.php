<?php

/*  Copyright 2011 Simon Wheatley

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

require_once ( dirname (__FILE__) . '/class-Plugin.php' );

// Let me know if these seem useful: http://www.simonwheatley.co.uk/contact/
// SWTODO: Ability to specify that the image for the default category is never shown?

/**
 * A Class to allow authors to set default categories for their new posts.
 *
 * Extends John Godley's WordPress Plugin Class, which adds all sorts of functionality
 * like templating which can be overriden by the theme, etc.
 * 
 * @package default
 * @author Simon Wheatley
 **/
class CategoryImagesII extends CategoryImagesII_Plugin {

	/**
	 * Flag indicating whether this is the edit screen.
	 *
	 * @var boolean
	 **/
	protected $edit_screen;

	/**
	 * Arbitrary version number for benefit of rewrite rules, 
	 * data structures, etc.
	 *
	 * @var int
	 **/
	protected $version;

	/**
	 * Constructor for this class. 
	 *
	 * @return void
	 * @author Simon Wheatley
	 **/
	function __construct() 
	{
		$this->setup( 'ciii' );
		$this->edit_screen = false;
		$this->version = 1;
		if ( is_admin() ) {
			// Amend the form element
			$this->add_action( 'load-categories.php', 'start_buffer' );
			$this->add_action( 'load-edit-tags.php', 'start_buffer' );
			// Form fields
			$this->add_action( 'edit_category_form', 'edit_term_form' );
			$this->add_action( 'edit_tag_form', 'edit_term_form' );
			$this->add_action( 'add_tag_form', 'edit_term_form' );
			// CSS for categories.php
			$this->add_action( 'admin_print_styles-categories.php', 'edit_categories_styles' );
			// Process any uploads, etc
			$this->add_action( 'edited_term', null, null, 2 );
			// Hooks involved with setting the categories on a new post
			$this->add_action( 'load-edit-tags.php', 'enqueue' );
			// Options pages 'n stuff
			$this->add_action( 'admin_menu' );
			// Process form submissions from options page
			$this->add_action( 'load-settings_page_category_images_ii', 'options_form_submission' );
			$this->do_updates();
		}
	}
	
	function activate()
	{
		// Set a reasonable initial max side value
		$this->save_max_side( 50 );
	}
	
	/* HOOKS */
	
	/**
	 * Start the Output Buffer so we can alter the form element.
	 *
	 * @author Viper007Bond
	 **/
	public function start_buffer() 
	{
		if ( empty( $_GET['page'] ) ) 
			ob_start( array( & $this, 'modify_buffer' ) );
		$this->edit_screen = true;
	}
	
	public function modify_buffer( $buffer )
	{
		// Amend the form
		$buffer = str_replace( 
			array( '<form name="editcat"', '<form name="edittag"' ),
			array( '<form name="editcat" enctype="multipart/form-data"', '<form name="edittag" enctype="multipart/form-data"' ), 
			$buffer );
		return $buffer;
	}

	public function edit_term_form( $term )
	{
		$screen = get_current_screen();
		if ( is_object( $term ) && ! in_array( $screen->taxonomy, $this->get_selected_taxonomies() ) )
			return;
		$taxonomy = get_taxonomy( $screen->taxonomy );
		
		$action = @ $_GET[ 'action' ];
		if ( ! $action ) {
			echo '<p id="category_images_ii">';
			printf( __( "You cannot add an image here, only after you have created this %s.", 'category-images-ii' ), $taxonomy->labels->singular_name );
			echo '</p>';
			return;
		}
		$images_base_url = $this->term_images_base_url();
		$vars = array();
		$vars[ 'max_upload_size' ] = $this->max_file_upload();
		$vars[ 'term_image' ] = $this->get_image_url( $term->term_id, 'original' );
		$vars[ 'term_image_thumb' ] = $this->get_image_url( $term->term_id, 'thumb' );
		$this->render_admin( 'image-edit-and-upload.php', $vars );
	}
	
	public function edited_term( $term_id, $tt_id )
	{
		// SECURE-A-TEA: If the nonce isn't present, or incorrect, then we don't have anything to do
		if ( ! wp_verify_nonce( $_REQUEST[ '_ciii_nonce' ], 'category_images_ii') ) 
			return;

		// Deal with any file deletion *before* any upload
		$this->process_deletion();
		// Deal with any file upload
		$this->process_upload();
	}
	
	public function edit_categories_styles()
	{
		$vars = array();
		$this->render_admin( 'edit-categories-styles.php', $vars );
	}
	
	public function enqueue()
	{
		wp_enqueue_script( 'jquery' ); // Just to be sure
		wp_enqueue_script( 'ciii', $this->url( '/js/manage-categories.js' ) );
		wp_enqueue_style( 'ciii', $this->url( '/css/admin.css' ) );
	}
	
	public function admin_menu() {
		// ( $page_title, $menu_title, $access_level, $file, $function = '' ) {
		add_options_page( __( 'Category Images II', 'category-images-ii' ), __( 'Category Images II', 'category-images-ii' ), 'manage_options', 'category_images_ii', array( $this, 'options_page' ) );
	}
	
	public function options_page()
	{
		$vars = array();
		$vars[ 'max_side' ] = $this->get_max_side();
		$vars[ 'taxonomies' ] = get_taxonomies( array( 'show_ui' => true ), 'objects' );
		$vars[ 'selected_taxonomies' ] = $this->get_selected_taxonomies();
		$this->render_admin( 'options-page.php', $vars );
	}
	
	public function options_form_submission()
	{
		if ( ! @ $_REQUEST[ '_ciii_nonce' ] )
			return;
		// SECURE-A-TEA: If the nonce isn't present, or incorrect, then we don't have anything to do
		if ( ! wp_verify_nonce( $_REQUEST[ '_ciii_nonce' ], 'ciii_options') ) 
			return;
		$max_side = (int) $_POST[ 'ciii_max_side' ];
		$this->save_max_side( $max_side );
		$taxonomies = (array) $_POST[ 'taxonomies' ];
		foreach ( $taxonomies as & $taxonomy )
			$taxonomy = sanitize_title( $taxonomy );
		$this->save_selected_taxonomies( $taxonomies );
	}
	
	public function display_images( $taxonomy, $term_ids, $args )
	{
		$link_images = $args[ 'link_images' ];
		$size = $args[ 'size' ];
		$show_description = $args[ 'show_description' ];
		
		$terms = array();
		foreach ( $term_ids AS $term_id ) {
			$term = array();
			// If there's no image set, then skip this one
			if ( ! $term[ 'thumb' ] = $this->get_image_url( $term_id, 'thumb' ) ) 
				continue;
			$term[ 'id' ] = (int) $term_id;
			$term[ 'name' ] = $this->get_term_name( $taxonomy, $term_id );
			$term[ 'image' ] = $this->get_image_url( $term_id, $size );
			$term[ 'thumbnail' ] = $this->get_image_url( $term_id, 'thumb' );
			$terms[] = $term;
			unset( $term );
		}
		// Did we end up with *any* categories with images?
		if ( empty( $terms ) ) 
			return;
		// Otherwise, render the template
		$vars = array();
		$vars[ 'taxonomy' ] = $taxonomy;
		$vars[ 'terms' ] = $terms;
		$vars[ 'link_images' ] = $link_images;
		$vars[ 'show_description' ] = $show_description;
 		if ( $this->locate_template( 'category-images.php', false ) )
			return $this->capture( 'category-images.php', $vars );
		else 
			return $this->capture( 'term-images.php', $vars );
	}
	
	/* UTILITIES */
	
	protected function process_deletion()
	{
		// Check that the delete button was actually pressed.
		if ( ! $this->was_button_pressed( 'ciii_delete' ) ) 
			return;

		// Delete button was pressed, and we verified the Nonce earlier. Kindly proceed.
		
		$term_id = $this->admin_term_id();
		$term_image_original = $this->get_image_filepath( $term_id, 'original' );
		$term_image_thumb = $this->get_image_filepath( $term_id, 'thumb' );
		unlink( $term_image_original );
		unlink( $term_image_thumb );
		$this->delete_data( $term_id );
		$this->set_admin_notice( __( 'Image deleted.', 'category-image-ii' ) );
	}
	
	protected function process_upload()
	{
		// Have we actually got an upload?
		if ( $_FILES[ 'category_images_ii' ][ 'error' ] == 4 )
			return;

		// Is it an image?
		$img_info = getimagesize( $_FILES[ 'category_images_ii' ][ 'tmp_name' ] );
		if ( ! $img_info ) {
			$this->set_admin_error( __( "Sorry, something went wrong with the image upload. Please try again, or contact the website administrator.", 'category-images-ii' ) );
			return;
		}
		if ( ! $this->valid_uploaded_file_type( $img_info ) ) {
			$this->set_admin_error( __( "Sorry, you cannot upload an image file of this type. Allowed image file types are: GIF, JPEG, or PNG.", 'category-images-ii' ) );
			return;
		}

		// Find the directory we want the file to live in
		$base_dir = $this->term_images_base_dir();
		// Make a filename
		$term_id = $this->admin_term_id();
		$ext = $this->preferred_image_extension( $img_info );
		$original_name = "$term_id.original.$ext";
		// Put it all together
		$original_file = $base_dir . '/' . $original_name;
		// Safely move the uploaded file (this func will return false if it's not a properly uploaded file, e.g. could be hack attack!)
		if ( ! move_uploaded_file( $_FILES[ 'category_images_ii' ][ 'tmp_name' ], $original_file ) ) 
			wp_die( __( "Something went wrong. Could not move the uploaded file.", 'category-images-ii' ) );
		// Resize and save thumb
		$thumb_name = "$term_id.thumb.$ext";
		$thumb_file = $base_dir . "/$term_id.thumb.$ext";
		$this->save_thumb( $original_file, $thumb_file );
		// Save this info in the options
		$data = array( 
			'original' => $original_name,
			'thumb' => $thumb_name,
		);
		$this->save_data( $term_id, $data );
		$this->set_admin_notice( __( 'Image uploaded.', 'category-image-ii' ) );
	}
	
	protected function save_thumb( $original_file, $thumb_file )
	{
		// Scale the image.
		list( $w, $h, $format ) = getimagesize( $original_file );
		$max_side = $this->get_max_side();
		$xratio = $max_side / $w;
		$yratio = $max_side / $h;
		$ratio = min( $xratio, $yratio );
		$targetw = (int) $w * $ratio;
		$targeth = (int) $h * $ratio;

		$src_gd = $this->image_create_from_file( $original_file );
		assert( $src_gd );
		$target_gd = imagecreatetruecolor( $targetw, $targeth );
		imagecopyresampled ( $target_gd, $src_gd, 0, 0, 0, 0, $targetw, $targeth, $w, $h );
		// create the initial copy from the original file
		// also overwrite the filename (in case the extension isn't accurate)
		$success = false;
		if ( $format == IMAGETYPE_GIF ) {
			$success = imagegif( $target_gd, $thumb_file );
		} elseif ( $format == IMAGETYPE_JPEG ) {
			$success = imagejpeg( $target_gd, $thumb_file, 90 );
		} elseif ( $format == IMAGETYPE_PNG ) {
			$success = imagepng( $target_gd, $thumb_file );
		} else {
			wp_die( __( 'Unknown image type. Please upload a JPEG, GIF or PNG.', 'category-images-ii' ) );
		}
		if ( ! $success )
			wp_die( __( 'Image resizing failed.', 'category-images-ii' ) );
	}
	
	protected function delete_category_image_files( $term_id )
	{
		// Find the directory the files live in
		$base_dir = $this->term_images_base_dir();
		// Make the full filepaths
		$category_images = $this->get_category_image_names( $term_id );
		$original_file = $base_dir . "/" . $category_images[ 'original' ];
		$thumb_file = $base_dir . "/" . $category_images[ 'thumb' ];
		// Unlink the files
		unlink( $original_file );
		unlink( $thumb_file );
	}
	
	// The following was lifted from:
	// http://uk.php.net/manual/en/ref.image.php
	// With minor mods: ON error now returns false.
	// No longer accepts xbms (silly format)
	protected function image_create_from_file( $filename )
	{
		static $image_creators;

		if (!isset($image_creators)) {
			$image_creators = array(
				1  => "imagecreatefromgif",
				2  => "imagecreatefromjpeg",
				3  => "imagecreatefrompng"
			);
		}

		list( $w, $h, $file_type ) = getimagesize($filename);
		if ( isset( $image_creators[$file_type] ) ) {
			$image_creator = $image_creators[ $file_type ];
			if ( function_exists( $image_creator ) ) {
				// Set artificially high because GD uses uncompressed images in memory
				@ini_set('memory_limit', '256M');
				return $image_creator( $filename );
			}
		}

		// Changed to return false on error
		return false;
	}

	protected function term_images_base_dir()
	{
		// Where should the dir be? Get the base WP uploads dir
		$wp_upload_dir = wp_upload_dir();
		$base_dir = $wp_upload_dir[ 'basedir' ];
		// Append our subdir
		$dir = $base_dir . '/category-images-ii';
		// Does the dir exist? (If not, then make it)
		if ( ! file_exists( $dir ) )
			mkdir( $dir );
		// Now return it
		return $dir;
	}

	protected function term_images_base_url()
	{
		// Where should the dir be? Get the base WP uploads dir
		$wp_upload_dir = wp_upload_dir();
		$base_url = $wp_upload_dir[ 'baseurl' ];
		// Append our subdir
		$url = $base_url . '/category-images-ii';
		return $url;
	}
	
	protected function max_file_upload()
	{
		$upload_max_filesize = ini_get( 'upload_max_filesize' );
		$post_max_size = ini_get( 'post_max_size' );
		return min( $post_max_size, $upload_max_filesize );
	}

	protected function admin_term_id()
	{
		$screen = get_current_screen();
		$term_id = @ $_REQUEST[ 'tag_ID' ];
		if ( ! $term_id ) 
			return false;
		return (int) $term_id;
	}
	
	// Method will only with within the admin cat editing interface
	protected function get_term_name( $taxonomy, $term_id = false )
	{
		if ( $term_id === false )
			$term_id = $this->admin_term_id();
		if ( $term_id === false )
			return false;
		$term = get_term_by( 'id', $term_id, $taxonomy );
		return $term->name;
	}
	
	protected function valid_uploaded_file_type( $img_info )
	{
		return (bool) $this->preferred_image_extension( $img_info );
	}

	protected function preferred_image_extension( $img_info )
	{
		switch( $img_info[ 2 ] ) {
			case IMAGETYPE_GIF:
				return 'gif';
			case IMAGETYPE_JPEG:
				return 'jpg';
			case IMAGETYPE_PNG:
				return 'png';
		}
		return false;
	}
	
	/**
	 * Returns the filename for an image, used by the methods to
	 * get the filepath and URL.
	 *
	 * @param int $term_id The term ID of the image
	 * @param string $type Either 'original' or 'thumb' 
	 * @return void
	 * @author Simon Wheatley
	 **/
	protected function get_image_filename( $term_id, $type ) {
		if ( ! $data = $this->get_data( $term_id ) )
			return false;
		if ( ! isset( $data[ $type ] ) )
			return false;
		return $data[ $type ];
	}

	/**
	 * Returns the URL for an image file.
	 *
	 * @param int $term_id The ID of the term we want an image for 
	 * @param string $type The type of image
	 * @return string|bool A URL on success, false on failure
	 * @author Simon Wheatley
	 **/
	protected function get_image_url( $term_id, $type ) {
		if ( ! $filename = $this->get_image_filename( $term_id, $type ) )
			return false;
		$base_url = $this->term_images_base_url();
		return "$base_url/$filename";
	}

	/**
	 * Returns the filepath for an image file.
	 *
	 * @param int $term_id The ID of the term we want an image for 
	 * @param string $type The type of image
	 * @return string|bool A filesystem path on success, false on failure
	 * @author Simon Wheatley
	 **/
	protected function get_image_filepath( $term_id, $type ) {
		if ( ! $filename = $this->get_image_filename( $term_id, $type ) )
			return false;
		$base_dir = $this->term_images_base_dir();
		return "$base_dir/$filename";
	}
	
	/**
	 * Saves a data file named as per the term ID.
	 *
	 * @param int $term_id The term ID to save the data file for 
	 * @param array $data The data to save
	 * @return void
	 * @author Simon Wheatley
	 **/
	protected function save_data( $term_id, $data ) {
		$base_dir = $this->term_images_base_dir();
		$file_path = "$base_dir/$term_id.dat";
		$serialized = serialize( $data );
		file_put_contents( $file_path, $serialized );
		wp_cache_set( $term_id, $data, 'ciii' );
	}
	
	/**
	 * Saves a data file named as per the term ID.
	 *
	 * @param int $term_id The term ID to save the data file for 
	 * @param array $data The data to save
	 * @return bool|array Array of filenames on success, false on failure
	 * @author Simon Wheatley
	 **/
	protected function get_data( $term_id ) {
		if ( $data = wp_cache_get( $term_id, 'ciii' ) ) {
			return $data;
		}
		$base_dir = $this->term_images_base_dir();
		$file_path = "$base_dir/$term_id.dat";
		if ( ! file_exists( $file_path ) )
			return false;
		$serialized = file_get_contents( $file_path );
		$data = unserialize( $serialized );
		wp_cache_set( $term_id, $data, 'ciii' );
		return $data;
	}
	
	/**
	 * Deletes a data file named as per the term ID. Also deletes the cache.
	 *
	 * @param int $term_id The term ID to save the data file for 
	 * @return void
	 * @author Simon Wheatley
	 **/
	protected function delete_data( $term_id ) {
		$base_dir = $this->term_images_base_dir();
		$file_path = "$base_dir/$term_id.dat";
		unlink( $file_path );
		wp_cache_delete( $term_id, 'ciii' );
	}
	
	/**
	 * Removes a data file named as per the term ID.
	 *
	 * @param int $term_id The term ID to remove the data file for 
	 * @return void
	 * @author Simon Wheatley
	 **/
	protected function remove_files( $term_id ) {
		$base_dir = $this->term_images_base_dir();
		$file_path = "$base_dir/$term_id.dat";
		unlink( $file_path );
		$original = $this->get_image_file_path( $term_id, 'original' );
		unlink( $original );
		$thumb = $this->get_image_file_path( $term_id, 'original' );
		unlink( $thumb );
	}
	
	protected function get_max_side()
	{
		$option = get_option( 'ciii_max_side' );
		if ( ! $option )
			$option = 100; // Default size
		return $option;
	}
	
	protected function save_max_side( $max_side )
	{
		delete_option( 'ciii_max_side' );
		return update_option( 'ciii_max_side', $max_side );
	}
	
	protected function get_selected_taxonomies()
	{
		$option = get_option( 'ciii_taxonomies', array( '1' => '1', 'taxonomies' => array( 'category' ) ) );
		return (array) $option[ 'taxonomies' ];
	}
	
	protected function save_selected_taxonomies( $taxonomies )
	{
		$option = array( '1' => '1', 'taxonomies' => $taxonomies );
		delete_option( 'ciii_taxonomies' );
		return update_option( 'ciii_taxonomies', $option );
	}
	
	protected function was_button_pressed( $name, $post_only = false )
 	{
		if ( $post_only ) {
			$test_array = & $_POST;
		} else {
			$test_array = & $_REQUEST;
		}
		if ( @ $test_array[ $name ] != '' ) {
			return true;
		}
		if ( @ $test_array[$name . '_x'] != '' ) {
			return true;
		}
		return false;
	 }

	/**
	 * Updates various things:
	 * * Deletes unneeded option
	 *
	 * @return void
	 * @author Simon Wheatley
	 **/
	protected function do_updates() {
		$version = (int) get_option( $this->name . '-version', 0 );
		if ( $version < 1 ) {
			delete_option( 'ciii_image_names' );
		}
		if ( $version < 2 ) {
			$base_dir = $this->term_images_base_dir();
			if ( $files = glob( "$base_dir/*original*" ) ) {
				foreach ( $files as $file ) {
					$bits = explode( '/', $file );
					$filename = array_pop( $bits );
					preg_match( '/([0-9]+)\.original\.([a-z]+)/i', $filename, $matches );
					if ( ! isset( $matches[ 1 ] ) )
						throw new exception( "Problem migrating data, no ID. Sorry." );
					if ( ! isset( $matches[ 2 ] ) )
						throw new exception( "Problem migrating data, no file type. Sorry." );
					$term_id = (int) $matches[ 1 ];
					$ext = $matches[ 2 ];
					$data = array(
						'original' => "$term_id.original.$ext",
						'thumb' => "$term_id.thumb.$ext",
					);
					$this->save_data( $term_id, $data );
				}
			}
		}
		update_option( $this->name . '-version', (int) $this->version );
	}
	
	/**
	 * Takes a filename and attempts to find that in the designated plugin templates
	 * folder in the theme (defaults to main theme directory, but uses a custom filter
	 * to allow theme devs to specify a sub-folder for all plugin template files using
	 * this system).
	 * 
	 * Searches in the STYLESHEETPATH before TEMPLATEPATH to cope with themes which
	 * inherit from a parent theme by just overloading one file.
	 *
	 * @param string $template_file A template filename to search for 
	 * @param bool $throw_error Whether to throw an error if a template is not found
	 * @return string|bool The path to the template file to use, or false on failure
	 * @author Simon Wheatley
	 **/
	protected function locate_template( $template_file, $throw_error = true ) {
		$located = '';
		// If there's a tpl in a (child theme or theme with no child)
		if ( file_exists( STYLESHEETPATH . "/view/category-images-ii/$template_file" ) )
			return STYLESHEETPATH . "/view/category-images-ii/$template_file";
		// If there's a tpl in the parent of the current child theme
		else if ( file_exists( TEMPLATEPATH . "/view/category-images-ii/$template_file" ) )
			return TEMPLATEPATH . "/view/category-images-ii/$template_file";
		// Fall back on the bundled plugin template (N.B. no filtered subfolder involved)
		else if ( file_exists( $this->dir( "view/category-images-ii/$template_file" ) ) )
			return $this->dir( "view/category-images-ii/$template_file" );
		// Oh dear. We can't find the template.
		if ( $throw_error ) {
			$msg = sprintf( __( "This plugin template could not be found: %s" ), $this->dir( "templates/$template_file" ) );
			error_log( "Template error: $msg" );
			echo "<p style='background-color: #ffa; border: 1px solid red; color: #300; padding: 10px;'>$msg</p>";
		}
		return false;
	}

}

/**
 * Instantiate the plugin
 *
 * @global
 **/

$CategoryImagesII = new CategoryImagesII();

?>