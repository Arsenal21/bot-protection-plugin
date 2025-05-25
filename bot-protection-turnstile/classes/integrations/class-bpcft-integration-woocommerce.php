<?php

class BPCFT_WooCommerce_Integration {

	public BPCFT_Turnstile $turnstile;

	public BPCFT_Config $settings;

	public function __construct() {

		$this->turnstile = BPCFT_Turnstile::get_instance();
		$this->settings  = BPCFT_Config::get_instance();

		$bpcft_enable_on_woo_login = $this->settings->get_value( 'bpcft_enable_on_woo_login' );
		if ( $bpcft_enable_on_woo_login ) {
			add_action( 'woocommerce_login_form', array( $this, 'render_woo_login_form_cft' ) );

			// TODO: Need to fix this.
			if ( empty( $this->settings->get_value( 'bpcft_enable_on_wp_login' ) ) ) {
				add_action( 'authenticate', array( $this, 'check_woo_login' ), 30, 1 );
			}
		}
	}

	public function render_woo_login_form_cft() {
		$this->turnstile->render_implicit( 'bpcft_callback', 'woocommerce-login', wp_rand(), '' );
	}

	public function check_woo_login( $user ) {
		// Check skip
		if ( ! isset( $user->ID ) ) {
			return $user;
		}
		if ( ! isset( $_POST['woocommerce-login-nonce'] ) ) {
			return $user;
		} // Skip if not WooCommerce login
		if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			return $user;
		} // Skip XMLRPC
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return $user;
		} // Skip REST API
		if ( is_wp_error( $user ) && isset( $user->errors['empty_username'] ) && isset( $user->errors['empty_password'] ) ) {
			return $user;
		} // Skip Errors

		// Check Turnstile
		$result = $this->turnstile->check_cft_token_response();

		$success       = isset( $result['success'] ) ? boolval( $result['success'] ) : false;
		$error_message = isset( $result['error_message'] ) ? $result['error_message'] : '';

		if ( empty( $success ) ) {
			$user = new WP_Error( 'wpf_cf_turnstile_error', BPCFT_Utils::failed_message( $error_message ) );
		}

		return $user;
	}

}