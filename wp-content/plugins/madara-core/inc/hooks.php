<?php

add_action('wp-manga-manga-properties', 'wp_manga_single_manga_info_user_rate', 10, 1);

if(!function_exists('wp_manga_single_manga_info_user_rate')){
	function wp_manga_single_manga_info_user_rate( $manga_id ) {
		$wp_manga_settings = get_option( 'wp_manga_settings' );
		$user_rate = isset( $wp_manga_settings['user_rating'] ) ? $wp_manga_settings['user_rating'] : 1;
		
		if($user_rate){
			global $wp_manga_functions;
			$rate        = $wp_manga_functions->get_total_review( $manga_id );
			$vote        = $wp_manga_functions->get_total_vote( $manga_id );
            $vote_raw    = $wp_manga_functions->get_total_vote( $manga_id, true );
			
			global $wp_manga_template;
			include $wp_manga_template->load_template('single/info','rating', false);
		}
	}
}

add_action('wp-manga-manga-properties', 'wp_manga_single_manga_info_rank', 12, 1);

if(!function_exists('wp_manga_single_manga_info_rank')){
	function wp_manga_single_manga_info_rank( $manga_id ) {
        global $wp_manga_setting;
            
        $enabled_manga_view = $wp_manga_setting->get_manga_option('manga_view', 1);
                
        if($enabled_manga_view){    
            global $wp_manga_functions;
            $rank              = $wp_manga_functions->get_manga_rank( $manga_id );
            $views             = $wp_manga_functions->get_manga_monthly_views( $manga_id );
            
            global $wp_manga_template;
            include $wp_manga_template->load_template('single/info','rank', false);
        }
	}
}

add_action('wp-manga-manga-properties', 'wp_manga_single_manga_info_afternativename', 14, 1);

if(!function_exists('wp_manga_single_manga_info_afternativename')){
	function wp_manga_single_manga_info_afternativename( $manga_id ) {
		global $wp_manga_functions;
		$alternative = $wp_manga_functions->get_manga_alternative( $manga_id );
		
		global $wp_manga_template;
		include $wp_manga_template->load_template('single/info','alternative-name', false);
	}
}

add_action('wp-manga-manga-properties', 'wp_manga_single_manga_info_authors', 16, 1);

if(!function_exists('wp_manga_single_manga_info_authors')){
	function wp_manga_single_manga_info_authors( $manga_id ) {
		global $wp_manga_functions;
		$authors     = $wp_manga_functions->get_manga_authors( $manga_id );
		
		global $wp_manga_template;
		include $wp_manga_template->load_template('single/info','authors', false);
	}
}

add_action('wp-manga-manga-properties', 'wp_manga_single_manga_info_artists', 18, 1);

if(!function_exists('wp_manga_single_manga_info_artists')){
	function wp_manga_single_manga_info_artists( $manga_id ) {
		global $wp_manga_functions;
		$artists     = $wp_manga_functions->get_manga_artists( $manga_id );
		
		global $wp_manga_template;
		include $wp_manga_template->load_template('single/info','artists', false);
	}
}

add_action('wp-manga-manga-properties', 'wp_manga_single_manga_info_genres', 20, 1);

if(!function_exists('wp_manga_single_manga_info_genres')){
	function wp_manga_single_manga_info_genres( $manga_id ) {
		global $wp_manga_functions;
		$genres     = $wp_manga_functions->get_manga_genres( $manga_id );
		
		global $wp_manga_template;
		include $wp_manga_template->load_template('single/info','genres', false);
	}
}

add_action('wp-manga-manga-properties', 'wp_manga_single_manga_info_type', 22, 1);

if(!function_exists('wp_manga_single_manga_info_type')){
	function wp_manga_single_manga_info_type( $manga_id ) {
		global $wp_manga_functions;
		$type = $wp_manga_functions->get_manga_type( $manga_id );
		
		global $wp_manga_template;
		include $wp_manga_template->load_template('single/info','type', false);
	}
}

add_action('wp-manga-manga-properties', 'wp_manga_single_manga_info_tags', 22, 1);

if(!function_exists('wp_manga_single_manga_info_tags')){
	function wp_manga_single_manga_info_tags( $manga_id ) {
		global $wp_manga_functions;
		$tags = $wp_manga_functions->get_manga_tags( $manga_id );
		
		global $wp_manga_template;
		include $wp_manga_template->load_template('single/info','tags', false);
	}
}

add_action('wp-manga-manga-status', 'wp_manga_single_manga_info_release', 10, 1);

if(!function_exists('wp_manga_single_manga_info_release')){
	function wp_manga_single_manga_info_release( $manga_id ) {
		global $wp_manga_functions;
		
		$release = $wp_manga_functions->get_manga_release( $manga_id );
		
		global $wp_manga_template;
		include $wp_manga_template->load_template('single/info','release', false);
	}
}

add_action('wp-manga-manga-status', 'wp_manga_single_manga_info_status', 12, 1);

if(!function_exists('wp_manga_single_manga_info_status')){
	function wp_manga_single_manga_info_status( $manga_id ) {
		global $wp_manga_functions;
		
		$status = $wp_manga_functions->get_manga_status( $manga_id );
		
		global $wp_manga_template;
		include $wp_manga_template->load_template('single/info','status', false);
	}
}

add_action('wp-manga-manga-status', 'wp_manga_single_manga_user_buttons', 12, 1);

if(!function_exists('wp_manga_single_manga_user_buttons')){
	function wp_manga_single_manga_user_buttons( $manga_id ) {
		global $wp_manga_functions;
		
		$wp_manga_settings = get_option( 'wp_manga_settings' );
		$manga_comment = isset( $wp_manga_settings['enable_comment'] ) ? $wp_manga_settings['enable_comment'] : 1;
		$user_bookmark = get_option('users_can_register') ? (isset( $wp_manga_settings['user_bookmark'] ) ? $wp_manga_settings['user_bookmark'] : 1) : 0;
		
		global $wp_manga_template;
		include $wp_manga_template->load_template('single/user-actions','', false);
	}
}

add_action('wp-manga-chapter-listing', 'wp_manga_single_manga_info_chapters');
if(!function_exists('wp_manga_single_manga_info_chapters')){
	function wp_manga_single_manga_info_chapters( $manga_id ) {
		?>
		<div id="manga-chapters-holder" data-id="<?php echo esc_attr($manga_id);?>"><i class="fas fa-spinner fa-spin fa-3x"></i></div>
		<?php
		/**
		global $wp_manga_functions, $wp_manga_database;
		
		$sort_option = $wp_manga_database->get_sort_setting();
		
		$manga = $wp_manga_functions->get_all_chapters( $manga_id, $sort_option['sort'] );
		
		$current_read_chapter = 0;
		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
			$history = madara_get_current_reading_chapter($user_id, $manga_id);
			if($history){
				$current_read_chapter = $history['c'];
			}
		}
		
		global $wp_manga_template;
		include $wp_manga_template->load_template('single/info','chapters', false);
		**/
	}
}

add_action('wp-manga-reading-chapters-selectbox', 'wp_manga_reading_chapters_selectbox', 10, 3);
if(!function_exists('wp_manga_reading_chapters_selectbox')){
	function wp_manga_reading_chapters_selectbox($cur_chap, $all_chaps, $manga_type){
		if( !empty( $_GET['style'] ) ){
			$style = $_GET['style'];
		} else {
			global $wp_manga_functions;
			$style = $wp_manga_functions->get_reading_style();
		}
		
		if($manga_type == 'manga'){
			?>
			<div class="chapter-selection chapters_selectbox_holder" data-manga="<?php echo esc_attr($cur_chap['post_id']);?>" data-chapter="<?php echo esc_attr($cur_chap['chapter_slug']);?>" data-vol="<?php echo esc_attr($cur_chap['volume_id']);?>" data-type="manga" data-style="<?php echo esc_attr($style);?>">
				<!-- place holder -->
			</div>
			<?php
		} else {
			// $manga_type == 'content';
			?>
			<div class="c-selectpicker selectpicker_chapter chapter-selection chapters_selectbox_holder" data-manga="<?php echo esc_attr($cur_chap['post_id']);?>" data-chapter="<?php echo esc_attr($cur_chap['chapter_slug']);?>" data-vol="<?php echo esc_attr($cur_chap['volume_id']);?>" data-type="content">
					<!-- place holder -->
			</div>
			<?php 
		}
	}
}

/**
 * Support Speaker (merkulove) plugin
 **/
add_filter( 'madara_chapter_content_li_html', 'wp_manga_speaker_admin_generate_button', 10, 3);
add_action( 'admin_enqueue_scripts', 'wp_manga_speaker_admin_enqueue_script' );
add_action( 'wp_ajax_wp-manga-generate-voice', 'wp_manga_speaker_chapters_voicing' );
add_action('wp_manga_before_chapter_content', 'wp_manga_speaker_showing_shortcode', 10, 2);

function wp_manga_speaker_showing_shortcode($cur_chap, $manga_id){
    if ( class_exists( '\Merkulove\Speaker\SpeakerCaster' ) ) {
        $reading_chapter = madara_permalink_reading_chapter();
        $chapter_type = get_post_meta( $manga_id, '_wp_manga_chapter_type', true );
        if($chapter_type == 'text'){
            $speakerCaster = \Merkulove\Speaker\SpeakerCaster::get_instance();
            
            if($speakerCaster->audio_exists( $reading_chapter['chapter_id'] )){
                echo do_shortcode( '[speaker id="'. $reading_chapter['chapter_id'] .'"]' );
            }
        }
    }
}

function wp_manga_speaker_admin_enqueue_script(){
    global $wp_manga;
    if ( $wp_manga->is_manga_edit_page() ) {
        wp_enqueue_script( 'wp-manga-admin-single-manga-speaker', WP_MANGA_URI . 'assets/js/admin-speaker.js', array( 'jquery', 'wp-manga-admin-single-manga' ), '1.6.6.5', true );
    }
}

function wp_manga_speaker_admin_generate_button( $output, $chapter_id, $c ){
    if ( class_exists( '\Merkulove\Speaker\SpeakerCaster' ) ) {
        $manga_id = $c['post_id'];
        $chapter_type = get_post_meta( $manga_id, '_wp_manga_chapter_type', true );
        if($chapter_type == 'text'){
            $speakerCaster = \Merkulove\Speaker\SpeakerCaster::get_instance();
            
            if($speakerCaster->audio_exists( $chapter_id )){
                $output .= '<a href="#" id="chapter-'. $chapter_id .'-voice-generator" data-chapter="' . esc_attr( $chapter_id ) . '" class="speaker-generate generated"><i class="fas fa-spinner fa-spin"></i> <i class="fas fa-file-audio"></i> <span>' . esc_html__('Regenerate?', WP_MANGA_TEXTDOMAIN) . '</span></a>';
            } else {
                $output .= '<a href="#" id="chapter-'. $chapter_id .'-voice-generator" data-chapter="' . esc_attr( $chapter_id ) . '" class="speaker-generate"><i class="fas fa-spinner fa-spin"></i> <span>' . esc_html__('Generate Audio', WP_MANGA_TEXTDOMAIN) . '</span></a>';
            }
        }
    }   
    
    return $output;
}

/** 
 * Ajax call to generate voice for a chapter
 **/
function wp_manga_speaker_chapters_voicing(){
    /** Security Check. */
    // check_ajax_referer( 'speaker-nonce', 'nonce' );

    /** Current post attributes */
    $manga_id = (int)$_POST['postID'];
    $chapter_id = (int)$_POST['chapterID'];
    
    global $wp_manga_text_type;
    $chapter = $wp_manga_text_type->get_chapter_content_post($chapter_id);
    if(!$chapter){
        return;
    }
    
    $voice_acting = true;
    
    /** Get content, send for voicing, glue chapters, and save result files */
    $voice_acting = \Merkulove\Speaker\SpeakerCaster::get_instance()->voice_acting(

        $chapter_id,
        'custom-content',
        wp_manga_str_split_unicode( $chapter->post_content, 4500 ) // break ito small trunks to do voice acting
    );

    /** Return processing results to JS */
    wp_send_json( $voice_acting ?
        [
            'success' => true,
            'message' => esc_html__( 'Audio Generated Successfully', WP_MANGA_TEXTDOMAIN )
        ] :
        [
            'success' => false,
            'message' => esc_html__( 'An error occurred while generating the audio.', WP_MANGA_TEXTDOMAIN )
        ]
    );
}


add_action( 'wp_insert_comment', 'wp_manga_reply_chapter_comment', 10, 2 );

/** 
 * In case admin reply comment, make sure the reply has Chapter ID meta
 **/
function wp_manga_reply_chapter_comment( $id, $comment ){
    if($comment->comment_parent){
        $chapter_id = get_comment_meta($comment->comment_parent, 'chapter_id', true);
        if($chapter_id){
            add_comment_meta( $id, 'chapter_id', $chapter_id, true );
        }
    }
}

/** Support WPDiscuz **/

/**
 * Update logout link for WPDiscuz when in a chapter reading page
 **/
add_action('wpdiscuz_user_info_and_logout_link', 'wp_manga_wpdiscuz_user_info_and_logout_link');
function wp_manga_wpdiscuz_user_info_and_logout_link( $logout_text ){
    $wpdiscuz = wpDiscuz();
    $currentUser = $wpdiscuz->helper->getCurrentUser();
    
    $user_url = get_author_posts_url($currentUser->ID);
    $user_url = $wpdiscuz->helper->getProfileUrl($user_url, $currentUser);
    
    $reading_chapter = madara_permalink_reading_chapter();
    if($reading_chapter){
        global $wp_manga_functions;
        
        $logout = wp_loginout($wp_manga_functions->build_chapter_url($reading_chapter['post_id'], $reading_chapter), false);
    } else {
        $logout = wp_loginout(get_permalink(), false);
    }
    $logout = preg_replace("!>([^<]+)!is", ">" . esc_html($wpdiscuz->options->getPhrase("wc_log_out")), $logout);
    if ($user_url) {
        $logout_text = esc_html($wpdiscuz->options->getPhrase("wc_logged_in_as")) . " <a href='" . esc_url_raw($user_url) . "'>" . esc_html($wpdiscuz->helper->getCurrentUserDisplayName($currentUser)) . "</a> | " . $logout;
    } else {
        $logout_text = esc_html($wpdiscuz->options->getPhrase("wc_logged_in_as")) . " " . esc_html($wpdiscuz->helper->getCurrentUserDisplayName($currentUser)) . " | " . $logout;
    }
    
    return $logout_text;
}

add_action('wpdiscuz_login_link', 'wp_manga_wpdiscuz_login_link');
function wp_manga_wpdiscuz_login_link( $login ){
    $wpdiscuz = wpDiscuz();
    
    if ($wpdiscuz->options->login["loginUrl"]) {
        $login = "<a href='" . esc_url_raw($wpdiscuz->options->login["loginUrl"]) . "'><i class='fas fa-sign-in-alt'></i> " . esc_html($wpdiscuz->options->getPhrase("wc_log_in")) . "</a>";
    } else {
        $redirect_to = get_permalink();
        $reading_chapter = madara_permalink_reading_chapter();
        if($reading_chapter){
            global $wp_manga_functions;
            
            $redirect_to = $wp_manga_functions->build_chapter_url($reading_chapter['post_id'], $reading_chapter);
        }
        
        $login = $wpdiscuz->options->login["loginUrl"] ? "<a href='" . esc_url_raw($wpdiscuz->options->login["loginUrl"]) . "'></a>" : wp_loginout($redirect_to, false);
        $login = preg_replace("!>([^<]+)!is", "><i class='fas fa-sign-in-alt'></i> " . esc_html($wpdiscuz->options->getPhrase("wc_log_in")), $login);
    }
    if ($wpdiscuz->options->isShowLoginButtons()) {
        echo "<div class='wpd-sep'></div>";
    }
    
    return $login;
}

/**
 * Correct comment link for chapter
 **/
add_filter('get_comment_link', 'wp_manga_get_comment_link', 10, 4);
function wp_manga_get_comment_link($link, $comment, $args, $cpage){
    global $wp_rewrite, $in_comment_loop;
 
    $comment = get_comment( $comment );
 
    // Back-compat.
    if ( ! is_array( $args ) ) {
        $args = array( 'page' => $args );
    }
 
    $defaults = array(
        'type'      => 'all',
        'page'      => '',
        'per_page'  => '',
        'max_depth' => '',
        'cpage'     => null,
    );
    $args     = wp_parse_args( $args, $defaults );
 
    $link = get_permalink( $comment->comment_post_ID );
    
    $comment_chapter = get_comment_meta($comment->comment_ID, 'chapter_id', true);
    if($comment_chapter){
        global $wp_manga_functions, $wp_manga_chapter;
        
        $chapter = $wp_manga_chapter->get_chapter_by_id( $comment->comment_post_ID, $comment_chapter );
        
        $link = $wp_manga_functions->build_chapter_url($comment->comment_post_ID, $chapter);
    }
 
    // The 'cpage' param takes precedence.
    if ( ! is_null( $args['cpage'] ) ) {
        $cpage = $args['cpage'];
 
        // No 'cpage' is provided, so we calculate one.
    } else {
        if ( '' === $args['per_page'] && get_option( 'page_comments' ) ) {
            $args['per_page'] = get_option( 'comments_per_page' );
        }
 
        if ( empty( $args['per_page'] ) ) {
            $args['per_page'] = 0;
            $args['page']     = 0;
        }
 
        $cpage = $args['page'];
 
        if ( '' == $cpage ) {
            if ( ! empty( $in_comment_loop ) ) {
                $cpage = get_query_var( 'cpage' );
            } else {
                // Requires a database hit, so we only do it when we can't figure out from context.
                $cpage = get_page_of_comment( $comment->comment_ID, $args );
            }
        }
 
        /*
         * If the default page displays the oldest comments, the permalinks for comments on the default page
         * do not need a 'cpage' query var.
         */
        if ( 'oldest' === get_option( 'default_comments_page' ) && 1 === $cpage ) {
            $cpage = '';
        }
    }
 
    if ( $cpage && get_option( 'page_comments' ) ) {
        if ( $wp_rewrite->using_permalinks() ) {
            if ( $cpage ) {
                $link = trailingslashit( $link ) . $wp_rewrite->comments_pagination_base . '-' . $cpage;
            }
 
            $link = user_trailingslashit( $link, 'comment' );
        } elseif ( $cpage ) {
            $link = add_query_arg( 'cpage', $cpage, $link );
        }
    }
 
    if ( $wp_rewrite->using_permalinks() ) {
        $link = user_trailingslashit( $link, 'comment' );
    }
 
    $link = $link . '#comment-' . $comment->comment_ID;
    
    return $link;
}

add_filter("wpdiscuz_comment_list_args", "wp_manga_wpdiscuz_comment_list_args");
function wp_manga_wpdiscuz_comment_list_args( $args ){
    $post_id = $args['post_id'];
    
    if(isset($_POST['chapterId'])){
        $chapter_id = $_POST['chapterId'];
        global $wp_manga_chapter;
        
        $reading_chapter = $wp_manga_chapter->get_chapter_by_id( $post_id, $chapter_id );
    } else {
        $reading_chapter = madara_permalink_reading_chapter();
    }
    
    if($reading_chapter){
        global $wp_manga_functions;
        
        $chapter_url = $wp_manga_functions->build_chapter_url($post_id, $reading_chapter);
        
        $args["share_buttons"] = str_replace(esc_url_raw($args['post_permalink']),esc_url_raw($chapter_url),$args["share_buttons"]);
        
        $args['post_permalink'] = $chapter_url;

        $args['chapterId'] = $reading_chapter['chapter_id'];
    }    
    
    return $args;
}


add_filter('wpdiscuz_before_getcomments', 'wp_manga_wpdiscuz_before_getcomments', 10, 3);

function wp_manga_wpdiscuz_before_getcomments($commentsArgs, $current_user, $comment_list_args){
    if(isset($comment_list_args['chapterId'])){
        $commentsArgs['chapterId'] = $comment_list_args['chapterId'];
    }    
    
    return $commentsArgs;
}

add_filter('wpdiscuz_comments_args', 'wp_manga_wpdiscuz_comments_args');
function wp_manga_wpdiscuz_comments_args($args){
    $reading_chapter = madara_permalink_reading_chapter();
    if($reading_chapter){
        $chapter_id = $reading_chapter['chapter_id'];
        $args['chapterId'] = $chapter_id;
    }

    return $args;
}


add_filter('wpdiscuz_filter_args', 'wp_manga_filter_get_comments_args');
function wp_manga_filter_get_comments_args($args){
    $chapter_id = WpdiscuzHelper::sanitize(INPUT_POST, "chapterId", FILTER_SANITIZE_NUMBER_INT, 0);

    if(!$chapter_id){
        $reading_chapter = madara_permalink_reading_chapter();
        if($reading_chapter){
            $chapter_id = $reading_chapter['chapter_id'];
        }        
    }

    $meta_query = isset($args['meta_query']) ? $args['meta_query'] : array();
    
    if($chapter_id){
        $meta_query[] = [
            'key' => 'chapter_id',
            'value' => $chapter_id
        ];
    } else {
        $meta_query[] = [
            'key' => 'chapter_id',
            'value' => ''
        ];
        $meta_query[] = [
            'key' => 'chapter_id',
            'value' => '',
            'compare' => 'NOT EXISTS'
        ];
        $meta_query['relation'] = 'OR';
    }
    
    $args['meta_query'] = $meta_query;

    return $args;
}

add_filter("wpdiscuz_js_options", function ($wpdiscuzOptionsJs, $options) {
    $wpdiscuzOptionsJs["dataFilterCallbacks"]["wpdLoadMoreComments"][] = "wp_manga_chapter_comment";

    $wpdiscuzOptionsJs["dataFilterCallbacks"]["wpdSorting"][] = "wp_manga_chapter_comment";
    $wpdiscuzOptionsJs["dataFilterCallbacks"]["wpdUserRate"][] = "wp_manga_chapter_comment";

    return $wpdiscuzOptionsJs;
}, 10, 2);

add_action('wp_manga_after_update_manga_views', 'wp_manga_increase_chapter_views');

if(!function_exists('wp_manga_increase_chapter_views')){
    function wp_manga_increase_chapter_views(){
        $wp_manga_settings = get_option( 'wp_manga_settings' );
		if(isset($wp_manga_settings['chapter_view']) && $wp_manga_settings['chapter_view']){
            $manga_id = intval($_POST['manga']);
            $chapter_slug = '';
            if(isset($_POST['chapter']) && $_POST['chapter'] != 'undefined'){
                $chapter_slug = $_POST['chapter'];
            }
            
            if($chapter_slug != ''){
                global $wp_manga_chapter;
                $chapter = $wp_manga_chapter->get_chapter_by_slug( $manga_id, $chapter_slug);
            
                if($chapter){
                    $chapter_id = $chapter['chapter_id'];
                    global $wpdb, $wp_manga_functions;
    
                    $views = $wp_manga_functions->get_chapter_views($chapter_id);
                    $wpdb->update("{$wpdb->prefix}manga_chapters", array('views'=>++$views), array('chapter_id' => $chapter_id));
                }
            }
        }
    }
}

add_action('madara_after_chapter_listing', 'wp_manga_chapters_pagination');
function wp_manga_chapters_pagination(){
    global $wp_manga_functions;

    $chapter_page = isset($_GET['t']) ? intval($_GET['t']) : 1;
    $number_of_chapters_per_page = $wp_manga_functions->get_number_chapters_per_page();
    $manga_id = get_query_var('manga_id');
    $total_items = $wp_manga_functions->get_chapters_count($manga_id);
    if($number_of_chapters_per_page && $total_items > $number_of_chapters_per_page){
        $total_pages = ceil($total_items / $number_of_chapters_per_page);
        wp_manga_pagination_generate($total_pages, $chapter_page, '/', 't');
    }
    
}

add_filter('chapter_data_cache_enabled', 'wp_manga_chapter_data_cache_enabled_control');
function wp_manga_chapter_data_cache_enabled_control($cache_enabled){
    if(class_exists('WP_MANGA_ADDON_CHAPTER_COIN') || class_exists('WP_MANGA_ADDON_CHAPTER_PERMISSIONS')){
        // disabled chapter data cache if WP Manga Chapter Coin or WP Manga Chapter Permissions is used
        return false;
    }

    return $cache_enabled;
}