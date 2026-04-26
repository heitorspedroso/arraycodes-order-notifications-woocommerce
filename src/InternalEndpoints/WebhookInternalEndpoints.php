<?php
/**
 * WebhookInternalEndpoints
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\InternalEndpoints;

defined( 'ABSPATH' ) || exit;

use ArraycodesOrderNotifications\FacebookCommunicator\FacebookCommunicatorInterface;
use ArraycodesOrderNotifications\FacebookCommunicator\WebhookFacebookCommunicator;
use ArraycodesOrderNotifications\WhatsAppResponse\WhatsAppResponseHandler;
use ArraycodesOrderNotifications\WhatsAppResponse\WhatsAppReturnInterpreter;
use ArraycodesOrderNotifications\Logger\LoggerInterface;

if ( ! class_exists( 'WebhookInternalEndpoints' ) ) :
	/**
	 * Class WebhookInternalEndpoints
	 */
	class WebhookInternalEndpoints {
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
		 * @param WebhookFacebookCommunicator $apiFunctions The Facebook Communicator Interface.
		 * @param LoggerInterface $logger The Logger Interface.
		 */
		public function __construct( WebhookFacebookCommunicator $apiFunctions, LoggerInterface $logger) {
			$this->apiFunctions = $apiFunctions;
			$this->logger       = $logger;
		}

		/**
		 *  ApiEndpoints
		 *
		 * @param object $request Request.
		 */
		public function check_webhook( $request ) : string {
			$data = $request->get_params();

			if (!empty($data) && isset($data['hub_mode']) && isset($data['hub_verify_token']) && isset($data['hub_challenge'])) {

				$site_url             = get_bloginfo('url');
				$site_url             = $site_url . '/wp-json/arraycodes-order-notifications-woocommerce/v1/webhook';
				$webhook_verify_token = wp_hash( $site_url );

				$mode      = $data['hub_mode'];
				$token     = $data['hub_verify_token'];
				$challenge = $data['hub_challenge'];

				if ('subscribe' === $mode && $token === $webhook_verify_token) {
					$response = new \WP_REST_Response($challenge, 200);
					print esc_html($response->data);
					exit;
				} else {
					$this->logger->save_log_api($this->apiFunctions->get_class_type() . ' - Return Validate Token API Facebook', array('Invalid token'));
					return false;
				}
			}
			$this->logger->save_log_api($this->apiFunctions->get_class_type() . ' - Return Validate Token API Facebook', array('No data received when valid token'));

			return false;

		}

		/**
		 *  ApiEndpoints
		 *
		 * @param object $request Request.
		 */
		public function action_webhook( $request): \WP_REST_Response {

			$app_secret = $this->apiFunctions->get_app_secret();

			if ( empty( $app_secret ) ) {
				$this->logger->save_log_api( $this->apiFunctions->get_class_type() . ' - Webhook rejected: App Secret not configured', array() );
				return new \WP_REST_Response( array( 'message' => false ), 403 );
			}

			$signature_header = $request->get_header( 'x-hub-signature-256' );
			$raw_body         = $request->get_body();
			$expected         = 'sha256=' . hash_hmac( 'sha256', $raw_body, $app_secret );

			if ( empty( $signature_header ) || ! hash_equals( $expected, $signature_header ) ) {
				$this->logger->save_log_api( $this->apiFunctions->get_class_type() . ' - Invalid webhook signature', array( 'signature_mismatch' ) );
				return new \WP_REST_Response( array( 'message' => false ), 403 );
			}

			$data = $request->get_json_params();

			$facebookResponse = new WhatsAppResponseHandler(wp_json_encode($data));
			$facebookResponse->setApiFunctions($this->apiFunctions);
			$facebookResponse->handleResponse();

			$this->logger->save_log_api('Return Message: ' . $this->apiFunctions->get_class_type() . ' - Return API Facebook', $data);

			return new \WP_REST_Response(array('message' => true), 200);
		}

		/**
		 *  ApiEndpoints
		 *
		 */
		public function get_webhook_callback_url(): \WP_REST_Response {

			$site_url = get_bloginfo('url');
			$site_url = $site_url . '/wp-json/arraycodes-order-notifications-woocommerce/v1/webhook';
			$token    = wp_hash( $site_url );

			$return = array(
				'whatsapp_api_webhook_callback_url' => $site_url,
				'whatsapp_api_webhook_token'        => $token,
			);
			return new \WP_REST_Response(array('message' => 'ok', 'data'=> $return, 200));

		}

	}
endif;
