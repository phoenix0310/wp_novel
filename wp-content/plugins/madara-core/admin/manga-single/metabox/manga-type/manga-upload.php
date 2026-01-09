<?php

    if( ! defined( 'ABSPATH' ) ){
        exit;
    }

    global $manga_post;

    extract( $manga_post );

?>
    <div id="manga-upload" class="tab-content manga-chapter-tab chapter-content-tab">
		
		<?php do_action( 'manga_multi_chapter_before_upload_form_fields', $manga_post ); ?>
		
        <h2>
            <?php echo esc_html__('Upload Manga', WP_MANGA_TEXTDOMAIN); ?>
        </h2>
        <p>
            <label for="manga-storage"> <?php echo esc_html__('Storage', WP_MANGA_TEXTDOMAIN) ?> </label>
            <select name="manga-storage">
                <?php
                    foreach ( $available_host as $host ) { ?>
                        <option value="<?php echo esc_attr( $host['value'] ) ?>" <?php selected( $host['value'], $default_storage ); ?>><?php echo esc_attr( $host['text'] ) ?></option>
                    <?php
                    }
                ?>
            </select>
            <?php $GLOBALS['wp_manga_google_upload']->albums_dropdown( $default_storage, true ); ?>
			<?php do_action('wp_manga_storage_albumdropdown', $default_storage, 'manga-upload');?>
        </p>

        <div class="wp-manga-volume-section">
            <label for="wp-manga-volume-upload"><?php esc_html_e( 'Volume', WP_MANGA_TEXTDOMAIN ); ?> </label>
                <?php echo $volume_dropdown_html; ?>
            <button id="new-volume" class="button"><?php esc_html_e( 'New Volume', WP_MANGA_TEXTDOMAIN) ?></button>
            <div class="new-volume" style="margin-top:10px; display:none; position:relative;">
                <input type="text" id="volume-name" name="volume-name" class="disable-submit" style="width:25%;" placeholder="New Volume Name">
                <i class="fa fa-spinner fa-spin"></i>
                <button id="add-new-volume" class="add-new-volume button"><?php esc_html_e( 'Add', WP_MANGA_TEXTDOMAIN ); ?></button>
            </div>
        </div>
		
		<div class="wp-manga-input-file-path">
            <h4><?php esc_html_e('Direct File Path', WP_MANGA_TEXTDOMAIN);?></h4>
			<span class="description"><?php esc_html_e( 'Enter direct link to .zip file on your server', WP_MANGA_TEXTDOMAIN ); ?></span>
			<p><span class=""><?php echo ABSPATH;?></span><input type="text" id="wp-manga-file-path" name="wp-manga-file-path"></p>

            <div class="notice-message">
                <h4><?php esc_html_e( 'Allowed .zip file structures', WP_MANGA_TEXTDOMAIN ); ?></h4>
                <div class="notice-images-wrapper">
                    <img src="<?php echo esc_url( WP_MANGA_URI . 'assets/images/sample-zip-multi-chapters-no-volume.png' ); ?>">
                    <img src="<?php echo esc_url( WP_MANGA_URI . 'assets/images/sample-zip-multi-chapters-volumes.png' ); ?>">
                </div>
            </div>
        </div>
		
		<h2><?php esc_html_e('OR', WP_MANGA_TEXTDOMAIN);?></h2>

        <div class="wp-manga-input-file">
			<h4><?php esc_html_e('Upload File', WP_MANGA_TEXTDOMAIN);?></h4>
            <input type="file" id="wp-manga-file" name="wp-manga-file" value="true" tabindex="1" accept=".zip">

            <div class="notice-message">
                <h4><?php esc_html_e( 'Allowed .zip file structures', WP_MANGA_TEXTDOMAIN ); ?></h4>
                <div class="notice-images-wrapper">
					<p><?php esc_html_e('Use "--" separator in folder name for Chapter Name and Extend Name. For example: "Chapter 1 -- Other Name"');?></p>
                    <img src="<?php echo esc_url( WP_MANGA_URI . 'assets/images/sample-zip-multi-chapters-no-volume.png' ); ?>">
                    <img src="<?php echo esc_url( WP_MANGA_URI . 'assets/images/sample-zip-multi-chapters-volumes.png' ); ?>">
                </div>
            </div>

            <br>

            <span class="description"><?php esc_html_e( sprintf('Maximum upload file size: %dMB. If you use this upload, Direct Link will be ignored', $max_upload_file_size['actual_max_filesize_mb'] ), WP_MANGA_TEXTDOMAIN ); ?></span>

        </div>

        <button id="wp-manga-upload" class="button button-primary"> <?php echo esc_html__('Upload Manga', WP_MANGA_TEXTDOMAIN); ?> </button>

        <div id="manga-upload-msg" class="wp-manga-popup-content-msg">
        </div>
    </div>
<?php
