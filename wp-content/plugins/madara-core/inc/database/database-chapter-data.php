<?php

	class WP_DB_CHAPTER_DATA extends WP_MANGA_DATABASE {
        public $_chapter_data = array();

		private static $instance;

		public static function get_instance() {
			if ( null == self::$instance ) {
				self::$instance = new WP_DB_CHAPTER_DATA();
			}

			return self::$instance;
		}

		public function __construct() {

			parent::__construct();

			$this->table = $this->get_wpdb()->prefix . 'manga_chapters_data';
            $this->chapter_table = $this->get_wpdb()->prefix . 'manga_chapters';

		}

		/**
		 * Get all chapters data in single manga
		 */
		function get_manga_chapters_data( $post_id ){
			if(empty($post_id)){
				return false;
			} 
            
            if(isset($this->_chapter_data[$post_id])){
                return $this->_chapter_data[$post_id];
            }

			$cache_enabled = apply_filters('chapter_data_cache_enabled', true);
			if($cache_enabled){
				$cache_key = apply_filters('manga_chapters_data_cache_key', 'wp_manga_chapters_data_' . $post_id, $post_id);	
				$cache = wp_cache_get( $cache_key, 'wp_manga' );
				if( $cache !== false ){
					return $cache;
				}
			}
			
			// 
			// get all chapters data    
			$output = array();        
			$results = $this->select( array( 'post_id' => $post_id ) );

			if( !empty( $results ) ){
				foreach( $results as $storage ){

					$pages = json_decode( $storage['data'], true );
                    if(!is_array($pages)) $pages = [];					

                    if( ! isset( $output[ $storage['chapter_id'] ] ) || ! is_array( $output[ $storage['chapter_id'] ] ) ){
                        $output[ $storage['chapter_id'] ] = array(
                            'total_page' => apply_filters( 'manga_chapter_data_total_page', count( $pages ), $pages, $storage['chapter_id'], $post_id ),
                            'storage'    => array(
                                'inUse' => !empty( $storage['storage_in_use'] ) ? $storage['storage_in_use'] : $storage['storage']
                            )
                        );
                    }

                    $output[ $storage['chapter_id'] ]['storage'][ $storage['storage'] ] = array(
                        'host' => $storage['storage'] === 'local' ? WP_MANGA_DATA_URL : '',
                        'page' => $pages
                    );

				}

				if( !empty( $output ) ){
                    $this->_chapter_data[$post_id] = $output;
				}
			}

			if($cache_enabled){
				$cache_key = apply_filters('manga_chapters_data_cache_key', 'wp_manga_chapters_data_' . $post_id, $post_id);	
				wp_cache_set( $cache_key, $output, 'wp_manga', 3600 ); // set cache for 1 hour
			}
			
			return $output;
		}

		/**
		 * Get single chapter data
         * @return array(
                    'total_page' => int,
                    'storage' => array(
                                'inUse' => string,
                                $storage => array(
                                            'host' => string,
                                            'page' => array(
                                                            ['src' => string,
                                                             'mime' => string]
                                                        )
                                        )
                            )
                )
		 */
		function get_manga_chapter_data( $chapter_id, $post_id = null ){
			$cache_enabled = apply_filters('chapter_data_cache_enabled', true);

			if($cache_enabled){
				$cache_key = apply_filters('chapter_data_cache_key', 'wp_manga_single_chapter_data_' . $chapter_id, $chapter_id);	
				$cache = wp_cache_get( $cache_key, 'wp_manga' );
				if( $cache !== false ){					
					return $cache;
				}
			}

			$results = $this->select( array(
				'chapter_id' => $chapter_id,
				'post_id'    => $post_id
			) );
            
			$output = array();

			if( !empty( $results ) ){
				
				foreach( $results as $storage ){
					$pages = json_decode( $storage['data'], true );
					
					$validate_pages = array();
					// flag to check if we need to update images link (for google photos)
					$need_update = false;
					$pages_clone = $pages;

					if(!empty($pages)){
						foreach($pages as $key => $link){
							// exception: Google Photos
							if(strpos($link['src'], 'googleusercontent.com') !== false || strpos($link['src'], '=w-h') !== false || strpos($link['src'], '#') == 0){
								// check if link expired (Google Photos link expires every 60 minutes)
								
								if(($pos = strpos($link['src'], '#')) !== false){
									
									$data = substr($link['src'], $pos + 1);
									
									$pos = strrpos($data,'-');
									$item_id = substr($data, 0, $pos);
									$timestamp = substr($data, $pos + 1);

									// every 55 minutes, we get new links
									if($item_id && intval($timestamp) + 55 * 60 < time()){ 
										
										$gphoto_storage = wp_manga_storage_gphotos::get_instance();
										$update_url = $gphoto_storage->get_item_URL($item_id);
										
										if($update_url != '#'){
											// save database
											$link['src'] = $update_url;
											$pages_clone[$key] = $link;
											$need_update = true;
										}
									}
								}
								array_push($validate_pages, $link);	
							} else {
								if(pathinfo($link['src'], PATHINFO_EXTENSION) != '') {						
									array_push($validate_pages, $link);				
								}		
							}						
						}
					}
					
					$storage_clone = array();
					$storage_clone['chapter_id'] = $storage['chapter_id'];
					$storage_clone['storage'] = $storage['storage'];
					$storage_clone['data'] = json_encode($pages_clone);
					
					if(count($results) == 1 && $storage['storage_in_use'] != $storage['storage']){
						// fix storage
						$storage['storage_in_use'] = $storage['storage'];
					}
					
					if($need_update){
						global $wp_manga_database;
						
						$wp_manga_database->update($this->table, $storage_clone, array('data_id' => $storage['data_id']));
					}
					
					$pages = $validate_pages;

					if( ! isset( $output[ 'total_page' ] ) ){
						$output[ 'total_page' ] = apply_filters( 'manga_chapter_data_total_page', count( $pages ), $pages, $chapter_id, $post_id );
					}

					if( ! isset( $output['storage']['inUse'] ) ){
						$output['storage']['inUse'] = !empty( $storage['storage_in_use'] ) ? $storage['storage_in_use'] : $storage['storage'];
					}

                    $pages = apply_filters('wp_manga_chapter_images_data', $pages);
                    
                    $output['storage'][ $storage['storage'] ] = array(
                        'host' => $storage['storage'] === 'local' ? WP_MANGA_DATA_URL : '',
                        'page' => $pages,
                    );

				}
			}

			if($cache_enabled){
				$cache_key = apply_filters('chapter_data_cache_key', 'wp_manga_single_chapter_data_' . $chapter_id, $chapter_id);
				wp_cache_set( $cache_key, $output, 'wp_manga', 3600 ); // set cache for 1 hour
			}			

			return $output;
		}

		/**
		 * Get data of specific storage of single chapter
		 */
		function get_manga_chapter_storage_data( $chapter_id, $storage ){

			$data = $this->select(
				array(
					'chapter_id' => $chapter_id,
					'storage'    => $storage
				),
				array(
					'data'
				)
			);

			if( !empty( $data[0]['data'] ) ){
				return json_decode( $data[0]['data'], true );
			}

			return false;

		}

		/**
		 * Return chapter available storages with its data
		 */
		function get_chapter_storages( $chapter_id ){
			$results = $this->select(
				array(
					'chapter_id' => $chapter_id,
				),
				array(
					'storage',
					'data'
				)
			);

			if( !empty( $results ) ){
				$output = array();
				foreach( $results as $result ){
					$output[ $result['storage'] ] = json_decode( $result['data'], true );
				}
				return $output;
			}

			return false;
		}

		/**
		 * Custom select results fom chapter data table
		 */
		function select( $where = array(), $selects = array() ){

			if( empty( $where ) || ! is_array( $where ) ){
				return false;
			}

			if( !empty( $selects ) && is_array( $selects ) ){
				$selects = implode( ', ', $selects );
			}else{
				$selects = '*';
			}

			$conditions = array();
			foreach ( $where as $name => $value ) {

				if( empty( $value ) ){
					continue;
				}

				if( !empty( $where['post_id'] ) ){
					// make sure the column name came from correct table on join query
					if( isset( $this->table_cols[ $name ] ) ){
						$name = "D.{$name}";
					}elseif( isset( $this->chapter_table_cols[ $name ] ) ){
						$name = "C.{$name}";
					}
				}

				if( is_numeric( $value ) ){
					$conditions[] = "$name = $value";
				}else{
					$value = addslashes( $value );
					$conditions[] = "$name = '$value'";
				}
			}

			if( !empty( $conditions ) ){
				$sql_where = implode( ' AND ', $conditions );
			}

			if( isset( $where['post_id'] ) ){
				$sql = "SELECT {$selects}
						FROM {$this->chapter_table} as C
						JOIN {$this->table} as D
						ON D.chapter_id = C.chapter_id";
				if( !empty( $sql_where ) && strpos($sql_where, '.chapter_id') === false){
					$sql_where = str_replace('chapter_id', 'C.chapter_id', $sql_where);
				}
			}else{
				$sql = "SELECT {$selects}
						FROM {$this->table}";
			}

			if( !empty( $sql_where ) ){
				$sql .= " WHERE $sql_where";
			}

			if( !empty( $chapter_id ) ){
				if( is_array( $chapter_id ) ){
					$sql .= " AND C.chapter_id IN %s";
					$chapter_id = '(' . implode( ',', $chapter_id ) . ')';
				}else{
					$sql .= " AND C.chapter_id = %d";
				}
			}

			$results = $this
			->get_wpdb()
			->get_results(
				$sql,
				'ARRAY_A'
			);

			if( !empty( $results ) ){
				return $results;
			}

			return false;

		}

        function update( $update, $args, $dummy_arg = null ) {

			return parent::update( $this->table, $update, $args );

		}

		function delete( $args, $dummy_arg = null ) {

			return parent::delete( $this->table, $args );

		}

        function insert( $args, $dummy_arg = null ){

            if( empty( $args['chapter_id'] ) || empty( $args['data'] ) || empty( $args['storage'] ) ){
                return false;
            }

			// find if this chapter already has this storage data record
			$record = $this->select(
				array(
					'chapter_id' => $args['chapter_id'],
					'storage'    => $args['storage']
				),
				array(
					'data_id'
				)
			);

			if( empty( $record ) ){ // if update failed since there is no record
				return parent::insert( $this->table, $args );
			}else{
				return $this->update(
					array(
						'data'       => $args['data']
					),
					array(
						'data_id' => $record[0]['data_id']
					)
				);
			}

            return true;

        }

        function delete_chapter_data( $args ){

            if( !empty( $args['chapter_id'] ) ){
                return $this->delete( array(
                    'chapter_id' => $args['chapter_id']
                ) );
            }elseif( !empty( $args['post_id'] ) ){
                return $this->get_wpdb()->query(
                    $this->get_wpdb()->prepare(
                        "DELETE {$this->table}
						FROM {$this->table} as D
                        JOIN {$this->chapter_table} as C
                        ON D.chapter_id = C.chapter_id
                        WHERE C.post_id = %d",
                        $args['post_id']
                    )
                );
            }

        }


	}

	$GLOBALS['wp_manga_chapter_data'] = WP_DB_CHAPTER_DATA::get_instance();