<?php

	class WP_MANGA_FUNCTIONS {

		const WP_MANGA_TEXTDOMAIN = 'wp-manga';

		public $mangas;

		public $manga_by_views;

		public function __construct() {

			// All Mangas are cached 24 hours
			$all_mangas = get_transient('wp_manga_all_mangas');
			if(!$all_mangas){
				$all_mangas = $this->get_mangas( '_wp_manga_views', 100 );
				set_transient('wp_manga_all_mangas', $all_mangas, 60 * 60 * 24);
			}
			$this->manga_by_views = $all_mangas;
			
			add_filter( 'upload_mimes', array ($this, 'upload_mime_types'), 1, 1 );

			add_action('updated_user_meta', function($meta_id, $user_id, $meta_key, $meta_value) {
			    if ($meta_key === '_wp_manga_bookmark') {
					// clear lightspeed cache
			        do_action('litespeed_purge_user', $user_id);

					$user_action = new WP_MANGA_USER_ACTION();
					do_action('litespeed_purge_url', $user_action->get_user_tab_url('bookmark'));
			    }
			}, 10, 4);
		}
        
        /**
         * Get latest chapters of a manga
         *
         * @params - $post_id - int - Manga ID
         * @params - $q - boolean/string - keyword to search for a string in chapter names. Default is false, which does not require a search
         * @params - $num - int - number of items to return
         * @params - $all_meta - boolean - ???
         * @params - $orderby - select - name|date|index (chapter_number)
         * @params - $order - select - desc|asc
         *
         * @return - 
         **/
		function get_latest_chapters(
			$post_id,
			$q = false,
			$num = 2,
			$all_meta = 0,
			$orderby = 'name',
			$order = 'desc'
		) {

			if ( ! $post_id || $post_id == '' ) {
				$post_id = get_the_ID();
			}

			global $wp_manga_chapter;

			$chapters = $wp_manga_chapter->get_latest_chapters( $post_id, $q, $num, $all_meta, $orderby, $order );
			
			return apply_filters('wp_manga_latest_chapters', $chapters, $post_id, $q, $num, $all_meta, $orderby, $order);

		}

		function volume_dropdown( $post_id, $echo = true ) {

			if ( ! $post_id || $post_id == '' ) {
				$post_id = get_the_ID();
			}

			$output = '';

			ob_start();
			?>
            <select id="wp-manga-volume" name="wp-manga-volume" class="wp-manga-volume">
                <option value="0"> <?php echo esc_html__( 'None', WP_MANGA_TEXTDOMAIN ); ?> </option>
				<?php
					$volumes = $GLOBALS['wp_manga_volume']->get_manga_volumes( $post_id );
					if ( $volumes !== false ) {
						foreach ( $volumes as $v ) { ?>
                            <option value="<?php echo $v['volume_id'] ?>"><?php echo $v['volume_name'] ?></option>
							<?php
						}
					}
				?>
            </select>
			<?php
			$output = ob_get_contents();
			ob_end_clean();

			if ( $echo ) {
				echo $output;
			} else {
				return $output;
			}

		}

		// get all mangas. This is used to calculate manga rank. It consumes a lot of memory
		function get_mangas( $meta_key = null, $post_per_page = -1 ) {

			$args = array(
				'post_type'      => 'wp-manga',
				'posts_per_page' => $post_per_page,
				'post_status'    => 'publish',
			);
			if ( $meta_key ) {
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] = $meta_key;
			}
			$manga = new WP_Query( $args );

			return $manga->posts;
		}

		function get_manga_rank( $post_id ) {

			if ( ! $post_id || $post_id == '' ) {
				$post_id = get_the_ID();
			}

			$mangas = $this->manga_by_views;

			wp_reset_postdata();

			$arr = array();
			foreach ( $mangas as $manga ) {
				$arr[] = $manga->ID;
			}

			$rank      = array_search( $post_id, $arr );
			if($rank !== false){
				$true_rank = $rank + 1;
				$string    = $true_rank;
				$tail      = substr( $true_rank, - 1 );

				if ( $tail == 1 && $true_rank != 11) {
					$string = sprintf(esc_html__('%dst', WP_MANGA_TEXTDOMAIN), $true_rank);
				} elseif ( $tail == 2 && $true_rank != 12 ) {
					$string = sprintf(esc_html__('%dnd', WP_MANGA_TEXTDOMAIN), $true_rank);
				} elseif ( $tail == 3 && $true_rank != 13 ) {
					$string = sprintf(esc_html__('%drd', WP_MANGA_TEXTDOMAIN), $true_rank);
				} else {
					$string = sprintf(esc_html__('%dth', WP_MANGA_TEXTDOMAIN), $true_rank);
				}

				return $string;
			} else {
				return esc_html__('N/A', WP_MANGA_TEXTDOMAIN);
			}
		}

		/**
		 * Get average rating for a manga 
		 **/
		function get_total_review( $post_id, $reviews = array() ) {

			if ( ! $post_id || $post_id == '' ) {
				$post_id = get_the_ID();
			}

			if ( empty( $reviews ) ) {
				$reviews = get_post_meta( $post_id, '_manga_reviews', true );
			}

			
			if ( $reviews && is_array($reviews) ) {
				$num       = count( $reviews );
				$sub_total = 0;
				foreach ( $reviews as $review ) {
                    if($review >= 0 && $review <= 5){
                        $sub_total = $sub_total + intval( $review );
                    } else {
                        $num--;
                    }
				}
				if($num){
					$total = round( $sub_total / $num, 1, PHP_ROUND_HALF_UP );
				} else {
					$total = 0;
				}
			} else {
				$total = 0;
			}

			return $total;
		}
		
		/**
		 * Get number of ratings
		 **/
		function get_total_vote( $post_id, $raw = false ) {

			if ( ! $post_id || $post_id == '' ) {
				$post_id = get_the_ID();
			}

			$reviews = get_post_meta( $post_id, '_manga_reviews', true );

			if ( $reviews == false || !is_array($reviews)) {
				return false;
			}
			
			$total_votes = count( $reviews );
			$total_votes = get_post_meta( $post_id, '_manga_total_votes', true );
			if($total_votes != count($reviews)){
				update_post_meta($post_id, '_manga_total_votes', count($reviews));
			}

			return !$raw ? wp_manga_number_format_short(count($reviews)) : count($reviews);

		}

		function get_client_ip() {
			$ipaddress = '';
			if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
				$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
			} else if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
				$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else if ( isset( $_SERVER['HTTP_X_FORWARDED'] ) ) {
				$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
			} else if ( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) ) {
				$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
			} else if ( isset( $_SERVER['HTTP_FORWARDED'] ) ) {
				$ipaddress = $_SERVER['HTTP_FORWARDED'];
			} else if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
				$ipaddress = $_SERVER['REMOTE_ADDR'];
			} else {
				$ipaddress = 'UNKNOWN';
			}

			return $ipaddress;
		}

		function manga_rating_display( $post_id = '', $is_manga_single = false ) {
			$wp_manga_settings = get_option( 'wp_manga_settings' );
			$user_rate = isset( $wp_manga_settings['user_rating'] ) ? $wp_manga_settings['user_rating'] : 1;
			
			if($user_rate){
				echo $this->manga_rating( $post_id, $is_manga_single );
			}
		}

		function manga_rating( $post_id = '', $is_manga_single = false ) {

			if ( ! $post_id || $post_id == '' ) {
				$post_id = get_the_ID();
			}

			$html        = '';
			$post_rating = get_post_meta( $post_id, '_manga_avarage_reviews', true );
			$all_reviews = get_post_meta( $post_id, '_manga_reviews', true );
            $logged_in = is_user_logged_in();
            
			if ( $logged_in ) {
				$user_rating = isset( $all_reviews[ get_current_user_id() ] ) ? $all_reviews[ get_current_user_id() ] : '';
			} else {
				$user_rating = isset( $all_reviews[ $this->get_client_ip() ] ) ? $all_reviews[ $this->get_client_ip() ] : '';
			}
            
            global $wp_manga_setting;
                
            $guest_rating = $wp_manga_setting->get_manga_option( 'guest_rating', 1 );
            
            $class_toggle = 'allow_vote';
            if(!$guest_rating && !$logged_in){
                // if not allow guest rating, and user is not logged in
                $class_toggle = '';
            }

			//post total rating
			$html .= '<div class="post-total-rating ' . $class_toggle . '">';
			$html .= $this->manga_output_rating( $post_rating );
			$html .= '</div>';

			//user rating
			if ( $is_manga_single ) {
				$html .= '<div class="user-rating ' . $class_toggle . '">';
				$html .= $this->manga_output_rating( $user_rating, true );
				$html .= '</div>';

				$html .= '<input type="hidden" class="rating-post-id" value="' . $post_id . '">';
			}

			return $html;

		}

		function manga_output_rating( $rate, $is_user_rating = false ) {
            
			$html = '';

			$max_rate = 5;
			if ( 1 == strlen( $rate ) ) {
				for ( $i = 0; $i < $max_rate; $i ++ ) {
					if ( $i < $rate ) {
						$html .= '<i class="ion-ios-star ratings_stars rating_current"></i>';
					} else {
						$html .= '<i class="ion-ios-star-outline ratings_stars"></i>';
					}
				}
			} else {
				$rate = round( floatval($rate), 1, PHP_ROUND_HALF_UP );
				for ( $i = 0; $i < $max_rate; $i ++ ) {
					if( substr( $rate, 0, 1 ) == $i ) {
						if( 3 > substr( $rate, - 1 ) ){
							$html .= '<i class="ion-ios-star-outline ratings_stars"></i>';
						} elseif ( ( 3 <= substr( $rate, - 1 ) ) && ( substr( $rate, - 1 ) <= 7 ) ){
							$html .= '<i class="ion-ios-star-half ratings_stars rating_current_half"></i>';
						} else {
							$html .= '<i class="ion-ios-star ratings_stars rating_current"></i>';
						}
					} elseif ( $i < $rate ) {
						$html .= '<i class="ion-ios-star ratings_stars rating_current"></i>';
					} else {
						$html .= '<i class="ion-ios-star-outline ratings_stars"></i>';
					}
				}
			}

			if ( $is_user_rating ) {
				$html .= wp_kses( __( '<span class="score font-meta total_votes">Your Rating</span>', WP_MANGA_TEXTDOMAIN ), array( 'span' => array( 'class' => array() ) ) );
			} else {
				$html .= '<span class="score font-meta total_votes">' . $rate . '</span>';
			}

			return $html;
		}

		function update_manga_views( $manga_id, $chapter_slug = '' ) {

			// get all post meta for this manga at once
			$all_meta = get_post_meta( $manga_id );

			$manga_views = $all_meta['_wp_manga_views'][0];

			$day   = date( 'd' );
			$month = date( 'm' );
			$year  = date( 'y' );

			

			// day views
			$day_views = isset($all_meta['_wp_manga_day_views'][0]) ? $all_meta['_wp_manga_day_views'][0] : serialize(array());
			$day_views = unserialize($day_views);
			$d_views   = isset( $day_views['views'] ) ? $day_views['views'] : 0;

			$d_views++;

			$new_day_views = array( 'views' => $d_views, 'date' => $day );
			
			
			// week views

			//_wp_manga_week_views
			// $week_start = get_weekstartend()
			$start = get_option( 'start_of_week', 1 );
			switch ( $start ) {
				case 0:
					$day = 'Sun';
					break;
				case 1:
					$day = 'Mon';
					break;
				case 2:
					$day = 'Tue';
					break;
				case 3:
					$day = 'Wed';
					break;
				case 4:
					$day = 'Thu';
					break;
				case 5:
					$day = 'Fri';
					break;
				case 6:
					$day = 'Sat';
					break;
				default:
					$day = 'Mon';
					break;
			}

			$current_week_day = date( 'D-d' );
			$week_views       = isset($all_meta['_wp_manga_week_views'][0]) ? $all_meta['_wp_manga_week_views'][0] : serialize(array());
			$week_views = unserialize($week_views);
			$w_views          = isset( $week_views['views'] ) ? $week_views['views'] : 0;
			$w_date           = isset( $week_views['date'] ) ? $week_views['date'] : $current_week_day;

			$w_views++;

			$new_week_views = array( 'views' => $w_views, 'date' => $w_date );

			
			// month views
			$month_views = isset($all_meta['_wp_manga_month_views'][0]) ? $all_meta['_wp_manga_month_views'][0] : serialize(array());
			$month_views = unserialize($month_views);
			$m_views     = isset( $month_views['views'] ) ? $month_views['views'] : 0;
			
			$m_views++;
			
			$new_month_views = array( 'views' => $m_views, 'month' => $month );

			
			// year views
			$year_views = isset($all_meta['_wp_manga_year_views'][0]) ? $all_meta['_wp_manga_year_views'][0] : serialize(array());
			$year_views = unserialize($year_views);
			$y_views    = isset( $year_views['views'] ) ? $year_views['views'] : 0;
			$y_date     = isset( $year_views['date'] ) ? $year_views['date'] : $year;
			if ( $y_date != $year ) {
				$y_views = 1;
			} else {
				$y_views ++;
			}
			
			$new_year_views = array( 'views' => $y_views, 'date' => $year );
			
			/**
			 * Since 2.2.6+, we use wpdb to update the post meta instead of update_post_meta
			 */
			global $wpdb;

			$meta_updates = [
				'_wp_manga_day_views'         => $new_day_views,
				'_wp_manga_day_views_value'   => $d_views,
				'_wp_manga_week_views'        => $new_week_views,
				'_wp_manga_week_views_value'  => $w_views,
				'_wp_manga_month_views'       => $new_month_views,
				'_wp_manga_month_views_value' => $m_views,
				'_wp_manga_year_views'        => $new_year_views,
				'_wp_manga_year_views_value'  => $y_views,
				'_wp_manga_views'             => ++$manga_views,
			];

			foreach ( $meta_updates as $key => $value ) {
				update_post_meta($manga_id, $key, $value);
			}

			wp_cache_delete($manga_id, 'post_meta');
		}
		
		function print_ranking_views( $manga_id ) {
			
			$rank        = $this->get_manga_rank( $manga_id );
			$views       = $this->get_manga_monthly_views( $manga_id );
			
			echo apply_filters('madara_manga_ranking_views', sprintf( _n(' %1s, it has %2s monthly view',' %1s, it has %2s monthly views', $views, WP_MANGA_TEXTDOMAIN ), $rank, $views ), $manga_id, $rank, $views);
		}

		function get_manga_monthly_views( $post_id ) {
			if ( ! $post_id || $post_id == '' ) {
				$post_id = get_the_ID();
			}
			$month_views = get_post_meta( $post_id, '_wp_manga_month_views', true );

			$m_views = isset( $month_views['views'] ) ? $month_views['views'] : 0;

			return wp_manga_number_format_short(intval($m_views));
		}

		/**
		 * Get all manga metadata at once.
		 * @param int $manga_id
		 * @param array $arr - list of meta name to return
		 * Available meta names:
		 * - status
		 * - alternative
		 * - type
		 * @return array
		 */
		function get_manga_meta_bulk($manga_id, $arr = array()) {
			$all_meta = get_post_meta($manga_id);
			$meta_values = array();
			foreach($all_meta as $key => $value){
				if(in_array(str_replace('_wp_manga_', '', $key), $arr)){
					$meta_values[$key] = call_user_func(array($this, 'get_' . str_replace('_wp_manga_', '', $key)), $manga_id,  $value[0]);
				}
			}
			
			return $meta_values;
		}

		function get_manga_status( $post_id, $value = '' ) {
			if(!$value){
				if ( ! $post_id || $post_id == '' ) {
					$post_id = get_the_ID();
				}

				$value = get_post_meta( $post_id, '_wp_manga_status', true );
			}

			$val = isset( $value ) ? $value : 'on-going';
			
			$string = esc_html__( 'Completed', WP_MANGA_TEXTDOMAIN );
			
			global $wp_manga_post_type;
			$default_status = $wp_manga_post_type->get_manga_status();
			foreach($default_status as $key => $value){
				if($key == $val){
					$string = $value;
					break;
				}
			}
			
			return apply_filters('wp_manga_manga_status', $string, $post_id);
		}

		function get_manga_alternative( $post_id, $value = '' ) {
			if(!$value){
				if ( ! $post_id || $post_id == '' ) {
					$post_id = get_the_ID();
				}
				$value = get_post_meta( $post_id, '_wp_manga_alternative', true );
			}

			return apply_filters( 'wp_manga_info_filter', $value, 'alternative' );
		}

		function get_manga_type( $post_id, $value = '' ) {
			if(!$value){
				if ( ! $post_id || $post_id == '' ) {
					$post_id = get_the_ID();
				}

				$value = get_post_meta( $post_id, '_wp_manga_type', true );
			}

			return apply_filters( 'wp_manga_info_filter', $value, 'type' );
		}

		function get_manga_release( $post_id, $value = '' ) {
			if(!$value){
				if ( ! $post_id || $post_id == '' ) {
					$post_id = get_the_ID();
				}

				$value = get_the_term_list( $post_id, 'wp-manga-release', '', ',', '' );
			}

			return apply_filters( 'wp_manga_info_filter', $value, 'release' );
		}

		function get_manga_authors( $post_id = '', $value = '' ) {
			if(!$value){
				if ( ! $post_id || $post_id == '' ) {
					$post_id = get_the_ID();
				}

				$value = get_the_term_list( $post_id, 'wp-manga-author', '', ',', '' );
			}

			return apply_filters( 'wp_manga_info_filter', $value, 'author' );
		}

		function get_manga_artists( $post_id, $value = '' ) {
			if(!$value){
				if ( ! $post_id || $post_id == '' ) {
					$post_id = get_the_ID();
				}

				$value = get_the_term_list( $post_id, 'wp-manga-artist', '', ',', '' );
			}

			return apply_filters( 'wp_manga_info_filter', $value, 'artist' );
		}

		function get_manga_genres( $post_id, $value = '' ) {
			if(!$value){
				if ( ! $post_id || $post_id == '' ) {
					$post_id = get_the_ID();
				}

				$value = get_the_term_list( $post_id, 'wp-manga-genre', '', ', ', '' );
			}

			return apply_filters( 'wp_manga_info_filter', $value, 'genre' );

		}
        
        function get_manga_tags( $post_id, $value = '' ) {
			if(!$value){
				if ( ! $post_id || $post_id == '' ) {
					$post_id = get_the_ID();
				}

				$value = get_the_term_list( $post_id, 'wp-manga-tag', '', ', ', '' );
			}

			return apply_filters( 'wp_manga_info_filter', $value, 'tag' );

		}

		function prepare_archive_posts( $args = array() ) {
			
			if ( is_post_type_archive( 'wp-manga' ) || is_tax( 'wp-manga-author' ) || is_tax( 'wp-manga-artist' ) || is_tax( 'wp-manga-genre' ) && ! is_admin() ) {

				global $wp_query;
				$orderby = isset( $_GET['m_orderby'] ) ? $_GET['m_orderby'] : 'latest';
				
                $query   = array(
					'orderby'   => $orderby
				);

				$release = get_query_var( 'wp-manga-release' ) ? get_query_var( 'wp-manga-release' ) : false;
				if ( $release ) {
					$query['tax_query'][] = array(
						'taxonomy' => 'wp-manga-release',
						'terms'    => $release,
						'field'    => 'slug'
					);
				}

				$author = get_query_var( 'wp-manga-author' ) ? get_query_var( 'wp-manga-author' ) : false;
				if ( $author ) {
					$query['tax_query'][] = array(
						'taxonomy' => 'wp-manga-author',
						'terms'    => $author,
						'field'    => 'slug'
					);
				}

				$artist = get_query_var( 'wp-manga-artist' ) ? get_query_var( 'wp-manga-artist' ) : false;
				if ( $artist ) {
					$query['tax_query'][] = array(
						'taxonomy' => 'wp-manga-artist',
						'terms'    => $artist,
						'field'    => 'slug'
					);
				}

				$genre = get_query_var( 'wp-manga-genre' ) ? get_query_var( 'wp-manga-genre' ) : false;
				if ( $genre ) {
					$query['tax_query'][] = array(
						'taxonomy' => 'wp-manga-genre',
						'terms'    => $genre,
						'field'    => 'slug'
					);
				}
				
				$query = wp_parse_args( $args, $query );
				
				$query = apply_filters( 'wp_manga_prepare_archive_posts', $query );
                
                global $wp_manga;
                
                $wp_query->wp_manga = $wp_manga->mangabooth_manga_query( $query );
				$wp_query->wp_manga->post_count = count( $wp_query->wp_manga->posts );
			}

		}

		function wp_manga_has_manga( $args = array() ) {

			global $wp_query;

			if ( isset( $wp_query->wp_manga ) ) {
				return $wp_query->wp_manga->have_posts();
			}

			return false;
		}

		function wp_manga_the_manga() {
			global $wp_query;

			return $wp_query->wp_manga->the_post();
		}

		function get_archive_link( $orderby ) {
			$url = '';
			if ( is_post_type_archive( 'wp-manga' ) ) {
				$url = add_query_arg( 'm_orderby', $orderby, get_post_type_archive_link( 'wp-manga' ) );
			} else if ( is_tax( 'wp-manga-author' ) || is_tax( 'wp-manga-release' ) || is_tax( 'wp-manga-artist' ) || is_tax( 'wp-manga-genre' ) ) {
				$term     = get_query_var( 'term' );
				$taxonomy = get_query_var( 'taxonomy' );
				switch ( $taxonomy ) {
					case 'wp-manga-author':
						$url = add_query_arg( 'm_orderby', $orderby, get_term_link( $term, 'wp-manga-author' ) );
						break;
					case 'wp-manga-artist':
						$url = add_query_arg( 'm_orderby', $orderby, get_term_link( $term, 'wp-manga-artist' ) );
						break;
					case 'wp-manga-genre':
						$url = add_query_arg( 'm_orderby', $orderby, get_term_link( $term, 'wp-manga-genre' ) );
						break;
					case 'wp-manga-release':
						$url = add_query_arg( 'm_orderby', $orderby, get_term_link( $term, 'wp-manga-release' ) );
						break;
					default:
						# code...
						break;
				}
			}

			return $url;
		}

		function activated( $current, $check ) {
			$active = '';
			if ( $current == $check ) {
				$active = 'active';
			}

			return $active;
		}

		function get_time_diff( $time, $timestamp = false ) {
			// 259200 - 3 days.
			$diff    = '';
			$check   = ! $timestamp ? strtotime( $time ) : $time;
			$current = current_time( 'timestamp' );
			if ( $current > $check + 259200 ) {
				$diff = mysql2date( get_option( 'date_format' ), $time, true );
			} else {
				$diff = sprintf( __( '%s ago',  WP_MANGA_TEXTDOMAIN ), human_time_diff( $check, $current ) );
			}

			return apply_filters('wp_manga_get_time_diff', $diff, $time, $timestamp);
		}

		function get_html( $post_id ) {
			$html     = '';
			$html     .= '<div class="page-listing-item">';
			$html     .= '<div class="page-item-detail">';
			$html     .= '<div class="item-thumb">';
			$html     .= '<a href="' . get_the_permalink( $post_id ) . '">' . get_the_post_thumbnail( $post_id, 'manga-thumb-1' ) . '</a>';
			$html     .= '</div>';
			$html     .= '<div class="item-summary">';
			$html     .= '<div class="post-title font-title">';
			$html     .= '<h5>';
			$html     .= '<a href="' . get_the_permalink( $post_id ) . '">' . get_the_title( $post_id ) . '</a>';
			$html     .= '</h5>';
			$html     .= '</div>';
			$html     .= '<div class="meta-item rating">';
			$html     .= $this->manga_rating( $post_id );
			$html     .= '</div>';
			$html     .= '<div class="list-chapter">';
			$chapters = $this->get_latest_chapters( $post_id, 2, null );
			if ( $chapters ) {
				foreach ( $chapters as $chapter ) {
					$manga_link = $this->build_chapter_url( $post_id, $chapter, 'paged' );

					$html .= '<div class="chapter-item">';
					$html .= '<span class="chapter font-meta">';
					$html .= '<a href="' . esc_url( $manga_link ) . '">' . esc_attr( $chapter['chapter_name'] ) . '</a>';
					$html .= '</span>';

					if ( $chapter['volume_id'] != 0 ):

						$volume = $GLOBALS['wp_manga_chapter']->get_chapter_volume( $post_id, $chapter['volume_id'] );

						$html .= '<span class="vol font-meta">';
						$html .= '<a href="javascript:void(0)">' . esc_attr( $volume['volume_name'] ) . '</a>';
						$html .= '</span>';

					endif;
					$html .= '<span class="post-on font-meta">';
					$html .= $this->get_time_diff( $chapter['date'] );
					$html .= '</span>';
					$html .= '</div>';
				}
			}
			$html .= '</div>';
			$html .= '</div>';
			$html .= '</div>';
			$html .= '</div>';

			return $html;
		}

		function manga_filter() {
			global $wp_manga_template;
			$template = $wp_manga_template->load_template( 'manga', 'archive-filter', true );

			return $template;
		}

		function bookmark_link_e( $post_id = '', $is_manga_single = '' ) {

			echo $this->create_bookmark_link( $post_id, $is_manga_single );

		}

		function create_bookmark_link( $post_id = '', $is_manga_single = '', $chapter_slug = '' ) {
			
			global $wp_manga_setting, $wp_manga_functions, $wp_manga;
			
			$user_bookmark = $wp_manga_setting->get_manga_option('user_bookmark', 1);
			
			if(!$user_bookmark) return;

			$reading_chapter = madara_permalink_reading_chapter();
			
			$output          = '';
			$chapter         = $chapter_slug ? $chapter_slug : ($reading_chapter ? $reading_chapter['chapter_slug'] : '');
			$page            = isset( $_GET[$wp_manga->manga_paged_var] ) ? $_GET[$wp_manga->manga_paged_var] : '1';
			
			$is_manga_single = $is_manga_single !== '' ? $is_manga_single : $wp_manga_functions->is_manga_single();
			
			

			if ( empty( $post_id ) ) {
				$post_id = get_the_ID();
			}

			if ( ! empty( $chapter ) ) {
				$chapter_id = $GLOBALS['wp_manga_chapter']->get_chapter_id_by_slug( $post_id, $chapter );
			}
			$chapter_id = ! empty( $chapter_id ) ? $chapter_id : '';

			if ( empty( $post_id ) ) {
				return;
			}

			$total_bookmarked = get_post_meta($post_id, '_wp_user_bookmarked', true);
			if(!$total_bookmarked){
				$total_bookmarked = 0;
			} else {
				$total_bookmarked = count($total_bookmarked);
			}

			if ( $is_manga_single ) {
				$output .= '<div class="action_icon">';
			}

			if ( is_user_logged_in() ) {

				$bookmark_manga = get_user_meta( get_current_user_id(), '_wp_manga_bookmark', true );

				if ( ! empty( $bookmark_manga ) ) {

					$col = 'id';
					$index = array_search( $post_id, array_map(function($element) use($col ){return $element[$col ];}, $bookmark_manga) );
					
					if ( $index !== false ) {

						$is_chapter_bookmarked = ! empty( $chapter_id ) && isset( $bookmark_manga[ $index ]['c'] ) && $chapter_id == $bookmark_manga[ $index ]['c'];
						$is_page_bookmarked    = $is_chapter_bookmarked && isset( $bookmark_manga[ $index ]['p'] ) && $bookmark_manga[ $index ]['p'] == $page;

						if ( $is_manga_single || $is_page_bookmarked ) {

							$output .= '<a class="wp-manga-delete-bookmark" href="javascript:void(0)" data-action="delete-bookmark" data-post-id="' . $post_id . '" title="' . esc_attr__('Delete Bookmark',WP_MANGA_TEXTDOMAIN) . '"><i class="icon ion-md-checkmark"></i></a>';

							if ( $is_manga_single ) {
								$output .= '</div>';
								$output .= '<div class="action_detail">';
								$output .= '<span>' . $total_bookmarked > 2 ? sprintf(esc_attr__( 'You bookmarked this with %s others', WP_MANGA_TEXTDOMAIN ), wp_manga_number_format_short($total_bookmarked)) : ($total_bookmarked == 2 ? esc_attr__( 'You bookmarked this with one another', WP_MANGA_TEXTDOMAIN ) : esc_attr__( 'You bookmarked this', WP_MANGA_TEXTDOMAIN )) . '</span>';
								$output .= '</div>';
							}

							return $output;
						}

					}
				}

			} else {
				$output .= '<script type="text/javascript"> var requireLogin2BookMark = true; </script>';
			}

			$output .= '<a href="#" class="wp-manga-action-button" data-action="bookmark" data-post="' . $post_id . '" data-chapter="' . $chapter_id . '" data-page="' . $page . '" title="' . esc_attr__('Bookmark', WP_MANGA_TEXTDOMAIN) . '"><i class="icon ion-ios-bookmark"></i></a>';

			if ( $is_manga_single ) {
				$output .= '</div>';
				$output .= '<div class="action_detail">';
				$output .= '<span>' . ($total_bookmarked > 0 ? sprintf(esc_attr__( '%s Users bookmarked This', WP_MANGA_TEXTDOMAIN ), wp_manga_number_format_short($total_bookmarked)) : esc_attr__( 'Bookmark This', WP_MANGA_TEXTDOMAIN )) . '</span>';
				$output .= '</div>';
			}

			return $output;
		}

		function chapter_html_backend( $c, $post_id ) {

			$storage = $this->get_hosts( $post_id, $c['chapter_id'] );

			if ( $storage ) {
				unset( $storage['inUse'] );

				$hosts = array_keys( $storage );
			}

			$chapter_id          = isset( $c['chapter_id'] ) ? $c['chapter_id'] : '';
			$chapter_name        = isset( $c['chapter_name'] ) ? $c['chapter_name'] : '';
			$chapter_name_extend = isset( $c['chapter_name_extend'] ) ? $c['chapter_name_extend'] : '';

			$output = '<li>';

			$output .= '<input type="checkbox" id="chapter_select_'. $chapter_id .'" value="'. $chapter_id .'" class="chapter_select_item chapter_vol_' . $c['volume_id'] . '"><a href="#" class="wp-manga-edit-chapter" data-chapter="' . esc_attr( $chapter_id ) . '">[' . $chapter_id . '] ' . wp_kses_post( $chapter_name ) . $this->filter_extend_name( $chapter_name_extend );
			
			$status_text = '';
			switch($c['chapter_status']){
				case 2:
					$status_text = ' <span class="chapter-status uploading">' . esc_html__('(uploading)', WP_MANGA_TEXTDOMAIN) . '</span>';
					break;
				case 1:
					$status_text = ' <span class="chapter-status completed">' . esc_html__('(completed)', WP_MANGA_TEXTDOMAIN) . '</span>';
					break;
				default:
					break;
			}
			
			$status_text = apply_filters('wp_manga_chapter_status_text', $status_text, $c);
			
			$output .=  $status_text. '</a>';

			if ( ! empty( $hosts ) ) {
				$output .= '<span class="manga-chapter-storages">';
				foreach ( $hosts as $host ) {
					if ( $host == 'picasa' ) {
						$host = 'blogspot';
					}
					$output .= '<span class="' . esc_attr( $host ) . '-storage">' . esc_attr( $host ) . '</span>';
				}
				$output .= '</span>';
			}

			$output .= '<a id="wp-manga-delete-chapter" data-chapter="' . esc_attr( $chapter_id ) . '" href="javascript:void(0)" title="' . esc_html__( 'Delete Chapter', WP_MANGA_TEXTDOMAIN ) . '"><i class="ion-ios-close"></i></a>';

			$output = apply_filters( 'madara_chapter_content_li_html', $output, $chapter_id, $c );

			$output .= '</li>';

			return $output;

		}

		function list_chapters_by_volume( $post_id, $chapters, $is_search = false ) {

			if ( $chapters == false ) {
				return false;
			}

			$expanded = $is_search ? 'expanded' : '';
			$output = '<div id="admin-wp-manga-multi-actions"><p><span class="title">' . esc_html__('With selected: ', WP_MANGA_TEXTDOMAIN) . '</span><select id="select_multi_action"><option></option><option value="delete">'.esc_html__('Delete', WP_MANGA_TEXTDOMAIN) .'</option>
            <option value="move">'.esc_html__('Move', WP_MANGA_TEXTDOMAIN) .'</option></select><button id="btn_do_multi_action" value="'.$post_id.'">'.esc_html__('Apply', WP_MANGA_TEXTDOMAIN) .'</button></p>';
            
            $output .= '<p id="admin-wp-manga-move-chapters" style="display:none"><span class="title">' . esc_html__('Select volume:', WP_MANGA_TEXTDOMAIN) . '</span>';
                        if ( ! empty( $chapters ) ) {
                            $output .= '<select id="admin-wp-manga-move-chapters-select-vols">';
                            $output .= '<option>' . esc_html__('Select volume', WP_MANGA_TEXTDOMAIN) . '</option>';
                            $output .= '<option value="0">' . esc_html__('No Volume', WP_MANGA_TEXTDOMAIN) . '</option>';
                            foreach ( $chapters as $volume_id => $v ) {
                                if($volume_id != 0){
                                    $output .= '<option value="' . $volume_id . '">' . $v['volume_name'] . '</option>';
                                }
                            }
                            $output .= '</select>';
                        }
            $output .= '</p>';
            $output .= '</div>';
            
			$output .= '<ul id="volumes-list">';
			if ( ! empty( $chapters[0] ) ) {
				$output .= '<li class="manga-single-volume expanded" data-volume-id="0">';
				$output .= '<h3 class="volume-title">';
				$output .= '<input type="checkbox" id="vol_select_all_0" value="0" class="vol_select_all"/><span class="title">' . esc_html__( 'No Volume ', WP_MANGA_TEXTDOMAIN ) . '</span>';
				$output .= '<div class="volume-edit">';
				$output .= '<a href="javascript:void(0);" id="wp-manga-delete-volume" title="' . esc_html__( 'Delete Volume', WP_MANGA_TEXTDOMAIN ) . '"><i class="fa fa-times"></i></a>';
				$output .= '</div>';
				$output .= '</h3>';
				$output .= '<ul>';
				foreach ( $chapters[0]['chapters'] as $c ) {
					$output .= $this->chapter_html_backend( $c, $is_search, $post_id );
				}
				$output .= '</ul>';
				$output .= '</li>';

				unset( $chapters[0] );

				//variable to check if it is the first element
				$i = true;
			}

			if ( ! empty( $chapters ) ) {
				foreach ( $chapters as $volume_id => $v ) {

					if ( ! isset( $i ) && ! $is_search ) {
						$this_expanded = 'expanded';
					} else {
						$this_expanded = $expanded;
					}
					$i = true;

					$output .= '<li class="manga-single-volume ' . $this_expanded . '" data-volume-id="' . esc_attr( $volume_id ) . '">';

					$output .= '<h3 class="volume-title">';
					$output .= '<input type="checkbox" id="vol_select_all_'. $volume_id .'" value="'. $volume_id .'" class="vol_select_all"/><span>' . $v['volume_name'] . '</span>';
					$output .= '<div class="volume-edit">';
					$output .= '<a href="javascript:void(0);" id="edit-volume-name" title="' . esc_attr__( 'Edit Volume Name', WP_MANGA_TEXTDOMAIN ) . '"><i class="fas fa-pencil-alt"></i></a>';
					$output .= '<a href="javascript:void(0);" id="wp-manga-delete-volume" title="' . esc_attr__( 'Delete Volume', WP_MANGA_TEXTDOMAIN ) . '"><i class="fa fa-times"></i></a>';
					$output .= '</div>';

					$output .= '<input type="text" class="volume-input-field disable-submit" value="' . esc_attr( $v['volume_name'] ) . '" />';
					$output .= '</h3>';

					$output .= '<ul>';

					if ( ! empty( $v['chapters'] ) ) {
						foreach ( $v['chapters'] as $c ) {
							$output .= $this->chapter_html_backend( $c, $is_search, $post_id );
						}
					} else {
						$output .= '<span class="no-chapter">' . esc_html__( 'There is no chapter in this volume ', WP_MANGA_TEXTDOMAIN ) . '</span>';
					}

					$output .= '</ul>';
					$output .= '</li>';
				}
			}

			$output .= '</ul>';

			return $output;

		}

		/**
		 * @params
		 * 		$chapter_count - int - Number of "latest chapters" returned. "Latest Chapters" order is defined by $order param
		 *		$order - string (defined by... N/A)
		 * 
		 */
		function get_all_chapters( $post_id, $order = '', $chapters_count = 0, $chapter_page = 1 ) {
			
			global $wp_manga_volume, $wp_manga_chapter, $wp_manga_storage;

			$volumes = $wp_manga_volume->get_volumes( array(
				'post_id' => $post_id,
				'order' => $order
			) );

			$manga_chapters = array();
			
			$chapters_found = 0;

			$offset = ($chapter_page - 1) * $chapters_count;

			if ( ! empty( $volumes ) ) {
				foreach ( $volumes as $volume ) {
					
					$chapters = $wp_manga_chapter->get_chapters( array(
															'post_id'   => $post_id,
															'volume_id' => $volume['volume_id']
														),
														false,
														'',
														$order,
														$chapters_count,
														$offset
													); 
					
													
					$manga_chapters[ $volume['volume_id'] ] = array(
						'volume_name' => $volume['volume_name'],
						'volume_slug' => $wp_manga_storage->slugify($volume['volume_name']),
						'date'        => $volume['date'],
						'date_gmt'    => $volume['date_gmt'],
						'chapters'    => $chapters
					);
					
					// stop if we reach limit needed
					$chapters_found += count($chapters);
					if($chapters_count && $chapters_found >= $chapters_count){
						break;
					}
				}
			}
			
			if(!$chapters_count || $chapters_found < $chapters_count){
				
				$no_volume_chapters = $wp_manga_chapter->get_chapters( array(
						'post_id'   => $post_id,
						'volume_id' => 0
					),
					false,
					'',
					$order,
					$chapters_count,
					$offset
				);


				if ( $no_volume_chapters ) {
					$manga_chapters['0'] = array(
						'volume_name' => esc_html__( 'No Volume', WP_MANGA_TEXTDOMAIN ),
						'date'        => '',
						'date_gmt'    => '',
						'chapters'    => $no_volume_chapters
					);
				}
			
			}
			
			$manga_chapters = apply_filters('wp_manga_get_all_chapters', $manga_chapters, $post_id);

			if ( empty( $manga_chapters ) ) {
				return false;
			}

			return $manga_chapters;

		}

		function get_reading_style( $user_id = null, $manga_id = 0 ) {
			global $wp_manga, $is_amp_required;
			
			if(isset($is_amp_required) && $is_amp_required){
				return 'list';
			}
			
			$manga_paged = get_query_var($wp_manga->manga_paged_var);
			
			if(!$manga_paged){
				if ( $user_id == null && is_user_logged_in() ) {
					$user_id = get_current_user_id();
				}

				if ( ! empty( $user_id ) ) {
					$user_reading_style = get_user_meta( $user_id, '_manga_reading_style', true );
				} else {
					$user_reading_style = ''; // this should be read from Plugin
				}

				$user_reading_style = apply_filters( 'get_reading_style', $user_reading_style, $manga_id );
				
				if ( empty( $user_reading_style ) ) {
					$user_reading_style = 'paged';
				}
			} else {
				return 'paged'; // if there is /p/ param, then we consider it paged reading style
			}

			return $user_reading_style;
		}

		/**
         * Display latest chapters for a series
         *
         * @return - HTML - HTML code of chapters list
         **/
		function manga_meta( $post_id, $all_meta = 0, $count_chap = 2, $force_count = false ) {
            // this flag is to control the visibility of the "meta"
            $visible = apply_filters('wp_manga_meta_visibility', true);
            
            if(!$visible) return;
            
			global $wp_manga_database, $wp_manga_storage;
			
			$sort_setting = $wp_manga_database->get_sort_setting();

			$sort_by    = $sort_setting['sortBy'];
			$sort_order = $sort_setting['sort'];
			
			$user_id = null;
			if ( is_user_logged_in() ) {
				$user_id = get_current_user_id();
			}

			$manga_reading_style = $this->get_reading_style( $user_id, $post_id );

			$list_chapter = $this->get_latest_chapters( $post_id, null, $force_count ? $count_chap : apply_filters('wp_manga_latest_chaptes_count', $count_chap), $all_meta, $sort_by, $sort_order );

			if ( ! empty( $list_chapter ) ) {
				
				// save queried volumes to reduce call to DB
				$vols = array();
				foreach ( $list_chapter as $chapter ) {
					$this_vol = false;
					
					if ( $chapter['volume_id'] !== '0') {
						if(!isset($vols[$chapter['volume_id']])){ 
							$this_vol = $GLOBALS['wp_manga_volume']->get_volume_by_id( $post_id, $chapter['volume_id'] );
							$vols[$chapter['volume_id']] = $this_vol;
						} else {
							$this_vol = $vols[$chapter['volume_id']];
						}
						
						if($this_vol){
							// set volume_slug for chapter to reduce calls to DB when querying volume info.
							$chapter['volume_slug'] = $wp_manga_storage->slugify( $this_vol['volume_name'] );
						}
					}
					
					$c_url = $this->build_chapter_url( $post_id, $chapter, $manga_reading_style );

					?>
                    <div class="chapter-item <?php echo apply_filters('wp_manga_chapter_item_class', '', $chapter, $post_id);?>">

						<?php if ( isset( $chapter['chapter_name'] ) ) { ?>
                        <span class="chapter font-meta">
							<a href="<?php echo esc_attr( $c_url ); ?>" class="btn-link"> <?php echo apply_filters('wp_manga_archive_chapter_name', wp_kses_post( $chapter['chapter_name'] ), $chapter, $post_id); ?> </a>
						</span>
						<?php } ?>
						
						<?php if ( $this_vol !== false ) { ?>
							<span class="vol font-meta">
								<a href="<?php echo esc_attr( $c_url ); ?>"> <?php echo $this_vol['volume_name']; ?> </a>
							</span>
						<?php }

						if ( ! empty( $chapter['date'] ) ) {
							$show_chapter_date = apply_filters('wp_manga_chapter_item_date_show', 'on', $chapter);
							if($show_chapter_date == 'on'){
								$time_diff = $this->get_time_diff( $chapter['date'] );

								if ( $time_diff ) {

									$time_diff = apply_filters( 'madara_archive_chapter_date', $time_diff, $chapter['chapter_id'], $chapter['date'], $c_url );

									?>
									<span class="post-on font-meta">
										<?php echo wp_kses_post( $time_diff ); ?>
									</span>
									<?php
								}
							}
							
						}
						?>
                    </div>
					<?php
				}
			}
		}
        
        /**
         * Display latest chapters for a series, get from cache if possible
         *
         * @return - HTML - HTML code of chapters list
         **/
		function manga_meta_cached( $post_ids, $post_id, $all_meta = 0, $count_chap = 2, $force_count = false ) {
            // this flag is to control the visibility of the "meta"
            $visible = apply_filters('wp_manga_meta_visibility', true);
            
            if(!$visible) return;
            
			global $wp_manga_database, $wp_manga_storage;
			
			$sort_setting = $wp_manga_database->get_sort_setting();

			$sort_by    = $sort_setting['sortBy'];
			$sort_order = $sort_setting['sort'];
			
			$user_id = null;
			if ( is_user_logged_in() ) {
				$user_id = get_current_user_id();
			}

			$manga_reading_style = $this->get_reading_style( $user_id, $post_id );

			if ( ! $post_id || $post_id == '' ) {
				$post_id = get_the_ID();
			}

			global $wp_manga_chapter;

			$num = $force_count ? $count_chap : apply_filters('wp_manga_latest_chaptes_count', $count_chap);

			$chapters = $wp_manga_chapter->get_latest_chapters_bulk( $post_ids, null, $num, $all_meta, $sort_by, $sort_order );
			
			if(isset($chapters[$post_id])){
				$chapters = $chapters[$post_id];
			} else {
				$chapters = $this->get_latest_chapters( $post_id, null, $num, $all_meta, $sort_by, $sort_order );
			}
			
			$list_chapter = apply_filters('wp_manga_latest_chapters', $chapters, $post_id, null, $num, $all_meta, $sort_by, $sort_order);

			if ( ! empty( $list_chapter ) ) {
				
				// save queried volumes to reduce call to DB
				$vols = array();
				foreach ( $list_chapter as $chapter ) {
					$this_vol = false;
					
					if ( $chapter['volume_id'] !== '0') {
						if(!isset($vols[$chapter['volume_id']])){ 
							$this_vol = $GLOBALS['wp_manga_volume']->get_volume_by_id( $post_id, $chapter['volume_id'] );
							$vols[$chapter['volume_id']] = $this_vol;
						} else {
							$this_vol = $vols[$chapter['volume_id']];
						}
						
						if($this_vol){
							// set volume_slug for chapter to reduce calls to DB when querying volume info.
							$chapter['volume_slug'] = $wp_manga_storage->slugify( $this_vol['volume_name'] );
						}
					}
					
					$c_url = $this->build_chapter_url( $post_id, $chapter, $manga_reading_style );

					?>
                    <div class="chapter-item <?php echo apply_filters('wp_manga_chapter_item_class', '', $chapter, $post_id);?>">

						<?php if ( isset( $chapter['chapter_name'] ) ) { ?>
                        <span class="chapter font-meta">
							<a href="<?php echo esc_attr( $c_url ); ?>" class="btn-link"> <?php echo wp_kses_post( $chapter['chapter_name'] ); ?> </a>
						</span>
						<?php } ?>
						
						<?php if ( $this_vol !== false ) { ?>
							<span class="vol font-meta">
								<a href="<?php echo esc_attr( $c_url ); ?>"> <?php echo $this_vol['volume_name']; ?> </a>
							</span>
						<?php }

						if ( ! empty( $chapter['date'] ) ) {
							$show_chapter_date = apply_filters('wp_manga_chapter_item_date_show', 'on', $chapter);
							if($show_chapter_date == 'on'){
								$time_diff = $this->get_time_diff( $chapter['date'] );

								if ( $time_diff ) {

									$time_diff = apply_filters( 'madara_archive_chapter_date', $time_diff, $chapter['chapter_id'], $chapter['date'], $c_url );

									?>
									<span class="post-on font-meta">
										<?php echo wp_kses_post( $time_diff ); ?>
									</span>
									<?php
								}
							}
							
						}
						?>
                    </div>
					<?php
				}
			}
		}
        
        function get_chapters_count($manga_id){
            global $wp_manga_chapter;
            return $wp_manga_chapter->get_chapters_count($manga_id);
        }

		function manga_get_all_chapter(
			$post_id,
			$all_meta = 0,
			$orderby = 'name',
			$order = 'desc'
		) {
			$user_id = null;
			if ( is_user_logged_in() ) {
				$user_id = get_current_user_id();
			}

			$manga_reading_style = $this->get_reading_style( $user_id, $post_id );

			$list_chapter = $this->get_latest_chapters( $post_id, null, 0, $all_meta, $orderby, $order );

			echo '<div class="row c-row">';


			if ( ! empty( $list_chapter ) ) {
				foreach ( $list_chapter as $chapter ) {
					$c_url = $this->build_chapter_url( $post_id, $chapter, $manga_reading_style );

					?>
                    <div class="chapter-item col-md-4">

						<?php if ( isset( $chapter['chapter_name'] ) ) { ?>
                            <span class="chapter font-meta">
							<a href="<?php echo esc_attr( $c_url ); ?>"> <?php echo esc_html( $chapter['chapter_name'] ); ?> </a>
						</span>
						<?php } ?>
						<?php
							if ( $chapter['volume_id'] != 0 ) {
								?>
                                <span class="vol font-meta">
							<?php $this_vol = $GLOBALS['wp_manga_volume']->get_volume_by_id( $post_id, $chapter['volume_id'] ); ?>
									<?php if ( $this_vol !== false ) { ?>
                                        <a href="<?php echo esc_attr( $c_url ); ?>"> <?php echo $this_vol['volume_name']; ?> </a>
									<?php } ?>
						</span>
								<?php
							}

							if ( ! empty( $chapter['date'] ) ) {
								$time_diff = $this->get_time_diff( $chapter['date'] );
								$time_diff = apply_filters( 'madara_archive_chapter_date', $time_diff, $chapter['chapter_id'], $chapter['date'], $c_url );

								if ( $time_diff ) {
									?>
                                    <span class="post-on font-meta">
                                        <?php echo wp_kses_post( $time_diff ); ?>
                                    </span>
									<?php
								}
							}
						?>
                    </div>
					<?php
				}
			}

			echo '</div>';

		}

		function get_manga_archive_page_setting() {

			global $wp_manga_setting;
			//get manga archive page id
			$manga_archive_page = $wp_manga_setting->get_manga_option( 'manga_archive_page', 0 );

			if ( $manga_archive_page == 0 ) {
				return false;
			}

			return $manga_archive_page;

		}

		function get_manga_archive_link(){
			if($manga_archive_page = $this->get_manga_archive_page_setting()){
				return get_permalink($manga_archive_page);
			} else {
				return get_post_type_archive_link( 'wp-manga' );
			}
		}
        
        /**
         * Get list of bookmarked mangas by an User
         *
         * @param int $user_id      ID of user
         * @param string $order     by default, bookmarked items are ordered by the time it is added to the list. Use 'update' to order items by manga's Latest Updated time   
         *
         * @return array Array of bookmark object
                                array(
                                    'id'       => Post ID,
                                    'c'        => Chapter ID,
                                    'p'        => Chapter Page, (current reading page in a chapter),
                                    'unread_c' => [], // number of unread chapter - obsolete,
                                    'post' => WP_POST object
                                );
         */
        function get_bookmarked_mangas($user_id, $order = ''){
            $bookmarks     = get_user_meta( $user_id, '_wp_manga_bookmark', true );
            
            if($bookmarks){                
                if($order == 'update'){
                    $args = array('orderby' => 'latest', 'post__in' => array_column( $bookmarks, 'id'), 'posts_per_page' => -1);
                } else {
                    $args = array('orderby' => 'post__in', 'post__in' => array_column( $bookmarks, 'id'), 'posts_per_page' => -1);
                }
                
                global $wp_manga;
                
                $myquery = $wp_manga->mangabooth_manga_query( $args );
                                    
                if($myquery->have_posts()){
                    $mangas = $myquery->posts;
                    
                    $ordered_bookmarks = array();
                    
                    foreach($mangas as $manga){
                        foreach($bookmarks as $bookmark){
                            if($bookmark['id'] == $manga->ID){
                                $item = $bookmark;
                                $item['post'] = $manga; // save for later use
                                $ordered_bookmarks[] = $item;
                                break;
                            }
                        }
                    }
                    
                    return array_reverse($ordered_bookmarks);
                }
                
                return $bookmarks;
            }
            
            return array();
        }

		function is_manga_archive_front_page() {

			$manga_archive_page = $this->get_manga_archive_page_setting();

			if ( is_bool( $manga_archive_page ) ) {
				return false;
			}

			//if manga archive page is set to be front-page
			if ( get_option( 'page_on_front' ) == $manga_archive_page && is_front_page() ) {
				return true;
			}

			return false;

		}

		function is_manga_archive_page() {

			$manga_archive_page = $this->get_manga_archive_page_setting();

			if ( is_bool( $manga_archive_page ) ) {
				return false;
			}

			global $wp_query;
			$current_page_id = $wp_query->queried_object_id;

			//if current page is set to be manga archive page
			if ( $manga_archive_page == $current_page_id ) {
				return true;
			}

			return false;

		}

		function is_manga_posttype_archive() {

			if ( is_post_type_archive( 'wp-manga' ) || $this->is_manga_archive_page() || $this->is_manga_archive_front_page() ) {
				return true;
			}

			return false;

		}

		function is_manga_search_page() {

			$post_type = isset( $_GET['post_type'] ) ? $_GET['post_type'] : '';

			if ( is_search() && $post_type == 'wp-manga' ) {
				return true;
			}

			return false;
		}

		function is_manga_single( $manga_id = 0) {

			if ( is_singular( 'wp-manga' ) && ! $this->is_manga_reading_page() ) {
				
				if($manga_id == 0){
					return true;
				} elseif($manga_id == get_the_ID()){
					return true;
				}
			}

			return false;
		}

		// used when loading page. not in a ajax call
		function is_manga_reading_page( $chapter_slug = '', $manga_id = 0) {

			if ( ! is_singular( 'wp-manga' ) ) {
				return false;
			}
			
			$current_manga_id = get_the_ID();
			$reading_chapter = madara_permalink_reading_chapter();
			
			if ( ($chapter_slug == '' && $reading_chapter && ($manga_id == 0 || ($manga_id == $current_manga_id))) || ($chapter_slug != '' && $reading_chapter && $reading_chapter['chapter_slug'] == $chapter_slug && $current_manga_id == $manga_id) ) {
				return true;
			}

			return false;
		}

		function is_manga_archive() {

			$is_manga_archive = false;

			if ( is_tax( 'wp-manga-genre' ) || is_tax( 'wp-manga-release' ) || is_tax( 'wp-manga-tag' ) || is_tax( 'wp-manga-author' ) || is_tax( 'wp-manga-artist' ) || $this->is_manga_posttype_archive() ) {
				$is_manga_archive = true;
			}

			return apply_filters( 'is_manga_archive', $is_manga_archive );

		}

		function is_wp_manga_page() {

			if ( $this->is_manga_archive() || $this->is_manga_reading_page() || $this->is_manga_single() || $this->is_manga_search_page() ) {
				return true;
			}

			return false;

		}
        
        // check if current page is the User Settings page
        function is_user_settings_page(){
            global $wp_manga_setting;

			$user_page = $wp_manga_setting->get_manga_option( 'user_page', null );
            
            if(is_page()){
                global $wp;
                $current_slug = add_query_arg( array(), $wp->request );
                if(get_post_field( 'post_name', $user_page ) == $current_slug){
                    return true;
                }
            }
            
            return false;
        }

		/**
		 * Check if current page is a manga genre page
		 * @param $term string Name of genre to check
		 * @return boolean True if current page is a genre $term page
		 */
		function is_manga_genre( $term = '' ){
			return is_tax('wp-manga-genre', $term);
		}

		/**
		 * Check if current page is a manga tag page
		 * @param $term string Name of tag to check
		 * @return boolean True if current page is a tag $term page
		 */
		function is_manga_tag( $term = '' ){
			return is_tax('wp-manga-tag', $term);
		}

		/**
		 * Check if current manga is an adult manga
		 */
		function is_adult_manga(){
			if ( ! is_singular( 'wp-manga' ) ) {
				return false;
			}

			return get_post_meta(get_the_ID(), 'manga_adult_content', true);
		}

		function unique_slug( $post_id, $c_name, $c_id = null ) {

			global $wp_manga_chapter, $wp_manga_volume, $wp_manga_storage;

			$args = array(
				'post_id'      => $post_id,
				'chapter_name' => $c_name,
			);

			$chapters  = $wp_manga_chapter->get_chapters( $args );
			$slugified = $wp_manga_storage->slugify( $c_name );

			if( $c_id ){
				$this_chapter = $wp_manga_chapter->get_chapter_by_id( $post_id, $c_id );

				if( isset( $this_chapter['chapter_slug'] ) && $slugified === $this_chapter['chapter_slug'] ){
					return $this_chapter['chapter_slug'];
				}

			}

			if ( $chapters ) {
				$col = 'chapter_slug';
				$chapters_slug = array_map(function($element) use($col ){return $element[$col ];}, $chapters);

				$i = 0;

				do {
					if($i == 0){
					    $new_slugified = $slugified;
					} else {
					    $new_slugified = $slugified . '_' . $i;
					}
					
					$i ++;
				} while ( in_array( $new_slugified, $chapters_slug ) );

				return $new_slugified;

			}

			return $slugified;

		}

		function check_unique_chapter( $c_name, $volume, $post_id ) {

			if ( empty( $c_name ) || empty( $post_id ) ) {
				return false;
			}

			global $wp_manga_chapter, $wp_manga_volume, $wp_manga_storage;

			$args = array(
				'post_id'      => $post_id,
				'chapter_name' => $c_name,
			);

			$chapters = $wp_manga_chapter->get_chapters( $args );
			$col = 'chapter_slug';
			$chapters_slug = array_map(function($element) use($col ){return $element[$col ];}, $chapters);
			$slugified     = $wp_manga_storage->slugify( $c_name );

			if ( $chapters ) {

				$i = 0;

				do {
					$i ++;
					$new_slugified = $slugified . '_' . $i;
				} while ( in_array( $new_slugified, $chapters_slug ) );
				$col = 'volume_id';
				if ( ! in_array( $volume, array_map(function($element) use($col ){return $element[$col ];}, $chapters) ) ) {
					return array( 'c_uniq_slug' => $new_slugified, 'overwrite' => false );
				}

				$output = '';
				foreach ( $chapters as $c ) {

					if ( $c['volume_id'] != 0 ) {
						$this_volume = $wp_manga_volume->get_volume_by_id( $post_id, $c['volume_id'] );
						$this_volume = $this_volume['volume_name'];
					} else {
						$this_volume = __( 'No Volume', WP_MANGA_TEXTDOMAIN );
					}

					$output .= '<label><input type="radio" name="chapter-to-overwrite" value="' . $c['chapter_id'] . '"><span>' . $c['chapter_name'] . $this->filter_extend_name( $c['chapter_name_extend'] ) . ' (' . $this_volume . ')</span></label><br>';
				}

				return array( 'c_uniq_slug' => $new_slugified, 'output' => $output );
			} else {
				return false;
			}

		}

		function validate_size_setting( $file_size ) {

			$size = rtrim( $file_size, 'M' );

			if ( ! is_numeric( $size ) ) {
				$size = rtrim( $file_size, 'G' );
				$size *= 1024;
			}

			if ( $size <= 10 ) {
				return 'low';
			} elseif ( 10 < $size && $size <= 64 ) {
				return 'medium';
			} elseif ( 64 <= $size ) {
				return 'high';
			}

			return false;

		}

		function validate_time_setting( $time ) {

			if ( $time <= 60 ) {
				return 'low';
			} elseif ( 60 < $time && $time < 300 ) {
				return 'medium';
			} elseif ( 300 <= $time ) {
				return 'high';
			}

			return false;
		}

		function filter_extend_name( $chapter_name_extend ) {

			if ( ! empty( $chapter_name_extend ) ) {
				return ' - ' . $chapter_name_extend;
			}

			return '';

		}

		function parse_chapter_full_name( $chapter ) {

			if ( empty( $chapter ) || ! isset( $chapter['chapter_name_extend'] ) ) {
				return null;
			}

			return $chapter['chapter_name'] . $this->filter_extend_name( $chapter['chapter_name_extend'] );

		}
		
		function get_chapter_by_slug( $post_id, $chapter_slug ){
			global $wp_manga, $wp_manga_chapter;
            
            $chapter = $wp_manga->get_loaded_chapter_by_slug( $post_id, $chapter_slug );
			if(!$chapter){
				$chapter = $wp_manga_chapter->get_chapter_by_slug( $post_id, $chapter_slug );
                
                $wp_manga->cache_chapter_data($post_id, $chapter_slug, $chapter);
                
			}
			
			return $chapter;
		}

		/**
		 * $chapter_slug - array (DB row) of chapter (preferred for better performance) or Chapter Slug (string)
		 **/
		function build_chapter_url( $post_id, $chapter_slug, $page_style = null, $host = null, $paged = null ) {
			global $wp_manga_chapter, $wp_manga_volume, $wp_manga_storage, $wp_manga;
			if(is_array($chapter_slug)){
				$chapter = $chapter_slug;
				$chapter_slug = $chapter['chapter_slug'];
			} else {
				$chapter = $this->get_chapter_by_slug($post_id, $chapter_slug);
			}

			$url = get_the_permalink( $post_id );
            
			$addition_params = array();

			//get and remove query string from URL
			$query_string = parse_url( $url, PHP_URL_QUERY );
			if ( $query_string ) {
				//remove
				$url = trim( $url, $query_string );

				parse_str( $query_string, $query_vars );
				if ( ! empty( $query_vars ) ) {
					$addition_params = array_merge( $addition_params, $query_vars );
				}
			}

			$is_slug_structure = ! get_option( 'permalink_structure' ) || get_post_status( $post_id ) !== 'publish' ? false : true;

			//remove some special characters
			$url = trim( $url, '/' );
			$url = trim( $url, '?' );

			//volume path
			if ( ! empty( $chapter['volume_id'] ) ) {
				$volume_slug = '';

				if(isset($chapter['volume_slug'])){
					$volume_slug = $chapter['volume_slug'];
				} else {
					$volume = $wp_manga_volume->get_volume_by_id( $post_id, $chapter['volume_id'] );
					if($volume) {
						$volume_slug = $wp_manga_storage->slugify( $volume['volume_name'] );
					}
				}

				if ( !empty($volume_slug ) ) {

					if ( ! $is_slug_structure ) {
						$url = add_query_arg( array( 'volume' => $volume_slug ), $url );
					} else {
						$url .= '/' . $volume_slug;
					}
				}
			}

			//if permalink structure is ?p= or the post haven't be published yet, then use normal query url
			/*
			$options = get_option( 'wp_manga_settings', array() );
			$chapter_slug_or_id = isset( $options['chapter_slug_or_id'] ) ? $options['chapter_slug_or_id'] : 'slug';
			
			if ( ! $is_slug_structure ) {
				$url = add_query_arg( array( 'chapter' => $chapter_slug_or_id == 'slug' ? $chapter_slug : $chapter['chapter_id'] ), $url );
			} else {
				$url .= '/' . ($chapter_slug_or_id == 'slug' ? $chapter_slug : $chapter['chapter_id']);
			}
			*/
			if ( ! $is_slug_structure ) {
				$url = add_query_arg( array( 'chapter' => $chapter_slug ), $url );
			} else {
				$url .= '/' . $chapter_slug;
			}

			//remove page style if it's not manga chapter
			$chapter_type = get_post_meta( $post_id, '_wp_manga_chapter_type', true );
			
			if ( $page_style && $chapter_type != 'text' && $chapter_type != 'video' ) {
				// if $page_style != user setting, then we don't need param
				$user_id = null;
				if ( is_user_logged_in() ) {
					$user_id = get_current_user_id();
				}
				
				if($page_style != $this->get_reading_style( $user_id, $post_id )){
					$addition_params['style'] = $page_style;
				}
			}

			if ( $chapter_type != 'text' && $chapter_type != 'video' && $host ) {
				$addition_params['host'] = $host;
			}
			
			
			if($page_style != 'list' && ($chapter_type == 'manga' || $chapter_type == '')){
				if ( $paged && $paged > 1 ) {
					$url .= '/p/' . $paged . '/';
					
					if(! empty( $addition_params )) {
						unset($addition_params['style']);
					}
					
				}
			}
			
			if ( $is_slug_structure ) {
				$url = trim($url, '/') . '/';
			}
			
			if ( ! empty( $addition_params ) ) {
				$url = add_query_arg( $addition_params, $url );
			}
			
			return apply_filters('wp_manga-chapter-url', $url, $post_id, $chapter, $page_style, $host, $paged);

		}

		function get_ini_file_size( $name ) {

			$filesize = ini_get( $name );

			$filesize = strtolower( $filesize );

			$filesize = rtrim( $filesize, 'm' );
			$filesize = rtrim( $filesize, 'mb' );

			if ( ! is_numeric( $filesize ) ) {
				$filesize = rtrim( $filesize, 'g' );
				$filesize = rtrim( $filesize, 'gb' );

				if ( is_numeric( $filesize ) ) {
					$filesize *= 1024;
				} else {
					return 0;
				}
			}

			return $filesize;

		}

		function max_upload_file_size() {

			$upload_max_filesize = $this->get_ini_file_size( 'upload_max_filesize' );
			$post_max_size       = $this->get_ini_file_size( 'post_max_size' );

			if ( $upload_max_filesize <= $post_max_size ) {
				$actual_max_filesize = $upload_max_filesize;
			} else {
				$actual_max_filesize = $post_max_size;
			}

			return array(
				'actual_max_filesize'    => intval( $actual_max_filesize ) * 1000,
				'actual_max_filesize_mb' => intval( $actual_max_filesize ),
				'upload_max_filesize'    => intval( $upload_max_filesize ) * 1000,
				'post_max_size'          => intval( $post_max_size ) * 1000
			);
		}

		function update_latest_meta( $post_id ) {

			$new_date = current_time( 'timestamp', false );
			$old_date = get_post_meta( $post_id, '_latest_update', true );

			do_action( 'manga_update_chapter', $post_id );

			return update_post_meta( $post_id, '_latest_update', $new_date, $old_date );

		}

		function get_folder_basename($path){
			$parts = explode("/", $path);
			return $parts[count($parts) - 1];
		}

		/**
		 * Get manga chapters data, for manga type only
		 */
		function get_chapter( $post_id, $return_chapters_only = false ) {
			global $wp_manga_chapter_data;
			$results = $wp_manga_chapter_data->get_manga_chapters_data( $post_id );
			
			if( ! empty( $results ) ){
				if( ! $return_chapters_only ){
					return array(
						'total_chapters' => count( $results ),
						'chapters'       => $results
					);
				} else {
					return $results;
				}
			}

			return false;
		}

		/**
		 * Get data of all storages in single chapter
		 */
		function get_hosts( $post_id, $chapter_id ) {

			global $wp_manga_chapter_data;

			$results = $wp_manga_chapter_data->get_manga_chapters_data( $post_id, $chapter_id );

			if( !empty( $results['storage'] ) ){
				return $results['storage'];
			}

			return false;

		}

		/**
		 * Get all available storage of chapter
		 */
		function get_chapter_hosts( $post_id, $chapter_id ) {

			global $wp_manga_chapter_data;

			$results = $wp_manga_chapter_data->select( array( 'chapter_id' => $chapter_id ), array( 'storage' ) );

			if( !empty( $results ) ){
				$col = 'storage';
				return array_map(function($element) use($col ){return $element[$col ];}, $results);
			}

			return false;

		}

		/**
		 *	Get single chapter data, with all images
		 */
		function get_single_chapter( $post_id, $chapter_id ) {

			global $wp_manga_chapter_data;

			$results = $wp_manga_chapter_data->get_manga_chapter_data( $chapter_id, $post_id );

			if( ! empty( $results ) ){
				return $results;
			}

			return false;

		}
		
		/**
		 * $key = 'manga_unique_id';
		 **/
		function get_manga_by($key, $value){
			$args = array();
			if($key == 'manga_unique_id'){
				$args = array(
					'post_type' => 'wp-manga',
					'post_status' => 'any',
				   'meta_query' => array(
					   array(
						   'key' => 'manga_unique_id',
						   'value' => $value,
						   'compare' => '=',
					   )
				   )
				);
			}
			
			if(count($args) > 0){
				$query = new WP_Query($args);
				if($query->have_posts()){
					return $query->get_posts();
				}
			}
		}
		
		public static function get_validated_image_extensions(){
			return apply_filters('wp_manga_validate_image_extensions', array( 'jpg', 'jpeg', 'bmp', 'png', 'gif', 'webp' ));
		}
		
		function upload_mime_types( $mime_types ) {
			$mime_types['webp'] = 'image/webp';
		  
			return $mime_types;
		}

		function get_chapter_views($chapter_id){
			global $wpdb;
			$sql = "SELECT views from {$wpdb->prefix}manga_chapters WHERE chapter_id=%d" ;
			$views = $wpdb->get_var($wpdb->prepare($sql, $chapter_id));
			return $views ? $views : 0;
		}

		function get_total_chapters_views($post_id){
			global $wpdb;
			$sql = "SELECT SUM(views) FROM {$wpdb->prefix}manga_chapters WHERE post_id=%d" ;
			$views = $wpdb->get_var($wpdb->prepare($sql, $post_id));
			return $views ? $views : 0;
		}

		function get_number_chapters_per_page(){
			return apply_filters('wp_manga_chapters_per_page', 0);
		}
	}

	$GLOBALS['wp_manga_functions'] = new WP_MANGA_FUNCTIONS();