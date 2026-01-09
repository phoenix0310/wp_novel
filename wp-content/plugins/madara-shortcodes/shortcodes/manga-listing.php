<?php

	/**
	 * MadaraShortcodeMangaListing
	 */
	class MadaraShortcodeMangaListing extends MadaraShortcode {
		public function __construct( $params = null, $content = '' ) {
			parent::__construct( 'manga_listing', $params, $content );
		}

		/**
		 * @param $atts
		 * @param $content
		 *
		 * @return string
		 */
		public function renderShortcode( $atts, $content ) {

			$title         = !empty( $atts['heading'] ) ? $atts['heading'] : '';
			$heading_icon  = !empty( $atts['heading_icon'] ) ? $atts['heading_icon'] : (function_exists( 'madara_default_heading_icon' ) ? madara_default_heading_icon( false ) : '');
			$orderby       = !empty( $atts['orderby'] ) ? $atts['orderby'] : 'latest';
			$count         = !empty( $atts['count'] ) ? $atts['count'] : '';
			$order         = !empty( $atts['order'] ) ? $atts['order'] : 'DESC';
			$genres        = !empty( $atts['genres'] ) ? $atts['genres'] : null;
			$tags          = !empty( $atts['tags'] ) ? $atts['tags'] : null;
			$ids           = !empty( $atts['ids'] ) ? $atts['ids'] : '';
			$items_per_row = !empty( $atts['items_per_row'] ) ? $atts['items_per_row'] : 0;
			$chapter_type  = !empty( $atts['chapter_type'] ) ? $atts['chapter_type'] : 'all';
			$sidebar = !empty( $atts['sidebar'] ) ? $atts['sidebar'] : 0;
			// use 0 to get current user ID, leave empty if not filtered by author
			$author_id = isset($atts['author']) ? $atts['author'] : '';
			
			$shortcode_args = array();
			if($author_id != ''){
				$shortcode_args['author'] = $author_id ? $author_id : get_current_user_id();
			}
			
			// if only list mangas by Following 
			$following = isset($atts['following']) ? 1 : 0;
			
			if($following){
				$current_user_id = get_current_user_id();
				if(!$current_user_id){
					return;
				}
				
				$bookmarks     = get_user_meta( $current_user_id, '_wp_manga_bookmark', true );
				if(! empty($bookmarks)){
					$ids = implode(',',array_column($bookmarks, 'id'));
				} else {
					return;
				}
			}

			if( empty( $ids ) ){
				$shortcode_args = array_merge($shortcode_args, array(
					'post_type'      => 'wp-manga',
					'posts_per_page' => $count,
					'order'          => $order,
					'orderby'        => $orderby,
				));
				
				$meta_query = array('relation' => 'AND');

				if ( $chapter_type == 'manga' ) {
					$meta_query = array(
						'relation' => 'AND',
						array(
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
						)
					);
				} elseif ( $chapter_type == 'text' || $chapter_type == 'video' ) {
					$meta_query = array(
						'relation' => 'AND',
						array(
							'key'     => '_wp_manga_chapter_type',
							'value'   => $chapter_type,
							'compare' => '='
						)
					);
				}
				
				$type = isset($atts['type']) ? $atts['type'] : '';
				$status = isset($atts['status']) ? $atts['status'] : '';
				
				if ( ! empty( $status ) ) {
					array_push( $meta_query, array(
										'key'     => '_wp_manga_status',
										'value'   => $status,
										));
				}
				
				if ( ! empty( $type ) ) {
					array_push( $meta_query, array(
										'key'     => '_wp_manga_type',
										'value'   => $type,
										));
				}

				if(isset($atts['adult'])){
					array_push( $meta_query, array(
										'key'     => 'manga_adult_content',
										'value'   => serialize(array('yes')),
										'compare' => $atts['adult'] ? '=' : '!='
										));
										
										
				}
				
				$shortcode_args['meta_query'] = $meta_query;

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

			} else{
				$shortcode_args = array_merge($shortcode_args, array(
					'post__in'       => explode( ',', $ids ),
					'post_type'      => 'wp-manga',
				));

				$shortcode_query = new WP_Query( $shortcode_args );
			}
			
			$item_layout = isset($atts['item_layout']) ? $atts['item_layout'] : 'default';
			$num_chapters = isset($atts['num_chapters']) ? intval($atts['num_chapters']) : '';

			ob_start();

			if ( $shortcode_query->have_posts() ) {
				?>
                <div class="c-page">
                    <div class="c-page__content">

						<?php if ( ! empty( $title ) ) { ?>
                            <div class="tab-wrap">
                                <div class="c-blog__heading style-2 font-heading">

                                    <h4>
                                        <i class="<?php echo esc_attr( $heading_icon ); ?>"></i>
										<?php echo esc_html( $title ); ?>
                                    </h4>
                                </div>
                            </div>
						<?php } ?>

                        <!-- Tab panes -->
                        <div class="tab-content-wrap">
                            <div role="tabpanel" class="c-tabs-item">
                                <div class="page-content-listing item-<?php echo esc_attr($item_layout);?>">
									<?php
										if ( $shortcode_query->have_posts() ) {

											global $wp_query;
											$index = 1;
											$wp_query->set( 'madara_post_count', madara_get_post_count( $shortcode_query ) );

											if ( !$sidebar ) {
												$wp_query->set( 'sidebar', 'full' );
											}
											
											$wp_query->set('manga_archives_item_layout', $item_layout);
											
											if($items_per_row){
												$wp_query->set('manga_archives_item_columns', $items_per_row);
											}
											
											if($item_layout == 'chapters'){
												$html = '<table class="manga-shortcodes manga-chapters-listing">
												<thead>
												<th class="genre">' . esc_html__('Genre','madara-shortcode') . '</th>
												<th class="title">' . esc_html__('Title','madara-shortcode') . '</th>
												<th class="release">' . esc_html__('Release','madara-shortcode') . '</th>
												<th class="author">' . esc_html__('Author','madara-shortcode') . '</th>
												<th class="time">' . esc_html__('Time','madara-shortcode') . '</th>
												</thead><tbody>';
												echo apply_filters('wp-manga-shortcode-manga-listing-layout-chapters-header', $html);
											}

											$wp_query->set('manga_archive_latest_chapters_count', $num_chapters);

											while ( $shortcode_query->have_posts() ) {

												$wp_query->set( 'madara_loop_index', $index );
												$index ++;

												$shortcode_query->the_post();
												
												if($item_layout == 'chapters'){
													if(locate_template('madara-core/shortcodes/manga-listing/chapter-item.php')){
														get_template_part( 'madara-core/shortcodes/manga-listing/chapter-item');
													} else {
														include dirname(__FILE__) .  '/html/manga-listing/chapter-item.php';
													}
												} else {
													get_template_part( 'madara-core/content/content', 'archive' );
												}
											}
											
											if($item_layout == 'chapters'){
												$html = '</tbody></table>';
												echo apply_filters('wp-manga-shortcode-manga-listing-layout-chapters-footer', $html);
											}

										} else {
											get_template_part( 'madara-core/content/content-none' );
										}

										wp_reset_postdata();

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

	$madara_button = new MadaraShortcodeMangaListing();

	/**
	 * add button to visual composer
	 */
	add_action( 'after_setup_theme', 'reg_manga_listing' );

	function reg_manga_listing() {
		if ( function_exists( 'vc_map' ) ) {
			$params = array(
				array(
					"admin_label" => true,
					"type"        => "textfield",
					"heading"     => esc_html__( "Heading", 'madara-shortcode' ),
					"param_name"  => "heading",
					"value"       => "",
					"description" => esc_html__( 'Title for Manga Listing section', 'madara-shortcode' )
				),

				array(
					"admin_label" => true,
					"type"        => "iconpicker",
					"heading"     => esc_html__( "Heading Icon", 'madara-shortcode' ),
					"param_name"  => "heading_icon",
					"value"       => "",
					"description" => esc_html__( 'Icon on Heading for Manga Listing section', 'madara-shortcode' )
				),

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
					"type"        => "textfield",
					"heading"     => esc_html__( "Count", 'madara-shortcode' ),
					"param_name"  => "count",
					"value"       => "",
					"description" => esc_html__( 'number of items to query', 'madara-shortcode' )
				),
				
				array(
					"admin_label" => true,
					"type"        => "dropdown",
					"heading"     => esc_html__( "Item Layout", 'madara-shortcode' ),
					"param_name"  => "item_layout",
					"value"       => array(
						esc_html__( "Default", 'madara-shortcode' ) => 'default',
						esc_html__( "Big Thumbnail", 'madara-shortcode' ) => 'big_thumbnail',
						esc_html__( "Simple", 'madara-shortcode' ) => 'simple',
						esc_html__( "Chapters Table", 'madara-shortcode' ) => 'chapters'
					),
					"description" => esc_html__( "Choose Item Layout", 'madara-shortcode' )
				),

				array(
					"admin_label" => true,
					"type"        => "dropdown",
					"heading"     => esc_html__( "Items Per Row", 'madara-shortcode' ),
					"param_name"  => "items_per_row",
					"value"       => array(
						esc_html__( "1 item per row", 'madara-shortcode' ) => 1,
						esc_html__( "2 items per row", 'madara-shortcode' ) => 2,
						esc_html__( "3 items per row", 'madara-shortcode' ) => 3,
					),
					"description" => esc_html__( "Number of items per row", 'madara-shortcode' )
				),

				array(
					"admin_label" => true,
					"type"        => "dropdown",
					"heading"     => esc_html__( "Oder By", 'madara-shortcode' ),
					"param_name"  => "orderby",
					"value"       => array(
						esc_html__( "Latest", 'madara-shortcode' )                                          => 'latest',
						esc_html__( "A-Z", 'madara-shortcode' )                                             => 'alphabet',
						esc_html__( "Rating", 'madara-shortcode' )                                          => 'rating',
						esc_html__( "Trending", 'madara-shortcode' )                                        => "trending",
						esc_html__( "Most Views", 'madara-shortcode' )                                      => "views",
						esc_html__( "New Manga", 'madara-shortcode' )                                       => "new-manga",
						esc_html__( "Input (only available when using ids parameter)", 'madara-shortcode' ) => "input",
						esc_html__( "Random", 'madara-shortcode' )                                          => "random"
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
				'name'     => esc_html__( 'Madara Manga Listing', 'madara-shortcode' ),
				'base'     => 'manga_listing',
				'icon'     => CT_SHORTCODE_PLUGIN_URL . '/shortcodes/img/c_post_slider.png',
				'category' => esc_html__( 'Madara Shortcodes', 'madara-shortcode' ),
				'params'   => $params,
			) );
		}
	}
	
function wp_manga_gutenberg_manga_listing_block() {
    wp_register_script(
        'wp_manga_gutenberg_manga_listing_block',
        plugins_url( 'gutenberg/manga-listing.js', __FILE__ ),
        array( 'wp-blocks', 'wp-element' )
    );

	if(function_exists('register_block_type')){
    register_block_type( 'wp-manga/gutenberg-manga-listing-block', array(
        'editor_script' => 'wp_manga_gutenberg_manga_listing_block',
    ) );
	}
}
add_action( 'init', 'wp_manga_gutenberg_manga_listing_block' );
