<?php

class BPCFT_SDM_Integration {

	public BPCFT_Turnstile $turnstile;

	public BPCFT_Config $settings;

	public function __construct() {
		$this->turnstile = BPCFT_Turnstile::get_instance();

		$this->settings  = BPCFT_Config::get_instance();

		$bpcft_enable_on_sdm_download = $this->settings->get_value( 'bpcft_enable_on_sdm_download' );
		if ( $bpcft_enable_on_sdm_download ) {
			add_filter( 'sdm_before_download_button', array( $this, 'render_sdm_download_form_cft' ), 10, 3 );
			add_action( 'sdm_download_via_direct_post', array( $this, 'check_download_request' ) );

            // For hidden downloads.
            add_action( 'sdm_hd_process_download_request', array( $this, 'check_download_request' ) );
		}

		$bpcft_enable_on_sdm_sf = $this->settings->get_value( 'bpcft_enable_on_sdm_sf' );
		if ( $bpcft_enable_on_sdm_sf ) {
			add_filter( 'sdm_sf_before_download_button', array( $this, 'render_sdm_sf_download_form_cft' ), 10, 3 );
			add_action( 'sdm_sf_download_form_submitted', array( $this, 'check_download_request' ) );
		}
	}

	/**
     * For core and hidden downloads form.
	 */
	public function render_sdm_download_form_cft($output, $id, $args ) {
		wp_enqueue_script( 'bpcft-script-sdm', BPCFT_URL . '/js/bpcft-script-sdm.js' , array( 'bpcft-common-script' ), BPCFT_VERSION, array(
			'strategy'  => 'defer',
			'in_footer' => true,
		) );

		$cft_unique_id = 'sdm' . wp_rand();

        $dl_specific_cft_class_name = "bpcft-sdm-dl-" . $id; // This unique class will be used in js to detect desired cft response field.

        $dl_specific_cft_callback_name = "bpcft_sdm_callback" . $cft_unique_id;

        $class = '';
        $class .= $dl_specific_cft_class_name;

        $fancy = isset($args['fancy']) ? intval($args['fancy']) : '';
        $addon = isset($args['addon']) ? $args['addon'] : '';

        // Check if sdm squeeze form addon.
        if ($addon == 'sdm-sf') {
            if (in_array($fancy, array(0, 1, 4, 5))){
                $class .= ' bpcft-place-widget-center';
                $class .= ' bpcft-widget-mt-12';
            }

            if (in_array($fancy, array(0, 1))) {
                $class .= ' bpcft-small-widget-size';
            }
        }

        // Check if core sdm plugin.
        if ( empty($addon) && $fancy == 2 ){
            // Force display compact widget size for core plugins fancy 2 display
            $this->turnstile->add_settings_override('widget_size', 'compact');

	        $class .= ' bpcft-place-widget-center';
        }

        // Registering a unique callback function for each download item. NOTE: This unique function is a wrapper for 'bpcft_sdm_process_cft_response' function which is the main cft response processor function.
		$inline_js = 'window["'.esc_js($dl_specific_cft_callback_name).'"]=function(token){bpcft_sdm_process_cft_response(token,"'.esc_js($cft_unique_id).'")}';
		wp_add_inline_script('bpcft-script-sdm', $inline_js);

		$output = '';
		ob_start();
		$this->turnstile->render_implicit($dl_specific_cft_callback_name, 'sdm-download-'. $id, $cft_unique_id, $class );
		$output .= ob_get_clean();

        // Clear settings overrides to it doesn't affect next render.
        $this->turnstile->clear_settings_overrides();

		return $output;
	}

	/**
	 * For squeeze forms
	 */
	public function render_sdm_sf_download_form_cft($output, $id, $args ) {
        if (!is_array($args)) {
            $args = array();
        }
        $args['addon'] = 'sdm-sf';

        return $this->render_sdm_download_form_cft($output, $id, $args);
    }

	public function check_download_request( ) {
		// Check Turnstile
		$result = $this->turnstile->check_cft_token_response();

		$success       = isset( $result['success'] ) ? boolval( $result['success'] ) : false;
		$error_message = isset( $result['error_message'] ) ? $result['error_message'] : '';

		// Send error response if failed.
		if ( empty($success) ) {
			$error_msg = __( 'Cloudflare turnstile error: ', 'bot-protection-turnstile' );
			$error_msg .= BPCFT_Utils::failed_message( $error_message );

			wp_die(esc_attr($error_msg));
		}
	}
}