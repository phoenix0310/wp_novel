<?php

	class WP_MANGA_STORAGE {

		public function __construct() {
			// add_action('init', array( $this, 'local_storage' ) );
			add_action( 'before_delete_post', array( $this, 'delete_manga' ) );
			add_action( 'wp_manga_upload_after_extract', array( $this, 'check_storage_limit' ), 10, 4 );
			add_action( 'wp_manga_upload_after_extract', array( $this, 'set_temp_dir_to_clean' ), 10, 4 );

			add_action( 'wp_manga_upload_after_extract', array( $this, 'flag_upload_start' ), 10, 2 );
			add_action( 'wp_manga_upload_completed', array( $this, 'flag_upload_end' ), 10, 5 );
		}

		function mime_content_type( $filename ) {

			$mime_types = array(

				'txt'  => 'text/plain',
				'htm'  => 'text/html',
				'html' => 'text/html',
				'php'  => 'text/html',
				'css'  => 'text/css',
				'js'   => 'application/javascript',
				'json' => 'application/json',
				'xml'  => 'application/xml',
				'swf'  => 'application/x-shockwave-flash',
				'flv'  => 'video/x-flv',

				// images
				'png'  => 'image/png',
				'jpe'  => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'jpg'  => 'image/jpeg',
				'gif'  => 'image/gif',
				'bmp'  => 'image/bmp',
				'ico'  => 'image/vnd.microsoft.icon',
				'tiff' => 'image/tiff',
				'tif'  => 'image/tiff',
				'svg'  => 'image/svg+xml',
				'svgz' => 'image/svg+xml',

				// archives
				'zip'  => 'application/zip',
				'rar'  => 'application/x-rar-compressed',
				'exe'  => 'application/x-msdownload',
				'msi'  => 'application/x-msdownload',
				'cab'  => 'application/vnd.ms-cab-compressed',

				// audio/video
				'mp3'  => 'audio/mpeg',
				'qt'   => 'video/quicktime',
				'mov'  => 'video/quicktime',

				// adobe
				'pdf'  => 'application/pdf',
				'psd'  => 'image/vnd.adobe.photoshop',
				'ai'   => 'application/postscript',
				'eps'  => 'application/postscript',
				'ps'   => 'application/postscript',

				// ms office
				'doc'  => 'application/msword',
				'rtf'  => 'application/rtf',
				'xls'  => 'application/vnd.ms-excel',
				'ppt'  => 'application/vnd.ms-powerpoint',

				// open office
				'odt'  => 'application/vnd.oasis.opendocument.text',
				'ods'  => 'application/vnd.oasis.opendocument.spreadsheet',
			);
			$explode    = explode( '.', $filename );
			$ext        = strtolower( array_pop( $explode ) );
			if ( array_key_exists( $ext, $mime_types ) ) {
				return $mime_types[ $ext ];
			} elseif ( function_exists( 'finfo_open' ) ) {
				$finfo    = finfo_open( FILEINFO_MIME );
				$mimetype = @finfo_file( $finfo, $filename );
				return $mimetype;
			} else {
				return 'application/octet-stream';
			}
		}

		function slugify( $text ) {
			$text = sanitize_title( $text );

			if ( empty( $text ) ) {
				return 'n-a';
			}

			return $text;
		}

		function unzip( $temp ) {
			$zip     = new ZipArchive;
			$return  = array();
			$res     = $zip->open( $temp['tmp_name'] );
			$extract = WP_MANGA_DIR . 'extract/';

			if ( $res === true ) {
				$zip->extractTo( $extract );
				$zip->close();

				if ( is_dir( $extract ) ) {
					if ( $dh = opendir( $extract ) ) {
						while ( ( $file = readdir( $dh ) ) !== false ) {

							if ( is_dir( rtrim( $extract, '/' ) . '/' . $file ) ) {
								continue;
							}

							if ( '.' != $file && '..' != $file && strtolower( $file ) !== '.ds_store' ) {
								$return[ $file ] = $extract . $file;
							} elseif ( strtolower( $file ) == '.ds_store' ) {
								unlink( $extract . $file );
							}
						}
						closedir( $dh );
					}
				}

				return $return;
			} else {
				return false;
			}
		}
	
		/**
		 * @return 
				array(
					'host' => string,
					'file' => array(string)
					);
		**/
		function local_storage( $uniqid, $c_slug, $extract, $extract_uri, $overwrite = false ) {

			global $wp_manga;

			if ( is_dir( $extract ) ) {
				$result = array(
							'host' => '',
							'file' => array()
							);
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

							// find real (physical) chapter slug
							$image_folder_name = basename( $extract );

							$result['file'][] = $uniqid . '/' . $image_folder_name . '/' . $file_name;
						} elseif ( strtolower( $file ) == '.ds_store' ) {
							unlink( $extract . '/' . $file );
						}
					}
					closedir( $dh );
				}

				$result['host'] = $extract_uri;				

				if(isset($result['file']) && is_array($result['file'])){
					natcasesort( $result['file'] );
				}
                
                $result = apply_filters( 'manga_upload_chapters_files', $result, $uniqid, 'local' );

				return $result;
			}

			return false;
		}

		function local_remove_storage( $path ) {

			$path = rtrim( $path, '/' );

			if ( file_exists( $path ) ) {

				if ( is_dir( $path ) ) {

					//use scandir instead of glob will grab hidden files
					$all_files = scandir( $path );

					if ( count( $all_files ) === 2 ) {
						rmdir( $path );

						return true;
					}

					foreach ( $all_files as $file ) {

						if ( $file === '.' || $file === '..' ) {
							continue;
						}

						$this->local_remove_storage( $path . '/' . $file );
					}

					if ( count( scandir( $path ) ) === 2 ) {
						rmdir( $path );

						return true;
					}

				} elseif ( is_file( $path ) ) {
					unlink( $path );
				}

			}

			return false;
		}

		/**
		 * @params
				$chapter_args = array(
			         'post_id'             => int,
			         'volume_id'           => int,
			         'chapter_name'        => string,
			         'chapter_name_extend' => string,
			         'chapter_slug'        => string,
			     );
				 
				$result = array(
					'host' => string,
					'file' => array(string)
					);
					
				$storage = string;
				
				$overwrite = boolean;
				
			@result Chapter ID (int)
		**/
		function create_chapter( $chapter_args, $chapter_images, $storage, $overwrite = false ) {

			global $wp_manga, $wp_manga_chapter;
			
			$chapter_id = $wp_manga_chapter->insert_chapter( $chapter_args );

			if ( $chapter_id == false ) {
				return false;
			}

			$resp = $this->create_json(
				$chapter_args['post_id'],
				$chapter_id,
				$chapter_images,
				$storage,
				$overwrite
			);

			return $chapter_id;

		}

		function update_chapter( $chapter_args, $result, $storage, $overwrite = true ) {

			global $wp_manga_chapter;

			// Update chapter row in db
			$wp_manga_chapter->update_chapter( $chapter_args['update'], $chapter_args['args'] );
			
			// Update chapter images
			return $this->create_json( $chapter_args['args']['post_id'], $chapter_args['args']['chapter_id'], $result, $storage, $overwrite, $update = true );
		}

		/**
		 * $extract - folder name to store chapter images. If not passed, chapter slug will be used
		 **/
		function create_json( $post_id, $chapter_id, $result, $storage, $overwrite = false, $update = false ) {
			$data = array();
			
			if(isset($result['file']) && is_array($result['file'])){

				foreach ( $result['file'] as $file ) {
					if ( ! isset( $page ) ) {
						$page = 1;
					}

					$data[ $page ]['src']  = $file;
					$data[ $page ]['mime'] = $this->mime_content_type( $file );

					$page ++;
				}
			
			}

			$data = json_encode( apply_filters( 'madara_chapter_data', $data, $post_id, $chapter_id, $storage ) );

			global $wp_manga_chapter_data;

			if( $update ){

				if ( $overwrite == true && $storage === 'local' ) {
					// delete old chapter local directory, in case of unique folder name
					$encrypt_dir = apply_filters('wp_manga_upload_encrypt_chapter_dir', true, $post_id);
					if($encrypt_dir){
						$this->local_delete_chapter_files( $chapter_id );
					}
				}

				// Get current data
				$current_data = $wp_manga_chapter_data->select( array(
					'chapter_id' => $chapter_id,
					'storage'    => $storage
				) );

				// If empty then insert
				if( empty( $current_data ) ){
					$resp = $wp_manga_chapter_data->insert(
						array(
							'chapter_id' => $chapter_id,
							'storage'    => $storage,
							'data'       => $data
						)
					);
				}else{
					$resp = $wp_manga_chapter_data->update(
						array(
							'data'       => $data
						),
						array(
							'chapter_id' => $chapter_id,
							'storage'    => $storage,
						)
					);
				}

			}else{
				if ( $overwrite == true ) {

					if( $storage === 'local' ){
						// delete old chapter local directory
						$encrypt_dir = apply_filters('wp_manga_upload_encrypt_chapter_dir', true, $post_id);
						if($encrypt_dir){
							$this->local_delete_chapter_files( $chapter_id );
						}
					}

					// delete all old data of chapter
					$update_chap = $wp_manga_chapter_data->delete(
						array(
							'chapter_id' => $chapter_id
						)
					);
				}

				$resp = $wp_manga_chapter_data->insert(
					array(
						'chapter_id' => $chapter_id,
						'storage'    => $storage,
						'data'       => $data
					)
				);
			}


			if( empty( $resp ) ){
				return false;
			}

			return $data;

		}

		function get_host( $storage ) {
			$host = '';
			switch ( $storage ) {
				case 'local':
					$host = WP_MANGA_DATA_URL;
					break;

				default:
					# code...
					break;
			}

			return $host;
		}


		// for upload to other server :
		// 1. create temp folder
		// 2. upload files
		// 3. delete temp folder

		function _storage( $uniqid, $slugified_name, $extract_dir, $extract_uri, $host ) {
			chmod( $extract_dir, 0777 );
			$upload = array();

			if ( is_dir( $extract_dir ) ) {
				if ( $dh = opendir( $extract_dir ) ) {
					while ( ( $file = readdir( $dh ) ) !== false ) {

						if ( is_dir( rtrim( $extract_dir, '/' ) . '/' . $file ) ) {
							continue;
						}

						if ( '.' != $file && '..' != $file && strtolower( $file ) !== '.ds_store' ) {

							//rename image name to slug
							$file_name         = preg_replace( '/\s/', '-', $file );
							$current_file_path = $extract_dir . '/' . $file;
							$new_file_path     = $extract_dir . '/' . $file_name;
							rename( $current_file_path, $new_file_path );

							$upload['file'][] = '/' . $file_name;
						} elseif ( strtolower( $file ) === '.ds_store' ) {
							unlink( $extract_dir . '/' . $file );
						}
					}
					
					closedir( $dh );
				}
			}

      		$upload['dir']     = rtrim($extract_dir, '/');
			$upload['uniqid']  = $uniqid;
			$upload['host']    = trim($extract_uri, '/');
			$upload['chapter'] = $slugified_name;

			natcasesort( $upload['file'] );
            
            $upload = apply_filters( 'manga_upload_chapters_files', $upload, $uniqid, $host );

			return $this->upload_cloud( $upload, $host );
		}
		
		/**
		 * $upload_data - The upload data object array('file' => array(), 'dir' => '', 'uniqid' => '', 'host' => '', 'chapter' => '');
		 *
		 * $storage - name of cloud storage
		 **/
		function upload_cloud( $upload_data, $storage ){
			global $wp_manga_imgur_upload, $wp_manga_flickr_upload, $wp_manga_google_upload, $wp_manga_amazon_upload;
			switch ( $storage ) {
				case 'imgur':
					$data = $wp_manga_imgur_upload->imgur_upload( $upload_data );
					break;
				case 'flickr':
					$data = $wp_manga_flickr_upload->flickr_upload( $upload_data );
					break;
				case 'picasa':
					$data = $wp_manga_google_upload->google_upload( $upload_data );
					break;
				case 'amazon':
					$data = $wp_manga_amazon_upload->amazon_upload( $upload_data );
					break;
				default:
					// support custom storage
					if(class_exists('wp_manga_storage_' . $storage )){
						
						$class = 'wp_manga_storage_' . $storage;
						$uploader = $class::get_instance();
						$upload_data = apply_filters('wp_manga_upload_' . $storage . '_params', $upload_data);
						
						$data = $uploader->upload( $upload_data );
					}
					
					break;
			}
			
			// if $data is not set, then it should be "unknown storage". We still pass the process, leave it to that "unknown storage" handler.
			if(isset($data)){
				if ( !$data || isset( $data->data->error ) || isset($data['error']) ) {
					return array( 'error' => 'storage_error', 'message' => (!$data ? __('Unknown error', WP_MANGA_TEXTDOMAIN) : (is_array($data) ? $data['error'] : $data->data->error->message)) );
				}
			
			}

			$upload['host'] = '';
			$upload['file'] = isset($data) ? $data : null;

			return $upload;
		}

		function create_volume( $volumeName, $postID ) {

			global $wp_manga, $wp_manga_volume;

			return $wp_manga_volume->insert_volume( array(
				'post_id'     => $postID,
				'volume_name' => $volumeName,
			) );

		}
		
        /**
         * Remove a chapter storage
         *
		 * $force_remove - boolean - Force to remove the last storage. However, keep the last storage as "inUse" property
         * @return string message
         **/
		function remove_storage( $post_id, $chapter_id, $storage, $force_remove = false ) {
			global $wp_manga_functions, $wp_manga_chapter;

			$chapter = $wp_manga_functions->get_single_chapter( $post_id, $chapter_id );
			
			if ( empty( $chapter ) ) {
				return __( 'Chapter doesn\'t exists', WP_MANGA_TEXTDOMAIN );
			}
			
			if(count($chapter['storage']) > 2 || $force_remove){				
				if ( !isset( $chapter['storage'][ $storage ] ) ) {
					return __( 'Chapter does not exist on this server', WP_MANGA_TEXTDOMAIN );
				}
				
				global $wp_manga_chapter_data;
				// remove in database
				$resp = $wp_manga_chapter_data->delete(
							array(
								'chapter_id' => $chapter_id,
								'storage'    => $storage
							)
						);
				
				// update current storage
				unset($chapter['storage'][ $storage ]);
				
				if(count($chapter['storage']) == 2){
					
					foreach($chapter['storage'] as $key => $value){
						if($key != 'inUse') break;
					}
					
					if($key != 'inUse'){
						$inUse = $key;
					} else {
						$inUse = 'local';
					}
					
					$wp_manga_chapter->update_chapter(array('storage_in_use' => $inUse), array('chapter_id' => $chapter_id));
				}

				// clear to see the effect of the removal
				wp_cache_delete('wp_manga_single_chapter_data_' . $chapter_id, 'wp_manga');

				return __( 'Remove successfully!', WP_MANGA_TEXTDOMAIN );
			} else {
				return __( 'Cannot remove the last storage', WP_MANGA_TEXTDOMAIN );
			}
		}

		function duplicate_server( $post_id, $chapter_id, $duplicate_to ) {

			global $wp_manga, $wp_manga_functions, $wp_manga_chapter;

			$chapter_slug = $wp_manga_chapter->get_chapter_slug_by_id( $post_id, $chapter_id );

			$chapter = $wp_manga_functions->get_single_chapter( $post_id, $chapter_id );
			
			$uniqid = $wp_manga->get_uniqid( $post_id );

			if ( empty( $chapter ) ) {
				return __( 'Chapter doesn\'t exists', WP_MANGA_TEXTDOMAIN );
			}

			if ( isset( $chapter['storage'][ $duplicate_to ] ) ) {
				return __( 'Chapter is already existed on this server', WP_MANGA_TEXTDOMAIN );
			}

			//start duplicating

			//if chapter is already in local
			if ( isset( $chapter['storage']['local'] ) ) {
				$chapter_basedir = dirname( $chapter['storage']['local']['page']['1']['src'] );

				$chapter_dir = WP_MANGA_DATA_DIR . $chapter_basedir;
				$chapter_uri = WP_MANGA_DATA_URL . $chapter_basedir;

				$response = $this->wp_manga_upload_action( $uniqid, $chapter_slug, $chapter_dir, $chapter_uri, $duplicate_to );

			} else {				
				$storage = $chapter['storage'][ $chapter['storage']['inUse'] ];

				$chapter_basedir = $this->get_uniq_dir_slug( $chapter_slug );

				//if duplicate to server not local, then chapter dir is temp
				if ( $duplicate_to !== 'local' ) {
					$chapter_dir = WP_MANGA_EXTRACT_DIR . $uniqid . '/' . $chapter_basedir . '/';
					$chapter_uri = WP_MANGA_EXTRACT_URL . $uniqid . '/' . $chapter_basedir . '/';
				}else{
					$chapter_dir = WP_MANGA_DATA_DIR . $uniqid . '/' . $chapter_basedir . '/' ;
					$chapter_uri = WP_MANGA_DATA_URL; // not use
				}
				
				//pull chapter to local
				foreach ( $storage['page'] as $page => $file ) {
					$content   = file_get_contents( $file['src'] );
					$mime_type = explode( '/', $file['mime'] );

					//create chapter dir for putting content
					if ( ! file_exists( $chapter_dir ) ) {
						wp_mkdir_p( $chapter_dir );
					}
					
					file_put_contents( $chapter_dir . $page . '.' . $mime_type[1], $content );					
				}

				if ( file_exists( $chapter_dir ) || ! empty( glob( $chapter_dir . '/*' ) ) ) {

					$response = $this->wp_manga_upload_action( $uniqid, $chapter_slug, $chapter_dir, $chapter_uri, $duplicate_to );

					do_action( 'wp_manga_upload_completed', $chapter_id, $post_id, $chapter_dir, $chapter_uri, $duplicate_to );
				}
			}

			if ( is_wp_error( $response ) ) {

				$this->local_remove_storage( $chapter_dir );

				return $response;

			} else if ( empty( $response ) ) {

				$this->local_remove_storage( $chapter_dir );

				return __( 'There was something wrong happened, please try again later ', WP_MANGA_TEXTDOMAIN );

			} else if ( ! empty( $response ) ) {

				if ( strpos( $chapter_dir, 'temp' ) !== false ) {
					$this->local_remove_storage( $chapter_dir );
				}
				
				if(isset($response['error'])){
					return $response['message'];
				} else {
				
					$this->create_json( $post_id, $chapter_id, $response, $duplicate_to );

					// clear to see the effect of the duplicate
					wp_cache_delete('wp_manga_single_chapter_data_' . $chapter_id, 'wp_manga');

					return __( 'Duplicate successfully!', WP_MANGA_TEXTDOMAIN );
				}

			}
		}

		function delete_volume( $post_id, $volume_id ) {

			global $wp_manga_volume, $wp_manga_chapter;

			//delete volume from database
			$wp_manga_volume->delete_volume( array(
				'post_id'   => $post_id,
				'volume_id' => $volume_id
			) );

			//get and delete all chapters in this volume
			$chapters = $wp_manga_chapter->get_chapters( array(
				'post_id'   => $post_id,
				'volume_id' => $volume_id
			) );

			if ( ! empty( $chapters ) ) {
				foreach ( $chapters as $chapter ) {
					$this->delete_chapter( $post_id, $chapter['chapter_id'] );
				}
			}

			return true;

		}

		function delete_chapter( $post_id, $c_id ) {

			global $wp_manga_chapter, $wp_manga_chapter_data;

			$this->local_delete_chapter_files( $c_id );

			$storages = $wp_manga_chapter_data->get_chapter_storages( $c_id );

			$chapter = $wp_manga_chapter->get_chapter_by_id(null, $c_id);
		
			if($chapter){
				$wp_manga_chapter->delete_chapter( array(
					'post_id'    => $post_id,
					'chapter_id' => $c_id
				) );

				do_action( 'manga_chapter_deleted', array(
					'post_id'    => $post_id,
					'chapter_id' => $c_id,
					'chapter_name' => $chapter['chapter_name'],
					'storage'    => $storages
				) );

				return true;
			}
			
			return false;
		}
        
        /**
         * Empty images inside a chapter
         **/
        function empty_chapter( $post_id, $c_id, $storage ) {

			global $wp_manga_chapter_data;

			$this->local_delete_chapter_files( $c_id );

			$storages = $wp_manga_chapter_data->get_chapter_storages( $c_id );
			
			if(isset($storages[$storage])){
				$this->remove_storage($post_id, $c_id, $storage, true);
			
				do_action( 'manga_chapter_deleted', array(
					'post_id'    => $post_id,
					'chapter_id' => $c_id,
					'storage'    => $storages
				) );
            
				$resp = $wp_manga_chapter_data->insert(
						array(
							'data'       => json_encode([]),
							'chapter_id' => $c_id,
							'storage'    => $storage,
						)
					);
			}

			return true;

		}

		function local_delete_chapter_files( $c_id ) {

			global $wp_manga_chapter_data;

			// Get chapter dir from data
			$chapter_data = $wp_manga_chapter_data->get_manga_chapter_storage_data( $c_id, 'local' );

			if( !empty( $chapter_data[1]['src'] ) ){
				$chapter_dir = WP_MANGA_DATA_DIR . dirname( $chapter_data[1]['src'] );

				if( file_exists( $chapter_dir ) ){
					return $this->local_remove_storage( $chapter_dir );
				}

			}

			return true;

		}
        
        /**
         * Move chapter to a new volume
         **/
        function move_chapter($chapter_id, $vol_id){
            global $wp_manga, $wp_manga_chapter;

			// Update chapter row in db
			return $wp_manga_chapter->update_chapter( ['volume_id' => $vol_id], ['chapter_id' => $chapter_id] );
        }        

		function delete_manga( $post_id ) {
			if ( get_post_type( $post_id ) !== 'wp-manga' ) {
				return;
			}

			global $wp_manga, $wp_manga_chapter, $wp_manga_volume;

			$uniqid = $wp_manga->get_uniqid( $post_id );

			if ( empty( $uniqid ) ) {
				return;
			}

			//remove files
			$c_path = WP_MANGA_DATA_DIR . "$uniqid/";

			if ( file_exists( $c_path ) ) {
				$this->local_remove_storage( $c_path );
			}

			//remove json
			$json_path = WP_MANGA_JSON_DIR . "$uniqid/";

			if ( file_exists( $json_path ) ) {
				$this->local_remove_storage( $json_path );
			}

			//remove from database
			//	remove all volume, remove all chapter
			$wp_manga_volume->delete_volume( array(
				'post_id' => $post_id
			) );
			
			$wp_manga_chapter->delete_chapter( array(
				'post_id' => $post_id
			) );

		}

		function count_image_files( $dir ) {

			$count = 0;

			if ( is_dir( $dir ) ) {
				foreach ( glob( $dir . '/*' ) as $file ) {
					if ( is_dir( $file ) ) {
						$count += $this->count_image_files( $file );
					} else {
						if ( strpos( $this->mime_content_type( $file ), 'image' ) !== false ) {
							$count ++;
						}
					}
				}
			} else {
				if ( strpos( $this->mime_content_type( $file ), 'image' ) !== false ) {
					$count ++;
				}
			}

			return $count;

		}

		function move_chapter_dir( $old_chapter_dir, $new_chapter_dir ) {

			if ( is_dir( $old_chapter_dir ) ) {

				//if this chapter is exist
				if ( ! is_dir( $new_chapter_dir ) ) {
					mkdir( $new_chapter_dir );
				}

				//copy all file to new dir
				$files = scandir( $old_chapter_dir );
				foreach ( $files as $file ) {
					$file_path = $old_chapter_dir . '/' . $file;
					if ( is_file( $file_path ) ) {
						copy( $file_path, $new_chapter_dir . '/' . $file );
						unlink( $file_path );
					}
				}

				$response = rmdir( $old_chapter_dir );

				if ( $response ) {
					return true;
				}
			}

			return false;
		}

		function zip_chapter( $post_id, $chapter_id, $storage = '' ) {

			global $wp_manga, $wp_manga_chapter;
			$chapter_slug = $wp_manga_chapter->get_chapter_slug_by_id( $post_id, $chapter_id );
			
			if(!file_exists(WP_MANGA_DIR . 'extract')){
				mkdir(WP_MANGA_DIR . 'extract');
			}
			
			$file_name = $chapter_slug;//uniqid('p' . $post_id . 'c'. $chapter_id.'_');
			$zip = array(
				'zip_dir'  => WP_MANGA_DIR . 'extract/' . $file_name . '.zip',
				'zip_path' => WP_MANGA_URI . 'extract/' . $file_name . '.zip',
			);

			$chapter_zip = new ZipArchive();
			$resp        = $chapter_zip->open( $zip['zip_dir'], ZipArchive::CREATE );

			$manga_chapter_type = get_post_meta( $post_id, '_wp_manga_chapter_type', true );

			if ( $manga_chapter_type == 'text' || $manga_chapter_type == 'video' ) {
				//wp_send_json_error( esc_html__( "Download Content Chapter is not supported", 'madara' ) );
				global $wp_manga_text_type;
				$chapter_content_post = $wp_manga_text_type->get_chapter_content_post($chapter_id);
				if($chapter_content_post){
					$chapter_content = $chapter_content_post->post_content;
					// save to text
					$file = WP_MANGA_DIR . 'extract/' . $file_name . '.txt';
					file_put_contents($file, $chapter_content);
					$chapter_zip->addFile( $file, basename( $file ) );
				}
			} else {
				if ( $storage == 'local' ) {
					// find folder name of chapter
					global $wp_manga_chapter_data;
					
					$chapter_path = '';
					$chapter_data = $wp_manga_chapter_data->get_manga_chapter_storage_data( $chapter_id, 'local' );

					if( !empty( $chapter_data[1]['src'] ) ){
						$chapter_path = WP_MANGA_DATA_DIR . dirname( $chapter_data[1]['src'] );
					}
					
					if ( $chapter_path != '' && file_exists( $chapter_path ) ) {
						
						$files       = glob( $chapter_path . '/*.*' );
						
						if ( $resp ) {
							foreach ( $files as $file ) {
								$chapter_zip->addFile( $file, basename( $file ) );
							}
						}
					}
				} else {
					$available_hosts = $wp_manga->get_available_host();
					// check if $storage is valid
					$valid = false;
					foreach($available_hosts as $host){
						if($host['value'] == $storage){
							$valid = true;
							break;
						}
					}
					
					if ( $valid ) {

						global $wp_manga_functions;

						$chapter_data = $wp_manga_functions->get_single_chapter( $post_id, $chapter_id );
						
						if( !empty( $chapter_data ) && !empty( $chapter_data['storage'][$storage] ) ){

							$chapter_files = $chapter_data['storage'][$storage]['page'];
							if( !empty( $chapter_files ) ){
								foreach ( $chapter_files as $page => $file ) {
									$file_content = file_get_contents( $file['src'] );
									$result = $chapter_zip->addFromString( $chapter_slug . '/' . $page . '.jpg', $file_content );
								}
							}
						}

					}
				}
			}

			$chapter_zip->close();

			if ( file_exists( $zip['zip_dir'] ) ) {
				return $zip;
			}

			return false;
		}

		function zip_manga( $post_id, $uniqid, $storage = '' ) {

			global $wp_manga_functions;

			if ( ! file_exists( WP_MANGA_EXTRACT_DIR ) ) {

				$make_dir = mkdir( WP_MANGA_EXTRACT_DIR, 0777, true );

				if ( ! $make_dir ) {
					return false;
				}
			}
			
			$file_name = uniqid('p' . $uniqid . '_');
			
			$zip = array(
				'zip_dir'  => WP_MANGA_EXTRACT_DIR . $file_name . '.zip',
				'zip_path' => WP_MANGA_EXTRACT_URL . $file_name . '.zip',
			);

			$manga_zip = new ZipArchive();
			$resp      = $manga_zip->open( $zip['zip_dir'], ZipArchive::CREATE | ZIPARCHIVE::OVERWRITE );

			if ( ! $resp ) {
				wp_send_json_error( esc_html__( "Cannot create zip file", 'madara' ) );
			}

			$manga_chapter_type = get_post_meta( $post_id, '_wp_manga_chapter_type', true );

			if ( empty( $manga_chapter_type ) ) {
				$manga_chapter_type = 'manga';
			}

			global $wp_manga_text_type;

			$all_chapters = $wp_manga_functions->get_all_chapters( $post_id );

			if ( ! $all_chapters ) {
				wp_send_json_error( esc_html__( "This Manga doesn't have any chapter", 'madara' ) );
			}

			if ( $manga_chapter_type == 'manga' ) {

				$manga_chapters = $wp_manga_functions->get_chapter( $post_id, true );

				if ( empty( $manga_chapters ) ) {
					wp_send_json_error( esc_html__( "This Manga doesn't have any chapters", 'madara' ) );
				}

			}
			
			foreach ( $all_chapters as $volume => $volume_data ) {

				$chapters = $volume_data['chapters'];

				foreach ( $chapters as $chapter ) {

					$chapter_id = $chapter['chapter_id'];

					if ( $manga_chapter_type == 'manga' ) {

						if ( ! isset( $manga_chapters[ $chapter_id ] ) ) {
							continue;
						}

						$this_chapter     = $manga_chapters[ $chapter_id ];
						$chapter_zip_path = $volume_data['volume_name'] . '/' . $chapter['chapter_name'];

						if ( isset( $this_chapter['storage']['local'] ) ) {

							foreach ( $this_chapter['storage']['local']['page'] as $page => $file ) {

								$file_path      = WP_MANGA_DATA_DIR . $file['src'];
								$file_extension = pathinfo( $file_path );

								if ( ! file_exists( $file_path ) ) {
									continue;
								}

								$manga_zip->addfile( $file_path, $chapter_zip_path . '/' . $page . '.' . $file_extension['extension'] );
							}
						} else {
							unset($this_chapter['storage']['inUse']);
							
							$storage = key( $this_chapter['storage'] ); // get first storage server 
							
							if(isset($this_chapter['storage'][ $storage ]['page'])){

								foreach ( $this_chapter['storage'][ $storage ]['page'] as $page => $file ) {
									$file_content   = file_get_contents( $file['src'] );
									$file_extension = pathinfo( $file['src'] );

									$manga_zip->addFromString( $chapter_zip_path . '/' . $page . '.' . $file_extension['extension'], $file_content );
								}
							
							}
						}

					} else {
						$chapter_content = $wp_manga_text_type->get_chapter_content_post( $chapter_id );

						if ( ! $chapter_content ) {
							continue;
						}
                        
                        $file_content = apply_filters('wp_manga_novel_chapter_content_export', $chapter_content->post_content, $chapter_id, $post_id);
                        $file_path = apply_filters('wp_manga_novel_chapter_zipfilepath_export', $volume_data['volume_name'] . '/' . $chapter['chapter_name'] . '/content.txt');                       

						$manga_zip->addFromString( $file_path, $file_content );
					}

				}
			}

			$manga_zip->close();

			if ( file_exists( $zip['zip_dir'] ) ) {
				return $zip;
			}

			return false;
		}

		/**
		 * @return Chapter ID (int)
		 **/
		function wp_manga_upload_single_chapter(
			$chapter_args,
			$extract,
			$extract_uri,
			$storage,
			$overwrite = false
		) {

			/**
			 * chapter_args structure
			 *
			 * @param $chapter_args = array(
			 *        'post_id'             => $post_id,
			 *        'volume_id'           => $volume,
			 *        'chapter_name'        => $name,
			 *        'chapter_name_extend' => $nameExtend,
			 *        'chapter_slug'        => $slugified_name,
			 *    );
			 */
			if ( file_exists( $extract ) ) {

				global $wp_manga, $wp_manga_chapter;
				$uniqid = $wp_manga->get_uniqid( $chapter_args['post_id'] );
				$result = $this->wp_manga_upload_action( $uniqid, $chapter_args['chapter_slug'], $extract, $extract_uri, $storage, $overwrite );

				if ( is_wp_error( $result ) || isset( $result['error'] ) || empty( $result ) ) {
					error_log('wp_manga_upload_action failed: ' . var_export($result, true));
					return $result;
				} elseif ( ! empty( $result ) ) {

					if ( $overwrite ) {
						$chapter_id = $wp_manga_chapter->get_chapter_id_by_slug( $chapter_args['post_id'], $chapter_args['chapter_slug'] );
					
						if($chapter_id){
							$update_args = array(
								'update' => array(
									'volume_id'           => $chapter_args['volume_id'],
									'chapter_name'        => $chapter_args['chapter_name'],
									'chapter_name_extend' => $chapter_args['chapter_name_extend'],
									'chapter_slug'        => $chapter_args['chapter_slug'],
									'storage_in_use' => $storage
								),
								'args'   => array(
									'post_id'    => $chapter_args['post_id'],
									'chapter_id' => $chapter_id,
								)
							);

							$response = $this->update_chapter( $update_args, $result, $storage, $overwrite );
						} else {
							error_log('Cannot find Chapter: ' . $chapter_args['post_id'] . ' - ' . $chapter_args['chapter_slug']);
							return false;
						}
						
						
					} else {
						$chapter_args['storage_in_use'] = $storage;
						
						$chapter_id = $this->create_chapter( $chapter_args, $result, $storage, $overwrite );
					}
					
					// remove tmp folder
					if($storage != 'local'){
						wp_manga_delete_dir($extract);
					}

				}

				return $chapter_id;
			} else {
				error_log('Extract folder does not exist: ' . $extract);
			}

			return false;
		}
		
		
		/**
		 * @return  
				array(
					'host' => string,
					'file' => array(string)
					);
		 **/
		function wp_manga_upload_action( $uniqid, $c_slug, $extract, $extract_uri, $storage, $overwrite = false ) {

			switch ( $storage ) {
				case 'local':
					$result = $this->local_storage( $uniqid, $c_slug, $extract, $extract_uri, $overwrite );
					break;
				default:
					$result = $this->_storage( $uniqid, $c_slug, $extract, $extract_uri, $storage );
					break;
			}

			$result = apply_filters( 'wp_manga_upload_action_result', $result, compact( [ 'uniqid', 'c_slug', 'extract', 'extract_uri', 'storage', 'overwrite' ] ) );
			return $result;
		}

		function check_storage_limit( $post_id, $slugified_name, $extract, $storage ) {

			if ( $storage == 'imgur' && $this->count_image_files( $extract ) > 50 ) {

				$this->local_remove_storage( $extract );

				wp_send_json_error( __( 'Oops, Imgur only allows user to upload maximum 50 images per hour, but your file contains more than 50 images. Please try another files', WP_MANGA_TEXTDOMAIN ) );
			}

			if ( $storage == 'picasa' ) {

				global $wp_manga_google_upload;
				$album = $wp_manga_google_upload->get_album();

				if ( $album == false ) {
					$this->local_remove_storage( $extract );
					wp_send_json_error( __( 'Not found this Album', WP_MANGA_TEXTDOMAIN ) );
				}

				$current_album_numphotos = $album['numphotos'];

				if ( $current_album_numphotos !== false && intval( $current_album_numphotos ) == 2000 ) {
					$this->local_remove_storage( $extract );
					wp_send_json_error( __( sprintf( 'Oops, Picasa only allows 2,000 photos in an album while %s album already has %d items.', $album['title'], $current_album_numphotos ), WP_MANGA_TEXTDOMAIN ) );
				}

				$count_files = $this->count_image_files( $extract );

				if ( $count_files !== false && intval( $current_album_numphotos + $count_files ) >= 2000 ) {
					$this->local_remove_storage( $extract );
					wp_send_json_error( esc_html__( sprintf( 'Oops, Picasa  only allows 2,000 photos in an album while %s album already has %d items and this .zip file contains %d photos. Please create a new album to upload successfully.', $album['title'], $current_album_numphotos, $count_files ), WP_MANGA_TEXTDOMAIN ) );
				}

			}


		}

		//There might be errors caused while uploading files, so this function will clean temp folder for a specific time
		function set_temp_dir_to_clean( $post_id, $slugified_name, $extract, $storage ) {

			global $wp_manga;

			$uniqid = $wp_manga->get_uniqid( $post_id );

			if ( $storage !== 'local' ) {

				$current_paths = get_transient( 'path_to_clean_' . $uniqid );

				if ( $current_paths == false ) {
					set_transient( 'path_to_clean_' . $uniqid, array( $extract ), 60 * 60 );
				} else {
					set_transient( 'path_to_clean_' . $uniqid, array_merge( $current_paths, array( $extract ) ), 60 * 60 );
				}
			}

		}

		/**
		 * Handle Manga Upload ( multi chapters )
		 */

		function manga_upload( $post_id, $manga_zip, $storage, $volume_id = 0) {

			global $wp_manga_functions, $wp_manga, $wp_manga_volume;
			
			$uniqid = $wp_manga->get_uniqid( $post_id );

			$temp_dir_name = uniqid( 'temp_unzip_' );

			//open zip
			$zip_manga = new ZipArchive();

			if ( ! $zip_manga->open( $manga_zip['tmp_name'] ) ) {
				wp_send_json_error( __( 'Cannot open Zip file ', WP_MANGA_TEXTDOMAIN ) );
			}

			//extract manga zip
			if ( $storage == 'imgur' || $storage == 'flickr' || $storage == 'picasa' || $storage == 'amazon' ) {
				$extract     = WP_MANGA_DIR . 'extract/temp/' . $uniqid . '/' . $temp_dir_name;
				$extract_uri = WP_MANGA_URI . 'extract/temp/' . $uniqid . '/' . $temp_dir_name;
			}else{
				$extract       = WP_MANGA_DATA_DIR . $uniqid . '/' . $temp_dir_name;
				$final_extract = WP_MANGA_DATA_DIR . $uniqid;
				$extract_uri   = WP_MANGA_DATA_URL;
			}

			$zip_manga->extractTo( $extract );
			$zip_manga->close();

			do_action( 'wp_manga_upload_after_extract', $post_id, $temp_dir_name, $extract, $storage );
			$encrypt_dir = apply_filters('wp_manga_upload_encrypt_chapter_dir', true, $post_id);
			//scan all dir
			$scandir_lv1 = glob( $extract . '/*' );
			$result      = array();

			//Dir level 1
			foreach ( $scandir_lv1 as $dir_lv1 ) {

				if ( basename( $dir_lv1 ) === '__MACOSX' ) {
					continue;
				}

				if ( is_dir( $dir_lv1 ) ) {

					//rename dir to slug name
					$chapter_slug = $wp_manga_functions->unique_slug( $post_id, wp_basename( $dir_lv1 ) );
					$dir_slug_lv1       = $encrypt_dir ? $this->get_uniq_dir_slug( $dir_lv1 ) : basename($dir_lv1);
					$rename_dir_lv1     = $extract . '/' . $dir_slug_lv1;
					$rename_dir_lv1_uri = $extract_uri . '/' . $dir_slug_lv1;

					if($encrypt_dir) rename( $dir_lv1, $rename_dir_lv1 );

					//check if dir level 1 is volume dir or chapter dir
					$scandir_lv2 = glob( $rename_dir_lv1 . '/*' );
					$is_volume   = false;
					foreach ( $scandir_lv2 as $dir_lv2 ) {

						if ( basename( $dir_lv2 ) === '__MACOSX' ) {
							continue;
						}

						//if dir level 2 is dir then dir level 1 is volume
						if ( is_dir( $dir_lv2 ) ) {

							//rename dir lv2 to slug
							$chapter_slug = $wp_manga_functions->unique_slug( $post_id, wp_basename( $dir_lv2 ) );
							$dir_slug_lv2   = $encrypt_dir ? $this->get_uniq_dir_slug( $dir_lv2 ) : basename($dir_lv2);
							$rename_dir_lv2 = $rename_dir_lv1 . '/' . $dir_slug_lv2;
							
							if($encrypt_dir) rename( $dir_lv2, $rename_dir_lv2 );

							if ( $storage == 'local' ) {
								//move to original manga directory
								$chapter_path      = $final_extract . '/' . $dir_slug_lv2;
								$response          = $this->move_chapter_dir( $rename_dir_lv2, $chapter_path );
								$extract_uri_final = $extract_uri;

								if ( $response == false ) {
									wp_send_json_error( __( 'Access denied! Please check directory permission', WP_MANGA_TEXTDOMAIN ) );
								}

							} else {
								$chapter_path      = $rename_dir_lv2;
								$extract_uri_final = $rename_dir_lv1_uri . '/' . $dir_slug_lv2;
							}

							//By now, dir lv1 is volume. Then check if this volume is already existed or create a new one
							$this_volume = $wp_manga_volume->get_volumes( array(
								'post_id'     => $post_id,
								'volume_name' => wp_basename( $dir_lv1 ),
							) );

							if ( $this_volume == false ) {
								$this_volume = $this->create_volume( wp_basename( $dir_lv1 ), $post_id );
							} else {
								$this_volume = $this_volume[0]['volume_id'];
							}

							//upload each chapter dir
							$chapter_name = wp_basename( $dir_lv2 );
							$chapter_name_extend = '';
							
							$name_parts = explode('--',$chapter_name);
							if(count($name_parts) == 2){
								$chapter_name = trim($name_parts[0]);
								$chapter_name_extend = trim($name_parts[1]);
							}
							
							$result[ $chapter_name ] = $this->wp_manga_upload_single_chapter( array(
								'post_id'             => $post_id,
								'volume_id'           => $this_volume,
								'chapter_name'        => $chapter_name,
								'chapter_name_extend' => $chapter_name_extend,
								'chapter_slug'        => $this->slugify($chapter_name),
								'chapter_metas' => serialize(array('uid' => $dir_slug_lv2))
							), $chapter_path, $extract_uri_final, //$chapter_uri
								$storage );
                                
							//confirm dir level 1 is volume
							$is_volume = true;
						}

					}

					if ( $is_volume !== true ) {
						//if dir level 1 is chapter
						if ( $storage == 'local' ) {
							$chapter_path      = $final_extract . '/' . $dir_slug_lv1;
							$response          = $this->move_chapter_dir( $rename_dir_lv1, $chapter_path );
							$extract_uri_final = $extract_uri;
						} else {
							$chapter_path      = $extract . '/' . $dir_slug_lv1;
							$extract_uri_final = $rename_dir_lv1_uri;
						}
						
						$chapter_name = wp_basename( $dir_lv1 );
						$chapter_name_extend = '';
						
						$name_parts = explode('--',$chapter_name);
						if(count($name_parts) == 2){
							$chapter_name = trim($name_parts[0]);
							$chapter_name_extend = trim($name_parts[1]);
						}

						$result[ $chapter_name ] = $this->wp_manga_upload_single_chapter( array(
							'post_id'             => $post_id,
							'volume_id'           => $volume_id,
							'chapter_name'        => $chapter_name,
							'chapter_name_extend' => $chapter_name_extend,
							'chapter_slug'        => $this->slugify($chapter_name),
							'chapter_metas' => serialize(array('uid' => $dir_slug_lv1))
						), $chapter_path, $extract_uri_final, $storage );

					}
				}
			}

			do_action( 'wp_manga_upload_completed', $result, $post_id, $extract, $extract_uri, $storage );

			$this->local_remove_storage( $extract );

			if ( ! empty( $result ) ) {
				return array(
					'success' => true,
					'message' => __( 'Upload Complete!', WP_MANGA_TEXTDOMAIN ),
				);
			} else {
				return array(
					'success' => false,
					'message' => __( 'This zip file is invalid for uploading manga', WP_MANGA_TEXTDOMAIN ),
				);
			}
		}

		function flag_upload_start( $post_id, $extract ) {

			if ( strpos( $extract, WP_MANGA_EXTRACT_DIR ) !== false ) {
				set_transient( 'temp_dir_uploading', true, ini_get( 'max_execution_time' ) );
			}

		}

		function flag_upload_end( $result, $post_id, $extract, $extract_uri, $storage ) {

			if ( strpos( $extract, WP_MANGA_EXTRACT_DIR ) !== false ) {
				delete_transient( 'temp_dir_uploading' );
			}

		}

		function get_uniq_dir_slug( $string ){
			return md5( $string . date( "Y-m-d H:i:s" ) );
		}

	}

	$GLOBALS['wp_manga_storage'] = new WP_MANGA_STORAGE();
