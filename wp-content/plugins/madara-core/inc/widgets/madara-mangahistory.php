<?php

    namespace App\Plugins\Widgets;

    use App\Madara;
	use App\Models\Database;

    class MangaHistory extends \WP_Widget {

        public function __construct() {

            $widget_options = array(
                'classname' => 'manga-history-widget',
                'description' => esc_html__( 'Show User Manga History', 'madara' ),
            );

            parent::__construct( 'manga-history-id', esc_html__( 'WP Manga : Manga History', 'madara' ), $widget_options );

        }

        function widget( $args, $instance ) {
            if(isset($args['widget_id'])){
			extract( $args );
			$count = isset( $instance['number_of_posts'] ) ? $instance['number_of_posts'] : 3;

			echo wp_kses_post( $before_widget );

			if( !empty( $instance['title'] ) ) {
				echo wp_kses_post( $before_title );
					echo esc_html( $instance['title'] );
				echo wp_kses_post( $after_title );
			}

			?>
			<div class="my-history">
				<!-- -->
				<span class="no-histories" style="display:none"><?php esc_html_e('You don\'t have anything in histories', WP_MANGA_TEXTDOMAIN);?></span>
			</div>
			<?php

			echo wp_kses_post( $after_widget );
			
			?>
			<script type="text/javascript">
			jQuery(document).on('ready', function(){
				jQuery.ajax({
					url: manga.ajax_url,
					type: 'GET',
					data: {
						action: 'guest_histories',
						count: <?php echo $count;?>
					},
					success: function(html){
						if(html && html != "0") {
							jQuery('#<?php echo $args['widget_id'];?> .my-history').html(html);
						} else {
							jQuery('#<?php echo $args['widget_id'];?> .no-histories').show();
						}
					},
					complete: function(e){
						
					}
				});
			});
			</script>
			<?php
            }
        }

        function form( $instance ) {

            $title = isset( $instance['title'] ) ? $instance['title'] : '';
            $number_of_posts = isset( $instance['number_of_posts'] ) ? $instance['number_of_posts'] : 3;

            ?>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
                    <?php esc_html_e( 'Title: ', 'madara' ); ?>
                    <input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title ') ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $title ); ?>" />
                </label>
            </p>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id('number_of_posts' ) ); ?>">
                    <?php esc_html_e( 'Number of manga: ', 'madara' ); ?>
                    <input type="number" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'number_of_posts' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number_of_posts' ) ); ?>" value="<?php echo esc_attr( $number_of_posts ) ?>" max="12" />
                </label>
            </p>
            <?php

        }

        function update( $new_instance, $old_instance ) {

            $instance = $old_instance;

            $instance['title'] = isset( $new_instance['title'] ) ? $new_instance['title'] : '';
            $instance['number_of_posts'] = isset( $new_instance['number_of_posts'] ) ? $new_instance['number_of_posts'] : 3;

            return $instance;

        }

    }

    add_action( 'widgets_init', function(){
        register_widget( 'App\Plugins\Widgets\MangaHistory' );
    });
