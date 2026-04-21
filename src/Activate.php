<?php
/**
 * ArraycodesOrderNotifications Activate
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications;

use ArraycodesOrderNotifications\Database\TableManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Activate' ) ) :
	/**
	 * Class Activate
	 */
	final class Activate {

		/**
		 *  Activate
		 */
		public static function activate() {
			TableManager::install_table();
			flush_rewrite_rules();
		}

	}
endif;
