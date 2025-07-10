<?php

class BPCFT_CF7_Integration {

	public BPCFT_Turnstile $turnstile;

	public BPCFT_Config $settings;

	const BPCFT_CF7_TAG_NAME = 'bot_protection_turnstile';
	public $error_message = '';

	public function __construct() {
		$this->turnstile = BPCFT_Turnstile::get_instance();
		$this->settings = BPCFT_Config::get_instance();

		add_action( 'wpcf7_init', array( $this, 'register_cf7_bpcft_form_tag' ) );
		add_action( 'wpcf7_admin_init', array( $this, 'cf7_bpcft_tag_insert_button' ), 999 );

		$bpcft_enable_on_cf7_forms = $this->settings->get_value( 'bpcft_enable_on_cf7_forms' );
		if ( $bpcft_enable_on_cf7_forms ) {
            // For all forms
			add_filter( 'wpcf7_form_elements', array( $this, 'render_cf7_cft' ) );
            add_filter( 'wpcf7_validate', array( $this, 'check_cft_response' ), 999 );
		} else {
            // For specific forms, bpcft widget is rendered via the cf7 form tag by the user. Only validation need to be done.
            add_filter( 'wpcf7_validate_' . self::BPCFT_CF7_TAG_NAME, array( $this, 'check_cft_response_by_tag' ), 10, 2 );
		}
	}

	/**
	 * Returns the necessary HTML markups for turnstile widget.
	 */
	public function cf7_bpcft_content() {
		wp_enqueue_script( 'bpcft-script-cf7', BPCFT_URL . '/js/bpcft-script-cf7.js', array( 'cloudflare-turnstile-script' ), BPCFT_VERSION );

		$unique_id = wp_rand();

		ob_start();
		$this->turnstile->render_implicit( 'bpcft_callback', 'cf7-form', $unique_id );

		return ob_get_clean();
	}

	/**
	 * This function is used to add turnstile widget to add cf7 forms.
	 */
	public function render_cf7_cft( $content ) {
		// Add cft widget if not added yet.
        $bpcft_div = 'bp-cf-turnstile-div';
		if ( strpos( $content, $bpcft_div ) === false ) {
			return preg_replace( '/(<input[^>]*type="submit")/i', $this->cf7_bpcft_content() . '<br/>$1', $content );
		}

		return $content;
	}

    public function check_cft_response($wpcf7_result) {
		if (!class_exists('WPCF7_Submission')) {
			return $wpcf7_result;
		}

		$post = \WPCF7_Submission::get_instance();

		if (!empty($post)) {
            $tag = array('type'=> self::BPCFT_CF7_TAG_NAME, 'name' => 'bpcft');
            return $this->check_cft_response_by_tag($wpcf7_result, $tag );
		}

        return $wpcf7_result;
    }

	/**
	 * Doc: https://contactform7.com/2015/03/28/custom-validation/
	 */
	public function check_cft_response_by_tag( $wpcf7_result, $tag ) {
		$result = $this->turnstile->check_cft_token_response();

		$success       = isset( $result['success'] ) ? boolval( $result['success'] ) : false;
		$error_message = isset( $result['error_message'] ) ? $result['error_message'] : '';

		if ( empty( $success ) ) {
            $this->error_message = BPCFT_Utils::failed_message($error_message);
            $wpcf7_result->invalidate( $tag, $this->error_message );

			add_filter('wpcf7_display_message', array($this, 'filter_cf7_validation_msg'));
		}

		return $wpcf7_result;
	}

    public function filter_cf7_validation_msg($message) {
        if (empty($this->error_message)){
            return $message;
        }

	    $msg = $this->error_message;

        $this->error_message = ''; // Clear error msg.

        return $msg;
    }

	/**
	 * Registers a custom form tag for bpcft widget.
	 * Custom tag: [bpcft-cf7-turnstile]
	 * Placing this tag inside the form (using the edit form interface of contact form 7), it renders the turnstile widget.
	 *
	 * Doc: https://contactform7.com/2015/01/10/adding-a-custom-form-tag/
	 */
	public function register_cf7_bpcft_form_tag() {
		if ( function_exists( 'wpcf7_add_form_tag' ) ) {
			wpcf7_add_form_tag( self::BPCFT_CF7_TAG_NAME, array( $this, 'cf7_bpcft_content' ), array( 'name-attr' => true ));
		}
	}

	public function cf7_bpcft_tag_insert_button() {
		if ( class_exists( 'WPCF7_TagGenerator' ) ) {
			$tag_generator = WPCF7_TagGenerator::get_instance();
			$tag_generator->add( 'bpcft', esc_html__( 'Bot Protection Turnstile', 'bot-protection-turnstile' ), array(
				$this,
				'cf7_bpcft_tag_inserter'
			), array( 'version' => 2 ) );
		}
	}

	public function cf7_bpcft_tag_inserter() {
		?>
        <header class="description-box">
            <h3><?php esc_html_e( 'Bot Protection Turnstile form-tag generator', 'bot-protection-turnstile' ); ?></h3>

            <p><?php esc_html_e( 'Generates a form-tag for a bot protection turnstile widget field.', 'bot-protection-turnstile' ); ?></p>
        </header>
        <footer class="insert-box">
            <div class="flex-container">
                <input type="text"
                       value="<?php echo '[' . self::BPCFT_CF7_TAG_NAME . ' bpcft]' ?>"
                       class="tag code"
                       style="width: 100%"
                       readonly="readonly"
                       onfocus="this.select()"
                />
                <input type="button" class="button button-primary insert-tag"
                       value="<?php echo esc_attr( __( 'Insert Tag', 'contact-form-7' ) ); ?>"/>
            </div>
        </footer>
		<?php
	}
}