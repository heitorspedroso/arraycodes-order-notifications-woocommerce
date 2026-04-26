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

	/**
	 * Validate the App Secret by using it as part of an app access token.
	 */
	public function debug_app_secret_api(): array {

		$options = $this->optionsCredentialsManager->get_options_whatsapp_settings_credentials();

		if ( empty( $options['whatsapp_api_token'] ) || empty( $options['whatsapp_api_app_secret'] ) ) {
			return array( 'message' => 'Fields are false', 'code' => 403 );
		}

		$debug_response = wp_remote_get(
			'https://graph.facebook.com/v22.0/debug_token?input_token=' . rawurlencode( $options['whatsapp_api_token'] ),
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $options['whatsapp_api_token'],
					'Content-Type'  => 'application/json',
				),
				'timeout' => 9999,
			)
		);

		if ( is_wp_error( $debug_response ) ) {
			$code = $debug_response->get_error_code();
			return array( 'message' => $debug_response->get_error_message(), 'code' => 'http_request_failed' === $code ? 408 : $code );
		}

		$debug_body = json_decode( wp_remote_retrieve_body( $debug_response ) );

		if ( empty( $debug_body->data->app_id ) ) {
			return array( 'message' => 'Could not retrieve app_id from token', 'code' => 400 );
		}

		$app_id           = $debug_body->data->app_id;
		$app_access_token = $app_id . '|' . $options['whatsapp_api_app_secret'];

		$validate_response = wp_remote_get(
			'https://graph.facebook.com/v22.0/' . rawurlencode( $app_id ) . '?fields=id,name&access_token=' . rawurlencode( $app_access_token ),
			array( 'timeout' => 9999 )
		);

		if ( is_wp_error( $validate_response ) ) {
			$code = $validate_response->get_error_code();
			return array( 'message' => $validate_response->get_error_message(), 'code' => 'http_request_failed' === $code ? 408 : $code );
		}

		return array(
			'message' => wp_remote_retrieve_body( $validate_response ),
			'code'    => wp_remote_retrieve_response_code( $validate_response ),
		);

	}

}
