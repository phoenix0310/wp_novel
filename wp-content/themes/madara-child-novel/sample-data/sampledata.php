<?php

function is_madara_novelhub_child_theme() {
    $current_theme = wp_get_theme();
    return $current_theme->get_stylesheet() === 'madara-child-novel';
}

if (!is_madara_novelhub_child_theme()) {
    return;
}

function madara_child_novel_demo_import_lists($theme_slug){

	$sample_data_url = get_stylesheet_directory_uri() . '/sample-data';
	$demo_lists = array(
		'novelhub' =>array(
			'title' => __( 'Novelhub', 'madara-child-novel' ),/*Title*/
			'is_pro' => false,/*Is Premium*/
			'type' => 'Elementor',
			'author' => __( 'Mangabooth', 'madara-child-novel' ),/*Author Name*/
			'keywords' => array( 'novel' ),/*Search keyword*/
			'categories' => array( 'novel' ),/*Categories*/
            'template_url' => array(
                'content' => $sample_data_url . '/content.json',/*Full URL Path to content.json*/
                'options' => $sample_data_url . '/options.json',/*Full URL Path to options.json*/
                'widgets' => $sample_data_url . '/widgets.json',/*Full URL Path to widgets.json*/
            ),
			'screenshot_url' => $sample_data_url . '/preview.png',/*Full URL Path to demo screenshot image*/
			'demo_url' => 'https://live.mangabooth.com/novelreader/',/*Full URL Path to Live Demo*/
		),
	);

	if (!is_plugin_active('widget-logic/widget_logic.php')) {
		$demo_lists['novelhub']['plugins'] = array(
			array(
				'name' => __( 'Widget Logic', 'madara-child-novel' ),
				'slug' => 'widget-logic',
				'source' => $sample_data_url . '/plugins/widget-logic.zip',
			),
		);
	}

	return $demo_lists;
}
add_filter('advanced_import_demo_lists','madara_child_novel_demo_import_lists', 100);

add_action('advanced_import_before_complete_screen', 'add_chapters_from_json');
function add_chapters_from_json() {
    if (!class_exists('WP_MANGA_TEXT_CHAPTER')) {
        return;
    }

    global $wp_manga_text_type;

	if (!file_exists(get_stylesheet_directory() . '/sample-data/chapters-data.json')) {
		error_log("File 'chapters-data.json' not found.");
		return;
	}

    $json_data = file_get_contents(get_stylesheet_directory() . '/sample-data/chapters-data.json');

    $chapters_by_slug = json_decode($json_data, true);

    foreach ($chapters_by_slug as $novel_slug => $chapters) {

		$novel = get_page_by_path($novel_slug, OBJECT, 'wp-manga');

        if (!$novel) {
            error_log("Novel with slug '$novel_slug' not found.");
            continue;
        }

		// set novel featured image by slug
		set_manga_images($novel->ID, $novel_slug);

		//insert chapter data
        foreach ($chapters as $chapter) {

            $insert_chapter_args = [
                'post_id' => $novel->ID,
                'volume_id' => 0,
                'chapter_name' => $chapter['chapter_name'],
                'chapter_name_extend' => '',
                'chapter_slug' => sanitize_title($chapter['chapter_name']),
                'chapter_content' => $chapter['chapter_content'] . $chapter['chapter_content'] . $chapter['chapter_content'], // make it longer
            ];

			$insert_chapter = $wp_manga_text_type->insert_chapter($insert_chapter_args);

            if (is_wp_error($insert_chapter)) {
                error_log("Error adding chapter '{$chapter['chapter_name']}' for novel slug '$novel_slug': " . $insert_chapter->get_error_message());
            }
        }
    }
}

function set_manga_images($manga_id, $image_slug) {
    $post_id = $manga_id;
    $thumbnail_path = get_stylesheet_directory() . '/sample-data/images/' . $image_slug . '.png';
	$banner_path = get_stylesheet_directory() . '/sample-data/images/' . $image_slug . '-banner.jpeg';

    if (file_exists($thumbnail_path)) {

		$upload = wp_upload_bits(basename($thumbnail_path), null, file_get_contents($thumbnail_path));

		if (!$upload['error']) {

			$attachment = array(
                'post_mime_type' => 'image/png',
                'post_title'     => basename($thumbnail_path),
                'post_content'   => '',
                'post_status'    => 'inherit',
            );

            $attachment_id = wp_insert_attachment($attachment, $upload['file'], $post_id);

            $attachment_metadata = wp_generate_attachment_metadata($attachment_id, $upload['file']);
            wp_update_attachment_metadata($attachment_id, $attachment_metadata);

			set_post_thumbnail($post_id, $attachment_id);
        }
    }

	if (file_exists($banner_path)) {
		$upload = wp_upload_bits(basename($banner_path), null, file_get_contents($banner_path));

		if (!$upload['error']) {

			$attachment = array(
				'post_mime_type' => 'image/jpeg',
				'post_title'     => basename($banner_path),
				'post_content'   => '',
				'post_status'    => 'inherit',
			);

			$attachment_id = wp_insert_attachment($attachment, $upload['file'], $post_id);

			$attachment_metadata = wp_generate_attachment_metadata($attachment_id, $upload['file']);
			wp_update_attachment_metadata($attachment_id, $attachment_metadata);
			$attachment_url = wp_get_attachment_url($attachment_id);
			update_post_meta($post_id, 'manga_banner', $attachment_url);
		}
	}
}


add_filter ('madara_required_plugins', 'madara_novelhub_required_plugins');
function madara_novelhub_required_plugins($madara_required_plugins) {
	$madara_required_plugins[] = array(
		'name' => 'Advanced Import : One Click Import for WordPress or Theme Demo Data',
		'slug' => 'advanced-import',
		'required' => false,
	);

	return $madara_required_plugins;
}
