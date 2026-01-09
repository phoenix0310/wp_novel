<?php

add_filter('wpseo_opengraph_title', 'wp_manag_wpseo_opengraph_title', 10, 2);
function wp_manag_wpseo_opengraph_title($title, $presenter){
    return $title;
}
