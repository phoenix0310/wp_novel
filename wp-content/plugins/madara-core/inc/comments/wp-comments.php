<?php

    class WP_MANGA_COMMENTS{

        function __construct(){
            add_action('pre_get_comments', array( $this, 'filter_get_comments' ), 1000);
            add_action('wp_insert_comment', array( $this, 'filter_save_comments' ) );
            add_action('comment_form', array( $this, 'chapter_comment_field' ) );

            add_filter('comment_post_redirect', array( $this, 'redirect_after_comment' ) );
            add_filter('get_comments_number', array( $this, 'manga_get_comments_number' ) );

            add_filter( 'comment_post_redirect', array( $this, 'custom_comment_post_redirect' ), 10, 2 );

			add_filter( 'manage_edit-comments_columns', array( $this, 'admin_comment_columns' ));
			add_filter( 'manage_comments_custom_column', array( $this, 'admin_comment_chapter_content'), 10, 2 );
        }
		
		function admin_comment_columns( $columns )
		{
			$columns['chapter'] = esc_html__( 'Chapter', 'madara' );
			return $columns;
		}
		
		function admin_comment_chapter_content( $column, $comment_ID )
		{
			if ( 'chapter' == $column ) {
                $chapter_comment = get_comment_meta( $comment_ID, 'chapter_id', true);
                
				if($chapter_id = $chapter_comment){
					global $wp_manga_chapter;
					$chapter = $wp_manga_chapter->get_chapter_by_id(null, $chapter_id);
					
					if($chapter){
						global $wp_manga_functions;
						$url = $wp_manga_functions->build_chapter_url($chapter['post_id'], $chapter);
						echo '<a href="' . esc_url($url) . '" target="_blank">'. $chapter['chapter_name'] .'</a>';
					}
				}
			}
		}

        function comment_get_chapter_id(){
            global $wp_manga_functions;

            if( $wp_manga_functions->is_manga_single() ){
                return 0;
            } else {
				$reading_chapter = madara_permalink_reading_chapter();
                
				if($reading_chapter){
					return $reading_chapter['chapter_id'];
				}
            }

            return false;
        }

        function filter_get_comments( $comments_query ){
            global $IN_WIDGET_COMMENTS;
            if(isset($IN_WIDGET_COMMENTS) && $IN_WIDGET_COMMENTS){ 
                return;
            }

            // get current chapter if we are in the Chapter Reading page
            if ( wp_doing_ajax() ) {
                $chapter_id = isset($_POST['chapter_id']) ? $_POST['chapter_id'] : false;
            } else {
                $chapter_id = $this->comment_get_chapter_id();
            }
            
            if( $chapter_id === false ){
                return;
            }
            
            $meta_query = $comments_query->query_vars['meta_query'];            

            $new_meta_query = array(
                'relation'    => 'OR'
            );
            
            if(!$chapter_id){
                $new_meta_query[] = array(
                            'key'     => 'chapter_id',
                            'compare' => 'NOT EXISTS'
                        );
            }
            
            $new_meta_query[] = 
                array(
                    'key'     => 'chapter_id',
                    'value'   => $chapter_id,
                );
            
            if($chapter_id == 0){
                $new_meta_query[] = 
                    array(
                        'key'     => 'chapter_id',
                        'value'   => '',
                    );
            }
            
            if($meta_query){
                $meta_query = array_merge( $meta_query, $new_meta_query );
            } else {
                $meta_query = $new_meta_query;  
            }
            
            $comments_query->query_vars['meta_query'] = $meta_query;
			
        }

        function filter_save_comments( $comment_id ){

            $chapter_id = isset( $_REQUEST['wp_manga_chapter_id'] ) ? $_REQUEST['wp_manga_chapter_id'] : null;

            if( $chapter_id === null ){
                return;
            }

            update_comment_meta( $comment_id, 'chapter_id', $chapter_id );

        }

        function chapter_comment_field(){

            $chapter_id = $this->comment_get_chapter_id();

            if( $chapter_id === false ){
                return;
            }

            ?>
                <input type="hidden" name="wp_manga_chapter_id" value="<?php echo esc_attr( $chapter_id ); ?>">
            <?php
        }

        function redirect_after_comment($location){
            return $_SERVER["HTTP_REFERER"];
        }

        function manga_get_comments_number(){

            $comments = get_comments( array( 'post_id' => get_the_ID() ) );

            return count( $comments );
        }

        function custom_comment_post_redirect( $url, $comment ){

            if(
                isset( $comment->comment_post_ID )
                && get_post_type( $comment->comment_post_ID ) === 'wp-manga'
                && strpos( $url, '#' ) === false
            ){
                $url .= '#div-comment-' . $comment->comment_ID;
            }

            return $url;

        }

    }

    $GLOBALS['wp_manga_comments'] = new WP_MANGA_COMMENTS();
