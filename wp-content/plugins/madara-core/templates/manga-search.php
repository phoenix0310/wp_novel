<?php
    /**
	 * The Template for Manga Search results page
	 *
	 * This template can be overridden by copying it to your-child-theme/madara-core/manga-search.php
	 *
	 * HOWEVER, on occasion Madara will need to update template files and you
	 * (the theme developer) will need to copy the new files to your theme to
	 * maintain compatibility. We try to do this as little as possible, but it does
	 * happen. When this occurs the version of the template file will be bumped and
	 * we will list any important changes on our theme release logs.
	 * @package Madara
	 * @version 1.7.2.2
	 */
	 
	 use App\Madara;
    
	/**
	 * @package madara
	 */

	get_header();

	/**
	 * madara_before_main_page hook
	 *
	 * @hooked madara_output_before_main_page - 10
	 * @hooked madara_output_top_sidebar - 89
	 *
	 * @author
	 * @since 1.0
	 * @code     Madara
	 */

	do_action( 'madara_before_main_page' );

	global $wp_manga, $wp_manga_functions;
	$s         = isset( $_GET['s'] ) ? $_GET['s'] : '';
	$s_genre   = isset( $_GET['genre'] ) ? $_GET['genre'] : array();
	$s_author  = isset( $_GET['author'] ) ? $_GET['author'] : '';
	$s_artist  = isset( $_GET['artist'] ) ? $_GET['artist'] : '';
	$s_release = isset( $_GET['release'] ) ? $_GET['release'] : '';
	$s_status  = isset( $_GET['status'] ) ? $_GET['status'] : array();
	$s_adult = isset( $_GET['adult'] ) ? $_GET['adult'] : '';
	$s_genre_condition = isset( $_GET['op'] ) ? $_GET['op'] : '';

	$s_orderby = isset( $_GET['m_orderby'] ) ? $_GET['m_orderby'] : '';
	$s_paged   = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
	
	$s_args = madara_get_search_args();
	
	$s_query = madara_manga_query( $s_args );

	$search_header_background = madara_output_background_options( 'search_header_background' );
    $madara_ajax_search = Madara::getOption('madara_ajax_search', 'on');
?>
    <!--<header class="site-header">-->
    <div class="c-search-header__wrapper" style="<?php echo esc_attr( $search_header_background != '' ? $search_header_background : 'background-image: url(' . get_parent_theme_file_uri( '/images/bg-search.jpg' ) . ');'); ?>">
        <div class="container">

            <div class="search-content">
                <form role="search" method="get" class="search-form manga-search-form <?php echo ($madara_ajax_search == 'on' ? 'ajax' : '');?>" action="<?php echo home_url('/');?>">
						<span class="screen-reader-text"><?php esc_html_e( 'Search for:', 'madara' ); ?></span>
                        <input type="text" class="search-field manga-search-field" placeholder="<?php esc_html_e( 'Search...', 'madara' ); ?>" value="<?php echo esc_attr( stripcslashes( $s )); ?>" name="s">
						<input type="submit" class="search-submit" value="<?php esc_html_e( 'Search', 'madara' ); ?>">
						<div class="loader-inner line-scale">
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                        </div>
						<i class="icon ion-md-search"></i>	
                        <input type="hidden" name="post_type" value="wp-manga">
                        <script>
							jQuery(document).ready(function ($) {
								$('form.search-form input.search-field[name="s"]').keyup(function () {
									var s = $('form.search-form input.search-field[name="s"]').val();
									$('form.search-advanced-form input[name="s"]').val(s);
								});

								$('.search-form').on('submit', function(e){
									e.preventDefault();
									$('.search-advanced-form').submit();
								});
							});
                        </script>
                </form>
                <a class="btn-search-adv collapsed" data-toggle="collapse" data-target="#search-advanced"><?php esc_html_e( 'Advanced', 'madara' ); ?>
                    <span class="icon-search-adv"></span></a>
            </div>
            <div class="collapse" id="search-advanced">
                <form action="<?php echo home_url('/');?>" method="get" role="form" class="search-advanced-form">
                    <input type="hidden" name="s" id="adv-s" value="<?php echo esc_attr($s);?>">
                    <input type="hidden" name="post_type" value="wp-manga">
                    <!-- Manga Genres -->
                    <div class="form-group checkbox-group row">
						<?php
							$genre_args = array(
								'taxonomy'   => 'wp-manga-genre',
								'hide_empty' => false
							);

							$genres = get_terms( $genre_args );

							if ( ! empty( $genres ) ) {
								foreach ( $genres as $genre ) {
									$checked = array_search( $genre->slug, $s_genre ) !== false ? 'checked' : '';
									?>
                                    <div class="checkbox col-6 col-sm-4 col-md-2 ">
                                        <input id="<?php echo esc_attr( $genre->slug ); ?>" value="<?php echo esc_attr( $genre->slug ); ?>" name="genre[]" type="checkbox" <?php echo esc_attr( $checked ); ?>/>
                                        <label for="<?php echo esc_attr( $genre->slug ); ?>"> <?php echo esc_html( $genre->name ); ?> </label>
                                    </div>
									<?php
								}
							}
						?>

                    </div>
					<!-- Genre Condition -->
                    <div class="form-group">
                        <span><?php esc_html_e( 'Genres condition', 'madara' ); ?></span>
                        <select name="op" class="form-control">
							<option value="" <?php selected($s_genre_condition, '');?>><?php esc_html_e('OR (having one of selected genres)', 'madara');?></option>
							<option value="1" <?php selected($s_genre_condition, 1);?>><?php esc_html_e('AND (having all selected genres)', 'madara');?></option>
						</select>
                    </div>
					<!-- Manga Author -->
                    <div class="form-group">
                        <span><?php esc_html_e( 'Author', 'madara' ); ?></span>
                        <input type="text" class="form-control" name="author" placeholder="<?php esc_attr_e( 'Author', 'madara' ) ?>" value="<?php echo esc_attr( $s_author ); ?>">
                    </div>
                    <!-- Manga Artist -->
                    <div class="form-group">
                        <span><?php esc_html_e( 'Artist', 'madara' ); ?></span>
                        <input type="text" class="form-control" name="artist" placeholder="<?php esc_attr_e( 'Artist', 'madara' ); ?>" value="<?php echo esc_attr( $s_artist ); ?>">
                    </div>
                    <!-- Manga Release -->
                    <div class="form-group">
                        <span><?php esc_html_e( 'Year of Released', 'madara' ); ?></span>
                        <input type="text" class="form-control" name="release" placeholder="<?php esc_attr_e( 'Year', 'madara' ); ?>" value="<?php echo esc_attr( $s_release ); ?>">
                    </div>
					<!-- Manga Adult Content -->
                    <div class="form-group">
                        <span><?php esc_html_e( 'Adult content', 'madara' ); ?></span>
						<select name="adult" class="form-control">
							<option value="" <?php selected($s_adult, '');?>><?php esc_html_e('All', 'madara');?></option>
							<option value="0" <?php selected($s_adult, 0);?>><?php esc_html_e('None adult content', 'madara');?></option>
							<option value="1" <?php selected($s_adult, 1);?>><?php esc_html_e('Only adult content', 'madara');?></option>
						</select>
                    </div>
                    <!-- Manga Status -->
                    <div class="form-group">
                        <span><?php esc_html_e( 'Status', 'madara' ); ?></span>
                        <div class="checkbox-inline">
                            <input id="complete" type="checkbox" name="status[]" <?php echo in_array( 'end', $s_status ) ? 'checked' : '' ; ?> value="end" />
                            <label for="complete"><?php esc_html_e( 'Completed', 'madara' ); ?></label>
                        </div>
                        <div class="checkbox-inline">
                            <input id="on-going" type="checkbox" name="status[]" <?php echo in_array( 'on-going', $s_status ) ? 'checked' : '' ; ?> value="on-going" />
                            <label for="on-going"><?php esc_html_e( 'Ongoing', 'madara' ); ?></label>
                        </div>
						<div class="checkbox-inline">
                            <input id="canceled" type="checkbox" name="status[]" <?php echo in_array( 'canceled', $s_status ) ? 'checked' : '' ; ?> value="canceled" />
                            <label for="canceled"><?php esc_html_e( 'Canceled', 'madara' ); ?></label>
                        </div>
						<div class="checkbox-inline">
                            <input id="on-hold" type="checkbox" name="status[]" <?php echo in_array( 'on-hold', $s_status ) ? 'checked' : '' ; ?> value="on-hold" />
                            <label for="on-hold"><?php esc_html_e( 'On Hold', 'madara' ); ?></label>
                        </div>
                    </div>
                    <div class="form-group group-btn">
                        <button type="submit" class="c-btn c-btn_style-1 search-adv-submit"><?php esc_html_e( 'Search', 'madara' ); ?></button>
                        <button type="submit" class="c-btn c-btn_style-2 search-adv-reset"><?php esc_html_e( 'Reset', 'madara' ); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div><!--</header>-->
    <script type="text/javascript">
		var manga_args = <?php echo str_replace( '\/', '/', json_encode( $s_args ) ); ?>;
    </script>
    <div class="c-page-content">
        <div class="content-area">
            <div class="container">
                <div class="row">
                    <div class="main-col col-md-12 sidebar-hidden">

						<?php get_template_part( 'html/main-bodytop' ); ?>

                        <!-- container & no-sidebar-->
                        <div class="main-col-inner">
							<?php
								if ( $s_query->have_posts() ) {
									?>
                                    <div class="search-wrap">
                                        <div class="tab-wrap">
                                            <div class="c-blog__heading style-2 font-heading">
                                                <h1 class="h4">
                                                    <i class="<?php madara_default_heading_icon(); ?>"></i> <?php echo sprintf( _n( '%s result for "%s"', '%s results for "%s"', $s_query->found_posts, 'madara' ), $s_query->found_posts, $s ); ?>
                                                </h1>
												<?php get_template_part( 'madara-core/manga-filter' ); ?>
                                            </div>
                                        </div>
                                        <!-- Tab panes -->
                                        <div class="tab-content-wrap">
                                            <div role="tabpanel" class="c-tabs-item">
													<?php
													
														while ( $s_query->have_posts() ) {

															$s_query->the_post();
															get_template_part( 'madara-core/content/content', 'search' );

														}														
														
														wp_reset_postdata();
													?>
                                            </div>
											<?php
											$madara_pagination = new App\Views\ParsePagination();
													$madara_pagination->renderPageNavigation( '.c-tabs-item', 'madara-core/content/content-search', $s_query );
													?>
                                        </div>
                                    </div>
									<?php
								} else {
									get_template_part( 'madara-core/content/content', 'none' );
								}
							?>
                        </div>

						<?php get_template_part( 'html/main-bodybottom' ); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
<?php

	/**
	 * madara_after_main_page hook
	 *
	 * @hooked madara_output_after_main_page - 90
	 * @hooked madara_output_bottom_sidebar - 91
	 *
	 * @author
	 * @since 1.0
	 * @code     Madara
	 */
	do_action( 'madara_after_main_page' );

	get_footer();
