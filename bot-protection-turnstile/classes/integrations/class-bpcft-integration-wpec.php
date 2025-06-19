<?php

class BPCFT_WPEC_Integration {

	public BPCFT_Turnstile $turnstile;

	public BPCFT_Config $settings;

	public function __construct() {
		$this->turnstile = BPCFT_Turnstile::get_instance();
		$this->settings  = BPCFT_Config::get_instance();

		$bpcft_enable_on_wpec_full_discount_checkout = $this->settings->get_value( 'bpcft_enable_on_wpec_full_discount_checkout' );
		if ($bpcft_enable_on_wpec_full_discount_checkout){
			add_action( 'wpec_before_full_discount_checkout_button', array( $this, 'render_wpec_full_discount_checkout_form' ), 10, 2 );
			add_action( 'wpec_process_payment', array( $this, 'check_wpec_full_discount_checkout' ), 10 ,2 );
			add_action( 'wpec_url_payment_box_before_head_close', array( $this, 'wpec_url_payment_bpcft_scripts' ) );
		}
	}

	public function wpec_url_payment_bpcft_scripts() {
		?>
		<script type="text/javascript" src="<?php echo esc_url_raw( BPCFT_Turnstile::get_cft_cdn_url_explicit() )?>"></script>
		<script type="text/javascript" src="<?php echo esc_url_raw( BPCFT_Turnstile::get_bpcft_script_url())?>"></script>
		<script type="text/javascript" src="<?php echo esc_url_raw(BPCFT_URL . '/js/bpcft-script-wpec.js')?>"></script>

		<link rel="stylesheet" href="<?php echo esc_url_raw(BPCFT_Turnstile::get_bpcft_style_url()) ?>" />
		<?php
	}

	public function render_wpec_full_discount_checkout_form($args, $button_id) {
		wp_enqueue_script('bpcft-script-wpec', BPCFT_URL . '/js/bpcft-script-wpec.js', array('bpcft-common-script-explicit'), BPCFT_VERSION );

        $class = 'bpcft-widget-mb-12 bpcft_widget_'.$button_id;

		$this->turnstile->render_explicit( 'bpcft_callback', 'wpec-full-discount-checkout', wp_rand() , $class);
	}

	public function check_wpec_full_discount_checkout($payment, $data) {
		$token = isset($payment['bpcftResponse']) ? $payment['bpcftResponse'] : '';

		// Check Turnstile
		$result = $this->turnstile->check_cft_token_response($token);

		$success = isset( $result['success'] ) ? boolval( $result['success'] ) : false;
		$error_message = isset( $result['error_message'] ) ? $result['error_message'] : '';

		if ( empty($success) ) {
			$msg = BPCFT_Utils::failed_message($error_message);
			\WP_Express_Checkout\Debug\Logger::log( '[BPCFT] ' .$msg, false );
			wp_send_json(BPCFT_Utils::failed_message($error_message));
		}
	}
}