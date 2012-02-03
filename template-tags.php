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


/**
 * Display a list of categories for a post in The Loop, each represented 
 * by the image uploaded for each. Categories without images are not 
 * shown. You are expected to apply styling to the generated HTML yourself.
 *
 * @param array|string $args 
 * @return string|void Returns the HTML if the echo arg is set to false
 * @author Simon Wheatley
 **/
function ciii_category_images( $args = null ) {
	if ( isset( $args[ 'category_ids' ] ) )
		$args[ 'term_ids' ] = $args[ 'category_ids' ];
	return ciii_term_images( 'category', $args );
}

/**
 * Display a list of terms for a post in The Loop, each represented by 
 * the image uploaded for each. Terms without images are not shown. You 
 * are expected to apply styling to the generated HTML yourself.
 *
 * @param array|string $args 
 * @return string|void Returns the HTML if the echo arg is set to false
 * @author Simon Wheatley
 **/
function ciii_term_images( $taxonomy, $args = null ) {
	global $CategoryImagesII;

	// Traditional WP argument munging.
	$defaults = array(
		'echo' => true,
		'link_images' => true,
		'size' => 'thumb',
		'term_ids' => false,
		'show_description' => true,
	);
	$r = wp_parse_args( $args, $defaults );
	
	// Term ID(s) passed?
	if ( $r[ 'term_ids' ] !== false ) {
		$term_ids = explode( ',', $r['term_ids'] );
	} else {

		// In the loop?
		if ( ! $terms = get_the_terms( get_the_ID(), $taxonomy ) )
			return;
		$term_ids = array();
		foreach ( $terms AS & $term )
			$term_ids[] = $term->term_id;
	}

	if ( ! $r[ 'echo' ] )
		return $CategoryImagesII->display_images( $taxonomy, $term_ids, $r );
	echo $CategoryImagesII->display_images( $taxonomy, $term_ids, $r );
}

/**
 * Display the thumbnail image for the current category archive page. You are expected
 * to apply styling to the generated HTML yourself.
 *
 * @param array|string $args 
 * @return string|void Returns the HTML if the echo arg is set to false
 * @author Simon Wheatley
 **/
function ciii_category_archive_image( $args = null ) {
	global $CategoryImagesII;
	// Traditional WP argument munging.
	$defaults = array(
		'echo' => true,
		'link_images' => true,
	);
	$r = wp_parse_args( $args, $defaults );

	if ( ! is_category() )
		return;
	$cat = (array) intval( get_query_var('cat') );

	if ( ! $r[ 'echo' ] )
		return $CategoryImagesII->display_images( 'category', $cat, $r[ 'link_images' ] );
	echo $CategoryImagesII->display_images( 'category', $cat, $r[ 'link_images' ] );
}

?>