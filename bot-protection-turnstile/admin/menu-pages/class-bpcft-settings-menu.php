<?php

class BPCFT_Settings_Menu extends BPCFT_Admin_Menu {
	public $menu_page_slug = BPCFT_MAIN_MENU_SLUG;

	/* Specify all the tabs of this menu in the following array */
	public $menu_tabs = array( 'tab1' => 'Cloudflare Turnstile Settings' );

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
		echo '<h1>' . esc_attr__( 'Bot Protection with Turnstile Settings', 'bot-protection-turnstile' ) . '</h1>';
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
			case $tab_keys[0]:
			default :
				$this->cft_api_settings_postbox_content();
				$this->cft_display_settings_postbox_content();
				break;
		}

		echo '</div></div>'; //end poststuff and post-body
		echo '</div>'; //<!-- end or wrap -->
	}

	public function cft_api_settings_postbox_content() {

		$settings = BPCFT_Config::get_instance();
		if ( isset( $_POST['bpcft_api_settings_submit'] ) && check_admin_referer( 'bpcft_api_settings_nonce' ) ) {
            $bpcft_site_key = isset($_POST['bpcft_site_key']) ? sanitize_text_field( wp_unslash($_POST['bpcft_site_key'])) : '';
			$settings->set_value( 'bpcft_site_key', $bpcft_site_key );
            $bpcft_secret_key = isset($_POST['bpcft_secret_key']) ? sanitize_text_field( wp_unslash($_POST['bpcft_secret_key'])) : '';
			$settings->set_value( 'bpcft_secret_key', $bpcft_secret_key );

			$settings->save_config();

			echo '<div class="notice notice-success"><p>' . esc_attr__( 'Settings saved.', 'bot-protection-turnstile' ) . '</p></div>';
		}

		$bpcft_site_key   = $settings->get_value( 'bpcft_site_key' );
		$bpcft_secret_key = $settings->get_value( 'bpcft_secret_key' );

		$output = '';
		ob_start();
		?>
        <div id="bpcft-turnstile-api-settings-postbox" class="postbox">
            <h3 class="hndle"><label for="title"><?php esc_attr_e("Turnstile API Settings", 'bot-protection-turnstile' ); ?></label></h3>
            <div class="inside">
                <form action="" method="post">
                    <table class="form-table">
                        <tr>
                            <th>
                                <label><?php esc_attr_e( 'Site Key', 'bot-protection-turnstile' ); ?></label>
                            </th>
                            <td>
                                <input type="text" name="bpcft_site_key" class="bpcft-settings-text-field-cat-2"
                                       value="<?php echo esc_attr( $bpcft_site_key ); ?>" required>
                                <p class="description"><?php esc_attr_e( 'The Site Key for the Cloudflare Turnstile API. You can obtain it from your Cloudflare Turnstile dashboard.', 'bot-protection-turnstile' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label><?php esc_attr_e( 'Secret Key', 'bot-protection-turnstile' ); ?></label>
                            </th>
                            <td>
                                <input type="text" name="bpcft_secret_key" class="bpcft-settings-text-field-cat-2"
                                       value="<?php echo esc_attr( $bpcft_secret_key ); ?>" required>
                                <p class="description"><?php esc_attr_e( 'The Secret Key for the Cloudflare Turnstile API. You can obtain it from your Cloudflare Turnstile dashboard.', 'bot-protection-turnstile' ); ?></p>
                            </td>
                        </tr>
                    </table>
                    <?php wp_nonce_field( 'bpcft_api_settings_nonce' ) ?>
                    <?php submit_button( __( 'Save Changes', 'bot-protection-turnstile' ), 'primary', 'bpcft_api_settings_submit' ) ?>
                </form>
            </div>
        </div>
		<?php
	}

	public function cft_display_settings_postbox_content() {

		$settings = BPCFT_Config::get_instance();
		if ( isset( $_POST['bpcft_display_settings_submit'] ) && check_admin_referer( 'bpcft_display_settings_nonce' ) ) {
            $bpcft_theme = isset($_POST['bpcft_theme']) ? sanitize_text_field( wp_unslash($_POST['bpcft_theme'])) : '';
			$settings->set_value( 'bpcft_theme', $bpcft_theme );
            $bpcft_widget_size = isset($_POST['bpcft_widget_size']) ? sanitize_text_field( wp_unslash($_POST['bpcft_widget_size'])) : '';
			$settings->set_value( 'bpcft_widget_size', $bpcft_widget_size );
            $bpcft_custom_error_msg = isset($_POST['bpcft_custom_error_msg']) ? sanitize_text_field( wp_unslash($_POST['bpcft_custom_error_msg'])) : '';
			$settings->set_value( 'bpcft_custom_error_msg', $bpcft_custom_error_msg );

			$settings->save_config();

			echo '<div class="notice notice-success"><p>' . esc_attr__( 'Settings saved.', 'bot-protection-turnstile' ) . '</p></div>';
		}

		$bpcft_theme            = $settings->get_value( 'bpcft_theme' );
		$bpcft_widget_size      = $settings->get_value( 'bpcft_widget_size' );
		$bpcft_custom_error_msg = $settings->get_value( 'bpcft_custom_error_msg' );

		?>
        <div id="bpcft-turnstile-appearance-settings-postbox" class="postbox">
            <h3 class="hndle"><label for="title"><?php esc_attr_e("Turnstile Display and Appearance", 'bot-protection-turnstile' ); ?></label></h3>
            <div class="inside">
                <form action="" method="post">
                    <table class="form-table">
                        <tr>
                            <th>
                                <label><?php esc_attr_e( 'Theme', 'bot-protection-turnstile' ); ?></label>
                            </th>
                            <td>
                                <select name="bpcft_theme">
                                    <option value="light" <?php echo $bpcft_theme == 'light' ? 'selected' : ''; ?>><?php esc_attr_e( 'Light', 'bot-protection-turnstile' ) ?></option>
                                    <option value="dark" <?php echo $bpcft_theme == 'dark' ? 'selected' : ''; ?>><?php esc_attr_e( 'Dark', 'bot-protection-turnstile' ) ?></option>
                                    <option value="auto" <?php echo $bpcft_theme == 'auto' ? 'selected' : ''; ?>><?php esc_attr_e( 'Auto', 'bot-protection-turnstile' ) ?></option>
                                </select>
                                <p class="description"><?php esc_attr_e( 'The theme for the turnstile widget.', 'bot-protection-turnstile' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label><?php esc_attr_e( 'Widget Size', 'bot-protection-turnstile' ); ?></label>
                            </th>
                            <td>
                                <select name="bpcft_widget_size">
                                    <option value="normal" <?php echo $bpcft_widget_size == 'normal' ? 'selected' : ''; ?>><?php esc_attr_e( 'Normal (300px)', 'bot-protection-turnstile' ) ?></option>
                                    <option value="flexible" <?php echo $bpcft_widget_size == 'flexible' ? 'selected' : ''; ?>><?php esc_attr_e( 'Flexible (min 300px, max 100%)', 'bot-protection-turnstile' ) ?></option>
                                    <option value="compact" <?php echo $bpcft_widget_size == 'compact' ? 'selected' : ''; ?>><?php esc_attr_e( 'Compact (150px)', 'bot-protection-turnstile' ) ?></option>
                                </select>
                                <p class="description"><?php esc_attr_e( 'The widget display size of turnstile checkbox. NOTE: Some themes or templates may override this setting to ensure proper visibility.', 'bot-protection-turnstile' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label><?php esc_attr_e( 'Custom Error Message', 'bot-protection-turnstile' ); ?></label>
                            </th>
                            <td>
                                <input type="text" name="bpcft_custom_error_msg" class="bpcft-settings-text-field-cat-2"
                                       value="<?php echo esc_attr( $bpcft_custom_error_msg ); ?>">
                                <p class="description"><?php esc_attr_e( 'Shown if the form is submitted without completing the Turnstile challenge. Leave blank to use the default.', 'bot-protection-turnstile' ); ?></p>
                            </td>
                        </tr>
                    </table>
                    <?php wp_nonce_field( 'bpcft_display_settings_nonce' ) ?>
                    <?php submit_button( __( 'Save Changes', 'bot-protection-turnstile' ), 'primary', 'bpcft_display_settings_submit' ) ?>
                </form>
            </div>
        </div>
		<?php
	}
} //end class