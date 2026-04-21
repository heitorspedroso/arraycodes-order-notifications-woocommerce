<?php
/**
 * WhatsAppInit
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\WhatsAppMessageCenter;

use ArraycodesOrderNotifications\Options\OptionsCredentialsManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WhatsAppInit' ) ) :
	/**
	 * Class WhatsAppInit
	 */
	final class WhatsAppInit {

		/**
		 * Instance of this class.
		 *
		 * @var self|null Instance of the class.
		*/
		protected static $instance = null;

		/**
		 * Init constructor.
		 */
		private function __construct() {
			add_action( 'woocommerce_thankyou', array( $this, 'init_whatsapp_message' ), 40 );
		}

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
		 * Init WhatsApp Message.
		 *
		 * @param string $order_id Orderid.
		 */
		public function init_whatsapp_message( $order_id ) {
			$order = wc_get_order( $order_id );

			$whatsapp_notification_send_message = $order->get_meta( '_whatsapp_notification_send_message' );
			if ( 'yes' !== $whatsapp_notification_send_message ) {
				$order->update_meta_data( '_whatsapp_notification_send_message', 'yes' );
				$order->save_meta_data();

				$optionsCredentialsManager = new OptionsCredentialsManager();
				$orderFieldsPreparator     = new OrderFieldsPreparator();

				$sellerWhatsAppMessage = new SellerWhatsAppMessage( $optionsCredentialsManager );
				$whatsAppCommunicator  = new WhatsAppCommunicator( $optionsCredentialsManager, $orderFieldsPreparator, $sellerWhatsAppMessage );
				$whatsAppCommunicator->sendMessage( $order );

				if ( $order->get_meta( 'whatsapp_notification_fields_optin' ) ) {
					$customerWhatsAppMessage = new CustomerWhatsAppMessage( $optionsCredentialsManager );
					$whatsAppCommunicator    = new WhatsAppCommunicator( $optionsCredentialsManager, $orderFieldsPreparator, $customerWhatsAppMessage );
					$whatsAppCommunicator->sendMessage( $order );
				}
			}
		}

	}
endif;
