<?php
class WP_MANGA_CACHE_SYSTEM {
    public static $CACHE_EXPIRATION = 600; // minutes
    /**
     * @param $query_args Array Array of WP Query arguments
     * @return Array WP Manga post results
     */
    public static function get_cached_or_query($query_args){
        $version = wp_cache_get('wq_v', 'wp_manga');
        if($version === false){
            $version = 0;
        }

        $cache_key = 'wq_' . $version . '_' . md5(serialize($query_args));

        $cached_results = wp_cache_get($cache_key, 'wp_manga');
        if($cached_results !== false){
            return $cached_results;
        }

        $query = madara_manga_query($query_args);
        $results = $query->get_posts();

        wp_cache_set($cache_key, $results, 'wp_manga', self::$CACHE_EXPIRATION + rand(1, 60)); // 10 minutes cache
        
        $count = $query->found_posts;
        $cache_key = 'wqc_' . $version . '_' . md5(serialize($query_args));
        wp_cache_set($cache_key, $count, 'wp_manga', self::$CACHE_EXPIRATION + rand(1, 60)); // 10 minutes cache

        return $results;
    }

    public static function get_cached_or_count($query_args){
        $version = wp_cache_get('wqc_v', 'wp_manga');
        if($version === false){
            $version = 0;
        }

        $cache_key = 'wqc_' . $version . '_' . md5(serialize($query_args));

        $cached_results = wp_cache_get($cache_key, 'wp_manga');
        if($cached_results !== false){
            return $cached_results;
        }

        $query = madara_manga_query($query_args);
        $results = $query->found_posts;

        wp_cache_set($cache_key, $results, 'wp_manga', self::$CACHE_EXPIRATION + rand(1, 60)); // 10 minutes cache

        return $results;
    }

    public static function get_cached($prefix, $args){
        $version = wp_cache_get($prefix . '_v', 'wp_manga');
        if($version === false){
            $version = 0;
        }

        $cache_key = $prefix . $version . '_' . md5(serialize($args));
        $cached_results = wp_cache_get($cache_key, 'wp_manga');
        
        return $cached_results;
    }

    public static function set_cached($prefix, $args, $data){
        $version = wp_cache_get($prefix . '_v', 'wp_manga');
        if($version === false){
            $version = 0;
        }

        $cache_key = $prefix . ($version + 1) . '_' . md5(serialize($args));
        wp_cache_set($cache_key, $data, 'wp_manga', self::$CACHE_EXPIRATION + rand(1, 60));

        wp_cache_set($prefix . '_v', $version + 1, 'wp_manga');
    }

    public static function clear_cache($prefix = ''){
        $cache_version = wp_cache_get($prefix . '_v', 'wp_manga');
        if($cache_version === false){
            $cache_version = 0;
        }

        wp_cache_set($prefix . '_v', $cache_version + 1, 'wp_manga', self::$CACHE_EXPIRATION * 2);
    }
}

// clear cache when a chapter is inserted or updated
add_action('manga_chapter_inserted', 'wp_manga_cache_system_clear_cache_chapter_inserted', 10, 2);
add_action('manga_chapter_deleted', 'wp_manga_cache_system_clear_cache_chapter_deleted', 10, 1);

function wp_manga_cache_system_clear_cache_chapter_inserted($chapter_id, $chapter_args){
    WP_MANGA_CACHE_SYSTEM::clear_cache('mc_' . $chapter_args['post_id']);
    WP_MANGA_CACHE_SYSTEM::clear_cache('mclb_');
}

function wp_manga_cache_system_clear_cache_chapter_deleted($args){
    WP_MANGA_CACHE_SYSTEM::clear_cache('mc_' . $args['post_id']);
    WP_MANGA_CACHE_SYSTEM::clear_cache('mclb_');
}

add_action('wp_manga_new_manga_created', 'wp_manga_cache_system_clear_cache_single_manga_created', 10, 1);
function wp_manga_cache_system_clear_cache_single_manga_created($post_id){
    WP_MANGA_CACHE_SYSTEM::clear_cache('wq_');
    WP_MANGA_CACHE_SYSTEM::clear_cache('wpc_');
}