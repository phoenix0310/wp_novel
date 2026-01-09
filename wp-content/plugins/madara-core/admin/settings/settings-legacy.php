<?php

    if( ! defined( 'ABSPATH' ) ){
        exit;
    }   

    if( isset($_POST['wp_manga_sort_by_name_legacy_submit']) ){
        update_option('wp_manga_sort_by_name_legacy', $_POST['wp_manga_sort_by_name']);
    }

    if( isset($_POST['wp_manga_sort_by_name_legacy_rebuild']) ){
        global $wpdb;
        $sql = "UPDATE {$wpdb->prefix}manga_chapters SET chapter_sort = 1*REGEXP_REPLACE(chapter_name, '[^0-9\.]', '')";
        $wpdb->query($sql);

        echo '<div class="notice notice-success"><p>' . esc_html__('Index rebuilt successfully.', WP_MANGA_TEXTDOMAIN) . '</p></div>';
    }

    if(isset($_GET['wp_manga_chapter_sort_built_ignore'])){
        update_option('wp_manga_chapter_sort_built_ignore', 1);
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Ignore option saved.', WP_MANGA_TEXTDOMAIN) . '</p></div>';
    }
    
    ?>
    <div class="wrap wp-manga-wrap">
        <h2>
            <?php echo get_admin_page_title(); ?>
        </h2>
        <form method="post">
            <h3>
                <?php esc_html_e( 'Sort by Name', WP_MANGA_TEXTDOMAIN ) ?>
            </h3>
            <p><?php esc_html_e('Since Madara 2.2.6+, the Chapter/Volume Sorting by Name is improved. If you are any issues with the sorting, check this setting to return to the old mechanism and let us know.', WP_MANGA_TEXTDOMAIN);?></p>
            <p><?php esc_html_e('Sort by Name only works properly if your chapter/volume names are in the format of "Chapter 1", "Chapter 2", "Chapter 3", etc. or "Volume 1", "Volume 2", "Volume 3", etc. Float numbers are also supported.', WP_MANGA_TEXTDOMAIN);?></p>
            <p><label><input type="checkbox" name="wp_manga_sort_by_name" value="1" <?php checked(get_option('wp_manga_sort_by_name_legacy'), 1); ?>> <?php esc_html_e('Use Legacy Sorting', WP_MANGA_TEXTDOMAIN); ?></label></p>
            <p><input type="submit" name="wp_manga_sort_by_name_legacy_submit" class="button button-primary" value="<?php esc_attr_e('Save Changes', WP_MANGA_TEXTDOMAIN); ?>"></p>
            <p><?php esc_html_e('Rebuild Index will rebuild the index for existing chapters and volumes. It will take time to rebuild the index if your site has a lot of chapters and volumes, so stay tuned! You only need to run this once after you upgrade to 2.2.6+', WP_MANGA_TEXTDOMAIN);?></p>
            <p><input type="submit" name="wp_manga_sort_by_name_legacy_rebuild" class="button button-primary" value="<?php esc_attr_e('Rebuild Index', WP_MANGA_TEXTDOMAIN); ?>">
            <h3>
                <?php esc_html_e( 'Rebuild Search Text', WP_MANGA_TEXTDOMAIN ) ?>
            </h3>
            <p><?php esc_html_e('Rebuild all search text for all series (mangas/novels) in your site. It only needs to be run once if you are from a version earlier than 1.7, or you will to rebuild all search text for some reasons. Search Text is used for keyword search, and these fields will be included:', WP_MANGA_TEXTDOMAIN);?>
                <ul style="list-style:disc; padding-left:20px">
                    <li><?php esc_html_e('Series Title',WP_MANGA_TEXTDOMAIN);?></li>
                    <li><?php esc_html_e('Series Alternative Title',WP_MANGA_TEXTDOMAIN);?></li>
                    <li><?php esc_html_e('Chapter Name & Extend Name',WP_MANGA_TEXTDOMAIN);?></li>
                </ul>
            </p>
            <p><?php esc_html_e('If you only need to search in Title, Excerpt or Description, then you don\'t need to do this.', WP_MANGA_TEXTDOMAIN);?></p>
            <p><?php esc_html_e('It will take time to rebuild the search text if your site has a lot of series and chapters, so stay tuned!', WP_MANGA_TEXTDOMAIN);?>
			<?php do_action('wp_manga_search_settings'); ?>
            </p>
            <?php
            
             $wp_manga_database = WP_MANGA_DATABASE::get_instance();;
            $wpdb = $wp_manga_database->get_wpdb();
            
            if(!$wp_manga_database->column_exists($wpdb->posts, 'wp_manga_search_text')){     
            ?>
            <p style="color:red"><?php esc_html_e('This function modify database structure. Make sure you backup your database before doing this! If your site has a lot of data, it may break in the middle', WP_MANGA_TEXTDOMAIN);?></p>
            <?php }?>
            <input type="hidden" name="wp_manga_search" value="rebuild"/>
            <p>
            <button type="submit" class="button button-primary" id="manga_rebuild_search_text"><?php esc_attr_e( 'Run', WP_MANGA_TEXTDOMAIN ) ?> <i class="fas fa-spinner fa-spin" style="display:none"></i></button>
            <button type="submit" class="button button-secondary" id="manga_cancel_rebuild_search_text" name="btnignore"><?php esc_attr_e( 'Ignore', WP_MANGA_TEXTDOMAIN ) ?></button>
            </p>
        </form>
    </div>
