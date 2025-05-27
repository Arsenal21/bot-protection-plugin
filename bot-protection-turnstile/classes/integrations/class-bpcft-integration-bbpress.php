<?php

class BPCFT_BBpress_Integration {

	public BPCFT_Turnstile $turnstile;

	public BPCFT_Config $settings;

	public function __construct() {
		$this->turnstile = BPCFT_Turnstile::get_instance();
		$this->settings  = BPCFT_Config::get_instance();

		// Set a reference that, the request is originated from a bbpress page, so later it can be used during cft validation.
		// Note: This gets applied for login, register and password reset forms by bbpress.
		add_filter( 'bbp_get_wp_login_action', array( $this, 'filter_bbp_wp_login_action' ), 30, 3 );

		$bpcft_enable_on_bbp_login = $this->settings->get_value( 'bpcft_enable_on_bbp_login' );
		if ($bpcft_enable_on_bbp_login){
			add_action( 'login_form', array( $this, 'render_bbp_login_form_cft' ) );
			add_action( 'authenticate', array( $this, 'check_bbp_login' ) ,  30, 1);
		}

		$bpcft_enable_on_bbp_registration = $this->settings->get_value( 'bpcft_enable_on_bbp_registration' );
		if ($bpcft_enable_on_bbp_registration){
			add_action( 'register_form', array( $this, 'render_bbp_register_form_cft' ) );
			add_action( 'registration_errors', array($this, 'check_bbp_registration'), 30, 3);
		}

		$bpcft_enable_on_bbp_pass_reset = $this->settings->get_value( 'bpcft_enable_on_bbp_pass_reset' );
		if ( $bpcft_enable_on_bbp_pass_reset ) {
			add_action( 'login_form', array( $this, 'render_bbp_pass_reset_form_cft' ) );
			add_action( 'lostpassword_post', array( $this, 'check_bbp_reset_password' ), 30, 1 );
		}

		$bpcft_enable_on_bbp_create_topic = $this->settings->get_value( 'bpcft_enable_on_bbp_create_topic' );
		if ($bpcft_enable_on_bbp_create_topic){
			add_action('bbp_theme_before_topic_form_submit_wrapper', array($this, 'render_bbp_create_topic_form_cft'));
			add_action('bbp_new_topic_pre_extras', array($this, 'check_bbp_topic'));
		}

		$bpcft_enable_on_bbp_topic_reply = $this->settings->get_value( 'bpcft_enable_on_bbp_topic_reply' );
		if ($bpcft_enable_on_bbp_topic_reply){
			add_action('bbp_theme_before_reply_form_submit_wrapper', array($this, 'render_bbp_topic_reply_form_cft'));
			add_action('bbp_new_reply_pre_extras', array($this, 'check_bbp_topic'));
		}
	}

	public function filter_bbp_wp_login_action( $login_url, $r, $args ){
		$login_url = add_query_arg( array( 'bbpress-bpcft' => 1 ), $login_url );

		return $login_url;
	}

	public function render_bbp_login_form_cft() {
		if (( function_exists('is_bbpress') && is_bbpress()) || (is_page() && has_shortcode(get_post()->post_content, 'bbp-login'))) {
			$this->turnstile->render_implicit( 'bpcft_callback', 'bbp-login', wp_rand(), 'bpcft-widget-mt-12' );
		}
	}

	public function render_bbp_register_form_cft() {
		if (is_page() && has_shortcode(get_post()->post_content, 'bbp-register')) {
			$this->turnstile->render_implicit( 'bpcft_callback', 'bbp-register', wp_rand(), 'bpcft-widget-mt-12' );
		}
	}

	public function render_bbp_pass_reset_form_cft() {
		if (is_page() && has_shortcode(get_post()->post_content, 'bbp-lost-pass')) {
			$this->turnstile->render_implicit( 'bpcft_callback', 'bbp-lost-pass', wp_rand() );
		}
	}

	public function render_bbp_create_topic_form_cft() {
		$this->turnstile->render_implicit( 'bpcft_callback', 'bbp-create-topic', 'bbp-create-topic' );
	}

	public function render_bbp_topic_reply_form_cft() {
		$this->turnstile->render_implicit( 'bpcft_callback', 'bbp-topic-reply', 'bbp-topic-reply' );
	}

	public function check_bbp_login( $user ) {
		if ( ! isset( $_REQUEST['bbpress-bpcft'] ) ){
			return $user;
		} // Skip if not a bbpress form submit.

		if ( ! isset( $user->ID ) ) {
			return $user;
		}
		// Check skip
		if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			return $user;
		} // Skip XMLRPC
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return $user;
		} // Skip REST API

		// Check Turnstile
		$result = $this->turnstile->check_cft_token_response();

		$success = isset( $result['success'] ) ? boolval( $result['success'] ) : false;
		$error_message = isset( $result['error_message'] ) ? $result['error_message'] : '';

		if ( empty($success) ) {
			$user = new WP_Error( 'bpcft_turnstile_error', BPCFT_Utils::failed_message($error_message) );
		}

		return $user;
	}

	public function check_bbp_registration($errors){
		if ( ! isset( $_REQUEST['bbpress-bpcft'] ) ){
			return $errors;
		} // Skip if not a bbpress form submit.

		// Check skip
		if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			return $errors;
		} // Skip XMLRPC
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return $errors;
		} // Skip REST API

		$result = $this->turnstile->check_cft_token_response();

		$success = isset( $result['success'] ) ? boolval( $result['success'] ) : false;
		$error_message = isset( $result['error_message'] ) ? $result['error_message'] : '';

		if ( empty($success) ) {
			$errors->add( 'bpcft_turnstile_error', BPCFT_Utils::failed_message($error_message) );
		}

		return $errors;
	}

	public function check_bbp_reset_password( $errors ) {
		// Check if cft validation already executed.
		if (is_wp_error($errors) && in_array('bpcft_turnstile_error', $errors->get_error_codes() )){
			return;
		}

		if ( ! isset( $_REQUEST['bbpress-bpcft'] ) ) {
			return;
		} // Skip if not a bbpress form submit.

		$result = $this->turnstile->check_cft_token_response();

		$success       = isset( $result['success'] ) ? boolval( $result['success'] ) : false;
		$error_message = isset( $result['error_message'] ) ? $result['error_message'] : '';

		if ( empty( $success ) ) {
			$errors->add( 'bpcft_turnstile_error', BPCFT_Utils::failed_message( $error_message ) );
		}
	}

	public function check_bbp_topic() {
		$result = $this->turnstile->check_cft_token_response();

		$success       = isset( $result['success'] ) ? boolval( $result['success'] ) : false;
		$error_message = isset( $result['error_message'] ) ? $result['error_message'] : '';

		if ( empty( $success ) ) {
			bbp_add_error( 'bpcft_turnstile_error', BPCFT_Utils::failed_message( $error_message ) );
		}
	}

}