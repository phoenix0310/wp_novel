<?php 
/**
 * 2021 Oct 6th - GooglePhotos does not provide a Remove media item or albums API
 **/
 
 
add_action('wp_manga_storage_settings', 'wp_manga_storage_settings_google_photos');
function wp_manga_storage_settings_google_photos(){
	$options = get_option( 'wp_manga', array() );
	
	$googlephotos_client_id     = isset( $options['google_client_id'] ) ? $options['google_client_id'] : '';
    $googlephotos_client_secret = isset( $options['google_client_secret'] ) ? $options['google_client_secret'] : '';
    $googlephotos_redirect      = isset( $options['google_redirect'] ) ? $options['google_redirect'] : '';
    $googlephotos_refreshtoken  = get_option('wp_manga_google_refreshToken');
	$gphotos_storage_option = isset($options['gphotos_storage_option']) ? $options['gphotos_storage_option'] : 'per_manga';
	?>
	<h2>
		<?php esc_html_e( 'Google Photos', WP_MANGA_TEXTDOMAIN ) ?>
		<p class="description" style="color: #ff0000">Note that Google Photos storage is quite limited. It is not suitable to store many and frequently accessed files.</p>
		<span class="wp-manga-tooltip dashicons dashicons-editor-help"><span class="wp-manga-tooltip-text"><?php esc_html_e( ' - You can start using this upload feature when you see the Authorizing success display.', WP_MANGA_TEXTDOMAIN ) ?>
				<br><?php esc_html_e( ' - Allow to upload only Image file type at this time.', WP_MANGA_TEXTDOMAIN ) ?></span></span>
	</h2>
            <p class="googlephotos-setup">
                <strong><?php esc_html_e( '* For Google Photos Api register :', WP_MANGA_TEXTDOMAIN ); ?></strong>
                <br>
                <?php esc_html_e( ' - You need to create a Oauth Client ID Credential and when setting, remember to put the redirect URL to your website. ', WP_MANGA_TEXTDOMAIN ); ?>
            </p>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <?php esc_html_e( 'Client ID', WP_MANGA_TEXTDOMAIN ) ?>
                    </th>
                    <td>
                        <p>
                            <input name="wp_manga[google_client_id]" type="text" class="large-text" value="<?php echo esc_attr( $googlephotos_client_id ); ?>">
                        <p class="description">
                            <?php esc_html_e( 'You can register the client ID at', WP_MANGA_TEXTDOMAIN ); ?>
                            <a href="https://console.developers.google.com/" target="_blank">
                                <?php esc_html_e( 'developers.google.com', WP_MANGA_TEXTDOMAIN ); ?>
                            </a>
							<?php echo sprintf(esc_html__(' and enable Photos Library API at %s and Create Credentials: OAuth client ID at %s', WP_MANGA_TEXTDOMAIN), "https://console.cloud.google.com/apis/library/photoslibrary.googleapis.com", "https://console.cloud.google.com/apis/credentials/oauthclient");?>
                        <p></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php esc_html_e( 'Client Secret', WP_MANGA_TEXTDOMAIN ) ?>
                    </th>
                    <td>
                        <p>
                            <input name="wp_manga[google_client_secret]" type="text" class="large-text" value="<?php echo esc_attr( $googlephotos_client_secret ); ?>">
                        <p class="description">
                            <?php esc_html_e( 'You will need Client Secret to Authorize and Upload to googlephotos function', WP_MANGA_TEXTDOMAIN ) ?>
                        <p></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php esc_html_e( 'Redirect URL', WP_MANGA_TEXTDOMAIN ) ?>
                    </th>
                    <td>
                        <p>
                            <input name="wp_manga[google_redirect]" type="text" class="large-text" value="<?php echo esc_url( $googlephotos_redirect ); ?>">
                        <p class="description">
                            <?php esc_html_e( 'Redirect URL need to match Credential\'s redirect URL when creating API Credential ', WP_MANGA_TEXTDOMAIN ) ?>
                        <p></p>
                    </td>
                </tr>
                <?php if( !empty( $googlephotos_refreshtoken )  ) { ?>
                    <tr>
                        <th scope="row">
                            <?php esc_html_e( 'Refresh Token', WP_MANGA_TEXTDOMAIN ) ?>
                        </th>
                        <td>
                            <p>
                                <input name="google_refreshtoken" type="text" class="large-text" value="<?php echo esc_attr( $googlephotos_refreshtoken ); ?>">
                            <p class="description">
                                <strong><?php esc_html_e( 'Google Refresh Token only being provided at the first time Authorizing the App, so you should remember or save the token to use the client ID again.', WP_MANGA_TEXTDOMAIN ) ?></strong>
                            <p>
                            <p class="description">
                                <strong><?php esc_html_e( 'Also if you have saved the Refresh Token along with Client ID and Secret, you can just fill all the details and don\'t need to authorize again. ', WP_MANGA_TEXTDOMAIN ) ?></strong>
                            <p>
                            <p class="description">
                                <?php esc_html_e( 'This will be auto generate when Authorize Process success.', WP_MANGA_TEXTDOMAIN ) ?>
                            <p></p>
                        </td>
                    </tr>
                <?php } ?>
                <?php
				
					$scopes = urlencode('https://www.googleapis.com/auth/photoslibrary.appendonly https://www.googleapis.com/auth/photoslibrary.readonly.appcreateddata');
					$auth_url = "https://accounts.google.com/o/oauth2/v2/auth?" .
						"scope=$scopes" .
						"&access_type=offline" .
						"&include_granted_scopes=true" .
						"&prompt=consent" .
						"&response_type=code" .
						"&redirect_uri=" . urlencode($googlephotos_redirect) .
						"&state=picasa&client_id=" . $googlephotos_client_id;
                ?>
                <tr>
                    <th scope="row">
                        <a id="googlephotos-authorize" href="<?php echo esc_url($auth_url);?>">
                            <?php esc_html_e( 'Authorize', WP_MANGA_TEXTDOMAIN ) ?>
                        </a>
                    </th>
                    <td>
                        <p>
                            <?php
                            if( !empty( $googlephotos_refreshtoken )  ){
                                if ( get_transient('google_authorized') ) { ?>
                                    <span class="dashicons dashicons-yes"></span>
                                    <?php esc_html_e( 'Authorizing Success', WP_MANGA_TEXTDOMAIN );
                                } else {
                                    $error_msg = get_transient( 'googlephotos_authorization_error' );
                                    ?>
                                    <span class="dashicons dashicons-dismiss"></span>
                                    <?php esc_html_e( 'Authorizing Failed. ', WP_MANGA_TEXTDOMAIN );

                                    if( $error_msg ){
                                        echo esc_html( $error_msg );
                                    }
                                }
                            }
                            ?>
                        </p>
						<p style="color:#ff0000"><?php esc_attr_e('You still need to authorize Google Photos again if you have authorized with Google Picasa/Blogspot as they are using different scope permissions. Google Picasa/Blogspot will be shutdown on March 15. 2019, so please consider moving data to Google Photos as soon as possible', WP_MANGA_TEXTDOMAIN);?></p>
                    </td>
                </tr>
				<tr>
					<th><?php esc_html_e( 'Create Album', WP_MANGA_TEXTDOMAIN ) ?></th>
					<td>
						<label for="gphotos_storage_option_per_manga"><input type="radio" id="gphotos_storage_option_per_manga" name="wp_manga[gphotos_storage_option][]" value="per_manga" <?php echo $gphotos_storage_option == 'per_manga' ? 'checked="checked"' : '';?>/> <?php esc_html_e( 'for All chapters in Manga', WP_MANGA_TEXTDOMAIN ) ?></label>
						<label for="gphotos_storage_option_per_chapter"><input type="radio" id="gphotos_storage_option_per_chapter" name="wp_manga[gphotos_storage_option][]" value="per_chapter" <?php echo $gphotos_storage_option == 'per_chapter' ? 'checked="checked"' : '';?>/> <?php esc_html_e( 'for each chapter in Manga', WP_MANGA_TEXTDOMAIN ) ?></label>
					</td>
				</tr>
            </table>
			<?php
}

//do this check on update refreshToken for the first time save, on every after the get_access_token will auto update status of fresh token
add_action('update_option', 'wp_manga_storage_gphotos_check_refresh_token', 10, 3 );
function wp_manga_storage_gphotos_check_refresh_token( $option, $oldvalue, $value ){
	if( $option == 'wp_manga_google_refreshToken' && !empty( $value ) ){
		//authorizing status will be set in get_access_token function
		$storage = wp_manga_storage_gphotos::get_instance();
		
		$storage->get_access_token( $value );
	}
}

add_filter('wp_manga_available_storages', 'wp_manga_storage_gphotos_add');
function wp_manga_storage_gphotos_add( $hosts ){
	if(get_option('wp_manga_google_refreshToken') != ''){
		$hosts['gphotos']['value'] = 'gphotos';
		$hosts['gphotos']['text']  = esc_html__( 'Google Photos', WP_MANGA_TEXTDOMAIN );
	}
	
	return $hosts;
}

add_action('manga_chapter_upload_url_form_fields', 'wp_manga_storage_gphotos_upload_form');
function wp_manga_storage_gphotos_upload_form( $manga_post ){
	extract( $manga_post );
	?>
	<!-- Google Photos -->
	<div class="gphotos-import" style="<?php echo $default_storage == 'gphotos' ? '' : 'display:none;' ?>">
		<div class="wp-manga-form-group">
			<h2>
				<label>
					<?php esc_attr_e( 'Search Album by Name', WP_MANGA_TEXTDOMAIN ); ?>
				</label>
			</h2>
			<input type="text" name="gphotos-album-name" placeholder="<?php esc_html_e( "Album Name", WP_MANGA_TEXTDOMAIN ); ?>">
			<button type="button" id="gphotos-search-album" class="button button-primary">
				<span class="fas fa-search"></span>
			</button>
			<button type="button" id="gphotos-create-album" class="button">
				<span><i style="display: none;" class="fa fa-spinner fa-spin"></i><?php echo esc_html("Create Album", WP_MANGA_TEXTDOMAIN);?></span>
			</button>

			<p class="description">
				<?php esc_html_e( 'Album needs to be exactly with the album you want to import in Google Photos (case sensitive). In addition, albums must be created from your site in advance', WP_MANGA_TEXTDOMAIN ); ?>
			</p>
		</div>

		<div class="wp-manga-form-group" style="display: none">
			<h2>
				<label>
					<?php esc_html_e( 'Select Album to Import', WP_MANGA_TEXTDOMAIN ); ?>
				</label>
			</h2>
			<select id="gphotos-albums">
			</select>
		</div>

	</div>
	<?php
}

add_action( 'wp_ajax_gphotos_search_album', 'wp_manga_gphotos_search_album'  );
function wp_manga_gphotos_search_album( $album = ''){
	$nonce = isset($_GET['nonce']) ? $_GET['nonce'] : '';
    if(wp_verify_nonce( $nonce, "wp-manga-admin" )){
        if($album == '') {
            $album = isset($_GET['album']) ? $_GET['album'] : '';
        }
        
        if ( empty( $album ) ) {
            wp_send_json_error( [
                'message' => esc_html__( 'Album Name cannot be empty', WP_MANGA_TEXTDOMAIN )
            ] );
        }

        $storage = wp_manga_storage_gphotos::get_instance();

        $output = $storage->find_album($album, true);

		if ( ! empty( $output ) ) {
			wp_send_json_success( [
				'data' => $output
			] );
		}

        wp_send_json_error( [
            'message' => esc_html__( 'Cannot find this album', WP_MANGA_TEXTDOMAIN )
        ] );
    } else {
		wp_send_json_error( [
            'message' => esc_html__( 'Unauthorized', WP_MANGA_TEXTDOMAIN )
        ] );
	}
}

add_action( 'wp_ajax_gphotos_create_album', 'wp_manga_gphotos_create_album'  );
function wp_manga_gphotos_create_album( $album = ''){
	$nonce = isset($_GET['nonce']) ? $_GET['nonce'] : '';
    if(wp_verify_nonce( $nonce, "wp-manga-admin" )){
		if($album == '') {
            $album = isset($_GET['album']) ? $_GET['album'] : '';
        }
        
        if ( empty( $album ) ) {
            wp_send_json_error( [
                'message' => esc_html__( 'Album Name cannot be empty', WP_MANGA_TEXTDOMAIN )
            ] );
        }

		$storage = wp_manga_storage_gphotos::get_instance();
		$result = $storage->create_album($album);

		if($result && isset($result->error)){
			wp_send_json_error( [
				'message' => $result->error->message
			] );
		}
		
		if($result){
			wp_send_json_success(esc_html__("Google Photos Album created", WP_MANGA_TEXTDOMAIN));
		} else {
			wp_send_json_error( [
				'message' => esc_html__('Cannot create album', WP_MANGA_TEXTDOMAIN)
			] );
		}
	}

	wp_send_json_error( [
		'message' => esc_html__( 'Unauthorized', WP_MANGA_TEXTDOMAIN )
	] );
}

add_action('wp_manga_storage_albumdropdown', 'wp_manga_gphotos_show_albums_dropdown', 10, 2);
function wp_manga_gphotos_show_albums_dropdown( $default_storage, $context ){
	if ( get_transient('google_authorized') ) {
		$options = get_option( 'wp_manga', array() );
		$google_client_id = isset( $options['google_client_id'] ) ? $options['google_client_id'] : '';
		if($google_client_id){
			$storage = wp_manga_storage_gphotos::get_instance();
			$storage->albums_dropdown( $default_storage , true);
		}
	}
}

add_action('wp_manga_before_storage_upload','wp_manga_gphotos_before_storage_upload', 10, 3);
function wp_manga_gphotos_before_storage_upload( $post_id, $manga_zip, $storage ){
	//if storage is gphotos
	if( $storage == 'gphotos' && isset( $_POST['gphotos_album'] ) ){
		update_option( 'gphotos_latest_album', $_POST['gphotos_album'] );
	}
}

add_action( 'wp_manga_upload_after_extract', 'wp_manga_gphotos_upload_after_extract', 10, 4);
function wp_manga_gphotos_upload_after_extract( $post_id, $slugified_name, $extract, $storage ){
	//if storage is gphotos
	if( $storage == 'gphotos' && isset( $_POST['gphotos_album'] ) ){
		update_option( 'gphotos_latest_album', $_POST['gphotos_album'] );
	}
}

add_filter('wp_manga_upload_gphotos_params', 'wp_manga_upload_gphotos_parse_params');
function wp_manga_upload_gphotos_parse_params($upload){
	if(isset($_POST['gphotos_album'])) $upload['album_id'] = $_POST['gphotos_album'];
	
	return $upload;
}

class wp_manga_storage_gphotos {

	private $googleClientID;

	private $googleClientSecret;

	private $googleRedirect;

	private $googleRefreshToken;
	
	private static $_instance;

	private function __construct() {
		$options = get_option( 'wp_manga', array() );
		$google_client_id = isset( $options['google_client_id'] ) ? $options['google_client_id'] : '';
		$google_client_secret = isset( $options['google_client_secret'] ) ? $options['google_client_secret'] : '';
		$google_redirect = isset( $options['google_redirect'] ) ? $options['google_redirect'] : '';
		$google_refreshtoken = get_option('wp_manga_google_refreshToken', null);

		$this->googleClientID = $google_client_id;
		$this->googleClientSecret = $google_client_secret;
		$this->googleRedirect = $google_redirect;
		$this->googleRefreshToken = $google_refreshtoken;
	}
	
	public static function get_instance(){
		if ( null == self::$_instance ) {
			self::$_instance = new wp_manga_storage_gphotos();
		}

		return self::$_instance;
	}

	function wp_manga_google_save_credential() {
		$googleClientID = isset( $_POST['googleClientID'] ) ? trim( $_POST['googleClientID'] ) : '';
		$googleClientSecret = isset( $_POST['googleClientSecret'] ) ? trim( $_POST['googleClientSecret'] ) : '';
		$googleRedirect = isset( $_POST['googleRedirect'] ) ? trim( $_POST['googleRedirect'] ) : '';
		$options = get_option( 'wp_manga', array() );

		if ( $googleClientID ) {
			$options['google_client_id'] = $googleClientID;
		}
		if ( $googleClientSecret ) {
			$options['google_client_secret'] = $googleClientSecret;
		}
		if ( $googleRedirect ) {
			$options['google_redirect'] = $googleRedirect;
		}
		update_option( 'wp_manga', $options );
		wp_send_json_success();
		die(0);
	}

	// get access token to picasa web api
	public function get_access_token( $refreshToken = '' ) {
		if( empty( $this->googleClientID ) || empty( $this->googleClientSecret ) || ( empty( $refreshToken ) && empty( $this->googleRefreshToken ) ) ){
			return;
		}

		$access_token = get_option('wp_manga_google_accessToken', '');

		if($access_token){
			$expired = intval(get_option('wp_manga_google_accessTokenExpired', ''));

			if($expired > time()){
				return $access_token;
			}
		}

		$headers = array();
	    // $headers[] = 'Content-Type: application/x-www-form-urlencoded';
		$url = 'https://www.googleapis.com/oauth2/v4/token';
		$params = array(
	        'client_id' => $this->googleClientID,
			'client_secret'   => $this->googleClientSecret,
			'refresh_token'   => !empty( $refreshToken ) ? $refreshToken : $this->googleRefreshToken,
			'grant_type'      => 'refresh_token'
        );

        $token = $this->post_url( $headers, $url, $params );
		
		if( !isset( $token->access_token ) ){
			//if refresh token is already expired or revoke, then set the authorizing state of picasa is false
			set_transient('google_authorized', false);

			if( isset( $token->error_description ) ){
				//put error message to transient
				set_transient('google_authorization_error', $token->error_description, 1 * 60 * 60);
			}

			return false;
		}

		set_transient('google_authorized', true);
		delete_transient( 'google_authorization_error' );

		update_option('wp_manga_google_accessToken', $token->access_token);
		update_option('wp_manga_google_accessTokenExpired', time() + $token->expires_in);
		if(isset($token->refresh_token)){
			update_option('wp_manga_google_refreshToken', $token->refresh_token);
			$this->googleRefreshToken = $token->refresh_token;
		}

        return $token->access_token;

	}

	public function upload( $upload ) {
		$result = array();
		if(!isset($upload['uniqid']) || $upload['uniqid'] == ''){
			$result['error'] = esc_html__('Please draft save the manga before uploading', WP_MANGA_TEXTDOMAIN);
			return $result;
		}

		global $wp_manga_functions;
		
		$google_refreshtoken = $this->googleRefreshToken;
		if ( $google_refreshtoken ) {
			$album_id = '';
			
			$options = get_option( 'wp_manga', array() );
			$gphotos_storage_option = isset($options['gphotos_storage_option']) ? $options['gphotos_storage_option'] : 'per_manga';
			$create_new_album_for_chapter = $gphotos_storage_option == 'per_chapter' ? true : false;
			
			if(isset($upload['album_id'])) $album_id = $upload['album_id'];
			
			if(!$album_id || $album_id == 'undefined'){
				// create new album 
				$mangas = $wp_manga_functions->get_manga_by('manga_unique_id', $upload['uniqid']);
				
				if($mangas && count($mangas) > 0){
					if($create_new_album_for_chapter){
						$album_name = $mangas[0]->post_title . '-' . $upload['chapter'];
					} else {
						$album_name = $mangas[0]->post_title;
					}
				} else {
					if($create_new_album_for_chapter){
						$album_name = $upload['uniqid'] . '-' . $upload['chapter'];
					} else {
						$album_name = $upload['uniqid'];
					}
				}
			
				$album_id = $this->find_album($album_name);
				if($album_id){
					$album = (object)array('id' => $album_id);
				} else {
					$album = $this->create_album($album_name);
				}
				
				if($album && isset($album->error)){
					$result['error'] = $album->error->message;
					return $result;
				}
				
				if($album){
					$album_id = $album->id;
					update_option( 'gphotos_latest_album', $album_id );
				} else {
					$result['error'] = esc_html__('Cannot create album', WP_MANGA_TEXTDOMAIN);
					return $result; 
				}
			}
			
			foreach ( $upload['file'] as $file ) {
				$dir = $upload['dir'] . $file;
				if(!file_exists($dir)){
					$result['error'] = esc_html__('Images do not exist', WP_MANGA_TEXTDOMAIN);
					return $result;
				}
				
				$result[] = $this->image_upload( $dir, $file , '', $album_id );
			}
			return $result;
		} else {
			$result['error'] = esc_html__('Please configure Google Refresh Token', WP_MANGA_TEXTDOMAIN);
			return $result;
		}
	}
	
	public function get_item_URL($item_id){
		$url = 'https://photoslibrary.googleapis.com/v1/mediaItems/' . $item_id;
		$item = $this->construct_curl($url);
		
		if(isset($item->error)){
			error_log("Google Photos: " . var_export($item->error,true));
			return '#';
		}
		
		$original_url = $item->baseUrl . '=w' . $item->mediaMetadata->width . '-h' . $item->mediaMetadata->height;
		
		// save item id after # so we can fetch item image again
		return $original_url . '#' . $item_id . '-' . time();
	}
	
	public function image_upload( $image_dir, $name , $mime = '', $album_id = '' ) {

		$images = array();
		
		if($album_id == ''){

			$album_id = get_option( 'gphotos_latest_album', '' );

			if($album_id != ''){
				$current_album_list = $this->get_album_list();

				if( !isset( $current_album_list[ $album_id ] ) ){
					$album_id = 'default';
				}
			}
		}
				
		$url = 'https://photoslibrary.googleapis.com/v1/uploads';

		$content = file_get_contents($image_dir);
		
		$uploadToken = $this->construct_curl(
			$url,
			array('Content-type:application/octet-stream',
                    'X-Goog-Upload-File-Name:' . $name,
                    'X-Goog-Upload-Protocol:raw'),
			array(
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => $content
			),
			true
		);

        if($uploadToken){
		
			$images[] = array("description" => $name,
								"simpleMediaItem" => array("uploadToken" => $uploadToken));

			$url = 'https://photoslibrary.googleapis.com/v1/mediaItems:batchCreate';
			
			$data = array(
						"newMediaItems" => $images,
						"albumId" => $album_id
					);
					
			$result = $this->construct_curl(
				$url,
				array('Content-Type:application/json'),
				array(
					CURLOPT_POSTFIELDS => json_encode($data)
					)
			);

			if(isset($result) && !isset($result->error)){
				$item = $result->newMediaItemResults[0]->mediaItem;
				if(isset($item->baseUrl)){
					$original_url = $item->baseUrl . '=w' . $item->mediaMetadata->width . '-h' . $item->mediaMetadata->height;
					
					return $original_url . '#' . $item->id . '-' . time();
				} else {
					return $this->get_item_URL($item->id);
				}
				
			} else {
				error_log($result->error->message);
			}
		}

		return;
	}

	public function get_album_list( &$pageToken = ''){
		
		$url = 'https://photoslibrary.googleapis.com/v1/albums?pageSize=50';

		if($pageToken != ''){
			$url .= '&pageToken=' . $pageToken;
		}
		
		$result = $this->construct_curl( $url );

		if($result && isset($result->error)){
			return $result;
		}

		if( ! $result ){
			return false;
		}

		$albums = array();
		
		if( isset( $result->albums ) ){
			foreach($result->albums as $album){
				$title = $album->title;
				$id = $album->id;

				$albums[$id] = array(
					'title' 	=> $title,
					'numphotos' => isset($album->mediaItemsCount) ? intval($album->mediaItemsCount) : 0
				);
			}
			
			if(isset($result->nextPageToken)){
				$pageToken = $result->nextPageToken;
			} else {
				$pageToken = '';
			}
		}

		return $albums;

	}

	/**
	 * get latest album id
	 **/
	public function get_album( ){

		$album = get_option( 'gphotos_latest_album', 'default' );
		$albums = $this->get_album_list();

		if( isset( $albums[$album] ) ){
			return $albums[$album];
		}

		return false;

	}
	
	/**
	 * Find album by name
	 **/
	public function find_album($name, $return_object = false){
		$pageToken = '';
		$end = false;
		while(!$end){
			
			$albums = $this->get_album_list( $pageToken );
			if($albums && count($albums) > 0){
				
				foreach($albums as $key => $album){
					
					if($album['title'] == stripcslashes($name)){
						
						if(!$return_object){
							return $key;
						} else{
							return array_merge($album, array('id' => (string)$key));
						}
					}
				}
				
				if($pageToken == ''){
					$end = true;
				}
			} else {
				$end = true;
			}
		}
		
		return false;
	}

	public function get_album_numphotos( $album_id = 'default' ){ //check if album exceeds 2000 photos limit

		$albums = $this->get_album_list();

		if( isset( $albums[$album_id] ) ){
			return $albums[$album_id]['numphotos'];
		}

		return false;
	}

	public function albums_dropdown( $default_storage, $echo = false ){
		$albums = $this->get_album_list();
		$latest_album = get_option( 'gphotos_latest_album', 'default' );

		if( $albums == false || isset($albums->error) ){

			//get error message
			$error = get_transient('google_authorization_error');
			if( $error ){
				return '<div class="error">' . esc_html( $error ) . '</div>';
			} else {
				return '<div class="error">' . esc_html( $albums->error->message ) . '</div>';
			}

			return;
		}

		$html = '';

		$html .= '<select id="wp-manga-gphotos-albums" class="wp-manga-gphotos-albums ' . ($default_storage == 'gphotos' ? '' : 'hidden') . '" name="wp-manga-gphotos-albums">';
			$html .= '<option value="">' . esc_html__('New album', WP_MANGA_TEXTDOMAIN) . '</option>';
			
			foreach( $albums as $id => $album ){
				$html .= '<option value="'.esc_attr( $id ).'"' . selected( $id, $latest_album, false ) . '>' . sprintf( esc_html__('[Album] %s (having %d items)', WP_MANGA_TEXTDOMAIN ), $album['title'], $album['numphotos'] ) . '</option>';
			}
		$html .= '</select>';

		if( $echo ){
			echo wp_kses( $html, array(
				'select'    => array(
					'id'       => array(),
					'class'    => array(),
					'name'     => array()
				),
				'option'    => array(
					'value'    => array(),
					'selected' => array(),
				),
			) );
			return;
		}

		return $html;

	}
	
	
	/**
	 * with Google Photos, we can only update images to the albums created by App
	 **/
	public function create_album($title){
		$url = 'https://photoslibrary.googleapis.com/v1/albums';
		$headers = array('Content-type:application/json');
		$posts = array('album' => array('title' => $title));
		$result = $this->construct_curl( $url, $headers, array(CURLOPT_POSTFIELDS => json_encode($posts)) );
		
		return $result;
	}
	
	public function get_album_images( $album_id ){

		$album_id = (string) $album_id;

		$url = "https://photoslibrary.googleapis.com/v1/mediaItems:search";

		$output = array();
		$complete = false;
		$nextPageToken = '';
		while(!$complete){
			$data = array('albumId' => $album_id, 'pageSize' => '100');
			if($nextPageToken != ''){
				$data['pageToken'] = $nextPageToken;
			}
			
			$headers = array('Content-type:application/json');
			$result = $this->construct_curl( $url, $headers, array(CURLOPT_POSTFIELDS => json_encode($data) ));
			
			
			if($result && isset($result->mediaItems) && count($result->mediaItems) > 0){
				foreach($result->mediaItems as $item){
					$original_url = $item->baseUrl . '=w' . $item->mediaMetadata->width . '-h' . $item->mediaMetadata->height;
					$output[$item->filename] = $original_url  . '#' . $item->id . '-' . time();
				}
				
				if(!isset($result->nextPageToken) || $result->nextPageToken == ''){
					$complete = true;
				} else {
					$nextPageToken = $result->nextPageToken;
				}
			} else {
				$complete = true;
			}
		}

		ksort( $output, SORT_NATURAL );

		return array_values( $output );
	}

	function check_token_scope(){
		$accessToken = $this->get_access_token();
		
		if( empty( $accessToken ) ) {
			return false;
		}

		$curl_headers = array();
	    $curl_headers[] = 'Authorization: Bearer ' . $accessToken;
		$curl_headers[] = 'Content-Type: application/json';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://www.googleapis.com/oauth2/v3/tokeninfo");

		curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

  		$ret = curl_exec($ch);

	    if($errno = curl_errno($ch)) {
		    $error_message = curl_strerror($errno);
		    echo "cURL error ({$errno}):\n {$error_message}"; die();
		}

	    curl_close($ch);

		$data = json_decode($ret);
		
		return $data;
	}

	function construct_curl( $url, $headers = false, $opts = false, $raw = false ){

		$accessToken = $this->get_access_token();
		
		if( empty( $accessToken ) ) {
			return false;
		}

		$curl_headers = array();
	    $curl_headers[] = 'Authorization: Bearer ' . $accessToken;

		if( $headers ){
			$curl_headers = array_merge( $curl_headers, $headers );
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);

		curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

		if( $opts && is_array($opts) ){
			foreach( $opts as $key => $value ){
				curl_setopt( $ch, $key, $value );
			}
		}

		$ret = curl_exec($ch);

	    if($errno = curl_errno($ch)) {
		    $error_message = curl_strerror($errno);
		    echo "cURL error ({$errno}):\n {$error_message}"; die();
		}

	    curl_close($ch);

		if( $raw ){
			return $ret;
		}

		$data = json_decode($ret);
		
		return $data;

	}
	

	function get_base64( $path ) {
		$data = file_get_contents( $path );
		$base64 = base64_encode( $data );
		return $base64;
	}

	function get_size( $dir ) {
		$size = filesize( $dir );
		return $size; 
	}

	function post_url( $headers, $url, $params ) {
	    $ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_HTTPGET, 0);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params, "", '&'));
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	    $ret = curl_exec($ch);

		// debug
		if($errno = curl_errno($ch)) {
		    $error_message = curl_strerror($errno);
		    echo "cURL error ({$errno}):\n {$error_message}";
		}
	    curl_close($ch);

	    return json_decode( $ret );
	}
}