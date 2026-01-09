<?php

    if( ! defined( 'ABSPATH' ) ){
        exit;
    }

    global $manga_post, $wp_manga_post_type;

    extract( $manga_post );
?>
    <div id="chapter-listing" class="tab-content">

        <!--search chapter-->
        <div class="search-chapter-section">
            <input type="text" id="search-chapter" class="regular-text disable-submit" placeholder="<?php esc_html_e( 'Search Chapter', WP_MANGA_TEXTDOMAIN ); ?>">
            <div class="search-chapter-icons">
                <i class="fa fa-search" aria-hidden="true"></i>
                <div class="wp-manga-spinner">
                  <div class="rect1"></div>
                  <div class="rect2"></div>
                  <div class="rect3"></div>
                  <div class="rect4"></div>
                  <div class="rect5"></div>
                </div>
            </div>
        </div>
        <div class="fetching-data hidden">
            <span><?php esc_html_e('Fetching New Chapters Data', WP_MANGA_TEXTDOMAIN ); ?></span><i class="fa fa-spinner fa-spin"></i>
        </div>
        <!--start listing chapter-->
        <div class="chapter-list">
            <?php
                $all_chapters = $wp_manga_post_type->list_all_chapters( $post_id );
                if( $all_chapters !== false ) {
                    echo $all_chapters;
                }else{
                    esc_html_e( 'This Manga doesn\'t have any chapters yet. ', WP_MANGA_TEXTDOMAIN );
                }
            ?>
        </div>
    </div>
<?php
