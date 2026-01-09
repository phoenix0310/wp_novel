<?php

	/**
	 * MadaraShortcodeMangaChapters
	 */
	class MadaraShortcodeMangaChapters extends MadaraShortcode {
		public function __construct( $params = null, $content = '' ) {
			parent::__construct( 'manga_chapters', $params, $content );
		}

		/**
		 * @param $atts
		 * @param $content
		 *
		 * @return string
		 */
		public function renderShortcode( $atts, $content ) {
			global $wp_manga_database, $wp_manga_functions;
										
			if(!isset($wp_manga_database)) return;

			$id            = isset( $atts['id'] ) && is_numeric( $atts['id'] ) ? $atts['id'] : ($wp_manga_functions->is_manga_single() ? get_the_ID() : null);
			
			$style = isset( $atts['style'] ) && is_numeric( $atts['style'] ) ? $atts['style'] : 1;
			$count = isset( $atts['count'] ) && is_numeric( $atts['count'] ) ? $atts['count'] : 5;
			$cols = isset( $atts['cols'] ) && is_numeric( $atts['cols'] ) ? $atts['cols'] : 1; // 1, 2, 3, 4
            $border = isset( $atts['border'] ) && is_numeric( $atts['border'] ) ? $atts['border'] : 0;
			$wp_manga_functions = madara_get_global_wp_manga_functions();
            $show_chapter_extend_name = isset( $atts['extend_name'] ) && is_numeric( $atts['extend_name'] ) ? 1 : 0;

			ob_start();

			if ( $id ) {

				?>

                <div class="page-listing-item shortcode-manga-chapters chapter-listing-<?php esc_attr_e($style);?> cols-<?php esc_attr_e($cols);?> border-<?php esc_attr_e($border);?>">

                    <div class="row">

                        <div class="col-xs-12 col-md-12">

                            <div id="manga-item-<?php echo esc_attr( $id ); ?>" class="manga-detail">
								
                                <div class="list-chapters listing-chapters_wrap">
									<?php
										
			
										$sort_setting = $wp_manga_database->get_sort_setting();

										$sort_by    = $sort_setting['sortBy'];
										$sort_order = $sort_setting['sort'];
										
										$chapters = $wp_manga_functions->get_latest_chapters( $id, null, $count, 0, $sort_by, $sort_order );
										if ( $chapters ) {
											?>
											<ul class="main version-chap">
											<?php
											foreach ( $chapters as $c_key => $chapter ) {
												$style = $wp_manga_functions->get_reading_style();

												$manga_link = $wp_manga_functions->build_chapter_url( $id, $chapter, $style );
												
                                                if($show_chapter_extend_name){
                                                    $c_extend_name = madara_get_global_wp_manga_functions()->filter_extend_name( $chapter['chapter_name_extend'] );
                                                } else {
                                                    $c_extend_name = '';
                                                }
                                                
												$time_diff     = $wp_manga_functions->get_time_diff( $chapter['date'] );
												$time_diff     = apply_filters( 'madara_archive_chapter_date', '<i>' . $time_diff . '</i>', $chapter['chapter_id'], $chapter['date'], $manga_link );
												
												?>

												<?php if ( isset( $chapter['chapter_name'] ) ) { ?>
													<li class="wp-manga-chapter <?php echo apply_filters('wp_manga_chapter_item_class','', $chapter, $id);?>">
														<?php do_action('wp_manga_before_chapter_name',$chapter, $id);?>
														<a href="<?php echo esc_url( $manga_link ); ?>">
															<?php echo wp_kses_post( $chapter['chapter_name'] . $c_extend_name ) ?>
														</a>

														<?php if ( $time_diff ) { ?>
															<span class="chapter-release-date">
																<?php echo wp_kses_post( $time_diff ); ?>
															</span>
														<?php } ?>
														
														<?php do_action('wp_manga_after_chapter_name',$chapter, $id);?>

													</li>
												<?php } ?>

											<?php } ?>
											</ul>
											<?php
										}
									?>
                                </div>
								
                            </div>

                        </div>
                    </div>

                </div>

				<?php
			}
			$output = ob_get_contents();
			ob_end_clean();

			return $output;
		}

	}

	$madara_button = new MadaraShortcodeMangaChapters();

	/**
	 * add button to visual composer
	 */
	add_action( 'after_setup_theme', 'reg_manga_chapters_shortcode' );

	function reg_manga_chapters_shortcode() {
		if ( function_exists( 'vc_map' ) ) {
			$params = array(

				array(
					"type"        => "textfield",
					"heading"     => esc_html__( "Manga ID", 'madara-shortcode' ),
					"param_name"  => "id",
					"value"       => "",
					"description" => esc_html__( 'Enter ID of manga to display.', 'madara-shortcode' )
				)
			);
			
			vc_map( array(
				'name'     => esc_html__( 'Madara Manga Chapters', 'madara-shortcode' ),
				'base'     => 'manga_chapters',
				'icon'     => CT_SHORTCODE_PLUGIN_URL . '/shortcodes/img/c_post_slider.png',
				'category' => esc_html__( 'Madara Shortcodes', 'madara-shortcode' ),
				'params'   => $params,
			) );
		}
	}
	
	function wp_manga_gutenberg_manga_chapters_block() {
		wp_register_script(
			'wp_manga_gutenberg_manga_chapters_block',
			plugins_url( 'gutenberg/manga-chapters.js', __FILE__ ),
			array( 'wp-blocks', 'wp-element' )
		);
		
		if(function_exists('register_block_type')){
		register_block_type( 'wp-manga/gutenberg-manga-chapters-block', array(
			'editor_script' => 'wp_manga_gutenberg_manga_chapters_block',
		) );
		}
	}
	add_action( 'init', 'wp_manga_gutenberg_manga_chapters_block' );
