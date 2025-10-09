<?php

class BPCFT_eMember_Integration {

	public BPCFT_Turnstile $turnstile;

	public BPCFT_Config $settings;

	public function __construct() {
		$this->turnstile = BPCFT_Turnstile::get_instance();
		$this->settings  = BPCFT_Config::get_instance();

		$bpcft_enable_on_emember_reg_form = $this->settings->get_value( 'bpcft_enable_on_emember_reg_form' );
		if ( $bpcft_enable_on_emember_reg_form ) {
			add_filter( 'emember_captcha', array( $this, 'render_emember_reg_form_cft' ) );
			add_filter( 'emember_captcha_varify', array( $this, 'check_cft' ) );
		}

		$bpcft_enable_on_emember_login_form = $this->settings->get_value( 'bpcft_enable_on_emember_login_form' );
		if ( $bpcft_enable_on_emember_login_form ) {
			add_filter( 'emember_captcha_login', array( $this, 'render_emember_login_form_cft' ) );
			add_filter( 'emember_captcha_varify_login', array( $this, 'check_cft' ) );
		}

		$bpcft_enable_on_emember_pass_reset_form = $this->settings->get_value( 'bpcft_enable_on_emember_pass_reset_form' );
		if ( $bpcft_enable_on_emember_pass_reset_form ) {
			add_filter( 'emember_captcha_pass_reset', array( $this, 'render_emember_pass_reset_form_cft' ) );
			add_filter( 'emember_captcha_varify_pass_reset', array( $this, 'check_cft' ) );
		}
	}
	
	public function render_emember_reg_form_cft($output) {
		ob_start();
		$this->turnstile->render_implicit( 'bpcft_callback', 'emember-registration', wp_rand() );
		$output .= ob_get_clean();
		return $output;
	}

	public function render_emember_login_form_cft($output) {
		ob_start();
		$this->turnstile->render_implicit( 'bpcft_callback', 'emember-login', wp_rand(), 'bpcft-semi-small-widget-size bpcft-transform-origin-top-left' );
		$output .= ob_get_clean();
		return $output;
	}

	public function render_emember_pass_reset_form_cft($output) {
		ob_start();
		?>
		<fieldset class="emember-centered">
			<?php echo $this->turnstile->render_implicit( 'bpcft_callback', 'emember-pass-reset', wp_rand() ); ?>
		</fieldset>
		<?php
		$output .= ob_get_clean();
		return $output;
	}

	public function check_cft( $is_valid ) {
		// Check Turnstile
		$result = $this->turnstile->check_cft_token_response();

		$success = isset( $result['success'] ) ? boolval( $result['success'] ) : false;
		$error_message = isset( $result['error_message'] ) ? $result['error_message'] : '';

		if ( empty($success) ) {
			if (function_exists('eMember_log_debug')) {
				$msg = BPCFT_Utils::failed_message($error_message);
				eMember_log_debug('[BPCFT] ' . $msg, false);
			}
			$is_valid = false;
		}

		return $is_valid;
	}
}