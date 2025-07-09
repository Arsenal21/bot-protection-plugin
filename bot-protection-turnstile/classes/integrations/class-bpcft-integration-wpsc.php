<?php

class BPCFT_WPSC_Integration {

	public BPCFT_Turnstile $turnstile;

	public BPCFT_Config $settings;

	public function __construct() {
		$this->turnstile = BPCFT_Turnstile::get_instance();
		$this->settings  = BPCFT_Config::get_instance();

		$bpcft_enable_on_wpsc_manual_checkout = $this->settings->get_value( 'bpcft_enable_on_wpsc_manual_checkout' );
		if ($bpcft_enable_on_wpsc_manual_checkout){
			add_action( 'wpsc_before_manual_checkout_form_submit', array( $this, 'render_wpsc_manual_checkout_form_cft' ) );
			add_action( 'wpsc_manual_payment_checkout', array( $this, 'check_wpsc_manual_checkout' ) );
		}
	}

	public function render_wpsc_manual_checkout_form_cft() {
		wp_enqueue_script( 'bpcft-script-wpsc', BPCFT_URL . '/js/bpcft-script-wpsc.js', array( 'cloudflare-turnstile-script' ), BPCFT_VERSION );

		ob_start();
		$this->turnstile->render_implicit( 'bpcft_callback', 'wpsc-manual-checkout', wp_rand() , 'bpcft-widget-mt-15');
		return ob_get_clean();
	}

	public function check_wpsc_manual_checkout($post_data) {
		$token = isset($post_data['bpcftResponse']) ? $post_data['bpcftResponse'] : '';

		// Check Turnstile
		$result = $this->turnstile->check_cft_token_response($token);

		$success = isset( $result['success'] ) ? boolval( $result['success'] ) : false;
		$error_message = isset( $result['error_message'] ) ? $result['error_message'] : '';

		if ( empty($success) ) {
			wp_send_json_error(array(
				"message" => BPCFT_Utils::failed_message($error_message),
			));
		}
	}
}