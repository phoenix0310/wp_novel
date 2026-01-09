<?php

	/**
	 * Text Chapter for WP Manga
	 **/

	class WP_MANGA_CHAPTER {

		function __construct() {

		}

		/**
		 * Parse Manga Nav for Manga Chapter
		 *
		 * $args - array - mix
				'manga_id' => int, // manga ID
				'cur_chap'  => string, // chapter slug
				'chapter'   => WP_MANGA_CHAPTER object,
				'all_chaps' => mixed,
				'position'  => 'header' | 'footer',
				'asc' => boolean
		 */

		function manga_nav( $args ) {
			global $wp_manga_functions, $wp_manga_template, $wp_manga_chapter, $wp_manga, $wp_manga_setting;

			extract( $args );

			if( !empty( $_GET['style'] ) ){
				$style = $_GET['style'];
			} else {
				$style = $wp_manga_functions->get_reading_style();
			}

			$single_chap = $wp_manga_functions->get_single_chapter( $manga_id, $chapter['chapter_id'] );
			$inUse       = $wp_manga_setting->get_manga_option( 'default_storage', 'local' );//$single_chap['storage']['inUse'];
			
			$hosting_selection = $wp_manga_setting->get_manga_option( 'hosting_selection', true );

			$hosting_anonymous_name = $wp_manga_setting->get_manga_option( 'hosting_anonymous_name', true );

			$s_host            = isset( $_GET['host'] ) && $hosting_selection ? $_GET['host'] : null;
			
			global $wp_manga_volume, $wp_manga_storage, $is_amp_required;
			
			// if in AMP mode, reading style select box is disabled by default
			if(!isset($is_amp_required) || !$is_amp_required){
				$enable_reading_style = $wp_manga_setting->get_manga_option( 'reading_style_selection', true );
			} else {
				$enable_reading_style = false;
			}
			
			$all_vols = $wp_manga_volume->get_manga_volumes( $manga_id );
			$cur_vol  = get_query_var( 'volume' );

			$using_ajax = function_exists( 'madara_page_reading_ajax' ) && madara_page_reading_ajax();
			
			$this_vol_all_chaps = $all_chaps;
			$cur_vol_index      = null;
			$prev_vol_all_chaps = null;
			$next_vol_all_chaps = null;
			$volume_id = $chapter['volume_id'];
            
            global $wp_manga_database;							
            $sort_setting = $wp_manga_database->get_sort_setting();

            $sort_by    = $sort_setting['sortBy'];
            $sort_order = $sort_setting['sort'];            
            $vol_order = $sort_setting['vol_order'];// smallest, oldest Vol on top (1 - desc) || biggest, latest Vol on top (0 - asc)
            
			?>
            <div class="wp-manga-nav">
                <div class="select-view">
					<?php
						if ( $hosting_selection ) { ?>
                            <!-- select host -->
                            <div class="c-selectpicker selectpicker_version">
                                <label>
									<?php
										$host_arr = $wp_manga_functions->get_chapter_hosts( $manga_id, $chapter['chapter_id'] );
                                        if($host_arr){
									?>
                                    <select class="selectpicker host-select">
										<?php

											if ( $s_host ) {
												$inUse = $s_host;
											}

											$idx = 1;

											foreach ( $host_arr as $h ) {
												$host_link = $wp_manga_functions->build_chapter_url( $manga_id, $cur_chap, $style, $h );
												?>
                                                <option class="short" data-limit="40" value="<?php echo $h ?>" data-redirect="<?php echo esc_url( $host_link ); ?>" <?php selected( $h, $inUse, true ) ?>><?php echo !$hosting_anonymous_name ? sprintf(__("Host: %s",WP_MANGA_TEXTDOMAIN),$h) : sprintf(__("Server %s", WP_MANGA_TEXTDOMAIN), $idx); ?></option>
											<?php
												$idx++;
											}
										?>
                                    </select> 
                                        <?php } ?>
                                </label>
                            </div>
						<?php }
					?>

                    <!-- select volume -->
					<?php
					
						if ( ! empty( $all_vols ) ) {
                            
                            if($vol_order == 'asc') {
                                $all_vols = array_reverse( $all_vols );
                            }
                            
							if(isset($is_amp_required) && $is_amp_required){
								// not show volumes select on AMP page
							} else {
							?>
                            <div class="c-selectpicker selectpicker_volume">
                                <label> 
									<select class="selectpicker volume-select" data-order="<?php echo esc_attr($vol_order == 'desc' ? 1 : 0);?>">
										<?php 
											$cur_vol_id = $all_vols[0]['volume_id'];
											
											foreach ( $all_vols as $vol ) {
												$vol_slug = isset($vol['volume_name']) ? $wp_manga_storage->slugify( $vol['volume_name'] ) : 'no-volume';
												if ( $vol_slug == $cur_vol ) {
													$cur_vol_id = $vol['volume_id'];
												}
												
												if($vol_slug == 'no-volume') $cur_vol_id = 0;
											?>
                                            <option class="short" data-limit="40" value="<?php echo $vol['volume_id']; ?>" <?php selected( $vol['volume_id'], $cur_vol_id, true ) ?>>
												<?php echo isset($vol['volume_name']) ? esc_html( $vol['volume_name'] ) : esc_html__('No Volume', WP_MANGA_TEXTDOMAIN); ?>
                                            </option>
										<?php } ?>
                                    </select> 
								</label>
                            </div>
							<?php
							}
						}
					?>
					
                    <!-- select chapter -->
					<?php
					
					
                    // search for current vol index
					foreach ( $all_vols as $index => $vol ) {
						if ( $vol['volume_id'] == $volume_id ) {
							$cur_vol_index = $index;  
                            
                            break;
						}
                    }
                    
                    // find next & prev links	
                    foreach ( $all_vols as $index => $vol ) {
                        if($vol['volume_id'] != $volume_id){
							// Get next all chaps of next volume                           
							if ( $cur_vol_index !== null && (($vol_order == 'desc' && $index == ( $cur_vol_index + 1 )) || ($vol_order == 'asc' && $index == ( $cur_vol_index - 1 ))) ) {
                                $next_vol_all_chaps = $wp_manga_volume->get_volume_chapters( $manga_id, $vol['volume_id'], $sort_by, $sort_order );
							}
                            
                            // Get next all chaps of prev volume                           
							if ( $cur_vol_index !== null && (($vol_order == 'desc' && $index == ( $cur_vol_index - 1 )) || ($vol_order == 'asc' && $index == ( $cur_vol_index + 1 ))) ) {
                                $prev_vol_all_chaps = $wp_manga_volume->get_volume_chapters( $manga_id, $vol['volume_id'], $sort_by, $sort_order );
							}
						}
					}
					
					do_action('wp-manga-reading-chapters-selectbox', $chapter, $this_vol_all_chaps, 'manga');
                    
					if($enable_reading_style){?>
                    <!-- select page style -->
                    <div class="c-selectpicker selectpicker_load">
						<?php
							$list_link = $wp_manga_functions->build_chapter_url( $manga_id, $cur_chap, 'list', $s_host );

							$paged_link = $wp_manga_functions->build_chapter_url( $manga_id, $cur_chap, 'paged', $s_host );
						?>
                        <label> <select class="selectpicker reading-style-select">
                                <option data-redirect="<?php echo esc_url( $list_link ); ?>" <?php selected( 'list', $style ); ?>><?php esc_html_e( 'List style', WP_MANGA_TEXTDOMAIN ); ?></option>
                                <option data-redirect="<?php echo esc_url( $paged_link ); ?>" <?php selected( 'paged', $style ); ?>><?php esc_html_e( 'Paged style', WP_MANGA_TEXTDOMAIN ); ?></option>
                            </select> </label>
                    </div>
					<?php }?>
                </div>
				<?php
					if ( 'paged' == $style ) {
						$current_page = get_query_var($wp_manga->manga_paged_var) ? get_query_var($wp_manga->manga_paged_var) : (isset($_GET[$wp_manga->manga_paged_var]) ? $_GET[$wp_manga->manga_paged_var] : 1);
						$total_page   = isset( $single_chap['total_page'] ) ? $single_chap['total_page'] : '';
						$this->manga_pager( $current_page, $single_chap['total_page'], $style, $this_vol_all_chaps, $prev_vol_all_chaps, $next_vol_all_chaps, isset($asc) ? $asc : true );
					} elseif ( $style == 'list' ) {
						$this->manga_list_navigation( $cur_chap, $this_vol_all_chaps, $prev_vol_all_chaps, $next_vol_all_chaps, isset($asc) ? $asc : true);
					}
				?>
            </div>

			<?php
		}
		
		/**
		 * Print out Chapters selectbox used for Reading page
		 * @params $all_chaps - array - All chapters in current volume
		 **/
		function reading_chapters_nav($manga_id, $all_chaps, $vol_id, $current_chap_slug, $reading_style){
			global $wp_manga_database, $is_amp_required, $wp_manga_functions, $wp_manga_volume, $wp_manga_template, $wp_manga_chapter, $wp_manga, $wp_manga_setting, $wp_manga_chapter_type;
			
			$all_vols = $wp_manga_volume->get_manga_volumes( $manga_id );
					
			$using_ajax = function_exists( 'madara_page_reading_ajax' ) && madara_page_reading_ajax();
					
			$sort_setting = $wp_manga_database->get_sort_setting();

			$sort_by    = $sort_setting['sortBy'];
			$sort_order = $sort_setting['sort'];
            $vol_order = $sort_setting['vol_order'];
			
			$col = 'volume_id';
			if ( ! in_array( $vol_id, array_map(function($element) use($col ){return $element[$col ];}, $all_vols) )) {
				array_push( $all_vols, array(
					'volume_id' => $vol_id
				) );
			}
			
			$this_vol_all_chaps = $all_chaps;
			$cur_vol_index      = null;
			$prev_vol_all_chaps = null;
			$next_vol_all_chaps = null;
			
			if(isset($is_amp_required) && $is_amp_required){ ?>
				<select class="selectpicker single-chapter-select" on="change:AMP.navigateTo(url=event.value)">
			<?php
			}
            
            if($vol_order == 'asc'){
                $all_vols = array_reverse($all_vols);
            }
			
			foreach ( $all_vols as $index => $vol ) {
				if ( $vol['volume_id'] == $vol_id ) {
					if ( $index !== 0 ) {
						// If this is current volume, then the old $all_chaps will be $prev_vol_all_chaps
						$prev_vol_all_chaps = $all_chaps;
					}

					$all_chaps     = $this_vol_all_chaps;
					$cur_vol_index = $index;
				} else {
					global $wp_manga_database;

					$all_chaps = $wp_manga_volume->get_volume_chapters( $manga_id, $vol['volume_id'], $sort_by, $sort_order );

					// Get next all chaps of next volume
					if ( $cur_vol_index !== null && $index == ( $cur_vol_index + 1 ) ) {
						$next_vol_all_chaps = $all_chaps;
					}
				}
				
				if ( empty( $all_chaps ) ) {
					continue;
				}

				$is_current_vol = $vol_id == $vol['volume_id'] ? true : false;
				
				$html_class = 'class="c-selectpicker selectpicker_chapter"' . (! $is_current_vol ? ' style="display:none;"' : '');
				
				$current_link            = $wp_manga_functions->build_chapter_url( $manga_id, $current_chap_slug, $reading_style );
				
				if(isset($is_amp_required) && $is_amp_required){
				?>
				<optgroup label="<?php echo isset($vol['volume_name']) ? esc_attr($vol['volume_name']) : esc_html__('No Volume', WP_MANGA_TEXTDOMAIN);?>">
				<?php } else { ?>
				<div <?php echo $html_class;?> for="volume-id-<?php echo esc_attr( $vol['volume_id'] ); ?>">
					<label> 
						<select class="selectpicker single-chapter-select" <?php echo (isset($is_amp_required) && $is_amp_required) ? 'on="change:AMP.navigateTo(url=event.value)"' : '';?>>
							<?php if ( ! $is_current_vol ) { ?>
								<option value="<?php echo (isset($is_amp_required) && $is_amp_required) ? esc_url( $current_link ) : ''; ?>"><?php esc_html_e( 'Select Chapter', WP_MANGA_TEXTDOMAIN ); ?></option>
							<?php } ?>
				<?php } ?>
							<?php
								foreach ( $all_chaps as $chap ) {
									$link            = $wp_manga_functions->build_chapter_url( $manga_id, $chap, $reading_style );
									$data_navigation = $using_ajax ? $wp_manga_chapter_type->chapter_navigate_ajax_params( $manga_id, $chap['chapter_slug'], 1 ) : '';
									?>
									<option class="short <?php echo apply_filters('wp_manga_chapter_select_option_class', '', $chap, $manga_id);?>" data-limit="40" value="<?php echo (isset($is_amp_required) && $is_amp_required) ? esc_url( $link ) : $chap['chapter_slug']; ?>" data-redirect="<?php echo esc_url( $link ) ?>" data-navigation="<?php echo $data_navigation; ?>" <?php selected( $chap['chapter_slug'], $current_chap_slug, true ) ?>>
										<?php echo esc_attr( $chap['chapter_name'] . $wp_manga_functions->filter_extend_name( $chap['chapter_name_extend'] ) ); ?>
									</option>
								<?php }
							?>
			<?php if(isset($is_amp_required) && $is_amp_required){ ?>
				</optgroup>
			<?php } else { ?>
						</select> 
					</label>
				</div>
			<?php } ?>
			<?php } ?>
			<?php 
			if(isset($is_amp_required) && $is_amp_required){ ?>
				</select>
			<?php }
		}

		/**
         * @params - $cur_chap - string - Current Chapter Slug
         * @params - $current_vol_all_chaps - array - chapters of current volume
         * @params - $prev_vol_all_chaps & $next_vol_all_chaps - contain list of chapters of prev and next volume. These lists are "well-prepared" regardless of $asc value
		 * @params - $asc - boolean - Order Ascending (true) or Descending (false)
         * 
		 **/
		function manga_list_navigation( $cur_chap, $current_vol_all_chaps, $prev_vol_all_chaps, $next_vol_all_chaps, $asc = true ) {

			global $wp_manga_functions;

			$page_style = 'list';
			$col = 'chapter_slug';
            
            $cur_chap_index = array_search( $cur_chap, array_map(function($element) use($col ){return $element[$col ];}, $current_vol_all_chaps) );
            
			if ( isset( $current_vol_all_chaps[ ($asc ? ($cur_chap_index - 1) : ($cur_chap_index + 1)) ] ) ) {
				$prev_chap = $current_vol_all_chaps[ ($asc ? ($cur_chap_index - 1) : ($cur_chap_index + 1)) ];
			} else if ( ! empty( $prev_vol_all_chaps ) ) {
				$prev_chap = $prev_vol_all_chaps[ ($asc ? count( $prev_vol_all_chaps ) - 1 : 0) ];
			} else {
				$prev_chap = null;
			}

			if ( isset( $current_vol_all_chaps[ ($asc ? ($cur_chap_index + 1) : ($cur_chap_index - 1)) ] ) ) {
				$next_chap = $current_vol_all_chaps[ ($asc ? ($cur_chap_index + 1) : ($cur_chap_index - 1)) ];
			} elseif ( ! empty( $next_vol_all_chaps ) ) {
				$next_chap = $next_vol_all_chaps[ ($asc ? 0 : count($next_vol_all_chaps) - 1)];
			} else {
				$next_chap = null;
			}

			$manga_id = get_the_ID();
			
			?>
            <div class="select-pagination">
                <div class="nav-links">
					<i class="mobile-nav-btn icon ion-md-menu"></i>
					
					<?php if ( $prev_chap ): ?><?php $prev_link = $wp_manga_functions->build_chapter_url( get_the_ID(), $prev_chap, $page_style ); ?>
                        <div class="nav-previous <?php echo apply_filters('wp_manga_chapter_nagivation_button_class', '', $prev_chap, $manga_id);?>">
							<?php

							echo apply_filters('wp_manga_prev_chapter_link_html', '<a href="' . $prev_link . '"  class="btn prev_page">' . esc_html__( 'Prev', WP_MANGA_TEXTDOMAIN ) . '</a>', $prev_link, $prev_chap, $manga_id);
							?>
                        </div>
					<?php endif ?>
					<?php 
					if ( $next_chap ): ?><?php $next_link = $wp_manga_functions->build_chapter_url( $manga_id, $next_chap, $page_style ); ?>
                        <div class="nav-next <?php echo apply_filters('wp_manga_chapter_nagivation_button_class', '', $next_chap, $manga_id);?>">
						<?php

						echo apply_filters('wp_manga_next_chapter_link_html', '<a href="' . $next_link . '"  class="btn next_page">' . esc_html__( 'Next', WP_MANGA_TEXTDOMAIN ) . '</a>', $next_link, $next_chap, $manga_id);
						?>
                        </div>
					<?php else:

					// back to manga info page 
					global $wp_manga_setting;
					$back_to_info_page = $wp_manga_setting->get_manga_option( 'navigation_manga_info', true );
					
					if($back_to_info_page){
					?>
					<div class="nav-next">
						<a href="<?php echo esc_url(get_permalink(get_the_ID()) . ((isset($is_amp_required) && $is_amp_required) ? 'amp' : ''));?>" class="btn back" >
							<?php esc_html_e( 'Manga Info', WP_MANGA_TEXTDOMAIN ); ?>
						</a>
					</div>
					<?php
					}
					
					endif;?>
                </div>
            </div>
			<?php

			//put prev and next link to global variable, so other function from other place can get it
			if ( ! empty( $next_link ) ) {
				$GLOBALS['madara_next_page_link'] = $next_link;
			}
			if ( ! empty( $prev_link ) ) {
				$GLOBALS['madara_prev_page_link'] = $prev_link;
			}

		}

		function manga_pager(
			$cur_page,
			$total_page,
			$style,
			$current_vol_all_chaps,
			$prev_vol_all_chaps = null,
			$next_vol_all_chaps = null, $asc = true
		) {

			global $wp_manga_functions, $wp_manga;

			$cur_host   = isset( $_GET['host'] ) ? $_GET['host'] : null;
			
			$reading_chapter = madara_permalink_reading_chapter();
			if($reading_chapter){
				$cur_chap = $reading_chapter['chapter_slug'];
			}
			
			$manga_id = get_the_ID();
			
			$link       = remove_query_arg('style', $wp_manga_functions->build_chapter_url( $manga_id, $cur_chap, $style, $cur_host ));
			if($pos = strrpos($link, '/p/')){
				// trim paged param
				$link = substr($link, 0, $pos);
			}
			
			$using_ajax = function_exists( 'madara_page_reading_ajax' ) && madara_page_reading_ajax();

			//get prev and next chap url
			$col = 'chapter_slug';
			$cur_chap_index = array_search( $cur_chap, array_map(function($element) use($col ){return $element[$col ];}, $current_vol_all_chaps) );
			
            
			// Next Chap
			if ( isset( $current_vol_all_chaps[ $asc ? $cur_chap_index + 1 : $cur_chap_index - 1 ] ) ) {
                
				$next_chap = $current_vol_all_chaps[ $asc ? $cur_chap_index + 1 : $cur_chap_index - 1 ];
			} else if ( ! empty( $next_vol_all_chaps ) && is_array( $next_vol_all_chaps ) ) {
                
                if($asc){
                    $next_chap = $next_vol_all_chaps[ 0 ];
                } else {
                    $next_chap = $next_vol_all_chaps[ count($next_vol_all_chaps) - 1 ];
                }
			}

			if ( ! empty( $next_chap ) ) {
				$next_chap_link = remove_query_arg('style', $wp_manga_functions->build_chapter_url( $manga_id, $next_chap['chapter_slug'], $style, $cur_host ));				
			}

			// Prev Chap
			if ( isset( $current_vol_all_chaps[ $asc ? $cur_chap_index - 1 : $cur_chap_index + 1 ] ) ) { // If there is prev chap in current volume
				$prev_chap = $current_vol_all_chaps[ $asc ? $cur_chap_index - 1 : $cur_chap_index + 1 ];
			} elseif ( ! empty( $prev_vol_all_chaps ) && is_array( $prev_vol_all_chaps ) ) { // or get the latest chap of prev volume
                if($asc){
                    $prev_chap = $prev_vol_all_chaps[ count( $prev_vol_all_chaps ) - 1 ];
                } else {
                    $prev_chap = $prev_vol_all_chaps[ 0 ];
                }
			}

			if ( ! empty( $prev_chap ) ) {
				$prev_chap_link = remove_query_arg('style', $wp_manga_functions->build_chapter_url( $manga_id, $prev_chap['chapter_slug'], $style, $cur_host ));
				
				if($pos = strrpos($prev_chap_link, '/p/')){
					// trim paged param
					$prev_chap_link = substr($prev_chap_link, 0, $pos);
				}

				// use for get the last page of previous chapter
				$prev_chap_data = $wp_manga_functions->get_single_chapter( $manga_id, $prev_chap['chapter_id'] );

				if ( ! empty( $prev_chap_data ) ) {
					$prev_chap_last_page = madara_actual_total_pages( $prev_chap_data['total_page'] );
				}
			}

			$prev_page = intval( $cur_page ) - 1;
			
			if ( $prev_page != 0 ) {
				$prev_link = trim($link , '/'). '/p/' . $prev_page . '/';

				if ( $using_ajax ) {
					$prev_ajax_params = $this->chapter_navigate_ajax_params( $manga_id, $cur_chap, $prev_page );
				}
				
				$prev_chap_name = $reading_chapter['chapter_name'];
			} else {
				if ( isset( $prev_chap_link ) && isset( $prev_chap ) && isset( $prev_chap_last_page ) ) {
					$prev_link = trim($prev_chap_link, '/') . '/p/' . $prev_chap_last_page; //add_query_arg( array( $wp_manga->manga_paged_var => $prev_chap_last_page ), $prev_chap_link );
					$prev_chap_name = $prev_chap['chapter_name'];
				}

				if ( $using_ajax && ! empty( $prev_chap ) && isset( $prev_chap_last_page ) ) {
					$prev_ajax_params = $this->chapter_navigate_ajax_params( $manga_id, $prev_chap['chapter_slug'], $prev_chap_last_page );
				}
			}

			$total_page = apply_filters('madara_actual_total_pages', $total_page, $manga_id, $reading_chapter ); // In Madara theme ???

			$next_page = intval( $cur_page ) + 1;

			if ( intval( $next_page ) <= intval( $total_page ) ) {
				// change to pretty link since 1.5.2.2
				$next_link = trim($link, '/') . '/p/' . $next_page . '/';// add_query_arg( array( $wp_manga->manga_paged_var => $next_page ), $link );

				if ( $using_ajax ) {
					$next_ajax_params = $this->chapter_navigate_ajax_params( $manga_id, $cur_chap, $next_page );
				}
				
				$next_chap_name = $reading_chapter['chapter_name'];
			} else {
				if ( isset( $next_chap_link ) ) {
					$next_link = trim($next_chap_link, '/') . (!strrpos($next_chap_link, '/p/1') ? '/p/1/' : '');
				}

				if ( $using_ajax && ! empty( $next_chap ) ) {
					$next_ajax_params = $this->chapter_navigate_ajax_params( $manga_id, $next_chap['chapter_slug'], 1 );
					
					$next_chap_name = $next_chap['chapter_name'];
				}
			}

			if ( $using_ajax ) {

				$params = array(
					'chapter' => $cur_chap,
					'postID'  => $manga_id
				);

				if ( isset( $prev_chap_link ) ) {
					$params['prev_chap_url'] = $prev_chap_link;
				}

				if ( isset( $next_chap_link ) ) {
					$params['next_chap_url'] = trim($next_chap_link, '/') . '/p/1/';
				}
			}

			if ( ! empty( $prev_chap_last_page ) ) { //for preload navigation ?>
                <script type="text/javascript">
					var prevChapLastPage = <?php echo intval( $prev_chap_last_page ); ?>;
                </script>
			<?php } ?>

            <div class="select-pagination">
                <div class="c-selectpicker selectpicker_page">
                    <label>

                        <select id="single-pager" class="selectpicker">
							<?php for ( $i = 1; $i <= intval( $total_page ); $i ++ ) { ?>

								<?php
								$data_redirect   = 'data-redirect="' . trim($link,'/') . '/p/' . $i . '/"';
								$data_navigation = $using_ajax ? 'data-navigation="' . $this->chapter_navigate_ajax_params( $manga_id, $cur_chap, $i ) . '"' : '';
								?>
                                <option value="<?php echo $i ?>" <?php echo $data_redirect; ?> <?php echo $data_navigation; ?> <?php selected( $i, $cur_page, true ) ?>>
									<?php echo $i . '/' . $total_page; ?>
                                </option>
							<?php } ?>
                        </select>

                    </label>
                </div>
                <div class="nav-links">

                    <i class="mobile-nav-btn icon ion-md-menu"></i>
					
					<?php if ( ! empty( $prev_link ) ): ?>
                        <div class="nav-previous <?php echo apply_filters('wp_manga_chapter_nagivation_button_class', '', isset( $prev_chap ) ? $prev_chap : null, $manga_id, $prev_link);?>">
						<?php

						echo apply_filters('wp_manga_prev_chapter_link_html', '<a href="' . esc_url($prev_link) . '" class="btn prev_page" ' . (isset( $prev_ajax_params ) ? 'data-navigation="' . esc_attr( $prev_ajax_params ) . '"' : '') . 'title="' . (isset( $prev_chap_name ) ? $prev_chap_name : '') . '">' . esc_html__( 'Prev', WP_MANGA_TEXTDOMAIN ) . '</a>', $prev_link, $prev_chap, $manga_id);
						?>
                            
                        </div>
					<?php else : ?>
                        <div class="nav-previous">
                            <a href="" style="display: none;" class="btn prev_page" <?php echo isset( $prev_ajax_params ) ? 'data-navigation="' . esc_attr( $prev_ajax_params ) . '"' : ''; ?>>
								<?php esc_html_e( 'Prev', WP_MANGA_TEXTDOMAIN ); ?>
                            </a>
                        </div>
							
					<?php endif ?>

					<?php 
					
					if ( ! empty( $next_link ) ): ?>
                        <div class="nav-next <?php echo apply_filters('wp_manga_chapter_nagivation_button_class', '', isset( $next_chap ) ? $next_chap : null, $manga_id, $next_link);?>">
						<?php

						echo apply_filters('wp_manga_next_chapter_link_html', '<a href="' . esc_url($next_link) . '" class="btn next_page" data-navigation="' . (isset( $next_ajax_params ) ? esc_attr( $next_ajax_params ) : '') . '" data-order="' . ($asc ? 0 : 1) . '" title="' . (isset( $next_chap_name ) ? $next_chap_name : '') . '">' . esc_html__( 'Next', WP_MANGA_TEXTDOMAIN ) . '</a>', $next_link, $next_chap, $manga_id);
						?>
                            
                        </div>
					<?php else :
							// this hidden button is used for front-end script to control visibility
					?>
                        <div class="nav-next">
                            <a href="" style="display: none;" class="btn next_page" data-navigation="<?php echo isset( $next_ajax_params ) ? esc_attr( $next_ajax_params ) : ''; ?>" data-order="<?php echo ($asc ? 0 : 1);?>">
								<?php esc_html_e( 'Next', WP_MANGA_TEXTDOMAIN ); ?>
                            </a>
                        </div>
					<?php
					endif;
					
					// back to manga info page 
					global $wp_manga_setting;
					$back_to_info_page = $wp_manga_setting->get_manga_option( 'navigation_manga_info', 2 );
					
					if($back_to_info_page == 2 || ($back_to_info_page == 1 && !isset( $next_link ) )) {?>					
						<div class="btn-primary">
                            <a href="<?php echo esc_url(get_permalink($manga_id));?>" class="btn back" >
								<?php esc_html_e( 'Manga Info', WP_MANGA_TEXTDOMAIN ); ?>
								
                            </a>
                        </div>
						<?php
						} 
					?>
                </div>
            </div>
			<?php

			//put prev and next link to global variable, so other function from other place can get it
			if ( ! empty( $next_link ) ) {
				$GLOBALS['madara_next_page_link'] = $next_link;
			}
			if ( ! empty( $prev_link ) ) {
				$GLOBALS['madara_prev_page_link'] = $prev_link;
			}

		}

		function chapter_navigate_ajax_params( $post_id, $chap, $paged ) {
			global $wp_manga;

			$params = array(
				'postID'      => $post_id,
				'chapter'     => $chap,
				$wp_manga->manga_paged_var => $paged,
				'style'       => 'paged',
				'action'      => 'chapter_navigate_page'
			);

			return http_build_query( $params );
		}

	}

	$GLOBALS['wp_manga_chapter_type'] = new WP_MANGA_CHAPTER();
