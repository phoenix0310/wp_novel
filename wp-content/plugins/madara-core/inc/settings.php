<?php

	class WP_MANGA_SETTING {

		public $settings;

		public function __construct() {

			add_action( 'admin_menu', array( $this, 'wp_manga_setting_page' ) );
			add_action( 'admin_init', array( $this, 'wp_manga_setting_save' ) );

			add_filter( 'dsq_can_load', array( $this, 'comment_type' ), 999 );

			add_action( 'wp_manga_discussion', array( $this, 'wp_manga_discussion_func' ) );
		}

	public function wp_manga_setting_page() {
		add_menu_page( esc_html__( 'WP Manga Settings', WP_MANGA_TEXTDOMAIN ), esc_html__( 'WP Manga Settings', WP_MANGA_TEXTDOMAIN ), 'manage_options', 'wp-manga-settings', array(
			$this,
			'wp_manga_setting_page_layout'
		), 'dashicons-book-alt', 30 );
	}

		function wp_manga_script_settings() {

			$settings = $this->settings;

			if ( ! empty( $settings['loading_slick'] ) ) {
				wp_dequeue_style( 'wp-manga-slick-css' );
				wp_dequeue_style( 'wp-manga-slick-theme-css' );
				wp_dequeue_script( 'wp-manga-slick-js' );
			}

			if ( ! empty( $settings['loading_fontawesome'] ) ) {
				wp_dequeue_style( 'wp-manga-font-awesome' );
			}

			if ( ! empty( $settings['loading_ionicon'] ) ) {
				wp_dequeue_style( 'wp-manga-ionicons' );
			}

		}

		function wp_manga_setting_page_layout() {
			if ( file_exists( WP_MANGA_DIR . 'admin/settings/settings-page.php' ) ) {
				include( WP_MANGA_DIR . 'admin/settings/settings-page.php' );
			}
		}

		function wp_manga_setting_save() {

			if ( isset( $_POST['wp_manga_settings'] ) ) {
				$wp_manga_settings = $_POST['wp_manga_settings'];

				$wp_manga_settings['manga_slug'] = urldecode( sanitize_title( $_POST['wp_manga_settings']['manga_slug'] ) );
				
				$wp_manga_settings['manga_genres_slug'] = urldecode( sanitize_title( $_POST['wp_manga_settings']['manga_genres_slug'] ) );
				
				$wp_manga_settings['manga_author_slug'] = urldecode( sanitize_title( $_POST['wp_manga_settings']['manga_author_slug'] ) );
				
				$wp_manga_settings['manga_tag_slug'] = urldecode( sanitize_title( $_POST['wp_manga_settings']['manga_tag_slug'] ) );
				
				$wp_manga_settings['manga_release_slug'] = urldecode( sanitize_title( $_POST['wp_manga_settings']['manga_release_slug'] ) );
				
				$wp_manga_settings['manga_artist_slug'] = urldecode( sanitize_title( $_POST['wp_manga_settings']['manga_artist_slug'] ) );

				$wp_manga_settings['loading_bootstrap'] = isset( $wp_manga_settings['loading_bootstrap'] ) ? $wp_manga_settings['loading_bootstrap'] : '0';

				$wp_manga_settings['loading_slick'] = isset( $wp_manga_settings['loading_slick'] ) ? $wp_manga_settings['loading_slick'] : '0';

				$wp_manga_settings['loading_fontawesome'] = isset( $wp_manga_settings['loading_fontawesome'] ) ? $wp_manga_settings['loading_fontawesome'] : '0';

				$wp_manga_settings['loading_ionicon'] = isset( $wp_manga_settings['loading_ionicon'] ) ? $wp_manga_settings['loading_ionicon'] : '0';
				
				$wp_manga_settings['admin_show_chapter_type'] = isset( $wp_manga_settings['admin_show_chapter_type'] ) ? $wp_manga_settings['admin_show_chapter_type'] : '0';

				$wp_manga_settings['encrypt_manga_folder_name'] = isset( $wp_manga_settings['encrypt_manga_folder_name'] ) ? $wp_manga_settings['encrypt_manga_folder_name'] : '0';

				$wp_manga_settings['encrypt_chapter_folder_name'] = isset( $wp_manga_settings['encrypt_chapter_folder_name'] ) ? $wp_manga_settings['encrypt_chapter_folder_name'] : '0';

				$wp_manga_settings['admin_hide_bar'] = isset( $wp_manga_settings['admin_hide_bar'] ) ? $wp_manga_settings['admin_hide_bar'] : '0';

				$wp_manga_settings['default_storage'] = isset( $wp_manga_settings['default_storage'] ) ? $wp_manga_settings['default_storage'] : 'local';

				$wp_manga_settings['hosting_selection'] = isset( $wp_manga_settings['hosting_selection'] ) ? $wp_manga_settings['hosting_selection'] : '0';
				
				$wp_manga_settings['hosting_anonymous_name'] = isset( $wp_manga_settings['hosting_anonymous_name'] ) ? $wp_manga_settings['hosting_anonymous_name'] : '0';
				
				$wp_manga_settings['breadcrumb_all_manga_link'] = isset( $wp_manga_settings['breadcrumb_all_manga_link'] ) ? $wp_manga_settings['breadcrumb_all_manga_link'] : '0';
				
				$wp_manga_settings['breadcrumb_first_genre_link'] = isset( $wp_manga_settings['breadcrumb_first_genre_link'] ) ? $wp_manga_settings['breadcrumb_first_genre_link'] : '0';
				
				$wp_manga_settings['navigation_manga_info'] = isset( $wp_manga_settings['navigation_manga_info'] ) ? $wp_manga_settings['navigation_manga_info'] : '0';
				
				$wp_manga_settings['guest_reading_history'] = isset( $wp_manga_settings['guest_reading_history'] ) ? $wp_manga_settings['guest_reading_history'] : '0';
				
				$wp_manga_settings['user_can_upload_avatar'] = isset( $wp_manga_settings['user_can_upload_avatar'] ) ? $wp_manga_settings['user_can_upload_avatar'] : '0';
				$wp_manga_settings['custom_avatar_size'] = isset( $wp_manga_settings['custom_avatar_size'] ) ? $wp_manga_settings['custom_avatar_size'] : '';
				$wp_manga_settings['custom_avatar_min'] = isset( $wp_manga_settings['custom_avatar_min'] ) ? $wp_manga_settings['custom_avatar_min'] : '';
				

				$wp_manga_settings['single_manga_seo'] = isset( $wp_manga_settings['single_manga_seo'] ) ? $wp_manga_settings['single_manga_seo'] : '0';

				$wp_manga_settings['related_manga'] = isset( $wp_manga_settings['related_manga'] ) ? $wp_manga_settings['related_manga'] : '0';

				$wp_manga_settings['default_comment'] = isset( $wp_manga_settings['default_comment'] ) ? $wp_manga_settings['default_comment'] : 'wp';
				
				$wp_manga_settings['default_video_server'] = isset($wp_manga_settings['default_video_server']) ? $wp_manga_settings['default_video_server'] : '';
				
				$wp_manga_settings['reading_style_selection'] = isset($wp_manga_settings['reading_style_selection']) ? $wp_manga_settings['reading_style_selection'] : '0';
				
				$wp_manga_settings['user_rating'] = isset($wp_manga_settings['user_rating']) ? $wp_manga_settings['user_rating'] : 0;
                
                $wp_manga_settings['guest_rating'] = isset($wp_manga_settings['guest_rating']) ? $wp_manga_settings['guest_rating'] : 0;
                
                $wp_manga_settings['manga_view'] = isset($wp_manga_settings['manga_view']) ? $wp_manga_settings['manga_view'] : 0;
				$wp_manga_settings['chapter_view'] = isset($wp_manga_settings['chapter_view']) ? $wp_manga_settings['chapter_view'] : 0;
				
				$wp_manga_settings['user_bookmark'] = isset($wp_manga_settings['user_bookmark']) ? $wp_manga_settings['user_bookmark'] : 0;
				
				$wp_manga_settings['user_bookmark_max'] = isset($wp_manga_settings['user_bookmark_max']) ? intval($wp_manga_settings['user_bookmark_max']) : 30;
				
				$wp_manga_settings['enable_comment'] = isset( $wp_manga_settings['enable_comment'] ) ? $wp_manga_settings['enable_comment'] : '0';
				
				$wp_manga_settings['click_to_scroll'] = isset( $wp_manga_settings['click_to_scroll'] ) ? 
                $wp_manga_settings['click_to_scroll'] : '0';
                
                $wp_manga_settings['keyboard_navigate'] = isset( $wp_manga_settings['keyboard_navigate'] ) ? $wp_manga_settings['keyboard_navigate'] : '0';

				update_option( 'wp_manga_settings', $wp_manga_settings );

				//change manga slug
				$args = get_post_type_object( 'wp-manga' );

				$args->rewrite['slug'] = $wp_manga_settings['manga_slug'];
				$args->has_archive     = true;

				register_post_type( $args->name, $args );

				flush_rewrite_rules();

			}

			do_action( 'wp_manga_setting_save' );

		}

		function get_manga_option( $option, $default_value = false ) {

			$settings = get_option( 'wp_manga_settings', array() );

			if ( 
				! empty( $settings[ $option ] ) 
				|| ( isset( $settings[ $option ] ) && ($settings[ $option ] === '0' || $settings[ $option ] === 0))  //some settings have default value is 0
			) {
				return $settings[ $option ];
			}

			return $default_value;

		}

		function wp_manga_discussion_func() {

			if ( ! comments_open() ) {
				return;
			}

			$enable_comment = $this->get_manga_option( 'enable_comment', false );

			if ( empty( $enable_comment ) ) {
				return;
			}

			$comment_type = $this->get_manga_option( 'default_comment', null );
			$settings = $this->settings = get_option( 'wp_manga_settings', array() );
			?>

			<?php do_action( 'madara_before_manga_discussion' ); // will be obsolete in Madara 2.0 ?>
			<?php do_action( 'wp_manga_before_manga_discussion' ); ?>
			<?php if(!function_exists('wpDiscuz')){
				
				$section_opening = '<div id="manga-discussion" class="c-blog__heading style-2 font-heading">
                <h4 class="h4"> <i class="ion-ios-star"></i> ' . esc_html__( 'MANGA DISCUSSION', WP_MANGA_TEXTDOMAIN ) . '</h4>';

				echo apply_filters('wp_manga_discussion_section_opening', $section_opening);
				?>
           

				<?php if ( $comment_type == 'both' ) { ?>
                    <div class="comment-selection-wrapper">
                        <select class="comment-selection" name="">
                            <option value="local"><?php esc_html_e( 'Local', WP_MANGA_TEXTDOMAIN ); ?></option>
                            <option value="disqus"><?php esc_html_e( 'Disqus', WP_MANGA_TEXTDOMAIN ); ?></option>
                        </select>
                    </div>

                    <script type="text/javascript">
						jQuery(function ($) {
							$('.comment-selection').on('change', function (e) {
								e.preventDefault();
								if ($(this).val() == 'disqus') {
									$('#manga-discussion-local').hide();
									$('#manga-discussion-disqus').show();
								} else {
									$('#manga-discussion-local').show();
									$('#manga-discussion-disqus').hide();
								}
							});
						});
                    </script>
				<?php } ?>
            </div>
			<?php } ?>
            <div <?php if(function_exists('wpDiscuz')){?>id="manga-discussion"<?php }?> class="manga-discussion wrapper">
				<?php if ( $comment_type == 'both' ) { ?>
                    <div id="manga-discussion-local">
						<?php comments_template(); ?>
                    </div>
                    <div id="manga-discussion-disqus" style="display:none;">
						<?php
							do_action( 'dsq_before_comments' );
							do_action( 'dsq_enqueue_comments_script' );
							
							if(file_exists(WP_MANGA_DIR . '/disqus-comment-system/public/partials/disqus-public-display.php'))

								include dirname( WP_MANGA_DIR . '/disqus-comment-system/public/partials/disqus-public-display.php' );
						?>
                    </div>
				<?php } else { ?><?php comments_template(); ?><?php } ?>
            </div>

			<?php do_action( 'madara_after_manga_discussion' ); // will be obsolete in Madara 2.0 ?>
			<?php do_action( 'wp_manga_after_manga_discussion' ); ?>
			<?php
		}

		function comment_type( $script_name ) {

			$comment_type = $this->get_manga_option( 'default_comment', null );

			if ( ! $comment_type ) {
				if ( $GLOBALS['madara_disqus_comments']->is_disqus_active() ) {
					$comment_type = 'disqus';
				} else {
					$comment_type = 'wp';
				}
			}

			if ( $comment_type !== 'disqus' ) {
				return false;
			}

			return $script_name;

		}

	}

	$GLOBALS['wp_manga_setting'] = new WP_MANGA_SETTING();
