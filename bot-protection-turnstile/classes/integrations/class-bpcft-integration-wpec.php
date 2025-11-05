<?php

class BPCFT_WPEC_Integration {

	public BPCFT_Turnstile $turnstile;

	public BPCFT_Config $settings;

    public $enabled_on_manual_checkout = false;
    public $enabled_on_full_discount_checkout = false;

	public function __construct() {
		$this->turnstile = BPCFT_Turnstile::get_instance();
		$this->settings  = BPCFT_Config::get_instance();

		$bpcft_enable_on_wpec_full_discount_checkout = $this->settings->get_value( 'bpcft_enable_on_wpec_full_discount_checkout' );
		if ($bpcft_enable_on_wpec_full_discount_checkout){
			add_action( 'wpec_before_full_discount_checkout_button', array( $this, 'render_wpec_full_discount_checkout_form' ), 10, 2 );
		}

		$bpcft_enable_on_wpec_manual_checkout = $this->settings->get_value( 'bpcft_enable_on_wpec_manual_checkout' );
        if ($bpcft_enable_on_wpec_manual_checkout){
			add_action( 'wpec_before_manual_checkout_submit_button', array( $this, 'render_wpec_manual_checkout_form' ), 10, 2 );
        }

        if ($bpcft_enable_on_wpec_full_discount_checkout || $bpcft_enable_on_wpec_manual_checkout){
			add_action( 'wpec_url_payment_box_before_head_close', array( $this, 'wpec_url_payment_bpcft_scripts' ) ); // For url payment form.
			add_action( 'wpec_process_payment', array( $this, 'check_wpec_process_payment' ), 10 ,2 );
			add_action( 'wpec_process_manual_checkout', array( $this, 'check_wpec_process_payment' ), 10 ,2 );
        }

        $this->enabled_on_full_discount_checkout = $bpcft_enable_on_wpec_full_discount_checkout;
        $this->enabled_on_manual_checkout = $bpcft_enable_on_wpec_manual_checkout;
	}

	public function wpec_url_payment_bpcft_scripts() {
		?>
		<script type="text/javascript" src="<?php echo esc_url_raw( BPCFT_Turnstile::get_bpcft_script_url())?>"></script>
		<script type="text/javascript" src="<?php echo esc_url_raw(BPCFT_URL . '/js/bpcft-script-wpec.js')?>"></script>
		<script type="text/javascript" src="<?php echo esc_url_raw( BPCFT_Turnstile::get_cft_cdn_url_explicit() )?>"></script>

		<link rel="stylesheet" href="<?php echo esc_url_raw(BPCFT_Turnstile::get_bpcft_style_url()) ?>" />
		<?php
	}

	public function render_wpec_full_discount_checkout_form($args, $button_id) {
		wp_enqueue_script('bpcft-script-wpec', BPCFT_URL . '/js/bpcft-script-wpec.js', array('cloudflare-turnstile-script-explicit'), BPCFT_VERSION);

        $class = 'bpcft-widget-mb-12 bpcft_widget_full_discount_'.$button_id;

		$this->turnstile->render_explicit( 'bpcft_callback', 'wpec-full-discount-checkout', wp_rand() , $class);
	}

	public function render_wpec_manual_checkout_form($args, $button_id) {
		wp_enqueue_script('bpcft-script-wpec', BPCFT_URL . '/js/bpcft-script-wpec.js', array('cloudflare-turnstile-script-explicit'), BPCFT_VERSION);

		$class = 'bpcft-widget-mb-12 bpcft_widget_manual_checkout_'.$button_id;

		$this->turnstile->render_explicit( 'bpcft_callback', 'wpec-manual-checkout', wp_rand() , $class);
	}

	public function check_wpec_process_payment($payment, $data) {
        $is_manual_checkout = isset($payment['id']) && strpos($payment['id'], 'manual') !== false;

        if ($is_manual_checkout && !$this->enabled_on_manual_checkout){
            // Not enabled for manual checkout. Noting to do.
            return;
        }

		if (!$is_manual_checkout && !$this->enabled_on_full_discount_checkout){
			// Not enabled for full discount checkout. Noting to do.
			return;
		}

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