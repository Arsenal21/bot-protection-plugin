<?php

class BPCFT_Utils {
	public static function is_login_page() {
		$login_url_path   = wp_parse_url( wp_login_url(), PHP_URL_PATH );

		$request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_url(wp_unslash($_SERVER['REQUEST_URI'])) : '';

		$current_url_path = wp_parse_url( $request_uri, PHP_URL_PATH );

		return $current_url_path == $login_url_path;
	}

	public static function is_registration_page() {
		// The registration page is in the same page of login, but with an action param 'register';
		if ( self::is_login_page() ) {
			return isset( $_GET['action'] ) && $_GET['action'] == 'register';
		}

		return false;
	}

	public static function is_reset_password_page() {
		// The registration page is in the same page of login, but with an action param 'lostpassword';
		if ( self::is_login_page() ) {
			return isset( $_GET['action'] ) && $_GET['action'] == 'lostpassword';
		}

		return false;
	}

	/**
	 * Gets the custom Turnstile failed message
	 */
	public static function failed_message( $default = "" ) {
		$error_msg = BPCFT_Config::get_instance()->get_value( 'bpcft_custom_error_msg', $default );

		if ( empty( $error_msg ) ) {
			return __( 'Please verify that you are human.', 'bot-protection-turnstile' );
		} else {
			return sanitize_text_field( $error_msg );
		}
	}

	public static function error_message_by_code( $code ) {
		switch ( $code ) {
			case 'missing-input-secret':
				return esc_html__( 'The secret parameter was not passed.','bot-protection-turnstile' );
			case 'invalid-input-secret':
				return esc_html__( 'The secret parameter was invalid or did not exist.','bot-protection-turnstile' );
			case 'missing-input-response':
				return esc_html__( 'The response parameter was not passed.','bot-protection-turnstile' );
			case 'invalid-input-response':
				return esc_html__( 'The response parameter is invalid or has expired.','bot-protection-turnstile' );
			case 'bad-request':
				return esc_html__( 'The request was rejected because it was malformed.','bot-protection-turnstile' );
			case 'timeout-or-duplicate':
				return esc_html__( 'The response parameter has already been validated before.','bot-protection-turnstile' );
			case 'internal-error':
				return esc_html__( 'An internal error happened while validating the response. The request can be retried.','bot-protection-turnstile' );
			default:
				return esc_html__( 'There was an error with Turnstile response. Please check your keys are correct.','bot-protection-turnstile' );
		}
	}

	public static function check_if_plugin_active( $plugin ) {
		// Include the plugin.php file if it's not already included
		if (!function_exists('is_plugin_active')) {
			include_once(ABSPATH . 'wp-admin/includes/plugin.php');
		}

		return is_plugin_active($plugin);
	}
}
