<?php

/*
 * Inits the admin dashboard side of things.
 * Main admin file which loads all settings panels and sets up admin menus.
 */

class BPCFT_Admin_Init {
	public $main_menu_page;
	public $dashboard_menu;
	public $settings_menu;

	public function __construct() {
		$this->admin_includes();
		add_action( 'admin_print_scripts', array( $this, 'admin_menu_page_scripts' ) );
		add_action( 'admin_print_styles', array( $this, 'admin_menu_page_styles' ) );
		add_action( 'admin_menu', array( $this, 'create_admin_menus' ) );
	}

	public function admin_includes() {
		include_once BPCFT_PATH . '/admin/menu-pages/class-bpcft-admin-menu.php';
	}

	public function admin_menu_page_scripts() {
		//make sure we are on the appropriate menu page
		if ( isset( $_GET['page'] ) && strpos( sanitize_text_field(wp_unslash($_GET['page'])), BPCFT_MENU_SLUG_PREFIX ) !== false ) {
			//wp_enqueue_script('postbox');
			//wp_enqueue_script('dashboard');
			//wp_enqueue_script('thickbox');
			//wp_enqueue_script('media-upload');
		}
	}

	public function admin_menu_page_styles() {
		//make sure we are on the appropriate menu page
		if ( isset( $_GET['page'] ) && strpos( sanitize_text_field(wp_unslash($_GET['page'])), BPCFT_MENU_SLUG_PREFIX ) !== false ) {
			wp_enqueue_style( 'bpcft-admin-css', BPCFT_URL . '/css/bpcft-admin-styles.css', array(), BPCFT_VERSION );
		}
	}

	public function create_admin_menus() {
		//Specify the menu icon.
		$menu_icon = 'dashicons-shield';
		//$menu_icon = BPCFT_URL . '/images/plugin-icon.png';
		
		//Add the main menu page
		$this->main_menu_page = add_menu_page( __( 'Bot Protection with Turnstile', 'bot-protection-turnstile' ), __( 'Bot Protection with Turnstile', 'bot-protection-turnstile' ), BPCFT_MANAGEMENT_PERMISSION, BPCFT_MAIN_MENU_SLUG, array( $this, 'handle_settings_menu_rendering' ), $menu_icon );
		add_submenu_page( BPCFT_MAIN_MENU_SLUG, __( 'Settings', 'bot-protection-turnstile' ), __( 'Settings', 'bot-protection-turnstile' ), BPCFT_MANAGEMENT_PERMISSION, BPCFT_MAIN_MENU_SLUG, array( $this, 'handle_settings_menu_rendering') );
		add_submenu_page( BPCFT_MAIN_MENU_SLUG, __( 'WordPress Forms', 'bot-protection-turnstile' ), __( 'WordPress Forms', 'bot-protection-turnstile' ), BPCFT_MANAGEMENT_PERMISSION, BPCFT_WORDPRESS_FORMS_MENU_SLUG, array( $this, 'handle_wordpress_menu_rendering' ) );
		add_submenu_page( BPCFT_MAIN_MENU_SLUG, __( 'eCommerce Integrations', 'bot-protection-turnstile' ), __( 'eCommerce Integrations', 'bot-protection-turnstile' ), BPCFT_MANAGEMENT_PERMISSION, BPCFT_ECOMMERCE_MENU_SLUG, array( $this, 'handle_ecommerce_menu_rendering' ) );
		add_submenu_page( BPCFT_MAIN_MENU_SLUG, __( 'Forum Integrations', 'bot-protection-turnstile' ), __( 'Forum Integrations', 'bot-protection-turnstile' ), BPCFT_MANAGEMENT_PERMISSION, BPCFT_FORUMS_MENU_SLUG, array( $this, 'handle_forums_menu_rendering' ) );
		add_submenu_page( BPCFT_MAIN_MENU_SLUG, __( 'Form Plugin Integrations', 'bot-protection-turnstile' ), __( 'Form Plugin Integrations', 'bot-protection-turnstile' ), BPCFT_MANAGEMENT_PERMISSION, BPCFT_FORM_PLUGINS_INTEGRATIONS_MENU_SLUG, array( $this, 'handle_forms_integrations_menu_rendering') );
		add_submenu_page( BPCFT_MAIN_MENU_SLUG, __( 'Plugin Integrations', 'bot-protection-turnstile' ), __( 'Plugin Integrations', 'bot-protection-turnstile' ), BPCFT_MANAGEMENT_PERMISSION, BPCFT_INTEGRATIONS_MENU_SLUG, array( $this, 'handle_integrations_menu_rendering') );

		//Trigger after menu creation action hook.
		do_action( 'bpcft_admin_menu_created' );
	}

	public function handle_settings_menu_rendering() {
		include_once BPCFT_PATH . '/admin/menu-pages/class-bpcft-settings-menu.php';
		$this->settings_menu = new BPCFT_Settings_Menu();
	}

	public function handle_wordpress_menu_rendering() {
		include_once BPCFT_PATH . '/admin/menu-pages/class-bpcft-wordpress-forms-menu.php';
		$this->dashboard_menu = new BPCFT_WordPress_Forms_Menu();
	}

	public function handle_ecommerce_menu_rendering() {
		include_once BPCFT_PATH . '/admin/menu-pages/class-bpcft-ecommerce-menu.php';
		$this->dashboard_menu = new BPCFT_Ecommerce_Menu();
	}

	public function handle_forums_menu_rendering() {
		include_once BPCFT_PATH . '/admin/menu-pages/class-bpcft-forums-menu.php';
		$this->dashboard_menu = new BPCFT_Forums_Menu();
	}

	public function handle_forms_integrations_menu_rendering() {
		include_once BPCFT_PATH . '/admin/menu-pages/class-bpcft-form-plugins-menu.php';
		$this->dashboard_menu = new BPCFT_Form_Plugins_Menu();
	}

	public function handle_integrations_menu_rendering() {
		include_once BPCFT_PATH . '/admin/menu-pages/class-bpcft-integrations-menu.php';
		$this->dashboard_menu = new BPCFT_Integrations_Menu();
	}
}//End of class
