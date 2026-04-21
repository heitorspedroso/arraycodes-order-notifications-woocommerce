<?php
/**
 * ResponseMessageWhatsAppMessage Class
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\ResponseMessage;

use ArraycodesOrderNotifications\WhatsAppResponse\WhatsAppResponseHandler;
use ArraycodesOrderNotifications\WhatsAppResponse\WhatsAppReturnInterpreter;
use ArraycodesOrderNotifications\Logger\Logger;
use ArraycodesOrderNotifications\Options\OptionsCredentialsManager;
use ArraycodesOrderNotifications\WhatsAppMessageCenter\TableConversation;
use ArraycodesOrderNotifications\WhatsAppMessageCenter\TableMessage;
use ArraycodesOrderNotifications\WhatsAppMessageCenter\TableUser;

defined( 'ABSPATH' ) || exit;

class ResponseMessageWhatsAppMessage {
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
	 * @param array $fields FieldsOrder.
	 */
	public function sendMessage( array $fields): array {
		$credential = $this->optionsCredentialsManager->get_options_whatsapp_settings_credentials();

		if ( !empty( $fields ) ) {

			$phone_customer = $fields['waId'];
			$message        = $fields['message'];

			$url         = 'https://graph.facebook.com/v22.0/' . $credential['whatsapp_api_phone_number'] . '/messages';
			$headers     = array(
				'Authorization' => 'Bearer ' . $credential['whatsapp_api_token'],
				'Content-Type' => 'application/json'
			);
			$body        = array(
				'messaging_product' => 'whatsapp',
				'recipient_type' => 'individual',
				'to' => $phone_customer,
				'type' => 'text',
				'text' => array(
					'body' => $message
				),
			);
			$args        = array(
				'headers' => $headers,
				'body' => wp_json_encode( $body ),
				'timeout' => 9999,
			);
			$response    = wp_remote_post( $url, $args );
			$code_status = wp_remote_retrieve_response_code( $response );

			$logger = new Logger($this->optionsCredentialsManager);
			$logger->save_log_api('Send Response Message - Status Return: ' . $code_status . '  - Return API Facebook', $response);

			if (is_wp_error($response)) {
				$error_message = $response->get_error_message();
				$code          = $response->get_error_code();

				if ( 'http_request_failed' === $code) {
					return array('message' => $error_message, 'code' => 408);
				}
				return array('message' => $error_message, 'code' => $code);
			} else {
				$response_body = wp_remote_retrieve_body($response);

				$facebookResponse = new WhatsAppResponseHandler($response_body);
				$facebookResponse->setIdentifier('send_response_message_system');
				$facebookResponse->setFieldsResponseMessageSystem($fields);
				$facebookResponse->handleResponse();

				$json_response_body = json_decode($response_body);

				if ($json_response_body->error) {
					return array('message' => $json_response_body->error, 'code' => 200);
				}

				return array('message' => $response_body, 'code' => 200);
			}

		}

		return array('message' => 'Fields are false', 'code' => 403);
	}

}
