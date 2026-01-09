<?php

	class WP_MANGA_USER_ACTION {

		public $user_actions;

		public function __construct() {

			add_shortcode( 'manga-user-page', array( $this, 'wp_manga_user_page' ) );

			add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_manga_settings' ), 10, 1 );

			add_action( 'wp', array( $this, 'wp_manga_user_page_redirect' ), 1 );

			add_action( 'manga_chapter_inserted', array( $this, 'prepare_notification_new_chapters' ), 10, 2 );

			add_action( 'manga_history', array( $this, 'remove_unread_chapter' ), 10, 2 );

			add_action( 'wp_footer', array( $this, 'get_user_player_id' ) );

		}

		function wp_manga_user_page() {
			global $wp_manga_template;

			$content = '';
			ob_start();
			$wp_manga_template->load_template( 'user/settings', '', true );
			$content = ob_get_contents();
			ob_end_clean();
			

			return $content;

		}

		function wp_manga_user_page_redirect() {
			
			global $wp_manga_setting;

			$user_page = $wp_manga_setting->get_manga_option( 'user_page' );

			if ( $user_page == get_the_ID() && ! is_user_logged_in() ) {
				wp_safe_redirect( home_url( '/' ), 302 );
			}

		}

		function wp_manga_user_section() {

			global $wp_manga_functions;

			$html = '<div class="c-modal_item">';

			if ( is_user_logged_in() ) {

				$html .= $this->get_user_section();

			} else {

				$html .= '<a href="javascript:void(0)" data-toggle="modal" data-target="#form-login" class="btn-active-modal">' . esc_html__( 'Sign in', WP_MANGA_TEXTDOMAIN ) . '</a>';
				$html .= '<a href="javascript:void(0)" data-toggle="modal" data-target="#form-sign-up" class="btn-active-modal">' . esc_html__( 'Sign up', WP_MANGA_TEXTDOMAIN ) . '</a>';

			}

			$html .= '</div>';

			return $html;

		}

		function get_offcanvas_user_section($size = 80, $echo = false) {
			$html = '';
			if ( is_user_logged_in() ) {
				ob_start();
				$current_user = wp_get_current_user();
				?>
				<div class="c-user_item">
					<div class="c-user_avatar" >
						<div class="c-user_avatar-image">
							<?php echo get_avatar( $current_user->ID, 80 ); ?>
						</div>
					</div>
					<span class="c-user_name"><?php echo esc_html( $current_user->display_name ); ?></span>
				</div>
				
			<?php 
				$html = ob_get_contents();
				ob_end_clean();
			}

			return $html;
		}

		function get_user_section( $size = 50, $echo = false ) { 
			global $wp_manga_setting, $wp;

			if ( ! is_user_logged_in() ) {
				return false;
			}

			$html = '';

			$current_user = wp_get_current_user();
			$link = $this->get_user_setting_page_url();

			$user_menu = '';

			$html = '<div class="c-user_item">';

			$html .= '<span>' . esc_html__( 'Hi, ', WP_MANGA_TEXTDOMAIN ) . $current_user->display_name . '</span>';
			$html .= '<div class="c-user_avatar">';
				$html .= '<div class="c-user_avatar-image">';
					$html .= get_avatar( $current_user->ID, $size );

				//$notify_num = $this->get_user_notify_num();

				//if ( ! empty( $notify_num ) ) {
					//$html .= '<div class="c-user_notify">' . $notify_num . '</div>';
				//}
				
				$html .= '</div>';

			$html .= '<ul class="c-user_menu">';
			
			$user_menu .= apply_filters('wp_manga_user_menu_before_items', '');
			
			if ( ! empty( $link ) ) {
				$user_menu .= '<li><a href="' . esc_url( "{$link}?tab=bookmark" ) . '">' . esc_html__( 'My Bookmarks', WP_MANGA_TEXTDOMAIN ) . '</a></li>';
			}

			//get menu items
			if ( $menu_items = $this->get_user_menu_items() ) {
				$user_menu .= implode( '', $menu_items );
			}
			if ( ! empty( $link ) ) {
				$user_menu .= '
					<li>
						<a href="' . esc_url( $link ) . '">' . esc_html__( 'User Settings', WP_MANGA_TEXTDOMAIN ) . '</a>
					</li>';
			}
			
			$user_menu .= apply_filters('wp_manga_user_menu_after_items', '');

			$user_menu .= '
                <li>
                    <a href="' . wp_logout_url( home_url( $wp->request ) ) . '">' . esc_html__( 'Logout', WP_MANGA_TEXTDOMAIN ) . '</a>
                </li>
            ';

			$user_menu = apply_filters( 'madara_user_menu_profile', $user_menu );

			$html .= $user_menu . '</ul>';
			$html .= '</div>';
			$html .= '</div>';

			if($echo) {
				echo apply_filters( 'madara_user_profile', $html, $size );
			} else {
				return apply_filters( 'madara_user_profile', $html, $size );
			}
		}

		function get_user_menu_items() {
			if ( has_nav_menu( 'user_menu' ) ) {

				$output = '';
				ob_start();
				wp_nav_menu( array( 'theme_location' => 'user_menu' ) );
				$output = ob_get_contents();
				ob_end_clean();

				preg_match_all( '/<li(.+)<\/li>/', $output, $matches );

				if ( ! empty( $matches[0] ) ) {
					return apply_filters('wp_manga_user_menu_items', $matches[0]);
				}

			}

			return false;

		}

		function get_user_setting_page_url() {

			global $wp_manga_setting;

			$user_page = $wp_manga_setting->get_manga_option( 'user_page', null );

			if($user_page){
				if(function_exists('pll_get_post')){
					return get_the_permalink( pll_get_post($user_page) );
				}
				else {
					return get_the_permalink( $user_page );
				}
			}
			
			return '';
		}

		function get_user_tab_url( $tab ) {

			$user_page_url = $this->get_user_setting_page_url();

			if ( empty( $user_page_url ) ) {
				return false;
			}

			return add_query_arg( [ 'tab' => $tab ], $user_page_url );

		}

		function add_admin_bar_manga_settings( $bar ) {

			global $wp_manga_setting;

			$user_page = $wp_manga_setting->get_manga_option( 'user_page' );

			if ( $user_page ) {
				$link = get_the_permalink( $user_page );
				$bar->add_node( array(
					'id'     => 'wp-manga-settings',
					'title'  => __( 'Manga Settings', WP_MANGA_TEXTDOMAIN ),
					'href'   => $link,
					'parent' => 'user-actions',
				) );
			}
		}

		function user_save_settings() {

			global $wp_manga_setting;

			if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( esc_html( $_POST['_wpnonce'] ), '_wp_manga_save_user_settings' ) ) {
				return false;
			}

			$current_user = get_current_user_id();

			$reading_style = sanitize_text_field(isset( $_POST['reading-style'] ) ? $_POST['reading-style'] : 'paged');

			update_user_meta( $current_user, '_manga_reading_style', $reading_style );
			wp_safe_redirect( get_the_permalink( $wp_manga_setting->get_manga_option( 'user_page' ) ) . '#setting' );
			exit;

		}

		function get_user_notify_num( $user_id = null, $post_id = null ) {

			if ( empty( $user_id ) ) {
				$user_id = get_current_user_id();
			}

			if ( empty( $user_id ) || ! is_user_logged_in() ) {
				return '';
			}

			$user_bookmarks = get_user_meta( $user_id, '_wp_manga_bookmark', true );

			if ( empty( $user_bookmarks ) ) {
				return '';
			}
			// If this is getting notify of specific manga
			if ( $post_id ) {
				$manga = $this->get_manga_bookmark( $post_id, $user_bookmarks );

				if ( empty( $manga ) || empty( $manga['unread_c'] ) ) {
					return '';
				}

				return count( isset($manga['unread_c']) ? $manga['unread_c'] : array() );

			} else {
				$col = 'unread_c';
				$mangas = array_map(function($element) use($col ){return $element[$col ];}, $user_bookmarks);

				$total = 0;
				foreach ( $mangas as $manga ) {
					$total += is_array( $manga ) ? count( $manga ) : 0;
				}

				return $total;

			}
		}

        /**
         * Find index of bookmark that user bookmarked this manga
         **/
		function get_manga_bookmark_index( $manga_id, $bookmarks ) {

			if ( ! is_array( $bookmarks ) ) {
				return false;
			}
			$col = 'id';
			$ids = array_map(function($element) use($col ){return isset($element[$col]) ? $element[$col] : 0;}, $bookmarks);

			if ( ! is_array( $ids ) ) {
				return false;
			}

			return 
				$index = array_search( $manga_id, $ids );
		}

		// Get Manga from user bookmark array
		function get_manga_bookmark( $post_id, $bookmarks ) {

			$index = $this->get_manga_bookmark_index( $post_id, $bookmarks );

			if ( $index === false ) {
				return $index;
			}

			return isset($bookmarks[ $index ]) ? $bookmarks[ $index ] : null;

		}
        
        /**
         * Add the manga which has the newly uploaded chapters to the queue list, so that we can send notifications to its subscribers
         **/
		function prepare_notification_new_chapters( $chapter_id, $chapter_args ) {
            $is_notify_enable  = $this->is_new_chapter_notify_enable();
                    
            if ( empty( $is_notify_enable ) ) {
                return;
            }
            
            $new_chapters = get_transient('_wp_manga_recent_chapters');
            if(!$new_chapters) $new_chapters = [];
            
            $found = false;
            foreach($new_chapters as $index => $new_chapter){
                if($new_chapter['post_id'] == $chapter_args['post_id']){
                    // merge chapter_id values
                    if(!isset($new_chapter['chapter_id'])){
                        $new_chapter['chapter_id'] = [];
                    }
                    $new_chapter['chapter_id'][] = $chapter_id;
                    $new_chapters[$index] = $new_chapter;
                    $found = true;
                    break;
                }
            }
            
            if(!$found){
                $chapter_args['chapter_id'] = [$chapter_id];
                $new_chapters[] = $chapter_args;
            }
			
			
            set_transient('_wp_manga_recent_chapters', $new_chapters);
		}
        
        /**
         * Send notification (new chapters) to each user's account
         * Loop through 10 mangas which has new chapters, and try to send notification to the next 500 subscribers of each manga
         **/
        function send_users_notifications(){
            $is_notify_enable  = $this->is_new_chapter_notify_enable();
            $is_webpush_enable = $this->is_webpush_enable();
   
            if ( empty( $is_notify_enable ) ) {
                return;
            }
              
            $new_chapters = get_transient('_wp_manga_recent_chapters');
			
            if($new_chapters && count($new_chapters) > 0){
                $new_chapters_top = array_slice($new_chapters, 0, apply_filters('wp_manga_users_notification_chapter_batch', 10));
                $list_changed = false;
                foreach($new_chapters_top as $new_chapter){
                    $chapter_ids = $new_chapter['chapter_id'];
                    $manga_id = $new_chapter['post_id'];

                    // Get the list of users who have bookmarked this manga
                    $users_bookmarked = get_post_meta( $manga_id, '_wp_user_bookmarked', true );
                    
                    // get the last index of user that we have sent notification
                    $manga_notification_index = get_post_meta( $manga_id, '_manga_notification_index', true );
                    if(!$manga_notification_index) $manga_notification_index = 0;
                    
                    if(empty( $users_bookmarked ) || (is_array($users_bookmarked) && $manga_notification_index >= count($users_bookmarked))){
                        // we have sent notifications to all subscribers of this manga, so remove this manga from the list
                        unset($new_chapters[array_search($new_chapter, $new_chapters)]);
                        $list_changed = true;
                        delete_post_meta( $manga_id, '_manga_notification_index' );
                        continue;
                    }
                    
                    // get the next 100 users to send notification
					if (is_serialized($users_bookmarked)) {
						$users_bookmarked = unserialize($users_bookmarked);
						if (is_serialized($users_bookmarked)) {
							$users_bookmarked = unserialize($users_bookmarked);
						}
					}
					
					$users_to_notify = $users_bookmarked ? array_slice($users_bookmarked, $manga_notification_index, apply_filters('wp_manga_users_notification_user_batch', 100)) : [];

                    $cur_notifications = get_transient( '_wp_manga_chapter_notifications' );
                    
                    global $wp_manga_setting;
                    $max_bookmark_count = $wp_manga_setting->get_manga_option( 'user_bookmark_max', 30 );
					
                    foreach ( $users_to_notify as $user_id ) {
                        $manga_notification_index++;
                        
                        // All manga that user bookmarked
                        $user_bookmark = get_user_meta( $user_id, '_wp_manga_bookmark', true );
                        if(!is_array($user_bookmark)) $user_bookmark = array();
                        
                        // only store $max_bookmark_count latest item in users_bookmarked;
                        if(count($user_bookmark) > $max_bookmark_count){
                            $user_bookmark = array_slice($user_bookmark, count($user_bookmark) - $max_bookmark_count);
                        }
                        
                        $index = $this->get_manga_bookmark_index( $manga_id, $user_bookmark );

                        if ( $index === false || empty( $user_bookmark[ $index ] ) ) {
                            continue;
                        }
                        
                        foreach($chapter_ids as $chapter_id){
                            // Add this new chapter to the list of unread chapters
                            if ( ! is_array( $user_bookmark[ $index ]['unread_c'] ) ) {
                                $user_bookmark[ $index ]['unread_c'] = array( $chapter_id );
                            } else {
                                $user_bookmark[ $index ]['unread_c'][] = $chapter_id;
                            }
                        }
                        
                        if(count($user_bookmark[ $index ]['unread_c']) > 20){
                            // reduce number of unread chapters, so the $user_bookmark is not big
                            $temp = array_slice($user_bookmark[$index]['unread_c'], count($user_bookmark[ $index ]['unread_c']) - 20);
                            $user_bookmark[ $index ]['unread_c'] = $temp;
                        }

                        $resp = update_user_meta( $user_id, '_wp_manga_bookmark', $user_bookmark );

                        if ( $resp && $is_webpush_enable ) {
                            if(empty($cur_notifications)) {
                                
                                $cur_notifications = array();
                                
                            }
                            
                            if(!isset($cur_notifications[ $manga_id ])) {
                                
                                $cur_notifications[ $manga_id ] = array();
                                
                            }
                            
                            foreach($chapter_ids as $chapter_id){
                                if(!isset($cur_notifications[ $manga_id ][ $chapter_id ])) {
                                    
                                    $cur_notifications[ $manga_id ][ $chapter_id ] = array();
                                    
                                }
                                
                                $cur_notifications[ $manga_id ][ $chapter_id ][] = $user_id;
                            }
                        }
                        
                    }
                    
                    // check again
                    if ( $is_webpush_enable ) {
                        set_transient( '_wp_manga_chapter_notifications', $cur_notifications, 24 * 60 * 60 );
                    }

					update_post_meta( $manga_id, '_manga_notification_index', $manga_notification_index );
					
					// check again if we have sent notifications to all subscribers of this manga, so remove this manga from the list
					if($manga_notification_index >= count($users_bookmarked)){
                        unset($new_chapters[array_search($new_chapter, $new_chapters)]);
                        $list_changed = true;
                        delete_post_meta( $manga_id, '_manga_notification_index' );
                        continue;
                    }
                }
                
                if($list_changed){
                    set_transient('_wp_manga_recent_chapters', $new_chapters);
                }
            }
        }
        
        /**
         * Send notification (new chapters) to OneSignal
         **/
		function send_onesignal_notifications() {
			
            /*if(!$this->is_onesignal_active()){
                return;
            }
            */
			$notifications = get_transient( '_wp_manga_chapter_notifications' );

			//error_log("_wp_manga_chapter_notifications: " . var_export($notifications,true));

			if ( empty( $notifications ) && ! is_array( $notifications ) ) {
				return;
			}

			// Send 100 notifications each request
			$index = 1;
			global $wp_manga_chapter;
			foreach ( $notifications as $post_id => $chapters ) {
				
				// If this is manga chapter type then check if it's uploaded completed
				$completed_chapters = $wp_manga_chapter->get_manga_chapters( $post_id	);	
				if(!$completed_chapters || count($completed_chapters) == 0 || count($chapters) == 0 ){
					unset($notifications[$post_id]);
					continue;
				}

				foreach ( $chapters as $chapter_id => $users ) {
					if(!$chapter_id){
						unset( $notifications[ $post_id ][ $chapter_id ] );
						continue;
					}

					if ( $index == apply_filters('wp_manga_onesignal_batch_count', 100) ) {
						break;
					}

					// Check if chapter is upload completed, or else skip
					$found = false;
					foreach($completed_chapters as $completed_chapter){
						if($completed_chapter['chapter_id'] == $chapter_id){
							$found = true;
							break;
						}
					}
					if ( !$found ) {
						// Chapter ID does not exist, unset
						unset( $notifications[ $post_id ][ $chapter_id ] );

						continue;
					}

					$users_player_ids = array();
					
					//error_log('user to send: ' . var_export($users, true));
					foreach ( $users as $user ) {
						
						$users_player_ids[] = wp_manga_generate_onesignal_externalid($user);
						
						/*
						// an user could have more than one player ids
						$player_ids = get_user_meta( $user, '_onesignal_player_id', true );

						if ( ! empty( $player_ids ) ) {
							$users_player_ids = array_merge( $users_player_ids, $player_ids );
						}
						*/
					}
					//error_log('onesignal send: ' . var_export($users_player_ids, true));
					
					if ( ! empty( $users_player_ids ) ) {
					
						$resp = $this->send_notification( $post_id, $chapter_id, $users_player_ids );

						if ( is_wp_error( $resp ) ) {
							error_log( $resp->get_error_message() );
						} else {
							unset( $notifications[ $post_id ][ $chapter_id ] );
						}

						$index ++;
					}

				}
			}

			return set_transient( '_wp_manga_chapter_notifications', $notifications, 24 * 60 * 60 );

		}

		function send_notification( $post_id, $chapter_id, $player_ids ) {

			if ( ! $this->is_onesignal_active() ) {
				return new WP_Error( 'OneSignal', 'OneSignal is not activated' );
			}

			global $wp_manga_setting, $wp_manga_chapter, $wp_manga_functions;

			// Prepare content
			$content = $wp_manga_setting->get_manga_option( 'webpush_noti_content', '%manga% has new chapter %chapter%!' );

			$manga_name   = get_the_title( $post_id );
			$chapter      = $wp_manga_chapter->get_chapter_by_id( $post_id, $chapter_id );
			$chapter_name = $wp_manga_functions->parse_chapter_full_name( $chapter );

			if ( empty( $chapter ) ) {
				return new WP_Error( '404', esc_html__('Chapter not found', WP_MANGA_TEXTDOMAIN) );
			}

			$content = str_replace( '%manga%', $manga_name, $content );
			$content = str_replace( '%chapter%', $chapter_name, $content );

			if ( empty( $player_ids ) || ! is_array( $player_ids ) ) {
				return false;
			}

			// Content
			// $notif_content = OneSignalUtils::decode_entities( $content );

			// Url
			$page_style = apply_filters('wp_manga_default_notification_chapter_reading_style', 'paged');
			$url = $wp_manga_functions->build_chapter_url( $post_id, $chapter['chapter_slug'], $page_style );

			// Title
			$os_settings = get_option('OneSignalWPSetting', onesignal_get_default_settings());

			$site_title  = get_bloginfo( 'name' );

			//$site_title = OneSignalUtils::decode_entities( $site_title );

			$fields = array(
				'app_id'             => $os_settings['app_id'],
				'headings'           => array( "en" => $site_title ),
				'include_aliases' => array("external_id" => $player_ids),
				'target_channel' => 'push',
				'isAnyWeb'           => true,
				'url'                => $url,
				'contents'           => array( "en" => html_entity_decode($content) )
			);

			$send_to_mobile = $os_settings['send_to_mobile_platforms'];
			if ( $send_to_mobile == true ) {
				$fields['isIos']     = true;
				$fields['isAndroid'] = true;
			}

			if ( ! empty( $os_settings['utm_additional_url_params'] ) ) {
				$fields['url'] .= '?' . $os_settings['utm_additional_url_params'];
			}

			if ( has_post_thumbnail( $post_id ) ) {

				$post_thumbnail_id = get_post_thumbnail_id( $post_id );

				// Higher resolution (2x retina, + a little more) for the notification small icon
				$thumb_sized = wp_get_attachment_image_src( $post_thumbnail_id, array( 192, 192 ), true );

				// Much higher resolution for the notification large image
				$large_sized = wp_get_attachment_image_src( $post_thumbnail_id, 'large', true );

				$use_ft_image_icon = true;

				$use_ft_image_image = true;

				// get the icon image from wordpress if it exists
				if ( $use_ft_image_icon ) {
					$thumb_image = $thumb_sized[0];

					// set the icon image for both chrome and firefox-1
					$fields['chrome_web_icon'] = $thumb_image;
					$fields['firefox_icon']    = $thumb_image;
				}

				if ( $use_ft_image_image ) {
					$fields['chrome_web_image'] = $large_sized[0];
				}
			}

			$ch = curl_init();

			$api_url = "https://api.onesignal.com/notifications?c=push";

			$onesignal_auth_key = $os_settings['app_rest_api_key'];

			curl_setopt( $ch, CURLOPT_URL, $api_url );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Authorization: ' . $onesignal_auth_key
			) );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_HEADER, false );
			curl_setopt( $ch, CURLOPT_POST, true );
			curl_setopt( $ch, CURLOPT_ENCODING, "");
			curl_setopt( $ch, CURLOPT_MAXREDIRS, 10);
			curl_setopt( $ch, CURLOPT_TIMEOUT, 30);
			curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $fields ) );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );

			$response = curl_exec( $ch );

			$curl_http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

			if ( $curl_http_code != 200 ) {

				if ( $curl_http_code != 0 ) {

					set_transient( 'onesignal_transient_error', '<div class="error notice onesignal-error-notice">
					<p><strong>OneSignal Push:</strong><em> There was a ' . $curl_http_code . ' error sending your notification:</em></p>
					<pre>' . print_r( $response, true ) . '</pre>
					</div>', 86400 );

				} else {

					// A 0 HTTP status code means the connection couldn't be established
					set_transient( 'onesignal_transient_error', '<div class="error notice onesignal-error-notice">
					<p><strong>OneSignal Push:</strong><em> There was an error establishing a network connection. Please make sure outgoing network connections from cURL are allowed.</em></p>
					</div>', 86400 );

				}

			} else {

				$parsed_response = json_decode( $response, true );

				if ( ! empty( $parsed_response ) ) {
					// API can send a 200 OK even if the notification failed to send
					$message_id   = $parsed_response['id'];
					if($message_id){
						set_transient( 'onesignal_transient_success', '<div class="updated notice notice-success is-dismissible">
							<p><strong>OneSignal Push:</strong><em> Successfully sent messages to ' . count($player_ids) . ' users.</em></p>
							</div>', 86400 );
					} else {
						set_transient( 'onesignal_transient_success', '<div class="updated notice notice-success is-dismissible">
							<p><strong>OneSignal Push:</strong><em> A notification was sent unsuccessfully.</em></p>
							</div>', 86400 );
					}
				}
			}

			curl_close( $ch );

			return $response;

		}

		function is_new_chapter_notify_enable() {

			global $wp_manga_setting;

			return $wp_manga_setting->get_manga_option( 'new_chap_notify', false );

		}

		function is_webpush_enable() {

			global $wp_manga_setting;

			return $wp_manga_setting->get_manga_option( 'webpush_noti', false );

		}

		function is_onesignal_active() {

			if ( ! defined('ONESIGNAL_VERSION_V3') ) {
				return false;
			} 

			//$os_settings = OneSignal::get_onesignal_settings(); V2
			$os_settings = get_option('OneSignalWPSetting', onesignal_get_default_settings());

			if ( empty( $os_settings['app_id'] ) || empty( $os_settings['app_rest_api_key'] ) ) {
				return false;
			}

			return true;

		}
        
        /**
         * Get list of unread chapters of a manga for an user that is saved in bookmark history
         **/
        function get_unread_chapters($user_id, $manga_id){
            $user_bookmarks = get_user_meta( $user_id, '_wp_manga_bookmark', true );
            
            $index = $this->get_manga_bookmark_index( $manga_id, $user_bookmarks );

			if ( $index === false || empty( $user_bookmarks[ $index ] ) ) {
				return array();
			}

			if ( ! empty( $user_bookmarks[ $index ]['unread_c'] ) && is_array( $user_bookmarks[ $index ]['unread_c'] ) ) {
				return $user_bookmarks[ $index ]['unread_c'];
			}

			return array();
        }
        
        /**
         * Call when an user reads an "unread chapter", then remove it from the unread list
         **/
		function remove_unread_chapter( $user_id, $history ) {

			if ( empty( $user_id ) || empty( $history ) ) {
				return;
			}
            
			$user_bookmarks = get_user_meta( $user_id, '_wp_manga_bookmark', true );

			$index = $this->get_manga_bookmark_index( $history['id'], $user_bookmarks );

			if ( $index === false || empty( $user_bookmarks[ $index ] ) ) {
				return;
			}

			if ( ! empty( $user_bookmarks[ $index ]['unread_c'] ) && is_array( $user_bookmarks[ $index ]['unread_c'] ) ) {
				foreach ( $user_bookmarks[ $index ]['unread_c'] as $c_index => $chap ) {
					if ( $chap == $history['c'] ) {
						unset( $user_bookmarks[ $index ]['unread_c'][ $c_index ] );
					}
				}
			}

			update_user_meta( $user_id, '_wp_manga_bookmark', $user_bookmarks );

		}

		function get_user_player_id() {

			if ( is_user_logged_in() && $this->is_onesignal_active() ) {
				?>
                <script type="text/javascript">
					window.OneSignalDeferred = window.OneSignalDeferred || [];
					OneSignalDeferred.push(async function(OneSignal) {
						OneSignal.login("<?php echo wp_manga_generate_onesignal_externalid();?>");
					});
                </script>
				<?php
			}

		}
	}

	$GLOBALS['wp_manga_user_actions'] = new WP_MANGA_USER_ACTION();
