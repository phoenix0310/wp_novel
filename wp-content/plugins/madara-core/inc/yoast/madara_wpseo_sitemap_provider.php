<?php

/**
 * Generate sitemap for Manga Chapters
 **/
class Madara_Sitemap_Provider implements WPSEO_Sitemap_Provider{
	/**
	 * Check if provider supports given item type.
	 *
	 * @param string $type Type string to check for.
	 *
	 * @return boolean
	 */
	public function handles_type( $type ) {
		return true;
	}

	/**
	 * Get set of sitemaps index link data.
	 *
	 * @param int $max_entries Entries per sitemap.
	 *
	 * @return array
	 */
	public function get_index_links( $max_entries ){
		$index = array();
		
		global $wpdb;
		
		$sql = "SELECT count(*) as total FROM {$wpdb->prefix}manga_chapters";
		
		$count = $wpdb->get_var($sql);
		$total_page = floor($count / $max_entries) + 1;
		
		for($current_page = 1; $current_page <= $total_page; $current_page++){
			if($max_entries * ($current_page - 1) < $count) {
				$offset = $max_entries * ($current_page - 1);
				$sql = "SELECT max(date_gmt) FROM {$wpdb->prefix}manga_chapters ORDER BY date_gmt DESC LIMIT {$max_entries},{$offset}";
				$date = $wpdb->get_var($sql);
		
				$index[] = array(
						'loc'     => WPSEO_Sitemaps_Router::get_base_url( 'wp-manga-chapters-sitemap'. $current_page .'.xml' ),
						'lastmod' => $date,
					);
			}
		}
		
		return $index;
	}

	/**
	 * Get set of sitemap link data.
	 *
	 * @param string $type         Sitemap type.
	 * @param int    $max_entries  Entries per sitemap.
	 * @param int    $current_page Current page of the sitemap.
	 *
	 * @return array
	 */
	public function get_sitemap_links( $type, $max_entries, $current_page ){
		$urls = array();
		
		global $wpdb, $wp_manga_functions;
		
		$offset = ($current_page - 1) * $max_entries;
		
		$sql = "SELECT * FROM {$wpdb->prefix}manga_chapters ORDER BY date_gmt DESC LIMIT {$offset}, {$max_entries}";

		/**
		 * @params
		 * $sql - full SQL query
		 * $select
		 * $where
		 * $order_by
		 * $limit
		 **/
		$sql = apply_filters('wp_manga_chapter_feed_query', $sql, "SELECT * FROM {$wpdb->prefix}manga_chapters", "", "ORDER BY date_gmt DESC", "LIMIT {$offset}, {$max_entries}");
		
		$results = $wpdb->get_results($sql);
		
		global $_wp_manga_wpseo_sitemap;
		$_wp_manga_wpseo_sitemap = true;
		foreach($results as $result){
			$manga_id = $result->post_id;
			
			// check if $manga is active
			if(get_post_status($manga_id) == 'publish'){
				$chapter_slug = $result->chapter_slug;
				
				$link = $wp_manga_functions->build_chapter_url( $manga_id, $chapter_slug );
				
				$urls[] = array(
						'loc' => esc_url($link),
						'mod' => $result->date_gmt,
						'chf' => 'daily', // Deprecated, kept for backwards data compat. R.
						'pri' => 1, // Deprecated, kept for backwards data compat. R.
						'images' => array());
			}
		}
		$_wp_manga_wpseo_sitemap = false;
		
		return $urls;
	}
}