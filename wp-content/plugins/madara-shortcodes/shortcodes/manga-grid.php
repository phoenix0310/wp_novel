<?php
	use App\Madara;
	
	/**
	 * MadaraShortcodeMangaGrid
	 */
	class MadaraShortcodeMangaGrid extends MadaraShortcode {
		public function __construct( $params = null, $content = '' ) {
			parent::__construct( 'manga_grid', $params, $content );
		}

		/**
		 * @param $atts
		 * @param $content
		 *
		 * @return string
		 */
		public function renderShortcode( $atts, $content ) {
			$orderby       = !empty( $atts['orderby'] ) ? $atts['orderby'] : 'latest';
			$order         = !empty( $atts['order'] ) ? $atts['order'] : 'DESC';
			$genres        = !empty( $atts['genres'] ) ? $atts['genres'] : null;
			$tags          = !empty( $atts['tags'] ) ? $atts['tags'] : null;
			$ids           = !empty( $atts['ids'] ) ? $atts['ids'] : '';
			$chapter_type  = !empty( $atts['chapter_type'] ) ? $atts['chapter_type'] : 'all';
			
			// use 0 to get current user ID
			$author_id = isset($atts['author']) ? $atts['author'] : '';
			
			$shortcode_args = array();
			if($author_id != ''){
				$shortcode_args['author'] = $author_id ? $author_id : get_current_user_id();
			}

			if( empty( $ids ) ){
				$shortcode_args = array_merge($shortcode_args, array(
					'post_type'      => 'wp-manga',
					'posts_per_page' => 9, // fix
					'order'          => $order,
					'orderby'        => $orderby,
				));

				if ( $chapter_type == 'manga' ) {
					$shortcode_args['meta_query'] = array(
						'relation' => 'OR',
						array(
							'key'     => '_wp_manga_chapter_type',
							'value'   => '',
							'compare' => 'NOT EXISTS'
						),
						array(
							'key'   => '_wp_manga_chapter_type',
							'value' => 'manga',
						)
					);
				} elseif ( $chapter_type == 'text' || $chapter_type == 'video' ) {
					$shortcode_args['meta_query'] = array(
						array(
							'key'     => '_wp_manga_chapter_type',
							'value'   => $chapter_type,
							'compare' => '='
						),
					);
				}

				if( !empty( $tags ) || !empty( $genres ) ){
					$shortcode_args['tax_query'] = array(
						'relation' => 'OR'
					);

					if( !empty( $tags ) ){
						$tags = explode( ',', $tags );

						if( !empty( $tags ) ){
							$shortcode_args['tax_query'][] = array(
								'taxonomy' => 'wp-manga-tag',
								'field'    => 'slug',
								'terms'    => $tags
							);
						}
					}

					if( !empty( $genres ) ){
						$genres = explode( ',', $genres );

						if( !empty( $genres ) ){
							$shortcode_args['tax_query'][] = array(
								'taxonomy' => 'wp-manga-genre',
								'field'    => 'slug',
								'terms'    => $genres
							);
						}
					}
				}

				if( !function_exists( 'madara_manga_query' ) ){
					return;
				}

				$shortcode_query = madara_manga_query( $shortcode_args );

			} else {
				$shortcode_args = array_merge($shortcode_args, array(
					'post__in'       => explode( ',', $ids ),
					'post_type'      => 'wp-manga',
				));

				$shortcode_query = new WP_Query( $shortcode_args );
			}
			
			$title_badge_pos = Madara::getOption('manga_badge_position', 1); // 1: before title, 2: before thumbnail

			ob_start();

			if ( $shortcode_query->have_posts() ) {
				?>
                <div class="c-page">
                    <div class="c-page__content">
						<div class="grid9">
							<?php 
							$idx = 0;
							$thumb_size          = 'manga-single';
							global $wp_manga_functions;
							while ( $shortcode_query->have_posts() ) { 
								$shortcode_query->the_post();
								
								$manga_id = get_the_ID();
								
								$class = "grid_col_2";
								if($idx == 0) { $class = "grid_col_4"; }
							?>
							<div class="item <?php echo esc_attr($class);?> <?php echo 'badge-pos-' . esc_attr($title_badge_pos);?>">
								<div class="item-inner" id="manga-item-<?php echo esc_attr( $manga_id ); ?>"  data-post-id="<?php echo esc_attr($manga_id); ?>">
									<div class="item-thumb c-image-hover">
										<?php
											if ( has_post_thumbnail() ) {
												?>
												<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
													<?php echo madara_thumbnail( $thumb_size ); ?>
													<?php madara_manga_title_badges_html( $manga_id, 1 ); ?>
												</a>
												<?php
											}
										?>
									</div>
									<div class="item-summary">
										<div class="post-title font-title">
											<h3 class="h5 text2row">
												<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
											</h3>
										</div>
										<div class="meta-item genres">
											<?php
												$genres = $wp_manga_functions->get_manga_genres( $manga_id );
												if($genres){
													$genres = explode(',', $genres);
													echo trim($genres[0]);
												}
											?>
										</div>
										<div class="meta-item rating">
											<?php
												$wp_manga_functions->manga_rating_display( $manga_id );
											?>
										</div>
										<?php if($idx == 0){?>
										<div class="meta-item description">
											<?php
												the_excerpt();
											?>
										</div>
										<?php }?>
									</div>
								
								</div>
							</div>
							<?php 
									$idx++;
								}
							
							wp_reset_postdata();
							?>
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

	$madara_button = new MadaraShortcodeMangaGrid();

	/**
	 * add button to visual composer
	 */
	add_action( 'after_setup_theme', 'reg_manga_grid' );

	function reg_manga_grid() {
		if ( function_exists( 'vc_map' ) ) {
			$params = array(
				array(
					"admin_label" => true,
					"type"        => "dropdown",
					"heading"     => esc_html__( "Manga Chapter Type", 'madara-shortcode' ),
					"param_name"  => "chapter_type",
					"value"       => array(
						esc_html__( "All", 'madara-shortcode' )   => 'all',
						esc_html__( "Image", 'madara-shortcode' ) => 'manga',
						esc_html__( "Text", 'madara-shortcode' )  => 'text',
						esc_html__( "Video", 'madara-shortcode' ) => 'video',
					),
					"std"         => "all",
					"description" => esc_html__( "Type of Manga Chapter to query", 'madara-shortcode' )
				),
				
				array(
					"admin_label" => true,
					"type"        => "textfield",
					"heading"     => esc_html__( "Author ID", 'madara-shortcode' ),
					"param_name"  => "author_id",
					"value"       => "",
					"description" => esc_html__( 'Filter by Author ID. Enter 0 to get current logged-in user', 'madara-shortcode' )
				),

				array(
					"admin_label" => true,
					"type"        => "dropdown",
					"heading"     => esc_html__( "Oder By", 'madara-shortcode' ),
					"param_name"  => "orderby",
					"value"       => array(
						esc_html__( "Latest", 'madara-shortcode' ) => 'latest',
						esc_html__( "A-Z", 'madara-shortcode' ) => 'alphabet',
						esc_html__( "Rating", 'madara-shortcode' ) => 'rating',
						esc_html__( "Trending", 'madara-shortcode' ) => "trending",
						esc_html__( "Most Views", 'madara-shortcode' ) => "views",
						esc_html__( "New Manga", 'madara-shortcode' ) => "new-manga",
						esc_html__( "Input (only available when using ids parameter)", 'madara-shortcode' ) => "input",
						esc_html__( "Random", 'madara-shortcode' ) => "random"
					),
					"description" => esc_html__( "condition to query items", 'madara-shortcode' )
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
					"type"        => "textfield",
					"heading"     => esc_html__( "Manga Genres", 'madara-shortcode' ),
					"param_name"  => "genres",
					"value"       => "",
					"description" => esc_html__( 'List of Manga Genres slug to query items from, separated by a comma.', 'madara-shortcode' )
				),

				array(
					"type"        => "textfield",
					"heading"     => esc_html__( "Manga Tags", 'madara-shortcode' ),
					"param_name"  => "tags",
					"value"       => "",
					"description" => esc_html__( 'List of Manga Tags slug to query items from, separated by a comma.', 'madara-shortcode' )
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
				'name'     => esc_html__( 'Manga Grid', 'madara-shortcode' ),
				'base'     => 'manga_grid',
				'icon'     => CT_SHORTCODE_PLUGIN_URL . '/shortcodes/img/c_post_slider.png',
				'category' => esc_html__( 'Madara Shortcodes', 'madara-shortcode' ),
				'params'   => $params,
			) );
		}
	}
	
function wp_manga_gutenberg_manga_grid_block() {
    wp_register_script(
        'wp_manga_gutenberg_manga_grid_block',
        plugins_url( 'gutenberg/manga-grid.js', __FILE__ ),
        array( 'wp-blocks', 'wp-element' )
    );

	if(function_exists('register_block_type')){
    register_block_type( 'wp-manga/gutenberg-manga-grid-block', array(
        'editor_script' => 'wp_manga_gutenberg_manga_grid_block',
    ) );
	}
}
add_action( 'init', 'wp_manga_gutenberg_manga_grid_block' );
