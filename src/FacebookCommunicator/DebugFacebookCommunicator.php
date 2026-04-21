<?php
/**
 * DebugFacebookCommunicator
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\FacebookCommunicator;

use ArraycodesOrderNotifications\Options\OptionsCredentialsManager;
use ArraycodesOrderNotifications\Options\CustomerOptionsManager;

defined( 'ABSPATH' ) || exit;

/**
 * Class DebugFacebookCommunicator
 */
class DebugFacebookCommunicator {

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
	 * Create_model_new_order.
	 *
	 */
	public function debug_token_api(): array {

		$options_whatsapp_settings_credentials = $this->optionsCredentialsManager->get_options_whatsapp_settings_credentials();

		if ( $options_whatsapp_settings_credentials ) {
			$url = 'https://graph.facebook.com/v22.0/debug_token?input_token=' . $options_whatsapp_settings_credentials['whatsapp_api_token'];

			$headers = array(
				'Authorization' => 'Bearer ' . $options_whatsapp_settings_credentials['whatsapp_api_token'],
				'Content-Type' => 'application/json'
			);

			$args = array(
				'headers' => $headers,
				'timeout' => 9999,
			);

			$response = wp_remote_get($url, $args);

			if (is_wp_error($response)) {
				$error_message = $response->get_error_message();
				$code          = $response->get_error_code();

				if ( 'http_request_failed' === $code) {
					return array('message' => $error_message, 'code' => 408);
				}
				return array('message' => $error_message, 'code' => $code);
			} else {
				$response_body = wp_remote_retrieve_body($response);
				$response_code = wp_remote_retrieve_response_code( $response );

				return array('message' => $response_body, 'code' => $response_code);
			}

		}

		return array('message' => 'Fields are false', 'code' => 403);

	}

	/**
	 * Create_model_new_order.
	 *
	 */
	public function debug_waba_id_api(): array {

		$options_whatsapp_settings_credentials = $this->optionsCredentialsManager->get_options_whatsapp_settings_credentials();

		if ( $options_whatsapp_settings_credentials ) {
			$url = 'https://graph.facebook.com/v22.0/' . $options_whatsapp_settings_credentials['whatsapp_api_account_id'] . '/phone_numbers';

			$headers = array(
				'Authorization' => 'Bearer ' . $options_whatsapp_settings_credentials['whatsapp_api_token'],
				'Content-Type' => 'application/json'
			);

			$args = array(
				'headers' => $headers,
				'timeout' => 9999,
			);

			$response = wp_remote_get($url, $args);

			if (is_wp_error($response)) {
				$error_message = $response->get_error_message();
				$code          = $response->get_error_code();

				if ( 'http_request_failed' === $code) {
					return array('message' => $error_message, 'code' => 408);
				}
				return array('message' => $error_message, 'code' => $code);
			} else {
				$response_body = wp_remote_retrieve_body($response);
				$response_code = wp_remote_retrieve_response_code( $response );

				return array('message' => $response_body, 'code' => $response_code);
			}

		}

		return array('message' => 'Fields are false', 'code' => 403);

	}

}
