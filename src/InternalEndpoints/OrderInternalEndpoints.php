<?php
/**
 * OrderInternalEndpoints
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\InternalEndpoints;

defined( 'ABSPATH' ) || exit;

use ArraycodesOrderNotifications\FacebookCommunicator\FacebookCommunicatorInterface;
use ArraycodesOrderNotifications\Logger\LoggerInterface;

if ( ! class_exists( 'OrderInternalEndpoints' ) ) :
	/**
	 * Class OrderInternalEndpoints
	 */
	class OrderInternalEndpoints implements InternalEndpointsInterface {
		/**
		 * The single instance of the class.
		 *
		 * @var FacebookCommunicatorInterface
		 */
		private $apiFunctions;

		/**
		 * The single instance of the class.
		 *
		 * @var LoggerInterface
		 */
		private $logger;

		/**
		 * Constructor.
		 *
		 * @param FacebookCommunicatorInterface $apiFunctions The Facebook Communicator Interface.
		 * @param LoggerInterface $logger The Logger Interface.
		 */
		public function __construct( FacebookCommunicatorInterface $apiFunctions, LoggerInterface $logger) {
			$this->apiFunctions = $apiFunctions;
			$this->logger       = $logger;
		}

		/**
		 *  ApiEndpoints
		 *
		 * @param object $request Request.
		 */
		public function new_order( $request ): \WP_REST_Response {

			$data = $request->get_json_params();

			$nonce = sanitize_text_field($data['security']);
			if (!wp_verify_nonce($nonce, 'arraycodes-order-notifications-woocommerce-nonce')) {
				return new \WP_Error('nonce_verification_failed', 'Nonce verification failed.', array('status' => 403));
			}

			$sanitized_fields = array();
			$fields           = $data['fields'];

			foreach ( $fields as $key => $value ) {
				if ( strpos( $key, '_body' ) !== false && is_string( $value ) ) {
					$sanitized_fields[ $key ] = sanitize_textarea_field( $value );
					continue;
				}

				if ( 'order_status_customer_buttons' === $key && is_array( $value ) ) {
					$sanitized_fields[ $key ] = $this->sanitize_recursive( $value );
					continue;
				}

				if ( is_string( $value ) ) {
					$sanitized_fields[ $key ] = sanitize_text_field( $value );
				} else {
					$sanitized_fields[ $key ] = $value;
				}
			}

			$return = $this->apiFunctions->create_new_order( $sanitized_fields );

			$this->logger->save_log_api('Create Template: ' . $this->apiFunctions->get_class_type() . ' - Return API Facebook', $return);

			return new \WP_REST_Response(array('message' => $return['message']), $return['code']);

		}

		/**
		 *  ApiEndpoints
		 *
		 * @param object $request Request.
		 */
		public function update_order( $request ): \WP_REST_Response {

			$data = $request->get_json_params();

			$nonce = sanitize_text_field($data['security']);
			if (!wp_verify_nonce($nonce, 'arraycodes-order-notifications-woocommerce-nonce')) {
				return new \WP_Error('nonce_verification_failed', 'Nonce verification failed.', array('status' => 403));
			}

			$sanitized_fields = array();
			$fields           = $data['fields'];

			foreach ( $fields as $key => $value ) {
				if ( strpos( $key, '_body' ) !== false && is_string( $value ) ) {
					$sanitized_fields[ $key ] = sanitize_textarea_field( $value );
					continue;
				}

				if ( 'order_status_customer_buttons' === $key && is_array( $value ) ) {
					$sanitized_fields[ $key ] = $this->sanitize_recursive( $value );
					continue;
				}

				if ( is_string( $value ) ) {
					$sanitized_fields[ $key ] = sanitize_text_field( $value );
				} else {
					$sanitized_fields[ $key ] = $value;
				}
			}

			$return = $this->apiFunctions->update_new_order( $sanitized_fields );

			$this->logger->save_log_api('Update Template: ' . $this->apiFunctions->get_class_type() . ' - Return API Facebook', $return);

			return new \WP_REST_Response(array('message' => $return['message']), $return['code']);

		}

		/**
		 *  ApiEndpoints
		 *
		 * @param object $request Request.
		 */
		public function delete_order( $request ): \WP_REST_Response {

			$data = $request->get_json_params();

			$nonce = sanitize_text_field($data['security']);
			if (!wp_verify_nonce($nonce, 'notifications-with-whatsapp-nonce')) {
				return new \WP_Error('nonce_verification_failed', 'Nonce verification failed.', array('status' => 403));
			}

			$sanitized_fields = array();
			$fields           = $data['fields'];

			foreach ($fields as $key => $value) {
				$sanitized_value        = sanitize_text_field($value);
				$sanitized_fields[$key] = $sanitized_value;
			}

			$return = $this->apiFunctions->delete_new_order( $sanitized_fields );

			$this->logger->save_log_api('Delete Template: ' . $this->apiFunctions->get_class_type() . ' - Return API Facebook', $return);

			return new \WP_REST_Response(array('message' => $return['message']), $return['code']);

		}

		/**
		 *  ApiEndpoints
		 *
		 * @param object $request Request.
		 */
		public function get_order( $request ): array {

			$return = $this->apiFunctions->get_order();

			$this->logger->save_log_api('Get Template: ' . $this->apiFunctions->get_class_type() . ' - Return API Facebook', $return);

			return $return;

		}

		/**
		 * Recursively sanitizes an array or a string.
		 *
		 * If the input is an array, the method iterates through each key-value pair,
		 * sanitizes the keys using `sanitize_key` and recursively sanitizes the values.
		 * If the input is a string, it sanitizes the string using `sanitize_text_field`.
		 * All other types of input are returned unaltered.
		 *
		 * @param mixed $value The value to sanitize. Can be an array, string, or other data type.
		 * @return mixed Returns the sanitized value. Arrays and strings are sanitized, while other types are returned as-is.
		 */
		private function sanitize_recursive( $value ) {
			if ( is_array( $value ) ) {
				$sanitized = [];

				foreach ( $value as $key => $item ) {
					$sanitized[ sanitize_key( $key ) ] = $this->sanitize_recursive( $item );
				}

				return $sanitized;
			}

			if ( is_string( $value ) ) {
				return sanitize_text_field( $value );
			}

			return $value;
		}

	}
endif;
