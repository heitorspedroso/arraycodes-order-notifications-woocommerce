<?php
/**
 * MessagesEndpoints
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\InternalEndpoints;

defined( 'ABSPATH' ) || exit;

use ArraycodesOrderNotifications\Database\TableMessage;
use ArraycodesOrderNotifications\FacebookCommunicator\WebhookFacebookCommunicator;
use ArraycodesOrderNotifications\Logger\Logger;
use ArraycodesOrderNotifications\Options\OptionsCredentialsManager;

if ( ! class_exists( 'MessagesEndpoints' ) ) :
	/**
	 * Class MessagesEndpoints
	 */
	class MessagesEndpoints {

		/**
		 * Constructor.
		 *
		 */
		public function __construct() {
		}

		/**
		 *  Get messages
		 *
		 */
		public function get_messages( $request): \WP_REST_Response {
			$params   = $request->get_params();
			$page     = isset( $params['page'] ) ? (int) $params['page'] : 1;
			$per_page = isset( $params['per_page'] ) ? (int) $params['per_page'] : 10;
			$offset   = ( $page - 1 ) * $per_page;

			$tableMessage = new TableMessage();
			$messages     = $tableMessage->read_with_user_info( $offset, $per_page );

			return new \WP_REST_Response(array('result' => $messages), 200);
		}

		/**
		 *  Get messages
		 *
		 */
		public function get_messages_by_id( $request): \WP_REST_Response {
			$params       = $request->get_params();
			$wa_id        = isset( $params['wa_id'] ) ? (int) $params['wa_id'] : 0;
			$tableMessage = new TableMessage();
			$messages     = $tableMessage->read_by_id_with_user_info( $wa_id );
			if ($messages) {
				$tableMessage->mark_as_read_database( $wa_id );

				$lastUserMessage = null;
				for ($i = count($messages) - 1; $i >= 0; $i--) {
					if ('user' === $messages[$i]->sender_type) {
						$lastUserMessage = $messages[$i];
						break;
					}
				}

				if ($lastUserMessage->message_id) {
					$optionsCredentialsManager             = new OptionsCredentialsManager();
					$options_whatsapp_settings_credentials = $optionsCredentialsManager->get_options_whatsapp_settings_credentials();
					$business_phone_number_id              = $options_whatsapp_settings_credentials['whatsapp_api_phone_number'];
					$logger                                = new Logger($optionsCredentialsManager);
					$webhookFacebookCommunicator           = new WebhookFacebookCommunicator($optionsCredentialsManager);
					$markAsRead                            = $webhookFacebookCommunicator->mark_as_read($business_phone_number_id, $lastUserMessage->message_id);
					$logger->save_log_api('Message Response Mark as Read - Return API Facebook', $markAsRead);
				}

			}

			return new \WP_REST_Response(array('result' => $messages), 200);
		}

		/**
		 *  Get messages
		 *
		 */
		public function get_new_messages(): \WP_REST_Response {
			$tableMessage = new TableMessage();
			$messages     = $tableMessage->count_new_messages();

			return new \WP_REST_Response(array('result' => $messages), 200);
		}

		/**
		 *  Get Status Order
		 *
		 */
		public function get_status_order(): \WP_REST_Response {
			$statuses = wc_get_order_statuses();

			$cleaned_statuses = array_filter($statuses, function( $status_slug) {
				return strpos($status_slug, 'checkout-draft') === false;
			}, ARRAY_FILTER_USE_KEY);

			$cleaned_statuses = array_combine(
				array_map(function( $status_slug) {
					return str_replace('wc-', '', $status_slug);
				}, array_keys($cleaned_statuses)),
				$cleaned_statuses
			);

			$formatted_statuses   = array();
			$formatted_statuses[] = array(
				'disabled' => true,
				'label'=>'Select an Option',
				'value'=>'',
			);
			$key                  = 0;
			foreach ($cleaned_statuses as $status_slug => $status_name) {
				$formatted_statuses[] = array(
					'label' => esc_html($status_name),
					'value' => esc_html($status_slug)
				);
				$key++;
			}

			return new \WP_REST_Response(array('result' => $formatted_statuses), 200);
		}

	}
endif;
