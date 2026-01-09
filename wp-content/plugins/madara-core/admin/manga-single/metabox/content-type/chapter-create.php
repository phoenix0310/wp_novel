<?php

    if( ! defined( 'ABSPATH' ) ){
        exit;
    }

    global $manga_post;

    extract( $manga_post );

?>
    <div id="chapter-content" class="tab-content chapter-content-tab">
        <div class="chapter-input">
		
			<?php do_action( 'manga_chapter_upload_url_before_form_fields', $manga_post ); ?>

            <h2><label for="wp-manga-volume"> <?php esc_attr_e( 'Volume', WP_MANGA_TEXTDOMAIN ) ?></label></h2>

            <?php echo $volume_dropdown_html; ?>

            <button id="new-volume" class="button"><?php esc_html_e('New Volume', WP_MANGA_TEXTDOMAIN ); ?></button>
            <div class="new-volume" style="margin-top:10px; display:none; position:relative;">
                <input type="text" id="volume-name" name="volume-name" class="disable-submit" style="width:25%;" placeholder="New Volume Name">
                <i class="fa fa-spinner fa-spin"></i>
                <button id="add-new-volume" class="add-new-volume button"><?php esc_html_e('Add', WP_MANGA_TEXTDOMAIN ); ?></button>
            </div>

            <h2>
                <label for="wp-manga-chapter-name"> <?php esc_attr_e( 'Chapter Name', WP_MANGA_TEXTDOMAIN ) ?></label>
            </h2>
            <input type="text" id="wp-manga-chapter-name" name="wp-manga-chapter-name" class="large-text disable-submit" value="" tabindex="1">
			<span class="description"><?php esc_attr_e( 'If you want to override a chapter, use same chapter name here', WP_MANGA_TEXTDOMAIN ) ?></span>
            <h2>
                <label for="wp-manga-chapter-name-extend"> <?php esc_attr_e( 'Name Extend', WP_MANGA_TEXTDOMAIN ) ?> </label>
            </h2>
            <input type="text" id="wp-manga-chapter-name-extend" name="wp-manga-chapter-name-extend" class="large-text disable-submit" value="" tabindex="1">
            <span class="description"><?php esc_attr_e( '(optional) Name extend of chapter for better display => Chapter name: Name extend', WP_MANGA_TEXTDOMAIN ) ?></span>
			
			<h2>
                <label for="wp-manga-chapter-index"> <?php esc_attr_e( 'Chapter Index', WP_MANGA_TEXTDOMAIN ) ?> </label>
            </h2>
            <input type="text" id="wp-manga-chapter-index" name="wp-manga-chapter-index" class="large-text disable-submit" value="" tabindex="1">
            <span class="description"><?php esc_attr_e( '(Optional) Index of Chapter which is used to sort Chapter', WP_MANGA_TEXTDOMAIN ) ?></span>

            <h2>
                <label for="wp-manga-chapter-content"> <?php esc_attr_e( 'Chapter Content', WP_MANGA_TEXTDOMAIN ); ?></label>
            </h2>
            <p class="description"><?php esc_html_e('To enable multiple servers, use this format in the content',WP_MANGA_TEXTDOMAIN);?><br/><?php esc_attr_e( 'SERVER 1 NAME :: CONTENT 1 || SERVER 2 NAME :: CONTENT', WP_MANGA_TEXTDOMAIN ) ?></p>
            <?php wp_editor( '', 'wp-manga-chapter-content'); ?>

            <?php do_action( 'manga_chapter_upload_url_form_fields', $manga_post ); ?>

            <p>
                <button id="wp-manga-content-chapter-create" class="button button-primary"> <?php esc_attr_e( 'Create Chapter', WP_MANGA_TEXTDOMAIN ) ?> </button>
            </p>
        </div>
        <div id="chapter-create-msg" class="wp-manga-popup-content-msg">
        </div>
    </div>
