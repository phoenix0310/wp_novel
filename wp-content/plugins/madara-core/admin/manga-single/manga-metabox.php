<?php

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	global $wp_manga, $wp_manga_functions, $post, $wp_manga_post_type, $wp_manga_setting, $wp_manga_template;
	$post_id      = $post->ID;
	$uniqid       = $wp_manga->get_uniqid( $post_id );
	
	$alternative  = get_post_meta( $post_id, '_wp_manga_alternative', true );
    $chapter_warning  = get_post_meta( $post_id, '_wp_manga_chapters_warning', true );
	$type         = get_post_meta( $post_id, '_wp_manga_type', true );
	$release      = get_post_meta( $post_id, '_wp_manga_release', true );

	$default_storage              = $wp_manga_setting->get_manga_option( 'default_storage', 'local' );
	$allow_to_choose_chapter_type = $wp_manga_post_type->allow_to_choose_chapter_type( $post_id );
    $chapter_type = get_post_meta( $post_id, '_wp_manga_chapter_type', true );
	$chapter_type = apply_filters( 'manga_metabox_chapter_type', $chapter_type, $post_id );

    $supported_chapter_types = get_option( 'wp_manga_settings', array() )['supported_chapter_types'] ?? 'all';
    //echo $supported_chapter_types;exit;
    if($supported_chapter_types != 'all'){
        $allow_to_choose_chapter_type = false;

        if($chapter_type == ''){
            // set default chapter type
            update_post_meta( $post_id, '_wp_manga_chapter_type', $supported_chapter_types );
            $chapter_type = $supported_chapter_types;
        }
    }

	$available_host = $wp_manga->get_available_host();

	$max_upload_file_size = $wp_manga_functions->max_upload_file_size();
    	
    //print max upload file size
	wp_localize_script( 'wp-manga-upload', 'file_size_settings', $max_upload_file_size );

	$volume_dropdown_html = $wp_manga_functions->volume_dropdown( $post_id, false );

	$GLOBALS['manga_post'] = compact( [
		'post_id',
		'default_storage',
		'available_host',
		'max_upload_file_size',
		'volume_dropdown_html',
		'chapter_type'
	] );
?>

<input type="hidden" name="postID" value="<?php echo esc_attr( $post_id ); ?>">
<input type="hidden" name="uniqid" value="<?php echo esc_attr( $uniqid ); ?>">
<input type="hidden" name="wp-manga-chapter-type" value="<?php echo ! empty( $chapter_type ) ? $chapter_type : 'manga'; ?>">

<div class="wp-manga-info wp-manga-tabs-wrapper <?php echo $allow_to_choose_chapter_type ? 'choosing-manga-type' : ''; ?>">
    <div class="wp-manga-tabs">
        <ul>
            <li class="tab-active">
                <a href="#manga-information"> <?php echo __( 'Manga Extra Info', WP_MANGA_TEXTDOMAIN ); ?> </a>
            </li>
            <li>
                <a href="#chapter-listing"> <?php echo __( 'Manga Chapters List', WP_MANGA_TEXTDOMAIN ); ?> </a>
            </li>

			<?php if ( $chapter_type != 'video' && $chapter_type != 'text' ) { ?>

                <li class="manga-tab-select">
                    <a href="#chapter-upload"> <?php echo __( 'Upload Single Chapter', WP_MANGA_TEXTDOMAIN ); ?> </a>
                </li>
                <li class="manga-tab-select">
                    <a href="#manga-upload"> <?php echo __( 'Upload Multi Chapters', WP_MANGA_TEXTDOMAIN ); ?> </a>
                </li>
			<?php } ?>

			<?php if ( $chapter_type == 'text' || $allow_to_choose_chapter_type ) { ?>
                <li class="text-tab-select">
                    <a href="#chapter-content"> <?php echo __( 'Text Chapter', WP_MANGA_TEXTDOMAIN ); ?> </a>
                </li>
                <li class="text-tab-select">
                    <a href="#chapter-content-upload"><?php esc_html_e( 'Upload Multi Chapters', WP_MANGA_TEXTDOMAIN ); ?></a>
                </li>
			<?php } ?>

			<?php if ( $chapter_type == 'video' || $allow_to_choose_chapter_type ) { ?>
                <li class="video-tab-select">
                    <a href="#chapter-content"> <?php echo __( 'Video Chapter', WP_MANGA_TEXTDOMAIN ); ?> </a>
                </li>
                <li class="video-tab-select">
                    <a href="#chapter-content-upload"><?php esc_html_e( 'Upload Multi Chapters', WP_MANGA_TEXTDOMAIN ); ?></a>
                </li>
			<?php } ?>
        </ul>
    </div>
    <div class="wp-manga-content">
        <!--manga information-->
        <div id="manga-information" style="display:block;" class="tab-content">
            <div id="extra-info">

                <h2>
                    <span> <?php esc_html_e( 'Manga Extra Info', WP_MANGA_TEXTDOMAIN ); ?> </span>
                </h2>

                <label for="wp-manga-alternative">
                    <h4> <?php esc_attr_e( 'Alternative Name', WP_MANGA_TEXTDOMAIN ) ?> </h4></label>
                <input type="text" id="wp-manga-alternative" name="wp-manga-alternative" class="large-text" value="<?php echo esc_attr( $alternative ) ?>" tabindex="1">

                <label for="wp-manga-type"><h4> <?php esc_attr_e( 'Type', WP_MANGA_TEXTDOMAIN ) ?> </h4></label>
                <input type="text" id="wp-manga-type" name="wp-manga-type" class="large-text" value="<?php echo esc_attr( $type ) ?>" tabindex="1">

            </div>
            <div id="status-section"></div>
            <div id="release-year-section"></div>
            <div id="author-section"></div>
            <div id="artist-section"></div>
            <div id="genre-section"></div>
            <div id="tags-section"></div>
            <div id="views-section"></div>
            <div id="others-section">
                <h2>
                    <span> <?php esc_html_e( 'Other Settings', WP_MANGA_TEXTDOMAIN ); ?> </span>
                </h2>
                
                <label for="wp-manga-chapters-warning">
                    <h4> <?php esc_attr_e( 'Global Message In All Chapters', WP_MANGA_TEXTDOMAIN ) ?> </h4></label>
					<?php wp_editor( $chapter_warning, 'wp-manga-chapters-warning', $args = array(
    'tinymce'       => array(
        'toolbar1'      => 'bold,italic,underline,separator,alignleft,aligncenter,alignright,separator,link,unlink,undo,redo',
        'toolbar2'      => '',
        'toolbar3'      => '',
    ),
)); ?>
            </div>
        </div>

        <!--chapter list-->
		<?php include WP_MANGA_DIR . 'admin/manga-single/metabox/chapter-list.php'; ?>

		<?php if ( $chapter_type != 'video' && $chapter_type != 'text' ) { ?>
            <!--manga chapter upload-->
			<?php include WP_MANGA_DIR . 'admin/manga-single/metabox/manga-type/chapter-upload.php'; ?>

            <!--manga upload-->
			<?php include WP_MANGA_DIR . 'admin/manga-single/metabox/manga-type/manga-upload.php'; ?>
		<?php } ?>

		<?php if ( $chapter_type == 'text' || $chapter_type == 'video' || $allow_to_choose_chapter_type ) { ?>
            <!-- content chapter create -->
			<?php include WP_MANGA_DIR . 'admin/manga-single/metabox/content-type/chapter-create.php'; ?>

            <!--text/video multi chapters upload-->
			<?php include WP_MANGA_DIR . 'admin/manga-single/metabox/content-type/manga-upload.php'; ?>
		<?php }?>

        <div class="wp-manga-popup-loading">
            <div class="wp-manga-popup-loading-wrapper">
                <i class="fa fa-spinner fa-spin"></i>
            </div>
        </div>
    </div>

	<?php if ( $allow_to_choose_chapter_type ) { ?>
	<?php include WP_MANGA_DIR . 'admin/manga-single/metabox/manga-type-select.php'; ?>
	<?php } ?>

</div>

<?php include WP_MANGA_DIR . 'admin/manga-single/chapter-edit-modal.php'; ?>
