<?php

    if( ! defined( 'ABSPATH' ) ){
        exit;
    }

    global $manga_post;

    extract( $manga_post );

?>

<div id="chapter-content-upload" class="tab-content manga-chapter-tab chapter-content-tab">
    <?php do_action('wp_manga_content_chapter_before_form_upload', $manga_post);?>
	
	<div class="wp-manga-volume-section">
		
        <h2>
            <label for="wp-manga-volume-upload"><?php esc_html_e( 'Volume', WP_MANGA_TEXTDOMAIN ); ?> </label>
        </h2>
        <?php echo $volume_dropdown_html; ?>
        <button id="new-volume" class="button"><?php esc_html_e( 'New Volume', WP_MANGA_TEXTDOMAIN) ?></button>
        <div class="new-volume" style="margin-top:10px; display:none; position:relative;">
            <input type="text" id="volume-name" name="volume-name" class="disable-submit" style="width:25%;" placeholder="New Volume Name">
            <i class="fa fa-spinner fa-spin"></i>
            <button id="add-new-volume" class="add-new-volume button"><?php esc_html_e( 'Add', WP_MANGA_TEXTDOMAIN ); ?></button>
        </div>
    </div>
	
	<div class="wp-manga-chapter-file-path">
		<h4><?php esc_html_e('Direct File Path', WP_MANGA_TEXTDOMAIN);?></h4>
		<span class="description"><?php esc_html_e( 'Enter direct link to .zip file on your server', WP_MANGA_TEXTDOMAIN ); ?></span>
		<p><span class=""><?php echo ABSPATH;?></span><input type="text" id="wp-manga-chapter-file-path" name="wp-manga-chapter-file-path"></p>

		<div class="notice-message">
			<h4><?php esc_html_e( 'Allowed .zip file structures', WP_MANGA_TEXTDOMAIN ); ?></h4>
			<div class="notice-images-wrapper">
				<img src="<?php echo esc_url( WP_MANGA_URI . 'assets/images/sample-zip-text-chapter.png' ); ?>">
			</div>
		</div>
	</div>
	
	<h2><?php esc_html_e('OR', WP_MANGA_TEXTDOMAIN);?></h2>

    <div class="wp-manga-input-file">
        <h2>
            <label><?php esc_html_e( 'Multi Chapters File', WP_MANGA_TEXTDOMAIN ); ?> </label>
        </h2>
        <input type="file" id="chapter-content-file" name="chapter-content-file" value="true" tabindex="1" accept=".zip">
        <div class="notice-message">
            <h4><?php esc_html_e( 'Allowed .zip file structure', WP_MANGA_TEXTDOMAIN ); ?></h4>
            <div class="notice-images-wrapper">
                <img src="<?php echo esc_url( WP_MANGA_URI . 'assets/images/sample-zip-text-chapter.png' ); ?>">
            </div>
        </div>
        <br>
        <span class="description"><?php esc_html_e( sprintf('Maximum upload file size: %dMB.', $max_upload_file_size['actual_max_filesize_mb'] ), WP_MANGA_TEXTDOMAIN ); ?></span>
    </div>

    <button id="chapter-content-upload-btn" class="button button-primary"> <?php echo esc_html__('Upload File', WP_MANGA_TEXTDOMAIN); ?> </button>

    <div id="chapter-content-upload-msg" class="wp-manga-popup-content-msg">
    </div>
</div>
