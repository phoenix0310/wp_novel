<?php

	$options            = get_option( 'wp_manga_settings', array() );
	$paging_style       = isset( $options['paging_style'] ) ? $options['paging_style'] : 'load-more';
	$user_page          = isset( $options['user_page'] ) ? $options['user_page'] : '';
	$manga_archive_page = isset( $options['manga_archive_page'] ) ? $options['manga_archive_page'] : 'all';
	$supported_chapter_types = isset( $options['supported_chapter_types'] ) ? $options['supported_chapter_types'] : 'all';
	$enable_comment    = isset( $options['enable_comment'] ) ? $options['enable_comment'] : '1';
	$click_to_scroll    = isset( $options['click_to_scroll'] ) ? $options['click_to_scroll'] : '1';
    $keyboard_navigate    = isset( $options['keyboard_navigate'] ) ? $options['keyboard_navigate'] : '1';
    
	$default_comment   = isset( $options['default_comment'] ) ? $options['default_comment'] : ( $GLOBALS['madara_disqus_comments']->is_disqus_active() ? 'disqus' : '' );
	$related_manga     = isset( $options['related_manga'] ) ? $options['related_manga'] : '1';
	$single_manga_seo  = isset( $options['single_manga_seo'] ) ? $options['single_manga_seo'] : '1';
	$related_by        = isset( $options['related_by'] ) ? $options['related_by'] : 'related_genre';
	$hosting_selection = isset( $options['hosting_selection'] ) ? $options['hosting_selection'] : '1';
    
	$hosting_anonymous_name = isset( $options['hosting_anonymous_name'] ) ? $options['hosting_anonymous_name'] : '1';
	
	$breadcrumb_all_manga_link = isset( $options['breadcrumb_all_manga_link'] ) ? $options['breadcrumb_all_manga_link'] : '0';
	
	$breadcrumb_first_genre_link = isset( $options['breadcrumb_first_genre_link'] ) ? $options['breadcrumb_first_genre_link'] : '1';
	
	$navigation_manga_info = isset( $options['navigation_manga_info'] ) ? $options['navigation_manga_info'] : '1';
	
	$guest_reading_history = isset( $options['guest_reading_history'] ) ? $options['guest_reading_history'] : '1';
	
	$user_can_upload_avatar = isset( $options['user_can_upload_avatar'] ) ? $options['user_can_upload_avatar'] : '1';
	
	$manga_slug                   = isset( $options['manga_slug'] ) ? $options['manga_slug'] : 'manga';
	
	$manga_slug_or_id = isset( $options['manga_slug_or_id'] ) ? $options['manga_slug_or_id'] : 'slug';
	
	$chapter_slug_or_id = isset( $options['chapter_slug_or_id'] ) ? $options['chapter_slug_or_id'] : 'slug';
	
	$manga_genres_slug            = isset( $options['manga_genres_slug'] ) ? $options['manga_genres_slug'] : 'manga-genre';
	
	$manga_author_slug            = isset( $options['manga_author_slug'] ) ? $options['manga_author_slug'] : 'manga-author';
	
	$manga_artist_slug            = isset( $options['manga_artist_slug'] ) ? $options['manga_artist_slug'] : 'manga-artist';
	
	$manga_tag_slug            = isset( $options['manga_tag_slug'] ) ? $options['manga_tag_slug'] : 'manga-tag';
	
	$manga_release_slug            = isset( $options['manga_release_slug'] ) ? $options['manga_release_slug'] : 'manga-release';
	
	$manga_paged_var            = isset( $options['manga_paged_var'] ) ? $options['manga_paged_var'] : 'manga-paged';
	
	$default_storage              = isset( $options['default_storage'] ) ? $options['default_storage'] : 'local';

	$loading_bootstrap   = isset( $options['loading_bootstrap'] ) ? $options['loading_bootstrap'] : '1';
	$loading_slick       = isset( $options['loading_slick'] ) ? $options['loading_slick'] : '1';
	$loading_fontawesome = isset( $options['loading_fontawesome'] ) ? $options['loading_fontawesome'] : '1';
	$loading_ionicon     = isset( $options['loading_ionicon'] ) ? $options['loading_ionicon'] : '1';
	$manga_feed_max_entries = isset( $options['manga_feed_max_entries'] ) ? $options['manga_feed_max_entries'] : 100;
	$admin_show_chapter_type = isset( $options['admin_show_chapter_type'] ) ? $options['admin_show_chapter_type'] : '1';
	$encrypt_manga_folder_name = isset( $options['encrypt_manga_folder_name'] ) ? $options['encrypt_manga_folder_name'] : '1';
	$encrypt_chapter_folder_name = isset( $options['encrypt_chapter_folder_name'] ) ? $options['encrypt_chapter_folder_name'] : '1';
	$admin_hide_bar              = isset( $options['admin_hide_bar'] ) ? $options['admin_hide_bar'] : '0';
	$new_chap_notify             = isset( $options['new_chap_notify'] ) ? $options['new_chap_notify'] : '0';
	$webpush_notification        = isset( $options['webpush_noti'] ) ? $options['webpush_noti'] : '0';
	$webpush_notifcation_content = isset( $options['webpush_noti_content'] ) ? $options['webpush_noti_content'] : '%manga% has new chapter %chapter%!';
	$default_video_server = isset($options['default_video_server']) ? $options['default_video_server'] : '';
	$reading_style_selection = isset($options['reading_style_selection']) ? $options['reading_style_selection'] : '0';
	$user_rating = isset($options['user_rating']) ? $options['user_rating'] : 1;
    $guest_rating = isset($options['guest_rating']) ? $options['guest_rating'] : 1;
    $manga_view = isset($options['manga_view']) ? $options['manga_view'] : 1;
	$chapter_view = isset($options['chapter_view']) ? $options['chapter_view'] : 1;
	$user_bookmark = isset($options['user_bookmark']) ? $options['user_bookmark'] : 1;
	$user_bookmark_max = isset($options['user_bookmark_max']) ? $options['user_bookmark_max'] : 30;

	$custom_avatar_size = isset($options['custom_avatar_size']) ? $options['custom_avatar_size'] : '';
	$custom_avatar_min = isset($options['custom_avatar_min']) ? $options['custom_avatar_min'] : '';
?>
<script>
jQuery(document).ready(function($) {
	$('.wp-manga-nav-tab').on('click', function(e) {
		e.preventDefault();
		var target = $(this).data('tab');
		
		$('.wp-manga-nav-tab').removeClass('nav-tab-active');
		$(this).addClass('nav-tab-active');
		
		$('.wp-manga-tab-content').removeClass('active');
		$('#' + target).addClass('active');
	});
});
</script>
<div class="wrap wp-manga-wrap">
    <h2><?php echo get_admin_page_title(); ?></h2>
    <form method="post">
		<div id="wp-manga-core-settings">
			<h3><?php esc_html_e( 'WP Manga Core Settings', WP_MANGA_TEXTDOMAIN ); ?></h3>
		<nav class="nav-tab-wrapper wp-manga-tabs">
			<a href="#" class="nav-tab wp-manga-nav-tab nav-tab-active" data-tab="tab-permalink"><?php esc_html_e( 'Permalink', WP_MANGA_TEXTDOMAIN ); ?></a>
			<a href="#" class="nav-tab wp-manga-nav-tab" data-tab="tab-general"><?php esc_html_e( 'General', WP_MANGA_TEXTDOMAIN ); ?></a>
			<a href="#" class="nav-tab wp-manga-nav-tab" data-tab="tab-archives"><?php esc_html_e( 'Archives', WP_MANGA_TEXTDOMAIN ); ?></a>
			<a href="#" class="nav-tab wp-manga-nav-tab" data-tab="tab-detail"><?php esc_html_e( 'Detail Page', WP_MANGA_TEXTDOMAIN ); ?></a>
			<a href="#" class="nav-tab wp-manga-nav-tab" data-tab="tab-reading"><?php esc_html_e( 'Reading Page', WP_MANGA_TEXTDOMAIN ); ?></a>
			<a href="#" class="nav-tab wp-manga-nav-tab" data-tab="tab-user"><?php esc_html_e( 'User Settings', WP_MANGA_TEXTDOMAIN ); ?></a>
		</nav>
		
		<div id="tab-permalink" class="wp-manga-tab-content active">
        <div class="section">
			<h2 class="title"><?php esc_html_e( 'Manga Permalink Settings', WP_MANGA_TEXTDOMAIN ); ?></h2>
			<table class="form-table">
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Manga Single Slug', WP_MANGA_TEXTDOMAIN ); ?>
					</th>
					<td>
						<p>
							<input type="text" name="wp_manga_settings[manga_slug]" value="<?php echo esc_attr( $manga_slug ); ?>">
							<br/>
							<span class="description"> <?php _e( 'Change slug for Single Manga, default slug is <strong> manga </strong>.', WP_MANGA_TEXTDOMAIN ); ?> </span>
						</p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Manga Genres Slug', WP_MANGA_TEXTDOMAIN ); ?>
					</th>
					<td>
						<p>
							<input type="text" name="wp_manga_settings[manga_genres_slug]" value="<?php echo esc_attr( $manga_genres_slug ); ?>">
							<br/>
							<span class="description"> <?php _e( 'Change slug for Manga Genres Page, default slug is <strong> manga-genre </strong>.', WP_MANGA_TEXTDOMAIN ); ?> </span>
						</p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Manga Author Slug', WP_MANGA_TEXTDOMAIN ); ?>
					</th>
					<td>
						<p>
							<input type="text" name="wp_manga_settings[manga_author_slug]" value="<?php echo esc_attr( $manga_author_slug ); ?>">
							<br/>
							<span class="description"> <?php _e( 'Change slug for Manga Author Page, default slug is <strong> manga-author </strong>.', WP_MANGA_TEXTDOMAIN ); ?> </span>
						</p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Manga Artist Slug', WP_MANGA_TEXTDOMAIN ); ?>
					</th>
					<td>
						<p>
							<input type="text" name="wp_manga_settings[manga_artist_slug]" value="<?php echo esc_attr( $manga_artist_slug ); ?>">
							<br/>
							<span class="description"> <?php _e( 'Change slug for Manga Artist Page, default slug is <strong> manga-artist </strong>.', WP_MANGA_TEXTDOMAIN ); ?> </span>
						</p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Manga Tag Slug', WP_MANGA_TEXTDOMAIN ); ?>
					</th>
					<td>
						<p>
							<input type="text" name="wp_manga_settings[manga_tag_slug]" value="<?php echo esc_attr( $manga_tag_slug ); ?>">
							<br/>
							<span class="description"> <?php _e( 'Change slug for Manga Tag Page, default slug is <strong> manga-tag </strong>. Do not use default "tag" slug', WP_MANGA_TEXTDOMAIN ); ?> </span>
						</p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Manga Release Year Slug', WP_MANGA_TEXTDOMAIN ); ?>
					</th>
					<td>
						<p>
							<input type="text" name="wp_manga_settings[manga_release_slug]" value="<?php echo esc_attr( $manga_release_slug ); ?>">
							<br/>
							<span class="description"> <?php _e( 'Change slug for Manga Release Year Page, default slug is <strong> manga-release </strong>.', WP_MANGA_TEXTDOMAIN ); ?> </span>
						</p>
					</td>
				</tr>
			</table>
			<p style="color:#FF0000"><?php _e('Remember to save the <b>Settings > Permalink</b> setting again if you change any of above values',WP_MANGA_TEXTDOMAIN);?></p>
		</div>
		</div>
		
		<div id="tab-general" class="wp-manga-tab-content">
        <div class="section">
			<h2 class="title"><?php esc_html_e( 'Manga General Settings', WP_MANGA_TEXTDOMAIN ); ?></h2>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Supported Chapter Types', WP_MANGA_TEXTDOMAIN ) ?></th>
					<td>
						<p>
							<select name="wp_manga_settings[supported_chapter_types]">
								<option value="all" <?php selected( 'all', $supported_chapter_types, true ) ?>><?php esc_html_e( 'All', WP_MANGA_TEXTDOMAIN ) ?></option>
								<option value="manga" <?php selected( 'manga', $supported_chapter_types, true ) ?>><?php esc_html_e( 'Manga', WP_MANGA_TEXTDOMAIN ) ?></option>
								<option value="text" <?php selected( 'text', $supported_chapter_types, true ) ?>><?php esc_html_e( 'Novel', WP_MANGA_TEXTDOMAIN ) ?></option>
								<option value="video" <?php selected( 'video', $supported_chapter_types, true ) ?>><?php esc_html_e( 'Video', WP_MANGA_TEXTDOMAIN ) ?></option>
							</select>
							<br><span class="description"><?php esc_html_e( 'Choose the chapter types that are supported by the site. When you choose a specific type, the newly created manga will be assigned to that type.', WP_MANGA_TEXTDOMAIN ) ?></span>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Manga Archive Page', WP_MANGA_TEXTDOMAIN ) ?></th>
					<td>
						<p>
							<?php
								wp_dropdown_pages( array(
									'name'              => 'wp_manga_settings[manga_archive_page]',
									'show_option_none'  => __( 'Default', WP_MANGA_TEXTDOMAIN ),
									'option_none_value' => 0,
									'selected'          => $manga_archive_page,
								) );
							?>
							<br><span class="description"><?php esc_html_e( 'Choose page for Manga archive to show all manga.', WP_MANGA_TEXTDOMAIN ) ?></span>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'User Page', WP_MANGA_TEXTDOMAIN ) ?></th>
					<td>
						<p>
							<?php
								wp_dropdown_pages( array(
									'name'              => 'wp_manga_settings[user_page]',
									'show_option_none'  => esc_html__( 'Select User Page', WP_MANGA_TEXTDOMAIN ),
									'option_none_value' => 0,
									'selected'          => $user_page,
								) );

							?>
							<br><span class="description"><?php _e( 'A page display user\'s bookmark, history and settings. The <code>[manga-user-page]</code> short code must be on this page.', 'aw-twitch-press' ) ?></span>
						</p>
					</td>
				</tr>
                <tr>
					<th scope="row">
						<?php esc_html_e( 'Loading Bootstrap', WP_MANGA_TEXTDOMAIN ); ?>
					</th>
					<td>
						<p>
							<label for="loading-bootstrap">
								<input type="checkbox" name="wp_manga_settings[loading_bootstrap]" <?php checked( $loading_bootstrap, '1' ); ?> value="1" id="loading-bootstrap">
								<?php _e( 'Option to turn off loading Bootstrap', WP_MANGA_TEXTDOMAIN ); ?>
								<br/>
								<span class="description"> <?php _e( 'Turn off loading Bootstrap might break Manga pages layout. However, in some cases, your theme already has Bootstrap, then you can switch this off to avoid conflicts.', WP_MANGA_TEXTDOMAIN ); ?> </span>
								<br/>
								<span class="description"> <?php _e( 'By default, plugin would check if your theme already has Bootstrap and turn off this.', WP_MANGA_TEXTDOMAIN ); ?> </span>
							</label>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Loading Slick', WP_MANGA_TEXTDOMAIN ); ?>
					</th>
					<td>
						<p>
							<label for="loading-slick">
								<input type="checkbox" name="wp_manga_settings[loading_slick]" <?php checked( $loading_slick, '1' ); ?> value="1" id="loading-slick">
								<?php _e( 'Option to turn off loading slick', WP_MANGA_TEXTDOMAIN ); ?>
							</label>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Loading FontAwesome', WP_MANGA_TEXTDOMAIN ); ?>
					</th>
					<td>
						<p>
							<label for="loading-fontawesome">
								<input type="checkbox" name="wp_manga_settings[loading_fontawesome]" <?php checked( $loading_fontawesome, '1' ); ?> value="1" id="loading-fontawesome">
								<?php _e( 'Option to turn off loading FontAwesome', WP_MANGA_TEXTDOMAIN ); ?>
							</label>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Loading IonIcons', WP_MANGA_TEXTDOMAIN ); ?>
					</th>
					<td>
						<p>
							<label for="loading-ionicon">
								<input type="checkbox" name="wp_manga_settings[loading_ionicon]" <?php checked( $loading_ionicon, '1' ); ?> value="1" id="loading-ionicon">
								<?php esc_html_e( 'Option to turn off loading IonIcons', WP_MANGA_TEXTDOMAIN ); ?>
							</label>
						</p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Manga Chapters Feed', WP_MANGA_TEXTDOMAIN ); ?>
					</th>
					<td>
						<p>
								<input type="text" name="wp_manga_settings[manga_feed_max_entries]" value="<?php esc_attr_e($manga_feed_max_entries);?>">
								<br/>
								<span class="description">
								<?php echo wp_kses( sprintf(__('Number of entries in the Chapters Feed. Link to Chapters Feed: <a href="%s" target="_blank">Chapters Feed</a>', WP_MANGA_TEXTDOMAIN ), get_site_url() . '/feed/manga-chapters'), array('a' => array('href'=> 1, 'target'=>1))); ?>
								</span>
							</label>
						</p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Show Chapter Type in Admin', WP_MANGA_TEXTDOMAIN ); ?>
					</th>
					<td>
						<p>
							<label for="loading-ionicon">
								<input type="checkbox" name="wp_manga_settings[admin_show_chapter_type]" <?php checked( $admin_show_chapter_type, '1' ); ?> value="1" id="admin_show_chapter_type">
								<?php esc_html_e( 'Show', WP_MANGA_TEXTDOMAIN ); ?>
							</label>
							<br/>
							<span class="description">
								<?php esc_html_e('If your site has more than 1 Chapter Type (manga, novel, video), you can check this option so in Manga > All Mangas page, you will see a column showing Chapter Type', WP_MANGA_TEXTDOMAIN); ?>
							</span>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<?php esc_html_e( 'Encrypt Manga Folder Name', WP_MANGA_TEXTDOMAIN ); ?>
					</th>
					<td>
						<p>
							<label for="loading-ionicon">
								<input type="checkbox" name="wp_manga_settings[encrypt_manga_folder_name]" <?php checked( $encrypt_manga_folder_name, '1' ); ?> value="1" id="encrypt_manga_folder_name">
								<?php esc_html_e( 'Yes', WP_MANGA_TEXTDOMAIN ); ?>
							</label>
							<br/>
							<span class="description">
								<?php esc_html_e('By default, Manga Folder Name is generated automatically and uniquely. If you want to keep the Folder Name the same as the Manga Name, uncheck this. Note that it uses the Manga Name in the first time you save the post and it will not be updated afterward.',WP_MANGA_TEXTDOMAIN); ?>
							</span>
						</p>
					</td>
					
				</tr>
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Encrypt Chapter Folder Name', WP_MANGA_TEXTDOMAIN ); ?>
					</th>
					<td>
						<p>
							<label for="loading-ionicon">
								<input type="checkbox" name="wp_manga_settings[encrypt_chapter_folder_name]" <?php checked( $encrypt_chapter_folder_name, '1' ); ?> value="1" id="encrypt_chapter_folder_name">
								<?php esc_html_e( 'Yes', WP_MANGA_TEXTDOMAIN ); ?>
							</label>
							<br/>
							<span class="description">
								<?php esc_html_e('By default, Chapter Folder Name is generated automatically and uniquely when you upload chapters. If you want to keep the Folder Name the same as the Chapter Name, uncheck this. Make sure your Chapter Name is unique, or chapter images may be overriden.',WP_MANGA_TEXTDOMAIN); ?>
							</span>
						</p>
					</td>
				</tr>

			</table>
		</div>
		</div>
		
		<div id="tab-archives" class="wp-manga-tab-content">
        <div class="section">
			<h2 class="title"><?php esc_html_e( 'Manga Archives Page Settings', WP_MANGA_TEXTDOMAIN ); ?></h2>
			<table class="form-table">
                <tr>
					<th scope="row"><?php esc_html_e( 'Paging Style', WP_MANGA_TEXTDOMAIN ) ?></th>
					<td>
						<p>
							<select class="regular-text" name="wp_manga_settings[paging_style]" type="text" class="large-text">
								<option value="default" <?php selected( 'default', $paging_style, true ) ?>><?php esc_html_e( 'Default', WP_MANGA_TEXTDOMAIN ) ?></option>
								<option value="load-more" <?php selected( 'load-more', $paging_style, true ) ?>><?php esc_html_e( 'Load More Button', WP_MANGA_TEXTDOMAIN ) ?></option>
							</select>
						</p>
					</td>
				</tr>
            </table>
        </div>
		</div>
		
		<div id="tab-detail" class="wp-manga-tab-content">
        <div class="section">
			<h2 class="title"><?php esc_html_e( 'Manga Detail Page Settings', WP_MANGA_TEXTDOMAIN ); ?></h2>
			<table class="form-table">
                <tr>
					<th scope="row"><?php esc_html_e( 'SEO', WP_MANGA_TEXTDOMAIN ); ?></th>
					<td>
						<p>
							<label for="single_manga_seo">
								<input type="checkbox" id="single_manga_seo" name="wp_manga_settings[single_manga_seo]" value="1" <?php checked( 1, $single_manga_seo, true ); ?> >
								<?php esc_html_e( 'Add website name to meta title tag & meta keywords tag', WP_MANGA_TEXTDOMAIN ); ?>
							</label>
						</p>
					</td>
				</tr>
                
                <tr>
					<th scope="row"><?php esc_html_e( 'Manga Rating', WP_MANGA_TEXTDOMAIN ) ?></th>
					<td>
						<p>
							<label for="user_rating">
								<input type="checkbox" id="user_rating" name="wp_manga_settings[user_rating]" value="1" <?php checked( 1, $user_rating, true ); ?>>
								<?php esc_html_e( 'Enable Rating for manga', WP_MANGA_TEXTDOMAIN ) ?>
							</label>
						</p>
					</td>
				</tr>
                
                <tr>
					<th scope="row"><?php esc_html_e( 'Manga Guest Rating', WP_MANGA_TEXTDOMAIN ) ?></th>
					<td>
						<p>
							<label for="guest_rating">
								<input type="checkbox" id="guest_rating" name="wp_manga_settings[guest_rating]" value="1" <?php checked( 1, $guest_rating, true ); ?>>
								<?php esc_html_e( 'Allow Guests to rate. The above "Manga Rating" must be checked', WP_MANGA_TEXTDOMAIN ) ?>
							</label>
						</p>
					</td>
				</tr>
                
                <tr>
					<th scope="row"><?php esc_html_e( 'Manga Views', WP_MANGA_TEXTDOMAIN ) ?></th>
					<td>
						<p>
							<label for="manga_view">
								<input type="checkbox" id="manga_view" name="wp_manga_settings[manga_view]" value="1" <?php checked( 1, $manga_view, true ); ?>>
								<?php esc_html_e( 'Enable Views Count for Manga', WP_MANGA_TEXTDOMAIN ) ?>
							</label>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Chapter Views', WP_MANGA_TEXTDOMAIN ) ?></th>
					<td>
						<p>
							<label for="chapter_view">
								<input type="checkbox" id="chapter_view" name="wp_manga_settings[chapter_view]" value="1" <?php checked( 1, $chapter_view, true ); ?>>
								<?php esc_html_e( 'Enable Views Count for Chapters', WP_MANGA_TEXTDOMAIN ) ?>
							</label>
						</p>
					</td>
				</tr>
                
                <tr>
					<th scope="row"><?php esc_html_e( 'Saving reading history for Guests', WP_MANGA_TEXTDOMAIN ); ?></th>
					<td>
						<p>
								<input type="checkbox" id="guest_reading_history" name="wp_manga_settings[guest_reading_history]" value="1" <?php checked( 1, $guest_reading_history, true ); ?> >
								<?php esc_html_e( 'Enable', WP_MANGA_TEXTDOMAIN ); ?>
						</p>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><?php esc_html_e( 'Manga Bookmark', WP_MANGA_TEXTDOMAIN ) ?></th>
					<td>
						<p>
							<label for="user_bookmark">
								<input type="checkbox" id="user_bookmark" name="wp_manga_settings[user_bookmark]" value="1" <?php checked( 1, $user_bookmark, true ); ?>>
								<?php esc_html_e( 'Enable Bookmark feature', WP_MANGA_TEXTDOMAIN ) ?>
							</label>
						</p>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><?php esc_html_e( 'Maximum Bookmark Items', WP_MANGA_TEXTDOMAIN ) ?></th>
					<td>
						<p>
							<input type="number" class="" id="user_bookmark_max" name="wp_manga_settings[user_bookmark_max]" value="<?php echo esc_attr($user_bookmark_max);?>"><br/>
							<span class="description"> <?php esc_html_e( 'We should limit number of Mangas an user can bookmark to prevent memory limit issue', WP_MANGA_TEXTDOMAIN ); ?> </span>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Manga Comment', WP_MANGA_TEXTDOMAIN ) ?></th>
					<td>
						<p>
							<label for="enable_comment">
								<input type="checkbox" id="enable_comment" name="wp_manga_settings[enable_comment]" value="1" <?php checked( 1, $enable_comment, true ); ?>>
								<?php esc_html_e( 'Enable Comment for manga', WP_MANGA_TEXTDOMAIN ) ?>
							</label>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Default Comment', WP_MANGA_TEXTDOMAIN ) ?></th>
					<td>
						<select class="regular-text" name="wp_manga_settings[default_comment]">
							<option value="disqus" <?php selected( $default_comment, 'disqus' ); ?>><?php esc_html_e( 'Disqus Comment', WP_MANGA_TEXTDOMAIN ); ?></option>
							<option value="wp" <?php selected( $default_comment, 'wp' ); ?>><?php esc_html_e( 'Wordpress', WP_MANGA_TEXTDOMAIN ); ?></option>
							<option value="both" <?php selected( $default_comment, 'both' ); ?>><?php esc_html_e( 'Both', WP_MANGA_TEXTDOMAIN ); ?></option>
						</select>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Related Manga', WP_MANGA_TEXTDOMAIN ); ?></th>
					<td>
						<p>
							<label for="related_manga">
								<input type="checkbox" id="related_manga" name="wp_manga_settings[related_manga]" value="1" <?php checked( 1, is_array($related_manga) ? $related_manga['related_by'] : $related_manga, true ); ?>>
								<?php esc_html_e( 'Enable Related Manga', WP_MANGA_TEXTDOMAIN ); ?>
							</label>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Related by', WP_MANGA_TEXTDOMAIN ); ?></th>
					<td>
						<p>
							<select class="regular-text" name="wp_manga_settings[related_by]">
								<option value="related_author" <?php selected( $related_by, 'related_author' ); ?> ><?php esc_html_e( 'Author', WP_MANGA_TEXTDOMAIN ); ?></option>
								<option value="related_year" <?php selected( $related_by, 'related_year' ); ?>><?php esc_html_e( 'Release Year', WP_MANGA_TEXTDOMAIN ); ?></option>
								<option value="related_artist" <?php selected( $related_by, 'related_artist' ); ?>><?php esc_html_e( 'Artists', WP_MANGA_TEXTDOMAIN ); ?></option>
								<option value="related_genre" <?php selected( $related_by, 'related_genre' ); ?>><?php esc_html_e( 'Genres', WP_MANGA_TEXTDOMAIN ); ?></option>
							</select>
						</p>
					</td>
				</tr>
            </table>
        </div>
		</div>
		
		<div id="tab-reading" class="wp-manga-tab-content">
		<div class="section">
			<h2 class="title"><?php esc_html_e( 'Manga Reading Page Settings', WP_MANGA_TEXTDOMAIN ); ?></h2>
			<table class="form-table">

				<tr>
					<th scope="row"><?php esc_html_e( 'Hosting Selection', WP_MANGA_TEXTDOMAIN ) ?></th>
					<td>
						<p>
							<label for="hosting_selection">
								<input type="checkbox" id="hosting_selection" name="wp_manga_settings[hosting_selection]" value="1" <?php checked( 1, $hosting_selection, true ); ?>>
								<?php esc_html_e( 'Show Hosting Selection for Manga', WP_MANGA_TEXTDOMAIN ) ?>
							</label> <br/>
							<span class="description"> <?php esc_html_e( 'Uncheck to hide Hosting Selection. Hosting Selection should be hide if you only use one hosting for your Manga', WP_MANGA_TEXTDOMAIN ); ?> </span>
						</p>
					</td>
				</tr>
                
                <tr>
					<th scope="row"><?php esc_html_e( 'Use Anonymous Name for Hosting', WP_MANGA_TEXTDOMAIN ) ?></th>
					<td>
						<p>
							<label for="hosting_anonymous_name">
								<input type="checkbox" id="hosting_anonymous_name" name="wp_manga_settings[hosting_anonymous_name]" value="1" <?php checked( 1, $hosting_anonymous_name, true ); ?>>
								<?php esc_html_e( 'Enable', WP_MANGA_TEXTDOMAIN ) ?>
							</label> <br/>
							<span class="description"> <?php esc_html_e( 'Use anonymous name for hosting, for example "Server 1", "Server 2"...', WP_MANGA_TEXTDOMAIN ); ?> </span>
						</p>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><?php esc_html_e( 'Image Chapter - Reading Style Selection', WP_MANGA_TEXTDOMAIN ) ?></th>
					<td>
						<p>
							<label for="reading_style_selection">
								<input type="checkbox" id="reading_style_selection" name="wp_manga_settings[reading_style_selection]" value="1" <?php checked( 1, $reading_style_selection, true ); ?>>
								<?php esc_html_e( 'Show Reading Style selection box', WP_MANGA_TEXTDOMAIN ) ?>
							</label> <br/>
							<span class="description"> <?php esc_html_e( 'Show/hide Reading Style selection box in Chapter Reading page (ie. switching between Paged and List style. Uncheck to hide Reading Style selection box. Global setting in Theme Options and in individual User Settings will be used', WP_MANGA_TEXTDOMAIN ); ?> </span>
						</p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Image Chapter - Default Storage', WP_MANGA_TEXTDOMAIN ); ?>
					</th>
					<td>
						<p>
							<select class="regular-text" name="wp_manga_settings[default_storage]">
								<?php
									$available_host = $GLOBALS['wp_manga']->get_available_host();
									foreach ( $available_host as $host ) {
										?>
										<option value="<?php echo esc_attr( $host['value'] ); ?>" <?php selected( $default_storage, $host['value'] ); ?>><?php echo esc_attr( $host['text'] ); ?></option>
										<?php
									}
								?>
							</select> <br/>
							<span class="description"> <?php esc_html_e( 'Change default storage to upload Manga images', WP_MANGA_TEXTDOMAIN ); ?> </span>
						</p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Video Chapter - Default Server', WP_MANGA_TEXTDOMAIN ); ?>
					</th>
					<td>
						<p>
							<input type="text" class="regular-text" name="wp_manga_settings[default_video_server]" value="<?php echo esc_attr($default_video_server);?>"><br/>
							<span class="description"> <?php esc_html_e( 'Set default server for Video Chapter. Please enter exact server name here', WP_MANGA_TEXTDOMAIN ); ?> </span>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'When reading, click to scroll', WP_MANGA_TEXTDOMAIN ) ?></th>
					<td>
						<p>
							<label for="click_to_scroll">
								<input type="checkbox" id="click_to_scroll" name="wp_manga_settings[click_to_scroll]" value="1" <?php checked( 1, $click_to_scroll, true ); ?>>
								<?php esc_html_e( 'Click to Scroll page when reading chapter', WP_MANGA_TEXTDOMAIN ) ?>
							</label>
						</p>
					</td>
				</tr>
                
                <tr>
					<th scope="row"><?php esc_html_e( 'Use Left/Right Keyboard', WP_MANGA_TEXTDOMAIN ) ?></th>
					<td>
						<p>
							<label for="keyboard_navigate">
								<input type="checkbox" id="keyboard_navigate" name="wp_manga_settings[keyboard_navigate]" value="1" <?php checked( 1, $keyboard_navigate, true ); ?>>
								<?php esc_html_e( 'Next/Prev chapter pages using left/right keyboard', WP_MANGA_TEXTDOMAIN ) ?>
							</label>
						</p>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><?php esc_html_e( 'Link to All Mangas page in BreadCrumbs', WP_MANGA_TEXTDOMAIN ); ?></th>
					<td>
						<p>
							<label for="breadcrumb_all_manga_link">
								<input type="checkbox" id="breadcrumb_all_manga_link" name="wp_manga_settings[breadcrumb_all_manga_link]" value="1" <?php checked( 1, $breadcrumb_all_manga_link, true ); ?> >
								<?php esc_html_e( 'Enable', WP_MANGA_TEXTDOMAIN ); ?>
							</label>
						</p>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><?php esc_html_e( 'Link to First Genre in BreadCrumbs', WP_MANGA_TEXTDOMAIN ); ?></th>
					<td>
						<p>
							<label for="breadcrumb_first_genre_link">
								<input type="checkbox" id="breadcrumb_first_genre_link" name="wp_manga_settings[breadcrumb_first_genre_link]" value="1" <?php checked( 1, $breadcrumb_first_genre_link, true ); ?> >
								<?php esc_html_e( 'Enable', WP_MANGA_TEXTDOMAIN ); ?>
							</label><br/>
							<span class="description"> <?php esc_html_e( 'When manga belongs to several Genres, only the first genre will appear in the breadcrumbs', WP_MANGA_TEXTDOMAIN ); ?> </span>
						</p>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><?php esc_html_e( 'Link to Manga Info when reaching last chapter', WP_MANGA_TEXTDOMAIN ); ?></th>
					<td>
						<p>
							<label for="navigation_manga_info">
								<select name="wp_manga_settings[navigation_manga_info]">
									<option value="0" <?php selected( 0, $navigation_manga_info, true ); ?>><?php esc_html_e( 'Hidden', WP_MANGA_TEXTDOMAIN ); ?></option>
									<option value="1" <?php selected( 1, $navigation_manga_info, true ); ?>><?php esc_html_e( 'Show when reader reaches the end', WP_MANGA_TEXTDOMAIN ); ?></option>
									<option value="2" <?php selected( 2, $navigation_manga_info, true ); ?>><?php esc_html_e( 'Always show', WP_MANGA_TEXTDOMAIN ); ?></option>
								</select>
							</label><br/>
							<span class="description"> <?php esc_html_e( 'Display a button link to the Manga/Novel detail page', WP_MANGA_TEXTDOMAIN ); ?> </span>
						</p>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><?php esc_html_e( 'Variable name for Chapter Pagination', WP_MANGA_TEXTDOMAIN ); ?></th>
					<td>
						<p>
								<input type="text" id="manga_paged_var" name="wp_manga_settings[manga_paged_var]" value="<?php esc_attr_e($manga_paged_var);?>" >
						</p>
					</td>
				</tr>

			</table>
		</div>
		</div>
		
		<div id="tab-user" class="wp-manga-tab-content">
		<div class="section">
			<!-- Manga User Settings -->
			<h2 class="title"><?php esc_html_e( 'Manga User Settings', WP_MANGA_TEXTDOMAIN ); ?></h2>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Allow Upload Avatar', WP_MANGA_TEXTDOMAIN ); ?></th>
					<td>
						<p>
							<label for="user_can_upload_avatar">
								<input type="checkbox" id="user_can_upload_avatar" name="wp_manga_settings[user_can_upload_avatar]" value="1" <?php checked( 1, $user_can_upload_avatar, true ); ?> >
								<?php esc_html_e( 'Enable', WP_MANGA_TEXTDOMAIN ); ?>
							</label><br/>
							<span class="description"> <?php esc_html_e( 'Allow User to upload custom avatar. Or else, Gravatar is used (based on user\'s email)', WP_MANGA_TEXTDOMAIN ); ?> </span>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Avatar Max Size', WP_MANGA_TEXTDOMAIN ); ?></th>
					<td>
						<p>
							<label for="custom_avatar_size">
								<input type="number" placeholder="2" id="custom_avatar_size" name="wp_manga_settings[custom_avatar_size]" value="<?php echo $custom_avatar_size; ?>" >
							</label><br/>
							<span class="description"> <?php esc_html_e( 'Maximum size for avatar, in MB', WP_MANGA_TEXTDOMAIN ); ?> </span>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Minimum Avatar Size', WP_MANGA_TEXTDOMAIN ); ?></th>
					<td>
						<p>
							<label for="custom_avatar_min">
								<input type="number" placeholder="200" id="custom_avatar_min" name="wp_manga_settings[custom_avatar_min]" value="<?php echo $custom_avatar_min; ?>" >
							</label><br/>
							<span class="description"> <?php esc_html_e( 'Minimum avatar width&height, in px', WP_MANGA_TEXTDOMAIN ); ?> </span>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Admin Bar', WP_MANGA_TEXTDOMAIN ); ?>
					</th>
					<td>
						<p>
							<label for="loading-ionicon">
								<input type="checkbox" name="wp_manga_settings[admin_hide_bar]" <?php checked( $admin_hide_bar, '1' ); ?> value="1" id="admin-hide-bar">
								<?php _e( 'Hide Admin Bar for Administrator', WP_MANGA_TEXTDOMAIN ); ?>
							</label> <br/>
							<span class="description"> <?php _e( 'By default, Admin Bar would be hidden for all user roles but Administrator. This option will let you hide Admin Bar for Administrator role.', WP_MANGA_TEXTDOMAIN ); ?> </span>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Notify User', WP_MANGA_TEXTDOMAIN ); ?>
					</th>
					<td>
						<p>
							<label for="new_chap_notify">
								<input type="checkbox" name="wp_manga_settings[new_chap_notify]" <?php checked( $new_chap_notify, '1' ); ?> value="1" id="new_chap_notify">
								<?php _e( 'When User Bookmarked Manga has new chapter. Re-activate Madara-Core plugin one time after enabling this option to register cronjob', WP_MANGA_TEXTDOMAIN ); ?>
							</label>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Webpush Notification', WP_MANGA_TEXTDOMAIN ); ?>
					</th>
					<td>
						<p>
							<label for="webpush_noti">
								<input type="checkbox" name="wp_manga_settings[webpush_noti]" <?php checked( $webpush_notification, '1' ); ?> value="1" id="webpush_noti">
								 <?php _e( 'Send a webpush notification for users bookmarked Manga', WP_MANGA_TEXTDOMAIN ); ?>
							</label>
							
						</p>
						<p class="description"><?php _e( 'Require OneSignal – Web Push Notifications plugin', WP_MANGA_TEXTDOMAIN ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Webpush Notification Content', WP_MANGA_TEXTDOMAIN ); ?>
					</th>
					<td>
						<p>
							<label for="loading-ionicon">
								<textarea name="wp_manga_settings[webpush_noti_content]" rows="3" cols="80"><?php echo esc_html( $webpush_notifcation_content ); ?></textarea>
							</label> <br/>
							<span class="description"> <?php _e( 'Message to send notification for user. Use <strong>%manga%</strong> for Manga name, <strong>%chapter%</strong> for Chapter name', WP_MANGA_TEXTDOMAIN ); ?> </span>
						</p>
					</td>
				</tr>
			</table>
		</div>
		</div>
		</div>

		<?php do_action( 'after_madara_settings_page' ); ?>
        <button type="submit" class="button button-primary"><?php esc_attr_e( 'Save All Changes', WP_MANGA_TEXTDOMAIN ) ?></button>
    </form>
</div>
