<?php

if ( ! function_exists('startsWith')) {
    function startsWith($haystack, $needle)
    {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, - strlen($haystack)) !== false;
    }
}

if ( ! function_exists('endsWith')) {
    function endsWith($haystack, $needle)
    {
        // search forward starting from end minus needle length characters
        return $needle === "" || ( ( $temp = strlen($haystack) - strlen($needle) ) >= 0 && strpos($haystack, $needle, $temp) !== false );
    }
}

/**
 * Sanitize CSS to prevent XSS attacks
 * SECURITY PATCH: Remove dangerous tags and scripts from CSS
 */
function madara_sanitize_custom_css( $css ) {
    if ( empty( $css ) ) {
        return '';
    }
    
    // Remove script tags
    $css = preg_replace( '/<script\b[^>]*>(.*?)<\/script>/is', '', $css );
    
    // Remove iframe tags
    $css = preg_replace( '/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $css );
    
    // Remove object and embed tags
    $css = preg_replace( '/<object\b[^>]*>(.*?)<\/object>/is', '', $css );
    $css = preg_replace( '/<embed\b[^>]*>(.*?)<\/embed>/is', '', $css );
    
    // Remove dangerous event handlers (onclick, onerror, onload, etc.)
    $css = preg_replace( '/\s*on\w+\s*=\s*["\']?[^"\']*["\']?/i', '', $css );
    
    // Remove javascript: protocol
    $css = preg_replace( '/javascript:/i', '', $css );
    
    // Remove vbscript: protocol
    $css = preg_replace( '/vbscript:/i', '', $css );
    
    // Remove data: protocol with javascript
    $css = preg_replace( '/data:text\/html[^,]*,/i', '', $css );
    
    // Remove any HTML tag that closes the style prematurely
    $css = str_replace( '</style>', '', $css );
    
    // Additional filter: allow only valid CSS
    // This prevents closing the <style> tag and injecting code
    $css = wp_strip_all_tags( $css );
    
    return $css;
}