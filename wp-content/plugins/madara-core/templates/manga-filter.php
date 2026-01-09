<?php

	/**
	 * The Template for Manga Filter tab in Manga Archives page
	 *
	 * This template can be overridden by copying it to your-child-theme/madara-core/manga-filter.php
	 *
	 * HOWEVER, on occasion Madara will need to update template files and you
	 * (the theme developer) will need to copy the new files to your theme to
	 * maintain compatibility. We try to do this as little as possible, but it does
	 * happen. When this occurs the version of the template file will be bumped and
	 * we will list any important changes on our theme release logs.
	 * @package Madara
	 * @version 1.7.2.2
	 */
	 
	 $orderby = isset( $_GET['m_orderby'] ) ? $_GET['m_orderby'] : '';

?>

<div class="c-nav-tabs">
    <span> <?php esc_html_e( 'Order by', 'madara' ); ?> </span>
    <ul class="c-tabs-content">
		<?php 
		
		if(is_search()){?>
        <li class="<?php echo esc_attr($orderby == '' ? "active" : ''); ?>">
            <a href="<?php echo is_search() ? madara_search_filter_url( '' ) : ''; ?>">
				<?php esc_html_e( 'Relevance', 'madara' ); ?>
            </a>
        </li>
		
		<?php }?>
		<li class="<?php echo esc_attr($orderby == 'latest' ? "active" : ''); ?>">
            <a href="<?php echo is_search() ? madara_search_filter_url( 'latest' ) : '?m_orderby=latest'; ?>">
				<?php esc_html_e( 'Latest', 'madara' ); ?>
            </a>
        </li>
        <li class="<?php echo esc_attr($orderby == 'alphabet' ? "active" : ''); ?>">
            <a href="<?php echo is_search() ? madara_search_filter_url( 'alphabet' ) : '?m_orderby=alphabet'; ?>">
				<?php esc_html_e( 'A-Z', 'madara' ); ?>
            </a>
        </li>
        <li class="<?php echo esc_attr($orderby == 'rating' ? 'active' : ''); ?>">
            <a href="<?php echo is_search() ? madara_search_filter_url( 'rating' ) : '?m_orderby=rating'; ?>">
				<?php esc_html_e( 'Rating', 'madara' ); ?>
            </a>
        </li>
        <li class="<?php echo esc_attr($orderby == 'trending' ? "active" : ''); ?>">
            <a href="<?php echo is_search() ? madara_search_filter_url( 'trending' ) : '?m_orderby=trending'; ?>">
				<?php esc_html_e( 'Trending', 'madara' ); ?>
            </a>
        </li>
        <li class="<?php echo esc_attr($orderby == 'views' ? 'active' : ''); ?>">
            <a href="<?php echo is_search() ? madara_search_filter_url( 'views' ) : '?m_orderby=views'; ?>">
				<?php esc_html_e( 'Most Views', 'madara' ); ?>
            </a>
        </li>
        <li class="<?php echo esc_attr($orderby == 'new-manga' ? 'class="active"' : ''); ?>">
            <a href="<?php echo is_search() ? madara_search_filter_url( 'new-manga' ) : '?m_orderby=new-manga'; ?>">
				<?php esc_html_e( 'New', 'madara' ); ?>
            </a>
        </li>
    </ul>
</div>
