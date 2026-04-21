<?php
/**
 * OrderSellerFacebookCommunicator
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\FacebookCommunicator;

use ArraycodesOrderNotifications\Options\OptionsCredentialsManager;
use ArraycodesOrderNotifications\Options\SellerOptionsManager;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'OrderSellerFacebookCommunicator' ) ) :
	/**
	 * Class OrderSellerFacebookCommunicator
	 */
	class OrderSellerFacebookCommunicator extends BaseOrderFacebookCommunicator {

		/**
		 * The single instance of the class.
		 *
		 * @var OptionsCredentialsManager
		 */
		private $optionsCredentialsManager;

		/**
		 * The single instance of the class.
		 *
		 * @var SellerOptionsManager
		 */
		private $optionsManager;

		/**
		 * Constructor.
		 *
		 * @param SellerOptionsManager $optionsManager The options manager instance.
		 */
		public function __construct( OptionsCredentialsManager $optionsCredentialsManager, SellerOptionsManager $optionsManager) {
			$this->optionsCredentialsManager = $optionsCredentialsManager;
			$this->optionsManager            = $optionsManager;
		}

		/**
		 * Get_class_type.
		 *
		 */
		public function get_class_type(): string {
			return 'New Order Seller';
		}

		/**
		 * Create_model_new_order.
		 *
		 * @param array $fields Fields.
		 */
		public function create_new_order( $fields ): array {

			$prepared_fields = $this->prepare_fields( $fields['whatsapp_template_new_order_body'], $fields['whatsapp_template_new_order_language'], $fields['whatsapp_template_new_order_name'], $fields['whatsapp_template_new_order_header'], $fields['whatsapp_template_new_order_footer'] );

			$options_whatsapp_settings_credentials = $this->optionsCredentialsManager->get_options_whatsapp_settings_credentials();

			if ( $options_whatsapp_settings_credentials ) {
				$url = 'https://graph.facebook.com/v22.0/' . $options_whatsapp_settings_credentials['whatsapp_api_account_id'] . '/message_templates';

				$headers = array(
					'Authorization' => 'Bearer ' . $options_whatsapp_settings_credentials['whatsapp_api_token'],
					'Content-Type' => 'application/json'
				);

				$args = array(
					'headers' => $headers,
					'body' => wp_json_encode( $prepared_fields ),
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
						$this->update_status_new_order_seller( $json_response_body->id, $json_response_body->status );
					}

					return array('message' => $response_body, 'code' => 200);
				}

			}

			return array('message' => 'Fields are false', 'code' => 403);

		}

		/**
		 * Update_new_order.
		 *
		 * @param array $fields Fields.
		 */
		public function update_new_order( $fields ): array {

			$prepared_fields = $this->prepare_fields( $fields['whatsapp_template_new_order_body'], $fields['whatsapp_template_new_order_language'], $fields['whatsapp_template_new_order_name'], $fields['whatsapp_template_new_order_header'], $fields['whatsapp_template_new_order_footer'] );

			$options_whatsapp_settings_credentials = $this->optionsCredentialsManager->get_options_whatsapp_settings_credentials();

			if ( $options_whatsapp_settings_credentials ) {
				$options_whatsapp_settings_new_order = $this->get_options_whatsapp_settings_new_order();

				$url = 'https://graph.facebook.com/v22.0/' . $options_whatsapp_settings_new_order['whatsapp_template_new_order_id'];

				$headers = array(
					'Authorization' => 'Bearer ' . $options_whatsapp_settings_credentials['whatsapp_api_token'],
					'Content-Type' => 'application/json'
				);

				$args = array(
					'headers' => $headers,
					'body' => wp_json_encode( array('components' => $prepared_fields['components']) ),
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

					return array('message' => $response_body, 'code' => 200);
				}

			}

			return array('message' => 'Fields are false', 'code' => 403);

		}

		/**
		 * Delete_new_order.
		 *
		 * @param array $fields Fields.
		 */
		public function delete_new_order( $fields ): array {

			$options_whatsapp_settings_credentials = $this->optionsCredentialsManager->get_options_whatsapp_settings_credentials();

			if ( $options_whatsapp_settings_credentials ) {
				$options_whatsapp_settings_new_order = $this->get_options_whatsapp_settings_new_order();

				$url = 'https://graph.facebook.com/v22.0/' . $options_whatsapp_settings_credentials['whatsapp_api_account_id'] . '/message_templates';

				$headers = array(
					'Authorization' => 'Bearer ' . $options_whatsapp_settings_credentials['whatsapp_api_token'],
					'Content-Type' => 'application/json'
				);

				$args = array(
					'headers' => $headers,
					'method'     => 'DELETE',
					'body' => wp_json_encode( array('hsm_id' => $options_whatsapp_settings_new_order['whatsapp_template_new_order_id'], 'name' => $options_whatsapp_settings_new_order['whatsapp_template_new_order_name']) ),
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

					if ($json_response_body->success) {
						$this->update_status_new_order_seller( '', '' );
					}

					return array('message' => $response_body, 'code' => 200);
				}

			}

			return array('message' => 'Fields are false', 'code' => 403);

		}

		/**
		 * Get_order.
		 *
		 */
		public function get_order(): array {
			$options_whatsapp_settings_credentials = $this->optionsCredentialsManager->get_options_whatsapp_settings_credentials();

			if ( $options_whatsapp_settings_credentials ) {

				$options_whatsapp_settings_new_order = $this->get_options_whatsapp_settings_new_order();

				if ( !empty($options_whatsapp_settings_new_order['whatsapp_template_new_order_id']) ) {
					$url = 'https://graph.facebook.com/v22.0/' . $options_whatsapp_settings_new_order['whatsapp_template_new_order_id'];

					$headers = array(
						'Authorization' => 'Bearer ' . $options_whatsapp_settings_credentials['whatsapp_api_token'],
						'Content-Type' => 'application/json'
					);

					$args = array(
						'headers' => $headers,
					);

					$response = wp_remote_get($url, $args);

					if ( is_wp_error( $response ) ) {
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
							$this->update_status_new_order_seller( '', '' );
							return array('message' => $json_response_body->error, 'data'=> array('whatsapp_template_new_order_customer_id' => '','whatsapp_template_new_order_customer_status'=> 'REMOVED'), 'code' => 200);
						}

						return array( 'message' => 'ok', 'data'=> array('whatsapp_template_new_order_status'=> $json_response_body->status, 'whatsapp_template_new_order_category'=> $json_response_body->category ?? '') );
					}
				}

				return array();
			}

			return array();
		}

		/**
		 * Update_status_new_order_seller.
		 *
		 * @param string $id Id.
		 * @param string $status Status.
		 */
		private function update_status_new_order_seller( $id, $status) {
			$options = get_option( 'arraycodes_on_fields_free' );

			$options['whatsapp_template_new_order_id']     = $id;
			$options['whatsapp_template_new_order_status'] = $status;

			update_option('arraycodes_on_fields_free', $options);

		}

		/**
		 * Get_options_whatsapp_settings_new_order.
		 *
		 */
		public function get_options_whatsapp_settings_new_order() {
			return  $this->optionsManager->get_options_whatsapp_settings_new_order();
		}
	}
endif;
