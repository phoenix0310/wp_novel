<?php

	/**
	 * MadaraShortcodePostSlider
	 */
	class MadaraShortcodePostSlider extends MadaraShortcode {
		public function __construct( $params = null, $content = '' ) {
			parent::__construct( 'manga_post_slider', $params, $content );
		}

		/**
		 * @param $atts
		 * @param $content
		 *
		 * @return string
		 */
		public function renderShortcode( $atts, $content ) {

			$id        = 'c-post-slider-' . rand( 1, 999 );
			$style     = isset( $atts['style'] ) && $atts['style'] != '' ? $atts['style'] : 1;
			$cats      = isset( $atts['cats'] ) && $atts['cats'] != '' ? $atts['cats'] : '';
			$tags      = isset( $atts['tags'] ) && $atts['tags'] != '' ? $atts['tags'] : '';
			$orderby   = isset( $atts['orderby'] ) && $atts['orderby'] != '' ? $atts['orderby'] : 'latest';
			$count     = isset( $atts['count'] ) && $atts['count'] != '' ? $atts['count'] : '5';
			$order     = isset( $atts['order'] ) && $atts['order'] != '' ? $atts['order'] : 'DESC';
			$ids       = isset( $atts['ids'] ) && $atts['ids'] != '' ? $atts['ids'] : '';
			$post_type = isset( $atts['post_type'] ) && $atts['post_type'] != '' ? $atts['post_type'] : 'post';
			$manga_type = isset( $atts['manga_type'] ) && $atts['manga_type'] != '' ? $atts['manga_type'] : '';
			$number    = isset( $atts['number'] ) && $atts['number'] != '' ? $atts['number'] : '2';
			$time      = isset( $atts['time'] ) && $atts['time'] != '' ? $atts['time'] : 'all';
			$autoplay      = isset( $atts['autoplay'] ) && $atts['autoplay'] != '' ? $atts['autoplay'] : false;
			$thumb = isset($atts['use_banner']) ? 1 : 0;

			$thumb_size = array( 642, 320 );

			if ( $orderby == 'view' ) {
				$orderby = 'most_viewed';
			} else if ( $orderby == 'random' ) {
				$orderby = 'rand';
			} else if ( $orderby == 'comment' ) {
				$orderby = 'most_commented';
			} else if ( $orderby == 'title' ) {
				$orderby = 'title';
			} else if ( $orderby == 'input' ) {
				$orderby = 'post__in';
			} else {
				$orderby = 'date';
			}
			
			$args = array(
				'categories' => $cats,
				'tags'       => $tags,
				'ids'        => $ids,
				'post_type'  => $post_type,
				'timerange'  => $time
			);
			
			if($post_type == 'wp-manga'){
				if($manga_type != ''){
					$args['meta_query_value'] = $manga_type;
					$args['key'] = '_wp_manga_chapter_type';
				}

				$args['category_taxonomy'] = 'wp-manga-genre';
				$args['tax_taxonomy'] = 'wp-manga-tag';
			}

			if(isset($atts['category_taxonomy'])){
				$args['category_taxonomy'] = $atts['category_taxonomy'];
			}
			
			if(isset($atts['tax_taxonomy'])){
				$args['tax_taxonomy'] = $atts['tax_taxonomy'];
			}

			if($orderby == 'most_viewed'){
				switch($time){
					case 'day':
						$args['viewed_meta_key'] = '_wp_manga_day_views_value';
						break;
					case 'week':
						$args['viewed_meta_key'] = '_wp_manga_week_views_value';
						break;
					case 'month':
						$args['viewed_meta_key'] = '_wp_manga_month_views_value';
						break;
					case 'year':
						$args['viewed_meta_key'] = '_wp_manga_year_views_value';
						break;
					case 'all':
					default:
						$args['viewed_meta_key'] = '_wp_manga_views';
						break;
				}
			}

			$shortcode_query = App\Models\Database::getPosts( $count, $order, 1, $orderby, $args );

			switch ( $style ) {
				case '1':
					$wrap_class  = 'manga-slider';
					$inner_class = 'slider__container';
					$data_style  = 'style-1';
					$classes     = 'style-1 no-padding';
					break;
				case '2':
					$wrap_class  = 'manga-slider';
					$inner_class = 'slider__container';
					$data_style  = 'style-2';
					$classes     = 'style-2';
					break;
				case '3':
					$wrap_class  = 'manga-slider';
					$inner_class = 'slider__container';
					$data_style  = 'style-1';
					$classes     = 'style-1 no-full-width style-3';
					break;
				case '4':
					$wrap_class  = 'related-post manga-slider';
					$inner_class = 'related__container slider__container row c-row';
					$data_style  = 'style-4';
					$classes     = 'style-4';
					break;
			}
			ob_start();

			if ( $shortcode_query->have_posts() ) {
				?>


                <div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $wrap_class ) . ' ' . esc_attr( $classes ); ?>" data-style="<?php echo esc_attr( $data_style ); ?>" data-count="<?php echo esc_attr( $number ); ?>" <?php if($autoplay && $autoplay == '1'){?>data-autoplay="1"<?php }?>>

                    <div class="<?php echo esc_attr( $inner_class ); ?>" role="toolbar">

						<?php
							while ( $shortcode_query->have_posts() ) {

								$shortcode_query->the_post(); ?>

								<?php if ( $style != 4 ) { ?>

                                    <div class="slider__item <?php echo has_post_thumbnail() ? '' : 'no-thumb'; ?>">

										<?php if ( has_post_thumbnail() ) { ?>
                                            <div class="slider__thumb">
                                                <div class="slider__thumb_item">
                                                    <a href="<?php echo get_the_permalink() ?>">
														<?php 
														if(!$thumb){
															echo madara_thumbnail( $thumb_size );
														} else {
															// use manga banner
															echo '<img src="' . get_post_meta(get_the_ID(), 'manga_banner', true) . '" alt="' . get_the_title() . '"/>';
														}
															 
															?>
                                                        <div class="slider-overlay"></div>
                                                    </a>
                                                </div>
                                            </div>
										<?php } ?>

                                        <div class="slider__content">
                                            <div class="slider__content_item">
                                                <div class="post-title font-title">
                                                    <h4>
                                                        <a href="<?php echo get_the_permalink() ?>"><?php echo get_the_title() ?></a>
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

								<?php } else { ?>

                                    <div class="col-sm-6 col-md-3 related__item <?php echo has_post_thumbnail() ? '' : 'no-thumb'; ?>">

										<?php if ( has_post_thumbnail() ) { ?>
                                            <div class="related__thumb">
                                                <div class="related__thumb_item">
                                                    <a href="<?php echo get_the_permalink() ?>">
														<?php echo madara_thumbnail( $thumb_size ); ?>
                                                        <div class="related-overlay"></div>
                                                    </a>
                                                </div>
                                            </div>
										<?php } ?>

                                        <div class="related__content">
                                            <div class="related__content_item">
                                                <div class="post-title font-title">
                                                    <h5>
                                                        <a href="<?php echo get_the_permalink() ?>"><?php echo get_the_title() ?></a>
                                                    </h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

								<?php } ?>

							<?php }//end while
							wp_reset_postdata();
						?>

                    </div>
                </div>


				<?php
			}
			$output = ob_get_contents();
			ob_end_clean();

			return $output;
		}

	}

	$madara_button = new MadaraShortcodePostSlider();

	/**
	 * add button to visual composer
	 */
	add_action( 'after_setup_theme', 'reg_manga_post_slider' );

	function reg_manga_post_slider() {
		if ( function_exists( 'vc_map' ) ) {
			$params = array(
				array(
					"admin_label" => true,
					"type"        => "dropdown",
					"heading"     => esc_html__( "Style", 'madara-shortcode' ),
					"param_name"  => "style",
					"std"         => 1,
					"value"       => array(
						esc_html__( "Style 1", 'madara-shortcode' ) => 1,
						esc_html__( "Style 2", 'madara-shortcode' ) => 2,
						esc_html__( "Style 3", 'madara-shortcode' ) => 3,
						esc_html__( "Style 4", 'madara-shortcode' ) => 4,
					),
					"description" => esc_html__( "condition to query items", 'madara-shortcode' )
				),
				array(
					"admin_label" => true,
					"type"        => "textfield",
					"heading"     => esc_html__( "Count", 'madara-shortcode' ),
					"param_name"  => "count",
					"value"       => "",
					"description" => esc_html__( 'number of items to query', 'madara-shortcode' )
				),

				array(
					"admin_label" => true,
					"type"        => "dropdown",
					"heading"     => esc_html__( "Number of Post to show", 'madara-shortcode' ),
					"param_name"  => "number",
					"std"         => '2',
					"description" => esc_html__( 'Number of Post to show', 'madara-shortcode' ),
					"value"       => array(
						esc_html__( "1", 'madara-shortcode' ) => "1",
						esc_html__( "2", 'madara-shortcode' ) => "2",
						esc_html__( "3", 'madara-shortcode' ) => "3",
						esc_html__( "4", 'madara-shortcode' ) => "4",
					),
				),

				array(
					"admin_label" => true,
					"type"        => "dropdown",
					"heading"     => esc_html__( "Oder By", 'madara-shortcode' ),
					"param_name"  => "orderby",
					"value"       => array(
						esc_html__( "Latest", 'madara-shortcode' )                                         => 'latest',
						esc_html__( "Most viewed", 'madara-shortcode' )                                    => 'view',
						esc_html__( "Most commented", 'madara-shortcode' )                                 => 'comment',
						esc_html__( "Title", 'madara-shortcode' )                                          => "title",
						esc_html__( "Input(only available when using ids parameter)", 'madara-shortcode' ) => "input",
						esc_html__( "Random", 'madara-shortcode' )                                         => "random"
					),
					"description" => esc_html__( "condition to query items", 'madara-shortcode' )
				),

				array(
					"admin_label" => true,
					"type"        => "dropdown",
					"heading"     => esc_html__( "Time Range to query posts", 'madara-shortcode' ),
					"param_name"  => "time",
					"std"         => 'all',
					"description" => esc_html__( 'Time Range to query posts', 'madara-shortcode' ),
					"value"       => array(
						esc_html__( "All Time", 'madara-shortcode' ) => "all",
						esc_html__( "1 Day", 'madara-shortcode' )    => "day",
						esc_html__( "1 Week", 'madara-shortcode' )   => "week",
						esc_html__( "1 Month", 'madara-shortcode' )  => "month",
						esc_html__( "1 Year", 'madara-shortcode' )   => "year",
					),
					'dependency'  => array(
						'element' => 'orderby',
						'value'   => array( 'view' ),
					),
				),

				array(
					"admin_label" => true,
					"type"        => "dropdown",
					"heading"     => esc_html__( "Order", 'madara-shortcode' ),
					"param_name"  => "order",
					"value"       => array(
						esc_html__( "Descending", 'madara-shortcode' ) => "DESC",
						esc_html__( "Ascending", 'madara-shortcode' )  => "ASC"
					),
				),

				array(
					"admin_label" => true,
					"type"        => "textfield",
					"heading"     => esc_html__( "Categories", 'madara-shortcode' ),
					"param_name"  => "cats",
					"description" => esc_html__( "list of categories (ID) to query items from, separated by a comma.", 'madara-shortcode' )
				),

				array(
					"admin_label" => true,
					"type"        => "textfield",
					"heading"     => esc_html__( "Tags", 'madara-shortcode' ),
					"param_name"  => "tags",
					"value"       => "",
					"description" => esc_html__( 'list of tags slug to query items from, separated by a comma. For example: tag-1, tag-2, tag-3', 'madara-shortcode' )
				),
				array(
					"type"        => "textfield",
					"heading"     => esc_html__( "IDs", 'madara-shortcode' ),
					"param_name"  => "ids",
					"value"       => "",
					"description" => esc_html__( 'list of post IDs to query, separated by a comma. If this value is not empty, cats, tags and featured are omitted', 'madara-shortcode' )
				),
			);
			vc_map( array(
				'name'     => esc_html__( 'Madara Posts Slider', 'madara-shortcode' ),
				'base'     => 'manga_post_slider',
				'icon'     => CT_SHORTCODE_PLUGIN_URL . '/shortcodes/img/c_post_slider.png',
				'category' => esc_html__( 'Madara Shortcodes', 'madara-shortcode' ),
				'params'   => $params,
			) );
		}
	}
	
	function wp_manga_gutenberg_manga_sliders_block() {
		wp_register_script(
			'wp_manga_gutenberg_manga_sliders_block',
			plugins_url( 'gutenberg/manga-sliders.js', __FILE__ ),
			array( 'wp-blocks', 'wp-element' )
		);

		if(function_exists('register_block_type')){
		register_block_type( 'wp-manga/gutenberg-manga-sliders-block', array(
			'editor_script' => 'wp_manga_gutenberg_manga_sliders_block',
		) );
		}
	}
	add_action( 'init', 'wp_manga_gutenberg_manga_sliders_block' );