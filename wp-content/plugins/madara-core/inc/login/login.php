<?php
	/**
	 *  Plugin Name: MangaBooth Manga
	 *  Description: Login
	 */


	Class WP_MANGA_LOGIN {

		public function __construct() {

			// add_shortcode( 'wp_manga_user', array($this, 'wp_manga_user_shortcode') );

			add_action( 'wp_enqueue_scripts', array( $this, 'wp_manga_login_styles' ), 50 );
			add_action( 'wp_ajax_nopriv_wp_manga_signin', array( $this, 'wp_manga_sign_in' ) );
			add_action( 'wp_ajax_nopriv_wp_manga_signup', array( $this, 'wp_manga_sign_up' ) );
			add_action( 'wp_ajax_nopriv_wp_manga_reset', array( $this, 'wp_manga_reset' ) );
			add_action( 'wp_ajax_nopriv_wp_manga_reset_password', array( $this, 'wp_manga_reset_password' ) );

			add_action( 'wp_footer', array( $this, 'login_template' ) );
			add_action( 'wp_loaded', array( $this, 'remove_wp_toolbar' ) );
		}

		function login_template() {

			global $wp_manga_template;
			if ( ! is_user_logged_in() ) {
				$wp_manga_template->load_template( 'login', false );
			}

			// Reset password form
			if(
				isset( $_GET['action'] )
				&& $_GET['action'] == 'rp'
				&& !empty( $_GET['key'] )
				&& !empty( $_GET['login'] )
			){
				$check = check_password_reset_key( $_GET['key'], $_GET['login'] );

				if( is_wp_error( $check ) ){
					return;
				}

				wp_enqueue_script('utils');
				wp_enqueue_script('user-profile');
				$wp_manga_template->load_template( 'reset-password', false );
			}

		}

		function wp_manga_login_styles() {
			wp_enqueue_script( 'wp-manga-login-ajax', WP_MANGA_URI . 'assets/js/login.js', array( 'jquery' ), '1.7.2', true );
			wp_localize_script( 'wp-manga-login-ajax', 'wpMangaLogin', array(
				'admin_ajax' => admin_url( 'admin-ajax.php' ),
				'home_url'   => get_home_url(),
                'nonce' => wp_create_nonce('wp-manga'),
				'messages' => array(
					'please_enter_username' => esc_html__('Please enter username', WP_MANGA_TEXTDOMAIN),
					'please_enter_password' => esc_html__('Please enter password', WP_MANGA_TEXTDOMAIN),
					'invalid_username_or_password' => esc_html__('Invalid username or password', WP_MANGA_TEXTDOMAIN),
					'server_error' => esc_html__('Server Error!', WP_MANGA_TEXTDOMAIN),
					'username_or_email_cannot_be_empty' => esc_html__('Username or Email cannot be empty', WP_MANGA_TEXTDOMAIN),
					'please_fill_all_fields' => esc_html__('Please fill in all password fields.', WP_MANGA_TEXTDOMAIN),
					'password_cannot_less_than_12' => esc_html__('Password cannot have less than 12 characters', WP_MANGA_TEXTDOMAIN),
					'password_doesnot_match' => esc_html__('Password does not match. Please try again.', WP_MANGA_TEXTDOMAIN),
					'username_cannot_empty' => esc_html__('Username cannot be empty', WP_MANGA_TEXTDOMAIN),
					'email_cannot_empty' => esc_html__('Email cannot be empty', WP_MANGA_TEXTDOMAIN),
					'password_cannot_empty' => esc_html__('Password cannot be empty', WP_MANGA_TEXTDOMAIN),
				)
			) );
		}

		function wp_manga_sign_in() {

			if( empty( $_POST['login'] ) || empty( $_POST['pass'] ) ){
				wp_send_json_error( esc_html__('Invalid Login Request', WP_MANGA_TEXTDOMAIN ) );
			}

			// This is for Login ReCaptcha plugin
			$_SERVER['PHP_SELF'] = 'wp-login.php';

			$user_data                  = array();
			$user_data['user_login']    = trim( $_POST['login'] );
			$user_data['user_password'] = trim( $_POST['pass'] );
			$user_data['remember']      = $_POST['rememberme'];
			$user                       = wp_signon( $user_data, false );
            $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
            if(wp_verify_nonce( $nonce, "wp-manga" )){
                if ( is_wp_error( $user ) ) {
                    wp_send_json_error( strip_tags( $user->get_error_message() ) );
                } else {
                    wp_set_current_user( $user->ID, $user_data['user_login'] );
                    wp_send_json_success( array(
                        'id'    => $user->ID
                    ) );
                }
            }
		}

		function wp_manga_sign_up() {
			if ( ! empty( $_POST['user_login'] ) && ! empty( $_POST['user_pass'] ) && ! empty( $_POST['user_email'] ) ) {

				/**
				* Verify Captcha
				*/
				if( method_exists( 'LoginNocaptcha', 'authenticate' ) ){
					// This is for Login ReCaptcha plugin
					$_SERVER['PHP_SELF'] = 'wp-login.php';

					// Verify Recaptcha with dummy user
					$recaptcha = LoginNocaptcha::authenticate( null, null, null );

					if( is_wp_error( $recaptcha ) ){
						wp_send_json_error( __( $recaptcha->get_error_message(), WP_MANGA_TEXTDOMAIN ) );
					}
				}

				/**
				 * Validate Email
				 */
				if( ! $this->is_valid_email( $_POST['user_email'] ) ){
					return wp_send_json_error( esc_html__( 'Invalid email address. Please check again.', WP_MANGA_TEXTDOMAIN ) );
				}

				$user_data               = array();
				$user_data['user_login'] = trim( $_POST['user_login'] );
				$user_data['user_pass']  = trim( $_POST['user_pass'] );
				$user_data['user_email'] = trim( $_POST['user_email'] );
				$user_data['role']       = get_option('default_role','subscriber');
				
				$min_username_characters = apply_filters('wp_manga_user_registration_username_min_characters', 6);
				if( strlen( $user_data['user_login'] ) < $min_username_characters ){
					return wp_send_json_error( sprintf(esc_html__( 'Username must have at least %d characters', WP_MANGA_TEXTDOMAIN ), $min_username_characters) );
				}
				
				$min_password_characters = apply_filters('wp_manga_user_registration_password_min_characters', 6);
				if( strlen( $user_data['user_pass'] ) < $min_password_characters ){
					return wp_send_json_error( sprintf(esc_html__( 'Password must have at least %d characters.', WP_MANGA_TEXTDOMAIN ), $min_password_characters) );
				}

				$user_id = wp_insert_user( $user_data );

				if ( is_wp_error( $user_id ) ) {
					wp_send_json_error( strip_tags( $user_id->get_error_message() ) );
				} else {

					do_action( 'register_new_user', $user_id );

					wp_send_json_success( __( 'Registration successfully! You can login now.', WP_MANGA_TEXTDOMAIN ) );
				}
			} else {
				wp_send_json_error( __( 'There was an error when registration', WP_MANGA_TEXTDOMAIN ) );
			}
		}

		function wp_manga_reset() {
			/**
			* Verify Captcha
			*/
			if( method_exists( 'LoginNocaptcha', 'authenticate' ) ){
				// This is for Login ReCaptcha plugin
				$_SERVER['PHP_SELF'] = 'wp-login.php';

				// Verify Recaptcha with dummy user
				$recaptcha = LoginNocaptcha::authenticate( null, null, null );

				if( is_wp_error( $recaptcha ) ){
					wp_send_json_error( __( $recaptcha->get_error_message(), WP_MANGA_TEXTDOMAIN ) );
				}
			}
			
			$user_reset = isset( $_POST['user'] ) ? $_POST['user'] : '';

			if ( empty( $user_reset ) ) {
				wp_send_json_error( __( 'Username or email address cannot be empty' ) );
			}

			$user_reset = trim( $user_reset );

			$result = $this->retrieve_password( $user_reset );

			if( is_wp_error( $result ) ){
				wp_send_json_error( $result->get_error_message() );
			}else{
				wp_send_json_success( __( 'Please check your email to get reset password link.', WP_MANGA_TEXTDOMAIN ) );
			}
			die();
		}

		function retrieve_password( $user_login ){            
            if( $this->is_valid_email( $user_login ) ){
                $user_data = get_user_by('email', $user_login );
            }else{
                $user_data = get_user_by( 'login', $user_login );
            }

            if( ! $user_data ){
                return new WP_Error( 'invalid_login', __( 'Invalid user login', WP_MANGA_TEXTDOMAIN ) );
            }

            $key = get_password_reset_key( $user_data );

            if ( is_wp_error( $key ) ) {
                return $key;
            }

            if ( is_multisite() ) {
                $site_name = get_network()->site_name;
            } else {
                /*
                 * The blogname option is escaped with esc_html on the way into the database
                 * in sanitize_option we want to reverse this for the plain text arena of emails.
                 */
                $site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
            }
            $user_email = $user_data->user_email;

            $message = __( 'Someone has requested a password reset for the following account:', WP_MANGA_TEXTDOMAIN ) . "\r\n\r\n";

            /* translators	: %s: user login */
            $message .= sprintf( __( 'Username: %s', WP_MANGA_TEXTDOMAIN), $user_login ) . "\r\n\r\n";
            $message .= __( 'If this was a mistake, just ignore this email and nothing will happen.', WP_MANGA_TEXTDOMAIN ) . "\r\n\r\n";
            $message .= __( 'To reset your password, visit the following address:', WP_MANGA_TEXTDOMAIN ) . "\r\n\r\n";
            $message .= '<' . home_url( "?action=rp&key=$key&login=" . rawurlencode( $user_data->user_login ) ) . ">\r\n";

            /* translators: Password reset email subject. %s: Site name */
            $title = sprintf( __( '[%s] Password Reset' ), $site_name );

            $title = apply_filters( 'retrieve_password_title', $title, $user_login, $user_data );

            $message = apply_filters( 'retrieve_password_message', $message, $key, $user_login, $user_data );

            if( ! wp_mail( $user_email, wp_specialchars_decode( $title ), $message ) ){
                return new WP_Error( 'sent_failed', __( 'The email could not be sent.', WP_MANGA_TEXTDOMAIN ) );
            }

            return true;
		}

		function remove_wp_toolbar() {

			if ( is_user_logged_in() ) {

				//check current user role
				$user  = wp_get_current_user();
				$roles = $user->roles;

				global $wp_manga_setting;
				//check if hide admin for administrator
				$admin_hide_bar = $wp_manga_setting->get_manga_option( 'admin_hide_bar', false );

				if ( in_array( 'administrator', $roles ) && $admin_hide_bar == false ) {
					show_admin_bar( true );

					return;
				}
			}

			show_admin_bar( false );
		}

		public function wp_manga_reset_password(){

			if(
				! empty( $_POST['user'] )
				&& ! empty( $_POST['new_password'] )
				&& strlen( $_POST['new_password'] ) > 12
				&& ! empty( $_POST['key'] )
			){
				$check = check_password_reset_key( $_POST['key'], $_POST['user'] );

				if( is_wp_error( $check ) ){
					return wp_send_json_error( __( 'Invalid reset password request', WP_MANGA_TEXTDOMAIN ) );;
				}

				$password = base64_decode( $_POST['new_password'] );
				$user_login = $_POST['user'];

				if( empty( $password ) ){
					wp_send_json_error( __( 'Invalid password requested', WP_MANGA_TEXTDOMAIN ) );
				}
				if( $this->is_valid_email( $user_login ) ){
					$user_data = get_user_by('email', $user_login );
				}else{
					$user_data = get_user_by( 'login', $user_login );
				}

				if( ! $user_data ){
					return wp_send_json_error( __( 'User not found', WP_MANGA_TEXTDOMAIN ) );
				}

				wp_set_password( $password, $user_data->ID );

				wp_send_json_success([
					'message' => __( 'Password reset successuflly! You can login now.' )
				]);

			}

			wp_send_json_error( __( 'Invalid reset password request', WP_MANGA_TEXTDOMAIN ) );

		}

		public function is_valid_email( $email ){
			return filter_var( $email, FILTER_VALIDATE_EMAIL );
		}
	}

	$GLOBALS['wp_manga_login'] = new WP_MANGA_LOGIN;
