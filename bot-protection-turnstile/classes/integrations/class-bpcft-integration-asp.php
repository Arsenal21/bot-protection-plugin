<?php

class BPCFT_ASP_Integration {

	public BPCFT_Turnstile $turnstile;

	public BPCFT_Config $settings;

	public function __construct() {
		$this->turnstile = BPCFT_Turnstile::get_instance();

		$this->settings  = BPCFT_Config::get_instance();

		$bpcft_enable_on_asp_checkout = $this->settings->get_value( 'bpcft_enable_on_asp_checkout' );
		if ( $bpcft_enable_on_asp_checkout ) {
			add_filter( 'asp_ng_pp_data_ready', array( $this, 'asp_ng_pp_data_ready' ), 10, 2 );
			add_action( 'asp_ng_pp_output_add_styles', array( $this, 'add_cft_styles' ) );
			add_action( 'asp_ng_pp_output_add_scripts', array( $this, 'add_cft_scripts' ) );
			add_filter( 'asp_ng_pp_output_before_buttons', array( $this, 'render_asp_checkout_form_cft' ), 10, 2 );
			add_action( 'asp_ng_before_api_pre_submission_validation', array( $this, 'check_asp_checkout' ) );

			// Hide the captcha disabled warning notice of asp core plugin if cft is enabled for asp.
			add_filter( 'asp_hide_captcha_disabled_warning_notice_in_admin',  array($this, 'asp_hide_captcha_disabled_warning_notice'));
		}
	}

	public function asp_hide_captcha_disabled_warning_notice() {
		return true;
	}

	public function asp_ng_pp_data_ready( $data, $atts ) {
		$addon            = array(
			'name'    => 'BP Cloudflare Turnstile',
			'handler' => 'BPCftHandlerNG',
		);
		$data['addons'][] = $addon;

		return $data;
	}

	public function add_cft_styles( $styles ) {
		$styles[] = array(
			'footer' => true,
			'src'    => BPCFT_Turnstile::get_bpcft_style_url() . '?ver=' . BPCFT_VERSION,
		);
		return $styles;
	}

	public function add_cft_scripts( $scripts ) {
		$scripts[] = array(
			'footer' => true,
			'src'    => BPCFT_Turnstile::get_cft_cdn_url(),
		);
		$scripts[] = array(
			'footer' => true,
			'src'    => BPCFT_Turnstile::get_bpcft_script_url() . '?ver=' . BPCFT_VERSION,
		);
		$scripts[] = array(
			'footer' => true,
			'src'    => BPCFT_URL . '/js/bpcft-script-asp.js' . '?ver=' . BPCFT_VERSION,
		);
		return $scripts;
	}

	public function render_asp_checkout_form_cft($out, $data) {
		echo wp_kses_post($this->turnstile->get_implicit_widget( 'bpcft_asp_checkout_form_callback', 'asp-checkout', wp_rand(), 'bpcft-place-widget-center' ));

		return $out;
	}

	public function check_asp_checkout( ) {
		$token = isset( $_POST['bpcft_token_response'] ) ? sanitize_text_field(wp_unslash($_POST['bpcft_token_response'])) : '';

		// Check Turnstile
		$result = $this->turnstile->check_cft_token_response( $token );

		$success       = isset( $result['success'] ) ? boolval( $result['success'] ) : false;
		$error_message = isset( $result['error_message'] ) ? $result['error_message'] : '';

		// Send error response if failed.
		if ( empty($success) ) {
			$out            = array();
			$out['success'] = false;

			$error_msg = __( 'Cloudflare turnstile error: ', 'bot-protection-turnstile' );
			$error_msg .= BPCFT_Utils::failed_message( $error_message );

			$out['err'] = $error_msg;
			wp_send_json( $out );
		}
	}
}