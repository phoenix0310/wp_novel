<?php

	/**
	 * Top_rating widget class
	 */
	class WP_MANGA_POPULAR_SLIDER extends WP_Widget {
		function __construct() {
			$widget_ops = array(
				'classname'   => 'manga-widget widget-manga-popular-slider',
				'description' => esc_html__( 'Display mangas in a slider', WP_MANGA_TEXTDOMAIN )
			);
			parent::__construct( 'manga-popular-slider', esc_html__( 'WP Manga: Manga Popular Slider', WP_MANGA_TEXTDOMAIN ), $widget_ops );
			$this->alt_option_name = 'widget_manga_popular_slider';
		}

		function form( $instance ) {
			$title          = ! empty( $instance['title'] ) ? $instance['title'] : '';
			$number_of_post = ! empty( $instance['number_of_post'] ) ? $instance['number_of_post'] : '5';
			$number_to_show = ! empty( $instance['number_to_show'] ) ? $instance['number_to_show'] : '2';
			$genre          = ! empty( $instance['genre'] ) ? $instance['genre'] : '';
			$author         = ! empty( $instance['author'] ) ? $instance['author'] : '';
			$artist         = ! empty( $instance['artist'] ) ? $instance['artist'] : '';
			$release        = ! empty( $instance['release'] ) ? $instance['release'] : '';
			$order_by       = ! empty( $instance['order_by'] ) ? $instance['order_by'] : 'latest';
			$order          = ! empty( $instance['order'] ) ? $instance['order'] : 'desc';
			$style          = ! empty( $instance['style'] ) ? $instance['style'] : 'style-1';
			$manga_type          = ! empty( $instance['manga_type'] ) ? $instance['manga_type'] : '';
			$manga_tags          = ! empty( $instance['manga_tags'] ) ? $instance['manga_tags'] : '';
			$autoplay          = ! empty( $instance['autoplay'] ) ? $instance['autoplay'] : 0;
			$timerange       = ! empty( $instance['timerange'] ) ? $instance['timerange'] : 'all';

			$order_by_list = array(
				'latest'    => esc_html__( 'Latest', WP_MANGA_TEXTDOMAIN ),
				'alphabet'  => esc_html__( 'Alphabet', WP_MANGA_TEXTDOMAIN ),
				'rating'    => esc_html__( 'Ratings', WP_MANGA_TEXTDOMAIN ),
				'trending'  => esc_html__( 'Trending', WP_MANGA_TEXTDOMAIN ),
				'views'     => esc_html__( 'Views', WP_MANGA_TEXTDOMAIN ),
				'new-manga' => esc_html__( 'New Manga', WP_MANGA_TEXTDOMAIN ),
				'random'    => esc_html__( 'Random', WP_MANGA_TEXTDOMAIN )
			);

			$trending_timerange = array(
				'all' => esc_html__( 'All Time', WP_MANGA_TEXTDOMAIN ),
				'year' => esc_html__( '1 year', WP_MANGA_TEXTDOMAIN ),
				'month' => esc_html__( '1 month', WP_MANGA_TEXTDOMAIN ),
				'week' => esc_html__( '1 week', WP_MANGA_TEXTDOMAIN ),
				'day' => esc_html__( '1 day', WP_MANGA_TEXTDOMAIN )
			);

			$order_list = array(
				'desc' => esc_html__( 'DESC', WP_MANGA_TEXTDOMAIN ),
				'asc'  => esc_html__( 'ASC', WP_MANGA_TEXTDOMAIN )
			);

			?>

            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>"> <?php echo esc_html__( 'Title', WP_MANGA_TEXTDOMAIN ); ?>
                    : </label>
                <input class="widefat" type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>">
            </p>

            <p>
                <label for="<?php echo $this->get_field_id( 'number_of_post' ); ?>"><?php echo esc_html__( 'Number of posts to query', WP_MANGA_TEXTDOMAIN ); ?>
                    : </label>
                <input class="widefat" type="number" id="<?php echo $this->get_field_id( 'number_of_post' ); ?>" name="<?php echo $this->get_field_name( 'number_of_post' ); ?>" value="<?php echo esc_attr( $number_of_post ) ?>">
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'number_to_show' ); ?>"><?php echo esc_html__( 'Number of Post to show', WP_MANGA_TEXTDOMAIN ); ?>
                    : </label>
                <select class="widefat" id="<?php echo $this->get_field_id( 'number_to_show' ); ?>" name="<?php echo $this->get_field_name( 'number_to_show' ); ?>">
					<option value="5" <?php selected($number_to_show, 5, true); ?>><?php echo esc_html__( '5', WP_MANGA_TEXTDOMAIN ); ?></option>
					<option value="4" <?php selected($number_to_show, 4, true); ?>><?php echo esc_html__( '4', WP_MANGA_TEXTDOMAIN ); ?></option>
                    <option value="3" <?php selected($number_to_show, 3, true); ?>><?php echo esc_html__( '3', WP_MANGA_TEXTDOMAIN ); ?></option>
					<option value="2" <?php selected($number_to_show, 2, true); ?>><?php echo esc_html__( '2', WP_MANGA_TEXTDOMAIN ); ?></option>                    
                    <option value="1" <?php selected($number_to_show, 1, true); ?>><?php echo esc_html__( '1', WP_MANGA_TEXTDOMAIN ); ?></option>
                </select>
            </p>
			
			<p>
                <label for="<?php echo $this->get_field_id( 'manga_type' ); ?>"><?php echo esc_html__( 'Manga Type', WP_MANGA_TEXTDOMAIN ); ?>
                    : </label>
                <select class="widefat" id="<?php echo $this->get_field_id( 'manga_type' ); ?>" name="<?php echo $this->get_field_name( 'manga_type' ); ?>">
					<option value="" <?php echo $manga_type == '' ? 'selected="selected"' : ''; ?>><?php echo esc_html__( 'All', WP_MANGA_TEXTDOMAIN ); ?></option>
                    <option value="manga" <?php echo $manga_type == 'manga' ? 'selected="selected"' : ''; ?>><?php echo esc_html__( 'Comic (Manga) only', WP_MANGA_TEXTDOMAIN ); ?></option>
                    <option value="text" <?php echo $manga_type == 'text' ? 'selected="selected"' : ''; ?>><?php echo esc_html__( 'Novel (Text) only', WP_MANGA_TEXTDOMAIN ); ?></option>
                    <option value="video" <?php echo $manga_type == 'video' ? 'selected="selected"' : ''; ?>><?php echo esc_html__( 'Drama (Video) only', WP_MANGA_TEXTDOMAIN ); ?></option>
                </select>
            </p>

            <p>
                <label for="<?php echo $this->get_field_id( 'genre' ); ?>"> <?php echo esc_html__( 'Genre', WP_MANGA_TEXTDOMAIN ); ?>
                    : </label>
                <input class="widefat" type="text" id="<?php echo $this->get_field_id( 'genre' ); ?>" name="<?php echo $this->get_field_name( 'genre' ); ?>" value="<?php echo esc_attr( $genre ); ?>">
				<span class="description"><?php echo esc_html__('Slugs of manga genres, separated by a comma', WP_MANGA_TEXTDOMAIN);?></span>
            </p>

            <p>
                <label for="<?php echo $this->get_field_id( 'author' ); ?>"> <?php echo esc_html__( 'Author', WP_MANGA_TEXTDOMAIN ); ?>
                    : </label>
                <input class="widefat" type="text" id="<?php echo $this->get_field_id( 'author' ); ?>" name="<?php echo $this->get_field_name( 'author' ); ?>" value="<?php echo esc_attr( $author ); ?>">
				<span class="description"><?php echo esc_html__('Slugs of manga authors, separated by a comma', WP_MANGA_TEXTDOMAIN);?></span>
            </p>

            <p>
                <label for="<?php echo $this->get_field_id( 'artist' ); ?>"> <?php echo esc_html__( 'Artist', WP_MANGA_TEXTDOMAIN ); ?>
                    : </label>
                <input class="widefat" type="text" id="<?php echo $this->get_field_id( 'artist' ); ?>" name="<?php echo $this->get_field_name( 'artist' ); ?>" value="<?php echo esc_attr( $artist ); ?>">
				<span class="description"><?php echo esc_html__('Slugs of manga artists, separated by a comma', WP_MANGA_TEXTDOMAIN);?></span>
            </p>

            <p>
                <label for="<?php echo $this->get_field_id( 'release' ); ?>"> <?php echo esc_html__( 'Release Year', WP_MANGA_TEXTDOMAIN ); ?>
                    : </label>
                <input class="widefat" type="text" id="<?php echo $this->get_field_id( 'release' ); ?>" name="<?php echo $this->get_field_name( 'release' ); ?>" value="<?php echo esc_attr( $release ); ?>">
				<span class="description"><?php echo esc_html__('Slugs of manga releases, separated by a comma', WP_MANGA_TEXTDOMAIN);?></span>
            </p>
			
			 <p>
                <label for="<?php echo $this->get_field_id( 'manga_tags' ); ?>"> <?php echo esc_html__( 'Manga Tags', WP_MANGA_TEXTDOMAIN ); ?>
                    : </label>
                <input class="widefat" type="text" id="<?php echo $this->get_field_id( 'manga_tags' ); ?>" name="<?php echo $this->get_field_name( 'manga_tags' ); ?>" value="<?php echo esc_attr( $manga_tags ); ?>">
				<span class="description"><?php echo esc_html__('Slugs of manga tags, separated by a comma', WP_MANGA_TEXTDOMAIN);?></span>
            </p>

            <p>
                <label for="<?php echo $this->get_field_id( 'order_by' ); ?>"><?php echo esc_html__( 'Order by', WP_MANGA_TEXTDOMAIN ); ?>
                    : </label>
                <select class="widefat" id="<?php echo $this->get_field_id( 'order_by' ); ?>" name="<?php echo $this->get_field_name( 'order_by' ); ?>">
					<?php
						foreach ( $order_by_list as $value => $title ) {
							$selected = $order_by == $value ? 'selected' : '';
							?>
                            <option value="<?php echo esc_attr( $value ); ?>" <?php echo $selected; ?>><?php echo $title ?></option>
							<?php
						}
					?>
                </select>
            </p>

			<p>
                <label for="<?php echo $this->get_field_id( 'timerange' ); ?>"><?php echo esc_html__( 'Trending by', WP_MANGA_TEXTDOMAIN ); ?>
                    : </label>
                <select class="widefat" id="<?php echo $this->get_field_id( 'timerange' ); ?>" name="<?php echo $this->get_field_name( 'timerange' ); ?>">
					<?php
						foreach ( $trending_timerange as $value => $title ) {
							$selected = $timerange == $value ? 'selected' : '';
							?>
                            <option value="<?php echo esc_attr( $value ); ?>" <?php echo $selected; ?>><?php echo $title ?></option>
							<?php
						}
					?>
                </select>
				<span class="description"><?php echo esc_html__('Applied for Order By > Trending', WP_MANGA_TEXTDOMAIN);?></span>
            </p>
			
            <p>
                <label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php echo esc_html__( 'Order', WP_MANGA_TEXTDOMAIN ); ?>
                    : </label>
                <select class="widefat" id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>">
					<?php
						foreach ( $order_list as $value => $title ) {
							$selected = $order == $value ? 'selected' : '';
							?>
                            <option value="<?php echo esc_attr( $value ); ?>" <?php echo $selected; ?>><?php echo $title ?></option>
							<?php
						}
					?>
                </select>
            </p>
			<?php
			$style_1 = $style == 'style-1' ? 'selected="selected"' : '';
			$style_2 = $style == 'style-2' ? 'selected="selected"' : '';
			?>
            <p>
                <label for="<?php echo $this->get_field_id( 'style' ); ?>"><?php echo esc_html__( 'Style', WP_MANGA_TEXTDOMAIN ); ?>
                    : </label>
                <select class="widefat" id="<?php echo $this->get_field_id( 'style' ); ?>" name="<?php echo $this->get_field_name( 'style' ); ?>">

                    <option value="style-1" <?php echo $style_1; ?>><?php echo esc_html__( 'Style 1', WP_MANGA_TEXTDOMAIN ); ?></option>
                    <option value="style-2" <?php echo $style_2; ?>><?php echo esc_html__( 'Style 2', WP_MANGA_TEXTDOMAIN ); ?></option>
                </select>
            </p>
			<?php
			$autoplay_1 = $autoplay == 1 ? 'selected="selected"' : '';
			$autoplay_0 = $autoplay == 0 ? 'selected="selected"' : '';
			?>
            <p>
                <label for="<?php echo $this->get_field_id( 'autoplay' ); ?>"><?php echo esc_html__( 'Autoplay', WP_MANGA_TEXTDOMAIN ); ?>
                    : </label>
                <select class="widefat" id="<?php echo $this->get_field_id( 'autoplay' ); ?>" name="<?php echo $this->get_field_name( 'autoplay' ); ?>">

                    <option value="1" <?php echo $autoplay_1; ?>><?php echo esc_html__( 'Yes', WP_MANGA_TEXTDOMAIN ); ?></option>
                    <option value="0" <?php echo $autoplay_0; ?>><?php echo esc_html__( 'No', WP_MANGA_TEXTDOMAIN ); ?></option>
                </select>
            </p>
			<?php
		}

		function update( $new_instance, $old_instance ) {
			$instance = $old_instance;

			$instance['title']          = strip_tags( $new_instance['title'] );
			$instance['number_of_post'] = strip_tags( $new_instance['number_of_post'] );
			$instance['number_to_show'] = strip_tags( $new_instance['number_to_show'] );
			$instance['genre']          = strip_tags( $new_instance['genre'] );
			$instance['author']         = strip_tags( $new_instance['author'] );
			$instance['artist']         = strip_tags( $new_instance['artist'] );
			$instance['release']        = strip_tags( $new_instance['release'] );
			$instance['order_by']       = strip_tags( $new_instance['order_by'] );
			$instance['order']          = strip_tags( $new_instance['order'] );
			$instance['timerange']        = strip_tags( $new_instance['timerange'] );
			$instance['style']          = strip_tags( $new_instance['style'] );
			$instance['manga_type']          = strip_tags( $new_instance['manga_type'] );
			$instance['manga_tags']          = strip_tags( $new_instance['manga_tags'] );
			$instance['autoplay']          = strip_tags( $new_instance['autoplay'] );

			return $instance;
		}

		function widget( $args, $instance ) {

			global $wp_manga_functions, $wp_manga_template, $wp_manga;

			if ( ! isset( $args['widget_id'] ) ) {
				$args['widget_id'] = $this->id;
			}

			ob_start();
			extract( $args );
			$title          = ! empty( $instance['title'] ) ? $instance['title'] : '';
			$number_of_post = ! empty( $instance['number_of_post'] ) ? $instance['number_of_post'] : '5';
			$number_to_show = ! empty( $instance['number_to_show'] ) ? $instance['number_to_show'] : '2';
			$order_by       = ! empty( $instance['order_by'] ) ? $instance['order_by'] : '';
			$order          = ! empty( $instance['order'] ) ? $instance['order'] : '';
			$style          = ! empty( $instance['style'] ) ? $instance['style'] : 'style-1';
			$manga_type          = ! empty( $instance['manga_type'] ) ? $instance['manga_type'] : '';
			$autoplay          = ! empty( $instance['autoplay'] ) ? $instance['autoplay'] : 0;
			$timerange = ! empty( $instance['timerange'] ) ? $instance['timerange'] : 'all';

			if ( $order_by == 'random' ) {
				$order_by = 'rand';
			}

			$query_args = array(
				'posts_per_page' => $number_of_post,
				'order'          => $order,
				'orderby'        => $order_by
			);

			if($timerange != 'week'){
				// the "week" time range will be used if "timerange" arg is missing
				$query_args['timerange'] = $timerange;
			}
			
			if($manga_type != ''){
				$query_args['meta_query_value'] = $manga_type;
				$query_args['key'] = '_wp_manga_chapter_type';
			}

			$genre = ! empty( $instance['genre'] ) ? $instance['genre'] : '';
			if ( $genre && '' != $genre ) {
				$query_args['tax_query']['relation'] = 'OR';
				$genre_array                         = explode( ',', $genre );
				foreach ( $genre_array as $g ) {
					$query_args['tax_query'][] = array(
						'taxonomy' => 'wp-manga-genre',
						'terms'    => trim($g),
						'field'    => 'slug',
					);
				}
			}

			$author = ! empty( $instance['author'] ) ? $instance['author'] : '';
			if ( $author && '' != $author ) {
				$query_args['tax_query']['relation'] = 'OR';
				$author_array                        = explode( ',', $author );
				foreach ( $author_array as $au ) {
					$query_args['tax_query'][] = array(
						'taxonomy' => 'wp-manga-author',
						'terms'    => trim($au),
						'field'    => 'slug',
					);
				}
			}

			$artist = ! empty( $instance['artist'] ) ? $instance['artist'] : '';
			if ( $artist && '' != $artist ) {
				$query_args['tax_query']['relation'] = 'OR';
				$artist_array                        = explode( ',', $artist );
				foreach ( $artist_array as $ar ) {
					$query_args['tax_query'][] = array(
						'taxonomy' => 'wp-manga-artist',
						'terms'    => trim($ar),
						'field'    => 'slug',
					);
				}
			}

			$release = ! empty( $instance['release'] ) ? $instance['release'] : '';
			if ( $release && '' != $release ) {
				$query_args['tax_query']['relation'] = 'OR';
				$release_array                       = explode( ',', $release );
				foreach ( $release_array as $r ) {
					$query_args['tax_query'][] = array(
						'taxonomy' => 'wp-manga-release',
						'terms'    => trim($r),
						'field'    => 'slug',
					);
				}
			}
			
			$manga_tags = ! empty( $instance['manga_tags'] ) ? $instance['manga_tags'] : '';
			if ( $manga_tags && '' != $manga_tags ) {
				$query_args['tax_query']['relation'] = 'OR';
				$manga_tags_array = explode( ',', $manga_tags );
				foreach ( $manga_tags_array as $r ) {
					$query_args['tax_query'][] = array(
						'taxonomy' => 'wp-manga-tag',
						'terms'    => trim($r),
						'field'    => 'slug',
					);
				}
			}
			
			$queried_posts = $wp_manga->mangabooth_manga_query( $query_args );

			echo $before_widget;

			switch ( $style ) {
				case 'style-1':
					$data_style = 'style-1';
					$classes    = 'style-1';
					break;
				case 'style-2':
					$data_style = 'style-2';
					$classes    = 'style-3';
					break;
			}

			?>

            <div class="popular-slider <?php echo esc_attr( $classes ); ?>" data-autoplay="<?php echo esc_attr($autoplay);?>" data-style="<?php echo esc_attr( $data_style ); ?>" data-count="<?php echo esc_attr( $number_to_show ); ?>">
				<?php
					if ( $title != '' ) {
						echo $before_title . $title . $after_title;
					}
				?>
                <div class="slider__container" role="toolbar">

					<?php while ( $queried_posts->have_posts() ) {
						$queried_posts->the_post();
						$wp_manga_template->load_template( 'widgets/popular-slider/slider', false );
					}
						wp_reset_postdata();
					?>
                </div>
            </div>
			<?php
			wp_reset_postdata();
			echo $after_widget;
		}
	}

	add_action( 'widgets_init', function(){register_widget( "WP_MANGA_POPULAR_SLIDER" );} );
