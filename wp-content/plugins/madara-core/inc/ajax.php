<?php

class WP_MANGA_AJAX {

	public function __construct() {
        
		require_once( WP_MANGA_DIR . '/inc/ajax/backend.php' );
		require_once( WP_MANGA_DIR . '/inc/ajax/frontend.php' );
		require_once( WP_MANGA_DIR . '/inc/ajax/upload.php' );
	}
}

$GLOBALS['wp_manga_ajax'] = new WP_MANGA_AJAX();