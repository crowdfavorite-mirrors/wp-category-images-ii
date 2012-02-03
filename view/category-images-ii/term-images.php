<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<ul class="category_images_ii term-images taxonomy-<?php echo esc_attr( $taxonomy ); ?>">
<?php foreach( $terms AS & $term ) { ?>
	<li class="category_image term_image"><?php 
	if ( $link_images ) : ?><a href="<?php 
	echo esc_attr( get_term_link( $term[ 'id' ], $taxonomy ) ); 
	?>"><?php endif; ?><img src="<?php 
	echo $term[ 'image' ]; ?>" alt="<?php echo $term[ 'name' ]; ?>" /><?php 
	if ( $link_images ) : ?></a><?php 
	endif; ?>
	<?php if ( $show_description ) : ?>
		<p><?php term_description( $term[ 'id' ], $taxonomy ); ?></p>
	<?php endif; ?>
	</li>
<?php } ?>
</ul>