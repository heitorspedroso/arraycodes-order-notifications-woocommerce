<?php
/**
 * SellerWhatsAppMessage Class
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\WhatsAppMessageCenter;

use ArraycodesOrderNotifications\FacebookCommunicator\OrderSellerFacebookCommunicator;
use ArraycodesOrderNotifications\Logger\Logger;
use ArraycodesOrderNotifications\Options\OptionsCredentialsManager;
use ArraycodesOrderNotifications\Options\SellerOptionsManager;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'SellerWhatsAppMessage' ) ) :
	class SellerWhatsAppMessage extends BaseWhatsAppMessage {
		/**
		 * OptionsCredentialsManager.
		 *
		 * @var OptionsCredentialsManager
		 */
		private $optionsCredentialsManager;

		/**
		 * Constructor.
		 *
		 * @param OptionsCredentialsManager $optionsCredentialsManager The options manager instance.
		 */
		public function __construct( OptionsCredentialsManager $optionsCredentialsManager) {
			$this->optionsCredentialsManager = $optionsCredentialsManager;
		}

		/**
		 *  SendMessage
		 *
		 * @param array $fieldsOrder FieldsOrder.
		 */
		public function sendMessage( array $fieldsOrder) {
			$optionsCredentialsManager           = $this->optionsCredentialsManager;
			$sellerOptionsManager                = new SellerOptionsManager();
			$orderSellerFacebookCommunicator     = new OrderSellerFacebookCommunicator($optionsCredentialsManager, $sellerOptionsManager);
			$options_whatsapp_settings_new_order = $orderSellerFacebookCommunicator->get_options_whatsapp_settings_new_order();

			if ( $options_whatsapp_settings_new_order ) {

				$whatsapp_template_new_order_id = $options_whatsapp_settings_new_order['whatsapp_template_new_order_id'];

				if ( !empty( $whatsapp_template_new_order_id ) ) {

					$whatsapp_template_new_order_name     = $options_whatsapp_settings_new_order['whatsapp_template_new_order_name'];
					$whatsapp_template_new_order_language = $options_whatsapp_settings_new_order['whatsapp_template_new_order_language'];
					$whatsapp_template_new_order_body     = $options_whatsapp_settings_new_order['whatsapp_template_new_order_body'];

					$body_components = $this->getBodyComponents($fieldsOrder, $whatsapp_template_new_order_body);

					$url         = 'https://graph.facebook.com/v22.0/' . $fieldsOrder['whatsapp_api_phone_number'] . '/messages';
					$headers     = array(
						'Authorization' => 'Bearer ' . $fieldsOrder['whatsapp_api_token'],
						'Content-Type' => 'application/json'
					);
					$body        = array(
						'messaging_product' => 'whatsapp',
						'recipient_type' => 'individual',
						'to' => $fieldsOrder['whatsapp_api_phone_number_to'],
						'type' => 'template',
						'template' => array(
							'name' => $whatsapp_template_new_order_name,
							'language' => array(
								'code' => $whatsapp_template_new_order_language
							),
							'components' => array(
								array(
									'type' => 'body',
									'parameters' => $body_components
								)
							)
						)
					);
					$args        = array(
						'headers' => $headers,
						'body' => wp_json_encode( $body ),
						'timeout' => 9999,
					);
					$response    = wp_remote_post( $url, $args );
					$code_status = wp_remote_retrieve_response_code( $response );

					$logger = new Logger($optionsCredentialsManager);
					$logger->save_log_api('Send New Order ' . $orderSellerFacebookCommunicator->get_class_type() . ' - Order ID: ' . $fieldsOrder['transaction_id'] . ' - Status Return: ' . $code_status . '  - Return API Facebook', $response);

				}

			}
		}

		/**
		 * Status Order.
		 *
		 * @param string $status Status Order.
		 */
		public function setStatus( $status) {}
	}
endif;
