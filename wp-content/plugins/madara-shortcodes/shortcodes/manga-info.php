<?php

	/**
	 * MadaraShortcodeMangaInfo
	 */
	class MadaraShortcodeMangaInfo extends MadaraShortcode {
		public function __construct( $params = null, $content = '' ) {
			parent::__construct( 'manga_info', $params, $content );
		}

		/**
		 * @param $atts
		 * @param $content
		 *
		 * @return string
		 */
		public function renderShortcode( $atts, $content ) {

			$id            = isset( $atts['id'] ) && is_numeric( $atts['id'] ) ? $atts['id'] : (get_the_ID() ? get_the_ID() : null);
			$order         = isset( $atts['order'] ) && $atts['order'] != '' ? $atts['order'] : 'ASC';
			$orderby       = isset( $atts['orderby'] ) && $atts['orderby'] != '' ? $atts['orderby'] : 'name';
			$manga_details = isset( $atts['manga_details'] ) && $atts['manga_details'] != '' ? $atts['manga_details'] : '1';
			$manga_title   = isset( $atts['manga_title'] ) && $atts['manga_title'] != '' ? $atts['manga_title'] : '1';
			$list_chapters = isset( $atts['chapters'] ) && $atts['chapters'] != '' ? $atts['chapters'] : '1';
            $title_link = isset( $atts['title_link'] ) && $atts['title_link'] != '' ? $atts['title_link'] : '0';
            $author_link = isset( $atts['author_link'] ) && $atts['author_link'] != '' ? $atts['author_link'] : '1';
            $artist_link = isset( $atts['artist_link'] ) && $atts['artist_link'] != '' ? $atts['artist_link'] : '1';
            $genre_link = isset( $atts['genre_link'] ) && $atts['genre_link'] != '' ? $atts['genre_link'] : '1';
            $tag_link = isset( $atts['tag_link'] ) && $atts['tag_link'] != '' ? $atts['tag_link'] : '1';
			$wp_manga_functions = madara_get_global_wp_manga_functions();
			$alternative        = $wp_manga_functions->get_manga_alternative( $id );
			$rank               = $wp_manga_functions->get_manga_rank( $id );
			$views              = $wp_manga_functions->get_manga_monthly_views( $id );
			$authors            = $wp_manga_functions->get_manga_authors( $id );
			$rate               = $wp_manga_functions->get_total_review( $id );
			$vote               = $wp_manga_functions->get_total_vote( $id );
			$artists            = $wp_manga_functions->get_manga_artists( $id );
			$genres             = $wp_manga_functions->get_manga_genres( $id );
            $tags             = $wp_manga_functions->get_manga_tags( $id );
			$type               = $wp_manga_functions->get_manga_type( $id );
			
			$thumb_size = array( 193, 278 );

			ob_start();
			
			if ( $id ) {

				?>

                <div class="page-listing-item shortcode-manga-chapter">

                    <div class="row">

                        <div class="col-xs-12 col-md-12">

                            <div id="manga-item-<?php echo esc_attr( $id ); ?>" class="manga-detail <?php echo $manga_details == '1' ? '' : 'detail-hide'; ?>">

								<?php if ( $manga_details == '1' ) { ?>

									<?php if ( $manga_title == '1' ) { ?>
                                        <div class="post-title font-title">
                                            <h3>
												<?php madara_manga_title_badges_html( $id, 1 ); ?>
                                                <?php
                                                if($title_link){?>
                                                <a href="<?php echo get_the_permalink( $id ); ?>"><?php echo get_the_title( $id ); ?></a>
                                                <?php } else {?>
                                                <?php echo get_the_title( $id ); ?>
                                                <?php }?>
                                            </h3>
                                        </div>
									<?php } ?>

                                    <div class="manga-info">
                                        <div class="item-thumb">
											<?php
												if ( has_post_thumbnail( $id ) ) {

													$thumb_url  = get_the_post_thumbnail_url( $id );
													$thumb_type = 'gif';
													if ( $thumb_url != '' ) {
														$image_type = substr( $thumb_url, - 3 );
													}

													if ( $thumb_type == $image_type ) {
														$thumb_size = 'full';
													}

													?>
                                                    <a href="<?php get_the_permalink( $id ); ?>" title="<?php get_the_title( $id ); ?>">
														<?php echo madara_thumbnail( $thumb_size, $id ); ?>
                                                    </a>
													<?php
												}
											?>
                                        </div>
                                        <div class="item-summary">

                                            <div class="post-content">
                                                <div class="post-rating">
													<?php echo $wp_manga_functions->manga_rating_display( $id ); ?>
                                                </div>
                                                <div class="post-content_item">
                                                    <div class="summary-heading">
                                                        <h5><?php echo esc_attr__( 'Rating', 'madara-shortcode' ); ?></h5>
                                                    </div>
                                                    <div class="summary-content vote-details">
														<?php echo sprintf( _n( 'Average %1s / %2s out of %3s total vote.', 'Average %1s / %2s out of %3s total votes.', $vote, 'madara-shortcode' ), $rate, '5', $vote ); ?>
                                                    </div>
                                                </div>
                                                <div class="post-content_item">
                                                    <div class="summary-heading">
                                                        <h5>
															<?php echo esc_attr__( 'Rank', 'madara-shortcode' ); ?>
                                                        </h5>
                                                    </div>
                                                    <div class="summary-content">
														<?php echo sprintf( _n( ' %1s, it has %2s monthly view', ' %1s, it has %2s monthly views', $views, 'madara-shortcode' ), $rank, $views ); ?>
                                                    </div>
                                                </div>
												<?php if ( $alternative ): ?>
                                                    <div class="post-content_item">
                                                        <div class="summary-heading">
                                                            <h5>
																<?php echo esc_attr__( 'Alternative', 'madara-shortcode' ); ?>
                                                            </h5>
                                                        </div>
                                                        <div class="summary-content">
															<?php echo $alternative; ?>
                                                        </div>
                                                    </div>
												<?php endif ?>

												<?php if ( $authors ): ?>
                                                    <div class="post-content_item">
                                                        <div class="summary-heading">
                                                            <h5>
																<?php echo esc_attr__( 'Author(s)', 'madara-shortcode' ); ?>
                                                            </h5>
                                                        </div>
                                                        <div class="summary-content">
                                                            <div class="author-content">
																<?php echo $author_link ? wp_kses_post($authors) : strip_tags($authors); ?>
                                                            </div>
                                                        </div>
                                                    </div>
												<?php endif ?>

												<?php if ( $artists ): ?>
                                                    <div class="post-content_item">
                                                        <div class="summary-heading">
                                                            <h5>
																<?php echo esc_attr__( 'Artist(s)', 'madara-shortcode' ); ?>
                                                            </h5>
                                                        </div>
                                                        <div class="summary-content">
                                                            <div class="artist-content">
																<?php echo $artist_link ? wp_kses_post($artists) : strip_tags($artists); ?>
                                                            </div>
                                                        </div>
                                                    </div>
												<?php endif ?>

												<?php if ( $genres ): ?>
                                                    <div class="post-content_item">
                                                        <div class="summary-heading">
                                                            <h5>
																<?php echo esc_attr__( 'Genre(s)', 'madara-shortcode' ); ?>
                                                            </h5>
                                                        </div>
                                                        <div class="summary-content">
                                                            <div class="genres-content full">
																<?php echo $genre_link ? wp_kses_post($genres) : strip_tags($genres); ?>
                                                            </div>
                                                        </div>
                                                    </div>
												<?php endif ?>
                                                
                                                <?php if ( $tags ): ?>
                                                    <div class="post-content_item">
                                                        <div class="summary-heading">
                                                            <h5>
																<?php echo esc_attr__( 'Tag(s)', 'madara-shortcode' ); ?>
                                                            </h5>
                                                        </div>
                                                        <div class="summary-content">
                                                            <div class="tags-content full">
																<?php echo $tag_link ? wp_kses_post($tags) : strip_tags($tags); ?>
                                                            </div>
                                                        </div>
                                                    </div>
												<?php endif ?>

												<?php if ( $type ): ?>
                                                    <div class="post-content_item">
                                                        <div class="summary-heading">
                                                            <h5>
																<?php echo esc_attr__( 'Type', 'madara-shortcode' ); ?>
                                                            </h5>
                                                        </div>
                                                        <div class="summary-content">
															<?php echo $type; ?>
                                                        </div>
                                                    </div>
												<?php endif ?>
                                            </div>


                                        </div>
                                    </div>

								<?php } ?>
								
								<?php if($list_chapters == '1'){?>
                                <div class="list-chapter">
									<?php
										$wp_manga_functions->manga_get_all_chapter( $id, 1, $orderby, $order );
									?>
                                </div>
								<?php }?>
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

	$madara_button = new MadaraShortcodeMangaInfo();

	/**
	 * add button to visual composer
	 */
	add_action( 'after_setup_theme', 'reg_manga_info' );

	function reg_manga_info() {
		if ( function_exists( 'vc_map' ) ) {
			$params = array(

				array(
					"type"        => "textfield",
					"heading"     => esc_html__( "Manga ID", 'madara-shortcode' ),
					"param_name"  => "id",
					"value"       => "",
					"description" => esc_html__( 'Enter ID of manga to display.', 'madara-shortcode' )
				),

				array(
					"admin_label" => true,
					"type"        => "dropdown",
					"heading"     => esc_html__( "Chapter Order", 'madara-shortcode' ),
					"param_name"  => "order",
					"std"         => 'ASC',
					"value"       => array(
						esc_html__( "Ascending", 'madara-shortcode' )  => "ASC",
						esc_html__( "Descending", 'madara-shortcode' ) => "DESC",
					),
				),

				array(
					"admin_label" => true,
					"type"        => "dropdown",
					"heading"     => esc_html__( "Enable Manga Details", 'madara-shortcode' ),
					"param_name"  => "manga_details",
					"std"         => 'enable',
					"value"       => array(
						esc_html__( "Enable", 'madara-shortcode' )  => "1",
						esc_html__( "Disable", 'madara-shortcode' ) => "0"
					),
				),

				array(
					"admin_label" => true,
					"type"        => "dropdown",
					"heading"     => esc_html__( "Enable Manga Title", 'madara-shortcode' ),
					"param_name"  => "manga_title",
					"std"         => 'enable',
					"value"       => array(
						esc_html__( "Enable", 'madara-shortcode' )  => "1",
						esc_html__( "Disable", 'madara-shortcode' ) => "0"
					),
					"dependency"  => array(
						"element" => "manga_details",
						"value"   => array( "1" ),
					),
				),

			);
			
			vc_map( array(
				'name'     => esc_html__( 'Madara Manga Info', 'madara-shortcode' ),
				'base'     => 'manga_info',
				'icon'     => CT_SHORTCODE_PLUGIN_URL . '/shortcodes/img/c_post_slider.png',
				'category' => esc_html__( 'Madara Shortcodes', 'madara-shortcode' ),
				'params'   => $params,
			) );
		}
	}
	
	function wp_manga_gutenberg_manga_info_block() {
		wp_register_script(
			'wp_manga_gutenberg_manga_info_block',
			plugins_url( 'gutenberg/manga-info.js', __FILE__ ),
			array( 'wp-blocks', 'wp-element' )
		);
		
		if(function_exists('register_block_type')){
		register_block_type( 'wp-manga/gutenberg-manga-info-block', array(
			'editor_script' => 'wp_manga_gutenberg_manga_info_block',
		) );
		}
	}
	add_action( 'init', 'wp_manga_gutenberg_manga_info_block' );
