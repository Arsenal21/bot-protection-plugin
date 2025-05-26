<?php

class BPCFT_WordPress_Integration {

	public BPCFT_Turnstile $turnstile;

	public BPCFT_Config $settings;

	public function __construct() {

		$this->turnstile = BPCFT_Turnstile::get_instance();
		$this->settings  = BPCFT_Config::get_instance();

		$bpcft_enable_on_wp_login = $this->settings->get_value( 'bpcft_enable_on_wp_login' );
		if ( $bpcft_enable_on_wp_login ) {
			add_action( 'login_form', array( $this, 'render_wp_login_form_cft' ) );
			add_action( 'authenticate', array( $this, 'check_wp_login' ), 30, 1 ); // Here use the lower priority to get the WP_User object properly.
		}

		$bpcft_enable_on_wp_register = $this->settings->get_value( 'bpcft_enable_on_wp_register' );
		if ( $bpcft_enable_on_wp_register ) {
			add_action('register_form', array($this, 'render_wp_register_form_cft'));
			add_action('registration_errors', array($this, 'check_wp_registration'), 30, 3);
		}

		$bpcft_enable_on_wp_reset_password = $this->settings->get_value( 'bpcft_enable_on_wp_reset_password' );
		if ( $bpcft_enable_on_wp_reset_password ) {
			add_action('lostpassword_form', array($this, 'render_wp_pass_reset_form_cft'));
			add_action('lostpassword_post', array($this, 'check_wp_reset_password'), 30, 1);
		}

		$bpcft_enable_on_wp_comment = $this->settings->get_value( 'bpcft_enable_on_wp_comment' );
		if ( $bpcft_enable_on_wp_comment ) {
			add_action('comment_form_submit_button', array($this, 'render_comment_form_cft'), 100, 2);
			add_action('pre_comment_on_post', array( $this, 'check_wp_comment'), 30, 1);
		}
	}

	public function render_wp_login_form_cft() {
		// Skip if not on wp login page
		if (! BPCFT_Utils::is_login_page()) {
			return;
		}

		$this->turnstile->render_implicit( 'bpcft_callback', 'wordpress-login', wp_rand(), 'bpcft-widget-ml-n15 bpcft-widget-mb-12 bpcft-semi-small-widget-size' );
	}

	public function render_wp_register_form_cft() {
		// Skip if not on wp registration page
		if (! BPCFT_Utils::is_registration_page()) {
			return;
		}

		$this->turnstile->render_implicit('bpcft_callback', 'wordpress-register', wp_rand(), 'bpcft-widget-ml-n15 bpcft-semi-small-widget-size' );
	}

	public function render_wp_pass_reset_form_cft(){
		// Check if not on wp password reset page.
		if (! BPCFT_Utils::is_reset_password_page() ){
			return;
		}

		$this->turnstile->render_implicit('bpcft_callback', 'wordpress-reset', wp_rand(), 'bpcft-widget-ml-n15 bpcft-widget-mb-12 bpcft-semi-small-widget-size' );
	}

	public function render_comment_form_cft( $submit_button, $args ) {
		wp_enqueue_script('bpcft-common-script');
		$unique_id = wp_rand();

		$submit_before = '';
		$submit_after  = '';

		$submit_before .= $this->turnstile->get_implicit_widget( 'bpcft_callback', 'wordpress-comment', 'c-' .$unique_id );
		$submit_before .= '<br>';

        // TODO: This might be needed later.
		// $submit_after .= $this->turnstile->force_re_render( "-c-" . $unique_id );

		return $submit_before . $submit_button . $submit_after;
	}

	public function check_wp_login( $user ) {
		// Check skip
		if ( ! isset( $user->ID ) ) {
			return $user;
		}
		if ( isset( $_POST['woocommerce-login-nonce'] ) ) {
			return $user;
		} // Skip woo
		if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			return $user;
		} // Skip XMLRPC
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return $user;
		} // Skip REST API
		if ( is_wp_error( $user ) && isset( $user->errors['empty_username'] ) && isset( $user->errors['empty_password'] ) ) {
			return $user;
		} // Skip Errors

		// Skip if not on login page
		if ( ! BPCFT_Utils::is_login_page()) {
			return $user;
		}

		// Check Turnstile
		$result = $this->turnstile->check_cft_token_response();

		$success = isset( $result['success'] ) ? boolval( $result['success'] ) : false;
		$error_message = isset( $result['error_message'] ) ? $result['error_message'] : '';

		if ( empty($success) ) {
			 $user = new WP_Error( 'bpcft_turnstile_error', BPCFT_Utils::failed_message($error_message) );
		}

		return $user;
	}

	public function check_wp_registration($errors){
		// Check skip
		if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			return $errors;
		} // Skip XMLRPC
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return $errors;
		} // Skip REST API
		if ( isset( $_POST['woocommerce-register-nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash($_POST['woocommerce-register-nonce']) ), 'woocommerce-register' ) ) {
			return $errors;
		} // Skip Woo

		// Skip if not on registration page
		if ( ! BPCFT_Utils::is_registration_page() ) {
			return $errors;
		}

		// Skip Logged In Admins
		if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
			return $errors;
		}

		$result = $this->turnstile->check_cft_token_response();

		$success = isset( $result['success'] ) ? boolval( $result['success'] ) : false;
		$error_message = isset( $result['error_message'] ) ? $result['error_message'] : '';

		if ( empty($success) ) {
			$errors->add( 'bpcft_turnstile_error', BPCFT_Utils::failed_message($error_message) );
		}

		return $errors;
	}

	public function check_wp_reset_password($validation_errors){
		// Skip Woo
		if ( isset( $_POST['woocommerce-lost-password-nonce'] ) ) {
			return;
		}

		// Check if password reset page.
		if ( ! BPCFT_Utils::is_reset_password_page() ){
			return;
		}

		$result = $this->turnstile->check_cft_token_response();

		$success = isset( $result['success'] ) ? boolval( $result['success'] ) : false;
		$error_message = isset( $result['error_message'] ) ? $result['error_message'] : '';

		if ( empty($success) ) {
			$validation_errors->add( 'bpcft_turnstile_error', BPCFT_Utils::failed_message($error_message) );
		}
	}

	public function check_wp_comment( $comment_data ) {
		if ( is_admin() ) {
			return $comment_data;
		}

		if ( ! empty( $_POST ) ) {
			$result = BPCFT_Turnstile::get_instance()->check_cft_token_response();

			$success = isset( $result['success'] ) ? boolval( $result['success'] ) : false;
			$error_message = isset( $result['error_message'] ) ? $result['error_message'] : '';

			if ( empty($success) ) {
				wp_die(
					'<p><strong>' . esc_attr__( 'ERROR:', 'bot-protection-turnstile' ) . '</strong> ' . esc_html(BPCFT_Utils::failed_message($error_message)) . '</p>',
					'bot-protection-turnstile',
					array( 'response'  => 403 )
				);
			}

		}
		return $comment_data;
	}

}