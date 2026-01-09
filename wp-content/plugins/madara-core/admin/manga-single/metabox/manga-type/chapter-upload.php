<?php

    if( ! defined( 'ABSPATH' ) ){
        exit;
    }

    global $manga_post;

    extract( $manga_post );

    wp_localize_script( 'wp-manga-upload', 'cloudStorageURLRegex', array(
        'imgur'  => 'https:\/\/imgur.com\/a\/(\S+)',
        'amazon' => apply_filters('amazon_s3_cloud_folder_path_validate_js','https:\/\/s3\.console\.aws\.amazon\.com\/s3\/buckets\/(\S+)\/\?region'),
        'flickr' => 'https:\/\/www.flickr.com\/photos\/(\S+)\/albums\/(\d+)'
    ) );

?>
    <div id="chapter-upload" class="tab-content manga-chapter-tab chapter-content-tab">

        <div class="chapter-input">
			
			 <?php do_action( 'manga_single_chapter_before_form_fields', $manga_post ); ?>
            
			<div class="wp-manga-form-group">
                <h2>
                    <label for="wp-manga-volume">
                        <?php esc_attr_e( 'Volume', WP_MANGA_TEXTDOMAIN ) ?>
                    </label>
                </h2>

                <?php echo $volume_dropdown_html; ?>

                <button id="new-volume" class="button"><?php esc_attr_e( 'New Volume', WP_MANGA_TEXTDOMAIN ) ?></button>
                <div class="new-volume" style="margin-top:10px; display:none; position:relative;">
                    <input type="text" id="volume-name" name="volume-name" class="disable-submit" style="width:25%;" placeholder="New Volume Name">
                    <i class="fa fa-spinner fa-spin"></i>
                    <button id="add-new-volume" class="add-new-volume button"><?php esc_attr_e( 'Add', WP_MANGA_TEXTDOMAIN ) ?></button>
                </div>
            </div>

            <div class="wp-manga-form-group">
                <h2>
                    <label for="wp-manga-chapter-name">
                        <?php esc_attr_e( 'Chapter Name', WP_MANGA_TEXTDOMAIN ) ?>
                    </label>
                </h2>
                <input type="text" id="wp-manga-chapter-name" name="wp-manga-chapter-name" class="large-text disable-submit" value="" tabindex="1">
				<span class="description"><?php esc_attr_e( 'If you want to override a chapter, use same chapter name here', WP_MANGA_TEXTDOMAIN ) ?></span>
            </div>

            <div class="wp-manga-form-group">
                <h2>
                    <label for="wp-manga-chapter-name-extend">
                        <?php esc_attr_e( 'Name Extend', WP_MANGA_TEXTDOMAIN ) ?>
                    </label>
                </h2>
                <input type="text" id="wp-manga-chapter-name-extend" name="wp-manga-chapter-name-extend" class="large-text disable-submit" value="" tabindex="1">
                <span class="description">
                    <?php esc_attr_e( '(Optional) Name extend of chapter for better display => Chapter name: Name extend', WP_MANGA_TEXTDOMAIN ); ?>
                </span>
            </div>
			
			<div class="wp-manga-form-group">
                <h2>
                    <label for="wp-manga-chapter-name-extend">
                        <?php esc_attr_e( 'Chapter Index', WP_MANGA_TEXTDOMAIN ) ?>
                    </label>
                </h2>
                <input type="text" id="wp-manga-chapter-index" name="wp-manga-chapter-index" class="large-text disable-submit" value="" tabindex="1">
                <span class="description">
                    <?php esc_attr_e( '(Optional) Index of Chapter which is used to sort Chapters', WP_MANGA_TEXTDOMAIN ); ?>
                </span>
            </div>

            <div class="wp-manga-form-group">
                <h2>
                    <label>
                        <?php esc_attr_e( 'Upload Type', WP_MANGA_TEXTDOMAIN ) ?>
                    </label>
                </h2>
                <div class="wp-manga-upload-type">
                    <label for="upload-zip">
                        <input type="radio" name="wp-manga-upload-type" value="upload-zip" id="upload-zip"> <?php esc_html_e( 'Upload Zip File', WP_MANGA_TEXTDOMAIN ); ?>
                    </label>
                    <label for="select-album">
                        <input type="radio" name="wp-manga-upload-type" value="select-album" id="select-album"> <?php esc_html_e( 'Select Uploaded Album from Cloud Server', WP_MANGA_TEXTDOMAIN ); ?>
                    </label>
                </div>
            </div>

            <!-- Upload Zip File -->
            <div class="upload-zip upload-type" style="display:none;">
                <div class="wp-manga-form-group">
                    <h2>
                        <label for="wp-manga-chapter-storage"><?php esc_attr_e( 'Choose where to upload', WP_MANGA_TEXTDOMAIN ); ?></label>
                    </h2>
                    <select id="wp-manga-chapter-storage" name="wp-manga-chapter-storage">
                        <?php
                        foreach ( $available_host as $host ) { ?>
                            <option value="<?php echo esc_attr( $host['value'] ) ?>" <?php selected( $host['value'], $default_storage ); ?>><?php echo esc_attr( $host['text'] ) ?></option>
                        <?php
                        }
                        ?>
                    </select>
                    <?php $GLOBALS['wp_manga_google_upload']->albums_dropdown( $default_storage, true ); ?>
					<?php do_action('wp_manga_storage_albumdropdown', $default_storage, 'chapter-upload');?>
                </div>
				<div class="wp-manga-form-group">
                    <h2>
                        <label for="wp-manga-chapter-link">
                            <?php esc_attr_e( 'Direct Link', WP_MANGA_TEXTDOMAIN ) ?>
                        </label>
                    </h2>
                    <div class="wp-manga-input-link">
                        <p><span class=""><?php echo ABSPATH;?></span><input type="text" id="wp-manga-chapter-link" name="wp-manga-chapter-link" value="" tabindex="1"></p>
                        <div class="notice-message">
                            <h4><?php esc_html_e( 'Allowed .zip file structure', WP_MANGA_TEXTDOMAIN ); ?></h4>
                            <div class="notice-images-wrapper">
                                <img src="<?php echo esc_url( WP_MANGA_URI . 'assets/images/sample-zip-single-chapter.png' ); ?>">
                            </div>
                        </div>
                    </div>

                    <span class="description"><?php esc_attr_e( 'Direct link on local server to the .zip file which contains all pictures of the chapter.' , WP_MANGA_TEXTDOMAIN ); ?></span>
                    <br/>
                    <span class="description">
                        <?php esc_html_e( sprintf('Maximum upload file size: %dMB.', $max_upload_file_size['actual_max_filesize_mb'] ), WP_MANGA_TEXTDOMAIN ); ?>
                    </span>
                </div>
                <div class="wp-manga-form-group">
                    <h2>
                        <label for="wp-manga-chapter-file">
                            <?php esc_attr_e( 'File', WP_MANGA_TEXTDOMAIN ) ?>
                        </label>
                    </h2>
                    <div class="wp-manga-input-file">
                        <input type="file" id="wp-manga-chapter-file" name="wp-manga-chapter-file" value="true" tabindex="1" accept=".zip">
                        <div class="notice-message">
                            <h4><?php esc_html_e( 'Allowed .zip file structure', WP_MANGA_TEXTDOMAIN ); ?></h4>
                            <div class="notice-images-wrapper">
                                <img src="<?php echo esc_url( WP_MANGA_URI . 'assets/images/sample-zip-single-chapter.png' ); ?>">
                            </div>
                        </div>
                    </div>

                    <span class="description"><?php esc_attr_e( 'Update a .zip file which contains all pictures of the chapter. If you use this upload, Direct Link will be ignored' , WP_MANGA_TEXTDOMAIN ); ?></span>
                    <br/>
                    <span class="description">
                        <?php esc_html_e( sprintf('Maximum upload file size: %dMB.', $max_upload_file_size['actual_max_filesize_mb'] ), WP_MANGA_TEXTDOMAIN ); ?>
                    </span>
                </div>
                <div class="wp-manga-form-group">
                    <div id="chapter-overwrite" class="overwrite-options">
                        <h4> <?php esc_html_e( 'Do you want to overwrite chapter or create a new one?', WP_MANGA_TEXTDOMAIN ); ?> </h4>
                        <label>
                            <input type="radio" name="chapter-overwrite" id="overwrite" value="true" /> <span><?php esc_html_e('Overwrite old chapter', WP_MANGA_TEXTDOMAIN ); ?></span>
                        </label>
                        <label>
                            <input type="radio" name="chapter-overwrite" id="new" value="false" /> <span><?php esc_html_e('Create new chapter', WP_MANGA_TEXTDOMAIN ); ?></span>
                        </label>
                    </div>
                </div>
                <div class="wp-manga-form-group">
                    <div id="chapters-overwrite-select" class="overwrite-options">
                        <h4> <?php esc_html_e( 'Select Chapter to overwrite ', WP_MANGA_TEXTDOMAIN ); ?> </h4>
                        <div class="chapter-overwrite-contains">

                        </div>
                    </div>
                </div>

                <?php do_action( 'manga_chapter_upload_zip_form_fields', $manga_post ); ?>

                <div class="wp-manga-form-group">
                    <p>
                        <button id="wp-manga-chapter-file-upload" class="button button-primary"> <?php esc_attr_e( 'Upload Chapter', WP_MANGA_TEXTDOMAIN ) ?> </button>
                    </p>
                </div>
            </div>

            <!-- Select Uploaded Album -->
            <div class="select-album upload-type" style="display:none;">
                <div class="wp-manga-form-group">
                    <h2>
                        <label for="wp-manga-cloud-storage">
                            <?php esc_attr_e( 'Choose where to import', WP_MANGA_TEXTDOMAIN ); ?>
                        </label>
                    </h2>
                    <select id="wp-manga-cloud-storage" name="wp-manga-cloud-storage">
                        <?php foreach ( $available_host as $host ) { ?>

                            <?php
                                if( $host['value'] == 'local' ){
                                    continue;
                                }

                                if( $default_storage == 'local' ){
                                    $default_storage = $host['value'];
                                }
                            ?>

                            <option value="<?php echo esc_attr( $host['value'] ) ?>" <?php selected( $host['value'], $default_storage ); ?>>
                                <?php echo esc_attr( $host['text'] ) ?>
                            </option>

                        <?php } ?>
                    </select>
                </div>

                <!-- Picasa -->
                <div class="picasa-import" style="<?php echo $default_storage == 'picasa' ? '' : 'display:none;' ?>">
                    <div class="wp-manga-form-group">
                        <h2>
                            <label>
                                <?php esc_attr_e( 'Search Album by Name', WP_MANGA_TEXTDOMAIN ); ?>
                            </label>
                        </h2>
                        <input type="text" name="blogspot-album-name" placeholder="<?php esc_html_e( "Album Name", WP_MANGA_TEXTDOMAIN ); ?>">
                        <button type="button" id="blogspot-search-album" class="button button-primary">
                            <span class="fas fa-search"></span>
                        </button>

                        <p class="description">
                            <?php esc_html_e( 'Album needs to be exactly with the album you want to import in Google Photos (case sensitive.)', WP_MANGA_TEXTDOMAIN ); ?>
                        </p>
                    </div>

                    <div class="wp-manga-form-group" style="display: none;">
                        <h2>
                            <label>
                                <?php esc_html_e( 'Select Album to Import', WP_MANGA_TEXTDOMAIN ); ?>
                            </label>
                        </h2>
                        <select id="blogspot-select-album">
                        </select>
                    </div>

                </div>

                <!-- Imgur -->
                <div class="imgur-import" style="<?php echo $default_storage == 'imgur' ? '' : 'display:none;' ?>">
                    <div class="wp-manga-form-group">
                        <h2>
                            <label>
                                <?php esc_attr_e( 'Album URL', WP_MANGA_TEXTDOMAIN ); ?>
                            </label>
                        </h2>
                        <input type="text" name="imgur-album-url" id="imgur-album-url" pattern="">

                        <p class="description">
                            <?php echo __( 'Imgur album URL needs to be in <strong>https://imgur.com/a/{albumhash}</strong> format', WP_MANGA_TEXTDOMAIN ); ?>
                        </p>
                    </div>
                </div>

                <!-- Amazon -->
                <div class="amazon-import" style="<?php echo $default_storage == 'amazon' ? '' : 'display:none;' ?>">
                    <div class="wp-manga-form-group">
                        <h2>
                            <label>
                                <?php esc_attr_e( 'Folder URL', WP_MANGA_TEXTDOMAIN ); ?>
                            </label>
                        </h2>
                        <input type="text" name="amazon-folder-url" id="amazon-folder-url">

                        <p class="description">
                            <?php echo __( 'AmazonS3 folder URL needs to be in <strong>https://s3.console.aws.amazon.com/s3/buckets/{bucket}/{folder_path}/?region={region}</strong> format', WP_MANGA_TEXTDOMAIN ); ?>
                        </p>
                    </div>
                </div>

                <!-- Flickr -->
                <div class="flickr-import" style="<?php echo $default_storage == 'flickr' ? '' : 'display:none;' ?>">
                    <div class="wp-manga-form-group">
                        <h2>
                            <label>
                                <?php esc_attr_e( 'Album URL', WP_MANGA_TEXTDOMAIN ); ?>
                            </label>
                        </h2>
                        <input type="text" name="flickr-album-url" id="flickr-album-url">

                        <p class="description">
                            <?php echo __( 'Flickr album URL needs to be in <strong>https://www.flickr.com/photos/{user}/albums/{album_id}</strong> format', WP_MANGA_TEXTDOMAIN ); ?>
                        </p>
                    </div>
                </div>

                <?php do_action( 'manga_chapter_upload_url_form_fields', $manga_post ); ?>

                <div class="wp-manga-form-group">
                    <button type="button" class="button button-primary" id="import-album" title="<?php esc_html_e( "Search Album", WP_MANGA_TEXTDOMAIN ); ?>"><?php esc_html_e( 'Create Chapter', WP_MANGA_TEXTDOMAIN ); ?></button>
                </div>
            </div>

        </div>

        <div id="chapter-upload-msg" class="wp-manga-popup-content-msg"></div>

    </div>
<?php
