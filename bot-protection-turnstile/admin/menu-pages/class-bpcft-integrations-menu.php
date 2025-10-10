<?php

class BPCFT_Integrations_Menu extends BPCFT_Admin_Menu {
	public $menu_page_slug = BPCFT_INTEGRATIONS_MENU_SLUG;

	/* Specify all the tabs of this menu in the following array */
	public $menu_tabs = array( 'tab1' => 'Simple Download Monitor', 'tab2' => 'WP eMember' );

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
		echo '<h1>' . esc_attr__( 'Plugin Integration Settings', 'bot-protection-turnstile' ) . '</h1>';
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
				$this->eMember_integration_settings_postbox_content();
				break;
			case $tab_keys[0]:
			default:
                $this->sdm_integration_settings_postbox_content();
				break;
		}

		echo '</div></div>'; //end poststuff and post-body
		echo '</div>'; //<!-- end or wrap -->
	}

	public function sdm_integration_settings_postbox_content() {
		$settings = BPCFT_Config::get_instance();
		if ( isset( $_POST['bpcft_sdm_settings_submit'] ) && check_admin_referer( 'bpcft_sdm_settings_nonce' ) ) {
			$settings->set_value( 'bpcft_enable_on_sdm_download', ( isset( $_POST['bpcft_enable_on_sdm_download'] ) ? 'checked="checked"' : '' ) );
			$settings->set_value( 'bpcft_enable_on_sdm_sf', ( isset( $_POST['bpcft_enable_on_sdm_sf'] ) ? 'checked="checked"' : '' ) );

			$settings->save_config();

			echo '<div class="notice notice-success"><p>' . esc_attr__( 'Settings saved.', 'bot-protection-turnstile' ) . '</p></div>';
		}

		$bpcft_enable_on_sdm_download = $settings->get_value( 'bpcft_enable_on_sdm_download' );
		$bpcft_enable_on_sdm_sf = $settings->get_value( 'bpcft_enable_on_sdm_sf' );

		$sdm_plugin_captcha_enabled_val = $this->get_sdm_plugin_captcha_enabled_val()

		?>
        <div id="bpcft-sdm-integration-settings-postbox" class="postbox">
            <h3 class="hndle"><label for="title"><?php esc_attr_e("Turnstile Protection", 'bot-protection-turnstile' ); ?></label></h3>
            <div class="inside">

	            <?php if (! BPCFT_Utils::check_if_plugin_active( 'simple-download-monitor/main.php' )) { ?>
                    <div class="bpcft-grey-box">
			            <?php esc_html_e( 'Simple Download Monitor is not active on your site. Please activate it to use this integration.', 'bot-protection-turnstile' )?>
                    </div>
	            <?php } ?>

	            <?php if (!empty($sdm_plugin_captcha_enabled_val)) { ?>
                    <div class="bpcft-yellow-box">
                        <strong><?php esc_attr_e('Note: ', 'bot-protection-turnstile'); ?></strong><?php echo esc_attr(sprintf( __("The '%s' option is already enabled in the main Simple Download Monitor plugin. Please disable it before using the Turnstile CAPTCHA.", 'bot-protection-turnstile'), $sdm_plugin_captcha_enabled_val)); ?>
                    </div>
	            <?php } ?>

                <form action="" method="post">
                <table class="form-table">
                    <tr>
                        <th>
                            <label><?php esc_attr_e( 'Download Form', 'bot-protection-turnstile' ); ?></label>
                        </th>
                        <td>
                            <input type="checkbox"
                                   name="bpcft_enable_on_sdm_download" <?php echo esc_attr( $bpcft_enable_on_sdm_download ); ?>
                                   value="1">
                            <p class="description"><?php esc_attr_e( 'Enable turnstile CAPTCHA on the download forms of simple download monitor core plugin.', 'bot-protection-turnstile' ); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th>
                            <label><?php esc_attr_e( 'Squeeze Form', 'bot-protection-turnstile' ); ?></label>
                        </th>
                        <td>
                            <input type="checkbox"
                                   name="bpcft_enable_on_sdm_sf" <?php echo esc_attr( $bpcft_enable_on_sdm_sf ); ?>
                                   value="1">
                            <p class="description"><?php esc_attr_e( 'Enable turnstile CAPTCHA on the download forms of squeeze form addon.', 'bot-protection-turnstile' ); ?></p>
                        </td>
                    </tr>
                </table>
                <?php wp_nonce_field( 'bpcft_sdm_settings_nonce' ) ?>
                <?php submit_button( __( 'Save Changes', 'bot-protection-turnstile' ), 'primary', 'bpcft_sdm_settings_submit' ) ?>
            </form>
            </div>
        </div>
		<?php
	}

	public function eMember_integration_settings_postbox_content() {
		$settings = BPCFT_Config::get_instance();
		if ( isset( $_POST['bpcft_emember_settings_submit'] ) && check_admin_referer( 'bpcft_emember_settings_nonce' ) ) {
			$settings->set_value( 'bpcft_enable_on_emember_login_form', ( isset( $_POST['bpcft_enable_on_emember_login_form'] ) ? 'checked="checked"' : '' ) );
			$settings->set_value( 'bpcft_enable_on_emember_reg_form', ( isset( $_POST['bpcft_enable_on_emember_reg_form'] ) ? 'checked="checked"' : '' ) );
			$settings->set_value( 'bpcft_enable_on_emember_pass_reset_form', ( isset( $_POST['bpcft_enable_on_emember_pass_reset_form'] ) ? 'checked="checked"' : '' ) );

			$settings->save_config();

			echo '<div class="notice notice-success"><p>' . esc_attr__( 'Settings saved.', 'bot-protection-turnstile' ) . '</p></div>';
		}

		$bpcft_enable_on_emember_login_form = $settings->get_value( 'bpcft_enable_on_emember_login_form' );
		$bpcft_enable_on_emember_reg_form = $settings->get_value( 'bpcft_enable_on_emember_reg_form' );
		$bpcft_enable_on_emember_pass_reset_form = $settings->get_value( 'bpcft_enable_on_emember_pass_reset_form' );

		$eMember_plugin_captcha_enabled_val = $this->get_eMember_plugin_captcha_enabled_val()

		?>
        <div id="bpcft-emember-integration-settings-postbox" class="postbox">
            <h3 class="hndle"><label for="title"><?php esc_attr_e("Turnstile Protection", 'bot-protection-turnstile' ); ?></label></h3>
            <div class="inside">
				<?php
				_e('<p class="description">This integration is for the <a href="https://www.tipsandtricks-hq.com/wordpress-emember-easy-to-use-wordpress-membership-plugin-1706" target="_blank">WP eMember</a> plugin</p>', 'bot-protection-turnstile');
				?>

	            <?php if (! BPCFT_Utils::check_if_plugin_active( 'wp-eMember/wp_eMember.php' )) { ?>
                    <div class="bpcft-grey-box">
			            <?php esc_html_e( 'WP eMember plugin is not active on your site. Please activate it to use this integration.', 'bot-protection-turnstile' )?>
                    </div>
	            <?php } ?>

	            <?php if (!empty($eMember_plugin_captcha_enabled_val)) { ?>
                    <div class="bpcft-yellow-box">
                        <strong><?php esc_attr_e('Note: ', 'bot-protection-turnstile'); ?></strong><?php echo esc_attr(sprintf( __("The '%s' option is already enabled in the main WP eMember plugin. Please disable it before using the Turnstile CAPTCHA.", 'bot-protection-turnstile'), $eMember_plugin_captcha_enabled_val)); ?>
                    </div>
	            <?php } ?>

                <form action="" method="post">
                <table class="form-table">
                    <tr>
                        <th>
                            <label><?php esc_attr_e( 'WP eMember Login Form', 'bot-protection-turnstile' ); ?></label>
                        </th>
                        <td>
                            <input type="checkbox"
                                   name="bpcft_enable_on_emember_login_form" <?php echo esc_attr( $bpcft_enable_on_emember_login_form ); ?>
                                   value="1">
                            <p class="description"><?php esc_attr_e( 'Enable turnstile CAPTCHA on the login form of WP eMember plugin.', 'bot-protection-turnstile' ); ?></p>
                        </td>
                    </tr>
					<tr>
                        <th>
                            <label><?php esc_attr_e( 'WP eMember Registration Form', 'bot-protection-turnstile' ); ?></label>
                        </th>
                        <td>
                            <input type="checkbox"
                                   name="bpcft_enable_on_emember_reg_form" <?php echo esc_attr( $bpcft_enable_on_emember_reg_form ); ?>
                                   value="1">
                            <p class="description"><?php esc_attr_e( 'Enable turnstile CAPTCHA on the registration form of WP eMember plugin.', 'bot-protection-turnstile' ); ?></p>
                        </td>
                    </tr>
					<tr>
                        <th>
                            <label><?php esc_attr_e( 'WP eMember Password Reset Form', 'bot-protection-turnstile' ); ?></label>
                        </th>
                        <td>
                            <input type="checkbox"
                                   name="bpcft_enable_on_emember_pass_reset_form" <?php echo esc_attr( $bpcft_enable_on_emember_pass_reset_form ); ?>
                                   value="1">
                            <p class="description"><?php esc_attr_e( 'Enable turnstile CAPTCHA on the password reset form of WP eMember plugin.', 'bot-protection-turnstile' ); ?></p>
                        </td>
                    </tr>

                </table>
                <?php wp_nonce_field( 'bpcft_emember_settings_nonce' ) ?>
                <?php submit_button( __( 'Save Changes', 'bot-protection-turnstile' ), 'primary', 'bpcft_emember_settings_submit' ) ?>
            </form>
            </div>
        </div>
		<?php
	}

	public function get_sdm_plugin_captcha_enabled_val(){
		$settings =  get_option( 'sdm_advanced_options', array());

		$captcha_name = '';
		if ( isset($settings['recaptcha_v3_enable']) && !empty($settings['recaptcha_v3_enable']) ){
			$captcha_name = 'Google reCaptcha v3';
		} elseif ( isset($settings['recaptcha_enable']) && !empty($settings['recaptcha_enable']) ) {
			$captcha_name = 'Google reCaptcha v2';
		}
		
		return $captcha_name;
	}
	
	public function get_eMember_plugin_captcha_enabled_val(){
		$captcha_name = '';
		if (class_exists('Emember_Config')) {
			$emember_config = Emember_Config::getInstance();
			$enable_recaptcha = $emember_config->getValue('emember_enable_recaptcha');
			if (!empty($enable_recaptcha)) {
				$captcha_name = 'Google reCAPTCHA';
			}
		}

		return $captcha_name;
	}

} //end class