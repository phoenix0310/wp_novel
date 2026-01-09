<?php

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	global $manga_post;

	extract( $manga_post );

    $supported_chapter_types = get_option( 'wp_manga_settings', array() )['supported_chapter_types'] ?? 'all';
    
    $chapter_type = $supported_chapter_types == 'all' ? $chapter_type : $supported_chapter_types;
?>

<div class="choose-manga-type">
    <div class="choose-manga-type-wrapper">
        <h1>
			<?php esc_html_e( 'Chapter Type', WP_MANGA_TEXTDOMAIN ); ?>
        </h1>
        <div class="description">
			<?php esc_html_e( 'This setting cannot be changed after choosen.', WP_MANGA_TEXTDOMAIN ); ?>
        </div>

        <p>
        <div class="manga-type-choice">
         <input type="radio" name="wp-manga-chapter-type" value="manga" id="wp-manga-type" <?php checked( $chapter_type, 'manga' ); ?>/>
            <label for="wp-manga-type"><?php esc_html_e( 'Manga Chapter', WP_MANGA_TEXTDOMAIN ); ?></label>
        </div>
        <div class="manga-type-choice">
            <input type="radio" name="wp-manga-chapter-type" value="text" id="wp-text-type" <?php checked( $chapter_type, 'text' ); ?>/>
            <label for="wp-text-type"><?php esc_html_e( 'Text Chapter', WP_MANGA_TEXTDOMAIN ); ?></label>
        </div>
        <div class="manga-type-choice">
            <input type="radio" name="wp-manga-chapter-type" value="video" id="wp-video-type" <?php checked( $chapter_type, 'video' ); ?>/>
            <label for="wp-text-type"><?php esc_html_e( 'Video Chapter', WP_MANGA_TEXTDOMAIN ); ?></label>
        </div>
		<?php do_action( 'madara_manga_chapter_type', $chapter_type ); ?>
        </p>

    </div>
</div>