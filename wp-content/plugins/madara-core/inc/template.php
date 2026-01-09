<?php

	//data url
	if ( ! defined( 'WP_MANGA_TEMPLATE_DIR' ) ) {
		define( 'WP_MANGA_TEMPLATE_DIR', '/madara-core/' );
	}
	#[AllowDynamicProperties]
	class WP_MANGA_TEMPLATE {
		
		public $filters;

		public function __construct() {
			$this->filters = (object)array('wp_filter' => array(), 'merged_filters' => array());
			
			add_action( 'template_include', array( $this, 'manga_content' ) );
			add_action( 'template_redirect', array( $this, 'template_redirect' ));
		}
		
		function template_redirect()
		{
			$release = isset( $_GET['wp-manga-release'] ) ? $_GET['wp-manga-release'] : '';
			
			if( $release && is_404() )
			{
				wp_redirect( home_url( '?s=&post_type=wp-manga&release=' . $release ) );
				die;
			}
		}
		

		public function manga_content( $template ) {

			global $wp_manga_functions;

			$page_template       = isset( $page_template ) && ! empty( $page_template ) ? $page_template : 'single.php';
			$this->page_template = $page_template;
			$style               = isset( $_GET['style'] ) ? $_GET['style'] : 'paged';
			
			if ( is_singular( 'wp-manga' ) ) {
				if ( $wp_manga_functions->is_manga_reading_page() ) {
					$template = apply_filters('manga_single_reading_template', $this->load_template( 'manga', 'single-reading', false ));
				} elseif ( $wp_manga_functions->is_manga_single() ) {
                    
					$template = $this->load_template( 'manga', 'single', false );
                    
					wp_enqueue_script( 'wp-manga-single-js', WP_MANGA_URI . 'assets/js/manga-single.js', array( 'jquery' ), '', true );
					wp_localize_script( 'wp-manga-single-js', 'wpMangaSingle', array(
						'ajax_url' => admin_url( 'admin-ajax.php' ),
						'home_url' => get_home_url(),
					) );
				}
			} else if ( $wp_manga_functions->is_manga_search_page() ) {

				// search
				$template = $this->load_template( 'manga', 'search', false );

			} else if ( $wp_manga_functions->is_manga_archive() ) {
				//$args = array();
				//$this->remove_all_filters( 'the_content' );
				//$wp_manga_functions->prepare_archive_posts( $args );
				$template = $this->load_template( 'manga', 'archive', false );

				wp_localize_script( 'wp-manga-list-js', 'wpMangaList', array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'home_url' => get_home_url('/'),
				) );
			}
			
			return $template;
		}

		/**
		 * @params 
				$include - boolean - should only be used when this function is called directly
		 **/
		public function load_template( $name, $extend = false, $include = true ) {
			$check = true;
			if ( $extend ) {
				$name .= '-' . $extend;
			}

			$template = null;

			$child_template  = get_stylesheet_directory() . WP_MANGA_TEMPLATE_DIR . $name . '.php';
			$parent_template = get_template_directory() . WP_MANGA_TEMPLATE_DIR . $name . '.php';
			$plugin_template = apply_filters( 'wp-manga-template', WP_MANGA_DIR . 'templates/' . $name . '.php', $name );

			if ( file_exists( $child_template ) ) {

				$template = $child_template;

			} else if ( file_exists( $parent_template ) ) {
				$template = $parent_template;
			} else if ( file_exists( $plugin_template ) ) {
				$template = $plugin_template;
			}

			if ( ! isset( $template ) ) {
				_doing_it_wrong( __FUNCTION__, sprintf( "<strong>%s</strong> does not exists in <code>%s</code>.", $name, $template ), '1.4.0' );

				return false;
			}

			if ( ! $include ) {
				return $template;
			}
			
			include $template;
		}
		
		/**
		 * Retrieve metadata from a file. Based on WP Core's get_file_data function.
		 *
		 * @since  2.1.1
		 * @param  string $file Path to the file.
		 * @return string
		 */
		public static function get_file_version( $file ) {

			// Avoid notices if file does not exist.
			if ( ! file_exists( $file ) ) {
				return '';
			}

			// We don't need to write to the file, so just open for reading.
			$fp = fopen( $file, 'r' ); // @codingStandardsIgnoreLine.

			// Pull only the first 8kiB of the file in.
			$file_data = fread( $fp, 8192 ); // @codingStandardsIgnoreLine.

			// PHP will close file handle, but we are good citizens.
			fclose( $fp ); // @codingStandardsIgnoreLine.

			// Make sure we catch CR-only line endings.
			$file_data = str_replace( "\r", "\n", $file_data );
			$version   = '';

			if ( preg_match( '/^[ \t\/*#@]*' . preg_quote( '@version', '/' ) . '(.*)$/mi', $file_data, $match ) && $match[1] ) {
				$version = _cleanup_header_comment( $match[1] );
			}

			return $version;
		}
		
		/**
		 * Scan the template files.
		 *
		 * @param  string $template_path Path to the template directory.
		 * @return array
		 */
		public static function scan_template_files( $template_path ) {
			$files  = @scandir( $template_path ); // @codingStandardsIgnoreLine.
			$result = array();

			if ( ! empty( $files ) ) {

				foreach ( $files as $key => $value ) {

					if ( ! in_array( $value, array( '.', '..' ), true ) ) {

						if ( is_dir( $template_path . DIRECTORY_SEPARATOR . $value ) ) {
							$sub_files = self::scan_template_files( $template_path . DIRECTORY_SEPARATOR . $value );
							foreach ( $sub_files as $sub_file ) {
								$result[] = $value . DIRECTORY_SEPARATOR . $sub_file;
							}
						} else {
							$result[] = $value;
						}
					}
				}
			}
			return $result;
		}
		
		/**
		 * Look for any template override and return filenames.
		 *
		 * @return array
		 */
		public static function get_all_template_overrides() {
			$override_files  = array();
			
			$scanned_files  = self::scan_template_files( WP_MANGA_DIR . '/templates/' );

			$template_dir = get_template_directory();
			$stylesheet_dir = get_stylesheet_directory();
			$is_child = false;
			if($template_dir != $stylesheet_dir){
				$is_child = true;
			}

			foreach ( $scanned_files as $file ) {
				$child_template = false;
				$child_version = '';
				$parent_version = '';
				$parent_template = false;

				if ( file_exists( $stylesheet_dir . WP_MANGA_TEMPLATE_DIR . $file ) ) {
					$theme_file = $stylesheet_dir . WP_MANGA_TEMPLATE_DIR . $file;
					$child_template = true;
					$child_version =  self::get_file_version( $theme_file );
				}
				
				if ( file_exists( $template_dir . WP_MANGA_TEMPLATE_DIR . $file ) ) {
					$theme_file = $template_dir . WP_MANGA_TEMPLATE_DIR . $file;
					$parent_version =  self::get_file_version( $theme_file );
					$parent_template = true;
				}

				if ( $child_template || $parent_template ) {
					$core_version  = self::get_file_version( WP_MANGA_DIR . '/templates/' . $file );
					
					$override_files[] = array(
						'file'         => str_replace( WP_CONTENT_DIR . '/themes/', '', $theme_file ),
						'version'      => $parent_version,
						'child_version' => $child_version,
						'core_version' => $core_version,
					);
				}
			}
				
			return $override_files;
		}

		public function reset_content( $args ) {
			global $wp_query, $post;
			if ( isset( $wp_query->post ) ) {
				$dummy = wp_parse_args( $args, array(
					'ID'                    => $wp_query->post->ID,
					'post_status'           => $wp_query->post->post_status,
					'post_author'           => $wp_query->post->post_author,
					'post_parent'           => $wp_query->post->post_parent,
					'post_type'             => $wp_query->post->post_type,
					'post_date'             => $wp_query->post->post_date,
					'post_date_gmt'         => $wp_query->post->post_date_gmt,
					'post_modified'         => $wp_query->post->post_modified,
					'post_modified_gmt'     => $wp_query->post->post_modified_gmt,
					'post_content'          => $wp_query->post->post_content,
					'post_title'            => $wp_query->post->post_title,
					'post_excerpt'          => $wp_query->post->post_excerpt,
					'post_content_filtered' => $wp_query->post->post_content_filtered,
					'post_mime_type'        => $wp_query->post->post_mime_type,
					'post_password'         => $wp_query->post->post_password,
					'post_name'             => $wp_query->post->post_name,
					'guid'                  => $wp_query->post->guid,
					'menu_order'            => $wp_query->post->menu_order,
					'pinged'                => $wp_query->post->pinged,
					'to_ping'               => $wp_query->post->to_ping,
					'ping_status'           => $wp_query->post->ping_status,
					'comment_status'        => $wp_query->post->comment_status,
					'comment_count'         => $wp_query->post->comment_count,
					'filter'                => $wp_query->post->filter,

					'is_404'          => false,
					'is_page'         => false,
					'is_single'       => false,
					'is_archive'      => false,
					'is_tax'          => false,
					'current_comment' => 0,
				) );
			} else {
				$dummy = wp_parse_args( $args, array(
					'ID'                    => - 1,
					'post_status'           => 'private',
					'post_author'           => 0,
					'post_parent'           => 0,
					'post_type'             => 'page',
					'post_date'             => 0,
					'post_date_gmt'         => 0,
					'post_modified'         => 0,
					'post_modified_gmt'     => 0,
					'post_content'          => '',
					'post_title'            => '',
					'post_excerpt'          => '',
					'post_content_filtered' => '',
					'post_mime_type'        => '',
					'post_password'         => '',
					'post_name'             => '',
					'guid'                  => '',
					'menu_order'            => 0,
					'pinged'                => '',
					'to_ping'               => '',
					'ping_status'           => '',
					'comment_status'        => 'closed',
					'comment_count'         => 0,
					'filter'                => 'raw',

					'is_404'          => false,
					'is_page'         => false,
					'is_single'       => false,
					'is_archive'      => false,
					'is_tax'          => false,
					'current_comment' => 0,
				) );
			}
			// Bail if dummy post is empty
			if ( empty( $dummy ) ) {
				return;
			}
			// Set the $post global
			$post = new WP_Post( (object ) $dummy );
			setup_postdata( $post );
			// Copy the new post global into the main $wp_query
			$wp_query->post  = $post;
			$wp_query->posts = array( $post );

			// Prevent comments form from appearing
			$wp_query->post_count      = 1;
			$wp_query->is_404          = $dummy['is_404'];
			$wp_query->is_page         = $dummy['is_page'];
			$wp_query->is_single       = $dummy['is_single'];
			$wp_query->is_archive      = $dummy['is_archive'];
			$wp_query->is_tax          = $dummy['is_tax'];
			$wp_query->current_comment = $dummy['current_comment'];

		}

		public function remove_all_filters( $tag, $priority = false ) {
			global $wp_filter, $merged_filters;

			// Filters exist
			if ( isset( $wp_filter[ $tag ] ) ) {

				// Filters exist in this priority
				if ( ! empty( $priority ) && isset( $wp_filter[ $tag ][ $priority ] ) ) {

					// Store filters in a backup
					$this->filters->wp_filter[ $tag ][ $priority ] = $wp_filter[ $tag ][ $priority ];

					// Unset the filters
					unset( $wp_filter[ $tag ][ $priority ] );

					// Priority is empty
				} else {

					// Store filters in a backup
					$this->filters->wp_filter[ $tag ] = $wp_filter[ $tag ];

					// Unset the filters
					unset( $wp_filter[ $tag ] );
				}
			}

			// Check merged filters
			if ( isset( $merged_filters[ $tag ] ) ) {

				// Store filters in a backup
				$this->filters->merged_filters[ $tag ] = $merged_filters[ $tag ];

				// Unset the filters
				unset( $merged_filters[ $tag ] );
			}

			return true;
		}

		public function restore_all_filters( $tag, $priority = false ) {
			global $wp_filter, $merged_filters;

			// Filters exist
			if ( isset( $this->filters->wp_filter[ $tag ] ) ) {

				// Filters exist in this priority
				if ( ! empty( $priority ) && isset( $this->filters->wp_filter[ $tag ][ $priority ] ) ) {

					// Store filters in a backup
					$wp_filter[ $tag ][ $priority ] = $this->filters->wp_filter[ $tag ][ $priority ];

					// Unset the filters
					unset( $this->filters->wp_filter[ $tag ][ $priority ] );
					// Priority is empty
				} else {

					// Store filters in a backup
					$wp_filter[ $tag ] = $this->filters->wp_filter[ $tag ];

					// Unset the filters
					unset( $this->filters->wp_filter[ $tag ] );
				}
			}

			// Check merged filters
			if ( isset( $this->filters->merged_filters[ $tag ] ) ) {

				// Store filters in a backup
				$merged_filters[ $tag ] = $this->filters->merged_filters[ $tag ];

				// Unset the filters
				unset( $this->filters->merged_filters[ $tag ] );
			}

			return true;
		}

		public function page_template_body_class( $classes ) {
			$classes[] = 'page-template';

			$template_slug  = $this->page_template;
			$template_parts = explode( '/', $template_slug );

			foreach ( $template_parts as $part ) {
				$classes[] = 'page-template-' . sanitize_html_class( str_replace( array(
						'.',
						'/'
					), '-', basename( $part, '.php' ) ) );
				$classes[] = sanitize_html_class( str_replace( array( '.', '/' ), '-', basename( $part, '.php' ) ) );
			}
			$classes[] = 'page-template-' . sanitize_html_class( str_replace( '.', '-', $template_slug ) );

			return $classes;
		}

		public function close_default_comment( $open ) {
			if ( is_singular( 'wp-manga' ) ) {
				return false;
			}

			return $open;
		}

		public function sanitize_output( $buffer ) {
			$search = array(
				'/\>[^\S ]+/s',  // strip whitespaces after tags, except space
				'/[^\S ]+\</s',  // strip whitespaces before tags, except space
				'/(\s)+/s',       // shorten multiple whitespace sequences
				"/\r/",
				"/\n/",
				"/\t/",
				'/<!--[^>]*>/s',
			);

			$replace = array(
				'>',
				'<',
				'\\1',
				'',
				'',
				'',
				'',
			);

			$buffer = preg_replace( $search, $replace, $buffer );

			return $buffer;
		}
	}

	$GLOBALS['wp_manga_template'] = new WP_MANGA_TEMPLATE();
