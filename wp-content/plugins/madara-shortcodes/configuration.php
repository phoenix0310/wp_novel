<?php

	global $madara_shortcodes;

	$madara_shortcodes = array(
		'madara_shortcode_list' => array(
			'classic_js' => CT_SHORTCODE_PLUGIN_URL . 'shortcodes/js/admin/all-shortcodes.js'
		),
		'madara_post_slider'           => array(
			'path'       => 'shortcodes/post-slider.php',
			'classic_js' => CT_SHORTCODE_PLUGIN_URL . 'shortcodes/js/admin/post-slider.js',
			'class'      => 'MadaraShortcodePostSlider',
			'tag'        => 'manga_post_slider'
		),
		'madara_heading'           => array(
			'path'       => 'shortcodes/heading.php',
			'classic_js' => CT_SHORTCODE_PLUGIN_URL . 'shortcodes/js/admin/heading.js',
			'class'      => 'MadaraShortcodeHeading',
			'tag'        => 'manga_heading'
		),
		'madara_blog'            => array(
			'path'       => 'shortcodes/blog.php',
			'classic_js' => CT_SHORTCODE_PLUGIN_URL . 'shortcodes/js/admin/blog.js',
			'tag'        => 'manga_blog'
		),
		'mangas_listing' => array(
			'path'       => 'shortcodes/manga-listing.php',
			'classic_js' => CT_SHORTCODE_PLUGIN_URL . 'shortcodes/js/admin/manga-listing.js',
			'tag'        => 'manga_listing'
		),
		'manga_grid' => array(
			'path'       => 'shortcodes/manga-grid.php',
			'classic_js' => CT_SHORTCODE_PLUGIN_URL . 'shortcodes/js/admin/manga-grid.js',
			'tag'        => 'manga_grid'
		),
		'manga_info' => array(
			'path'       => 'shortcodes/manga-info.php',
			'classic_js' => CT_SHORTCODE_PLUGIN_URL . 'shortcodes/js/admin/manga-info.js',
			'tag'        => 'manga_info'
		),
		'manga_chapters' => array(
			'path'       => 'shortcodes/manga-chapters.php',
			'classic_js' => CT_SHORTCODE_PLUGIN_URL . 'shortcodes/js/admin/manga-chapters.js',
			'tag'        => 'manga_chapters'
		),
		'manga_bookmarks' => array(
			'path'       => 'shortcodes/manga-bookmarks.php',
			'tag'        => 'wp-manga-my-bookmarks'
		)
	);
	
	// Gutenbern support - register new categories
	function wp_manga_block_categories( $block_categories, $block_editor_context  ) {
		
		return array_merge(
			$block_categories,
			array(
				array(
					'slug' => 'wp-manga',
					'title' => esc_html__( 'WP Manga', 'madara' ),
					'icon'  => 'dashicons-index-card',
				),
			)
		);
	}
	add_filter( 'block_categories_all', 'wp_manga_block_categories', 10, 2 );
