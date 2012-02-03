=== Taxonomy Images II ===
Contributors: simonwheatley
Donate link: http://www.simonwheatley.co.uk/wordpress/
Tags: category, taxonomy, custom taxonomy, image, tag, category images, taxonomy images, term images
Requires at least: 3.2
Tested up to: 3.2
Stable tag: 1.31

This plugin allows you to upload images for categories and custom taxonomies, and provides a template tag to show the image(s) in your theme.

== Description ==

This plugin allows you to upload images for categories and custom taxonomies, and provides a template tag to show the image(s) in your theme.

To activate the images for a custom taxonomy, go to Settings > Category Images II.

To upload an image for a category, or a term in a custom taxonomy, go to the edit screen for that category or term.

=== Tag: ciii_category_images() && ciii_term_images() ===

`<?php ciii_category_images(); ?>`

Used within the loop, the above template tag will show the thumbnails for all the category images for the category of that post. If some categories have no image, no image is shown for that category (i.e. there is no default image). You will need to style the HTML output yourself.

`<?php ciii_category_images( 'category_ids=37,27' ); ?>`

Used anywhere and provided with category IDs, the above template tag will show the thumbnails for all the categories specified. If some categories have no image, no image is shown for that category (i.e. there is no default image here either).

(Note that this tag will get confused if you use it outside the loop. If you want to add a single image to your category archive pages, please use `ciii_category_archive_image()` below.)

`<?php ciii_term_images( 'post_tag' ); ?>`

Used within the loop, the above template tag will show the thumbnails for all the term images for the specified taxonomy (in this case 'post_tag') which are associated with that post. If some terms have no image, no image is shown for that term (i.e. there is no default image). You will need to style the HTML output yourself.

`<?php ciii_term_images( 'post_tag', category_ids=37,27' ); ?>`

Used anywhere and provided with term IDs, the above template tag will show the thumbnails for all the terms specified. If some terms have no image, no image is shown for that term (i.e. there is no default image here either). You will need to style the HTML output yourself.

You can pass the `show_description` parameter to not show the term or category description, and the `size` parameter to specify either 'original' or 'thumbnail'.

=== Tag: ciii_category_archive_image() ===

`<?php ciii_category_archive_image(); ?>`

This tag is designed to be used on the category archive page, either inside or outside the loop. It will show the image for the category in question.

=== Other notes ===

You can specify the maximum side of the category image thumbnail in "Settings > Category Images II". You can upload, and delete, images for each category from "Manage > Categories", click into each category you wish to edit and you'll see the uploading and deletion controls (deletion controls only show up if the category already has an image uploaded).

The HTML output is fairly well classed, but **if** you need to adapt it you can. Create a directory in your *theme* called "view", and a directory within that one called "category-images-ii". Then copy the template files `view/category-images-ii/term-images.php` from the plugin directory into your theme directory and amend as you need. If these files exist in these directories in your theme they will override the ones in the plugin directory. This is good because it means that when you update the plugin you can simply overwrite the old plugin directory as you haven't changed any files in it. All hail [John Godley](http://urbangiraffe.com/) for the code which allows this magic to happen.

Plugin initially produced on behalf of [Puffbox](http://www.puffbox.com).

Is this plugin lacking a feature you want? I'm happy to accept offers of feature sponsorship: [contact me](http://www.simonwheatley.co.uk/contact-me/) and we can discuss your ideas.

Any issues: [contact me](http://www.simonwheatley.co.uk/contact-me/).

== Installation ==

The plugin is simple to install:

1. Download `category-images-ii.zip`
1. Unzip
1. Upload `category-images-ii` directory to your `/wp-content/plugins` directory
1. Go to the plugin management page and enable the plugin
1. Give yourself a pat on the back

== Change Log ==

= v1.33 2011/08/20 =

* Attempt to de-confuse the template naming situation

= v1.32 2011/08/15 =

* BUGFIX: Reported issue with the glob not returning anything, causing the foreach to fail when upgrading the data
* Fixed class "name" property

= v1.31 2011/08/14 =

* BUGFIX: Default size wasn't properly working, resulting in broken images
* Slightly better documentation
* Fix Nonce notice

= v1.21 =

* Cope with other taxonomies than just categories

= v1.12 2010/06/18 =

* BUGFIX: Cope with changes to category edit screen in WP 3.0

= v1.11 2010/05/19 =

Dear Non-English Category Images II Users,

This release includes the facility for Exclude Pages to be translated into languages other than English. Please [contact me](http://www.simonwheatley.co.uk/contact-me/) if you want to translate Exclude Pages into your language.

Sorry it took so long.

Best regards,

Simon

* LOCALISATION: Added POT file for translators! Woo hoo!

= v1.21 2010/05/18 =

* ENHANCEMENT: You can now show images for other taxonomies than categories
* ENHANCEMENT: You can now opt not to show the description, by passing show_description=0 in the args
* ENHANCEMENT: You can now opt not to show the description, by passing show_description=0 in the args
* Various bug fixes

= v1.10 2010/05/18 =

* ENHANCEMENT: Category images now link to the categories

= v1.00 2009/02/24 =

* RELEASE: Version 1.00

= v0.40b 2009/02/23 =

* ENHANCEMENT: Added ciii_category_archive_image after (Richard Strauss)[http://littlegreenblog.com] pointed out the flaws of ciii_category_images when used in archive.php outside the loop.

= v0.30b 2009/01/13 =

* ENHANCEMENT: Now compatible with both 2.6.5 AND 2.7

= v0.21b 2009/01/13 =

* FIXED: Through a triumph of copying and pasting the readme.txt, I managed to rename this plugin to "Author Listings"

= v0.20b 2009/01/12 =

* Category images can now be deleted.

= v0.10b 2009/01/12 =

* Plugin first sees the light of day.
