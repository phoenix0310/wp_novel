<?php

	class WP_MANGA_AJAX_BACKEND {

		public function __construct() {
            //save page setting in first install page
            add_action( 'wp_ajax_wp_manga_first_install_page_save', array( $this, 'wp_manga_first_install_page_save') );

            //save post type setting in first install page
            add_action( 'wp_ajax_wp_manga_first_install_post_save', array( $this, 'wp_manga_first_install_post_save') );

            add_action( 'wp_ajax_wp_manga_skip_first_install', array( $this, 'wp_manga_skip_first_install' ) );

            add_action( 'wp_ajax_wp_manga_archive_loadmore', array( $this, 'wp_manga_archive_loadmore' ) );

            add_action( 'wp_ajax_wp_manga_clean_temp_folder', array( $this, 'wp_manga_clean_temp_folder' ) );

            add_action( 'wp_ajax_replace_blogspot_url', array( $this, 'replace_blogspot_url' ) );

            add_action( 'wp_ajax_wp-manga-duplicate-server', array( $this, 'wp_manga_duplicate_server' ) );

            add_action( 'wp_ajax_wp-manga-remove-storage', array( $this, 'wp_manga_remove_storage' ) );

			// get manga
			add_action( 'wp_ajax_wp-manga-get-chapter', array( $this, 'wp_manga_get_chapter' ) );

			// save manga paging ( back-end )
			add_action( 'wp_ajax_wp-manga-save-chapter-paging', array( $this, 'wp_save_chapter' ) );

			// download manga chapter ( back-end )
			add_action( 'wp_ajax_wp-manga-download-chapter', array( $this, 'wp_manga_download_chapter' ) );

			// delete manga chapter ( back-end )
			add_action( 'wp_ajax_wp-manga-delete-chapter', array( $this, 'wp_manga_delete_chapter' ) );
            add_action( 'wp_ajax_wp-manga-empty-chapter', array( $this, 'wp_manga_empty_chapter' ) );

            add_action( 'wp_ajax_wp-manga-move-chapter', array( $this, 'wp_manga_move_chapters' ) );

			// create volume ( back-end )
			add_action( 'wp_ajax_wp-manga-create-volume', array( $this, 'wp_manga_create_volume' ) );

			//download manga
			add_action( 'wp_ajax_wp-download-manga', array( $this, 'wp_download_manga' ) );

			// Search chapter
			add_action( 'wp_ajax_search-chapter', array( $this, 'wp_manga_search_chapter' ) );

			add_action( 'wp_ajax_wp-update-chapters-list', array( $this, 'wp_update_chapters_list' ) );

			add_action( 'wp_ajax_update_picasa_album_dropdown', array( $this, 'update_picasa_album_dropdown' ) );

			//change volume name
			add_action( 'wp_ajax_update_volume_name', array( $this, 'update_volume_name' ) );

			//delete volume
			add_action( 'wp_ajax_wp_manga_delete_volume', array( $this, 'wp_manga_delete_volume' ) );

			add_action( 'wp_ajax_wp_manga_save_chapter_type', array( $this, 'wp_manga_save_chapter_type' ) );

			add_action( 'wp_ajax_chapter_content_upload', array( $this, 'chapter_content_upload' ) );

			// Search blogspot album by name
			add_action( 'wp_ajax_blogspot_search_album', array( $this, 'blogspot_search_album' ) );

			add_action('wp_ajax_wp_manga_save_volumes_order', array($this, 'wp_manga_save_volumes_order'));

			add_action('wp_ajax_wp-manga-chapter-upload-images', array($this, 'wp_manga_chapter_upload_images'));

			add_action('wp_ajax_wp_manga_rebuild_search_text', array( $this,'wp_manga_rebuild_search_text' ) );
		}

        /**
         * Rebuild Search Text for all mangas
         **/
        function wp_manga_rebuild_search_text(){

            $wp_manga_database = WP_MANGA_DATABASE::get_instance();;
            $wpdb = $wp_manga_database->get_wpdb();

            if(!$wp_manga_database->column_exists($wpdb->posts, 'wp_manga_search_text')){
                $sql = "ALTER TABLE `{$wpdb->prefix}posts` ADD `wp_manga_search_text` TEXT NULL,
                  CHANGE `post_date` `post_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  CHANGE `post_date_gmt` `post_date_gmt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  CHANGE `post_modified` `post_modified` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  CHANGE `post_modified_gmt` `post_modified_gmt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;";
                // Note: due to sample Madara data, default DATETIME value of date columes in _posts table has '00:00:00' value, which will prevent alter table. We need to update default value of these columns at the same time

                if(!$wp_manga_database->get_wpdb()->query($sql)){
                    wp_send_json_success(['message' => esc_html__('Alter database failed. Please contact plugin author for help!', WP_MANGA_TEXTDOMAIN), 'end' => 1]);
                }
            }

            // offset to process next
            $offset = isset($_POST['offset']) ? $_POST['offset'] : 0;

            $posts = get_posts(['post_type' => 'wp-manga', 'post_status' => 'all', 'posts_per_page' => 100, 'paged' => $offset]);

            $end = 0;

            if($posts){
                if(count($posts) < 100){
                    $end = 1;
                }

                global $wp_manga, $wp_manga_database;
                foreach($posts as $post){
                    $search_text = $wp_manga->build_search_text($post->ID);
                    $wp_manga_database->update_search_text($post->ID, $search_text);
                }
            } else {
                $end = 1;
            }

            if($end){
                // mark done once
                update_option('wp_manga_search_text_built', 1);
            }

            wp_send_json_success(['message' => esc_html__('Build search text done!', WP_MANGA_TEXTDOMAIN), 'end' => $end]);

            wp_die();
        }

        function wp_manga_archive_loadmore(){

            $manga_args = isset( $_POST['manga_args'] ) ? $_POST['manga_args'] : '';
            $template = sanitize_file_name($_POST['template']);

            if( empty( $manga_args ) ){
                wp_send_json_error();
            }

            global $wp_manga, $wp_manga_template;

            $manga_args['paged'] += 1;
            $manga_query = $wp_manga->mangabooth_manga_query( $manga_args );

            if( $manga_query->have_posts() ) {

                $wp_manga->wq_vars_js( $manga_args );

                $index = 0;

                set_query_var( 'wp_manga_posts_per_page', $manga_query->post_count );
                set_query_var( 'wp_manga_paged', $manga_args['paged'] );

                while( $manga_query->have_posts() ){
                    $index++;
                    set_query_var( 'wp_manga_post_index', $index );

                    $manga_query->the_post();
                    $wp_manga_template->load_template( 'content/content', $template );
                }

                $args = $manga_query->query;
                $args['max_num_pages'] = $manga_query->max_num_pages;

                $wp_manga->wq_vars_js( $args, true );

                die();
            }

            wp_reset_postdata();

            die(0);

        }

        function replace_blogspot_url(){

            global $wp_manga_google_upload;
            $all_manga_dirs = glob( WP_MANGA_JSON_DIR . '/*' );

            foreach( $all_manga_dirs as $dir ){

                $manga_json = $dir . '/manga.json';

                if( !file_exists( $manga_json ) ){
                    continue;
                }

                $manga_json_data = file_get_contents( $manga_json );
                $manga_data = json_decode( $manga_json_data, true );

                if( empty( $manga_data['chapters'] ) ){
                    continue;
                }

                foreach( $manga_data['chapters'] as $chapter_id => $chapter ){

                    if( !empty( $chapter['storage']['picasa'] ) ){

                        foreach( $chapter['storage']['picasa']['page'] as $page_num => $page ){
                            $manga_data['chapters'][$chapter_id]['storage']['picasa']['page'][$page_num]['src'] = $wp_manga_google_upload->blogspot_url_filter( $page['src'] );
                        }
                    }
                }

                $fp = fopen( $manga_json , 'w');
                fwrite( $fp, json_encode( $manga_data ) );
                fclose( $fp );

            }

            update_option('_wp_manga_is_blogspot_replaced', true);
            wp_send_json_success();

        }

        function wp_manga_clean_temp_folder(){

            $post_id = isset( $_POST['postID'] ) ? $_POST['postID'] : '';

            if( empty( $post_id ) ){
                wp_send_json_error( __('Missing Post ID', WP_MANGA_TEXTDOMAIN ) );
            }

            global $wp_manga, $wp_manga_storage;
            $uniqid = $wp_manga->get_uniqid( $post_id );
            $paths_to_clean = get_transient( 'path_to_clean_' . $uniqid );

            if( $paths_to_clean ){
                foreach( $paths_to_clean as $path ){
                    $wp_manga_storage->local_remove_storage( $path );
                }
            }

            delete_transient( 'path_to_clean_' . $uniqid );

            wp_send_json_success();

        }

        function wp_manga_duplicate_server(){
            $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
            $postID    = isset( $_POST['postID'] ) ? $_POST['postID'] : null;

            $post_id          = isset( $_POST['postID'] ) ? $_POST['postID'] : '';
            $chapter_id       = isset( $_POST['chapterID'] ) ? $_POST['chapterID'] : '';
            $duplicate_server = isset( $_POST['duplicateServer'] ) ? $_POST['duplicateServer'] : '';

            if(wp_verify_nonce( $nonce, "wp-manga-admin" ) && current_user_can( 'edit_post', $post_id )){
                if( empty( $post_id ) || empty( $chapter_id ) || empty( $duplicate_server ) ) {
                    wp_send_json_error();
                }

                global $wp_manga_storage;
                $response = $wp_manga_storage->duplicate_server( $post_id, $chapter_id, $duplicate_server );

                if( $response !== false ) {
                    wp_send_json_success( $response );
                }
            }
        }

        function wp_manga_remove_storage(){
            $post_id          = isset( $_POST['postID'] ) ? $_POST['postID'] : '';
            $chapter_id       = isset( $_POST['chapterID'] ) ? $_POST['chapterID'] : '';
            $storage = isset( $_POST['storage'] ) ? $_POST['storage'] : '';
            $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
            if(wp_verify_nonce( $nonce, "wp-manga-admin" ) && current_user_can( 'edit_post', $post_id )){
                if( empty( $post_id ) || empty( $chapter_id ) || empty( $storage ) ) {
                    wp_send_json_error();
                }

                global $wp_manga_storage;
                $response = $wp_manga_storage->remove_storage( $post_id, $chapter_id, $storage );

                if( $response !== false ) {
                    wp_send_json_success( $response );
                }
            }
        }

        function wp_manga_first_install_page_save(){

            $manga_archive_page = sanitize_text_field(isset( $_POST['manga_archive_page'] ) ? $_POST['manga_archive_page'] : 0);
            $user_page = sanitize_text_field(isset( $_POST['user_page'] ) ? $_POST['user_page'] : 0);

            if( $manga_archive_page == 0 && $user_page == 0 ) {
                return false;
            }

            $settings = get_option( 'wp_manga_settings' , array() );
            $settings['manga_archive_page'] = $manga_archive_page;
            $settings['user_page'] = $user_page;

            $resp = update_option( 'wp_manga_settings', $settings );

            wp_send_json_success( $resp );

        }

        function wp_manga_first_install_post_save(){

            $manga_slug = isset( $_POST['manga_slug'] ) ? $_POST['manga_slug'] : 'manga';

            if( $manga_slug == 'manga' ) {
                return false;
            }

            $settings = get_option( 'wp_manga_settings' , array() );
            $settings['manga_slug'] = urldecode( sanitize_title( $manga_slug ) );
            update_option( 'wp_manga_settings', $settings );

            $args = get_post_type_object( 'wp-manga' );
            $args->rewrite['slug'] = $manga_slug;
            register_post_type( $args->name, $args );
            flush_rewrite_rules();

            wp_send_json_success();

        }

        function wp_manga_skip_first_install(){

            $resp = update_option( 'wp_manga_notice', true );
            wp_send_json_success( $resp );

        }

		/**
		 * Upload more images to chapter
		 **/
		function wp_manga_chapter_upload_images(){
            $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
            if(wp_verify_nonce( $nonce, "wp-manga-admin" )){
                $files = $_FILES ? $_FILES[ key( $_FILES ) ] : null;
                if($files){
                    if(isset($files['error']) && $files['error'] != 0){
                        error_log('Upload error code: ' . $files['error']);
                        return wp_send_json_error( esc_html__('Upload error. Please try again', WP_MANGA_TEXTDOMAIN) );
                    } else {
                        $chapter_id = isset($_POST['chapter_id']) ? $_POST['chapter_id'] : 0;
                        $storage = isset($_POST['storage']) ? $_POST['storage'] : '';
                        if($chapter_id && $storage){
                            global $wp_manga_chapter;

                            $chapter = $wp_manga_chapter->get_chapter_by_id(null, $chapter_id);

                            if($chapter){
                                global $wp_manga_storage, $wp_manga;
                                $slugified_name = $wp_manga_storage->slugify( $chapter['chapter_name'] );
                                $manga_id = $chapter['post_id'];
								$uniqid = $wp_manga->get_uniqid( $manga_id );

                                if(current_user_can('edit_post', $manga_id)){

									global $wp_manga_chapter_data;

									$chapter_datas = $wp_manga_chapter_data->select( array(
										'chapter_id' => $chapter_id,
										'post_id'    => $manga_id
									) );

                                    // find current chapter folder

									$chapter_metas = $wp_manga_chapter->get_chapter_meta($chapter_id);
									if(!$chapter_metas){
										$chapter_metas = array();
									}

									$physical_chapter_slug = '';

									if(!isset($chapter_metas['uid'])){
										if($chapter_datas){
											foreach($chapter_datas as $data){
												if($data['storage'] == $storage){
													$pages = $data['data'] != '' ? json_decode( $data['data'], true ) : array();
													$first_link = count($pages) ? array_values($pages)[0]['src'] : '';

													if($first_link){
														$physical_chapter_slug = basename(pathinfo($first_link)['dirname']);
													}

													break;
												}
											}
										}

										if(!$physical_chapter_slug){
											$physical_chapter_slug = $wp_manga_storage->get_uniq_dir_slug( $chapter['chapter_name'] );
										}

										$chapter_metas['uid'] = $physical_chapter_slug;

										$chapter_args = array(
											'update' => [],
											'args'   => array(
												'post_id'    => $manga_id,
												'chapter_id' => $chapter_id,
											)
										);

										$chapter_args['update']['chapter_metas'] = serialize($chapter_metas);
										$wp_manga_chapter->update_chapter( $chapter_args['update'], $chapter_args['args'] );
									} else {
										$physical_chapter_slug = $chapter_metas['uid'];
									}

                                    // local storage folder
                                    $local_folder = WP_MANGA_DATA_DIR . $uniqid . '/' . $physical_chapter_slug;
                                    $local_uri = WP_MANGA_DATA_URL . $uniqid . '/' . $physical_chapter_slug;

                                    // temp folder
                                    $extract = WP_MANGA_EXTRACT_DIR . $uniqid . '/' . $physical_chapter_slug . '/';
                                    $extract_uri = WP_MANGA_EXTRACT_URL . '/' . $uniqid . '/' . $physical_chapter_slug . '/';

                                    if(!file_exists($extract)){
                                        mkdir($extract, 0777, true);
                                    }

                                    $name = $files['name'];
                                    $exts = explode(".", $name);
                                    $new_images = array();

                                    if(in_array($exts[count($exts) - 1], WP_MANGA_FUNCTIONS::get_validated_image_extensions())){
                                        // single image
                                        // copy to extract
                                        copy($files['tmp_name'], $extract . $files['name']);
                                    } else {
                                        // Check if zip file contains invalid file
                                        $validation = MADARA_ZIP_VALIDATION::get_zip_structure( $files['tmp_name'] );

                                        do_action( 'single_chapter_upload_validation', $validation );

                                        if( is_wp_error( $validation ) ){
                                            return wp_send_json_error( $validation->get_error_message() );
                                        }elseif(
                                            isset( $validation['chapter_type'] )
                                            && $validation['chapter_type'] == 'content'
                                        ){
                                            return wp_send_json_error( __( 'Invalid Zip file for Manga Chapter upload. This should be zip file for Video or Text Chapter upload.', WP_MANGA_TEXTDOMAIN ) );
                                        }elseif(
                                            isset( $validation['zip_type'] )
                                            && in_array(
                                                $validation['zip_type'],
                                                array(
                                                    'multi_chapters_no_volume',
                                                    'multi_chapters_with_volumes'
                                                )
                                            )
                                        ){
                                            return wp_send_json_error( __( 'Invalid Zip file for Single Chapter upload. This is Multi Chapters Upload zip file', WP_MANGA_TEXTDOMAIN ) );
                                        }elseif( empty( $validation['zip_type'] ) || empty( $validation['chapter_type'] ) ){
                                            return wp_send_json_error( __( 'Unsupported Zip File for Single Chapter upload', WP_MANGA_TEXTDOMAIN ) );
                                        }

                                        $chapter_zip = new ZipArchive();
                                        if( $chapter_zip->open( $files['tmp_name'] ) ) {

                                            $chapter_zip->extractTo( $extract );
                                            $chapter_zip->close();
                                        }
                                    }

                                    $upload = array();
                                    // read all images in temp extract folder
                                    if ( $dh = opendir( $extract ) ) {
                                        while ( ( $file = readdir( $dh ) ) !== false ) {

                                            if ( is_dir( rtrim( $extract, '/' ) . '/' . $file ) ) {
                                                continue;
                                            }

                                            if ( '.' != $file && '..' != $file && strtolower( $file ) !== '.ds_store' ) {
                                                //rename image name to slug
                                                $file_name         = preg_replace( '/\s/', '-', $file );
                                                $current_file_path = $extract . '/' . $file;
                                                $new_file_path     = $extract . '/' . $file_name;
                                                rename( $current_file_path, $new_file_path );

                                                $local_path = $local_folder . '/' . $file_name;

                                                if($storage == 'local'){
                                                    // copy to real chapter folder
                                                    if(!file_exists($local_folder . '/')){
                                                        mkdir($local_folder . '/', 0777, true);
                                                    }

                                                    copy($new_file_path, $local_path);
                                                }

                                                $mime = $wp_manga_storage->mime_content_type($local_path);
                                                array_push($new_images, array( 'src' => trim($uniqid . '/' . $physical_chapter_slug . '/' . $file_name, '/'), 'mime' => $mime ));

                                                $upload['file'][] = '/' . $file_name;
                                            } elseif ( strtolower( $file ) === '.ds_store' ) {
                                                unlink( $extract . '/' . $file );
                                            }
                                        }

                                        closedir( $dh );
                                    }

                                    if($storage != 'local'){
                                        $upload['dir']     = rtrim($extract, '/');
                                        $upload['uniqid']  = $uniqid;
                                        $upload['host']    = trim($extract_uri, '/');
                                        $upload['chapter'] = $slugified_name;

                                        $upload = apply_filters( 'manga_upload_chapters_files', $upload, $uniqid, $storage );

                                        natcasesort( $upload['file'] );

                                        $upload = $wp_manga_storage->upload_cloud( $upload, $storage );

                                        $overwrite = false;
                                        $c_slug = $physical_chapter_slug;
                                        $upload = apply_filters( 'wp_manga_upload_action_result', $upload, compact( [ 'uniqid', 'c_slug', 'extract', 'extract_uri', 'storage', 'overwrite' ] ) );

                                        if(isset($upload['file']) && count($upload['file']) == count($new_images)) {
                                            $arr = array();
                                            for($i = 0; $i < count($new_images); $i++){
                                                array_push($arr, array('src' => $upload['file'][$i], 'mime' => $new_images[$i]['mime']));
                                            }

                                            $new_images = $arr;
                                        } else {
                                            wp_send_json_error( esc_html__('Upload failed. Please try again later', WP_MANGA_TEXTDOMAIN ) );
                                        }
                                    }

                                    do_action( 'wp_manga_upload_after_extract', $manga_id, $slugified_name, $extract, $storage );

                                    // update chapter data (ie. image links)
                                    if(count($new_images)){
                                        usort($new_images, function($image1, $image2){
                                            return strnatcasecmp($image1['src'], $image2['src']);
                                        });
                                        if($chapter_datas){
                                            foreach($chapter_datas as $data){
                                                if($data['storage'] == $storage){
                                                    $pages = $data['data'] != '' ? json_decode( $data['data'], true ) : array();

                                                    foreach($new_images as $new_image){
                                                        $pages[count($pages) + 1] = $new_image;
                                                    }

                                                    $wp_manga_chapter_data->update(array('data' => json_encode($pages)), array('data_id' => $data['data_id']));

                                                    break;
                                                }
                                            }
                                        }

                                        $update_chapter_modified_time = isset($_POST['chapter_update_time']) ? $_POST['chapter_update_time'] : 0;

                                        if($update_chapter_modified_time){
                                            update_post_meta( $manga_id, '_latest_update', current_time( 'timestamp', false ) );
                                        }
                                    } else {
                                        wp_send_json_error( esc_html__('Upload failed. No images uploaded. Please try again later', WP_MANGA_TEXTDOMAIN ) );
                                    }

                                    $wp_manga_storage->local_remove_storage( WP_MANGA_EXTRACT_DIR . $uniqid );

                                    wp_send_json_success( array( 'message' => esc_html__('Upload Successful', WP_MANGA_TEXTDOMAIN )));
                                }
                            }
                        }
                    }
                }
            }

			wp_send_json_error( esc_html__('Upload failed. Please try again later', WP_MANGA_TEXTDOMAIN ) );
		}

		function wp_manga_save_volumes_order(){
			global $wp_manga_volume;
			$nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
            if(wp_verify_nonce( $nonce, "wp-manga-admin" )){
                $vols = $_POST['vols'];
                if(is_array($vols)){
                    foreach($vols as $vol){
                        $result = $wp_manga_volume->update_volume(array('volume_index' => $vol['index']), array('volume_id' => $vol['id']));
                    }

                    wp_send_json_success( [
                            'data' => 'ok'
                        ] );
                }

                wp_send_json_error( [
                    'message' => esc_html__( 'Error', WP_MANGA_TEXTDOMAIN )
                ] );
            }
		}

		function blogspot_search_album() {

			if ( empty( $_GET['album'] ) ) {
				wp_send_json_error( [
					'message' => esc_html__( 'Album Name cannot be empty', WP_MANGA_TEXTDOMAIN )
				] );
			}

            $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
            if(wp_verify_nonce( $nonce, "wp-manga-admin" )){

                global $wp_manga_google_upload;

                $album_list = $wp_manga_google_upload->get_album_list();

                if ( ! empty( $album_list ) && is_array( $album_list ) ) {

                    $output = array();

                    foreach ( $album_list as $id => $album ) {
                        if ( $album['title'] == $_GET['album'] ) {
                            $output[] = array_merge( $album, array(
                                'id' => (string) $id
                            ) );
                        }
                    }

                    if ( ! empty( $output ) ) {
                        wp_send_json_success( [
                            'data' => $output
                        ] );
                    }
                }

                wp_send_json_error( [
                    'message' => esc_html__( 'Cannot find this album', WP_MANGA_TEXTDOMAIN )
                ] );
            }

		}

		function wp_manga_get_chapter() {

			global $wp_manga, $wp_manga_chapter, $wp_manga_functions;
			$postID       = isset( $_GET['postID'] ) ? $_GET['postID'] : null;
			$chapterID    = isset( $_GET['chapterID'] ) ? $_GET['chapterID'] : null;
			$chapter_type = ! empty( $_GET['type'] ) ? $_GET['type'] : 'manga';

			if ( empty( $postID ) ) {
				wp_send_json_error( esc_html__( 'Missing Post ID', WP_MANGA_TEXTDOMAIN ) );
			}

			if ( empty( $chapterID ) ) {
				wp_send_json_error( esc_html__( 'Missing Chapter ID', WP_MANGA_TEXTDOMAIN ) );
			}

            $nonce = isset($_GET['nonce']) ? $_GET['nonce'] : '';

            if(wp_verify_nonce($nonce, 'wp-manga-admin')){
                $this_chapter = $wp_manga_chapter->get_chapter_info( $postID, $chapterID );

                if ( ! $this_chapter ) {
                    wp_send_json_error( esc_html__( 'Cannot find this Chapter', WP_MANGA_TEXTDOMAIN ) );
                }

                if ( $chapter_type == 'text' || $chapter_type == 'video' ) {

                    /**
                     * Get text chapter and video chapter
                     */
                    if ( isset( $this_chapter['chapter_id'] ) ) {

                        global $wp_manga_text_type;

                        //get chapter content
                        $chapter_post_content = $wp_manga_text_type->get_chapter_content_post( $this_chapter['chapter_id'] );

                        if ( $chapter_post_content ) {
                            $chapter_data = array(
                                'type'    => $chapter_type,
                                'chapter' => $this_chapter,
                                'data'    => $chapter_post_content->post_content,
                            );

                            $chapter_data = apply_filters('wp_manga_get_chapter', $chapter_data, $chapterID, $chapter_type);

                            wp_send_json_success( $chapter_data );
                        } else {
                            wp_send_json_error( esc_html__( 'Cannot find Chapter Content Data', WP_MANGA_TEXTDOMAIN ) );
                        }
                    }
                } else {

                    /**
                     * Get manga chapter
                     */

                    $chapter_data = $wp_manga_functions->get_single_chapter( $postID, $chapterID );

                    if ( empty( $chapter_data ) ) {
                        wp_send_json_error( __( 'Empty chapter data', WP_MANGA_TEXTDOMAIN ) );
                    }

                    $manga = array(
                        'type'    => 'manga',
                        'chapter' => $this_chapter,
                        'data'    => $chapter_data,
                    );

                    $available_host = $wp_manga->get_available_host();

                    foreach ( $chapter_data['storage'] as $host => $storage ) {

                        //skip inUse in storage array
                        if ( $host == 'inUse' || empty( $host ) ) {
                            continue;
                        }

                        //add storage name to return data
                        if(isset($available_host[ $host ])){
                            $manga['data']['storage'][ $host ]['name'] = $available_host[ $host ]['text'];
                            unset( $available_host[ $host ] );
                        }
                    }

                    if ( ! empty( $available_host ) ) {
                        $manga['available_host'] = $available_host;
                    }

                    $manga = apply_filters('wp_manga_get_chapter', $manga, $chapterID, $chapter_type);

                    wp_send_json_success( $manga );

                }
            }
		}

		function wp_manga_delete_chapter() {
            $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
            $postID    = isset( $_POST['postID'] ) ? $_POST['postID'] : null;

            if(wp_verify_nonce( $nonce, "wp-manga-admin" ) && current_user_can( 'edit_post', $postID )){
                global $wp_manga_storage;

                $chapterID = isset( $_POST['chapterID'] ) ? $_POST['chapterID'] : null;

                if($chapterID){
                    $wp_manga_storage->delete_chapter( $postID, $chapterID );
                } else {
                    $chapterIds = isset( $_POST['chapterIDs'] ) ? $_POST['chapterIDs'] : [];
                    foreach($chapterIds as $cid){
                        $wp_manga_storage->delete_chapter( $postID, $cid );
                    }
                }

                wp_send_json_success( $chapterID );
            }
		}

        /**
         * Remove all images inside a chapter, from all storages
         **/
        function wp_manga_empty_chapter() {

			global $wp_manga_storage;
			$postID    = isset( $_POST['postID'] ) ? $_POST['postID'] : null;
			$nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';

			$chapterID = isset( $_POST['chapterID'] ) ? $_POST['chapterID'] : null;
			$storage = isset( $_POST['storage'] ) ? $_POST['storage'] : null;

			if($storage){
				if(wp_verify_nonce( $nonce, "wp-manga-admin" ) && current_user_can( 'edit_post', $postID )){
					if($chapterID){
						$wp_manga_storage->empty_chapter( $postID, $chapterID, $storage );
					} else {
						$chapterIds = isset( $_POST['chapterIDs'] ) ? $_POST['chapterIDs'] : [];
						foreach($chapterIds as $cid){
							$wp_manga_storage->empty_chapter( $postID, $cid, $storage );
						}
					}

					wp_send_json_success( $chapterID );
				}
			}
		}

        /**
         * Move chapters to a different volume
         **/
        function wp_manga_move_chapters() {

			global $wp_manga_storage;
			$postID    = isset( $_POST['postID'] ) ? $_POST['postID'] : null;

			$chapterID = isset( $_POST['chapterID'] ) ? $_POST['chapterID'] : null;
			$volID = isset( $_POST['volID'] ) ? $_POST['volID'] : null;

            if($volID != null){
                $chapterIds = isset( $_POST['chapterIDs'] ) ? $_POST['chapterIDs'] : [];
                foreach($chapterIds as $cid){
                    $wp_manga_storage->move_chapter( $cid, $volID );
                }
            }

			wp_send_json_success( $volID );
		}

		function wp_manga_create_volume() {
			global $wp_manga_storage;
			$volumeName = isset( $_POST['volumeName'] ) ? $_POST['volumeName'] : null;
			$postID     = isset( $_POST['postID'] ) ? $_POST['postID'] : null;

            $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
            if(wp_verify_nonce($nonce, 'wp-manga-admin')){
                if ( $postID ) {
                    $volume_id = $wp_manga_storage->create_volume( $volumeName, $postID );

                    wp_send_json_success( $volume_id );
                } else {
                    wp_send_json_error();
                }
            }
		}

		function wp_save_chapter() {

			global $wp_manga_storage, $wp_manga_chapter;

			$_POST = stripslashes_deep( $_POST );

			$paging         = isset( $_POST['paging'] ) ? $_POST['paging'] : null;
			$postID         = isset( $_POST['postID'] ) ? $_POST['postID'] : null;
			$chapterID      = isset( $_POST['chapterID'] ) ? $_POST['chapterID'] : null;
			$storage        = isset( $_POST['storage'] ) ? $_POST['storage'] : null;
			$chapterNewName = isset( $_POST['chapterNewName'] ) ? $_POST['chapterNewName'] : null;
			$nameExtend     = isset( $_POST['chapterNameExtend'] ) ? $_POST['chapterNameExtend'] : null;
			$chapterIndex     = isset( $_POST['chapterIndex'] ) ? $_POST['chapterIndex'] : 0;
			$chapter_type   = isset( $_POST['chapterType'] ) ? $_POST['chapterType'] : 'manga';
			$chapterContent = isset( $_POST['chapterContent'] ) ? $_POST['chapterContent'] : '';
			$chapterSEO = isset( $_POST['chapterSEO'] ) ? $_POST['chapterSEO'] : '';
			$chapterWarning = isset( $_POST['chapterWarning'] ) ? $_POST['chapterWarning'] : '';
			$chapterStatus  = isset( $_POST['chapterStatus'] ) ? $_POST['chapterStatus'] : 1;

			$volume = isset( $_POST['volume'] ) ? $_POST['volume'] : '0';

			$chapter_args = array(
				'update' => array(
					'volume_id'           => $volume,
					'chapter_name'        => $chapterNewName,
					'chapter_name_extend' => $nameExtend,
					'chapter_index' => $chapterIndex,
					'storage_in_use' => $storage,
					'chapter_seo' => $chapterSEO,
					'chapter_warning' => $chapterWarning,
					'chapter_status' => $chapterStatus
				),
				'args'   => array(
					'post_id'    => $postID,
					'chapter_id' => $chapterID,
				)
			);

            $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
            if(wp_verify_nonce( $nonce, "wp-manga-admin" ) && current_user_can('edit_post', $postID)){

                $chapter_args = apply_filters( 'wp_manga_save_chapter_args', $chapter_args );

				$chapter_metas = $wp_manga_chapter->get_chapter_meta($chapterID);
				if(!$chapter_metas){
					$chapter_metas = array();
				}

                $chapterAMPHeight = isset( $_POST['chapterAMPHeight'] ) ? $_POST['chapterAMPHeight'] : '';
                if($chapterAMPHeight != ''){
                    $chapter_metas['AMP_Height'] = $chapterAMPHeight;
                    $chapter_args['update']['chapter_metas'] = serialize($chapter_metas);
                }

				if(!isset($chapter_metas['uid'])){
					// support previous version of Madara where uid is not saved yet
					global $wp_manga, $wp_manga_chapter_data;

					$chapter_datas = $wp_manga_chapter_data->select( array(
						'chapter_id' => $chapterID,
						'post_id'    => $postID
					) );

					if($chapter_datas){
						foreach($chapter_datas as $data){
							if($data['storage'] == $storage){
								$pages = $data['data'] != '' ? json_decode( $data['data'], true ) : array();
								$first_link = count($pages) ? array_values($pages)[0]['src'] : '';

								if($first_link){
									$chapter_metas['uid'] = basename(pathinfo($first_link)['dirname']);
									$chapter_args['update']['chapter_metas'] = serialize($chapter_metas);
								}

								break;
							}
						}
					}
				}

                $chapter_args['update']['chapter_sort'] = $chapterIndex ? $chapterIndex : floatval(preg_replace('/[^0-9.]/', '', $chapterNewName));

                if ( $chapter_type == 'manga' ) {
                    if ( ! empty( $_POST['deletedImages'] ) && is_array( $_POST['deletedImages'] ) ) {
						$image_srcs = [];
                        foreach ( $_POST['deletedImages'] as $image ) {
							if($storage == 'local'){
								$image = WP_MANGA_DATA_DIR . $image;
								if ( file_exists( $image ) ) {
									// delete from local
									unlink( $image );
								}
							} else {
								$image_srcs[] = ['src' => $image];
							}
                        }

						$args = [
									'post_id' => $postID,
									'chapter_id' => $chapterID,
									'storage' => [
													$storage => $image_srcs
												]
								];

						do_action('manga_chapter_images_deleted', $args);
                    }

                    $result['file']   = $paging;
                    $result['volume'] = $volume;

                    //#needcheck
                    $result['host'] = $wp_manga_storage->get_host( $storage );

                    do_action('before_save_chapter', $chapterID, $chapter_args);

                    $chapter_data = $wp_manga_storage->update_chapter( $chapter_args, $result, $storage, $overwrite = false );

                    do_action('after_save_chapter', $chapterID, $chapter_args);

                    wp_send_json_success( $chapter_data );

                } else {

                    $resp = $wp_manga_chapter->update_chapter( $chapter_args['update'], $chapter_args['args'] );

                    if(!$resp){
                        global $wpdb;
                        if($wpdb->last_error){
                            wp_send_json_error($wpdb->last_error);
                        }
                    }

                    global $wp_manga_text_type;
                    //update chapter content
                    $chapter_post_content = $wp_manga_text_type->get_chapter_content_post( $chapterID );

                    do_action('before_save_chapter', $chapterID, $chapter_args);

                    if($chapter_post_content){
                        $resp = wp_update_post( array(
                            'ID'           => $chapter_post_content->ID,
                            'post_content' => $chapterContent,
                        ) );
                    } else {
                        // in some odd cases, chapter content is removed, we create it again
                        $chapter_content_args = array(
                            'post_type'    => 'chapter_text_content',
                            'post_content' => $chapterContent,
                            'post_status'  => 'publish',
                            'post_parent'  => $chapterID, //set chapter id as parent
                            'post_title' => $chapterID . '-' . sanitize_title($chapterNewName)
                        );

                        $resp = wp_insert_post( sanitize_post($chapter_content_args, 'db') );
                    }

                    do_action('after_save_chapter', $chapterID, $chapter_args);

                    wp_send_json_success( $resp );
                }
            }
		}

		function wp_manga_download_chapter() {

			global $wp_manga_storage;
			$post_id    = isset( $_POST['postID'] ) ? $_POST['postID'] : '';
			$chapter_id = isset( $_POST['chapterID'] ) ? $_POST['chapterID'] : '';
			$storage    = isset( $_POST['storage'] ) ? $_POST['storage'] : '';
            $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
            if(wp_verify_nonce( $nonce, "wp-manga-admin" ) && current_user_can('edit_post', $post_id)){
                if ( ! empty( $post_id ) && ! empty( $chapter_id ) && ! empty( $storage ) ) {
                    $zip = $wp_manga_storage->zip_chapter( $post_id, $chapter_id, $storage );

                    if ( $zip !== false ) {
                        // cron to delete tmp file 
                        $temp_files = get_transient("wp_manga_tmp_deleted");
                        if(!$temp_files){
                            $temp_files = array();
                        }
                        $temp_files[] = $zip['zip_path'];
                        set_transient("wp_manga_tmp_deleted", $temp_files, 36 * 60 * 60);
                        
                        wp_send_json_success( $zip );
                    }
                }
            }

			wp_send_json_error();
		}

		function wp_download_manga() {


			$post_id = isset( $_POST['postID'] ) ? $_POST['postID'] : '';

			if ( ! empty( $post_id ) ) {
				global $wp_manga, $wp_manga_storage;

				$uniqid = $wp_manga->get_uniqid( $post_id );

				$manga_zip = $wp_manga_storage->zip_manga( $post_id, $uniqid );

				if ( $manga_zip ) {

					$response = array(
						'zip' => $manga_zip
					);

                    // cron to delete tmp file 
                    $temp_files = get_transient("wp_manga_tmp_deleted");
                    if(!$temp_files){
                        $temp_files = array();
                    }
                    $temp_files[] = $manga_zip['zip_path'];
                    set_transient("wp_manga_tmp_deleted", $temp_files, 36 * 60 * 60);

					wp_send_json_success( $response );

				}

			}

			wp_send_json_error( __( 'Something wrong happened. Please try again later', WP_MANGA_TEXTDOMAIN ) );

		}

		function wp_manga_search_chapter() {

			global $wp_manga_functions, $wp_manga_chapter;

			$post_id = ! empty( $_POST['post'] ) ? $_POST['post'] : null;
			$search  = ! empty( $_POST['chapter'] ) ? $_POST['chapter'] : null;

			$post = get_post( get_post( $post_id ) );

			$chapters = $wp_manga_functions->get_latest_chapters( $post_id, $search, 10 );

			$volumes = array();

			foreach ( $chapters as $chapter ) {

				$this_chapter_volume = $wp_manga_chapter->get_chapter_volume( $post_id, $chapter['chapter_id'] );

				if ( ! isset( $volumes[ $chapter['volume_id'] ] ) ) {
					$volumes[ $chapter['volume_id'] ] = array(
						'volume_name' => $this_chapter_volume['volume_name']
					);
				}

				$volumes[ $chapter['volume_id'] ]['chapters'][] = $chapter;

			}

			$output = '';
			if ( $chapters ) {
				$output .= $wp_manga_functions->list_chapters_by_volume( $post_id, $volumes, true );
			} else {
				$output = __( '<span> Nothing matches </span>', WP_MANGA_TEXTDOMAIN );
			}

			wp_send_json_success( $output );

		}

		function wp_update_chapters_list() {

			global $wp_manga_post_type;

			$post_id = isset( $_POST['postID'] ) ? $_POST['postID'] : '';

            $output  = $wp_manga_post_type->list_all_chapters( $post_id );

			if ( ! empty( $output ) ) {
				wp_send_json_success( $output );
			}

			wp_send_json_error();

		}

		function update_picasa_album_dropdown() {

			$album         = get_option( 'google_latest_album', 'default' );
			$albums        = $GLOBALS['wp_manga_google_upload']->get_album_list();
			$current_album = isset( $_POST['current_album'] ) ? $_POST['current_album'] : '';
            $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
            if(wp_verify_nonce( $nonce, "wp-manga-admin" )){
                $html = '';

                foreach ( $albums as $id => $album ) {
                    $html .= '<option value="' . $id . '"' . selected( $id, $current_album, false ) . '>' . sprintf( __( '[Album] %s (having %d items)', WP_MANGA_TEXTDOMAIN ), $album['title'], $album['numphotos'] ) . '</option>';
                }

                wp_send_json_success( $html );
            }
		}

		function update_volume_name() {

			$post_id     = isset( $_POST['postID'] ) ? $_POST['postID'] : '';
			$volume_id   = isset( $_POST['volumeID'] ) ? $_POST['volumeID'] : '';
			$volume_name = isset( $_POST['volumeName'] ) ? $_POST['volumeName'] : '';
            $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
            if(wp_verify_nonce( $nonce, "wp-manga-admin" ) && current_user_can( 'edit_post', $post_id )){
                if ( empty( $volume_id ) ) {
                    wp_send_json_error( __( 'Missing Volume ID', WP_MANGA_TEXTDOMAIN ) );
                }

                global $wp_manga_volume;
                $args = array(
                    'volume_id' => $volume_id,
                );

                if ( ! empty( $post_id ) ) {
                    $args['post_id'] = $post_id;
                }

                $result = $wp_manga_volume->update_volume( array( 'volume_name' => $volume_name ), $args );

                wp_send_json_success( $result );
            }

		}

		function wp_manga_delete_volume() {

			$volume_id = isset( $_POST['volumeID'] ) ? $_POST['volumeID'] : '';
			$post_id   = isset( $_POST['postID'] ) ? $_POST['postID'] : '';

			if ( empty( $volume_id ) && $volume_id !== '0' ) {
				wp_send_json_error( __( 'Missing Volume ID', WP_MANGA_TEXTDOMAIN ) );
			}
            $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
            if(wp_verify_nonce( $nonce, "wp-manga-admin" ) && current_user_can( 'edit_post', $post_id )){
                global $wp_manga_storage;
                $wp_manga_storage->delete_volume( $post_id, $volume_id );

                wp_send_json_success();
            }

		}

		function wp_manga_save_chapter_type() {

			$post_id      = isset( $_POST['postID'] ) ? $_POST['postID'] : '';
			$chapter_type = isset( $_POST['chapterType'] ) ? $_POST['chapterType'] : '';
            $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
            if(wp_verify_nonce( $nonce, "wp-manga-admin" ) && current_user_can('edit_post', $post_id)){
                if ( empty( $post_id ) ) {
                    wp_send_json_error( esc_html__( 'Missing Post ID', WP_MANGA_TEXTDOMAIN ) );
                }

                if ( empty( $chapter_type ) ) {
                    wp_send_json_error( esc_html__( 'Missing Chapter Type', WP_MANGA_TEXTDOMAIN ) );
                }

                $type_array = apply_filters( 'madara_manga_chapter_type_array', array( 'manga', 'text', 'video' ) );

                if ( ! in_array( $chapter_type, $type_array ) ) {
                    wp_send_json_error( esc_html__( 'Invalid Chapter Type', WP_MANGA_TEXTDOMAIN ) );
                }

                do_action( 'madara_before_update_chapter_type_meta', $post_id, $chapter_type );

                if ( $chapter_type != 'manga' && $chapter_type != 'text' && $chapter_type != 'video' ) {
                    $chapter_type = 'manga';
                }

                update_post_meta( $post_id, '_wp_manga_chapter_type', $chapter_type );

                wp_send_json_success();
            }
		}

		/**
		 * Handle multi chapters upload for content chapter
		 *
		 */
		function chapter_content_upload() {
			if ( empty( $_POST['postID'] ) ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Missing Post ID', WP_MANGA_TEXTDOMAIN ) ) );
			}

			if ( empty( $_POST['chapterType'] ) ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Missing Chapter Type', WP_MANGA_TEXTDOMAIN ) ) );
			}

			if ( empty( $_FILES['file'] ) && empty($_POST['directlink']) ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Missing File', WP_MANGA_TEXTDOMAIN ) ) );
			} else {
				// Check if zip file contains invalid file
				$validation = MADARA_ZIP_VALIDATION::get_zip_structure( !empty( $_FILES['file'] ) ? $_FILES['file']['tmp_name'] : ABSPATH . $_POST['directlink'], $_POST['chapterType']);

				do_action( 'multi_chapters_content_upload_validation', $validation );

				if ( is_wp_error( $validation ) ) {
					return wp_send_json_error( $validation->get_error_message() );
				} elseif ( isset( $validation['chapter_type'] ) && $validation['chapter_type'] == 'manga' ) {
					return wp_send_json_error( __( 'Invalid Zip file for Manga Chapter Content type upload. This should be zip file for Manga Images Chapter type upload.', WP_MANGA_TEXTDOMAIN ) );
				} elseif ( isset( $validation['zip_type'] ) && $validation['zip_type'] == 'single_chapter' ) {
					return wp_send_json_error( __( 'Invalid Zip file for Multi Chapters upload. This is Single Chapter upload zip file', WP_MANGA_TEXTDOMAIN ) );
				} elseif ( empty( $validation['zip_type'] ) || empty( $validation['chapter_type'] ) ) {
					return wp_send_json_error( __( 'Unsupported Zip File for Multi Chapters upload', WP_MANGA_TEXTDOMAIN ) );
				}

			}

			$post_id      = $_POST['postID'];
			$volume       = isset( $_POST['volume'] ) ? $_POST['volume'] : '';
			$chapter_type = $_POST['chapterType'];

			global $wp_manga_text_type, $wp_manga_functions;

			$response = $wp_manga_text_type->upload_handler( $post_id, !empty( $_FILES['file'] ) ? $_FILES['file'] : array('name' => basename($_POST['directlink']),'tmp_name' => ABSPATH . $_POST['directlink']), $volume);

			if ( $response['success'] ) {
				wp_send_json_success( $response );
			} else {
				wp_send_json_error( $response );
			}

		}
	}

	new WP_MANGA_AJAX_BACKEND();
