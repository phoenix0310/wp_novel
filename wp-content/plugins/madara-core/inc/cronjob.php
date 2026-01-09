<?php
class WP_MANGA_CRONJOB{
    public function __construct() {
        add_filter( 'cron_schedules', array($this, '_cron_schedules' ));
        add_action('manga_reset_week_month_views', array($this, '_reset_week_month_views'));

        add_action( 'init', array( $this, 'register_scheduled_job' ) );

        // Clean temp dir daily
        add_action( 'wp_manga_daily', array( $this, 'clean_temp_dir' ) );
    }

    function _plugin_activate(){
        
        if (! wp_next_scheduled ( 'onesignal_manga_notification' )) {
            wp_schedule_event(time(), 'onesignal_manga_notification_interval', 'onesignal_manga_notification');
        }
        
        if (! wp_next_scheduled ( 'manga_new_chapters_notification' )) {                
            wp_schedule_event(time(), 'manga_new_chapters_notification_interval', 'manga_new_chapters_notification');
        }

        if (! wp_next_scheduled ( 'manga_reset_week_month_views' )) {     
            $date = new DateTime();
            $beginOfDay = strtotime("tomorrow", $date->getTimestamp());
            wp_schedule_event($beginOfDay + 1, 'manga_reset_week_month_views_interval', 'manga_reset_week_month_views');
        }
    }

    function _cron_schedules( $schedules ){
        $schedules['onesignal_manga_notification_interval'] = array(
            'interval' => 30 * 60, // 30 minutes
            'display'  => esc_html__( 'Run every 30 minutes to send out notifications via OneSignal' ) );
    
        $schedules['manga_new_chapters_notification_interval'] = array(
            'interval' => 1 * 60, // 1 minutes
            'display'  => esc_html__( 'Run every 1 minute to update users notification' ) );
            
        $schedules['manga_reset_week_month_views_interval'] = array(
            'interval' => 1 * 60 * 60 * 24, // 1 day
            'display'  => esc_html__( 'Run every day to reset weekly and monthly views' ) );

        return $schedules;
    }

    function _reset_week_month_views(){
        global $wpdb;

        // reset daily views
        $count = $wpdb->delete(
            $wpdb->prefix . 'postmeta',
            ['meta_key' => '_wp_manga_day_views']
        );

        $count = $wpdb->delete(
            $wpdb->prefix . 'postmeta',
            ['meta_key' => '_wp_manga_day_views_value']
        );
        
        // reset week views
        $dayofweek = date('w', time()); // "0" for Sunday, --> "6" for Saturday
        if($dayofweek == get_option( 'start_of_week', 1 )){
            $count = $wpdb->delete(
                $wpdb->prefix . 'postmeta',
                ['meta_key' => '_wp_manga_week_views']
            );

            $count = $wpdb->delete(
                $wpdb->prefix . 'postmeta',
                ['meta_key' => '_wp_manga_week_views_value']
            );
        }
        
        // reset month views
        $dayofmonth = date('j', time()); // 1 to 31
        if($dayofmonth == 1){
            // reset month views
            $count = $wpdb->delete(
                $wpdb->prefix . 'postmeta',
                ['meta_key' => '_wp_manga_month_views']
            );

            $count = $wpdb->delete(
                $wpdb->prefix . 'postmeta',
                ['meta_key' => '_wp_manga_month_views_value']
            );
        }
    }

    function register_scheduled_job() {
        // register scheduled event
        if ( ! wp_next_scheduled( 'wp_manga_daily' ) ) {
            wp_schedule_event( time(), 'daily', 'wp_manga_daily' );
        }
    }

    /**
     * Cleaning job
     */
    function clean_temp_dir() {
        if ( ! get_transient( 'temp_dir_uploading' ) ) {
            global $wp_manga_storage;

            $wp_manga_storage->local_remove_storage( WP_MANGA_EXTRACT_DIR );
        }

        $temp_files = get_transient("wp_manga_tmp_deleted");
        if(!$temp_files){
            $temp_files = array();
        }
        
        foreach($temp_files as $temp_file){
            if(file_exists($temp_file)){
                unlink(($temp_file));
            }
        }
    }

    function _plugin_deactive(){
        wp_clear_scheduled_hook('wp_manga_daily');
        wp_clear_scheduled_hook('onesignal_manga_notification');
        wp_clear_scheduled_hook('manga_new_chapters_notification');
        wp_clear_scheduled_hook('manga_reset_week_month_views');
    }
}

$GLOBALS['wp_manga_cronjob'] = new WP_MANGA_CRONJOB();