<?php
/**
 * ResponseMessageInit Class
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\ResponseMessage;

use ArraycodesOrderNotifications\Options\OptionsCredentialsManager;

defined( 'ABSPATH' ) || exit;

class ResponseMessageInit {
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
	 *  ApiEndpoints
	 *
	 * @param object $request Request.
	 */
	public function new_response_message( $request ): \WP_REST_Response {

		$data = $request->get_json_params();

		$nonce = sanitize_text_field($data['security']);
		if (!wp_verify_nonce($nonce, 'arraycodes-order-notifications-woocommerce-nonce')) {
			return new \WP_Error('nonce_verification_failed', 'Nonce verification failed.', array('status' => 403));
		}

		$sanitized_fields = array();
		$fields           = $data['fields'];

		foreach ($fields as $key => $value) {
			if (strpos($key, 'message') !== false) {
				$sanitized_value = sanitize_textarea_field($value);
			} else {
				$sanitized_value = sanitize_text_field($value);
			}
			$sanitized_fields[$key] = $sanitized_value;
		}

		$responseMessageWhatsAppMessage = new ResponseMessageWhatsAppMessage($this->optionsCredentialsManager);
		$return                         = $responseMessageWhatsAppMessage->sendMessage($sanitized_fields);

		return new \WP_REST_Response(array('message' => $return['message']), $return['code']);

	}

}
