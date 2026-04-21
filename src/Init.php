<?php
/**
 * Init Init
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications;

use ArraycodesOrderNotifications\AdminPage\AdminPageReact;
use ArraycodesOrderNotifications\Database\TableManager;
use ArraycodesOrderNotifications\InternalEndpoints\RegisterRoutes;
use ArraycodesOrderNotifications\WhatsAppMessageCenter\WhatsAppInit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Init' ) ) :
	/**
	 * Class Init
	 */
	final class Init {

		/**
		 * Const PLUGIN_PATH
		 *
		 * @const PLUGIN_PATH
		 */
		const PLUGIN_PATH = __FILE__;

		/**
		 * Instance of this class.
		 *
		 * @var self|null Instance of the class.
		*/
		protected static $instance = null;

		/**
		 * Return an instance of this class.
		 *
		 * @return self
		 */
		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Init constructor.
		 */
		private function __construct() {
			if ( ! $this->has_valid_requirements() ) {
				add_action( 'admin_notices', array( Notices::class, 'add_notice_requires_woocommerce_activated' ) );
				return;
			}
			$this->register_hooks();
			$this->register_admin_classes();
			$this->register_public_classes();
		}

		/**
		 * Register_hooks function.
		 */
		public function register_hooks() {
			add_filter( 'plugin_action_links_' . plugin_basename( dirname( __FILE__ ) . '/../arraycodes-order-notifications-woocommerce.php' ), array( $this, 'action_links' ) );
			add_action( 'before_woocommerce_init', array( $this, 'declare_compatibility_hpos') );
		}

		/**
		 * Action_links function
		 *
		 * @param array $actions     Actions.
		 */
		public function action_links( $actions ) {
			$settings = array('settings' => '<a href="admin.php?page=wc-admin&path=%2Farraycodes-order-notifications-woocommerce">' . __('Settings', 'arraycodes-order-notifications-woocommerce') . '</a>');
			$actions  = array_merge( $settings, $actions );
			return $actions;
		}

		/**
		 * Register_admin_classes function.
		 */
		private function register_admin_classes() {

		}

		/**
		 * Register_public_classes function.
		 */
		private function register_public_classes() {
			if ( class_exists( 'ArraycodesOrderNotifications\Database\TableMessage' ) ) :
				TableManager::instance();
			endif;
			if ( class_exists('ArraycodesOrderNotifications\AdminPage\AdminPageReact') ) :
				AdminPageReact::instance();
			endif;
			if ( class_exists( 'ArraycodesOrderNotifications\InternalEndpoints\RegisterRoutes' ) ) :
				RegisterRoutes::instance();
			endif;
			if ( class_exists( 'ArraycodesOrderNotifications\WhatsAppNotificationField' ) ) :
				WhatsAppNotificationField::instance();
			endif;
			if ( class_exists( 'ArraycodesOrderNotifications\WhatsAppMessageCenter\WhatsAppInit' ) ) :
				WhatsAppInit::instance();
			endif;
		}

		/**
		 * Declare_compatibility_hpos
		 */
		public function declare_compatibility_hpos() {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', plugin_basename( dirname( __FILE__ ) . '/../arraycodes-order-notifications-woocommerce.php' ), true );
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', plugin_basename( dirname( __FILE__ ) . '/../arraycodes-order-notifications-woocommerce.php' ), true );
			}
		}

		/**
		 * Has_valid_requirements
		 *
		 * @name 'has_valid_requirements'
		 * @return bool
		 */
		private function has_valid_requirements() {

			$plugin_path = trailingslashit( WP_PLUGIN_DIR ) . 'woocommerce/woocommerce.php';
			if ( in_array( $plugin_path, wp_get_active_and_valid_plugins(), true ) ) {
				return true;
			}

			$network_active_plugins = get_site_option('active_sitewide_plugins');
			if ( ( ! empty($network_active_plugins) && array_key_exists('woocommerce/woocommerce.php', $network_active_plugins) ) ) {
				return true;
			}

			return false;
		}
	}
endif;
