<?php
/*
Plugin Name: Bot Protection with Turnstile
Version: 1.0.8
Plugin URI: https://www.tipsandtricks-hq.com/bot-protection-with-turnstile-plugin
Author: Tips and Tricks HQ, mra13
Author URI: https://www.tipsandtricks-hq.com/
Description: A lightweight plugin that protects core WordPress forms and selected third‑party plugins from spam and bot attacks using Cloudflare Turnstile CAPTCHA.
Text Domain: bot-protection-turnstile
License: GPLv2 or later
*/

//Prefix - bpcft_

if ( ! defined( 'ABSPATH' ) ){
    //Exit if accessed directly
    exit;
}

//Defining the version constants here allows easy updating of them all from one file when releasing new versions.
define('BPCFT_VERSION', '1.0.8'); //Plugin version
define('BPCFT_DB_VERSION', '1.0'); //DB version

//Include the main plugin class
include_once('bp-turnstile-core.php');

//Activation and deactivation hooks
register_activation_hook(__FILE__,array( 'BPCFT_Main','activate_handler'));//activation hook
register_deactivation_hook(__FILE__,array( 'BPCFT_Main','deactivate_handler'));//deactivation hook

//Add settings link in plugins listing page
function bpcft_add_settings_link( $links, $file ) {
	if ( $file == plugin_basename( __FILE__ ) ) {
		$settings_link = '<a href="admin.php?page=bpcft">Settings</a>';
		array_unshift( $links, $settings_link );
	}
	return $links;
}
add_filter( 'plugin_action_links', 'bpcft_add_settings_link', 10, 2 );
