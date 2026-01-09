<?php
/**
 *  Version: 1.0.0
 *  Text Domain: mangabooth-manga
 *  @since 1.0.0
 */
// NOTE UP LOAD IMGUR CREATE ALBUM : NAME-CHAPNAME.....
class WP_MANGA_IMGUR_UPLOAD {

	public function __construct() {
		// add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_script' ) );
		add_action('wp_ajax_wp-manga-imgur-save-credential', array( $this, 'wp_manga_imgur_save_credential' ) );
		add_action('wp_ajax_non_priv_wp-manga-imgur-save-credential', array( $this, 'wp_manga_imgur_save_credential') );

		add_action( 'init', array( $this, '_get_token' ) );

	}

	function _get_token(){

		if( ! isset( $_GET['state'] ) || $_GET['state'] == 'picasa' ){
			return;
		}

		if( isset( $_GET['access_token'] ) ){

			$url_paths = parse_url( $_SERVER['REQUEST_URI'] );

			if( empty( $url_paths['query'] ) ){
				return;
			}

			parse_str( $url_paths['query'], $query );

			if( empty( $query['access_token'] ) || empty( $query['refresh_token'] ) ){
				return;
			}

			update_option( 'wp_manga_imgur_refreshToken', $query['refresh_token'] );
			set_transient( 'wp_manga_imgur_token', $query['access_token'], $query['expires_in'] );

			exit( wp_safe_redirect( admin_url( 'edit.php?post_type=wp-manga&page=wp-manga-storage' ) ) );

		}else{

			?>
				<script type="text/javascript">
					var queryString = location.hash.substring(1);

					if( queryString !== '' ){
						window.location = "<?php home_url('/') ?>" + '?state=imgur&' +  queryString;
					}
				</script>
			<?php
		}

	}

	function wp_manga_imgur_save_credential() {

		$client_id     = isset( $_POST['imgurClientID'] ) ? $_POST['imgurClientID'] : '';
		$client_secret = isset( $_POST['imgurClientSecret'] ) ? $_POST['imgurClientSecret'] : '';
		$options       = get_option( 'wp_manga', array() );

		if ( $client_id ) {
			$options['imgur_client_id'] = $client_id;
		}
		if ( $client_secret ) {
			$options['imgur_client_secret'] = $client_secret;
		}
		update_option( 'wp_manga', $options );
		wp_send_json_success();
		die(0);
	}

	function imgur_upload( $upload ) {

		global $wp_manga_storage;
		$result = array();
		$options = get_option( 'wp_manga', array() );

		$accessToken = $this->get_access_token();

		if ( $accessToken ) {
			$title = $upload['uniqid'].'_'.$upload['chapter'];
			$album = $this->create_album( $accessToken, $title );
			
			if ( $album ) {
				foreach ( $upload['file'] as $file ) {
					$path = $upload['dir'] . $file;
					
					if(!file_exists($path)){
						$result['error'] = __('Images do not exist', WP_MANGA_TEXTDOMAIN);
						return $result;
					}
					$mime = $wp_manga_storage->mime_content_type( $file );
					
					$image = $this->image_upload( $accessToken, $path, $file , isset($album->data->id) ? $album->data->id : null, $mime );

					if( isset( $image->data->link ) ) {
						$result[] = $image->data->link;
					}elseif( $image->success == false ){
						$result = $image;
						$result->data->error = (object)array('message' => $result->data->error);
						break;
					}
				}

				return $result;
			}

		} else {
			$result['error'] = __('Please configure IMGUR Access Token', WP_MANGA_TEXTDOMAIN);
			return $result;
		}
	}

	function create_album( $accessToken, $title ) {
		$headers = array();
	    $headers[] = 'Authorization: Bearer '.$accessToken;
		$url = 'https://api.imgur.com/3/album';
		$params = array(
	        'title' => $title,
        );
		$album = $this->c_url( $headers, $url, $params );
		return $album;
	}

	function image_upload( $accessToken, $image_url, $name , $album, $mime ) {
		$headers = array();
	    $headers[] = 'Authorization: Bearer ' . $accessToken;
		$url = 'https://api.imgur.com/3/image';
		
		//$base64 = $this->get_base64( $image_url );

		$params = array(
	        'image' => new CURLFILE($image_url),
	        'album' => $album,
	        'type' => $mime,
	        'name' => $name,
        );
		$image = $this->c_url( $headers, $url, $params );
		return $image;
	}

	function get_base64( $path ) {
		$data = file_get_contents( $path );
		$base64 = base64_encode( $data );
		return $base64;
	}

	function get_access_token() {

		$token = get_transient( 'wp_manga_imgur_token' );

		if( $token ){
			return $token;
		}

		$client_id     = isset( $options['imgur_client_id'] ) ? $options['imgur_client_id'] : '';
		$client_secret = isset( $options['imgur_client_secret'] ) ? $options['imgur_client_secret'] : '';
		$refreshtoken  = get_option('wp_manga_imgur_refreshToken', null);

		if( empty( $client_id ) || empty( $client_secret ) || empty( $refreshtoken ) ){
			return false;
		}

		$headers = array();
	    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
		$url = 'https://api.imgur.com/oauth2/token';
		$params = array(
	        'client_id' => $client_id,
			'client_secret'   => $client_secret,
			'refresh_token'   => $refreshtoken,
			'grant_type'      => 'refresh_token',
        );

        $token = $this->c_url( $headers, $url, $params );

        return $token->access_token;
	}

	function c_url( $headers, $url, $params, $method = 'POST' ) {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_HTTPGET, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_ENCODING, '');
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);		
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

		if( strtolower( $method ) == 'post' ){
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST ,'POST');
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		}

		$ret = curl_exec($ch);

		// debug
		if($errno = curl_errno($ch)) {
			$error_message = curl_strerror($errno);
			echo "cURL error ({$errno}):\n {$error_message}";
		}

		curl_close($ch);

		$res = json_decode( $ret );
		return $res;
	}

	function get_album_images( $album ){

		$album = str_replace( 'https://imgur.com/a/', '', $album );

		$accessToken = $this->get_access_token();

		if( ! $accessToken ){
			return new WP_Error( '404', 'Cannot get Imgur access token' );
		}

		$headers = array();
	    $headers[] = 'Authorization: Bearer '.$accessToken;
		$url = "https://api.imgur.com/3/album/{$album}/images";

		$images = $this->c_url( $headers, $url, null, 'get' );

		if( isset( $images->data ) ){

			if( isset( $images->data->error ) ){
				return new WP_Error( '404', $images->data->error );
			}else{

				$output = array();

				foreach( $images->data as $image ){
					$output[ $image->name ] = $image->link;
				}

				ksort( $output, SORT_NATURAL );

				return array_values( $output );
			}

		}

		return new WP_Error( '404', 'Cannot get Imgur Album' );

	}

}
$GLOBALS['wp_manga_imgur_upload'] = new WP_MANGA_IMGUR_UPLOAD();
