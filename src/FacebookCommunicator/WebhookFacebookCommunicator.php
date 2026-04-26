<?php
/**
 * WebhookFacebookCommunicator
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\FacebookCommunicator;

use ArraycodesOrderNotifications\Options\OptionsCredentialsManager;
use ArraycodesOrderNotifications\Options\CustomerOptionsManager;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WebhookFacebookCommunicator' ) ) :
	/**
	 * Class WebhookFacebookCommunicator
	 */
	class WebhookFacebookCommunicator {

		/**
		 * The single instance of the class.
		 *
		 * @var object OptionsCredentialsManager.
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
		 * Get_class_type.
		 *
		 */
		public function get_class_type(): string {
			return 'WebHook';
		}

		/**
		 * Create_model_new_order.
		 *
		 * @param string $business_phone_number_id Fields.
		 * @param string $message_id Fields.
		 */
		public function mark_as_read( $business_phone_number_id, $message_id ): array {

			$options_whatsapp_settings_credentials = $this->optionsCredentialsManager->get_options_whatsapp_settings_credentials();

			$fields = array(
				'messaging_product'=> 'whatsapp',
				'status'=> 'read',
				'message_id'=> $message_id
			);

			if ( $options_whatsapp_settings_credentials ) {
					$url = 'https://graph.facebook.com/v22.0/' . $business_phone_number_id . '/messages';

				$headers = array(
					'Authorization' => 'Bearer ' . $options_whatsapp_settings_credentials['whatsapp_api_token'],
					'Content-Type' => 'application/json'
				);

				$args = array(
					'headers' => $headers,
					'body' => wp_json_encode( $fields ),
					'timeout' => 9999,
				);

				$response = wp_remote_post($url, $args);

				if (is_wp_error($response)) {
					$error_message = $response->get_error_message();
					$code          = $response->get_error_code();

					if ( 'http_request_failed' === $code) {
						return array('message' => $error_message, 'code' => 408);
					}
					return array('message' => $error_message, 'code' => $code);
				} else {
					$response_body = wp_remote_retrieve_body($response);

					$json_response_body = json_decode($response_body);

					if ($json_response_body->error) {
						return array('message' => $json_response_body->error, 'code' => 200);
					}

					if ($json_response_body->id) {
						return array('message' => $response_body, 'code' => 200);
					}

					return array('message' => $response_body, 'code' => 200);
				}

			}

			return array('message' => 'Fields are false', 'code' => 403);

		}

		/**
		 * Get_app_secret.
		 */
		public function get_app_secret(): string {
			return $this->optionsCredentialsManager->get_app_secret();
		}

	}
endif;
