<?php
/**
 * RegisterRoutes
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\InternalEndpoints;

use ArraycodesOrderNotifications\FacebookCommunicator\DebugFacebookCommunicator;
use ArraycodesOrderNotifications\FacebookCommunicator\WebhookFacebookCommunicator;
use ArraycodesOrderNotifications\Logger\Logger;
use ArraycodesOrderNotifications\Options\CustomerOptionsManager;
use ArraycodesOrderNotifications\FacebookCommunicator\OrderCustomerFacebookCommunicator;
use ArraycodesOrderNotifications\FacebookCommunicator\OrderSellerFacebookCommunicator;
use ArraycodesOrderNotifications\Options\OptionsCredentialsManager;
use ArraycodesOrderNotifications\Options\SellerOptionsManager;
use ArraycodesOrderNotifications\Options\StatusCustomerOptionsManager;
use ArraycodesOrderNotifications\ResponseMessage\ResponseMessageInit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class RegisterRoutes
 */
final class RegisterRoutes {

	/**
	 * Instance of this class.
	 *
	 * @var self|null Instance of the class.
	*/
	protected static $instance = null;

	/**
	 * Init constructor.
	 */
	private function __construct() {
		add_action('rest_api_init', array( $this, 'register_rest_routes'));
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 *  Register_rest_routes
	 */
	public function register_rest_routes() {
		$optionsCredentialsManager = new OptionsCredentialsManager();
		$logger                    = new Logger($optionsCredentialsManager);

		$customerOptionsManager            = new CustomerOptionsManager();
		$orderCustomerFacebookCommunicator = new OrderCustomerFacebookCommunicator($optionsCredentialsManager, $customerOptionsManager);
		$orderCustomerInternalEndpoints    = new OrderInternalEndpoints($orderCustomerFacebookCommunicator, $logger);

		$sellerOptionsManager            = new SellerOptionsManager();
		$orderSellerFacebookCommunicator = new OrderSellerFacebookCommunicator($optionsCredentialsManager, $sellerOptionsManager);
		$orderSellerInternalEndpoints    = new OrderInternalEndpoints($orderSellerFacebookCommunicator, $logger);

		$webhookFacebookCommunicator = new WebhookFacebookCommunicator($optionsCredentialsManager);
		$webhookInternalEndpoints    = new WebhookInternalEndpoints($webhookFacebookCommunicator, $logger);

		$debugFacebookCommunicator = new DebugFacebookCommunicator($optionsCredentialsManager);
		$debugInternalEndpoints    = new DebugInternalEndpoints($debugFacebookCommunicator, $logger);

		$responseMessageInit = new ResponseMessageInit($optionsCredentialsManager);

		$messagesEndpoints = new MessagesEndpoints();

		register_rest_route('arraycodes-order-notifications-woocommerce/v1', '/new-order-customer', array(
			'methods' => 'POST',
			'callback' => array( $orderCustomerInternalEndpoints, 'new_order' ),
			'permission_callback' => function () {
				return current_user_can( 'edit_others_posts' );
			}
		));

		register_rest_route('arraycodes-order-notifications-woocommerce/v1', '/update-order-customer', array(
			'methods' => 'POST',
			'callback' => array( $orderCustomerInternalEndpoints, 'update_order' ),
			'permission_callback' => function () {
				return current_user_can( 'edit_others_posts' );
			}
		));

		register_rest_route('arraycodes-order-notifications-woocommerce/v1', '/get-order-customer', array(
			'methods' => 'GET',
			'callback' => array( $orderCustomerInternalEndpoints, 'get_order' ),
			'permission_callback' => function () {
				return current_user_can( 'edit_others_posts' );
			}
		));

		register_rest_route('arraycodes-order-notifications-woocommerce/v1', '/delete-order-customer', array(
			'methods' => 'POST',
			'callback' => array( $orderCustomerInternalEndpoints, 'delete_order' ),
			'permission_callback' => function () {
				return current_user_can( 'edit_others_posts' );
			}
		));


		register_rest_route('arraycodes-order-notifications-woocommerce/v1', '/new-order-seller', array(
			'methods' => 'POST',
			'callback' => array( $orderSellerInternalEndpoints, 'new_order' ),
			'permission_callback' => function () {
				return current_user_can( 'edit_others_posts' );
			}
		));

		register_rest_route('arraycodes-order-notifications-woocommerce/v1', '/update-order-seller', array(
			'methods' => 'POST',
			'callback' => array( $orderSellerInternalEndpoints, 'update_order' ),
			'permission_callback' => function () {
				return current_user_can( 'edit_others_posts' );
			}
		));

		register_rest_route('arraycodes-order-notifications-woocommerce/v1', '/get-order-seller', array(
			'methods' => 'GET',
			'callback' => array( $orderSellerInternalEndpoints, 'get_order' ),
			'permission_callback' => function () {
				return current_user_can( 'edit_others_posts' );
			}
		));

		register_rest_route('arraycodes-order-notifications-woocommerce/v1', '/delete-order-seller', array(
			'methods' => 'POST',
			'callback' => array( $orderSellerInternalEndpoints, 'delete_order' ),
			'permission_callback' => function () {
				return current_user_can( 'edit_others_posts' );
			}
		));

		register_rest_route('arraycodes-order-notifications-woocommerce/v1', '/webhook', array(
			'methods' => 'GET',
			'callback' => array( $webhookInternalEndpoints, 'check_webhook' ),
			'permission_callback' => '__return_true'
		));

		register_rest_route('arraycodes-order-notifications-woocommerce/v1', '/webhook', array(
			'methods' => 'POST',
			'callback' => array( $webhookInternalEndpoints, 'action_webhook' ),
			'permission_callback' => '__return_true'
		));

		register_rest_route('arraycodes-order-notifications-woocommerce/v1', '/get-webhook-callback-url', array(
			'methods' => 'GET',
			'callback' => array( $webhookInternalEndpoints, 'get_webhook_callback_url' ),
			'permission_callback' => function () {
				return current_user_can( 'edit_others_posts' );
			}
		));

		register_rest_route('arraycodes-order-notifications-woocommerce/v1', '/get-messages', array(
			'methods' => 'GET',
			'callback' => array( $messagesEndpoints, 'get_messages' ),
			'permission_callback' => function () {
				return current_user_can( 'edit_others_posts' );
			}
		));

		register_rest_route('arraycodes-order-notifications-woocommerce/v1', '/get-messages-by-id', array(
			'methods' => 'GET',
			'callback' => array( $messagesEndpoints, 'get_messages_by_id' ),
			'permission_callback' => function () {
				return current_user_can( 'edit_others_posts' );
			}
		));

		register_rest_route('arraycodes-order-notifications-woocommerce/v1', '/get-new-messages', array(
			'methods' => 'GET',
			'callback' => array( $messagesEndpoints, 'get_new_messages' ),
			'permission_callback' => function () {
				return current_user_can( 'edit_others_posts' );
			}
		));

		register_rest_route('arraycodes-order-notifications-woocommerce/v1', '/get-status-order', array(
			'methods' => 'GET',
			'callback' => array( $messagesEndpoints, 'get_status_order' ),
			'permission_callback' => function () {
				return current_user_can( 'edit_others_posts' );
			}
		));

		register_rest_route('arraycodes-order-notifications-woocommerce/v1', '/new-response-message', array(
			'methods' => 'POST',
			'callback' => array( $responseMessageInit, 'new_response_message' ),
			'permission_callback' => function () {
				return current_user_can( 'edit_others_posts' );
			}
		));

		register_rest_route('arraycodes-order-notifications-woocommerce/v1', '/debug-token', array(
			'methods' => 'GET',
			'callback' => array( $debugInternalEndpoints, 'debug_token' ),
			'permission_callback' => function () {
				return current_user_can( 'edit_others_posts' );
			}
		));

		register_rest_route('arraycodes-order-notifications-woocommerce/v1', '/debug-waba-id', array(
			'methods' => 'GET',
			'callback' => array( $debugInternalEndpoints, 'debug_waba_id' ),
			'permission_callback' => function () {
				return current_user_can( 'edit_others_posts' );
			}
		));

		register_rest_route('arraycodes-order-notifications-woocommerce/v1', '/debug-app-secret', array(
			'methods' => 'GET',
			'callback' => array( $debugInternalEndpoints, 'debug_app_secret' ),
			'permission_callback' => function () {
				return current_user_can( 'edit_others_posts' );
			}
		));

	}

	/**
	 *  Check_public_permission
	 */
	public function check_public_permission(): string {
		return '__return_true';
	}
}
