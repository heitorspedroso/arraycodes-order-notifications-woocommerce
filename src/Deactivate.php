<?php
/**
 * ArraycodesOrderNotifications Deactivate
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications;

use ArraycodesOrderNotifications\AbandonedCart\AbandonedCartNotifierManager;
use ArraycodesOrderNotifications\Database\TableAbandonedCart;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Deactivate' ) ) :
	/**
	 * Class Deactivate
	 */
	final class Deactivate {

		/**
		 *  Deactivate
		 */
		public static function deactivate() {
			flush_rewrite_rules();
		}

	}
endif;
