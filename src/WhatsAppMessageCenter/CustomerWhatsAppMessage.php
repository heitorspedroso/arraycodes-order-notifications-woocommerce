<?php
/**
 * CustomerWhatsAppMessage Class
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\WhatsAppMessageCenter;

use ArraycodesOrderNotifications\FacebookCommunicator\OrderCustomerFacebookCommunicator;
use ArraycodesOrderNotifications\WhatsAppResponse\WhatsAppResponseHandler;
use ArraycodesOrderNotifications\WhatsAppResponse\WhatsAppReturnInterpreter;
use ArraycodesOrderNotifications\Logger\Logger;
use ArraycodesOrderNotifications\Options\CustomerOptionsManager;
use ArraycodesOrderNotifications\Options\OptionsCredentialsManager;

defined( 'ABSPATH' ) || exit;

class CustomerWhatsAppMessage extends BaseWhatsAppMessage {
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
		$optionsCredentialsManager         = $this->optionsCredentialsManager;
		$customerOptionsManager            = new CustomerOptionsManager();
		$orderCustomerFacebookCommunicator = new OrderCustomerFacebookCommunicator($optionsCredentialsManager, $customerOptionsManager);

		$options_whatsapp_template = $this->getOptionsWhatsappTemplate( $customerOptionsManager );

		if ( $options_whatsapp_template ) {

			$whatsapp_template_new_order_customer_id = $options_whatsapp_template['whatsapp_template_new_order_customer_id'];

			if ( !empty( $whatsapp_template_new_order_customer_id ) ) {

				$phone_customer = $fieldsOrder['order']->get_billing_phone();

				if ( $phone_customer ) {

					$shipping_country     = WC()->customer->get_shipping_country();
					$country_code         = WC()->countries->get_country_calling_code($shipping_country);
					$existis_country_code = strpos($phone_customer, str_replace('+', '', $country_code));

					if (false === $existis_country_code) {
						$phone_customer = $country_code . $phone_customer;
					}
					$phone_customer = str_replace(array('+', ' ', '(', ')', '-'), array('', '', '', '', ''), $phone_customer);
				}

				$whatsapp_template_new_order_customer_name     = $options_whatsapp_template['whatsapp_template_new_order_customer_name'];
				$whatsapp_template_new_order_customer_language = $options_whatsapp_template['whatsapp_template_new_order_customer_language'];
				$whatsapp_template_new_order_customer_header   = $options_whatsapp_template['whatsapp_template_new_order_customer_header'];
				$whatsapp_template_new_order_customer_body     = $options_whatsapp_template['whatsapp_template_new_order_customer_body'];

				$body_components = $this->getBodyComponents($fieldsOrder, $whatsapp_template_new_order_customer_body);

				$url     = 'https://graph.facebook.com/v22.0/' . $fieldsOrder['whatsapp_api_phone_number'] . '/messages';
				$headers = array(
					'Authorization' => 'Bearer ' . $fieldsOrder['whatsapp_api_token'],
					'Content-Type' => 'application/json'
				);

				$components = array();
				if ('image' === $options_whatsapp_template['whatsapp_template_new_order_customer_header_type']) {
					$components[] = array(
						'type' => 'header',
						'parameters' => [
							[
								'type' => 'image',
								'image' => [
									'link' => $whatsapp_template_new_order_customer_header,
								]
							]
						]
					);
				}

				$components[] = array(
					'type' => 'body',
					'parameters' => $body_components
				);

				$body          = array(
					'messaging_product' => 'whatsapp',
					'recipient_type' => 'individual',
					'to' => $phone_customer,
					'type' => 'template',
					'template' => array(
						'name' => $whatsapp_template_new_order_customer_name,
						'language' => array(
							'code' => $whatsapp_template_new_order_customer_language
						),
						'components' => $components
					)
				);
				$args          = array(
					'headers' => $headers,
					'body' => wp_json_encode( $body ),
					'timeout' => 9999,
				);
				$response      = wp_remote_post( $url, $args );
				$code_status   = wp_remote_retrieve_response_code( $response );
				$response_body = wp_remote_retrieve_body($response);

				$facebookResponse = new WhatsAppResponseHandler($response_body);
				$facebookResponse->setOrder($fieldsOrder['order']);
				$facebookResponse->setIdentifier('new_order_message');
				$facebookResponse->handleResponse();

				$logger = new Logger($optionsCredentialsManager);
				$logger->save_log_api('Send New Order ' . $orderCustomerFacebookCommunicator->get_class_type() . ' - Order ID: ' . $fieldsOrder['transaction_id'] . ' - Status Return: ' . $code_status . '  - Return API Facebook', $response);

			}

		}
	}

	/**
	 * Status Order.
	 *
	 * @param string $status Status Order.
	 */
	public function setStatus( $status) {}

	/**
	 * Get Options WhatsApp Template
	 *
	 * @param CustomerOptionsManager $customerOptionsManager
	 * @return array|mixed
	 */
	private function getOptionsWhatsappTemplate( CustomerOptionsManager $customerOptionsManager) {
		return $customerOptionsManager->get_options_whatsapp_template();
	}
}
