<?php

class BPCFT_Turnstile {

	public static $instance;

	public BPCFT_Config $settings;

    public $settings_overrdies = array();

	public static function get_instance() : self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		$this->settings = BPCFT_Config::get_instance();
	}

    public static function get_cft_cdn_url() {
	    return 'https://challenges.cloudflare.com/turnstile/v0/api.js';
    }

    public static function get_cft_cdn_url_explicit() {
        return add_query_arg( array(
	        'render' => 'explicit',
            'onload' => apply_filters('bpcft_onload_cft_callback', 'bpcft_onload_cft_cdn')
        ), self::get_cft_cdn_url());
    }

    public static function get_bpcft_script_url() {
	    return BPCFT_URL . '/js/bpcft-common-script.js';
    }

    public static function get_bpcft_style_url() {
        return BPCFT_URL . '/css/bpcft-styles.css';
    }

	public static function register_scripts() {
		wp_register_script( 'bpcft-common-script', self::get_bpcft_script_url() , array(), BPCFT_VERSION );
        wp_register_script( 'cloudflare-turnstile-script', self::get_cft_cdn_url(), array('bpcft-common-script'), null, array(
			'strategy'  => 'defer',
			'in_footer' => true,
		) );
		wp_register_script( 'cloudflare-turnstile-script-explicit', self::get_cft_cdn_url_explicit(), array('bpcft-common-script'), null, array(
			'strategy'  => 'defer',
			'in_footer' => true,
		));

        // Public style
		wp_register_style( 'bpcft-styles', self::get_bpcft_style_url() , array(), BPCFT_VERSION );
	}

    public function get_widget_content( $callback = '', $form_name = '', $unique_id = '', $class = '' ){
	    $site_key    = $this->settings->get_value( 'bpcft_site_key' );
	    $secret_key    = $this->settings->get_value( 'bpcft_secret_key' );

        if (empty($site_key) || empty($secret_key)){
            return '<p class="bpcft-error-msg">'. __("Error! You have enabled the CAPTCHA option, but the necessary Turnstile API keys are missing. Please go to the settings menu and enter the API details to resolve this.", 'bot-protection-turnstile') .'</p>';
        }

	    $unique_id = !empty($unique_id) ? $unique_id : wp_rand();

        $widget_settings = $this->widget_settings();
	    $theme       = isset($widget_settings['theme']) ? $widget_settings['theme'] : 'auto';
	    $language    = isset($widget_settings['language']) ? $widget_settings['language'] : 'auto';
	    $appearance  = isset($widget_settings['appearance']) ? $widget_settings['appearance'] : 'always';
	    $widget_size = isset($widget_settings['widget_size']) ? $widget_settings['widget_size'] : 'normal';

        ob_start();
	    ?>
        <div id="cf-turnstile-<?php echo esc_attr( $unique_id ); ?>"
            class="cf-turnstile bp-cf-turnstile-div <?php echo !empty($class) ? esc_attr( $class ) : '' ?>"
            data-sitekey="<?php echo esc_attr( $site_key ); ?>"
            data-theme="<?php echo esc_attr( $theme ); ?>"
            data-language="<?php echo esc_attr( $language ); ?>"
            data-size="<?php echo esc_attr( $widget_size ); ?>"
            data-retry="auto"
            data-retry-interval="1000"
            data-action="<?php echo esc_attr( $form_name ); ?>"
            data-appearance="<?php echo esc_attr( $appearance ); ?>"
            <?php if ( !empty($callback) ) { ?>
            data-callback="<?php echo esc_attr( $callback ); ?>"
            <?php } ?>
            data-error-callback="bpcft_error_callback"
            data-expired-callback="bpcft_expired_callback"
        ></div>
	    <?php

	    return ob_get_clean();
    }

	/**
	 * Renders cloudflare turnstile widget.
	 */
    private function render($callback = '', $form_name = '', $unique_id = '', $class = '' , $widget_id = '', $is_explicit = false){
        if ($is_explicit){
	        wp_enqueue_script( 'cloudflare-turnstile-script-explicit' );
        } else {
	        wp_enqueue_script( 'cloudflare-turnstile-script' );
        }
	    wp_enqueue_style( 'bpcft-styles' );

	    $callback = sanitize_text_field($callback);
	    $form_name = sanitize_text_field($form_name);
	    $unique_id = sanitize_text_field($unique_id);
	    $class = sanitize_text_field($class);

	    do_action( "bpcft_before_cft_widget",  $unique_id );

	    $widget = $this->get_widget_content( $callback, $form_name, $unique_id, $class );

	    echo wp_kses_post($widget);

	    do_action( "bpcft_after_cft_widget", $unique_id, $widget_id );
    }

	/**
     * Renders cloudflare turnstile widget with implicit script
	 */
	public function render_implicit( $callback = '', $form_name = '', $unique_id = '', $class = '' , $widget_id = '') {
        $this->render($callback, $form_name, $unique_id, $class, $widget_id, false);
	}

	/**
     * Renders cloudflare turnstile widget with explicit script
	 */
    public function render_explicit($callback = '', $form_name = '', $unique_id = '', $class = '' , $widget_id = '') {
	    $this->render($callback, $form_name, $unique_id, $class, $widget_id, true);
    }

	/**
	 * Checks Turnstile Captcha POST is Valid
	 */
	public function check_cft_token_response( $cft_response_token = "" ) {
		$results = array();

		// Check if POST data is empty
		if ( empty( $cft_response_token ) && isset( $_REQUEST['cf-turnstile-response'] ) ) {
			$cft_response_token = sanitize_text_field( wp_unslash($_REQUEST['cf-turnstile-response']) );
		}

		// Get Turnstile Keys from Settings
		$site_key    = $this->settings->get_value( 'bpcft_site_key' );
		$secret_key = $this->settings->get_value( 'bpcft_secret_key' );

		if ( $site_key && $secret_key ) {
			$headers  = array(
				'body' => [
					'secret'   => $secret_key,
					'response' => $cft_response_token
				]
			);
			$verify   = wp_remote_post( 'https://challenges.cloudflare.com/turnstile/v0/siteverify', $headers );
			$verify   = wp_remote_retrieve_body( $verify );
			$response = json_decode( $verify );

			if ( $response->success ) {
				$results['success'] = $response->success;
			} else {
				$results['success'] = false;
			}

			foreach ( $response as $item_key => $item_value ) {
				if ( $item_key == 'error-codes' ) {
					foreach ( $item_value as $error_code ) {
						$results['error_code'] = $error_code;
						$results['error_message'] = BPCFT_Utils::error_message_by_code($error_code);
					}
				}
			}

			do_action('bpcft_after_cft_token_check', $response, $results);

			return $results;
		}

		return false;
	}

    public function widget_settings(){
        $settings = array(
            'theme'       => $this->settings->get_value( 'bpcft_theme', 'auto' ),
            'language'    => 'auto',
            'appearance'  => 'always',
            'widget_size' => $this->settings->get_value( 'bpcft_widget_size', 'normal' )
        );
        $overrides = $this->settings_overrdies;

        $out = array();
	    foreach ( $settings as $name => $default ) {
		    if ( array_key_exists( $name, $overrides ) ) {
			    $out[ $name ] = $overrides[ $name ];
		    } else {
			    $out[ $name ] = $default;
		    }
	    }

        return $out;
    }

    public function add_settings_override($name, $value){
        $this->settings_overrdies[$name] = $value;
    }

    public function clear_settings_overrides() {
	    $this->settings_overrdies = array();
    }
}
