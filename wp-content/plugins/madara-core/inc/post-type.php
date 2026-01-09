<?php

	class WP_MANGA_POST_TYPE {

		public function __construct() {
			add_action( 'init', array( $this, 'wp_manga' ) );

			add_action( 'add_meta_boxes', array( $this, 'wp_manga_meta_box' ) );
			add_action( 'save_post', array( $this, 'wp_manga_data_save' ), 10, 2 );
			add_filter( 'post_row_actions', array( $this, 'wp_manga_download_hook' ), 10, 2 );
			add_filter( 'register_post_type_args', array( $this, 'wp_manga_slug' ), 100, 2 );

			add_action( 'admin_footer', array( $this, 'wp_manga_popup' ) );

			add_action( 'updated_user_meta', array( $this, 'show_manga_taxonomies' ), 10, 4 );
			
			add_filter( 'manage_wp-manga_posts_columns', array($this, 'admin_columns') );
			add_action( 'manage_wp-manga_posts_custom_column' , array($this, 'admin_columns_value'), 10, 2 );
		}
		
		function admin_columns($columns) {
			$admin_show_chapter_type = $GLOBALS['wp_manga_setting']->get_manga_option('admin_show_chapter_type', '');
			
			if($admin_show_chapter_type){
				$columns['manga_type'] = esc_html__( 'Type', WP_MANGA_TEXTDOMAIN );
			}
            
            $columns = array_merge(array_slice($columns, 0, 1, true),
                            array('id'=> esc_html__('ID', WP_MANGA_TEXTDOMAIN )),
                                array_slice($columns, 1, count($columns)-1, true));

			return $columns;
		}

		function admin_columns_value( $column, $post_id ) {
			switch ( $column ) {

				case 'manga_type' :
					$chapter_type = get_post_meta( $post_id, '_wp_manga_chapter_type', true );
					echo '<span class="admin-tag chapter-type wp-manga-chapter-type-label ' . $chapter_type . '">';
					switch($chapter_type){
						case 'video': echo esc_html__('Drama', WP_MANGA_TEXTDOMAIN);break;
						case 'text': echo esc_html__('Novel', WP_MANGA_TEXTDOMAIN);break;
						case 'manga': 
                            
                            echo esc_html__('Manga', WP_MANGA_TEXTDOMAIN); break;
					}
					echo '</span>';
					break;
                case 'id':
                    echo $post_id;
                    break;
			}
		}

		function wp_manga() {


			$settings = get_option( 'wp_manga_settings', array() );

			register_post_type( 'wp-manga', array(
				'description'     => __( 'Manga Name', WP_MANGA_TEXTDOMAIN ),
				'labels'          => array(
					'name'               => esc_html__( 'Manga', WP_MANGA_TEXTDOMAIN ),
					'singular_name'      => esc_html__( 'Manga', WP_MANGA_TEXTDOMAIN ),
					'menu_name'          => esc_html__( 'Manga', WP_MANGA_TEXTDOMAIN ),
					'all_items'          => esc_html__( 'All Mangas', WP_MANGA_TEXTDOMAIN ),
					'add_new'            => esc_html__( 'Add New', WP_MANGA_TEXTDOMAIN ),
					'add_new_item'       => esc_html__( 'Add New Manga', WP_MANGA_TEXTDOMAIN ),
					'edit_item'          => esc_html__( 'Edit Manga', WP_MANGA_TEXTDOMAIN ),
					'new_item'           => esc_html__( 'New Manga', WP_MANGA_TEXTDOMAIN ),
					'view_item'          => esc_html__( 'View Manga', WP_MANGA_TEXTDOMAIN ),
					'search_items'       => esc_html__( 'Search Manga', WP_MANGA_TEXTDOMAIN ),
					'not_found'          => esc_html__( 'No Manga found', WP_MANGA_TEXTDOMAIN ),
					'not_found_in_trash' => esc_html__( 'No Manga found in Trash', WP_MANGA_TEXTDOMAIN ),
                    'archives' => esc_html__('Mangas', WP_MANGA_TEXTDOMAIN)
				),
				'supports'        => array(
					'title',
					'thumbnail',
					'author',
					'publicize',
					'wpcom-markdown',
					'editor',
					'comments'
				),
				'rewrite'         => array(
					'slug'       => 'manga',
					'with_front' => false,
					'feeds'      => true,
					'pages'      => true,
				),
				'public'          => true,
				'show_ui'         => true,
				'menu_position'   => 20,
				'menu_icon'       => 'dashicons-book',
				'capability_type' => 'post',
				'map_meta_cap'    => true,
				'taxonomies'      => array(
					'wp-manga-tag',
					'wp-manga-release',
					'wp-manga-author',
					'wp-manga-category',
					'wp-manga-artist'
				),
				'has_archive'     => true,
				'query_var'       => WP_MANGA_TEXTDOMAIN,
			) );
			
			$manga_tag_slug = isset( $settings['manga_tag_slug'] ) && $settings['manga_tag_slug'] != '' ? $settings['manga_tag_slug'] : 'manga-tag';

			register_taxonomy( 'wp-manga-tag', array( 'wp-manga' ), array(
				'hierarchical'      => false,
				'labels'            => array(
					'name'              => esc_html__( 'Manga Tags', WP_MANGA_TEXTDOMAIN ),
					'singular_name'     => esc_html__( 'Manga Tags', WP_MANGA_TEXTDOMAIN ),
					'menu_name'         => esc_html__( 'Manga Tags', WP_MANGA_TEXTDOMAIN ),
					'all_items'         => esc_html__( 'All Manga Tags', WP_MANGA_TEXTDOMAIN ),
					'edit_item'         => esc_html__( 'Edit Manga Tag', WP_MANGA_TEXTDOMAIN ),
					'view_item'         => esc_html__( 'View Manga Tag', WP_MANGA_TEXTDOMAIN ),
					'update_item'       => esc_html__( 'Update Manga Tag', WP_MANGA_TEXTDOMAIN ),
					'add_new_item'      => esc_html__( 'Add Manga Tag', WP_MANGA_TEXTDOMAIN ),
					'new_item_name'     => esc_html__( 'New Manga Tag', WP_MANGA_TEXTDOMAIN ),
					'parent_item'       => esc_html__( 'Parent Manga Tag', WP_MANGA_TEXTDOMAIN ),
					'parent_item_colon' => esc_html__( 'Parent Manga Tag:', WP_MANGA_TEXTDOMAIN ),
					'search_items'      => esc_html__( 'Search Manga Tag', WP_MANGA_TEXTDOMAIN ),
				),
				'public'            => true,
				'show_ui'           => true,
				'show_in_menu'      => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => $manga_tag_slug ),
				'capabilities' => array(
						'manage_terms' => 'manage_categories',
						'edit_terms' => 'manage_categories',
						'delete_terms' => 'manage_categories',
						'assign_terms' => 'edit_posts'
					)
			) );

			// Release year
			$release_slug = isset( $settings['manga_release_slug'] ) && $settings['manga_release_slug'] != '' ? $settings['manga_release_slug'] : 'manga-release';
			register_taxonomy( 'wp-manga-release', array( 'wp-manga' ), array(
				'hierarchical'      => false,
				'labels'            => array(
					'name'                       => esc_html__( 'Manga Release Year', WP_MANGA_TEXTDOMAIN ),
					'singular_name'              => esc_html__( 'Manga Release Year', WP_MANGA_TEXTDOMAIN ),
					'menu_name'                  => esc_html__( 'Manga Release Year', WP_MANGA_TEXTDOMAIN ),
					'all_items'                  => esc_html__( 'All Manga Release Year', WP_MANGA_TEXTDOMAIN ),
					'edit_item'                  => esc_html__( 'Edit Manga Release Year', WP_MANGA_TEXTDOMAIN ),
					'view_item'                  => esc_html__( 'View Manga Release Year', WP_MANGA_TEXTDOMAIN ),
					'update_item'                => esc_html__( 'Update Manga Release Year', WP_MANGA_TEXTDOMAIN ),
					'add_new_item'               => esc_html__( 'Add New Manga Release Year', WP_MANGA_TEXTDOMAIN ),
					'new_item_name'              => esc_html__( 'New Manga Release Year', WP_MANGA_TEXTDOMAIN ),
					'parent_item'                => esc_html__( 'Parent Manga Release Year', WP_MANGA_TEXTDOMAIN ),
					'parent_item_colon'          => esc_html__( 'Parent Manga Release Year:', WP_MANGA_TEXTDOMAIN ),
					'search_items'               => esc_html__( 'Search Manga Release Year', WP_MANGA_TEXTDOMAIN ),
					'separate_items_with_commas' => esc_html__( 'Separate Release Years with commas', WP_MANGA_TEXTDOMAIN ),
					'choose_from_most_used'      => esc_html__( 'Choose from the most used release years', WP_MANGA_TEXTDOMAIN ),
				),
				'public'            => true,
				'show_ui'           => true,
				'show_in_menu'      => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => $release_slug ),
				'capabilities' => array(
						'manage_terms' => 'manage_categories',
						'edit_terms' => 'manage_categories',
						'delete_terms' => 'manage_categories',
						'assign_terms' => 'edit_posts'
					)
			) );

			// Author
			$author_slug = isset( $settings['manga_author_slug'] ) && $settings['manga_author_slug'] != '' ? $settings['manga_author_slug'] : 'manga-author';

			register_taxonomy( 'wp-manga-author', array( 'wp-manga' ), array(
				'hierarchical'      => false,
				'labels'            => array(
					'name'                       => esc_html__( 'Manga Authors', WP_MANGA_TEXTDOMAIN ),
					'singular_name'              => esc_html__( 'Manga Author', WP_MANGA_TEXTDOMAIN ),
					'menu_name'                  => esc_html__( 'Manga Authors', WP_MANGA_TEXTDOMAIN ),
					'all_items'                  => esc_html__( 'All Manga Authors', WP_MANGA_TEXTDOMAIN ),
					'edit_item'                  => esc_html__( 'Edit Manga Author', WP_MANGA_TEXTDOMAIN ),
					'view_item'                  => esc_html__( 'View Manga Author', WP_MANGA_TEXTDOMAIN ),
					'update_item'                => esc_html__( 'Update Manga Author', WP_MANGA_TEXTDOMAIN ),
					'add_new_item'               => esc_html__( 'Add New Manga Author', WP_MANGA_TEXTDOMAIN ),
					'new_item_name'              => esc_html__( 'New Manga Author Name', WP_MANGA_TEXTDOMAIN ),
					'search_items'               => esc_html__( 'Search Manga Author', WP_MANGA_TEXTDOMAIN ),
					'popular_items'              => esc_html__( 'Popular Manga Author', WP_MANGA_TEXTDOMAIN ),
					'separate_items_with_commas' => esc_html__( 'Separate authors with commas', WP_MANGA_TEXTDOMAIN ),
					'add_or_remove_items'        => esc_html__( 'Add or remove authors', WP_MANGA_TEXTDOMAIN ),
					'choose_from_most_used'      => esc_html__( 'Choose from the most used authors', WP_MANGA_TEXTDOMAIN ),
					'not_found'                  => esc_html__( 'No authors found.', WP_MANGA_TEXTDOMAIN ),
				),
				'public'            => true,
				'show_ui'           => true,
				'show_in_nav_menus' => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => $author_slug ),
				'capabilities' => array(
						'manage_terms' => 'manage_categories',
						'edit_terms' => 'manage_categories',
						'delete_terms' => 'manage_categories',
						'assign_terms' => 'edit_posts'
					)
			) );
			
			$artist_slug = isset( $settings['manga_artist_slug'] ) && $settings['manga_artist_slug'] != '' ? $settings['manga_artist_slug'] : 'manga-artist';

			// artist
			register_taxonomy( 'wp-manga-artist', array( 'wp-manga' ), array(
				'hierarchical'      => false,
				'labels'            => array(
					'name'                       => esc_html__( 'Manga Artists', WP_MANGA_TEXTDOMAIN ),
					'singular_name'              => esc_html__( 'Manga Artist', WP_MANGA_TEXTDOMAIN ),
					'menu_name'                  => esc_html__( 'Manga Artists', WP_MANGA_TEXTDOMAIN ),
					'all_items'                  => esc_html__( 'All Manga Artists', WP_MANGA_TEXTDOMAIN ),
					'edit_item'                  => esc_html__( 'Edit Manga Artist', WP_MANGA_TEXTDOMAIN ),
					'view_item'                  => esc_html__( 'View Manga Artist', WP_MANGA_TEXTDOMAIN ),
					'update_item'                => esc_html__( 'Update Manga Artist', WP_MANGA_TEXTDOMAIN ),
					'add_new_item'               => esc_html__( 'Add New Manga Artist', WP_MANGA_TEXTDOMAIN ),
					'new_item_name'              => esc_html__( 'New Manga Artist Name', WP_MANGA_TEXTDOMAIN ),
					'search_items'               => esc_html__( 'Search Manga Artist', WP_MANGA_TEXTDOMAIN ),
					'popular_items'              => esc_html__( 'Popular Manga Artist', WP_MANGA_TEXTDOMAIN ),
					'separate_items_with_commas' => esc_html__( 'Separate artists with commas', WP_MANGA_TEXTDOMAIN ),
					'add_or_remove_items'        => esc_html__( 'Add or remove Artists', WP_MANGA_TEXTDOMAIN ),
					'choose_from_most_used'      => esc_html__( 'Choose from the most used Artists', WP_MANGA_TEXTDOMAIN ),
					'not_found'                  => esc_html__( 'No Artists found.', WP_MANGA_TEXTDOMAIN ),
				),
				'public'            => true,
				'show_ui'           => true,
				'show_in_nav_menus' => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => $artist_slug ),
				'capabilities' => array(
						'manage_terms' => 'manage_categories',
						'edit_terms' => 'manage_categories',
						'delete_terms' => 'manage_categories',
						'assign_terms' => 'edit_posts'
					)
			) );

			// genre

			$genres_slug = isset( $settings['manga_genres_slug'] ) && $settings['manga_genres_slug'] != '' ? $settings['manga_genres_slug'] : 'manga-genre';

			register_taxonomy( 'wp-manga-genre', array( 'wp-manga' ), array(
				'hierarchical'      => true,
				'labels'            => array(
					'name'              => esc_html__( 'Manga Genres', WP_MANGA_TEXTDOMAIN ),
					'singular_name'     => esc_html__( 'Manga Genre', WP_MANGA_TEXTDOMAIN ),
					'menu_name'         => esc_html__( 'Manga Genres', WP_MANGA_TEXTDOMAIN ),
					'all_items'         => esc_html__( 'All Manga Genres', WP_MANGA_TEXTDOMAIN ),
					'edit_item'         => esc_html__( 'Edit Manga Genre', WP_MANGA_TEXTDOMAIN ),
					'view_item'         => esc_html__( 'View Manga Genre', WP_MANGA_TEXTDOMAIN ),
					'update_item'       => esc_html__( 'Update Manga Genre', WP_MANGA_TEXTDOMAIN ),
					'add_new_item'      => esc_html__( 'Add New Manga Genre', WP_MANGA_TEXTDOMAIN ),
					'new_item_name'     => esc_html__( 'New Manga Genre Name', WP_MANGA_TEXTDOMAIN ),
					'parent_item'       => esc_html__( 'Parent Manga Genre', WP_MANGA_TEXTDOMAIN ),
					'parent_item_colon' => esc_html__( 'Parent Manga Genre:', WP_MANGA_TEXTDOMAIN ),
					'search_items'      => esc_html__( 'Search Manga Genres', WP_MANGA_TEXTDOMAIN ),
				),
				'public'            => true,
				'show_ui'           => true,
				'show_in_nav_menus' => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => $genres_slug ),
				'capabilities' => array(
						'manage_terms' => 'manage_categories',
						'edit_terms' => 'manage_categories',
						'delete_terms' => 'manage_categories',
						'assign_terms' => 'edit_posts'
					)
			) );

			register_post_type( 'chapter_text_content', array(
				'rewrite'            => array(
					'slug' => 'chapter_text_content'
				),
				'has_archive'        => false,
				'query_var'          => false,
				'publicly_queryable' => false,
				'public'             => false,
			) );
		}

		function wp_manga_meta_box() {
			add_meta_box( 'manga-information-metabox', __( 'WP Manga', WP_MANGA_TEXTDOMAIN ), array(
				$this,
				'wp_manga_metabox'
			), 'wp-manga', 'normal', 'high' );

			add_meta_box( 'manga_status_settings', __( 'Manga Status', WP_MANGA_TEXTDOMAIN ), array(
				$this,
				'wp_manga_status'
			), 'wp-manga', 'side', 'high' );

			add_meta_box( 'manga_views', __( 'Manga Views', WP_MANGA_TEXTDOMAIN ), array(
				$this,
				'wp_manga_views'
			), 'wp-manga', 'side', 'high' );
		}

		function wp_manga_views( $post ) {  
			$views = get_post_meta( $post->ID, '_wp_manga_views', true );

			if ( $views == false ) {
				$views = 0;
			}
			?>
            <input type="number" name="manga-views" value="<?php echo $views ?>"/>
			<?php
		}

		function wp_manga_status( $post ) {
			$status = get_post_meta( $post->ID, '_wp_manga_status', true );
			$manga_status = $this->get_manga_status();
			
			?>
            <select id="manga-status" name="manga-status">
				<?php foreach($manga_status as $prefined_status_key => $prefined_status_value){
					?>
					<option value="<?php echo esc_attr($prefined_status_key);?>" <?php selected( $prefined_status_key, $status, true ); ?>><?php esc_html_e($prefined_status_value); ?></option>
					<?php
				}?>
            </select>
			<?php
		}

		function wp_manga_metabox( $post ) {
			if ( file_exists( WP_MANGA_DIR . 'admin/manga-single/manga-metabox.php' ) ) {
				include( WP_MANGA_DIR . 'admin/manga-single/manga-metabox.php' );
			}

		}

		function wp_manga_data_save( $post_id, $post ) {
			if (isset($post->post_status) && 'auto-draft' == $post->post_status) {
				return;
			}
			
			// Autosave, do nothing
			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
				return;
			}

			// AJAX? Not used here
			if (defined('DOING_AJAX') && DOING_AJAX) {
				return;
			}
			
			// Return if it's a post revision
			if ( false !== wp_is_post_revision( $post_id ) ) {
				
				return;
			}
			
			if ( $post->post_type !== 'wp-manga' ) {
				return;
			}
			
			//set dummy data
			if ( empty( get_post_meta( $post_id, 'manga_unique_id', true ) ) ) {

				update_post_meta( $post_id, '_manga_avarage_reviews', 0 );
				update_post_meta( $post_id, '_wp_manga_week_views', 0 );

				if ( ! isset( $_POST['manga-views'] ) ) {
					update_post_meta( $post_id, '_wp_manga_views', 0 );
				}					

				//add unique id for manga post
				$encrypt_uniq = apply_filters('wp_manga_encrypt_manga_uniq', true, $post_id, $post->post_name);

				$uniqid = $encrypt_uniq ? uniqid( 'manga_' ) : ($post->post_name ? $post->post_name : sanitize_title($post->post_title));
				
				update_post_meta( $post_id, 'manga_unique_id', $uniqid ); 
			}

			if ( isset( $_POST['manga-status'] ) ) {
				update_post_meta( $post_id, '_wp_manga_status', $_POST['manga-status'] );
			}

			if ( isset( $_POST['manga-views'] ) ) {
				update_post_meta( $post_id, '_wp_manga_views', $_POST['manga-views'] );
			}

			if ( isset( $_POST['wp-manga-alternative'] ) ) {
				update_post_meta( $post_id, '_wp_manga_alternative', $_POST['wp-manga-alternative'] );
			}
            
            if ( isset( $_POST['wp-manga-chapters-warning'] ) ) {
				update_post_meta( $post_id, '_wp_manga_chapters_warning', $_POST['wp-manga-chapters-warning'] );
			}

			if ( isset( $_POST['wp-manga-type'] ) ) {
				update_post_meta( $post_id, '_wp_manga_type', $_POST['wp-manga-type'] );
			}

			$first_create = get_post_meta( $post_id, '_latest_update', true );
			if ( ! $first_create || $first_create == '' ) {
				update_post_meta( $post_id, '_latest_update', current_time( 'timestamp', false ) );

				do_action('wp_manga_new_manga_created', $post_id);
			}
            
            if(!isset($meta['manga_adult_content'])){
                if(!isset($_POST['manga_adult_content'])){
                    update_post_meta($post_id, 'manga_adult_content', ''); 
                }
            }
            
            // update search text
            global $wp_manga, $wp_manga_database;
            $search_text = $wp_manga->build_search_text($post_id);
            
            if($search_text){
                $wp_manga_database->update_search_text($post_id, $search_text);
            }
		}

		function wp_manga_download_hook( $actions, $post ) {

			if ( $post->post_type == 'wp-manga' ) {

				global $wp_manga_chapter;

				$chapters = $wp_manga_chapter->get_manga_chapters( $post->ID );

				if ( $chapters !== false ) {

					$output = '<a href="#" id="wp-manga-download" name="' . $post->ID . '"> Download Manga </a>';

					$output .= '<input type="hidden" name="post-name" value="' . get_the_title( $post->ID ) . '">';

					$actions['wp-manga-download'] = $output;

				}

				return $actions;
			}

			return $actions;

		}

		function wp_manga_popup() {

			global $pagenow, $post, $wp_manga;
			?>
            <div id="wp-manga-popup-all">
                <div class="wp-manga-popup-background"></div>
                <div class="wp-manga-popup-wrapper">
                    <div id="wp-manga-popup-header">
                        <h3 id="wp-manga-post-title"></h3>
                        <div id="wp-manga-popup-exit">
                            <i class="fa fa-times" aria-hidden="true"></i>
                        </div>
                    </div>
                    <hr>
                    <div id="wp-manga-popup-content">
                        <input type="hidden" name="postID" value="">

                        <button id="wp-manga-download-button" class="button button-primary">
                            <i class="fa fa-download"></i>
							<?php echo esc_html__( 'Download Manga', WP_MANGA_TEXTDOMAIN ); ?>
                        </button>

                        <div class="wp-manga-popup-content-msg"></div>

                        <div class="wp-manga-popup-loading">
                            <div class="wp-manga-popup-loading-wrapper">
                                <i class="fa fa-spinner fa-spin"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
			<?php
		}

		function list_all_chapters( $post_id, $order = '' ) {

			global $wp_manga_functions;
			$chapters = $wp_manga_functions->get_all_chapters( $post_id, $order );
            
			$output = $wp_manga_functions->list_chapters_by_volume( $post_id, $chapters, false );

			return $output ? $output : '<div></div>';
		}

		function wp_manga_slug( $args, $post_type ) {

			if ( $post_type == 'wp-manga' ) {
				$wp_manga_settings = get_option( 'wp_manga_settings' );

				if ( ! empty( $wp_manga_settings['manga_slug'] ) ) {
					$args['rewrite']['slug'] = $wp_manga_settings['manga_slug'];
				}

				$args['has_archive'] = true;
			}

			return $args;

		}

		function allow_to_choose_chapter_type( $post_id ) {

			$chapter_type = get_post_meta( $post_id, '_wp_manga_chapter_type', true );

			if ( ! empty( $chapter_type ) ) {
				return false;
			}

			//if this is add new manga post page
			if ( ! isset( $_GET['post'] ) ) {
				return true;
			}

			//if this manga is already published but haven't any chapter, then it is allowed to choose type
			global $wp_manga_chapter;
			$chapters = $wp_manga_chapter->get_manga_chapters( $post_id );

			if ( empty( $chapters ) ) {
				return true;
			}

			return false;

		}

		//this func keep all manga taxonomies boxes are showed
		function show_manga_taxonomies( $meta_id, $object_id, $meta_key, $meta_value ) {
			if ( $meta_key == 'metaboxhidden_wp-manga' && ! empty( $meta_value ) ) {
				update_user_meta( $object_id, $meta_key, array() );
			}
		}

		function search_c_extend_name( $manga_query, $manga_args ) {

			global $wp_manga_chapter;

			if ( empty( $manga_args['s'] ) ) {
				return $manga_query;
			}

			//Search Chapter Extend Name
			$chapters = $wp_manga_chapter->get_chapters( array(), $manga_args['s'], 'date' );

			if ( ! empty( $chapters ) ) {
				$col = 'post_id';
				$post_ids = array_unique( array_merge( array_map(function($element) use($col ){return $element[$col ];}, $chapters), wp_list_pluck( $manga_query->posts, 'ID' ) ) );

				$search_posts = new WP_Query( array(
					'post_type' => 'wp-manga',
					'post__in'  => $post_ids,
                    'posts_per_page' => 6,
				) );

				if ( $search_posts->have_posts() ) {
					$manga_query = $this->merge_query( $manga_query, $search_posts );
				}

			}

			return $manga_query;
		}

		function search_manga_alt_name( $manga_query, $manga_args ) {

			if ( empty( $manga_args['s'] ) ) {
				return $manga_query;
			}

			$search = $manga_args['s'];
			unset( $manga_args['s'] );
			unset( $manga_args['meta_key'] );
			if ( ! isset( $manga_args['meta_query'] ) ) {
				$manga_args['meta_query'] = array();
			}

			$manga_args['meta_query'][]           = array(
				'key'     => '_wp_manga_alternative',
				'value'   => $search,
				'compare' => 'LIKE',
			);
			$manga_args['meta_query']['relation'] = 'OR';
			$manga_args['post__not_in']           = wp_list_pluck( $manga_query->posts, 'ID' );
            $manga_args['posts_per_page'] = 6;

			$search_posts = new WP_Query( $manga_args );

			if ( $search_posts->have_posts() ) {
				$manga_query = $this->merge_query( $manga_query, $search_posts );
			}

			return $manga_query;
		}

		function merge_query( $query1, $query2 ) {

			$total_posts = array_merge( $query1->posts, $query2->posts );

			$query1->posts = array();
			$final_posts   = array();

			foreach( $total_posts as $post ){
				if( ! in_array( $post->ID, $final_posts ) ){
					$query1->posts[] = $post;
					$final_posts[]   = $post->ID;
				}
			}

			$query1->post_count  = count( $query1->posts );
			$query1->found_posts = count( $query1->posts );

			return $query1;

		}
		
		public function get_manga_status(){
			return apply_filters('wp-manga-series-status-list', array(
				'on-going' => esc_html__( 'OnGoing', WP_MANGA_TEXTDOMAIN ),
				'end' => esc_html__( 'Completed', WP_MANGA_TEXTDOMAIN ),
				'canceled' => esc_html__( 'Canceled', WP_MANGA_TEXTDOMAIN ),
				'on-hold' => esc_html__( 'On Hold', WP_MANGA_TEXTDOMAIN ),
				'upcoming' => esc_html__( 'Upcoming', WP_MANGA_TEXTDOMAIN )
				));
		}
	}

	$GLOBALS['wp_manga_post_type'] = new WP_MANGA_POST_TYPE();
