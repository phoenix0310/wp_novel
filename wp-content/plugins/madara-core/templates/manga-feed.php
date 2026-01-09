<?php

use App\Madara;

global $wpdb, $wp_manga_functions, $current_page, $wp_manga_chapter, $wp_manga_setting;

$max_entries = $wp_manga_setting->get_manga_option('manga_feed_max_entries', 100); // The number of chapters to show in the feed

$urls = array();

$offset = $current_page * $max_entries;

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

header('Content-Type: '.feed_content_type('rss-http').'; charset='.get_option('blog_charset'), true);
echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>';
?>
<rss version="2.0"
        xmlns:content="http://purl.org/rss/1.0/modules/content/"
        xmlns:wfw="http://wellformedweb.org/CommentAPI/"
        xmlns:dc="http://purl.org/dc/elements/1.1/"
        xmlns:atom="http://www.w3.org/2005/Atom"
        xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
        xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
        <?php do_action('rss2_ns'); ?>>
<channel>
        <title><?php bloginfo_rss('name'); ?> - Feed</title>
        <atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
        <link><?php bloginfo_rss('url') ?></link>
        <description><?php bloginfo_rss('description') ?></description>
        <lastBuildDate><?php echo date("D, d M Y H:i:s O", strtotime(get_date_from_gmt(get_lastpostmodified('GMT')))); ?></lastBuildDate>
        <language><?php bloginfo_rss( 'language' ); ?></language>
        <sy:updatePeriod><?php echo apply_filters( 'rss_update_period', 'hourly' ); ?></sy:updatePeriod>
        <sy:updateFrequency><?php echo apply_filters( 'rss_update_frequency', '1' ); ?></sy:updateFrequency>
        <?php foreach($results as $result){
				$manga_id = $result->post_id;
				$chapter_slug = $result->chapter_slug;
				
				$link = $wp_manga_functions->build_chapter_url( $manga_id, $chapter_slug );
				
				$manga = get_post($manga_id);
				$manga_title = esc_html__('Unknown', WP_MANGA_TEXTDOMAIN);
				if($manga){
					$manga_title = $manga->post_title;
					if($manga->post_status != 'publish') continue;
				}
				
				$chapter_name = $result->chapter_name;
				$c_name_extend = $wp_manga_functions->filter_extend_name( $result->chapter_name_extend );
				$chapter_index = $result->chapter_index ? $result->chapter_index : '';
				$description = $manga->post_content;

				$seo = $wp_manga_setting->get_manga_option('single_manga_seo', 'manga');

				$chapter_full_name = "{$chapter_name}" . ( !empty( $c_name_extend ) ? " - {$c_name_extend}" : "" );
				
				$chapter_summary = $manga->post_content;
				
				$chapter_type = get_post_meta( $manga_id, '_wp_manga_chapter_type', true );
				
				if($chapter_type == 'text') {
					$chapter_content = new WP_Query( array(
						'post_parent' => $result->chapter_id,
						'post_type'   => 'chapter_text_content'
					) );

					if ( $chapter_content->have_posts() ) {

						$post = $chapter_content->the_post();

						setup_postdata( $post );
						
						$chapter_summary = wp_trim_words( get_the_content(), 55);
						
						wp_reset_postdata();
					}
				}

				$chapter_title = Madara::getOption( 'seo_chapter_title', null );
				$chapter_desc  = Madara::getOption( 'seo_chapter_desc', null );

				if( !empty( $chapter_title ) ){
					$chapter_title = str_replace( '%chapter%', $chapter_full_name, $chapter_title );
					$chapter_title = str_replace( '%title%', $manga_title, $chapter_title );
					$chapter_title = str_replace( '%chapter_index%', $chapter_index, $chapter_title );

					$chapter_name = $chapter_title;
				} else {
					$chapter_name = $manga_title . ' - ' . $chapter_full_name;
				}

				if( !empty( $chapter_desc ) ){
					$chapter_desc = str_replace( '%chapter%', $chapter_full_name, $chapter_desc );
					$chapter_desc = str_replace( '%title%', $manga_title, $chapter_desc );
					
					$chapter_desc = str_replace ( '%summary%', $chapter_summary, $chapter_desc);

					$description = $chapter_desc;
				}

				$keywords = "{$manga_title} {$chapter_name}" . ( !empty( $c_name_extend ) ? ", {$c_name_extend}" : "" );
		?>
                <item>
						<guid isPermaLink="false"><?php echo $result->chapter_id; ?></guid>
                        <title><?php echo esc_html($chapter_name); ?></title>
                        <link><?php echo esc_url($link); ?></link>
                        <pubDate><?php echo date("D, d M Y H:i:s O", strtotime(get_date_from_gmt($result->date_gmt))); ?></pubDate>
                        <description><![CDATA[<?php echo $description; ?>]]></description>
                        <?php do_action('manga_chapter_rss_item', $manga_id, $chapter_slug); ?>
                </item>
        <?php } ?>
</channel>
</rss>