<?php
/**
 * Plugin Name: ArrayCodes Order Notifications for WooCommerce
 * Plugin URI: https://wordpress.org/plugins/arraycodes-order-notifications-woocommerce/
 * Description: Efficiently enhance order management and customer communication by sending WhatsApp notifications for new WooCommerce orders.
 * Version: 1.0.0
 * Requires at least: 6.2
 * Tested up to: 6.9
 * Requires PHP: 7.4
 * Author: Array.codes
 * Author URI:https://array.codes/
 * Developer: Heitor Sousa
 * Developer URI: https://array.codes/
 * Domain Path: /languages
 * Text Domain: arraycodes-order-notifications-woocommerce
 * Requires Plugins: woocommerce
 * *
 * WC requires at least: 4.8.0
 * WC tested up to: 10.6.2
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package Notifications with WhatsApp
*/

use ArraycodesOrderNotifications\Activate;
use ArraycodesOrderNotifications\Deactivate;
use ArraycodesOrderNotifications\Init;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

if ( ! class_exists( 'Activate' ) ) :
	/**
	 * Activate function.
	 */
	function noifications_with_whats_free_activate() {
		Activate::activate();
	}
	register_activation_hook( __FILE__, 'noifications_with_whats_free_activate' );
endif;

if ( ! class_exists( 'Deactivate' ) ) :
	/**
	 * Deactivate function.
	 */
	function noifications_with_whats_free_activate_deactivate() {
		Deactivate::deactivate();
	}
	register_deactivation_hook( __FILE__, 'noifications_with_whats_free_activate_deactivate' );
endif;

if ( ! class_exists( 'Init' ) ) :
	Init::instance();
endif;
