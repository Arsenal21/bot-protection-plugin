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
			add_action( 'authenticate', array( $this, 'check_woo_login' ), 30, 1 );
		}

		$bpcft_enable_on_woo_register = $this->settings->get_value( 'bpcft_enable_on_woo_registration' );
		if ( $bpcft_enable_on_woo_register ) {
			add_action( 'woocommerce_register_form', array( $this, 'render_woo_register_form_cft' ) );
			if ( ! is_admin() ) { // Prevents admin registration from failing
				add_action( 'woocommerce_register_post', array( $this, 'check_woo_register' ), 10, 3 );
			}
		}

		$bpcft_enable_on_woo_pass_reset = $this->settings->get_value( 'bpcft_enable_on_woo_pass_reset' );
		if ( $bpcft_enable_on_woo_pass_reset ) {
			add_action( 'woocommerce_lostpassword_form', array( $this, 'render_woo_register_form_cft' ) );
			add_action( 'lostpassword_post', array( $this, 'check_wp_reset_password' ), 30, 1 );
		}

		$bpcft_enable_on_woo_checkout = $this->settings->get_value( 'bpcft_enable_on_woo_checkout' );
		if ( $bpcft_enable_on_woo_checkout ) {
			add_action( 'woocommerce_loaded', array( $this, 'register_woo_endpoint_data' ) );
			add_action( 'woocommerce_review_order_before_submit', array( $this, 'render_woo_checkout_form_cft' ) );
			add_filter( 'render_block_woocommerce/checkout-actions-block', array( $this, 'render_woo_pre_block_checkout_form_cft'), 999, 1 ); // Before Actions block, not sure if this option is still supported.
			add_action( 'woocommerce_checkout_process', array( $this, 'check_woo_checkout' ) );
			add_action( 'woocommerce_store_api_checkout_update_order_from_request', array( $this, 'check_woo_checkout_block' ), 10, 2 );
		}
	}

	public function render_woo_login_form_cft() {
		$this->turnstile->render_implicit( 'bpcft_callback', 'woocommerce-login', wp_rand(), '' );
	}

	public function render_woo_register_form_cft() {
		$this->turnstile->render_implicit( 'bpcft_callback', 'woocommerce-register', wp_rand(), '' );
	}

	public function render_woo_checkout_form_cft() {
		wp_enqueue_script( 'bpcft-script-woo', BPCFT_URL . '/js/bpcft-script-woo.js', array( 'cloudflare-turnstile-script', 'jquery' ), BPCFT_VERSION, array(
			'strategy'  => 'defer',
			'in_footer' => true,
		) );

		$this->turnstile->render_implicit( 'bpcft_callback', 'woocommerce-checkout', 'woo-checkout', '' );
	}

	public function render_woo_pre_block_checkout_form_cft( $block_content ) {
		ob_start();
		$this->render_woo_checkout_form_cft();
		echo $block_content;
		$block_content = ob_get_contents();
		ob_end_clean();

		return $block_content;
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
			$user = new WP_Error( 'bpcft_turnstile_error', BPCFT_Utils::failed_message( $error_message ) );
		}

		return $user;
	}

	public function check_woo_register( $username, $email, $errors ) {
		if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			return;
		} // Skip XMLRPC
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return;
		} // Skip REST API

		if ( ! is_checkout() ) {
			$result = $this->turnstile->check_cft_token_response();

			$success       = isset( $result['success'] ) ? boolval( $result['success'] ) : false;
			$error_message = isset( $result['error_message'] ) ? $result['error_message'] : '';

			if ( empty( $success ) ) {
				$errors->add( 'bpcft_turnstile_error', BPCFT_Utils::failed_message( $error_message ) );
			}
		}
	}

	public function check_wp_reset_password( $errors ) {
		if ( isset( $_POST['woocommerce-lost-password-nonce'] ) ) {
			$result = $this->turnstile->check_cft_token_response();

			$success       = isset( $result['success'] ) ? boolval( $result['success'] ) : false;
			$error_message = isset( $result['error_message'] ) ? $result['error_message'] : '';

			if ( empty( $success ) ) {
				$errors->add( 'bpcft_turnstile_error', BPCFT_Utils::failed_message( $error_message ) );
			}
		}
	}

	public function check_woo_checkout() {
		$result = $this->turnstile->check_cft_token_response();

		$success       = isset( $result['success'] ) ? boolval( $result['success'] ) : false;
		$error_message = isset( $result['error_message'] ) ? $result['error_message'] : '';

		if ( empty( $success ) ) {
			wc_add_notice( BPCFT_Utils::failed_message( $error_message ), 'error' );
		}
	}

	public function check_woo_checkout_block( $order, $request ) {
		if ( $request->get_method() === 'POST' ) {
			$extensions = $request->get_param( 'extensions' );
			if ( empty( $extensions ) ) {
				throw new \Exception( BPCFT_Utils::failed_message() );

			}
			$value = $extensions['bp-cf-turnstile'];
			if ( empty( $value ) ) {
				throw new \Exception( BPCFT_Utils::failed_message() );
			}

			$bpcft_token = $value['token'];
			$result = $this->turnstile->check_cft_token_response($bpcft_token);

			$success       = isset( $result['success'] ) ? boolval( $result['success'] ) : false;
			$error_message = isset( $result['error_message'] ) ? $result['error_message'] : '';

			if ( empty( $success ) ) {
				throw new \Exception( BPCFT_Utils::failed_message( $error_message ) );
			}
		}
	}

	public function register_woo_endpoint_data() {
		woocommerce_store_api_register_endpoint_data(
			array(
				'endpoint'        => 'checkout',
				'namespace'       => 'bp-cf-turnstile',
				'schema_callback' => array($this, 'get_bpcftt_woo_schema'),
			)
		);
	}

	public function get_bpcftt_woo_schema() {
		return array(
			'token' => array(
				'description' => __( 'Turnstile token.', 'bpcft' ),
				'type'        => 'string',
				'context'     => array()
			),
		);
	}
}