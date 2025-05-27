<?php
if ( ! defined( 'ABSPATH' ) ){
    //Exit if accessed directly
    exit;
}

if ( ! class_exists( 'BPCFT_Main' ) ) {

class BPCFT_Main {
	public $plugin_url;
	public $plugin_path;
	public $plugin_configs;//TODO - Does it need to be static?
	public $admin_init;
	public $debug_logger;

	public function __construct() {
		$this->define_constants();
		$this->load_configs();
		$this->includes();
		$this->initialize_and_run_classes();

		// Register action hooks.
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded_handler' ) );
		// Note: Create a separate class to handle the other init time tasks.
		add_action( 'init', array( $this, 'load_language' ) );

		// Trigger the action hook for when the constructor has finished loading.
		do_action( 'bpcft_loaded' );

		add_action( 'login_enqueue_scripts', array( $this, 'register_login_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
	}

	public function define_constants() {
		define( 'BPCFT_URL', $this->plugin_url() );
		define( 'BPCFT_PATH', $this->plugin_path() );
		define( 'BPCFT_TEXT_DOMAIN', 'bot-protection-turnstile' );
		define( 'BPCFT_MANAGEMENT_PERMISSION', 'manage_options' );
		define( 'BPCFT_MENU_SLUG_PREFIX', 'bpcft' );
		define( 'BPCFT_MAIN_MENU_SLUG', 'bpcft' );
		define( 'BPCFT_SETTINGS_MENU_SLUG', 'bpcft-settings' );
		define( 'BPCFT_WORDPRESS_FORMS_MENU_SLUG', 'bpcft-wp-forms' );
		define( 'BPCFT_INTEGRATIONS_MENU_SLUG', 'bpcft-integrations' );
		define( 'BPCFT_FORUMS_MENU_SLUG', 'bpcft-forums' );
		define( 'BPCFT_ECOMMERCE_MENU_SLUG', 'bpcft-ecommerce' );
		//global $wpdb;
		//define('DB_TABLE_TBL', $wpdb->prefix . "define_name_for_tbl");
	}

	public function plugin_url() {
		if ( $this->plugin_url ) {
			return $this->plugin_url;
		}

		return $this->plugin_url = plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) );
	}

	public function plugin_path() {
		if ( $this->plugin_path ) {
			return $this->plugin_path;
		}

		return $this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	public function load_configs() {
		include_once( BPCFT_PATH . '/classes/class-bpcft-config.php' );
		$this->plugin_configs = BPCFT_Config::get_instance();
	}

	public function includes() {
		//Load common files for everywhere
		include_once( BPCFT_PATH . '/classes/class-bpcft-utils.php' );
		include_once( BPCFT_PATH . '/classes/class-bpcft-turnstile.php' );
		include_once( BPCFT_PATH . '/classes/integrations/class-bpcft-integration-wp.php' );
		include_once( BPCFT_PATH . '/classes/integrations/class-bpcft-integration-asp.php' );
		include_once( BPCFT_PATH . '/classes/integrations/class-bpcft-integration-sdm.php' );
		include_once( BPCFT_PATH . '/classes/integrations/class-bpcft-integration-bbpress.php' );
		include_once( BPCFT_PATH . '/classes/integrations/class-bpcft-integration-woocommerce.php' );
		if ( is_admin() ) {
			//Load admin side only files
			include_once( BPCFT_PATH . '/admin/class-bpcft-admin-init.php' );
		} else {
			//Load front end side only files
		}
	}

	public function register_login_scripts(){
		BPCFT_Turnstile::register_scripts();
	}

	public function register_scripts() {
		BPCFT_Turnstile::register_scripts();
	}

	public function initialize_and_run_classes() {
		//Initialize and run classes here
		if ( is_admin() ) {
			//Do admin side operations
			$this->admin_init = new BPCFT_Admin_Init();
		}

		new BPCFT_WordPress_Integration();
		new BPCFT_ASP_Integration();
		new BPCFT_SDM_Integration();
		new BPCFT_BBpress_Integration();
		new BPCFT_WooCommerce_Integration();
	}

	public function load_language() {
		// Internationalization.
		// A better practice for text domain is to use dashes instead of underscores.
		//load_plugin_textdomain('language-text-domain', false, BPCFT_PATH . '/languages/');
	}

	public static function activate_handler() {
		// Only runs when the plugin activates - do installer tasks
		//include_once ('file-name-installer.php');
		//bpcft_run_activation();
	}

	public static function deactivate_handler() {}

	public function plugins_loaded_handler() {
		// Runs when plugins_loaded action gets fired.
		// Do any admin side plugins_loaded operations
		// if(is_admin()){
		//     $this->do_db_upgrade_check();
		// }
	}

	public function do_db_upgrade_check() {
		if ( is_admin() ) {
			//Check if DB needs to be updated
			if ( get_option( 'bpcft_db_version' ) != BPCFT_DB_VERSION ) {
				//include_once ('file-name-installer.php');
				//bpcft_run_db_upgrade();
			}
		}
	}

}//End of class

}//End of class not exists check

$GLOBALS['BPCFT_Main'] = new BPCFT_Main();
