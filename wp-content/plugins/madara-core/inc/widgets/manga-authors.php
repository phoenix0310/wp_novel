<?php
/*
*   Manga Authors Widget
*/

class Manga_Authors_Widget extends WP_Widget {

    public function __construct() {

        $widget_options = array(
            'classname' => 'manga-authors-widget',
            'description' => __( 'Show Manga Authors/Artists', WP_MANGA_TEXTDOMAIN )
        );

        parent::__construct( 'manga-authors-id', __( 'WP Manga: Manga Authors/Artists', WP_MANGA_TEXTDOMAIN ), $widget_options );

    }

    function widget( $args, $instance ) {

        extract( $args );

        $title = isset( $instance['title'] ) ? $instance['title'] : '';
        $exclude_author = isset( $instance['exclude_author'] ) ? $instance['exclude_author'] : array();
        $show_manga_counts = isset( $instance['show_manga_counts'] ) ? $instance['show_manga_counts'] : 'true';
        $layout = isset( $instance['layout'] ) ? $instance['layout'] : 'layout-1';
        $data = isset( $instance['data'] ) ? $instance['data'] : 'wp-manga-author';

        echo $before_widget;

        ?>
        <div class="genres_wrap">
            <div class="row">
                <div class="col-md-12">
                    <?php
                    if( !empty( $title ) ) {
                        echo $before_title . esc_html( $title ) . $after_title;
                    }

                    if( !empty( $exclude_author ) ){

                        $authors = explode( ',', $exclude_author );
                        $exclude_author = array();

                        //get author id from author slug
                        foreach( $authors as $author ){
                            $author = trim( $author );
                            $author_obj = get_term_by( 'slug', $author, $data );

                            if( $author_obj != false ) {
                                $exclude_author[] = $author_obj->term_id;
                            }
                        }

                        $exclude_author = array_merge( $exclude_author, $authors );

                    }

                    //author query
                    $author_args = array(
                        'taxonomy' => $data,
                        'hide_empty' => true,
                        'exclude' => $exclude_author
                    );
                    $authors = get_terms( $author_args );

                    if( !empty( $authors ) && !is_wp_error( $authors ) ) {
                        ?>
                        <div class="genres__collapse" style="display:block;">
                            <div class="row genres">
                                <ul class="list-unstyled">
                                    <?php
                                    foreach( $authors as $author ) {
                                        ?>
                                        <li class="<?php echo $layout == 'layout-2' ? 'col-xs-6 col-sm-4 col-md-2' : 'col-xs-6 col-sm-6'; ?>">
                                            <a href="<?php echo esc_url( get_term_link( $author ) ); ?>">
                                                <?php echo esc_html( $author->name ); ?>
                                                <?php
                                                if( $show_manga_counts == 'true' ) {
                                                    ?>
                                                    <span class="count">
                                                        (<?php echo esc_html( $author->count ); ?>)
                                                    </span>
                                                    <?php
                                                }
                                                ?>
                                            </a>
                                        </li>
                                        <?php
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                        <?php
                    }
                    ?>

                </div>
            </div>
        </div>

        <?php
        echo $after_widget;

    }

    function form( $instance ) {

        $title = isset( $instance['title'] ) ? $instance['title'] : '';
        $show_manga_counts = isset( $instance['show_manga_counts'] ) ? $instance['show_manga_counts'] : 'true';
        $exclude_author = isset( $instance['exclude_author'] ) ? $instance['exclude_author'] : '';
        $layout = isset( $instance['layout'] ) ? $instance['layout'] : "layout-1";
        $data = isset( $instance['data'] ) ? $instance['data'] : "wp-manga-author";

        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>">
                <?php esc_html_e( 'Title: ', WP_MANGA_TEXTDOMAIN ); ?>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>">
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'data' ); ?>">
                <?php esc_html_e( 'Data: ', WP_MANGA_TEXTDOMAIN ); ?>
                <select type="text" class="widefat" id="<?php echo $this->get_field_id( 'data' ); ?>" name="<?php echo $this->get_field_name( 'data' ); ?>">
                    <option value="wp-manga-author" <?php selected( $data, 'wp-manga-author' ); ?>>
                        <?php esc_html_e( 'Manga Author', WP_MANGA_TEXTDOMAIN ); ?>
                    </option>
                    <option value="wp-manga-artist" <?php selected( $data, 'wp-manga-artist' ); ?>>
                        <?php esc_html_e( 'Manga Artist', WP_MANGA_TEXTDOMAIN ); ?>
                    </option>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'exclude_author' ); ?>">
                <?php esc_html_e( 'Exclude authors/artists: ', WP_MANGA_TEXTDOMAIN ); ?>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'exclude_author' ); ?>" name="<?php echo $this->get_field_name( 'exclude_author' ); ?>" value="<?php echo esc_attr( $exclude_author ); ?>">
                <span class="description"> <?php esc_html_e( 'Use WP Manga authors/artists ID or slug, separated by comma', WP_MANGA_TEXTDOMAIN ); ?> </span>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'layout' ); ?>">
                <?php esc_html_e( 'Layout: ', WP_MANGA_TEXTDOMAIN ); ?>
                <select type="text" class="widefat" id="<?php echo $this->get_field_id( 'layout' ); ?>" name="<?php echo $this->get_field_name( 'layout' ); ?>">
                    <option value="layout-1" <?php selected( $layout, 'layout-1' ); ?>>
                        <?php esc_html_e( 'Layout 1 - 2 columns', WP_MANGA_TEXTDOMAIN ); ?>
                    </option>
                    <option value="layout-2" <?php selected( $layout, 'layout-2' ); ?>>
                        <?php esc_html_e( 'Layout 2 - 6 columns', WP_MANGA_TEXTDOMAIN ); ?>
                    </option>
                </select>
            </label>
        </p>
        <p>
            <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'show_manga_counts' ); ?>" name="<?php echo $this->get_field_name( 'show_manga_counts' ); ?>" <?php checked( $show_manga_counts, 'true' ); ?> value="true">
            <label for="<?php echo $this->get_field_id( 'show_manga_counts' ); ?>">
                <?php esc_html_e( 'Show manga counts', WP_MANGA_TEXTDOMAIN ); ?>
            </label>
        </p>

        <?php

    }

    function update( $new_instance, $old_instance ) {

        $instance = $old_instance;

        $instance['title'] = isset( $new_instance['title'] ) ? $new_instance['title'] : '';
        $instance['exclude_author'] = isset( $new_instance['exclude_author'] ) ? $new_instance['exclude_author'] : '';
        $instance['show_manga_counts'] = isset( $new_instance['show_manga_counts'] ) ? $new_instance['show_manga_counts'] : 'false';
        $instance['layout'] = isset( $new_instance['layout'] ) ? $new_instance['layout'] : 'layout-1';
        $instance['data'] = isset( $new_instance['data'] ) ? $new_instance['data'] : 'wp-manga-author';        

        return $instance;
    }

}

add_action( 'widgets_init', function(){
    register_widget( 'Manga_Authors_Widget' );
});

?>
