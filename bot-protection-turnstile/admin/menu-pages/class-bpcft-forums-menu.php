<?php

class BPCFT_Forums_Menu extends BPCFT_Admin_Menu {
	public $menu_page_slug = BPCFT_FORUMS_MENU_SLUG;

	/* Specify all the tabs of this menu in the following array */
	public $menu_tabs = array( 'tab1' => 'bbPress' );

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
		echo '<h1>' . esc_attr__( 'Forums Integration Settings', 'bot-protection-turnstile' ) . '</h1>';
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
			default:
				$this->woocommerce_integration_settings_postbox_content();
				break;
		}

		echo '</div></div>'; //end poststuff and post-body
		echo '</div>'; //<!-- end or wrap -->
	}

	public function woocommerce_integration_settings_postbox_content() {

		$settings = BPCFT_Config::get_instance();
		if ( isset( $_POST['bpcft_bbp_settings_submit'] ) && check_admin_referer( 'bpcft_bbp_settings_nonce' ) ) {
			$settings->set_value( 'bpcft_enable_on_bbp_login', ( isset( $_POST['bpcft_enable_on_bbp_login'] ) ? 'checked="checked"' : '' ) );
			$settings->set_value( 'bpcft_enable_on_bbp_registration', ( isset( $_POST['bpcft_enable_on_bbp_registration'] ) ? 'checked="checked"' : '' ) );
			$settings->set_value( 'bpcft_enable_on_bbp_pass_reset', ( isset( $_POST['bpcft_enable_on_bbp_pass_reset'] ) ? 'checked="checked"' : '' ) );
			$settings->set_value( 'bpcft_enable_on_bbp_create_topic', ( isset( $_POST['bpcft_enable_on_bbp_create_topic'] ) ? 'checked="checked"' : '' ) );
			$settings->set_value( 'bpcft_enable_on_bbp_topic_reply', ( isset( $_POST['bpcft_enable_on_bbp_topic_reply'] ) ? 'checked="checked"' : '' ) );

			$settings->save_config();

			echo '<div class="notice notice-success"><p>' . esc_attr__( 'Settings saved.', 'bot-protection-turnstile' ) . '</p></div>';
		}

		$bpcft_enable_on_bbp_login = $settings->get_value( 'bpcft_enable_on_bbp_login' );
		$bpcft_enable_on_bbp_registration = $settings->get_value( 'bpcft_enable_on_bbp_registration' );
		$bpcft_enable_on_bbp_pass_reset = $settings->get_value( 'bpcft_enable_on_bbp_pass_reset' );
		$bpcft_enable_on_bbp_create_topic = $settings->get_value( 'bpcft_enable_on_bbp_create_topic' );
		$bpcft_enable_on_bbp_topic_reply = $settings->get_value( 'bpcft_enable_on_bbp_topic_reply' );

		?>
        <div id="bpcft-asp-integration-settings-postbox" class="postbox">
            <h3 class="hndle"><label for="title"><?php esc_attr_e("Turnstile Protection", 'bot-protection-turnstile' ); ?></label></h3>
            <div class="inside">

                <?php if (! BPCFT_Utils::check_if_plugin_active( 'bbpress/bbpress.php' )) { ?>
                    <div class="bpcft-grey-box">
                        <?php esc_html_e( 'bbPress is not active on your site. Please activate it to use this integration.', 'bot-protection-turnstile' )?>
                    </div>
                <?php } ?>

                <form action="" method="post">
                    <table class="form-table">
                        <tr>
                            <th>
                                <label><?php esc_attr_e( 'bbPress Login Form', 'bot-protection-turnstile' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox"
                                       name="bpcft_enable_on_bbp_login" <?php echo esc_attr( $bpcft_enable_on_bbp_login ); ?>
                                       value="1">
                                <p class="description"><?php esc_attr_e( 'Enable turnstile CAPTCHA on the login form of bbPress plugin.', 'bot-protection-turnstile' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label><?php esc_attr_e( 'bbPress Registration Form', 'bot-protection-turnstile' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox"
                                       name="bpcft_enable_on_bbp_registration" <?php echo esc_attr( $bpcft_enable_on_bbp_registration ); ?>
                                       value="1">
                                <p class="description"><?php esc_attr_e( 'Enable turnstile CAPTCHA on the registration form of bbPress plugin.', 'bot-protection-turnstile' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label><?php esc_attr_e( 'bbPress Reset Password Form', 'bot-protection-turnstile' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox"
                                       name="bpcft_enable_on_bbp_pass_reset" <?php echo esc_attr( $bpcft_enable_on_bbp_pass_reset ); ?>
                                       value="1">
                                <p class="description"><?php esc_attr_e( 'Enable turnstile CAPTCHA on the password reset form of bbPress plugin.', 'bot-protection-turnstile' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label><?php esc_attr_e( 'bbPress Create Topic Form', 'bot-protection-turnstile' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox"
                                       name="bpcft_enable_on_bbp_create_topic" <?php echo esc_attr( $bpcft_enable_on_bbp_create_topic ); ?>
                                       value="1">
                                <p class="description"><?php esc_attr_e( 'Enable turnstile CAPTCHA on the create topic form of bbPress plugin.', 'bot-protection-turnstile' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label><?php esc_attr_e( 'bbPress Topic Reply Form', 'bot-protection-turnstile' ); ?></label>
                            </th>
                            <td>
                                <input type="checkbox"
                                       name="bpcft_enable_on_bbp_topic_reply" <?php echo esc_attr( $bpcft_enable_on_bbp_topic_reply ); ?>
                                       value="1">
                                <p class="description"><?php esc_attr_e( 'Enable turnstile CAPTCHA on the topic reply form of bbPress plugin.', 'bot-protection-turnstile' ); ?></p>
                            </td>
                        </tr>
                    </table>
					<?php wp_nonce_field( 'bpcft_bbp_settings_nonce' ) ?>
					<?php submit_button( __( 'Save Changes', 'bot-protection-turnstile' ), 'primary', 'bpcft_bbp_settings_submit' ) ?>
                </form>
            </div>
        </div>
		<?php
	}

} //end class