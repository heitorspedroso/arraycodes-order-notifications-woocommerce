<?php
/**
 * DebugInternalEndpoints
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\InternalEndpoints;

defined( 'ABSPATH' ) || exit;

use ArraycodesOrderNotifications\FacebookCommunicator\DebugFacebookCommunicator;
use ArraycodesOrderNotifications\Logger\LoggerInterface;

if ( ! class_exists( 'DebugInternalEndpoints' ) ) :
	/**
	 * Class DebugInternalEndpoints
	 */
	class DebugInternalEndpoints {
		/**
		 * The single instance of the class.
		 *
		 * @var object FacebookCommunicatorInterface.
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
		 * @param DebugFacebookCommunicator $apiFunctions The Logger Interface.
		 * @param LoggerInterface $logger
		 */
		public function __construct( DebugFacebookCommunicator $apiFunctions, LoggerInterface $logger) {
			$this->apiFunctions = $apiFunctions;
			$this->logger       = $logger;
		}

		/**
		 *  ApiEndpoints
		 *
		 */
		public function debug_token(): \WP_REST_Response {

			$debug_token_api = $this->apiFunctions->debug_token_api();
			$return_message  = json_decode( $debug_token_api['message'] );
			if (200 === $debug_token_api['code']) {
				if (isset($return_message->data->is_valid) && $return_message->data->is_valid) {
					return new \WP_REST_Response(array('message' => true, 'data'=> $debug_token_api));
				}
				return new \WP_REST_Response(array('message' => false, 'data'=> $debug_token_api));
			}
			return new \WP_REST_Response(array('message' => false, 'data'=> $debug_token_api));
		}

		/**
		 *  ApiEndpoints
		 *
		 */
		public function debug_waba_id(): \WP_REST_Response {

			$debug_token_api = $this->apiFunctions->debug_waba_id_api();
			$return_message  = json_decode( $debug_token_api['message'] );
			if (200 === $debug_token_api['code']) {
				if (isset($return_message->data['0']) && $return_message->data['0']) {
					return new \WP_REST_Response(array('message' => true, 'data'=> $return_message->data['0']));
				}
				return new \WP_REST_Response(array('message' => false, 'data'=> $return_message));
			}
			return new \WP_REST_Response(array('message' => false, 'data'=> $return_message));
		}

	/**
	 *  ApiEndpoints
	 *
	 */
	public function debug_app_secret(): \WP_REST_Response {

		$result = $this->apiFunctions->debug_app_secret_api();
		$body   = json_decode( $result['message'] );

		if ( 200 === $result['code'] && isset( $body->id ) ) {
			return new \WP_REST_Response( array( 'message' => true, 'data' => $result ) );
		}

		return new \WP_REST_Response( array( 'message' => false, 'data' => $result ) );
	}

	}
endif;
