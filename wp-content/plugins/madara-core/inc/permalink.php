<?php

class WP_Manga_Permalink {
	private static $instance;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new WP_Manga_Permalink();
		}

		return self::$instance;
	}
	
	function __construct() {
		add_action( 'init', array( $this, 'wp_manga_rewrite_rules' ) );
		/*
		$options            = get_option( 'wp_manga_settings', array() );
		$manga_slug_or_id = isset( $options['manga_slug_or_id'] ) ? $options['manga_slug_or_id'] : 'slug';
		
		if($manga_slug_or_id == 'id'){
			add_filter(	'post_type_link',array( $this, 'post_type_link' ), 0,4);
			add_action( 'registered_post_type', array( $this, 'register_post_type_rules' ), 10, 2 );
		}*/
		
		add_filter('amp_get_permalink', array($this, 'amp_get_permalink'), 10, 2);
		add_filter('wp_manga-chapter-url', array($this, 'chapter_permalink'), 100, 6);
        
        add_filter('query_vars', function($vars){
            $vars[] = 'manga_ajax';
            $vars[] = 'maction';
            return $vars;
        });
        
        add_filter('get_comments_pagenum_link', array($this, 'filter_comments_page_link'));
	}
    
    /**
     * Fix the comments pagination link for chapter reading page
     **/
    function filter_comments_page_link( $link ){
        global $wp_rewrite;
        
        if ( $wp_rewrite->using_permalinks() ) {
            $result = get_permalink();
            $chapter = wp_manga_get_current_chapter();
            if($chapter){
                global $wp_manga_functions;
                $chapter_url = $wp_manga_functions->build_chapter_url(get_the_ID(), $chapter['chapter_slug']);
                $link = str_replace($result, $chapter_url, $link);
            }
        }
        
        return $link;
    }
	
	// change chapter permalink to AMP link when needed
	function chapter_permalink( $url, $post_id, $chapter_slug, $page_style, $host, $paged ){
		global $is_amp_required;
		if(isset($is_amp_required) && $is_amp_required){
			// if $url is not AMP link yet
			if(strpos($url, '/amp/') === false){
				// we assume that $url for chapter is always List Style
				$url = rtrim($url, '/\\') . '/amp/';
			}
		}
		
		return $url;
	}
	
	function amp_get_permalink($amp_url, $post_id){
		$chapter = madara_permalink_reading_chapter();
		
		if($chapter){
			global $wp_manga_functions, $wp_manga_setting;
			$chapter_url = $wp_manga_functions->build_chapter_url($post_id, $chapter);
			
			$manga_paged_var = $wp_manga_setting->get_manga_option('manga_paged_var', 'manga-paged');
			$page = get_query_var($manga_paged_var);
			
			$amp_url = $chapter_url . 'amp';
			if($page && $page != 1){
				$amp_url .= '/p/' . $page;
			}
		}
		
		return $amp_url;
	}
	
	function wp_manga_rewrite_rules() {
		$manga_post_type = get_post_type_object( 'wp-manga' );
		$slug            = $manga_post_type->rewrite['slug'];
		
		global $wp_manga_setting, $wp_rewrite;;
		$manga_paged_var = $wp_manga_setting->get_manga_option('manga_paged_var', 'manga-paged');

		//rewrite endpoint for chapter
		add_rewrite_endpoint( 'chapter', EP_PERMALINK );
		add_rewrite_endpoint( 'volume', EP_PERMALINK );
		add_rewrite_endpoint( $manga_paged_var, EP_PERMALINK );
        
        $language_code = [];
        $hide_default_language = true;
        
        if(function_exists('PLL')){
            $polylang_options = PLL()->options;
            
            if($polylang_options['force_lang']){
                $current_theme = wp_get_theme();    
				if(isset($polylang_options['nav_menus'])){					
					$language_code = array_keys($polylang_options['nav_menus'][$current_theme->template]['primary_menu']);
				}
            }
            
            $hide_default_language = $polylang_options['hide_default'];
        }
		
        // for front-end ajax requests
        add_rewrite_tag('%manga_ajax%', '([0-9]+)'); 
        add_rewrite_tag('%maction%', '([^/]+)');
		
        if(count($language_code)){
            foreach($language_code as $lang){
                // /{manga-slug}/{vol-slug}/{chapter-slug}/amp/
                add_rewrite_rule( "{$lang}/{$slug}/([^/]+)/([^/]+)/([^/]+)/amp/?$", 'index.php?manga-core=$matches[1]&volume=$matches[2]&chapter=$matches[3]&amp', 'top' );
                // /{manga-slug}/{vol-slug}/{chapter-slug}/amp/p/{page}
                add_rewrite_rule( "{$lang}/{$slug}/([^/]+)/([^/]+)/([^/]+)/amp/p/([^/]+)?$", 'index.php?manga-core=$matches[1]&volume=$matches[2]&chapter=$matches[3]&'.$manga_paged_var.'=$matches[4]&amp', 'top' );
                // /{manga-slug}/{chapter-slug}/amp/
                add_rewrite_rule( "{$lang}/{$slug}/([^/]+)/([^/]+)/amp/?$", 'index.php?manga-core=$matches[1]&chapter=$matches[2]&amp', 'top' );
                // /{manga-slug}/{chapter-slug}/amp/p/{page}
                add_rewrite_rule( "{$lang}/{$slug}/([^/]+)/([^/]+)/amp/p/([^/]+)?$", 'index.php?manga-core=$matches[1]&chapter=$matches[2]&'.$manga_paged_var.'=$matches[3]&amp', 'top' );
                // /{manga-slug}/{chapter-slug}/comment-{page}/
                add_rewrite_rule( "{$lang}/{$slug}/([^/]+)/([^/]+)/" . $wp_rewrite->comments_pagination_base . "-([^/]+)/?$", 'index.php?manga-core=$matches[1]&chapter=$matches[2]&cpage=$matches[3]', 'top' );
                // /{manga-slug}/{vol-slug}/{chapter-slug}/comment-{page}
                add_rewrite_rule( "{$lang}/{$slug}/([^/]+)/([^/]+)/([^/]+)/" . $wp_rewrite->comments_pagination_base . "-([^/]+)/?$", 'index.php?manga-core=$matches[1]&volume=$matches[2]&chapter=$matches[3]&cpage=$matches[4]', 'top' );
                // /{manga-slug}/{vol-slug}/{chapter-slug}/p/{page}
                add_rewrite_rule( "{$lang}/{$slug}/([^/]+)/([^/]+)/([^/]+)/p/([^/]+)?$", 'index.php?manga-core=$matches[1]&volume=$matches[2]&chapter=$matches[3]&'.$manga_paged_var.'=$matches[4]', 'top' );
                // /{manga-slug}/{vol-slug}/{chapter-slug}/
                add_rewrite_rule( "{$lang}/{$slug}/([^/]+)/([^/]+)/([^/]+)/?$", 'index.php?manga-core=$matches[1]&volume=$matches[2]&chapter=$matches[3]', 'top' );
                
                // /{manga-slug}/{chapter-slug}/p/{page}
                add_rewrite_rule( "{$lang}/{$slug}/([^/]+)/([^/]+)/p/([^/]+)?$", 'index.php?manga-core=$matches[1]&chapter=$matches[2]&'.$manga_paged_var.'=$matches[3]', 'top' );
                // /{manga-slug}/amp/
                add_rewrite_rule( "{$lang}/{$slug}/([^/]+)/amp/?$", 'index.php?manga-core=$matches[1]&amp', 'top' );
                
                // /{manga-slug}/comment-{page}/
                add_rewrite_rule( "{$lang}/{$slug}/([^/]+)/" . $wp_rewrite->comments_pagination_base . "-([^/]+)/?$", 'index.php?manga-core=$matches[1]&cpage=$matches[2]', 'top' );
                // /{manga-slug}/{chapter-slug}/
                add_rewrite_rule( "{$lang}/{$slug}/([^/]+)/([^/]+)/?$", 'index.php?manga-core=$matches[1]&chapter=$matches[2]', 'top' );
            }
        }
		
        if(count($language_code) == 0 || $hide_default_language){
            // /{manga-slug}/{vol-slug}/{chapter-slug}/amp/
            add_rewrite_rule( "{$slug}/([^/]+)/([^/]+)/([^/]+)/amp/?$", 'index.php?manga-core=$matches[1]&volume=$matches[2]&chapter=$matches[3]&amp', 'top' );
            // /{manga-slug}/{vol-slug}/{chapter-slug}/amp/p/{page}
            add_rewrite_rule( "{$slug}/([^/]+)/([^/]+)/([^/]+)/amp/p/([^/]+)?$", 'index.php?manga-core=$matches[1]&volume=$matches[2]&chapter=$matches[3]&'.$manga_paged_var.'=$matches[4]&amp', 'top' );
            // /{manga-slug}/{chapter-slug}/amp/
            add_rewrite_rule( "{$slug}/([^/]+)/([^/]+)/amp/?$", 'index.php?manga-core=$matches[1]&chapter=$matches[2]&amp', 'top' );
            // /{manga-slug}/{chapter-slug}/amp/p/{page}
            add_rewrite_rule( "{$slug}/([^/]+)/([^/]+)/amp/p/([^/]+)?$", 'index.php?manga-core=$matches[1]&chapter=$matches[2]&'.$manga_paged_var.'=$matches[3]&amp', 'top' );
            // /{manga-slug}/{chapter-slug}/comment-{page}/
            add_rewrite_rule( "{$slug}/([^/]+)/([^/]+)/" . $wp_rewrite->comments_pagination_base . "-([^/]+)/?$", 'index.php?manga-core=$matches[1]&chapter=$matches[2]&cpage=$matches[3]', 'top' );
            // /{manga-slug}/{vol-slug}/{chapter-slug}/comment-{page}
            add_rewrite_rule( "{$slug}/([^/]+)/([^/]+)/([^/]+)/" . $wp_rewrite->comments_pagination_base . "-([^/]+)/?$", 'index.php?manga-core=$matches[1]&volume=$matches[2]&chapter=$matches[3]&cpage=$matches[4]', 'top' );
            // /{manga-slug}/{vol-slug}/{chapter-slug}/p/{page}
            add_rewrite_rule( "{$slug}/([^/]+)/([^/]+)/([^/]+)/p/([^/]+)?$", 'index.php?manga-core=$matches[1]&volume=$matches[2]&chapter=$matches[3]&'.$manga_paged_var.'=$matches[4]', 'top' );
            
            /**
             * Ajax action
             **/
            add_rewrite_rule( "{$slug}/([^/]+)/ajax/chapters/?$", 'index.php?manga-core=$matches[1]&manga_ajax=1&maction=get_chapters', 'top' );
            
            // /{manga-slug}/{vol-slug}/{chapter-slug}/
            add_rewrite_rule( "{$slug}/([^/]+)/([^/]+)/([^/]+)/?$", 'index.php?manga-core=$matches[1]&volume=$matches[2]&chapter=$matches[3]', 'top' );
            
            // /{manga-slug}/{chapter-slug}/p/{page}
            add_rewrite_rule( "{$slug}/([^/]+)/([^/]+)/p/([^/]+)?$", 'index.php?manga-core=$matches[1]&chapter=$matches[2]&'.$manga_paged_var.'=$matches[3]', 'top' );
            // /{manga-slug}/amp/
			
            add_rewrite_rule( "{$slug}/([^/]+)/amp/?$", 'index.php?manga-core=$matches[1]&amp', 'top' );
            
            // /{manga-slug}/comment-{page}/
            add_rewrite_rule( "{$slug}/([^/]+)/" . $wp_rewrite->comments_pagination_base . "-([^/]+)/?$", 'index.php?manga-core=$matches[1]&cpage=$matches[2]', 'top' );
            // /{manga-slug}/{chapter-slug}/
            add_rewrite_rule( "{$slug}/([^/]+)/([^/]+)/?$", 'index.php?manga-core=$matches[1]&chapter=$matches[2]', 'top' );
        }
        
	}
	
	/**
	 *
	 * Change WP-Manga permalink output. Beta Use
	 *
	 * @param String  $post_link link url.
	 * @param WP_Post $post post object.
	 * @param String  $leavename for edit.php.
	 *
	 * @version 1.6
	 *
	 * @return string
	 */
	function post_type_link( $post_link, $post, $leavename ){
		
		/**
		 * WP_Rewrite.
		 *
		 * @var WP_Rewrite $wp_rewrite
		 */
		global $wp_rewrite;

		if ( ! $wp_rewrite->permalink_structure ) {
			return $post_link;
		}

		$draft_or_pending = isset( $post->post_status ) && in_array( $post->post_status, array(
			'draft',
			'pending',
			'auto-draft',
		), true );
		if ( $draft_or_pending && ! $leavename ) {
			return $post_link;
		}

		$post_type = $post->post_type;
		$pt_object = get_post_type_object( $post_type );

		if ( false === $pt_object->rewrite ) {
			return $post_link;
		}

		if ( ! in_array( $post->post_type, array('wp-manga'), true ) ) {
			return $post_link;
		}
		
		$rewrite = $pt_object->rewrite;

		$permalink = apply_filters('manga_permalink_structure', $rewrite['slug'] . '/%post_id%/');

		$permalink = str_replace( '%post_id%', $post->ID, $permalink );

		$permalink = home_url( $permalink );
		return $permalink;
	}
	
	/**
	 * Register_post_type_rules. Beta Use
	 * add rewrite tag for Custom Post Type.
	 *
	 * @version 1.6
	 *
	 * @param string       $post_type Post type.
	 * @param WP_Post_Type $args      Arguments used to register the post type.
	 */
	public function register_post_type_rules( $post_type, $args ) {
		/**
		 * WP_Rewrite.
		 *
		 * @var WP_Rewrite $wp_rewrite
		 */
		global $wp_rewrite;

		if ( $args->_builtin ) {
			return;
		}

		if ( false === $args->rewrite ) {
			return;
		}

		if ( ! in_array( $post_type, array('wp-manga'), true ) ) {
			return;
		}

		$rewrite_args = $args->rewrite;
		$slug = $rewrite_args['slug'];

		$permalink = apply_filters('manga_permalink_structure', $slug . '/%post_id%/');

		$permalink = '%' . $post_type . '_slug%' . $permalink;

		add_rewrite_rule('^' . $slug . '/([^/]*)/([^/]*)?','index.php?post_type=' . $post_type . '&page_id=$matches[1]&chapter=$matches[2]','top');
		
		if ( ! is_array( $rewrite_args ) ) {
			$rewrite_args = array(
				'with_front' => $args->rewrite,
			);
		}
		
		if ( $args->has_archive ) {
			if ( is_string( $args->has_archive ) ) {
				$slug = $args->has_archive;
			};

			if ( $args->rewrite['with_front'] ) {
				$slug = substr( $wp_rewrite->front, 1 ) . $slug;
			}
		}

		$rewrite_args['walk_dirs'] = false;
		add_permastruct( $post_type, $permalink, $rewrite_args );
	}
}

$GLOBALS['wp_manga_permalink'] = WP_Manga_Permalink::get_instance();

function wp_manga_get_current_chapter(){
    return madara_permalink_reading_chapter();
}

/**
 * Get current reading chapter from query var
 *
 * @return - object - Current Reading Chapter, if any
 *
 * deprecated on 1.6.5.1
 **/
function madara_permalink_reading_chapter(){
	global $__CURRENT_CHAPTER;
	if(isset($__CURRENT_CHAPTER)){
		return $__CURRENT_CHAPTER;
	}
	
	$var = get_query_var( 'chapter' );
	
	$manga_id = get_the_ID();
	$__CURRENT_CHAPTER = false;

	if($var != '' && $manga_id){
		global $wp_manga_functions;
		$__CURRENT_CHAPTER = $wp_manga_functions->get_chapter_by_slug( $manga_id, $var );
	}
	
	$__CURRENT_CHAPTER = apply_filters('wp_manga_permalink_reading_chapter', $__CURRENT_CHAPTER);
	
	return $__CURRENT_CHAPTER;
	
	/**
	 *
	 *
	 $options = get_option( 'wp_manga_settings', array() );
		$chapter_slug_or_id = isset( $options['chapter_slug_or_id'] ) ? $options['chapter_slug_or_id'] : 'slug';
			
		if($chapter_slug_or_id == 'id'){
			$chapter = madara_get_global_wp_manga_chapter()->get_chapter_by_id( $manga_id, $var );
		} else {
			$chapter = $wp_manga_functions->get_chapter_by_slug( $manga_id, $var );
		}
		
		return $chapter;
		**/
}