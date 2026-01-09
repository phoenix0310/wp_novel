<?php


function wp_manga_number_format_short( $n, $precision = 1 ) {
	if ($n < 900) {
		// 0 - 900
		$n_format = number_format($n, $precision);
		$suffix = '';
	} else if ($n < 900000) {
		// 0.9k-850k
		$n_format = number_format($n / 1000, $precision);
		$suffix = 'K';
	} else if ($n < 900000000) {
		// 0.9m-850m
		$n_format = number_format($n / 1000000, $precision);
		$suffix = 'M';
	} else if ($n < 900000000000) {
		// 0.9b-850b
		$n_format = number_format($n / 1000000000, $precision);
		$suffix = 'B';
	} else {
		// 0.9t+
		$n_format = number_format($n / 1000000000000, $precision);
		$suffix = 'T';
	}
  // Remove unecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
  // Intentionally does not affect partials, eg "1.50" -> "1.50"
	if ( $precision > 0 ) {
		$dotzero = '.' . str_repeat( '0', $precision );
		$n_format = str_replace( $dotzero, '', $n_format );
	}
	return $n_format . $suffix;
}

function wp_manga_get_number_from_formated_short($number_str){
    $number = $number_str;
    
    if(strpos($number_str, "K") !== false){
        $number = floatval(str_replace("K","", $number_str)) * 1000;
    }
    if(strpos($number_str, "M") !== false){
        $number = floatval(str_replace("M","", $number_str)) * 1000000;
    }
    if(strpos($number_str, "B") !== false){
        $number = floatval(str_replace("B","", $number_str)) * 1000000000;
    }
    if(strpos($number_str, "T") !== false){
        $number = floatval(str_replace("T","", $number_str)) * 1000000000000;
    }
    
    return $number;
}

/**
 * Delete directory and all of its content
 **/
function wp_manga_delete_dir($dirPath) {
    if (! is_dir($dirPath)) {
        throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            wp_manga_delete_dir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dirPath);
}

function wp_manga_str_split_unicode($str, $length = 1) {
    $tmp = preg_split('~~u', $str, -1, PREG_SPLIT_NO_EMPTY);
    if ($length > 1) {
        $chunks = array_chunk($tmp, $length);
        foreach ($chunks as $i => $chunk) {
            $chunks[$i] = join('', (array) $chunk);
        }
        $tmp = $chunks;
    }
    return $tmp;
}


if(!function_exists('wp_manga_pagination_generate')){
    function wp_manga_pagination_generate($total_page, $current = 1, $base_url = '/', $page_var = 't'){
        if($total_page > 1){
            echo '<div class="pagination">';
            $visible = 3;
            $page = $current - floor($visible / 2);
            if($page < 1) $page = 1;
    
            if($page > 2 && $current > 1){
                echo '<span class="page page-' . ($current - 1) . '">';
                echo '<a href="' . add_query_arg($page_var, $current - 1, $base_url) . '" data-page="' . ($current - 1) . '"> &lt;&lt; </a>';
                echo '</span>';
            }
    
            if($page > 1){
                echo '<span class="page ' . (1 == $current ? 'current' : '') . ' page-1">';
                echo '<a href="' . add_query_arg($page_var, 1, $base_url) . '" data-page="1">1</a>';
                echo '</span>';
            }
    
            if($page > 2){
                echo '<span class="page space">';
                echo '...';
                echo '</span>';
            }
    
            for($count = 1; $count <= $visible; $count++){
                if($page <= $total_page){
                    echo '<span class="page ' . ($page == $current ? 'current' : '') . ' page-' . $page . '">';
                    if($page == $current){
                        echo $page;
                    } else {
                        echo '<a href="' . add_query_arg($page_var, $page, $base_url) . '" data-page="' . ($page) . '">';
                        echo $page;
                        echo '</a>';
                    }
                    
                    echo '</span>';
                }		
    
                $page++;
            }
    
            if($page < $total_page){
                echo '<span class="page space">';
                echo '...';
                echo '</span>';
            }
            
            if($page <= $total_page){
                echo '<span class="page ' . ($total_page == $current ? 'current' : '') . ' page-' . $total_page . '">';
                echo '<a href="' . add_query_arg($page_var, $total_page, $base_url) . '" data-page="' . ($total_page) . '">' . $total_page . '</a>';
                echo '</span>';
            }
    
            if($page < $total_page){
                echo '<span class="page page-' . ($current + 1) . '">';
                echo '<a href="' . add_query_arg($page_var, $current + 1, $base_url) . '" data-page="' . ($current + 1) . '"> &gt;&gt; </a>';
                echo '</span>';
            }
    
            echo '</div>';
        }
    }
    }

/**
 * Get current request URL
 * @return string
 */
function wp_manga_get_current_request(){
    if (isset($_SERVER['HTTPS']) &&
			($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
			isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
			$_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
			$protocol = 'https';
		}
		else {
			$protocol = 'http';
		}

	$actual_link = "$protocol://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    return $actual_link;
}

function wp_manga_generate_onesignal_externalid($user_id = false){
    $user_id = $user_id ? $user_id : get_current_user_id();
    if($user_id){
        return 'madara-' . $user_id;
    }

    return '';
}