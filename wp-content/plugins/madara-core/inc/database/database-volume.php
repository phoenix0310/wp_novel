<?php

class WP_DB_VOLUME extends WP_MANGA_DATABASE{

    public $table;

    private static $instance;

    public static function get_instance() {
        if ( null == self::$instance ) {
            self::$instance = new WP_DB_VOLUME();
        }

        return self::$instance;
    }

    public function __construct(){

        $this->table = $this->get_wpdb()->prefix . 'manga_volumes';

    }

	/**
	 * @return Volume Id
	 **/
    function insert_volume( $args ){

        //post_id require, volume_name, date, date_gmt

        if( empty( $args['post_id'] ) ){
            return false;
        }

        $args['date'] = current_time( 'mysql' );
        $args['date_gmt'] = current_time( 'mysql', true );

        return $this->insert( $this->table, $args );

    }

    function get_volumes( $args ){
        if( empty( $args ) ){
            return false;
        }

        $cached_results = WP_MANGA_CACHE_SYSTEM::get_cached('wp_manga_get_volumes_', $args);
        if($cached_results !== false){
            return $cached_results;
        }
		
        $conditions = array();

        foreach( $args as $name => $value ){

            if( $name == 'orderby' || $name == 'order' ){
                continue;
            }

            $conditions[] = "$name = '$value'";
        }

        $where = implode( ' AND ', $conditions );

        $orderby = isset( $args['orderby'] ) ? $args['orderby'] : 'volume_index';
        $order   = isset( $args['order'] ) ? $args['order'] : '';
		
        $results = $this->get( $this->table, $where, $orderby, $order );

        WP_MANGA_CACHE_SYSTEM::set_cached('wp_manga_get_volumes_', $args, $results);

        return $results;
    }

    function delete_volume( $args ){

        return $this->delete( $this->table, $args );

    }

    function update_volume( $update, $args ){

        return $this->update( $this->table, $update, $args );

    }

    function get_volume_by_id( $post_id, $volume_id ) {

        $volume = $this->get_volumes(
            array(
                'post_id' => $post_id,
                'volume_id' => $volume_id,
            )
        );

        if( isset( $volume[0] ) ){
            return $volume[0];
        }

        return false;
    }

    function get_manga_volumes( $post_id ){

        return $this->get_volumes(
            array(
                'post_id' => $post_id
            )
        );
    }

	/**
	 * $volume_id = 0 - get all chapters without volume
	 **/
    function get_volume_chapters( $post_id, $volume_id, $orderby = '', $order = '' ){

        global $wp_manga_chapter;

        $chapters = $wp_manga_chapter->get_chapters(
            array(
                'post_id'   => $post_id,
                'volume_id' => $volume_id
            ),
            $is_search = false,
            $orderby,
            $order
        );
		
		// attach volume_name to each chapter so we don't need to query again
		$volume = $this->get_volume_by_id( $post_id, $volume_id );
		if($volume) {
			foreach($chapters as $chapter){
				$chapter['volume_name'] = $volume['volume_name'];
			}
		}

        return $chapters;

    }

}

$GLOBALS['wp_manga_volume'] = WP_DB_VOLUME::get_instance();
