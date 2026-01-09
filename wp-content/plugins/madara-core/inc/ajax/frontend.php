<?php

	class WP_MANGA_AJAX_FRONTEND {

		public function __construct() {
            //delete_zip
            //add_action('wp_ajax_wp-manga-delete-zip', array( $this, 'wp_manga_delete_zip' ) );
            //add_action('wp_ajax_nopriv_wp-manga-delete-zip', array( $this, 'wp_manga_delete_zip' ) );

			// search manga
			add_action( 'wp_ajax_wp-manga-search-manga', array( $this, 'wp_manga_search_manga' ) );
			add_action( 'wp_ajax_nopriv_wp-manga-search-manga', array( $this, 'wp_manga_search_manga' ) );

			// delete bookmark manga
			add_action( 'wp_ajax_wp-manga-delete-bookmark', array( $this, 'wp_manga_delete_bookmark' ) );
			add_action( 'wp_ajax_nopriv_wp-manga-delete-bookmark', array( $this, 'wp_manga_delete_bookmark' ) );

			add_action( 'wp_ajax_wp-manga-delete-multi-bookmark', array( $this, 'wp_manga_delete_multi_bookmark' ) );
			add_action( 'wp_ajax_nopriv_wp-manga-delete-multi-bookmark', array(
				$this,
				'wp_manga_delete_multi_bookmark'
			) );

			// bookmark manga
			add_action( 'wp_ajax_wp-manga-user-bookmark', array( $this, 'wp_manga_user_bookmark' ) );
			add_action( 'wp_ajax_nopriv_wp-manga-user-bookmark', array( $this, 'wp_manga_user_bookmark' ) );

			// get next manga in list page ( front-end )
			add_action( 'wp_ajax_wp-manga-get-next-manga', array( $this, 'wp_manga_get_next_manga' ) );
			add_action( 'wp_ajax_nopriv_wp-manga-get-next-manga', array( $this, 'wp_manga_get_next_manga' ) );

			// save rating when user click ( front-end )
			add_action( 'wp_ajax_wp-manga-save-rating', array( $this, 'wp_manga_save_rating' ) );
			add_action( 'wp_ajax_nopriv_wp-manga-save-rating', array( $this, 'wp_manga_save_rating' ) );

			// User upload avatar
			add_action( 'wp_ajax_wp-manga-upload-avatar', array( $this, 'wp_manga_upload_avatar' ) );
			add_action( 'wp_ajax_nopriv_wp-manga-upload-avatar', array( $this, 'wp_manga_upload_avatar' ) );

			// Get User after login
			add_action( 'wp_ajax_wp-manga-get-user-section', array( $this, 'wp_manga_get_user_section' ) );
			add_action( 'wp_ajax_nopriv_wp-manga-get-user-section', array( $this, 'wp_manga_get_user_section' ) );

			// Chapter content when using ajax pagination
			add_action( 'wp_ajax_chapter_navigate_page', array( $this, 'chapter_navigate_page' ) );
			add_action( 'wp_ajax_nopriv_chapter_navigate_page', array( $this, 'chapter_navigate_page' ) );

			// Save user player id for web push
			add_action( 'wp_ajax_save_user_player_id', array( $this, 'save_user_player_id' ) );
			
			add_action('wp_ajax_manga_views', array($this, 'update_manga_views'));
			add_action('wp_ajax_nopriv_manga_views', array($this, 'update_manga_views'));
            
            add_action('template_redirect', array($this, '_ajax_process'));
			
			add_action('wp_ajax_manga_get_reading_nav', array($this, 'get_reading_nav'));
			add_action('wp_ajax_nopriv_manga_get_reading_nav', array($this, 'get_reading_nav'));
		}
        
        function _ajax_process(){
            global $wp_query, $wp;
			
            $manga_ajax = $wp_query->get('manga_ajax');
            if (isset($_SERVER['REQUEST_METHOD'])) {
                $method = $_SERVER['REQUEST_METHOD'];
				
                if (strtoupper($method) === 'POST') {                    
                    if(!empty($manga_ajax)){
                        // this check is to process ajax calls only
                        $action = $wp_query->get('maction');
                        
                        if(!empty($action)){
                            switch($action){
                                case 'get_chapters':
                                    $manga_slug = $wp_query->get('manga-core');
									
                                    if(!empty($manga_slug)){
                                        $manga = get_page_by_path($manga_slug, OBJECT, 'wp-manga');
                                        if($manga){
                                            $this->get_manga_chapters( $manga->ID );
                                        }
                                    }
                                    exit;
                                default:
                                    wp_send_json_error([]);
                                    break;
                            }
                        }
                    }
                }
            }
        }
        
        function wp_manga_delete_zip(){

            $zip_dir = isset( $_POST['zipDir'] ) ? $_POST['zipDir'] : '';

            if( !empty( $zip_dir ) ){
                unlink( $zip_dir );
            }

        }
		
        /**
         * Echo Chapters List of a manga
         */
		function get_manga_chapters( $manga_id ){
            global $wp_manga_functions, $wp_manga_database;
    
            $sort_option = $wp_manga_database->get_sort_setting();

			$chapter_page = isset($_GET['t']) ? intval($_GET['t']) : 1;
			$number_of_chapters_per_page = $wp_manga_functions->get_number_chapters_per_page();
            
            $manga = $wp_manga_functions->get_all_chapters( $manga_id, $sort_option['sort'], $number_of_chapters_per_page, $chapter_page );
            
            $current_read_chapter = 0;
            if ( is_user_logged_in() ) {
                $user_id = get_current_user_id();
                $history = madara_get_current_reading_chapter($user_id, $manga_id);
                if($history){
                    $current_read_chapter = $history['c'];
                }
            }
            set_query_var('manga_id', $manga_id);
			set_query_var('chapter_page', $chapter_page);

            global $wp_manga_template;
            
            
            include $wp_manga_template->load_template('single/info','chapters', false);
		}
		
		function get_reading_nav(){
			if(isset($_POST['manga']) && isset($_POST['volume_id'])) {
					
					$manga_id = $_POST['manga'];
					$volume_id = $_POST['volume_id'];
					$cur_chap = $_POST['chapter'];
					$style = isset($_POST['style']) ? $_POST['style'] : '';
					
					
					$html = '';
					
					ob_start();
					
					global $wp_manga_volume, $wp_manga_database;
					$sort_setting = $wp_manga_database->get_sort_setting();

					$sort_by    = $sort_setting['sortBy'];
					$sort_order = $sort_setting['sort'];
					
					$all_chaps = $wp_manga_volume->get_volume_chapters( $manga_id, $volume_id, $sort_by, $sort_order );
					
					if($_POST['type'] == 'manga'){
						global $wp_manga_chapter_type;
						
						$wp_manga_chapter_type->reading_chapters_nav($manga_id, $all_chaps, $volume_id, $cur_chap, $style);
						
					} else {
						// text/video chapter
						global $wp_manga_text_type;
						$wp_manga_text_type->reading_chapters_nav($manga_id, $all_chaps, $volume_id, $cur_chap);
					}

					$html = ob_get_contents();
					ob_end_clean();
					
					echo $html;
					wp_die();
				
			}
			
			wp_send_json_error();
		}
		
		/**
		 * called to update manga views
		 **/
		function update_manga_views(){
            global $wp_manga_setting;
            
            $enabled_manga_view = $wp_manga_setting->get_manga_option('manga_view', 1);
            
            if($enabled_manga_view){
                if(isset($_POST['manga'])) {
                    $manga_id = intval($_POST['manga']);
                    $chapter_slug = '';
                    if(isset($_POST['chapter']) && $_POST['chapter'] != 'undefined'){
                        $chapter_slug = sanitize_text_field($_POST['chapter']);
                    }
                    
                    global $wp_manga_functions;
                    $wp_manga_functions->update_manga_views($manga_id, $chapter_slug);
                    
                    do_action('wp_manga_after_update_manga_views');
                    
                    wp_send_json_success( 'ok' );
                }
            }
			
			wp_send_json_error();
		}

		function save_user_player_id() {

			if ( empty( $_POST['userID'] ) || empty( $_POST['playerID'] ) ) {
				die();
			}

			$cur_player_ids = get_post_meta( $_POST['userID'], '_onesignal_player_id', true );

			if ( empty( $cur_player_ids ) && ! is_array( $cur_player_ids ) ) {
				$cur_player_ids = array( $_POST['playerID'] );
			} else {
				$cur_player_ids[] = $_POST['playerID'];
			}

			$resp = update_user_meta( $_POST['userID'], '_onesignal_player_id', $cur_player_ids );

			wp_send_json_success( $resp );

		}

		function wp_manga_delete_bookmark() {

			global $wp_manga_functions;
			$post_id         = isset( $_POST['postID'] ) ? $_POST['postID'] : '';
			$is_manga_single = $_POST['isMangaSingle'];

			$user_id        = get_current_user_id();
			$bookmark_manga = get_user_meta( $user_id, '_wp_manga_bookmark', true );

			if ( empty( $post_id ) || empty( $bookmark_manga ) ) {
				wp_send_json_error();
			}

			// Remove from user bookmark
			foreach ( $bookmark_manga as $index => $manga ) {
				if ( $manga['id'] == $post_id ) {
					unset( $bookmark_manga[ $index ] );
				}
			}

			// Remove from manga user bookmark
			$user_bookmarked = get_post_meta( $post_id, '_wp_user_bookmarked', true );

			if ( ! empty( $user_bookmarked ) ) {
				$index = array_search( $user_id, $user_bookmarked );

				if ( $index !== false ) {
					unset( $user_bookmarked[ $index ] );
					update_post_meta( $post_id, '_wp_user_bookmarked', $user_bookmarked );
				}
			}

			$resp = update_user_meta( $user_id, '_wp_manga_bookmark', $bookmark_manga );

			if ( $resp == true ) {
				if ( empty( $bookmark_manga ) && ! $is_manga_single ) {
					wp_send_json_success( array(
						'is_empty' => true,
						'msg'      => esc_html__( 'You haven\'t bookmarked any manga yet', WP_MANGA_TEXTDOMAIN )
					) );
				};
				$link = $wp_manga_functions->create_bookmark_link( $post_id, $is_manga_single );
				wp_send_json_success( $link );
			}

			wp_send_json_error();
		}

		function wp_manga_delete_multi_bookmark() {

			$bookmark_ids = isset( $_POST['bookmark'] ) ? $_POST['bookmark'] : null;

			$user_id        = get_current_user_id();
			$bookmark_manga = get_user_meta( $user_id, '_wp_manga_bookmark', true );

			if ( $bookmark_ids ) {
				if ( is_user_logged_in() ) {

					foreach ( $bookmark_manga as $index => $manga ) {
						if ( in_array( $manga['id'], $bookmark_ids ) ) {
							unset( $bookmark_manga[ $index ] );
						}

						// Remove from manga user bookmark
						$user_bookmarked = get_post_meta( $manga['id'], '_wp_user_bookmarked', true );
						$index           = array_search( $user_id, $user_bookmarked );

						if ( $index !== false ) {
							unset( $user_bookmarked[ $index ] );
							update_post_meta( $manga['id'], '_wp_user_bookmarked', $user_bookmarked );
						}

					}

					$resp = update_user_meta( $user_id, '_wp_manga_bookmark', $bookmark_manga );

					if ( $resp == true ) {
						if ( empty( $bookmark_manga ) ) {
							wp_send_json_success( array(
								'is_empty' => true,
								'msg'      => esc_html__( 'You haven\'t bookmark any manga yet', WP_MANGA_TEXTDOMAIN )
							) );
						};
						wp_send_json_success();
					}

					wp_send_json_error();
				}
			} else {
				wp_send_json_error( array( 'message' => __( 'Eh, try to cheat ahh !?', WP_MANGA_TEXTDOMAIN ) ) );
			}
			die( 0 );
		}

		function wp_manga_user_bookmark() {
			global $wp_manga_login, $wp_manga, $wp_manga_functions;

			if ( is_user_logged_in() ) {

				$post_id    = isset( $_POST['postID'] ) ? $_POST['postID'] : '';
				$chapter_slug = isset( $_POST['chapter'] ) ? $_POST['chapter'] : null;
				$paged      = isset( $_POST['page'] ) ? $_POST['page'] : '';
				$user_id    = get_current_user_id();

				if ( empty( $post_id ) || empty( $user_id ) ) {
					wp_send_json_error();
				}
				
				global $wp_manga_chapter;
				$chapter = $wp_manga_chapter->get_chapter_by_slug( $post_id, $chapter_slug );
				if($chapter){
					$chapter_id = $chapter['chapter_id'];
				} else {
					$chapter_id = 0;
				}

				$this_bookmark = array(
					'id'       => $post_id,
					'c'        => $chapter_id,
					'p'        => $paged,
					'unread_c' => [] // number of unread chapter
				);

				$current_bookmark = get_user_meta( $user_id, '_wp_manga_bookmark', true );
				if ( ! empty( $current_bookmark ) && is_array( $current_bookmark ) ) {

					//check if current manga is existed
					$col = 'id';
					$index = array_search( $post_id, array_map(function($element) use($col){return $element[$col];}, $current_bookmark) );

					if ( $index !== false ) {
						$this_bookmark['unread_c']  = $current_bookmark[ $index ]['unread_c'];
						$current_bookmark[ $index ] = $this_bookmark;
						$manga_existed              = true;
					} else {
						$current_bookmark[] = $this_bookmark;
					}

				} else {
					$current_bookmark = array( $this_bookmark );
				}
				
				global $wp_manga_setting;
				$max_bookmark_count = $wp_manga_setting->get_manga_option( 'user_bookmark_max', 30 );
				
				// only store $max_bookmark_count latest item in users_bookmarked;
				$sliced_mangas = array();
				if(count($current_bookmark) > $max_bookmark_count){
					$sliced_mangas = array_slice($current_bookmark, 0, count($current_bookmark) - $max_bookmark_count);
					$current_bookmark = array_slice($current_bookmark, count($current_bookmark) - $max_bookmark_count);
				}

				$response = update_user_meta( $user_id, '_wp_manga_bookmark', $current_bookmark );

				if ( $response ) {
					
					if ( empty( $manga_existed ) ) {
						// Update user id to manga bookmarked meta
						$users_bookmarked = get_post_meta( $post_id, '_wp_user_bookmarked', true );

						if ( empty( $users_bookmarked ) ) {
							$users_bookmarked = array();
						}

						if ( is_array( $users_bookmarked ) && in_array( $user_id, $users_bookmarked ) === false ) {
							$users_bookmarked[] = $user_id;
						}

						update_post_meta( $post_id, '_wp_user_bookmarked', $users_bookmarked );
					}

					$is_manga_single = false;
					if ( empty( $chapter_slug ) ) {
						$is_manga_single = true;
					}
					
					$link = $wp_manga_functions->create_bookmark_link( $post_id, $is_manga_single, $chapter_slug );
					
					// remove $user_id in sliced mangas 
					if(count($sliced_mangas) > 0){
						foreach($sliced_mangas as $key => $sliced_manga){
							$users_bookmarked = get_post_meta( $sliced_manga['id'], '_wp_user_bookmarked', true );
							if ( is_array( $users_bookmarked ) && ($idx = array_search($user_id, $users_bookmarked)) !== false ) {
								unset($users_bookmarked[$idx]);
								update_post_meta( $sliced_manga['id'], '_wp_user_bookmarked', $users_bookmarked );
							}
						}
					}
					
					wp_send_json_success( $link );
				}

				wp_send_json_error( $response );


			} else {
				wp_send_json_error( array( 'code' => 'login_error' ) );
			}
		}

		function wp_manga_get_next_manga() {

			global $wp_manga_functions;

			$paged    = isset( $_POST['paged'] ) ? $_POST['paged'] : null;
			$term     = isset( $_POST['term'] ) ? $_POST['term'] : null;
			$taxonomy = isset( $_POST['taxonomy'] ) ? $_POST['taxonomy'] : null;
			$orderby  = isset( $_POST['orderby'] ) ? $_POST['orderby'] : 'latest';

			if ( $paged ) {
				$args = array(
					'post_type'   => 'wp-manga',
					'post_status' => 'publish',
					'paged'       => $paged,
				);

				if ( $term && $taxonomy ) {
					$args['tax_query'] = array(
						array(
							'taxonomy' => $taxonomy,
							'field'    => 'slug',
							'terms'    => $term,
						),
					);
				}

				if ( $orderby ) {
					switch ( $orderby ) {
						case 'latest':
							$args['orderby']  = 'meta_value_num';
							$args['meta_key'] = '_latest_update';
							break;
						case 'alphabet':
							$args['orderby'] = 'post_title';
							$args['order']   = 'ASC';
							break;
						case 'rating':
							$args['orderby']  = 'meta_value_num';
							$args['meta_key'] = '_manga_avarage_reviews';
							break;
						case 'trending':
							$args['orderby']  = 'meta_value_num';
							$args['meta_key'] = '_wp_manga_week_views';
							break;
						case 'most-views':
							$args['orderby']  = 'meta_value_num';
							$args['meta_key'] = '_wp_manga_views';
							break;
						case 'new-manga':
							$args['orderby'] = 'date';
							$args['order']   = 'DESC';
							break;
						default:
							$args['orderby'] = 'date';
							$args['order']   = 'DESC';
							break;
					}
				}

				$manga = new WP_Query( $args );

				if ( $manga->posts ) {
					$max_page = $manga->max_num_pages;
					$result   = array();
					foreach ( $manga->posts as $post ) {
						$html              = $wp_manga_functions->get_html( $post->ID );
						$result['posts'][] = $html;
					}

					if ( intval( $max_page ) == intval( $paged ) ) {
						$result['next'] = null;
					} else {
						$result['next'] = intval( $paged ) + 1;
					}
					wp_send_json_success( $result );
				} else {
					wp_send_json_error( array( 'code' => 'no-post' ) );
				}
			} else {
				wp_send_json_error( array( 'code' => 'no-page' ) );
			}
		}

		function wp_manga_save_rating() {

			global $wp_manga_functions;

			$postID = isset( $_POST['postID'] ) ? $_POST['postID'] : null;
			$rating = isset( $_POST['star'] ) ? floatval($_POST['star']) : null;
            
            global $wp_manga_setting;
			$guest_rating = $wp_manga_setting->get_manga_option( 'guest_rating', 1 );
            
            $logged_in = is_user_logged_in();
            
            if(!$guest_rating && !$logged_in){
                // if not allow guest rating, and user is not logged in
                return;
            }

			if ( !empty( $rating ) && $postID && $rating >= 0 && $rating <= 5 ) {
				$key          = '_manga_reviews';
				$prev_reviews = get_post_meta( $postID, $key, true );

				if ( '' == $prev_reviews ) {
					$prev_reviews = array();
				}

				if ( $logged_in ) {
					$new_reviews                          = $prev_reviews;
					$new_reviews[ get_current_user_id() ] = $rating;
				} else {
					$ipaddress                 = $wp_manga_functions->get_client_ip();
					$new_reviews               = $prev_reviews;
					$new_reviews[ $ipaddress ] = $rating;
				}

				update_post_meta( $postID, $key, $new_reviews, $prev_reviews );
				$review = $wp_manga_functions->get_total_review( $postID, $new_reviews );
				update_post_meta( $postID, '_manga_avarage_reviews', $review );

				$rating_html = $wp_manga_functions->manga_rating( $postID, true );

				wp_send_json_success( array(
					'rating_html' => $rating_html,
					'text'        => sprintf( _n( 'Average %1s / %2s out of %3s total vote.', 'Average %1s / %2s out of %3s total votes.', count( $new_reviews ), WP_MANGA_TEXTDOMAIN ), $review, '5', count( $new_reviews ) ),
				) );
			}
		}

		function wp_manga_upload_avatar() {
			global $wp_manga_setting;
			$user_can_upload_avatar = $wp_manga_setting->get_manga_option('user_can_upload_avatar', '1');
			
			if($user_can_upload_avatar == '1'){
				if( empty( $_FILES['userAvatar'] ) ){
					wp_send_json_error(__("Invalid data", WP_MANGA_TEXTDOMAIN));
				}

				$avatar_file = $_FILES['userAvatar'];
				$user_id     = get_current_user_id();

				if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], '_wp_manga_save_user_settings' ) || empty( $user_id ) ) {
					wp_send_json_error( array( 'msg' => __( 'I smell some cheating here', WP_MANGA_TEXTDOMAIN ) ) );
				}

				// validate image

				$limit_size = intval($wp_manga_setting->get_manga_option('custom_avatar_size', 2));
				if($avatar_file['size'] > $limit_size * 1000000) {
					// invalid
					wp_send_json_error( array( 'msg' => sprintf(__( 'Upload failed! Maximum size is %sMB', WP_MANGA_TEXTDOMAIN ), $limit_size ) ));
				}
				
				$min_px = intval($wp_manga_setting->get_manga_option('custom_avatar_min', 200)); // px
				$sizes = getimagesize($avatar_file["tmp_name"]);
				if($sizes[0] < $min_px || $sizes[1] < $min_px){
					// invalid
					wp_send_json_error( array( 'msg' => sprintf(__( 'Upload failed! Image is too small. Minimum width and height is %spx', WP_MANGA_TEXTDOMAIN ), $min_px )) );
				}

				//handle upload
				require_once( ABSPATH . 'wp-admin/includes/admin.php' );
				$avatar = wp_handle_upload( $avatar_file, array('test_form' => false, 'mimes' => array('jpg|jpeg|jpe' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif')) );
				
				if ( isset( $avatar['error'] ) || isset( $avatar['upload_error_handler'] ) ) {
					wp_send_json_error( array( 'msg' => __( 'Upload failed! Please try again later', WP_MANGA_TEXTDOMAIN ) ) );
				}

				//resize avatar
				$avatar_editor = wp_get_image_editor( $avatar['file'] );
				if ( ! is_wp_error( $avatar_editor ) ) {
					$avatar_editor->resize( 195, 195, false );
					$avatar_editor->save( $avatar['file'] );
				}				

				//media upload
				$avatar_media = array(
					'post_mime_type' => $avatar['type'],
					'post_title'     => '_wp_user_' . $user_id . '_avatar',
					'post_content'   => '',
					'post_status'    => 'inherit',
					'guid'           => $avatar['url'],
					'post_author'    => $user_id,
				);
				
				$avatar_id = wp_insert_attachment( $avatar_media, $avatar['file'] );

				if ( $avatar_id == 0 ) {
					wp_send_json_error( array( 'msg' => __( 'Upload failed! Please try again later', WP_MANGA_TEXTDOMAIN ) ) );
				}				

				$imagenew = get_post( $avatar_id );
				$fullsizepath = get_attached_file( $imagenew->ID );
				$attach_data = wp_generate_attachment_metadata( $avatar_id, $fullsizepath );
				wp_update_attachment_metadata( $avatar_id, $attach_data );
				
				// remove old avatar
				$current_avatar_id = get_user_meta( $user_id, '_wp_manga_user_avt_id', true );
				
				if($current_avatar_id){
					$attachment_meta = wp_delete_attachment($current_avatar_id, true);
					if($attachment_meta !== false && $attachment_meta){
						$file_url = $attachment_meta->guid;
						
						$file_path = str_replace(home_url('/'), ABSPATH, $file_url);
						if(file_exists($file_path)){
							unlink($file_path);
						}
					}
				}

				//update metadata
				$user_meta   = update_user_meta( $user_id, '_wp_manga_user_avt_id', $avatar_id );
				$avatar_meta = update_post_meta( $avatar_id, '_wp_manga_user_id', $user_id );

				if ( ! empty( $user_meta ) && ! empty( $avatar_meta ) ) {
					wp_send_json_success( get_avatar( $user_id, 195 ) );
				} else {
					wp_send_json_error( array( 'msg' => __( 'Upload failed! Please try again later', WP_MANGA_TEXTDOMAIN ) ) );
				}
			}
		}

		/**
		 * Get user section for main and off-canvas
		 */
		function wp_manga_get_user_section() {

			if ( ! is_user_logged_in() ) {
				wp_send_json_error();
			}

			global $wp_manga_user_actions;
			$main_user_section = $wp_manga_user_actions->get_user_section();
			$off_canvas_user_section = $wp_manga_user_actions->get_offcanvas_user_section( );

			wp_send_json_success( array(
				'main' => $main_user_section,
				'off_canvas' => $off_canvas_user_section
			) );
		}

		function chapter_navigate_page() {
			global $wp_manga_template, $wp_manga_chapter_type, $wp_manga_chapter, $wp_manga_volume, $wp_manga, $wp_manga_storage, $post, $wp_query;
			
			if ( empty( $_GET['postID'] ) ) {
				$this->send_json( 'error', esc_html__( 'Missing post ID', WP_MANGA_TEXTDOMAIN ) );
			}

			$manga_id = $_GET['postID'];

			if ( empty( $_GET[$wp_manga->manga_paged_var] ) ) {
				$this->send_json( 'error', esc_html__( 'Missing Query Page', WP_MANGA_TEXTDOMAIN ) );
			}

			if ( empty( $_GET['chapter'] ) ) {
				$this->send_json( 'error', esc_html__( 'Missing Chapter param', WP_MANGA_TEXTDOMAIN ) );
			}

			$this_post = get_post( $manga_id );

			$post = $this_post;

			$chapter = $wp_manga_chapter->get_chapter_by_slug( $manga_id, $_GET['chapter'] );

			if ( empty( $chapter ) ) {
				$this->send_json( 'error', esc_html__( 'Chapter not found', WP_MANGA_TEXTDOMAIN ) );
			}
			
			$options = get_option( 'wp_manga_settings', array() );
			$chapter_slug_or_id = isset( $options['chapter_slug_or_id'] ) ? $options['chapter_slug_or_id'] : 'slug';
				
			if($chapter_slug_or_id == 'id'){
				$wp_query->set( 'chapter', $chapter['chapter_id'] );
				$_GET['chapter'] = $chapter['chapter_slug'];
			}
			
			set_query_var('chapter', $_GET['chapter']);

			$volume = $wp_manga_chapter->get_chapter_volume( $manga_id, $chapter['chapter_id'] );

			if ( ! empty( $volume ) ) {
				$volume_slug = $wp_manga_storage->slugify( $volume['volume_name'] );
				$wp_query->set( 'volume', $volume_slug );
			}

			$output = array();
			
			ob_start();

			$paged = ! empty( $_GET[$wp_manga->manga_paged_var] ) ? $_GET[$wp_manga->manga_paged_var] : 1;
			
			$style = ! empty( $_GET['style'] ) ? $_GET['style'] : 'paged';

			echo apply_filters( 'madara_ads_before_content', madara_ads_position( 'ads_before_content', 'body-top-ads' ) ); ?>

            <div class="reading-content">
				<input type="hidden" id="wp-manga-current-chap" data-id="<?php echo esc_attr($chapter['chapter_id']);?>" value="<?php echo esc_attr($_GET['chapter']);?>"/>
				<?php 
				
				/**
				 * If alternative_content is empty, show default content
				 **/
				$alternative_content = apply_filters('wp_manga_chapter_content_alternative', '');
				
				if(!$alternative_content){
					set_query_var($wp_manga->manga_paged_var, $paged);
					do_action('wp_manga_chapter_content', $chapter, $manga_id);
				} else {
					echo $alternative_content;
				}
				?>

            </div>

			<?php echo apply_filters( 'madara_ads_after_content', madara_ads_position( 'ads_after_content', 'body-bottom-ads' ) ); ?>

			<?php
			$output['content'] = ob_get_contents();

			ob_end_clean();

			ob_start();
			
			$wp_manga->manga_nav( 'footer' );

			$output['nav'] = ob_get_contents();

			ob_end_clean();

			$output = apply_filters( 'madara_ajax_next_page_content', $output );

			$this->send_json( 'success', '', $output );
		}

		/**
		 * Ajax search for manga
		 */
		function wp_manga_search_manga() { 

			$title = sanitize_text_field(isset( $_POST['title'] ) ? $_POST['title'] : '');
			if ( ! $title ) {
				wp_send_json_error( array(
					array(
						'error'   => 'empty title',
						'message' => esc_html__( 'No manga found', WP_MANGA_TEXTDOMAIN ),
					)
				) );
			}

			$results = array();
			$is_search_text_available = get_option('wp_manga_search_text_built', 0);
			if($is_search_text_available){
				global $wpdb;
				// search maximum 10 results
				$sql = $wpdb->prepare("SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'wp-manga' AND post_status='publish' AND (post_title LIKE %s OR post_excerpt LIKE %s OR wp_manga_search_text LIKE %s) LIMIT 10", '%' . $title . '%', '%' . $title . '%', '%' . $title . '%');
			
				$query_results = $wpdb->get_results($sql);
				foreach($query_results as $post){
					$manga_id = $post->ID;
					$type = get_post_meta($manga_id, '_wp_manga_chapter_type', true);
					$results[] = array(
						'title' => get_post_field( 'post_title', $manga_id ),
						'url'   => get_permalink( $manga_id ),
						'type' => $type ? $type : 'manga'
					);
				}
			} else {
				$args_query = array(
					'post_type'      => 'wp-manga',
					'posts_per_page' => 6,
					'post_status'    => 'publish',
					's'              => $title,
				);

				$args_query = apply_filters( 'madara_manga_query_args', $args_query );
				
				$wp_manga = WP_MANGA::get_instance();
				add_filter( 'posts_clauses', array($wp_manga,'wp_query_search_clauses'), 10, 2 );
				$query = new WP_Query( $args_query );
				remove_filter( 'posts_clauses', array($wp_manga,'wp_query_search_clauses'), 10 );

				$query = apply_filters( 'madara_manga_query_filter', $query, $args_query );

				if ( $query->have_posts() ) {
					while ( $query->have_posts() ) {
						$query->the_post();
						$manga_id = get_the_ID();
						$type = get_post_meta($manga_id, '_wp_manga_chapter_type', true);
						$results[] = array(
							'title' => get_post_field( 'post_title', $manga_id ),
							'url'   => get_permalink( $manga_id ),
							'type' => $type ? $type : 'manga'
						);
					}
				}
				wp_reset_query();
			}
			
			if(!count($results)){
				wp_send_json_error( array(
						array(
							'error'   => 'not found',
							'message' => __( 'No Manga found', WP_MANGA_TEXTDOMAIN )
						)
					) );
			} else {
				wp_send_json_success( $results );
			}

			die( 0 );
		}


		function send_json( $type, $msg, $data = null ) {

			$response = array(
				'message' => $msg
			);

			if ( $data ) {
				$response['data'] = $data;
			}

			if ( $type == 'success' ) {
				wp_send_json_success( $response );
			} else {
				wp_send_json_error( $response );
			}

		}
	}

	new WP_MANGA_AJAX_FRONTEND();
