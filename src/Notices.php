<?php
/**
 * Notices Init
 *
 * @version 1.0.1
 * @package 'datalayer'
 */

namespace ArraycodesOrderNotifications;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Notices' ) ) :
	/**
	 * Class Notices
	 */
	class Notices {

		/**
		 *  Add_notice_requires_woocommerce_activated
		 *
		 * @name 'add_notice_requires_woocommerce_activated'
		 */
		public static function add_notice_requires_woocommerce_activated() {
			$class   = 'notice notice-error';
			$message = __( 'ArrayCodes Order Notifications requires WooCommerce activated.', 'arraycodes-order-notifications-woocommerce' );
			printf( '<div class="%1$s"><p><b>%2$s</b></p></div>', esc_attr( $class ), esc_html( $message ) );
		}

	}
endif;
