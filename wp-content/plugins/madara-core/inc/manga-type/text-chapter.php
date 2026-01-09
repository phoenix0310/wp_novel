<?php

    /**
 	 * Text Chapter for WP Manga
 	 **/

    class WP_MANGA_TEXT_CHAPTER{

        function manga_nav( $args ){

            global $wp_manga_template, $wp_manga, $wp_manga_functions, $wp_manga_setting, $is_amp_required;
			
			$default_video_server = $wp_manga_setting->get_manga_option( 'default_video_server', '' );
			
			// init
			$prev_chapter = $next_chapter = null;
			
            extract( $args );
			
			$html = '';
			ob_start();

            ?>

            <div class="wp-manga-nav">
                <div class="select-view">
					
					<?php
						$servers = $wp_manga->get_chapter_video_server_list($this->get_chapter_content_post($chapter['chapter_id']));
						
						if(count($servers) > 1) { ?>
                            <!-- select host -->
                            <div class="c-selectpicker selectpicker_version">
                                <label>
                                    <select class="selectpicker host-select">
										<?php
											foreach($servers as $server){
												?>
												<option data-redirect="<?php echo add_query_arg('host',$server,$_SERVER['REQUEST_URI']);?>" value="<?php echo $server;?>" <?php selected( $server, isset($_GET['host']) ? $_GET['host'] : $default_video_server, true );?>><?php echo $server;?></option>
												<?php
											}
										?>
                                    </select> </label>
                            </div>
						<?php }
					?>
					
					<!-- select volume -->
					<?php
					global $wp_manga_volume, $wp_manga_storage;
					$all_vols = $wp_manga_volume->get_manga_volumes( $manga_id );
					$cur_vol  = get_query_var( 'volume' );
			
					if ( ! empty( $all_vols ) ) {
						$all_vols = array_reverse( $all_vols );
						if(isset($is_amp_required) && $is_amp_required){
							// not show volumes select on AMP page
						} else {
						?>
						<div class="c-selectpicker selectpicker_volume">
							<label> 
								<select class="selectpicker volume-select">
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
					do_action('wp-manga-reading-chapters-selectbox', $chapter, $all_chaps, 'content'); ?>
                    
					<?php 
					$chapter_type = get_post_meta( $chapter['post_id'], '_wp_manga_chapter_type', true );
					
					if($chapter_type == 'video') {?>
					<a href="#chapter-video-frame" class="video-light" data-lity><i class="fas fa-lightbulb"></i> <span class="text text-off"><?php esc_html_e('Light off',WP_MANGA_TEXTDOMAIN);?></span> <span class="text text-on"><?php esc_html_e('Light on',WP_MANGA_TEXTDOMAIN);?></span></a>
					
					<?php } ?>
                </div>

                <div class="select-pagination">
                    <div class="nav-links">
						<?php
						
						foreach ( $all_chaps as $chap ) {
							if( isset( $cur_chap_passed ) && !isset( $next_chap ) ){
								$next_chap = $wp_manga_functions->build_chapter_url( $manga_id, $chap );
								$next_chapter = $chap;
							}

							if( $chap['chapter_slug'] == $cur_chap ){
								$cur_chap_passed = true;
								$cur_chap_link = $wp_manga_functions->build_chapter_url( $manga_id, $chap );
							}

							//always set current chap in loop as $prev_chap, stop once current chap is passed
							if( !isset( $cur_chap_passed ) ){
								$prev_chap = $wp_manga_functions->build_chapter_url( $manga_id, $chap );
								$prev_chapter = $chap;
							}
						}
								
						// check if there is next volume
						//global $wp_manga_volume;
						//$all_vols = $wp_manga_volume->get_manga_volumes( $chapter['post_id'] );
						//$all_vols = array_reverse($all_vols);
						
						if(!isset($prev_chap)){
							$prev_vol = false;
							
							if($all_vols && is_array($all_vols)){
								foreach($all_vols as $idx => $vol){
									
									if($vol['volume_id'] == $chapter['volume_id']){
										if(isset($all_vols[$idx + 1])){
											$prev_vol = $all_vols[$idx + 1];
											break;
										}
									}
								}
								
								if($prev_vol){
									$chapters = $wp_manga_volume->get_volume_chapters($chapter['post_id'], $prev_vol['volume_id']);
									if(count($chapters) > 0){
										$prev_chapter = ((isset($asc) && !$asc) ? $chapters[count($chapters) - 1] : $chapters[0]);
										$prev_chap = $wp_manga_functions->build_chapter_url( $chapter['post_id'], $prev_chapter );
									}
								}
							}
						}
						
						if(!isset($next_chap)){
							$next_vol = false;
							
							if($all_vols && is_array($all_vols)){
								foreach($all_vols as $idx => $vol){
									
									if($vol['volume_id'] == $chapter['volume_id']){
										if($idx >= 1){
											$next_vol = $all_vols[$idx - 1];
											break;
										}
									}
								}
								
								
								
								if($next_vol){									
									$chapters = $wp_manga_volume->get_volume_chapters($chapter['post_id'], $next_vol['volume_id']);
									
									if(count($chapters) > 0){
										$next_chapter = ((isset($asc) && !$asc) ? $chapters[0] : $chapters[count($chapters) - 1]);
										$next_chap = $wp_manga_functions->build_chapter_url( $chapter['post_id'], $next_chapter );
									}
								}
							}
						}
						
						if(isset($asc) && !$asc){
							// swap the link
							$temp = isset( $prev_chap ) ? $prev_chap : null;
							$prev_chap = isset( $next_chap ) ? $next_chap : null;
							$next_chap = $temp;
							
							// swap the object
							$temp = $prev_chapter;
							$prev_chapter = $next_chapter;
							$next_chapter = $temp;
						}
						
						if ( isset( $prev_chap ) && $prev_chap !== $cur_chap_link ): ?>
                            <div class="nav-previous <?php echo apply_filters('wp_manga_chapter_nagivation_button_class', '', $prev_chapter, $chapter['post_id'], $prev_chap);?>"><a href="<?php echo $prev_chap; ?>" class="btn prev_page" title="<?php echo $prev_chapter['chapter_name'];?>"><?php esc_html_e('Prev', WP_MANGA_TEXTDOMAIN ); ?></a>
                            </div>
                        <?php endif;
						
						if ( isset( $next_chap ) ): ?>
                            <div class="nav-next <?php echo apply_filters('wp_manga_chapter_nagivation_button_class', '', $next_chapter, $chapter['post_id'], $next_chap);?>"><a href="<?php echo $next_chap ?>" class="btn next_page" title="<?php echo $next_chapter['chapter_name'];?>"><?php esc_html_e('Next', WP_MANGA_TEXTDOMAIN ); ?></a></div>
                        <?php 
											
						endif;
						
						// back to manga info page 
						global $wp_manga_setting;
						$back_to_info_page = $wp_manga_setting->get_manga_option( 'navigation_manga_info', 2 );

						if($back_to_info_page == 2 || ($back_to_info_page == 1 && !isset( $next_chap ) )) {?>
						<div class="btn-primary">
							<a href="<?php echo esc_url(get_permalink($chapter['post_id']) . ((isset($is_amp_required) && $is_amp_required) ? 'amp' : ''));?>" class="btn back" >
								<?php esc_html_e( 'Novel Info', WP_MANGA_TEXTDOMAIN ); ?>
							</a>
						</div>	
						<?php }?>
                    </div>
                </div>

            </div>

            <?php
			
			$html = ob_get_contents();
			ob_end_clean();
			
			$html = apply_filters('wp_manga_text_chapter_nav', $html, $args);
			
			echo $html;
        }
		
		/**
		 * Print out Chapters selectbox used for Reading page
		 **/
		function reading_chapters_nav($manga_id, $all_chaps, $volume_id, $current_chap_slug){
			global $is_amp_required, $wp_manga_functions, $wp_manga_volume, $wp_manga_database;
			$all_vols = $wp_manga_volume->get_manga_volumes( $manga_id );
			
			$col = 'volume_id';
			if ( ! in_array( $volume_id, array_map(function($element) use($col ){return $element[$col ];}, $all_vols) )) {
				array_push( $all_vols, array(
					'volume_id' => $volume_id
				) );
			}
			
					
			$this_vol_all_chaps = $all_chaps;
			$cur_vol_index      = null;
			$prev_vol_all_chaps = null;
			$next_vol_all_chaps = null;
			
			$sort_setting = $wp_manga_database->get_sort_setting();

			$sort_by    = $sort_setting['sortBy'];
			$sort_order = $sort_setting['sort'];
			
			?>
			<label>
				<?php foreach ( $all_vols as $index => $vol ) {

					if ( $vol['volume_id'] == $volume_id ) {
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

					$is_current_vol = $volume_id == $vol['volume_id'] ? true : false;
					$html_class = 'c-selectpicker selectpicker_chapter selectpicker single-chapter-select';
					$current_link            = $wp_manga_functions->build_chapter_url( $manga_id, $current_chap_slug );
				?>
				<select class="<?php echo esc_attr($html_class);?>" style="<?php echo esc_attr(! $is_current_vol ? 'display:none;' : '');?>" for="volume-id-<?php echo esc_attr($vol['volume_id']);?>" <?php echo (isset($is_amp_required) && $is_amp_required) ? 'on="change:AMP.navigateTo(url=event.value)"' : '';?>>
					<?php if ( ! $is_current_vol ) { ?>
						<option value="<?php echo (isset($is_amp_required) && $is_amp_required) ? esc_url( $current_link ) : ''; ?>"><?php esc_html_e( 'Select Chapter', WP_MANGA_TEXTDOMAIN ); ?></option>
					<?php } ?>
					<?php
						
						foreach ( $all_chaps as $chap ) {

							$link = $wp_manga_functions->build_chapter_url( $manga_id, $chap );

							if( isset( $cur_chap_passed ) && !isset( $next_chap ) ){
								$next_chap = $link;
								$next_chapter = $chap;
							}

							if( $chap['chapter_slug'] == $current_chap_slug ){
								$cur_chap_passed = true;
								$cur_chap_link = $link;
							}

							//always set current chap in loop as $prev_chap, stop once current chap is passed
							if( !isset( $cur_chap_passed ) ){
								$prev_chap = $link;
								$prev_chapter = $chap;
							}

							?>
							<option class="short <?php echo apply_filters('wp_manga_chapter_select_option_class', '', $chap, $manga_id);?>" data-limit="40" value="<?php echo (isset($is_amp_required) && $is_amp_required) ? $link : $current_chap_slug; ?>" data-redirect="<?php echo esc_url( $link ) ?>" <?php selected( $chap['chapter_slug'], $current_chap_slug, true ) ?>><?php echo esc_attr( $chap['chapter_name'] . $wp_manga_functions->filter_extend_name( $chap['chapter_name_extend'] ) ); ?></option>

						<?php } ?>
				</select>
				<?php } ?>
			</label>
			<?php
		}

        /**
     	 * Get chapter_content post type which contains content for this chapter
    	 */
        public function get_chapter_content_post( $chapter_id ){

            $chapter_post_content = new WP_Query(
                array(
                    'post_parent' => $chapter_id,
                    'post_type'   => 'chapter_text_content'
                )
            );

            if( $chapter_post_content->have_posts() ){
                return $chapter_post_content->posts[0];
            }

            return false;

        }

        /**
        * Handle insert Chapter Content Type
        *
        * @return - int - Chapter ID
        */
        function insert_chapter( $args ){ 
			
            global $wp_manga_chapter, $wp_manga_storage;

            $chapter_content = $args['chapter_content'];
            unset( $args['chapter_content'] );

            //post_id require, volume id, chapter name, chapter extend name, chapter slug
    		$chapter_args = array_merge(
                $args,
                array(
                    'chapter_slug' => $wp_manga_storage->slugify( $args['chapter_name'] )
                )
            );
			
    		$chapter_id = $wp_manga_chapter->insert_chapter( $chapter_args );

    		if( ! $chapter_id ){
    			wp_send_json_error( array( 'message' => esc_html__('Cannot insert Chapter', WP_MANGA_TEXTDOMAIN ) ) );
    		}
			
    		$chapter_content_args = array(
    			'post_type'    => 'chapter_text_content',
    			'post_content' => $chapter_content,
    			'post_status'  => 'publish',
    			'post_parent'  => $chapter_id, //set chapter id as parent
				'post_title' => $chapter_id . '-' . $chapter_args['chapter_slug']
    		);
			
    		$resp = wp_insert_post( sanitize_post($chapter_content_args, 'db') );
			
            if( $resp ){
                if( is_wp_error( $resp ) ){
                    return $resp;
                } else {
					return $chapter_id;
                }
            } else {
                return new WP_Error( 'create_content_chapter_failed', __( 'Cannot create chapter', WP_MANGA_TEXTDOMAIN ) );
            }
        }

        function upload_handler( $post_id, $zip_file, $volume_id = 0 ){

            global $wp_manga_functions, $wp_manga, $wp_manga_storage, $wp_manga_volume;

    		$uniqid = $wp_manga->get_uniqid( $post_id );

    		$temp_name = $zip_file['tmp_name'];
    		$temp_dir_name = $wp_manga_storage->slugify( explode( '.', $zip_file['name'] )[0] );

    		//open zip
    		$zip_manga = new ZipArchive();

    		if( ! $zip_manga->open( $temp_name ) ) {
    			wp_send_json_error( __('Cannot open Zip file ', 'madara' ) );
    		}

            $extract = WP_MANGA_DATA_DIR . $uniqid . '/' . $temp_dir_name;

            $zip_manga->extractTo( $extract );
		    $zip_manga->close();
			
            //scan all dir
    		$scandir_lv1 = glob( $extract . '/*' );

			// sort by natural order of folder
			$folders = array_values($scandir_lv1);
			natsort(  $folders );
			
    		$result = array();

            $is_invalid_zip_file = true;
			$insert_result = array();

    		//Dir level 1
    		foreach( $folders as $dir_lv1 ) {

    			if( basename( $dir_lv1 ) === '__MACOSX' ){
    				continue;
    			}

    			if( is_dir( $dir_lv1 ) ) {

    				$has_volume = true;

                    foreach( glob( $dir_lv1 . '/*' ) as $dir_lv2 ) {

    					if( basename( $dir_lv2 ) === '__MACOSX' ){
    						continue;
    					}

    					//if dir level 2 is dir then dir level 1 is volume
    					if( is_dir( $dir_lv2 ) && $has_volume == true ) {

    						//By now, dir lv1 is volume. Then check if this volume is already existed or craete a new one
    						$this_volume = $wp_manga_volume->get_volumes(
    							array(
    								'post_id' => $post_id,
    								'volume_name' => basename( $dir_lv1 ),
    							)
    						);

    						if( $this_volume == false ){
    							$this_volume = $wp_manga_storage->create_volume( basename( $dir_lv1 ), $post_id );
    						}else{
    							$this_volume = $this_volume[0]['volume_id']; 
    						}

                            $chapters = glob( $dir_lv2 . '/*' );

                            foreach( $chapters as $chapter ){
								
                                //create chapter
								$chapter_content = wp_kses_post(file_get_contents( $chapter ));
								
								$chapter_name = basename( $dir_lv2 );
								$chapter_name_extend = '';
						
								$name_parts = explode('--',$chapter_name);
								if(count($name_parts) == 2){
									$chapter_name = trim($name_parts[0]);
									$chapter_name_extend = trim($name_parts[1]);
								}
								
								if($chapter_content != ''){
									$chapter_args = array(
										'post_id'             => $post_id,
										'chapter_name'        => $chapter_name,
										'chapter_name_extend' => $chapter_name_extend,
										'volume_id'           => $this_volume,
										'chapter_content'     => $chapter_content
									);

									$insert_result[$chapter_name] = $this->insert_chapter( $chapter_args );
                                    
                                    do_action('wp_manga_content_chapter_added', $insert_result[$chapter_name], $chapter_args);
								} else {
									$is_invalid_zip_file = false;
								}
                            }
    					}else{

                            if( $has_volume ){
                                $has_volume = false;
                            }
							
							$chapter_content = wp_kses_post(file_get_contents( $dir_lv2 ));
							
							if($chapter_content != ''){
								
								$chapter_name = basename( $dir_lv1 );
								$chapter_name_extend = '';
						
								$name_parts = explode('--',$chapter_name);
								if(count($name_parts) == 2){
									$chapter_name = trim($name_parts[0]);
									$chapter_name_extend = trim($name_parts[1]);
								}
								
								//create chapter
								$chapter_args = array(
									'post_id'             => $post_id,
									'chapter_name'        => $chapter_name,
									'chapter_name_extend' => $chapter_name_extend,
									'volume_id'           => $volume_id,
									'chapter_content'     => $chapter_content
								);
								
								$insert_result[$chapter_name] = $this->insert_chapter( $chapter_args );
                                
                                do_action('wp_manga_content_chapter_added', $insert_result[$chapter_name], $chapter_args);
							} else {
								$is_invalid_zip_file = false;
							}
                        }

    				}
    			} else {
                    $is_invalid_zip_file = false;
                }
    		}

    		$wp_manga_storage->local_remove_storage( $extract );

            if( !$is_invalid_zip_file ){
                return array(
                    'success' => false,
                    'message' => esc_html__('Upload failed', 'madara')
                );
            }
			
            $failed_chapters = array();
            foreach($insert_result as $chapter_name => $chapter_id){
                if(is_wp_error($chapter_id)){
                    $failed_chapters[] = $chapter_name;
                }   
            }
            
            if(count($failed_chapters) == 0){
                return array(
                    'success' => true,
                    'message' => esc_html__('Upload successfully', 'madara')
                );
            } else {
                $message = sprintf(esc_html__('The following chapters are failed to upload: %s (make sure the content is encoded in UTF-8', 'madara'), implode(", ", $failed_chapters));
                
                return array(
                    'success' => false,
                    'message' => $message
                );
            }
        }
    }

    $GLOBALS['wp_manga_text_type'] = new WP_MANGA_TEXT_CHAPTER();
