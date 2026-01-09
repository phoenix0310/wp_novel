<?php
	/**
	 *  Plugin Name: Madara - Core
	 *  Description: The first and most popular Manga plugin for WordPress
	 *  Plugin URI: https://www.mangabooth.com/
	 *  Author: MangaBooth
	 *  Author URI: https://www.mangabooth.com/
	 *  Author Email: wpstylish@gmail.com
	 *  Version: 2.2.7.1
	 *  Text Domain: manga-core
	 * @since 1.0
	 */

	if ( ! defined( 'WP_MANGA_FILE' ) ) {
		define( 'WP_MANGA_FILE', __FILE__ );
	}
	// plugin dir path
	if ( ! defined( 'WP_MANGA_DIR' ) ) {
		define( 'WP_MANGA_DIR', plugin_dir_path( __FILE__ ) );
	}
	// plugin dir URI
	if ( ! defined( 'WP_MANGA_URI' ) ) {
		define( 'WP_MANGA_URI', plugin_dir_url( __FILE__ ) );
	}

	$wp_upload_dir = wp_upload_dir();

	//data dir
	if ( ! defined( 'WP_MANGA_DATA_DIR' ) ) {
		define( 'WP_MANGA_DATA_DIR', $wp_upload_dir['basedir'] . '/WP-manga/data/' );
	}

	//data url
	if ( ! defined( 'WP_MANGA_DATA_URL' ) ) {
		define( 'WP_MANGA_DATA_URL', $wp_upload_dir['baseurl'] . '/WP-manga/data/' );
	}

	//json dir
	if ( ! defined( 'WP_MANGA_JSON_DIR' ) ) {
		define( 'WP_MANGA_JSON_DIR', $wp_upload_dir['basedir'] . '/WP-manga/json/' );
	}

	//temp dir
	if ( ! defined( 'WP_MANGA_EXTRACT_DIR' ) ) {
		define( 'WP_MANGA_EXTRACT_DIR', $wp_upload_dir['basedir'] . '/WP-manga/temp/' );
	}

	//temp url
	if ( ! defined( 'WP_MANGA_EXTRACT_URL' ) ) {
		define( 'WP_MANGA_EXTRACT_URL', $wp_upload_dir['baseurl'] . '/WP-manga/temp/' );
	}

	if ( ! defined( 'WP_MANGA_TEXTDOMAIN' ) ) {
		define( 'WP_MANGA_TEXTDOMAIN', 'manga-core' );
	}

	if( ! defined( 'WP_MANGA_VER' ) ){
		define( 'WP_MANGA_VER', 2.226 );
	}
	
	#[AllowDynamicProperties]
	class WP_MANGA {
		public $manga_paged_var = 'manga-paged';

		private static $instance;

        /**
         * Store chapter data by slug to prevent duplicated queries
         **/
        public $_chapters = array();

		public static function get_instance() {
			if ( null == self::$instance ) {
				self::$instance = new WP_MANGA();
			}


			return self::$instance;
		}

		private function __construct() {
			global $pagenow;

			$this->dir = WP_MANGA_DIR;
			$this->uri = WP_MANGA_URI;
			
			require_once( WP_MANGA_DIR . '/inc/wp_manga_cache_system.php' );

			require_once( WP_MANGA_DIR . '/inc/database/database.php' );

			require_once( WP_MANGA_DIR . 'inc/database/database-volume.php' );
			require_once( WP_MANGA_DIR . '/inc/database/database-chapter-data.php' );
            require_once( WP_MANGA_DIR . '/inc/database/database-chapter.php' );			
            require_once( WP_MANGA_DIR . '/inc/ajax.php' );
            require_once( WP_MANGA_DIR . '/inc/storage.php' );
            require_once( WP_MANGA_DIR . '/inc/functions.php' );

			require_once( WP_MANGA_DIR . '/inc/post-type.php' );
			require_once( WP_MANGA_DIR . '/inc/manga-type/manga-chapter.php' );
			require_once( WP_MANGA_DIR . '/inc/manga-type/text-chapter.php' );
			require_once( WP_MANGA_DIR . '/inc/settings.php' );

			require_once( WP_MANGA_DIR . '/inc/template.php' );
			require_once( WP_MANGA_DIR . '/inc/sidebar.php' );

			require_once( WP_MANGA_DIR . '/inc/extras.php' );

			require_once( WP_MANGA_DIR . '/admin/first-install.php' );
			require_once( WP_MANGA_DIR . '/admin/class-wpmanga-admin.php' );
			require_once( WP_MANGA_DIR . '/inc/zip-validation.php' );

			require_once( WP_MANGA_DIR . '/inc/comments/wp-comments.php' );
			require_once( WP_MANGA_DIR . '/inc/comments/disqus-comments.php' );

			require_once( WP_MANGA_DIR . '/inc/cronjob.php' );

			// user action
			require_once( WP_MANGA_DIR . '/inc/user-actions.php' );

			require_once( WP_MANGA_DIR . '/inc/login/login.php' );

			require_once( WP_MANGA_DIR . '/inc/upload/imgur-upload.php' );
			require_once( WP_MANGA_DIR . '/inc/upload/google-upload.php' );
			require_once( WP_MANGA_DIR . '/inc/upload/amazon-upload.php' );
			require_once( WP_MANGA_DIR . '/inc/upload/flickr-upload.php' );

			require_once( WP_MANGA_DIR . '/inc/widgets/wp-manga-recentcomments.php' );
			require_once( WP_MANGA_DIR . '/inc/widgets/recent-manga.php' );
			require_once( WP_MANGA_DIR . '/inc/widgets/manga-slider.php' );
			require_once( WP_MANGA_DIR . '/inc/widgets/manga-popular-slider.php' );
			require_once( WP_MANGA_DIR . '/inc/widgets/manga-search.php' );
			require_once( WP_MANGA_DIR . '/inc/widgets/manga-genres.php' );
			require_once( WP_MANGA_DIR . '/inc/widgets/manga-release.php' );
			require_once( WP_MANGA_DIR . '/inc/widgets/manga-taxonomy.php' );
			require_once( WP_MANGA_DIR . '/inc/widgets/manga-user-section.php' );
			require_once( WP_MANGA_DIR . '/inc/widgets/manga-authors.php' );
			require_once( WP_MANGA_DIR . '/inc/widgets/madara-mangabookmark.php' );
			require_once( WP_MANGA_DIR . '/inc/widgets/madara-mangahistory.php' );
			require_once( WP_MANGA_DIR . '/inc/widgets/madara-posts.php' );

			add_action( 'admin_init', array( $this, 'manga_activation_sampledata_redirect' ) );
			add_action( 'admin_init', array( $this, 'admin_init' ) );

			global $wp_manga_cronjob;

			register_activation_hook( WP_MANGA_FILE, array( $this, 'wp_manga_active' ) );
			register_deactivation_hook( WP_MANGA_FILE, array( $wp_manga_cronjob, '_plugin_deactive' ) );

			if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) {
				add_action( 'admin_footer', array( $this, 'chapter_edit_modal' ) );
			}

			add_action( 'admin_menu', array( $this, 'wp_manga_menu_page' ) );
			add_action( 'admin_init', array( $this, 'wp_manga_settings_save' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_script' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_script' ), 1000 );
			add_filter( 'body_class', array( $this, 'wp_manga_body_classes' ) );

			add_action('wp_footer', array($this, 'wp_manga_footer'));

			add_filter('get_canonical_url', array($this, 'canonical_url'), 10, 2);
			add_filter('wpseo_canonical', array($this,'canonical_url'),10,2); // suport WP YOAST SEO
			add_image_size( 'manga-thumb-1', 110, 150, true );
			add_image_size( 'manga-single', 193, 278, true );
			add_image_size( 'manga_wg_post_1', 75, 106, true );
			add_image_size( 'manga_wg_post_2', 300, 165, true );
			add_image_size( 'manga-slider', 642, 320, true );

			global $wp_manga_setting;

			//update manga_paged_var setting
			$this->manga_paged_var = $wp_manga_setting->get_manga_option('manga_paged_var', 'manga-paged');

			require_once( WP_MANGA_DIR . '/inc/permalink.php' );
			require_once( WP_MANGA_DIR . '/inc/hooks.php' );

			if(is_admin()){
				WP_Manga_Admin::init();
				require_once( WP_MANGA_DIR . '/admin/hooks.php' );
			}

			add_action('wp', array($this, 'wpmanga_init'), 1);
			add_action( "wp_ajax_guest_histories", array($this, 'get_guest_reading_histories' ));
			add_action( "wp_ajax_nopriv_guest_histories", array($this, 'get_guest_reading_histories' ) );

            add_action( 'init', array( $this, 'init' ) );

            global $wp_manga_user_actions;
            if($wp_manga_user_actions->is_new_chapter_notify_enable()){
				add_action('onesignal_manga_notification', array($wp_manga_user_actions, 'send_onesignal_notifications'));
                add_action('manga_new_chapters_notification', array($wp_manga_user_actions, 'send_users_notifications'));
            }

			$current_version = get_option( 'wp_manga_latest_version', 1.4 );
            
            if( $current_version < WP_MANGA_VER ){
				add_action('admin_notices', array($this, 'wp_manga_upgrade_notice'));
			}
		}

		public function admin_init(){
            $is_chapter_sort_built = get_option('wp_manga_chapter_sort_built', 0);
            if(!$is_chapter_sort_built){
                $ignore = get_option('wp_manga_chapter_sort_built_ignore', 0);
                if(!$ignore){
                    add_action( 'admin_notices', function(){
                        if(get_current_screen()->base !== 'wp-manga_page_wp-manga-legacy'){
                        ?>
                        <div class="notice notice-error">
                            <p><?php printf( __( '<p><strong>IMPORTANT UPDATE : </strong> If you have just updated to a WP Manga Core 2.2.6+ version, make sure you have rebuilt the Chapter Sort index if you sort chapters by name. <a href="%s" class="button button-primary">Click here to start</a>. Or <a href="%s" class="button button-primary">Click here to ignore</a>', WP_MANGA_TEXTDOMAIN ), admin_url( 'edit.php?post_type=wp-manga&page=wp-manga-legacy'), admin_url( 'admin.php?page=wp-manga-legacy&wp_manga_chapter_sort_built_ignore=1') ); ?></p>
                        </div>
                        <?php
                        }
                    } );
                }
            }
        }

		function wp_manga_upgrade_notice(){
			?>
			<div class="notice notice-error is-dismissible">
				<p><?php _e( 'WP Manga: Database upgrade required. Please deactivate Madara-Core plugin and activate it again to trigger the upgrade', 'wp-manga' ); ?></p>
			</div>
			<?php
		}

        function init(){ 
            load_plugin_textdomain( WP_MANGA_TEXTDOMAIN, false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
            // for Authorized Token
            $this->get_google_token();
			
			add_filter( 'posts_clauses', array($this,'wp_query_search_clauses'), 1, 2 );

			add_filter('wp_manga_encrypt_manga_uniq',array($this, 'wp_manga_encrypt_manga_uniq'), 10, 3);
			add_filter('wp_manga_upload_encrypt_chapter_dir',array($this, 'wp_manga_upload_encrypt_chapter_dir'), 10, 2);
        }

		function wp_manga_encrypt_manga_uniq($should_encrypt, $post_id, $post_name){
			$setting = $GLOBALS['wp_manga_setting']->get_manga_option('encrypt_manga_folder_name', '1');

			if($setting != '1'){
				return false;
			}

			return $should_encrypt;
		}

		function wp_manga_upload_encrypt_chapter_dir($should_encrypt, $post_id){
			$setting = $GLOBALS['wp_manga_setting']->get_manga_option('encrypt_chapter_folder_name', '1');

			if($setting != '1'){
				return false;
			}

			return $should_encrypt;
		}

		function wpmanga_init(){
			// save reading history for guest
			global $wp_manga_functions;
			if($wp_manga_functions->is_manga_reading_page() && !($user_id = get_current_user_id())){
				$reading_chapter = madara_permalink_reading_chapter();
				$manga_id = get_the_ID();

				$this_history = array(
					'id' => $manga_id,
					'c'  => $reading_chapter['chapter_id'],
					'p'  => 1,
					'i'  => '',
					't'  => current_time( 'timestamp' ),
				);

				if(isset($_COOKIE['wpmanga-reading-history'])){
					$manga_history = json_decode(base64_decode($_COOKIE['wpmanga-reading-history']));
				} else {
					$manga_history = array($this_history);
				}

				// max items are 12
				// if there are more than the maximum of numbers manga in history
				if ( is_array($manga_history)){
					if(count( $manga_history ) > 12 ) {
						// order then cut the history array
						uasort( $manga_history, function ( $a, $b ) {
							if(is_array($a)){
								if ( $a['t'] == $b['t'] ) {
									return 0;
								}

								return $a['t'] > $b['t'] ? - 1 : 1;
							} else {
								if ( $a->t == $b->t ) {
									return 0;
								}

								return $a->t > $b->t ? - 1 : 1;
							}
						} );

						$manga_history = array_slice( $manga_history, 0, 12 );
					}
				} else {
					$manga_history = array($this_history);
				}

				//check if current chapter is existed
				$index = array_search( $manga_id, array_column( $manga_history, 'id' ) );
				if ( $index !== false ) {
					$manga_history[ $index ] = $this_history;
				} else {
					$manga_history[] = $this_history;
				}

				setcookie('wpmanga-reading-history', base64_encode(json_encode($manga_history)), time() + 30 * 24 * 60 * 60, "/"); // 30 days
			}
		}

		function get_guest_reading_histories(){
			$user_id = get_current_user_id();

            if( !$user_id ) {
				global $wp_manga_setting;
				$guest_reading_history = $wp_manga_setting->get_manga_option( 'guest_reading_history', true );
				if($guest_reading_history && isset($_COOKIE['wpmanga-reading-history'])){

					$manga_history = json_decode(base64_decode($_COOKIE['wpmanga-reading-history']), true);

					if(count($manga_history) == 0){
						return;
					}
				} else {
					return;
				}
            } else {
				$manga_history = get_user_meta( $user_id, '_wp_manga_history', true );
			}


            $reading_style = $GLOBALS['wp_manga_functions']->get_reading_style();
            $count = isset($_GET['count']) ? $_GET['count'] : 3;

            if( !empty( $manga_history ) ) {

				//sort by latest manga
				$manga_history = array_combine( array_column( $manga_history, 't' ), $manga_history );
				krsort( $manga_history );

				foreach( $manga_history as $manga ) {

					if( $count == 0 ) {
						break;
					}

					$post_id = intval($manga['id']);

					$post = get_post( $post_id );

					if( $post == null || $post->post_status !== 'publish' ) {
						continue;
					}

					$count--;					

					$url = '';

					$reading_style = !empty( $reading_style ) ? $reading_style : 'paged';
					$page          = !empty( $manga['p'] ) ? intval($manga['p']) : '1';

					//get chapter
					if ( class_exists( 'WP_MANGA' ) && !empty( intval($manga['c']) ) && !is_array( $manga['c'] ) ) {

						$wp_manga_chapter = madara_get_global_wp_manga_chapter();
						$chapter_slug = $wp_manga_chapter->get_chapter_slug_by_id( $post_id, intval($manga['c']) );

						global $wp_manga_functions;
						$url = $wp_manga_functions->build_chapter_url( $post_id, $chapter_slug, $reading_style, null, $page );

					} else {
						continue;
					}

					if( !empty( $manga['i'] ) && $reading_style == 'list' ){
						$url .= '#image-' . intval($manga['i']);
					}

					?>
					<div class="my-history-item-wrap">
						<div class="my-history-title">
							<a title="<?php echo esc_attr( get_the_title( $post_id ) ); ?>" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a>
						</div>
						<?php if($page != 1) {?>
						<a href="<?php echo esc_url ( $url ); ?>">
							<div class="my-history-page">
								<span><?php echo sprintf(esc_html__( 'Page %s', WP_MANGA_TEXTDOMAIN ), $page ); ?></span>
							</div>
						</a>
						<?php }?>
					</div>
					<?php

					//reset for the next loop
					$chapter_slug = '';

				}
            }
		    wp_die();
		}

		function wp_manga_footer(){
			$settings = $GLOBALS['wp_manga_setting']->settings;
			if(isset($settings['default_comment']) && $settings['default_comment'] == 'disqus'){
				if(get_option('disqus_forum_url') != ''){
				?>
				<script id="dsq-count-scr" src="https://<?php echo get_option('disqus_forum_url');?>.disqus.com/count.js" async></script>
				<?php
				}
			}
		}

		// add canonical URL to chapter reading page, if 'manga-paged' is used
		function canonical_url( $canonical_url, $post ){

			global $wp_manga_functions;

			if( $wp_manga_functions->is_manga_reading_page() ){
				global $wp_manga_functions;

				$manga_id = get_the_ID();
				$reading_chapter = madara_permalink_reading_chapter();
				if($reading_chapter){
					$chapter_slug = $reading_chapter['chapter_slug'];

					$chapter_link = $wp_manga_functions->build_chapter_url($manga_id, $chapter_slug);

					return $chapter_link;
				}
			}

			return $canonical_url;
		}

	public function wp_manga_menu_page() {
		add_submenu_page( 'wp-manga-settings', esc_html__( 'WP Manga Storage', WP_MANGA_TEXTDOMAIN ), esc_html__( 'WP Manga Storage', WP_MANGA_TEXTDOMAIN ), 'manage_options', 'wp-manga-storage', array(
			$this,
			'wp_manga_setting_storage'
		) );

            add_submenu_page( 'wp-manga-settings', esc_html__( 'WP Manga - Legacy Settings', WP_MANGA_TEXTDOMAIN ), esc_html__( 'WP Manga Legacy Settings', WP_MANGA_TEXTDOMAIN ), 'manage_options', 'wp-manga-legacy', array(
				$this,
				'wp_manga_setting_legacy'
			) );
	}

        /** callback function for WP Manga Storage settings apge
        **/
		function wp_manga_setting_storage() {
			if ( file_exists( WP_MANGA_DIR . 'admin/settings/settings-storage.php' ) ) {
				include( WP_MANGA_DIR . 'admin/settings/settings-storage.php' );
			}
		}

        /** callback function for WP Manga Search settings apge
        **/
        function wp_manga_setting_legacy() {

			if ( file_exists( WP_MANGA_DIR . 'admin/settings/settings-legacy.php' ) ) {
				include( WP_MANGA_DIR . 'admin/settings/settings-legacy.php' );
			}

		}

		function admin_enqueue_script() {

			global $pagenow;
			// style
			wp_enqueue_style( 'wp-manga-css', WP_MANGA_URI . 'assets/css/admin.css', array(), '2.2.4.1' );

			wp_enqueue_script( 'admin_js', WP_MANGA_URI . 'assets/js/admin.js', array(), '2.2.4.1' );

			wp_localize_script( 'admin_js', 'wpManga', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'home_url' => get_home_url(),
                'base_url' => get_permalink(),
				// Localize translatable strings
				'strings' => array(
					'delChapter'   => esc_html__('Are you sure that you want to delete this Chapter?', WP_MANGA_TEXTDOMAIN ),
					'delVolume'    => esc_html__('Are you sure you want to delete this Volume and all chapters inside?', WP_MANGA_TEXTDOMAIN ),
					'delNoVolChap' => esc_html__('By this, you will delete all chapters without Volume.', WP_MANGA_TEXTDOMAIN ),
                    'confirmation' => esc_html__('Are you sure to do that?', WP_MANGA_TEXTDOMAIN ),
                    'max_upload_exceed' => esc_html__('This file exceeds the upload_max_filesize directive in php.ini', WP_MANGA_TEXTDOMAIN),
                    'max_post_file' => esc_html__('This file exceeds the post_max_size directive in php.ini', WP_MANGA_TEXTDOMAIN)
				),
                'nonce' => wp_create_nonce('wp-manga-admin')
			) );

            wp_enqueue_script( 'functions', WP_MANGA_URI . 'assets/js/functions.js', array( 'jquery', 'admin_js' ), '2.2.4.1', true );

			wp_enqueue_style( 'wp-manga-font-awesome', WP_MANGA_URI . 'assets/font-awesome/css/fontawesome.min.css' );
			wp_enqueue_style( 'wp-manga-ionicons', WP_MANGA_URI . 'assets/css/ionicons/css/ionicons.min.css' );

			if ( $this->is_manga_edit_page() || $pagenow == 'edit.php' ) {
				wp_enqueue_script( 'wp_manga_popup_js', WP_MANGA_URI . 'assets/js/manga-popup.js', array( 'jquery' ), '', true );
			}

			if ( $this->is_manga_edit_page() ) {
				wp_enqueue_script( 'wp-manga-admin-single-manga', WP_MANGA_URI . 'assets/js/admin-single-manga.js', array( 'jquery' ), '2.2.4', true );

				// formdata script
				wp_enqueue_script( 'wp-media-upload-form-data', WP_MANGA_URI . 'assets/js/form-data.js' );

				wp_enqueue_script( 'wp-manga-upload', WP_MANGA_URI . 'assets/js/upload.js', array( 'jquery' ), '2.2.4', true );
				wp_enqueue_script( 'wp-manga-search-chapter', WP_MANGA_URI . 'assets/js/search-chapter.js', array( 'jquery' ), '', true );

				wp_enqueue_script( 'wp-manga-text-chapter', WP_MANGA_URI . 'assets/js/create-text-chapter.js', array( 'jquery' ), '1.7.2.1', true );
			}

			if ( $pagenow == 'edit.php' ) {
				//download script
				wp_enqueue_script( 'wp_manga_download_js', WP_MANGA_URI . 'assets/js/manga-download.js', array( 'jquery' ), '', true );
			}
		}

		function bootstrap_check_enqueued() {

			global $wp_scripts;
			$all_scripts = array_column( $wp_scripts->registered, 'src' );

			foreach ( $all_scripts as $script ) {
				if ( strpos( $script, 'bootstrap' ) !== false ) {
					return true;
				}
			}

			return false;
		}

		function enqueue_script() {

			$settings = $GLOBALS['wp_manga_setting']->settings;

			global $wp_scripts, $wp_manga;

			$bootstrap   = isset( $settings['loading_bootstrap'] ) ? $settings['loading_bootstrap'] : 'true';
			$slick       = isset( $settings['loading_slick'] ) ? $settings['loading_slick'] : 'true';
			$fontawesome = isset( $settings['loading_fontawesome'] ) ? $settings['loading_fontawesome'] : 'true';
			$ionicon     = isset( $settings['loading_ionicon'] ) ? $settings['loading_ionicon'] : 'true';

			if ( $bootstrap == 'true' && $this->bootstrap_check_enqueued() == false ) {
				wp_enqueue_style( 'wp-manga-bootstrap-css', WP_MANGA_URI . 'assets/css/bootstrap.min.css', array(), '4.6.0' );
				wp_enqueue_script( 'wp-manga-bootstrap-js', WP_MANGA_URI . 'assets/js/bootstrap.min.js', array( 'jquery' ), '4.6.0', true );
			}

			//slick
			if ( $slick == 'true' ) {
				wp_enqueue_style( 'wp-manga-slick-css', WP_MANGA_URI . 'assets/slick/slick.css' );
				wp_enqueue_style( 'wp-manga-slick-theme-css', WP_MANGA_URI . 'assets/slick/slick-theme.css' );
				wp_enqueue_script( 'wp-manga-slick-js', WP_MANGA_URI . 'assets/slick/slick.min.js', array( 'jquery' ), '', true );
			}

			if ( $fontawesome == 'true' ) {
				wp_enqueue_style( 'wp-manga-font-awesome', WP_MANGA_URI . 'assets/font-awesome/css/all.min.css', array(), '5.15.3' );
			}

			if ( $ionicon == 'true' ) {
				wp_enqueue_style( 'wp-manga-ionicons', WP_MANGA_URI . 'assets/css/ionicons/css/ionicons.min.css', array(), '4.5.10' );
			}

			wp_enqueue_script( 'wp-manga', WP_MANGA_URI . 'assets/js/script.js', array(
				'jquery',
				'jquery-ui-autocomplete'
			), '2.1.0.1', true );

            global $wp_manga_setting;

            $enabled_manga_view = $wp_manga_setting->get_manga_option('manga_view', 1);

			$data = array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'home_url' => get_home_url(),
                'base_url' => get_permalink(),
				'manga_paged_var' => $wp_manga->manga_paged_var,
                'enable_manga_view' => $enabled_manga_view
			);

			if(is_singular('wp-manga')){
				$data['manga_id'] = get_the_ID();

				$reading_chapter = madara_permalink_reading_chapter();
				if($reading_chapter){
					$chapter_slug = $reading_chapter['chapter_slug'];
					$data['chapter_slug'] = $chapter_slug;

					global $wp_manga_setting;
					$back_to_info_page = $wp_manga_setting->get_manga_option( 'navigation_manga_info', true );
					wp_localize_script( 'wp-manga', 'mangaNav', array(
						'mangaUrl'     => get_the_permalink(),
						'backInfoPage' => $back_to_info_page ? 'on' : 'off',
						'text'         => array(
							'backInfoPage' => esc_html__( 'Manga Info', WP_MANGA_TEXTDOMAIN ),
							'prev'         => esc_html__( 'Prev', WP_MANGA_TEXTDOMAIN ),
							'next'         => esc_html__( 'Next', WP_MANGA_TEXTDOMAIN ),
						)
					) );

                    $chapter_type = get_post_meta( $data['manga_id'], '_wp_manga_chapter_type', true );
                    if($chapter_type == 'video'){
                        wp_enqueue_script( 'lity', WP_MANGA_URI . 'assets/js/lity.min.js', array('jquery'), '2.4.1', true );
                        wp_enqueue_style( 'lity', WP_MANGA_URI . 'assets/css/lity.min.css');
                    }
				}
			}

			wp_localize_script( 'wp-manga', 'manga', $data );

			wp_enqueue_style( 'wp-manga-plugin-css', WP_MANGA_URI . 'assets/css/style.css' );

			wp_enqueue_style( 'wp-manga-font', 'https://fonts.googleapis.com/css?family=Poppins' );

			if ( is_search() ) {
				wp_enqueue_script( 'wp-manga-search', WP_MANGA_URI . 'assets/js/manga-search.js', array( 'jquery' ), '', true );
			}

			$using_ajax = function_exists( 'madara_page_reading_ajax' ) && madara_page_reading_ajax();
			if($using_ajax){
				wp_localize_script( 'wp-manga', 'mangaReadingAjax', array( 'isEnable' => $using_ajax ) );
			}
		}

		function chapter_edit_modal() {

			if ( file_exists( WP_MANGA_DIR . 'inc/admin-template/manga-single/chapter-edit-modal.php' ) ) {
				include( WP_MANGA_DIR . 'inc/admin-template/manga-single/chapter-edit-modal.php' );
			}

		}

		function manga_nav( $position, $manga_id = 0, $cur_chap = '') {

			if( isset( $GLOBALS['madara_manga_navigation_html'] ) ){
				echo $this->wp_manga_nav_breadcrumbs( $GLOBALS['madara_manga_navigation_html'], $position );
				return;
			}
			global $wp_manga_chapter, $wp_manga_chapter_type, $wp_manga_text_type, $wp_manga_volume;

			$is_content_manga = $this->is_content_manga( $manga_id ? $manga_id : get_the_ID() );

			if(!$cur_chap){
				$reading_chapter = madara_permalink_reading_chapter();
				if($reading_chapter){
					$cur_chap = $reading_chapter['chapter_slug'];
				}
			} else {
				global $wp_manga_functions;
				$reading_chapter = $wp_manga_functions->get_chapter_by_slug($manga_id, $cur_chap);
			}


			if ( ! $reading_chapter ) {
				return;
			}

			global $wp_manga_database;
			$sort_setting = $wp_manga_database->get_sort_setting();

			$sort_by    = $sort_setting['sortBy'];
			$sort_order = $sort_setting['sort'];

			$manga_id = $manga_id ? $manga_id : get_the_ID();

			// all chaps in same volume
			$all_chaps = $wp_manga_volume->get_volume_chapters( $manga_id, $reading_chapter['volume_id'], $sort_by, $sort_order );

			$args = array(
				'manga_id' => $manga_id,
				'cur_chap'  => $cur_chap,
				'chapter'   => $reading_chapter,
				'all_chaps' => $all_chaps,
				'position'  => $position,
				'asc' => $sort_order == 'asc' ? true : false
			);

			$ouput = '';

			ob_start();

			if ( ! $is_content_manga ) {
				$wp_manga_chapter_type->manga_nav( $args );
			} else {
				$wp_manga_text_type->manga_nav( $args );
			}

			$output = ob_get_contents();

			ob_end_clean();

			$GLOBALS['madara_manga_navigation_html'] = $output;

			echo $this->wp_manga_nav_breadcrumbs( $output, $position );
		}

		function wp_manga_nav_breadcrumbs( $output, $position ){

			if( $position !== 'header' ){
				return $output;
			}

			$end_html = explode('<div class="wp-manga-nav">', $output);

			if( !isset( $end_html[1] ) ){
				return $output;
			}

			global $wp_manga_template;
			ob_start();
			?>
				<div class="wp-manga-nav">
					<div class="entry-header_wrap">
						<?php $wp_manga_template->load_template( 'manga', 'breadcrumb', true ); ?>
					</div>
					<?php echo $end_html[1]; ?>
			<?php
			$output = ob_get_contents();
			ob_end_clean();

			return $output;

		}

		function wp_manga_breadcrumbs() {
			$output = '<div class="c-breadcrumb">';
			$output .= '<ol class="breadcrumb">';

			$output .= '<li><a href="' . get_post_type_archive_link( 'wp-manga' ) . '"> ' . __( 'Manga', WP_MANGA_TEXTDOMAIN ) . ' </a></li>';

			if ( ( is_tax( 'wp-manga-author' ) || is_tax( 'wp-manga-release' ) || is_tax( 'wp-manga-genre' ) || is_tax( 'wp-manga-artist' ) ) && ! is_search() ) {

				$taxonomy     = get_query_var( 'taxonomy' );
				$term         = get_term_by( 'slug', get_query_var( $taxonomy ), $taxonomy );
				$current_term = $term;
				$term_output  = '';

				while ( $term->parent !== 0 ) {
					$parent_term = get_term_by( 'id', $term->parent, $taxonomy );
					$term_output = '<li><a href="' . get_term_link( $parent_term->term_id, $taxonomy ) . '">' . $parent_term->name . '</a></li>';
					$term        = $parent_term;
				}

				$output .= $term_output . '<li><span>' . $current_term->name . '</span></li>';

			}


			if ( is_singular( 'wp-manga' ) ) {
				$output .= '<li><a href="' . get_the_permalink( get_the_ID() ) . '">' . get_the_title() . '</a></li>';
			}

			if ( is_search() ) {
				$search = isset( $_GET['s'] ) ? $_GET['s'] : false;
				$output .= '<li><span>' . __( 'Showing search results for: ', WP_MANGA_TEXTDOMAIN ) . $search . '</span></li>';
			}

			$output .= '</ol>';
			$output .= '</div>';

			return $output;
		}

		function wp_manga_chapter_breadcrumbs( $chapter_name, $chapter_name_extend = '' ) {
			$output = '<div class="c-breadcrumb">';
			$output .= '<ol class="breadcrumb">';
			if ( is_singular( 'wp-manga' ) ) {
				$output .= '<li><a href="' . get_post_type_archive_link( 'wp-manga' ) . '"> Manga </a></li>';
				$output .= '<li><a href="' . get_the_permalink( get_the_ID() ) . '">' . get_the_title() . '</a></li>';
				$output .= '<li class="active">' . $chapter_name . ' : ' . $chapter_name_extend . '</li>';
			}
			$output .= '</ol>';
			$output .= '</div>';

			return $output;
		}

		function get_uniqid( $post_id ) {
			$uniqid = get_post_meta( $post_id, 'manga_unique_id', true );
			return $uniqid;
		}

		/**
		 * Save plugin settings
		 **/
		function wp_manga_settings_save() {

			if ( isset( $_POST['wp_manga'] ) ) {

				$options = get_option( 'wp_manga', $_POST['wp_manga'] );

				$manga_options = array();

				foreach ( $_POST['wp_manga'] as $key => $value ) {
					if(is_array($value)){
						if(count($value) == 1){
							$manga_options[ $key ] = $value[0];
						} else {
							$manga_options[ $key ] = serialize($value);
						}

					} else {
						$manga_options[ $key ] = trim( $value );
					}
				}

				//if google client id and client secret is change, then remove refreshtoken
				if ( $manga_options['google_client_id'] !== $options['google_client_id'] || $manga_options['google_client_secret'] !== $options['google_client_secret'] || ! isset( $_POST['google_refreshtoken'] ) ) {
					update_option( 'wp_manga_google_refreshToken', null );
				}

				//if imgur client id and client secret is change, then remove refreshtoken
				if ( $manga_options['imgur_client_secret'] !== $options['imgur_client_secret'] || $manga_options['imgur_client_id'] !== $options['imgur_client_id'] ) {
					update_option( 'wp_manga_imgur_refreshToken', null );
				}

				$manga_options = apply_filters('wp_manga_save_options', $manga_options);

				update_option( 'wp_manga', $manga_options );

			}

            if(isset($_POST['wp_manga_search']) && $_POST['wp_manga_search'] == 'rebuild'){
                if(isset($_POST['btnignore'])){
                    update_option( 'wp_manga_search_text_built_ignore', 1 );
                    wp_redirect(admin_url('index.php'));
                }
            }
		}

		function post_url( $url, $params ) {
			$ch        = curl_init();
			$headers   = array();
			$headers[] = 'Content-Type: application/x-www-form-urlencoded';
			curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_HTTPGET, 0 );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $params, "", '&' ) );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			$ret = curl_exec( $ch );

			curl_close( $ch );

			return $ret;
		}

		function get_google_token() {

			$state   = isset( $_GET['state'] ) ? $_GET['state'] : null;
			$options = get_option( 'wp_manga', array() );
			$code    = isset( $_GET['code'] ) ? $_GET['code'] : null;

			if ( 'imgur' == $state ) {

				global $wp_manga_imgur_upload;
				$wp_manga_imgur_upload->_get_token();

			} else if ( 'picasa' == $state && $code ) {
				$google_client_id     = isset( $options['google_client_id'] ) ? $options['google_client_id'] : '';
				$google_client_secret = isset( $options['google_client_secret'] ) ? $options['google_client_secret'] : '';
				$google_redirect      = isset( $options['google_redirect'] ) ? $options['google_redirect'] : '';
				$url                  = 'https://www.googleapis.com/oauth2/v4/token';
				$params               = array(
					'code'          => $code,
					'client_id'     => $google_client_id,
					'client_secret' => $google_client_secret,
					'redirect_uri'  => $google_redirect,
					'grant_type'    => 'authorization_code',
					'access_type'   => 'offline',
				);
				$res                  = $this->post_url( $url, $params );
				$tokens               = json_decode( $res );

				if ( isset( $tokens->refresh_token ) ) {
					update_option( 'wp_manga_google_refreshToken', $tokens->refresh_token );
					update_option('wp_manga_google_accessToken', $tokens->access_token);
					update_option('wp_manga_google_accessTokenExpired', time() + $tokens->expires_in);
				} else {
					set_transient( 'google_authorized', false );
					set_transient( 'google_authorization_error', esc_html__( 'Cannot get Google Refresh Token, please try again', 'madara' ), 1 * 60 * 60 );
				}
				exit( wp_safe_redirect( admin_url( 'edit.php?post_type=wp-manga&page=wp-manga-storage' ) ) );
			}
		}

		function get_available_host() {

			$available_host = array();
			$options        = get_option( 'wp_manga', array() );

			$available_host['local']['value'] = 'local';
			$available_host['local']['text']  = 'Local';

			// imgur
			$imgur_client_id     = isset( $options['imgur_client_id'] ) ? $options['imgur_client_id'] : null;
			$imgur_client_secret = isset( $options['imgur_client_secret'] ) ? $options['imgur_client_secret'] : null;
			$imgur_refresh_token = get_option( 'wp_manga_imgur_refreshToken', null );

			if ( $imgur_client_id && $imgur_client_secret && $imgur_refresh_token ) {
				$available_host['imgur']['value'] = 'imgur';
				$available_host['imgur']['text']  = __( 'Imgur', WP_MANGA_TEXTDOMAIN );
			}

			// flickr
            $flickr_api_key     = isset( $options['flickr_api_key'] ) ? $options['flickr_api_key'] : null;
            $flickr_api_secret = isset( $options['flickr_api_secret'] ) ? $options['flickr_api_secret'] : null;
            $flickr_oauth_token = get_option( 'wp_manga_flickr_oauth_token', null );
            $flickr_oauth_token_secret = get_option( 'wp_manga_flickr_oauth_token_secret', null );

            if ( $flickr_api_key && $flickr_api_secret && $flickr_oauth_token && $flickr_oauth_token_secret ) {
                $available_host['flickr']['value'] = 'flickr';
                $available_host['flickr']['text']  = __( 'Flickr', WP_MANGA_TEXTDOMAIN );
            }

			// google
			global $wp_manga_google_upload;
			$accessToken = $wp_manga_google_upload->get_access_token();
			if ( $accessToken ) {
				$available_host['picasa']['value'] = 'picasa';
				$available_host['picasa']['text']  = __( 'Blogspot', WP_MANGA_TEXTDOMAIN );
			}

			// amazon
			$amazon_s3_access_key    = isset( $options['amazon_s3_access_key'] ) ? $options['amazon_s3_access_key'] : null;
			$amazon_s3_access_secret = isset( $options['amazon_s3_access_secret'] ) ? $options['amazon_s3_access_secret'] : null;

			if ( $amazon_s3_access_key && $amazon_s3_access_secret ) {
				$available_host['amazon']['value'] = 'amazon';
				$available_host['amazon']['text']  = __( 'Amazon S3', WP_MANGA_TEXTDOMAIN );
			}

			return apply_filters('wp_manga_available_storages', $available_host);
		}

		function wp_manga_active() {
			global $wp_manga_cronjob;
            $wp_manga_cronjob->_plugin_activate();

			$first_active = get_option( 'wp_manga_first_active', false );
			if ( ! $first_active ) {
				update_option( 'wp_manga_first_active', true );
				update_option( 'wp_manga_notice', true ); // true == no notice
				$this->create_default_page();
				set_transient( 'wp_manga_welcome_redirect', true );
			}

            add_option( 'manga_activation_sampledata_redirect', true );

			do_action('wp_manga_active');

		}

		function create_default_page() {

			$user_page = wp_insert_post( array(
				'post_title'   => 'User Settings',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '[manga-user-page]'
			) );

			$archive_page = wp_insert_post( array(
				'post_title'  => 'Manga',
				'post_status' => 'publish',
				'post_type'   => 'page',
			) );

			$posts_data = array(
				'user_page'          => $user_page,
				'manga_archive_page' => $archive_page
			);

			$options = get_option( 'wp_manga_settings', array() );

			$user_page_settings = array_merge( $options, $posts_data );

			update_option( 'wp_manga_settings', $user_page_settings );
		}

		function is_manga_edit_page() {

			global $pagenow, $post;

			if ( ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) && $post->post_type == 'wp-manga' ) {
				return true;
			}

			return false;
		}

		function wp_manga_get_tags() {

			$manga_tags    = get_the_terms( get_the_ID(), 'wp-manga-tag' );
			$manga_tags    = isset( $manga_tags ) && ! empty( $manga_tags ) ? $manga_tags : array();
			$tag_count     = count( $manga_tags );
			$tag_flag      = 0;
			$separate_char = ', ';

			if ( $manga_tags == false || is_wp_error( $manga_tags ) ) {
				return;
			}

			?>
            <div class="wp-manga-tags-wrapper">
                <div class="wp-manga-tags">
                    <h5><?php esc_html_e( 'Tags: ', WP_MANGA_TEXTDOMAIN ); ?></h5>
                    <div class="wp-manga-tags-list">
						<?php foreach ( $manga_tags as $tag ) {
							$tag_flag ++;
							if ( $tag_flag == $tag_count ) {
								$separate_char = '';
							}
							?>
                            <a href="<?php echo esc_url( get_term_link( $tag->term_id ) ); ?>" class=""><?php echo esc_html( $tag->name ); ?></a><?php echo esc_html( $separate_char ); ?>
						<?php } ?>
                    </div>
                </div>
            </div>
			<?php
		}

		function mangabooth_manga_query( $manga_args ) {

			$manga_args['post_type']   = 'wp-manga';

			$manga_args['post_status'] = !empty( $manga_args['post_status'] ) ? $manga_args['post_status'] : 'publish';

			$manga_args['orderby'] = !empty( $manga_args['orderby'] ) ? $manga_args['orderby'] : '';

			$meta_query = isset($manga_args['meta_query']) ? $manga_args['meta_query'] : array();

			switch ( $manga_args['orderby'] ) {
				case 'alphabet' :
					$manga_args['orderby'] = 'post_title';
					$manga_args['order']   = 'ASC';
					break;
				case 'rating' :
					$meta_query['query_avarage_reviews'] = array(
														'key' => '_manga_avarage_reviews'
													);
					$meta_query['query_total_reviews'] = array(
														'key' => '_manga_total_votes'
													);
					$manga_args['orderby']  = array('query_avarage_reviews' => 'DESC', 'query_total_reviews' => 'DESC');
					break;
				case 'latest' :
					$manga_args['orderby']  = 'meta_value_num';
					$manga_args['meta_key'] = '_latest_update';
					$manga_args['order']  = 'desc';
					break;
				case 'trending' :
					$manga_args['orderby']  = 'meta_value_num';
                    $timerange_key = '_wp_manga_week_views_value';
                    if(isset($manga_args['timerange'])){
                        $time_range = $manga_args['timerange'];
                        if ( $time_range == 'all' ) {
                            $timerange_key = '_wp_manga_views';
                        } elseif ( $time_range == 'day' ) {
                            $timerange_key = '_wp_manga_day_views_value';
                        } elseif ( $time_range == 'month' ) {
                            $timerange_key = '_wp_manga_month_views_value';
                        } else {
                            $timerange_key = '_wp_manga_year_views_value';
                        }
                    }

					$manga_args['meta_key'] = $timerange_key;
					$manga_args['order']  = 'desc';
					break;
				case 'views' :
					$manga_args['orderby']  = 'meta_value_num';
					$manga_args['meta_key'] = '_wp_manga_views';
					break;
				case 'new-manga' :
					$manga_args['orderby'] = 'date';
					break;
				case 'random' :
					$manga_args['orderby'] = 'rand';
			}

			$manga_args = apply_filters( 'madara_manga_query_args', $manga_args );

			if(isset($manga_args['key']) && isset($manga_args['meta_query_value'])){
				$values = $manga_args['meta_query_value'];
				$values = explode(',', $values);
				foreach($values as $value){
					$meta_query = array_merge($meta_query,
							array(
								'key' => $manga_args['key'],
								'value' => $value
							));
				}

				unset($manga_args['key']);
				unset($manga_args['meta_query_value']);
			}

			$meta_query = apply_filters( 'madara_meta_query_args', $meta_query );
            $manga_args['meta_query'] = array('relation' => 'AND', $meta_query);

			
			$manga_query = new WP_Query( $manga_args );
			//remove_filter( 'posts_clauses', array($this,'wp_query_search_clauses'), 1 );
			
			return apply_filters( 'madara_manga_query_filter', $manga_query, $manga_args );
		}

        function wp_query_search_clauses( $pieces, $query){

			// if we are query wp-manga
			if(!is_admin() && isset($query->query['post_type']) && $query->query['post_type'] == 'wp-manga' && isset($query->query['s']) && $query->query['s'] != ''){
				global $wpdb;

				// make sure we have wp_manga_search_text
				$is_search_text_available = get_option('wp_manga_search_text_built', 0);
				if($is_search_text_available){
					$wheres = explode(' AND ', $pieces[ 'where' ]);
					foreach($wheres as &$where){
						if(strpos($where, 'posts.post_excerpt LIKE') !== false){
							$search_clauses = explode(' OR ', $where);
							foreach($search_clauses as &$search){
								if(strpos($search, 'posts.post_excerpt LIKE') !== false){
									$search = str_replace('post_excerpt LIKE','wp_manga_search_text LIKE', $search) . ' OR ' . $search;
								}
							}

							$where = implode(' OR ', $search_clauses);
						}
					}
					$pieces[ 'where' ] = implode( ' AND ', $wheres );
				}
			}            

            return $pieces;
        }

		function wp_manga_body_classes( $classes ) {

			global $wp_manga_functions;

			if( is_user_logged_in() ){
				if( current_user_can( 'administrator' ) ){
					$classes[] = 'admin';
				}

				global $post;
				if( is_singular( 'wp-manga' ) && get_current_user_id() == $post->post_author ){
					$classes[] = 'is-manga-author';
				}
			}

			if ( $wp_manga_functions->is_wp_manga_page() ) {
				$classes[] = 'wp-manga-page';
			}

			if ( $wp_manga_functions->is_manga_reading_page() ) {
				$classes[] = 'reading-manga';

				global $wp_manga_setting;
				$click_to_scroll = $wp_manga_setting->get_manga_option( 'click_to_scroll', '1' );
				$keyboard_navigate = $wp_manga_setting->get_manga_option( 'keyboard_navigate', '1' );
				if($click_to_scroll == '1'){
					$classes[] = 'click-to-scroll';
				}

                if($keyboard_navigate == '1'){
					$classes[] = 'keyboard-navigate';
				}
			}

			if ( $wp_manga_functions->is_manga_single() ) {
				$classes[] = 'manga-page';
			}

			return $classes;

		}

		function wp_manga_pagination( $query, $element, $template ) {

			if ( $query->max_num_pages == 1 || get_query_var( 'wp_manga_paged' ) >= $query->max_num_pages ) {
				return;
			}

			global $wp_manga_setting;
			$paging_style = $wp_manga_setting->get_manga_option( 'paging_style', 'load-more' );

			if ( $paging_style == 'load-more' ) {
				return $this->wp_manga_ajax_loadmore( $query, $element, $template );
			} else {
				return $this->wp_manga_default_pagination( $query, $element );
			}

		}

		function wp_manga_default_pagination( $query, $element ) {

			$args = array(
				'total'     => $query->max_num_pages,
				'current'   => get_query_var( 'wp_manga_paged' ),
				'prev_next' => false,
				'show_all'  => false,
				'end_size'  => 3,
				'mid_size'  => 5,
			);

			$html = '<div class="wp-manga-default-pagination wp-manga-pagination">';
			$html .= '<div class="nav-links">';
			$html .= paginate_links( $args );
			$html .= '</div>';
			$html .= '</div>';

			return $html;
		}

		function wp_manga_ajax_loadmore( $query, $element, $template ) {

			$current_page = get_query_var( 'wp_manga_paged' );

			$html = '<div class="wp-manga-ajax-loadmore wp-manga-pagination">';
			$html .= '<div class="wp-manga-ajax-button" data-element="' . esc_attr( $element ) . '" data-template="' . esc_attr( $template ) . '">';
			$html .= '<i class="fa fa-spinner fa-spin"></i>';
			$html .= '<span>' . esc_html( 'Load more', WP_MANGA_TEXTDOMAIN ) . '</span>';
			$html .= '</div>';
			$html .= '</div>';

			$args                  = $query->query;
			$args['max_num_pages'] = $query->max_num_pages;

			$html .= $this->wq_vars_js( $args );

			return $html;
		}

		function wq_vars_js( $args, $echo = false ) {

			$html = '<div class="wp-manga-query-vars hidden">';
			$html .= '<script>';
			$html .= 'var manga_args = ' . json_encode( $args ) . ';';
			$html .= '</script>';
			$html .= '</div>';

			if ( $echo ) {
				echo wp_kses( $html, array(
					'div'    => array(
						'class' => array(),
					),
					'script' => array(),
				) );
			}

			return $html;
		}

		function wp_manga_bg_img() {

			return WP_MANGA_URI . 'assets/images/bg-search.jpg';

		}

		function wp_manga_breadcrumb_middle( $obj ) {

			global $wp_manga_functions;

			if ( $obj == null ) {
				return;
			}

			$middle = array();

			if ( $wp_manga_functions->is_manga_single() ) {

				if ( $obj->post_parent != 0 ) {

					$post_id = $obj->post_parent;

					do {
						$parent = get_post( $post_id );
						if ( $parent && ! is_wp_error( $parent ) ) {
							$middle[ $parent->post_title ] = get_the_permalink( $parent->ID );
						}
						$post_id = $parent->post_parent;

					} while ( !empty($post_id) && $post_id != 0);

				}

				if ( has_term( '', 'wp-manga-genre' ) ) {

					$genre = get_the_terms( $obj->ID, 'wp-manga-genre' )[0];

					do {

						if ( $genre && ! is_wp_error( $genre ) ) {

							$middle[ $genre->name ] = get_term_link( $genre );
						}

						$genre = get_term_by( 'id', $genre->parent, 'wp-manga-genre' );

					} while ( isset( $genre->parent ) && $genre->parent != 0 );

				}

			} elseif ( is_tax() && $wp_manga_functions->is_manga_archive() ) {

				if ( $obj->parent == 0 ) {
					return false;
				}

				$term = get_term_by( 'id', $obj->parent, $obj->taxonomy );

				do {
					if ( $term && ! is_wp_error( $term ) ) {
						$middle[ $term->name ] = get_term_link( $term );
					}
					$term = get_term_by( 'id', $term->parent, $term->taxonomy );
				} while ( isset( $term->parent ) && $term->parent !== 0 );

			}

			return $middle;

		}

		function wp_manga_search_filter_url( $filter ) {

			if ( empty( $filter ) && empty( $_GET ) ) {
				return;
			}

			$get_vars = array_merge( $_GET, array( 'm_orderby' => $filter ) );

			if(isset($get_vars['s'])){
				$get_vars['s'] = urlencode(stripcslashes($get_vars['s']));
			}

			$url = add_query_arg( $get_vars, home_url() );

			return $url;

		}

		function manga_activation_sampledata_redirect() {
			if ( get_option( 'manga_activation_sampledata_redirect', false ) ) {
				delete_option( 'manga_activation_sampledata_redirect' );
				if ( ! isset( $_GET['activate-multi'] ) ) {
					wp_redirect( admin_url( 'plugins.php?wp-manga=first-install' ) );
				}
			}
		}

		function is_content_manga( $post_id ) {

			$chapter_type = get_post_meta( $post_id, '_wp_manga_chapter_type', true );

			return $chapter_type == 'text' || $chapter_type == 'video' ? true : false;

		}

		/**
		 * Chapter content is parsed because it has complex format
		 *
		 * SERVER 1 NAME :: CONTENT 1 || SERVER 2 NAME :: CONTENT
		 **
		 **/
		function chapter_video_content($chapter_post, $server = ''){

			$content = $chapter_post->post_content;

			$return = '';

			$all_content = explode("||",$content);
			if($server == ''){
				global $wp_manga_setting;
				$server = $wp_manga_setting->get_manga_option( 'default_video_server', '' );
			}
			foreach($all_content as $serv){
				if($server != '') {
					$values = explode("::", $serv);
					if(count($values) == 2){
						if(trim($server) == trim($values[0])) {
							$return = $values[1];
							break;
						}
					}
				}
			}

			if($return == ''){
				 foreach($all_content as $serv){
					// return content of first server
					$values = explode("::", $serv);
					if(count($values) == 2){
						$return = $values[1];
					} else {
						$return = $serv;
					}
					break;
				}
			}

			echo apply_filters('the_content', $return);
		}

		/**
		 * Get available servers of chapter video content. Chapter content is parsed because it has complex format
		 *
		 * SERVER 1 NAME :: CONTENT 1 || SERVER 2 NAME :: CONTENT
		 **
		 **/
		function get_chapter_video_server_list($chapter_post){
			$server_list = array();

			if($chapter_post){
				$content = $chapter_post->post_content;

				$all_content = explode("||",$content);
				$idx = 1;

				foreach($all_content as $serv){
					$values = explode("::", $serv);
					if(count($values) == 2){
						array_push($server_list, trim($values[0]));
					} else {
						array_push($server_list, sprintf(esc_html__('Server %d', WP_MANGA_TEXTDOMAIN), $idx));
					}
					$idx++;
				}
			}

			return $server_list;
		}

        /**
         * Get loaded chapter by Chapter Slug
         *
         * @param $manga_id int
         * @param $chapter_slug string
         *
         * @return Chapter data array
                    false if chapter is not loaded yet
         **/
        function get_loaded_chapter_by_slug( $manga_id, $chapter_slug ){
            if(isset($this->_chapters[$manga_id . '-' . $chapter_slug])){
                return $this->_chapters[$manga_id . '-' . $chapter_slug];
            }

            return false;
        }

        /**
         * Save loaded chapter to use later
         *
         * @param $manga_id int
         * @param $chapter_slug string
         * @param $chapter chapter data
         *
         **/
        function cache_chapter_data ($manga_id, $chapter_slug, $chapter){
            $this->_chapters[$manga_id . '-' . $chapter_slug] = $chapter;
        }
        /**
         * prepare search text to be used in Search query
         *
         * @params $manga_id INT Manga ID
         * @return text to search
         **/
        function build_search_text($manga_id){
            global $wp_manga_chapter;
            $alter_name = get_post_meta($manga_id, '_wp_manga_alternative', true);

            $chapter_names = [];
            $chapters = $wp_manga_chapter->get_manga_chapters($manga_id);
            if($chapters && count($chapters)){
                $chapter_names = array_map(function($chapter){return $chapter['chapter_name'] . '-' . $chapter['chapter_name_extend'];}, $chapters);

                if($alter_name){
                    array_unshift($chapter_names, $alter_name);
                }
            }

            return count($chapter_names) ? implode('|', $chapter_names) : '';
        }
	}

	$GLOBALS['wp_manga'] = WP_MANGA::get_instance();
