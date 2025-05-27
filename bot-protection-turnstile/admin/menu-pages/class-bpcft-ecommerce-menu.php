<?php

class BPCFT_Ecommerce_Menu extends BPCFT_Admin_Menu {
	public $menu_page_slug = BPCFT_ECOMMERCE_MENU_SLUG;

	/* Specify all the tabs of this menu in the following array */
	public $menu_tabs = array( 'tab1' => 'Accept Stripe Payments', 'tab2' => 'WooCommerce' );

	public function __construct() {
		$this->render_settings_menu_page();
	}

	public function get_current_tab() {
		//Get the current tab (if any), otherwise default to the first tab.
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'tab1';

		return $tab;
	}

	/*
	 * Renders our tabs of this menu as nav items
	 */
	public function render_menu_tabs() {
		$current_tab = $this->get_current_tab();

		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $this->menu_tabs as $tab_key => $tab_caption ) {
			$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
			echo '<a class="nav-tab ' . esc_attr($active) . '" href="?page=' . esc_attr($this->menu_page_slug) . '&tab=' . esc_attr($tab_key) . '">' . esc_attr($tab_caption) . '</a>';
		}
		echo '</h2>';
	}

	/*
	 * The menu rendering goes here
	 */
	public function render_settings_menu_page() {
		echo '<div class="wrap">';
		echo '<h1>' . esc_attr__( 'eCommerce Integration Settings', 'bot-protection-turnstile' ) . '</h1>';
		//Get the current tab
		$tab = $this->get_current_tab();

		//Render the menu tab before poststuff (for the menu tabs to be correctly rendered without CSS issue)
		$this->render_menu_tabs();

		//Post stuff and body
		echo '<div id="poststuff"><div id="post-body">';

		/* translators: documentation link */
		echo '<div class="bpcft-grey-box">' . sprintf( esc_html__( 'See the %s for usage instructions.', 'bot-protection-turnstile' ), '<a href="https://www.tipsandtricks-hq.com/bot-protection-with-turnstile-plugin" target="_blank">' . esc_attr__( 'plugin documentation', 'bot-protection-turnstile' ) . '</a>' ) . '</div>';

		//Switch based on the current tab
		$tab_keys = array_keys( $this->menu_tabs );
		switch ( $tab ) {
			case $tab_keys[1]:
				$this->woocommerce_integration_settings_postbox_content();
				break;
			case $tab_keys[0]:
			default:
				$this->asp_integration_settings_postbox_content();
				break;
		}

		echo '</div></div>'; //end poststuff and post-body
		echo '</div>'; //<!-- end or wrap -->
	}

	public function asp_integration_settings_postbox_content() {

		$settings = BPCFT_Config::get_instance();
		if ( isset( $_POST['bpcft_asp_settings_submit'] ) && check_admin_referer( 'bpcft_asp_settings_nonce' ) ) {
			$settings->set_value( 'bpcft_enable_on_asp_checkout', ( isset( $_POST['bpcft_enable_on_asp_checkout'] ) ? 'checked="checked"' : '' ) );

			$settings->save_config();

			echo '<div class="notice notice-success"><p>' . esc_attr__( 'Settings saved.', 'bot-protection-turnstile' ) . '</p></div>';
		}

		$bpcft_enable_on_asp_checkout = $settings->get_value( 'bpcft_enable_on_asp_checkout' );

		$asp_plugin_captcha_enabled_val = $this->get_asp_plugin_captcha_enabled_val();

		?>
        <div id="bpcft-asp-integration-settings-postbox" class="postbox">
            <h3 class="hndle"><label for="title"><?php esc_attr_e("Turnstile Protection", 'bot-protection-turnstile' ); ?></label></h3>
            <div class="inside">

	            <?php if (! BPCFT_Utils::check_if_plugin_active( 'stripe-payments/accept-stripe-payments.php' )) { ?>
                    <div class="bpcft-grey-box">
			            <?php esc_html_e( 'Accept Stripe Payments is not active on your site. Please activate it to use this integration.', 'bot-protection-turnstile' )?>
                    </div>
	            <?php } ?>

				<?php if (!empty($asp_plugin_captcha_enabled_val)) { ?>
                    <div class="bpcft-yellow-box">
                        <strong><?php esc_attr_e('Note: ', 'bot-protection-turnstile'); ?></strong><?php echo esc_attr(sprintf( __("The '%s' option is already enabled in the main Accept Stripe Payments plugin. Please disable it before using the Turnstile CAPTCHA.", 'bot-protection-turnstile'), $asp_plugin_captcha_enabled_val)); ?>
                    </div>
				<?php } ?>

                <form action="" method="post">
                    <table class="form-table">
                        <tr>
                            <th>
                                <label><?php esc_attr_e( 'Payment/Checkout Form', 'bot-protection-turnstile' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox"
                                       name="bpcft_enable_on_asp_checkout" <?php echo esc_attr( $bpcft_enable_on_asp_checkout ); ?>
                                       value="1">
                                <p class="description"><?php esc_attr_e( 'Enable turnstile CAPTCHA on the checkout form of stripe payments plugin.', 'bot-protection-turnstile' ); ?></p>
                            </td>
                        </tr>
                    </table>
					<?php wp_nonce_field( 'bpcft_asp_settings_nonce' ) ?>
					<?php submit_button( __( 'Save Changes', 'bot-protection-turnstile' ), 'primary', 'bpcft_asp_settings_submit' ) ?>
                </form>
            </div>
        </div>
		<?php
	}

	public function get_asp_plugin_captcha_enabled_val(){
		$settings =  get_option( 'AcceptStripePayments-settings', array());
		$captcha_type = isset($settings['captcha_type']) && !empty($settings['captcha_type']) ? sanitize_text_field($settings['captcha_type']) : '';
		$captcha_map = array(
			'recaptcha' => 'Google reCAPTCHA',
			'eprecaptcha' => 'Google Enterprise reCAPTCHA',
			'hcaptcha' => 'hCaptcha',
		);
		$captcha_name = array_key_exists($captcha_type, $captcha_map) ? $captcha_map[$captcha_type] : $captcha_type;

		return $captcha_name;
	}

	public function woocommerce_integration_settings_postbox_content() {

		$settings = BPCFT_Config::get_instance();
		if ( isset( $_POST['bpcft_woo_settings_submit'] ) && check_admin_referer( 'bpcft_woo_settings_nonce' ) ) {
			$settings->set_value( 'bpcft_enable_on_woo_login', ( isset( $_POST['bpcft_enable_on_woo_login'] ) ? 'checked="checked"' : '' ) );
			$settings->set_value( 'bpcft_enable_on_woo_registration', ( isset( $_POST['bpcft_enable_on_woo_registration'] ) ? 'checked="checked"' : '' ) );
			$settings->set_value( 'bpcft_enable_on_woo_pass_reset', ( isset( $_POST['bpcft_enable_on_woo_pass_reset'] ) ? 'checked="checked"' : '' ) );
			$settings->set_value( 'bpcft_enable_on_woo_checkout', ( isset( $_POST['bpcft_enable_on_woo_checkout'] ) ? 'checked="checked"' : '' ) );

			$settings->save_config();

			echo '<div class="notice notice-success"><p>' . esc_attr__( 'Settings saved.', 'bot-protection-turnstile' ) . '</p></div>';
		}

		$bpcft_enable_on_woo_login = $settings->get_value( 'bpcft_enable_on_woo_login' );
		$bpcft_enable_on_woo_registration = $settings->get_value( 'bpcft_enable_on_woo_registration' );
		$bpcft_enable_on_woo_pass_reset = $settings->get_value( 'bpcft_enable_on_woo_pass_reset' );
		$bpcft_enable_on_woo_checkout = $settings->get_value( 'bpcft_enable_on_woo_checkout' );
		?>
        <div id="bpcft-asp-integration-settings-postbox" class="postbox">
            <h3 class="hndle"><label for="title"><?php esc_attr_e("Turnstile Protection", 'bot-protection-turnstile' ); ?></label></h3>
            <div class="inside">

                <?php if (! BPCFT_Utils::check_if_plugin_active( 'woocommerce/woocommerce.php' )) { ?>
                    <div class="bpcft-grey-box">
                        <?php esc_html_e( 'WooCommerce is not active on your site. Please activate it to use this integration.', 'bot-protection-turnstile' )?>
                    </div>
                <?php } ?>

                <form action="" method="post">
                    <table class="form-table">
                        <tr>
                            <th>
                                <label><?php esc_attr_e( 'Woocommerce Login Form', 'bot-protection-turnstile' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox"
                                       name="bpcft_enable_on_woo_login" <?php echo esc_attr( $bpcft_enable_on_woo_login ); ?>
                                       value="1">
                                <p class="description"><?php esc_attr_e( 'Enable turnstile CAPTCHA on the login form of woocommerce plugin.', 'bot-protection-turnstile' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label><?php esc_attr_e( 'Woocommerce Register Form', 'bot-protection-turnstile' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox"
                                       name="bpcft_enable_on_woo_registration" <?php echo esc_attr( $bpcft_enable_on_woo_registration ); ?>
                                       value="1">
                                <p class="description"><?php esc_attr_e( 'Enable turnstile CAPTCHA on the registration form of woocommerce plugin.', 'bot-protection-turnstile' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label><?php esc_attr_e( 'Woocommerce Password Reset Form', 'bot-protection-turnstile' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox"
                                       name="bpcft_enable_on_woo_pass_reset" <?php echo esc_attr( $bpcft_enable_on_woo_pass_reset ); ?>
                                       value="1">
                                <p class="description"><?php esc_attr_e( 'Enable turnstile CAPTCHA on the password reset form of woocommerce plugin.', 'bot-protection-turnstile' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label><?php esc_attr_e( 'Woocommerce Checkout Form', 'bot-protection-turnstile' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox"
                                       name="bpcft_enable_on_woo_checkout" <?php echo esc_attr( $bpcft_enable_on_woo_checkout ); ?>
                                       value="1">
                                <p class="description"><?php esc_attr_e( 'Enable turnstile CAPTCHA on the checkout of woocommerce plugin.', 'bot-protection-turnstile' ); ?></p>
                            </td>
                        </tr>
                    </table>
					<?php wp_nonce_field( 'bpcft_woo_settings_nonce' ) ?>
					<?php submit_button( __( 'Save Changes', 'bot-protection-turnstile' ), 'primary', 'bpcft_woo_settings_submit' ) ?>
                </form>
            </div>
        </div>
		<?php
	}

} //end class