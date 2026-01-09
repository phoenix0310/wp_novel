<?php 

add_shortcode( 'wp-manga-my-bookmarks', 'wp_manga_my_bookmarks' );

function wp_manga_my_bookmarks($atts, $content = ""){
	global $wp_manga_template;
	
	$atts = shortcode_atts( array(
		'style' => '1',
		'column' => 3
	), $atts, 'wp-manga-my-bookmarks' );
	
	$user_id = get_current_user_id();
	
	if($user_id){
		$bookmarks = get_user_meta( $user_id, '_wp_manga_bookmark', true );
		ob_start();
		if( !empty( $bookmarks ) ) {
			?>
			<div class="shortcode my-bookmarks <?php echo 'style-'.$atts['style'];?>">
				<div class="row row-eq-height">
					<?php
					$idx = 0;
					if(12 % $atts['column'] !== 0) $atts['column'] = 2;
					$class = 'col-md-' . (12 / $atts['column']);
					foreach($bookmarks as $bookmark){
						$manga_post = get_post( intval( $bookmark['id'] ) );
						
						global $post;

						$post = $manga_post;
						
						?>
						<div class="col-12 <?php echo $class;?>">
							<div class="popular-item-wrap">

								<?php if ( $atts['style'] == '1' ) {
									$wp_manga_template->load_template( 'widgets/recent-manga/content-1', false );
								} else {
									$wp_manga_template->load_template( 'widgets/recent-manga/content-2', false );
								} ?>

							</div>
						</div>
						<?php
						//reset for the next loop
						$chapter_slug = '';
						$idx++;
						if($idx % $atts['column'] == 0){
							echo '</div>';
							if($idx < count($bookmarks)){
								echo '<div class="row row-eq-height">';
							}
						}
						
					}
					
					wp_reset_postdata();
					?>
				<?php 
				// close the last one if it is odd
				if($idx % $atts['column'] != 0){?>
				</div>
				<?php } ?>
			</div>
			<?php
		}
		$html = ob_get_contents();
		ob_end_clean();
		
		return $html;
	}
}

function wp_manga_gutenberg_bookmarks_block() {
    wp_register_script(
        'wp_manga_gutenberg_bookmarks_block',
        plugins_url( 'gutenberg/manga-bookmarks.js', __FILE__ ),
        array( 'wp-blocks', 'wp-element' )
    );

	if(function_exists('register_block_type')){
    register_block_type( 'wp-manga/gutenberg-bookmarks-block', array(
        'editor_script' => 'wp_manga_gutenberg_bookmarks_block',
    ) );
	}
}
add_action( 'init', 'wp_manga_gutenberg_bookmarks_block' );