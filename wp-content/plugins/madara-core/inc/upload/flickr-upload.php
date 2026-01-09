<?php

/**
 *  Version: 1.0.0
 *  Text Domain: mangabooth-manga
 * @since 1.0.0
 */
class WP_MANGA_FLICKR_UPLOAD
{

    private $api_key;

    private $api_secret;

    private $oauth_token;

    private $oauth_token_secret;

    public function __construct()
    {

        add_action('wp_ajax_wp-manga-flickr-save-credential', array($this, 'wp_manga_flickr_save_credential'));
        add_action('wp_ajax_non_priv_wp-manga-flickr-save-credential', array($this, 'wp_manga_flickr_save_credential'));

        $options                  = get_option('wp_manga', array());

        $this->api_key            = !empty( $options['flickr_api_key'] ) ? $options['flickr_api_key'] : '';
        $this->api_secret         = !empty( $options['flickr_api_secret'] ) ? $options['flickr_api_secret'] : '';

        $this->oauth_token        = get_option('wp_manga_flickr_oauth_token', null);
        $this->oauth_token_secret = get_option('wp_manga_flickr_oauth_token_secret', null);

        require WP_MANGA_DIR . 'lib/flickr/vendor/autoload.php';

        if (isset($_GET['action']) && $_GET['action'] == 'authorize-flickr') {
            $this->authorize();
        }
        if (isset($_GET['oauth_token']) && isset($_GET['oauth_verifier'])) {
            $this->authorize();
            $url = admin_url('edit.php?post_type=wp-manga&page=wp-manga-storage');
            header('Location: ' . $url);
            exit;
        }

    }

    public function authorize()
    {
        if ($this->api_key != '' && $this->api_secret != '') {
            $cacher = new Doctrine\Common\Cache\FilesystemCache('/tmp');
            $uploader = RemoteImageUploader\Factory::create('Flickr', array(
                'cacher'             => $cacher,
                'api_key'            => $this->api_key,
                'api_secret'         => $this->api_secret,
                'oauth_token'        => null,
                'oauth_token_secret' => null,
            ));

            $callbackUrl = admin_url('edit.php?post_type=wp-manga&page=wp-manga-storage');

            $uploader->authorize($callbackUrl);
        }
    }

    function wp_manga_flickr_save_credential()
    {
        $api_key    = isset( $_POST['flickr_api_key'] ) ? $_POST['flickr_api_key'] : '';
        $api_secret = isset( $_POST['flickr_api_secret'] ) ? $_POST['flickr_api_secret'] : '';

        $options = get_option('wp_manga', array());

        if ( !empty( $api_key ) ) {
            $options['flickr_api_key'] = $api_key;
        }
        if ( !empty( $api_secret ) ) {
            $options['flickr_api_secret'] = $api_secret;
        }

        update_option('wp_manga', $options);

        $redirect_url = admin_url('edit.php?post_type=wp-manga&page=wp-manga-storage');

        wp_send_json_success( array( $redirect_url ) ) ;

        die(0);
    }

    function flickr_upload($upload)
    {
        $result             = array();

        $check_access_token = $this->check_access_token();

        if ( $check_access_token ) {
            foreach ($upload['file'] as $file) {
                $path = $upload['dir'] . $file;
				if(!file_exists($path)){
					$result['error'] = __('Images do not exist', WP_MANGA_TEXTDOMAIN);
					return $result;
				}
                $url_image = $this->image_upload( $path );
                $result[] = $url_image;
            }
        } else {
            $result['error'] = __('Flickr Access Token does not exist', WP_MANGA_TEXTDOMAIN);
			return $result;
        }

        return $result;
    }

    function image_upload( $file_path )
    {
        if( empty( $this->api_key ) || empty( $this->api_secret ) ){
            return new WP_Error( 'flickr', 'Missing Flickr API key & secret' );
        }

        $cacher = new Doctrine\Common\Cache\FilesystemCache('/tmp');
        $uploader = RemoteImageUploader\Factory::create('Flickr', array(
            'cacher'             => $cacher,
            'api_key'            => $this->api_key,
            'api_secret'         => $this->api_secret,

            // if you have oauth_token and secret, you can set
            // to the options to pass
            'oauth_token'        => $this->oauth_token,
            'oauth_token_secret' => $this->oauth_token_secret,
        ));

        $url = $uploader->upload( $file_path );
        return $url;
    }

    function check_access_token()
    {
        if( empty( $this->api_key ) || empty( $this->api_secret ) ){
            return new WP_Error( 'flickr', 'Missing Flickr API key & secret' );
        }

        $cacher = new Doctrine\Common\Cache\FilesystemCache('/tmp');
        $uploader = RemoteImageUploader\Factory::create('Flickr', array(
            'cacher' => $cacher,
            'api_key' => $this->api_key,
            'api_secret' => $this->api_secret,

            // if you have oauth_token and secret, you can set
            // to the options to pass
            'oauth_token' => $this->oauth_token,
            'oauth_token_secret' => $this->oauth_token_secret,
        ));

        $result = $uploader->checkAccessTokenValid('flickr.auth.oauth.checkToken');

        if ($result) {
            return true;
        } else {
            update_option('wp_manga_flickr_oauth_token', null);
            update_option('wp_manga_flickr_oauth_token_secret', null);
            return false;
        }
    }

    function get_album_images( $album_url ){

        if( empty( $this->api_key ) || empty( $this->api_secret ) ){
            return new WP_Error( 'flickr', 'Missing Flickr API key & secret' );
        }

        /**
        * URL Sample
        * @param https://www.flickr.com/photos/137479892@N04/albums/72157666544477487
        */

        $paths = explode( '/albums/', $album_url );

        if( !isset( $paths[1] ) || ! is_numeric( $paths[1] ) ){
            return new WP_Error( '404', 'Invalid URL for Flickr Album' );
        }

        $album_id = $paths[1];

        $url = "https://api.flickr.com/services/rest/?method=flickr.photosets.getPhotos&api_key={$this->api_key}&photoset_id={$album_id}";

        $result = file_get_contents( $url );

        if( empty( $result ) ){
            return new WP_Error( '404', 'Cannot connect to Flick API' );
        }

        $result = simplexml_load_string( $result );
        $photos_id = array();

        $i = 0;

		while( isset( $result->photoset->photo[$i] ) ){

            $photo = $result->photoset->photo[$i];
            $atts = $photo->attributes();

            $photos_id[ (string) $atts['title'] ] = $this->get_image_url( (string) $atts['id'] );

			$i++;
		}

        ksort( $photos_id, SORT_NATURAL );

		return array_values( $photos_id );
    }

    function get_image_url( $image_id ){

        if( empty( $this->api_key ) || empty( $this->api_secret ) ){
            return new WP_Error( 'flickr', 'Missing Flickr API key & secret' );
        }

        $url = "https://api.flickr.com/services/rest/?method=flickr.photos.getSizes&api_key={$this->api_key}&photo_id={$image_id}";

        $result = file_get_contents( $url );

        if( empty( $result ) ){
            return new WP_Error( '404', 'Cannot connect to Flick API' );
        }

        $result = simplexml_load_string( $result );

        if( !isset( $result->sizes->size ) ){
            return new WP_Error( '404', 'Cannot get Flickr Image' );
        }

        $photo = $result->sizes->size[ count( $result->sizes->size ) - 1 ];
        $atts = $photo->attributes();

        return (string) $atts['source'];

    }
}

$GLOBALS['wp_manga_flickr_upload'] = new WP_MANGA_FLICKR_UPLOAD();
