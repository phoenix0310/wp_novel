<?php
	#[AllowDynamicProperties]
	class WP_MANGA_DATABASE {

		public $wpdb;
		
		private static $instance;

		public static function get_instance() {
			if ( null == self::$instance ) {
				self::$instance = new WP_MANGA_DATABASE();
			}

			return self::$instance;
		}

		function __construct() {
			global $wpdb;
			$this->wpdb = $wpdb;

			register_activation_hook( WP_MANGA_FILE, array( $this, 'wp_manga_create_db' ) );

			$this->volume_table_cols = apply_filters( 'manga_volumes_table_columns', array(
				'volume_id'   => 'bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY',
				'post_id'     => 'bigint(20) NOT NULL',
				'volume_name' => 'text NOT NULL',
				'date'        => 'datetime DEFAULT "0000-00-00 00:00:00" NOT NULL',
				'date_gmt'    => 'datetime DEFAULT "0000-00-00 00:00:00" NOT NULL',
			) );

			$this->chapter_table_cols = apply_filters( 'manga_chapters_table_columns', array(
				'chapter_id'          => 'bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY',
				'post_id'             => 'bigint(20) NOT NULL',
				'volume_id'           => 'bigint(20) NULL',
				'chapter_name'        => 'text NOT NULL',
				'chapter_name_extend' => 'text NOT NULL',
				'chapter_slug'        => 'text NOT NULL',
				'storage_in_use'      => 'varchar(20) NULL',
				'date'                => 'datetime DEFAULT "0000-00-00 00:00:00" NOT NULL',
				'date_gmt'            => 'datetime DEFAULT "0000-00-00 00:00:00" NOT NULL',
			) );

			$this->chapter_data_table_cols = apply_filters( 'manga_chapters_data_table_column', array(
				'data_id'    => 'bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY',
				'chapter_id' => 'bigint(20) NOT NULL',
				'storage'    => 'varchar(20) NOT NULL',
				'data'       => 'longtext NOT NULL',
			) );
		}

		/**
		 * @return wpdb
		 */
		function get_wpdb(){
			if( empty( $this->wpdb ) ){
				global $wpdb;
				$this->wpdb = $wpdb;
			}

			return $this->wpdb;
		}

		function wp_manga_create_db() {
			$this->create_table( 'manga_volumes', $this->volume_table_cols );
			$this->create_table( 'manga_chapters', $this->chapter_table_cols, array('manga_chapter_index_1' => '(`post_id`)', 'manga_chapter_index_2' => '(`post_id`,`chapter_id`)','manga_chapter_index_3' => '(`post_id`,`chapter_slug`(100))') );
			$this->create_table( 'manga_chapters_data', $this->chapter_data_table_cols, array('manga_chapter_data_index_2' => '(`chapter_id`,`storage`)') );
            
            $wpdb = $this->get_wpdb();
            
            if(!$this->column_exists($wpdb->posts, 'wp_manga_search_text')){                
                $sql = "ALTER TABLE `{$wpdb->prefix}posts`  ADD `wp_manga_search_text` TEXT NULL,
							CHANGE `post_date` `post_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
							CHANGE `post_date_gmt` `post_date_gmt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
							CHANGE `post_modified` `post_modified` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
							CHANGE `post_modified_gmt` `post_modified_gmt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;";
                // Note: due to sample Madara data, default DATETIME value of date columes in _posts table has '00:00:00' value, which will prevent alter table. We need to update default value of these columns at the same time
                
                $this->get_wpdb()->query($sql);
            }
			
			require_once( WP_MANGA_DIR . '/inc/upgrade.php' );
		}

		function create_table( $name, $args, $indexs = array() ) {
			if ( ! is_array( $args ) || empty( $args ) ) {
				return false;
			}

			$charset_collate = $this->get_wpdb()->get_charset_collate();
			$table_name      = $this->get_wpdb()->prefix . $name;

			if( $this->table_exists( $table_name ) ){
				foreach( $args as $column => $data ){
					if( ! $this->column_exists( $table_name, $column ) ){
						$this->alter_add_column( $table_name, $column, $data );
					}
				}
			}else{
				$query_args = array();

				foreach( $args as $column => $data ){
					$query_args[] = "{$column} {$data}";
				}

				$sql = "CREATE TABLE $table_name (
		            " . implode( ', ', $query_args );
				
				if(count($indexs) > 0) {
					$str = '';
					foreach($indexs as $key => $val){
						$str .= ",INDEX $key $val";
					}
					$sql .= $str;
				};
				
		        $sql .= ") $charset_collate;";

				$this->maybe_create_table( $table_name, $sql );
			}
			
			if(count($indexs) > 0){
				foreach($indexs as $key => $val){
					if(!$this->is_index_exists($table_name, $key)){
						// create index
						$sql = "CREATE INDEX $key ON $table_name $val";
						$this->get_wpdb()->query($sql);
					}
				}
			}
		}
		
		public function is_index_exists($table_name, $index_name){
			global $wpdb;
			
			$sql = $wpdb->prepare("SHOW KEYS FROM " . $table_name . " WHERE Key_name=%s",
							$index_name
						);
						
			$results = $wpdb->get_row($sql);

			if($results == null){
				// index is not exist
				return false;
			}
			
			return true;
		}

		function maybe_create_table( $table_name, $create_ddl ) {

		    global $wpdb;

			if( $this->table_exists( $table_name ) ){
				return true;
			}

		    // Didn't find it try to create it..
		    $wpdb->query($create_ddl);

		    // We cannot directly tell that whether this succeeded!
			if( $this->table_exists( $table_name ) ){
				return true;
			}

		    return false;
		}

		function insert( $table, $args ) {
			$this->get_wpdb()->insert( $table, $args );

			if ( isset( $this->get_wpdb()->insert_id ) ) {
				return $this->get_wpdb()->insert_id;
			}

			return false;

		}

		/**
		 * $limit - LIMIT {OFFSET},{COUNT}
		 **/
		function get( $table, $where, $orderBy, $order, $limit = '' ) {
			global $wpdb;
			$is_legacy_sorting = get_option('wp_manga_sort_by_name_legacy', 0);

			$sort_setting = $this->get_sort_setting();

			$sort_by    = $sort_setting['sortBy'];
			$sort_order = $sort_setting['sort'];

			if( !empty( $orderBy ) ){
				$sort_by = $orderBy;
				$sort_order = !empty( $order ) ? $order : 'desc';
			}
			
			/**
			 * it's not always better for performance to enable the query cache, and at larger cache sizes it can actually be detrimental to performance as cache pruning (pushing less-used data out of memory to make way for new entries) takes longer. When invalidation and pruning take longer than the query takes to execute, you've got serious problems.
			 **/
			$is_cache_enabled = defined('WP_MANGA_QUERY_CACHE') ? "SQL_CACHE {$table}.*" : "{$table}.*";
			
			$is_cache_enabled = apply_filters('wp_manga_db_get_SELECT', $is_cache_enabled, $table, $where, $orderBy, $order, $limit);
			$table = apply_filters('wp_manga_db_get_TABLE', $table, $is_cache_enabled, $where, $orderBy, $order, $limit);
			$where = apply_filters('wp_manga_db_get_WHERE', $where, $table, $is_cache_enabled, $orderBy, $order, $limit);
			
			$sql_orderby = "";
			
			if( $sort_by == 'date' ){
				$sql = "
							SELECT {$is_cache_enabled}
							FROM $table
						";

				if( !empty( $where ) ){
					$sql .= "WHERE $where";
				}

				$sql_orderby = "
							ORDER BY $sort_by $sort_order
						";
			} elseif($sort_by == 'name'){
				$sql = "
							SELECT {$is_cache_enabled}
							FROM $table
						";

				if( !empty( $where ) ){
					$sql .= "WHERE $where";
				}

				if(!$is_legacy_sorting){
					if( strpos( $table, 'chapters' ) !== false ){
						$column_sort = $wpdb->prefix . 'manga_chapters.chapter_sort';
					}elseif( strpos( $table, 'volumes' ) !== false ){
						$column_sort = $wpdb->prefix . 'manga_chapters.volume_name';
					}

					$sql .= " ORDER BY $column_sort $sort_order,
										date $sort_order";
				}
			} elseif( $sort_by == 'volume_index' ){
				// for getting volume
				$sql = "
							SELECT {$is_cache_enabled}
							FROM $table
						";

				if( !empty( $where ) ){
					$sql .= "WHERE $where";
				}
				$sql_orderby = " ORDER BY volume_index $sort_order";
			} else {
				// $sort_by = 'index';
				$sql = "
							SELECT {$is_cache_enabled}
							FROM $table
						";

				if( !empty( $where ) ){
					$sql .= "WHERE $where";
				}
				$sql_orderby = " ORDER BY chapter_index $sort_order";
			}
			
			$sql .= apply_filters('wp_manga_db_get_ORDERBY', $sql_orderby, $is_cache_enabled, $table, $where, $orderBy, $order, $limit);
			if((!$is_legacy_sorting || $sort_by != 'name') && $limit != ""){
				$sql .= " $limit";
			}
			
			$sql = apply_filters('wp_manga_db_get_SQL', $sql, $table, $where, $orderBy, $order, $limit);

			$results = $this
			->get_wpdb()
			->get_results( $sql, 'ARRAY_A' );

			/**
			 * low performance
			 **/
			if( $results && $is_legacy_sorting && $sort_by == 'name' ){

				if( strpos( $table, 'chapters' ) !== false ){
					$column = 'chapter_name';
				}elseif( strpos( $table, 'volumes' ) !== false ){
					$column = 'volume_name';
				}
				
				if( isset( $column ) ){

					//bring column name to be key of results array
					$names = array_map(function($element) use($column){return $element[$column];}, $results);

					natcasesort( $names );

					//put appropiate values to sorted position
					$output_results = array();
					foreach( $names as $key => $name ){
						$output_results[] = $results[ $key ];
					}

					if( !empty( $sort_order ) && $sort_order == 'desc' ){
						$results = array_reverse( $output_results );
					}else{
						$results = $output_results;
					}
				}
				
				if(strpos( $table, 'chapters' ) !== false && $limit){
					$count = substr($limit, strrpos($limit, ',') + 1); 
					$offset = intval(str_replace("LIMIT ", "", trim(explode(",", $limit)[0])));
					
					$results = array_slice($results, intval($offset), $count);
				}
			}
			
			return $results;
		}

		function update( $table, $data, $where ) {
			$result = $this->get_wpdb()->update( $table, $data, $where );
			return $result;
		}

		function delete( $table, $where ) {

			return $this->get_wpdb()->delete( $table, $where );

		}

		function table_exists( $table_name ){

			$query = $this->get_wpdb()->prepare( "SHOW TABLES LIKE %s", $this->get_wpdb()->esc_like( $table_name ) );

		    if ( $this->get_wpdb()->get_var( $query ) == $table_name ) {
		        return true;
		    }

			return false;

		}

		function column_exists( $table_name, $column_name ){

			$query = $this->get_wpdb()->prepare(
				"SELECT *
				FROM INFORMATION_SCHEMA.COLUMNS
				WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s
				AND COLUMN_NAME = %s",
				DB_NAME,
				$table_name,
				$column_name
			);

		    return !empty( $this->get_wpdb()->query( $query ) );

		}

		function alter_add_column( $table_name, $column_name, $column_data ){
			$sql = "ALTER TABLE {$table_name}
			ADD COLUMN {$column_name} {$column_data}";
			return !empty( $this->get_wpdb()->query( $sql ) );

		}

		function get_sort_setting(){

			$sort_option = array(
								'sortBy' => 'name',
								'sort' => 'desc',
                                'vol_order' => 'desc'
							);

			return apply_filters('wp_manga_chapters_sort', $sort_option);
		}
        
        function update_search_text($manga_id, $search_text){
            // make sure we have wp_manga_search_text
            $is_search_text_available = get_option('wp_manga_search_text_built', 0);
			//echo $is_search_text_available;exit;
            if($is_search_text_available){
                $wpdb = $this->get_wpdb();
                
                $sql = "UPDATE {$wpdb->posts} SET wp_manga_search_text = %s WHERE ID = %d";
                
                $wpdb->query($wpdb->prepare($sql, $search_text, $manga_id));
            }
        }
	}

	$GLOBALS['wp_manga_database'] = WP_MANGA_DATABASE::get_instance();
