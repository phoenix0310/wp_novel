<?php

    /**
     * Helper class for upgrading Manga plugin
     */
    class WP_MANGA_UPGRADE {

        public function __construct(){
			$current_version = $this->get_latest_version();
            if( $current_version < WP_MANGA_VER ){
                if( $current_version < 1.5){
					$this->update_to_1_5();
                    $current_version = 1.5;
                }
                
				if( $current_version < 1.503 ){
					$this->update_to_1_5_0_3();
					$this->update_latest_version( 1.503 );
                    $current_version = 1.503;
				}
                
				if($current_version < 1.514){
					$this->update_to_1_5_1_4();
					$this->update_latest_version( 1.514 );
                    $current_version = 1.514;
				}
                
				if($current_version < 1.53){
					$this->update_to_1_5_3();
					$this->update_latest_version( 1.53 );
                    $current_version = 1.53;
				}
                
				if($current_version < 1.533){
					$this->update_to_1_5_3_3();
					$this->update_latest_version( 1.533 );
                    $current_version = 1.533;
				}
                
				if($current_version < 1.55){
                    $this->update_to_1_5_5();
					$this->update_latest_version( 1.55 );
                    $current_version = 1.55;
				}
                
				if($current_version < 1.615){
					$this->update_to_1_6_1_5();
					$this->update_latest_version( 1.615 );
                    $current_version = 1.615;
				}
                
                if($current_version < 1.7){
					$this->upgrade_to_1_7();
					$this->update_latest_version( 1.7 );
                    $current_version = 1.7;
				}

                if($current_version < 2){
					$this->upgrade_to_2();
					$this->update_latest_version( 2 );
                    $current_version = 2.0;
				}

                if($current_version < 2.2){
                    $this->upgrade_to_22();  
                }

                if($current_version < 2.26){
                    $this->upgrade_to_226();  
                }
                
                $this->update_latest_version( WP_MANGA_VER);
            } elseif( get_transient( 'wp_manga_upgrading_completed' ) ){

                add_action( 'admin_notices', function(){
                    ?>
                    <div class="notice notice-success is-dismissible">
                        <p><?php _e( 'Madara - Core upgraded successfully!', 'sample-text-domain' ); ?></p>
                    </div>
                    <?php
                    delete_transient( 'wp_manga_upgrading_completed' );
                } );
				$this->update_latest_version( get_transient('wp_manga_upgrading_version') );
            } else {
				if($current_version != WP_MANGA_VER){
					$this->update_latest_version( WP_MANGA_VER );
				} else {
                    $is_search_text_built = get_option('wp_manga_search_text_built', 0);
                    if(!$is_search_text_built){
                        $ignore = get_option('wp_manga_search_text_built_ignore', 0);
                        if(!$ignore){
                            add_action( 'admin_notices', function(){
                                if(get_current_screen()->base !== 'wp-manga_page_wp-manga-legacy'){
                                ?>
                                <div class="notice notice-error">
                                    <p><?php printf( __( '<p><strong>IMPORTANT UPDATE : </strong> If you have just updated to a WP Manga Core 1.7+ version, make sure you have built the Search Text. It helps to improve the performance of Search. <a href="%s" class="button button-primary">Click here to start</a>', WP_MANGA_TEXTDOMAIN ), admin_url( 'edit.php?post_type=wp-manga&page=wp-manga-legacy') ); ?></p>
                                </div>
                                <?php
                                }
                            } );
                        }
                    }
                }
			}
        }

        private function upgrade_to_226(){
            $wp_manga_database = WP_MANGA_DATABASE::get_instance();
            $wpdb = $wp_manga_database->get_wpdb();
            
            if(!$wp_manga_database->column_exists($wpdb->prefix . 'manga_chapters', 'chapter_sort')){                
                $sql = "ALTER TABLE `{$wpdb->prefix}manga_chapters` ADD `chapter_sort` float NULL AFTER `chapter_index`";
                $wp_manga_database->get_wpdb()->query($sql);
            }
        }

        private function upgrade_to_22(){
            $madaradb = WP_MANGA_DATABASE::get_instance();
            
            // add index for manga_chapters table
            $indexs = array(
                'index_4' => '(`chapter_slug`(100))', 
                'index_5' => '(date_gmt)',
                'index_6' => '(volume_id)'
            );

            $table_name = $madaradb->get_wpdb()->prefix . 'manga_chapters';

            foreach($indexs as $key => $val){
                if(!$madaradb->is_index_exists($table_name, $key)){
                    // create index
                    $sql = "CREATE INDEX $key ON $table_name $val";
                    $madaradb->get_wpdb()->query($sql);
                }
            }

            // add index for manga_chapters_data table
            $indexs = array(
                'index_2' => '(storage)', 
                'index_3' => '(data(100))'
            );

            $table_name = $madaradb->get_wpdb()->prefix . 'manga_chapters_data';

            foreach($indexs as $key => $val){
                if(!$madaradb->is_index_exists($table_name, $key)){
                    // create index
                    $sql = "CREATE INDEX $key ON $table_name $val";
                    $madaradb->get_wpdb()->query($sql);
                }
            }

            // add index for manga_volumes table
            $indexs = array(
                'index_2' => '(post_id, date_gmt)', 
                'index_3' => '(volume_name(100))'
            );

            $table_name = $madaradb->get_wpdb()->prefix . 'manga_volumes';

            foreach($indexs as $key => $val){
                if(!$madaradb->is_index_exists($table_name, $key)){
                    // create index
                    $sql = "CREATE INDEX $key ON $table_name $val";
                    $madaradb->get_wpdb()->query($sql);
                }
            }
        }

        private function upgrade_to_2(){
            
            $db = WP_MANGA_DATABASE::get_instance();
            $wpdb = $db->get_wpdb();

            if(!$db->column_exists($wpdb->prefix . 'manga_chapters', 'views')){
                $db->alter_add_column($wpdb->prefix . 'manga_chapters', 'views', 'int');
            }
        }
        
        private function upgrade_to_1_7(){
            $wp_manga_database = WP_MANGA_DATABASE::get_instance();
            $wpdb = $wp_manga_database->get_wpdb();
            
            if(!$wp_manga_database->column_exists($wpdb->posts, 'wp_manga_search_text')){                
                $sql = "ALTER TABLE `{$wpdb->prefix}posts`  ADD `wp_manga_search_text` LONGTEXT NULL  AFTER `post_title`,
                  CHANGE `post_date` `post_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  CHANGE `post_date_gmt` `post_date_gmt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  CHANGE `post_modified` `post_modified` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  CHANGE `post_modified_gmt` `post_modified_gmt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;";
                // Note: due to sample Madara data, default DATETIME value of date columes in _posts table has '00:00:00' value, which will prevent alter table. We need to update default value of these columns at the same time
                
                $wp_manga_database->get_wpdb()->query($sql);
            }
            
            // update adult_content_value once
            $all_mangas = get_posts(['post_type' => 'wp-manga', 'post_status' => 'all', 'posts_per_page' => -1]);   
            $ids = [];
            foreach($all_mangas as $manga){
                $ids[] = $manga->ID;
            }
            
            $sql = "SELECT post_id from {$wpdb->prefix}postmeta WHERE meta_key='manga_adult_content'";
            $results = $wpdb->get_results($sql);
            $ids_adult = array_map(function($item){return $item->post_id;}, $results);
            $ids = array_diff($ids, $ids_adult);
            
            $chunks = array_chunk($ids, 100);
            foreach($chunks as $chunk){
                $values_str = implode(",'manga_adult_content', ''),(", $chunk);
                $values_str =  "(" .$values_str . ",'manga_adult_content', '')";
                
                $sql = "INSERT INTO {$wpdb->prefix}postmeta(post_id, meta_key, meta_value) VALUES {$values_str}";
                
                $wpdb->query($sql);
            }
            
            // just re-save permalinks once
			add_action('wp', function(){
				flush_rewrite_rules();
			});
        }
		
		private function update_to_1_5_5(){
			$wp_manga_database = WP_MANGA_DATABASE::get_instance();
			
			$table_name      = $wp_manga_database->get_wpdb()->prefix . 'manga_volumes';
                
			if(!$wp_manga_database->column_exists($table_name, 'volume_index')){
				$wp_manga_database->alter_add_column($table_name, 'volume_index', 'int default 0');
			}
			
            
			$table_name      = $wp_manga_database->get_wpdb()->prefix . 'manga_chapters';
			if(!$wp_manga_database->column_exists($table_name, 'chapter_status')){
				$wp_manga_database->alter_add_column($table_name, 'chapter_status', 'tinyint default 0');
			}
		}
		
		private function update_to_1_5_3(){
			// just re-save permalinks once
			add_action('wp', function(){
				flush_rewrite_rules();
			});
		}
		
		private function update_to_1_5(){
			$unconverted_posts = $this->get_unconverted_posts( array( 'posts_per_page' => 1 ) );

			if( $unconverted_posts->have_posts() ){
				if( ! isset( $_GET['page'] ) || $_GET['page'] !== 'madara-core-upgrading' ){
					// Notify user to upgrade data
					add_action( 'admin_notices', function(){
						?>
						<div class="notice notice-error">
							<p><?php printf( __( '<p><strong>IMPORTANT UPDATE : </strong> We will need to update database to make your site compatible with Madara-Core 1.5.</p> <a href="%s" class="button button-primary">Click here to start</a>', WP_MANGA_TEXTDOMAIN ), admin_url( '?page=madara-core-upgrading') ); ?></p>
						</div>
						<?php
					} );
				}

				add_action( 'admin_menu', function(){
					add_submenu_page(
						null,
						__( 'Madara - Core upgrading...', WP_MANGA_TEXTDOMAIN ),
						__( 'Madara - Core upgrading...', WP_MANGA_TEXTDOMAIN ),
						'manage_options',
						'madara-core-upgrading',
						function(){
							$this->upgrade_page();
						}
					);
				} );

				add_action( 'wp_ajax_madara_core_convert_post', array( $this, 'convert' ) );
				add_action( 'wp_ajax_madara_core_convert_post_end', array( $this, 'end' ) );
			} else {
				$this->update_latest_version( 1.503 );
			}
		}
		
		/**
		 * Upgrade DB to 1.5.0.3 version
		 **/
		private function update_to_1_5_0_3(){
			$wp_manga_database = WP_MANGA_DATABASE::get_instance();
			
			$index_manga_chapters = array('manga_chapter_index_1' => '(`post_id`)', 'manga_chapter_index_2' => '(`post_id`,`chapter_id`)','manga_chapter_index_3' => '(`post_id`,`chapter_slug`(100))');
			
			$index_manga_chapters_data = array('manga_chapter_data_index_2' => '(`chapter_id`,`storage`)');
			
			$table_name      = $wp_manga_database->get_wpdb()->prefix . 'manga_chapters';
			foreach($index_manga_chapters as $key => $val){
				if(!$wp_manga_database->is_index_exists($table_name, $key)){
					// create index
					$sql = "CREATE INDEX $key ON $table_name $val";
					$wp_manga_database->get_wpdb()->query($sql);
				}
			}
			
			$table_name      = $wp_manga_database->get_wpdb()->prefix . 'manga_chapters_data';
			foreach($index_manga_chapters_data as $key => $val){
				if(!$wp_manga_database->is_index_exists($table_name, $key)){
					// create index
					$sql = "CREATE INDEX $key ON $table_name $val";
					$wp_manga_database->get_wpdb()->query($sql);
				}
			}
		}
		
		/**
		 * Upgrade DB to 1.5.1.4 version
		 **/
		private function update_to_1_5_1_4(){
			$wp_manga_database = WP_MANGA_DATABASE::get_instance();
			
			$table_name      = $wp_manga_database->get_wpdb()->prefix . 'manga_chapters';
			
			if(!$wp_manga_database->column_exists($table_name, 'chapter_index')){
				$wp_manga_database->alter_add_column($table_name, 'chapter_index', 'int default 0');
			}
		}
		
		/**
		 * Upgrade DB to 1.5.3.3 version
		 **/
		private function update_to_1_5_3_3(){
			$wp_manga_database = WP_MANGA_DATABASE::get_instance();
			
			$table_name      = $wp_manga_database->get_wpdb()->prefix . 'manga_chapters';
			
			if(!$wp_manga_database->column_exists($table_name, 'chapter_seo')){
				$wp_manga_database->alter_add_column($table_name, 'chapter_seo', 'varchar(1000)');
			}
			if(!$wp_manga_database->column_exists($table_name, 'chapter_warning')){
				$wp_manga_database->alter_add_column($table_name, 'chapter_warning', 'text	');
			}
		}
		
		private function update_to_1_6_1_5(){
			$wp_manga_database = WP_MANGA_DATABASE::get_instance();
			
			$table_name      = $wp_manga_database->get_wpdb()->prefix . 'manga_chapters';
			
			if(!$wp_manga_database->column_exists($table_name, 'chapter_metas')){
				$wp_manga_database->alter_add_column($table_name, 'chapter_metas', 'varchar(1000)');
				// this column saves serializeed data of Chapter Metas
			}
		}

        public function convert(){

            if( empty( $_POST['post'] ) ){
                wp_send_json_error();
            }

            // Flag upgrading start
            $this->start();

            global $wp_manga_functions, $wp_manga_database;

            $data = $wp_manga_functions->get_all_chapters( $_POST['post'] );

            if( $data && !empty( $data['chapters'] ) && is_array( $data['chapters'] ) ){
                // Chapter loop
                foreach( $data['chapters'] as $id => $chapter ){

                    if( !empty( $chapter['storage'] ) && is_array( $chapter['storage'] ) ){

                        // Storage loop
                        foreach( $chapter['storage'] as $storage => $storage_data ){

                            if( !empty( $storage_data['page'] ) ){
                                $wp_manga_database->insert(
                                    $wp_manga_database->get_wpdb()->prefix . 'manga_chapters_data',
                                    array(
                                        'chapter_id' => $id,
                                        'storage'    => $storage,
                                        'data'       => json_encode( $storage_data['page'] )
                                    )
                                );
                            }
                        }

                        if( !empty( $chapter['storage']['inUse'] ) ){
                            $wp_manga_database->update(
                                $wp_manga_database->get_wpdb()->prefix . 'manga_chapters',
                                array(
                                    'storage_in_use' => $chapter['storage']['inUse'],
                                ),
                                array(
                                    'chapter_id'     => $id,
                                )
                            );
                        }
                    }

                }
            }

            $this->add_convert_posts( $_POST['post'] );

            wp_send_json_success( get_the_title( $_POST['post'] ) );

        }

        public function upgrade_page(){

            $is_upgrading = $this->is_manga_upgrading();

            if( empty( $is_upgrading ) ){

                // Update database
                $this->upgrade_database();

                // Query all manga posts
                $mangas = $this->get_unconverted_posts();

                ?>

                    <style scoped="true">
                        /* WP MANGA CORE UPGRADE */
                        .wp-manga-upgrade .progress-bar .progress-bar-wrapper {
                            height: 50px;
                            background-color: #dadada;
                            margin-top: 50px;
                            border-radius: 11px;
                            overflow: hidden;
                            background-image: -webkit-linear-gradient(45deg,rgba(255,255,255,.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.15) 50%,rgba(255,255,255,.15) 75%,transparent 75%,transparent);
                            background-image: -o-linear-gradient(45deg,rgba(255,255,255,.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.15) 50%,rgba(255,255,255,.15) 75%,transparent 75%,transparent);
                            background-image: linear-gradient(45deg,rgba(255,255,255,.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.15) 50%,rgba(255,255,255,.15) 75%,transparent 75%,transparent);
                            -webkit-background-size: 40px 40px;
                            background-size: 40px 40px;
                            -webkit-animation: progress-bar-stripes 2s linear infinite;
                            -o-animation: progress-bar-stripes 2s linear infinite;
                            animation: progress-bar-stripes 2s linear infinite;
                        }

                        .wp-manga-upgrade .progress-bar .progress-bar-wrapper .loaded {
                            width: 0%;
                            height: 100%;
                            float: left;
                            background-color: #419254;
                            transition: all ease-in-out 0.5s;
                        }

                        .wp-manga-upgrade h1{
                            text-align: center;
                        }

                        .wp-manga-upgrade .found-posts .post{
                            display:none;
                        }

                        .wp-manga-upgrade .found-posts .post.completed{
                            display: block;
                        }

                        @keyframes progress-bar-stripes{
                            0% {
                                background-position: 40px 0;
                            }
                            100% {
                                background-position: 0 0;
                            }

                        }
                    </style>
                    <div class="wrap">

                        <h1><?php esc_html_e( 'Madara - Core Upgrading...', WP_MANGA_TEXTDOMAIN ); ?></h1>

                        <div class="wp-manga-upgrade">
                            <div class="wrapper">
                                <div class="progress-bar">
                                    <div class="progress-bar-wrapper">
                                        <div class="loaded">
                                        </div>
                                    </div>
                                </div>
                                <h1><span class="converted">0</span>/<span class="total"><?php echo $mangas->found_posts; ?></span></h1>
                            </div>
                            <div class="found-posts">
                                <?php foreach( $mangas->posts as $post ){ ?>
                                    <div class="post" data-post="<?php echo esc_attr( $post->ID ) ?>">
                                        <span></span>
                                    </div>
                                <?php } ?>
                            </div>

                            <input type="hidden" name="running" value="1">
                        </div>
                    </div>

                    <script type="text/javascript">
                        jQuery(function($){
                            $(document).ready(function(){

                                var running = true;

                                window.onbeforeunload = function() {
                                    if( running ){
                                        return "Are you sure you want to leave this page?";
                                    }
                                }

                                function convert_post(){

                                    var posts = $('.found-posts > .post:not(.completed)');

                                    if( posts.length > 0 ){

                                        var self = $( posts[0] );

                                        $.ajax({
                                            url : '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
                                            method : 'POST',
                                            data : {
                                                action : 'madara_core_convert_post',
                                                post : self.data( 'post' )
                                            },
                                            success : function( response ){
                                                if( response.success ){
                                                    self.find( 'span' ).html( response.data );
                                                    self.addClass( 'completed' );

                                                    var converted = $('.found-posts > .post.completed' ).length;
                                                    $('.wp-manga-upgrade .converted').text( converted );
                                                    $('.wp-manga-upgrade .progress-bar .progress-bar-wrapper .loaded').css( 'width', ( converted / <?php echo $mangas->found_posts; ?> ) * 100 + '%' );
                                                }

                                                convert_post();
                                            }
                                        });

                                    }else{

                                        $.ajax({
                                            url : '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
                                            method : 'POST',
                                            data : {
                                                action : 'madara_core_convert_post_end'
                                            },
                                            success : function( response ){
                                                if( response.success ){
                                                    window.location = '<?php echo esc_url( admin_url() ); ?>';
                                                }else{
                                                    alert( 'Something wrong happened, please try again.' );
                                                }
                                                $('.wp-manga-upgrade input[name="running"]').val("0");
                                            }
                                        });

                                    }
                                }

                                convert_post();

                            });
                        });
                    </script>

                <?php

            }elseif( $is_upgrading ){
                ?>
                <div class="wrap">
                    <h1><?php esc_html_e( 'The upgrade is progressing in other tab...', WP_MANGA_TEXTDOMAIN ); ?></h1>
                </div>
                <?php
            }else{
                ?>
                <div class="wrap">
                    <h1><?php esc_html_e( 'Upgraded successfully!', WP_MANGA_TEXTDOMAIN ); ?></h1>
                </div>
                <?php
            }

        }

        public function get_cur_ver(){
            $meta_data = get_file_data( WP_MANGA_DIR . '/wp-manga.php', array( 'Version' => 'Version' ), 'plugin');

            if( isset( $meta_data['Version'] ) ){
                return (float) $meta_data['Version'];
            }else{
                return false;
            }
        }

        public function update_latest_version( $version ){
            return update_option( 'wp_manga_latest_version', $version );
        }

        public function get_latest_version(){
            return get_option( 'wp_manga_latest_version', 1.4 );
        }

        public function is_manga_upgrading(){
            return get_option( 'wp_manga_upgrading', null );
        }

        public function start(){
            return update_option( 'wp_manga_upgrading', true );
        }

        public function end(){

            if( wp_doing_ajax() ){

                $del_upgrading       = delete_option( 'wp_manga_upgrading' );
                $del_converted_posts = delete_option( 'wp_manga_converted_posts' );

                if( $del_upgrading && $del_converted_posts ){
                    $this->update_latest_version( 1.5 );
                    set_transient( 'wp_manga_upgrading_completed', 1 );
                    wp_send_json_success();
                }else{
                    wp_send_json_error();
                }
            }else{
                return $resp;
            }
        }

        private function get_converted_posts(){
            return get_option( 'wp_manga_converted_posts' );
        }

        private function get_unconverted_posts( $args = array() ){

            return new WP_Query( array_merge(
                array(
                    'post_type'      => 'wp-manga',
                    'post_status'    => array( 'any' ),
                    'posts_per_page' => -1,
                    'post__not_in'   => $this->get_converted_posts()
                ),
                $args
            ) );

        }

        private function add_convert_posts( $post ){
            $converted_posts = $this->get_converted_posts();

            if( empty( $converted_posts ) || ! is_array( $converted_posts ) ){
                $converted_posts = array( $post );
            }elseif( ! in_array( $post, $converted_posts ) ){
                $converted_posts[] = $post;
            }

            return update_option( 'wp_manga_converted_posts', $converted_posts );
        }

        public function upgrade_database(){

            global $wp_manga_database;
            $wp_manga_database->wp_manga_create_db();

        }
    }

    $GLOBALS['wp_manga_upgrade'] = new WP_MANGA_UPGRADE();
