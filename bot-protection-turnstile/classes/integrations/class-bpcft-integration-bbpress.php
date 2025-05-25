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
	}

	public function filter_bbp_wp_login_action( $login_url, $r, $args ){
		$login_url = add_query_arg( array( 'bbpress-bpcft' => 1 ), $login_url );

		return $login_url;
	}

}