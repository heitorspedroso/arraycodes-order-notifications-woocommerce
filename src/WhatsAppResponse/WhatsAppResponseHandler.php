<?php
/**
 * WhatsAppResponse
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\WhatsAppResponse;

use Crontrol\Event\Table;
use DateTime;
use ArraycodesOrderNotifications\Database\TableConversation;
use ArraycodesOrderNotifications\Database\TableMessage;
use ArraycodesOrderNotifications\Database\TableUser;
use ArraycodesOrderNotifications\FacebookCommunicator\WebhookFacebookCommunicator;
use ArraycodesOrderNotifications\Options\AutoReplyOptionsManager;
use ArraycodesOrderNotifications\Options\OptionsCredentialsManager;
use ArraycodesOrderNotifications\ResponseMessage\ResponseMessageWhatsAppMessage;

defined( 'ABSPATH' ) || exit;

/**
 * Class WhatsAppResponse
 */
class WhatsAppResponseHandler {

	/**
	 * The single instance of the class.
	 *
	 * @var object
	 */
	private $response;

	/**
	 * The single instance of the class.
	 *
	 * @var string
	 */
	private $apiFunctions;

	/**
	 * The single instance of the class.
	 *
	 * @var \WC_Order $order
	 */
	private $order;

	/**
	 * FieldsResponseMessageSystem
	 *
	 * @var array
	 */
	private $fieldsResponseMessageSystem;

	/**
	 * FieldsResponseMessageStock
	 *
	 * @var array
	 */
	private $fieldsResponseMessageStock;

	/**
	 * The Identifier.
	 *
	 * @var string
	 */
	private $identifier;

	/**
	 * Constructor.
	 *
	 * @param string $response The return JSON Facebook After send message or when receive Webhook.
	 */
	public function __construct( string $response) {
		$this->response = $response;
	}

	/**
	 * SetApiFunctions.
	 *
	 * @param WebhookFacebookCommunicator $apiFunctions The class type where the message is being sent.
	 */
	public function setApiFunctions( WebhookFacebookCommunicator $apiFunctions) {
		$this->apiFunctions = $apiFunctions;
	}

	/**
	 * SetOrder.
	 *
	 * @param \WC_Order $order The order where the message is being sent.
	 */
	public function setOrder( \WC_Order $order) {
		$this->order = $order;
	}

	/**
	 * SetFieldsResponseMessageSystem.
	 *
	 * @param array $fieldsResponseMessageSystem The Fields.
	 */
	public function setFieldsResponseMessageSystem( array $fieldsResponseMessageSystem) {
		$this->fieldsResponseMessageSystem = $fieldsResponseMessageSystem;
	}

	/**
	 * SetFieldsResponseMessageStock.
	 *
	 * @param array $fieldsResponseMessageStock The Fields.
	 */
	public function setFieldsResponseMessageStock( array $fieldsResponseMessageStock) {
		$this->fieldsResponseMessageStock = $fieldsResponseMessageStock;
	}

	/**
	 * SetIdentifier.
	 *
	 * @param string $identifier The Identifier.
	 */
	public function setIdentifier( string $identifier) {
		$this->identifier = $identifier;
	}

	/**
	 * HandleResponse.
	 *
	 * @throws \Exception
	 */
	public function handleResponse() {
		$response_body = json_decode($this->response, true);

		$whatsAppReturnInterpreter = new WhatsAppReturnInterpreter($response_body);
		$event_type                = $whatsAppReturnInterpreter->getEventType();

		switch ($event_type) {
			case 'message_sent':
				/** New Order, New Update Order Status & Send Message System*/
				$wa_id      = $whatsAppReturnInterpreter->getWaId();
				$message_id = $whatsAppReturnInterpreter->getMessageId();
				if ('send_response_message_system' === $this->identifier) {
					$this->saveResponseMessageSystem($wa_id, $message_id);
				} else {
					$this->saveInNewOrder($wa_id, $message_id);
				}
				break;
			/** Update Status Message (sent,delivered,read,failed)*/
			case 'message_status_update':
				$status_id      = $whatsAppReturnInterpreter->getStatusDetail()['id'];
				$status_message = $whatsAppReturnInterpreter->getStatusDetail()['status'];
				$wa_id          = $whatsAppReturnInterpreter->getStatusDetail()['recipient_id'];
				$message_id     = $whatsAppReturnInterpreter->getStatusDetail()['id'];

				$tableMessage            = new TableMessage();
				$messages_is_user_system = $tableMessage->select(['id'], ['message_id' => $message_id]);

				if ($messages_is_user_system) {
					$tableMessage->mark_status_sender_type_system_database($message_id, $status_message);
				} else {
					$order = $this->searchInOrderByWaId($wa_id);
					if ($order instanceof \WC_Order) {
						$this->setOrder($order);

						$status_send_last_message = true;

						if ('failed' === $status_message) {
							$status_message           = $whatsAppReturnInterpreter->getStatusDetail()['errors'][0]['message'];
							$status_send_last_message = false;
						}
						$this->addNoteInOrder($status_message, $message_id);
						$this->addFieldStatusSendLastMessage($status_send_last_message);
					}
				}
				break;
			/** Receive a new message */
			case 'message_received':
				$contacts = $whatsAppReturnInterpreter->getContacts()[0];
				$messages = $whatsAppReturnInterpreter->getMessages()[0];

				$profile_name = $contacts['profile']['name'];
				$wa_id        = (int) $contacts['wa_id'];

				if ( in_array( $messages['type'], [ 'text', 'image', 'audio', 'video', 'document', 'reaction' ], true ) ) {
					$message_id = $messages['id'];
					$timestamp  = $messages['timestamp'];

					$order   = $this->searchInOrderByWaId( $wa_id );
					$user_id = null;

					if ( $order instanceof \WC_Order ) {
						$this->setOrder( $order );
						$user_id = $this->order->get_user_id();
					}

					$existing_user = ( new TableUser() )->get_by_wa_id( $wa_id );

					$user_data = [];

					if ( $user_id ) {
						$user_data = [
							'data'   => [
								'user_id' => $user_id,
								'wa_id'   => $wa_id,
								'name'    => $profile_name,
							],
							'format' => [ '%d', '%d', '%s' ],
						];
					} elseif ( ! $existing_user ) {
						$user_data = [
							'data'   => [
								'user_id' => $wa_id,
								'wa_id'   => $wa_id,
								'name'    => $profile_name,
							],
							'format' => [ '%d', '%d', '%s' ],
						];
					}

					$conversation = [
						'data'   => [
							'user_wa_id' => $wa_id,
							'started_at' => gmdate( 'Y-m-d H:i:s' ),
						],
						'format' => [ '%s', '%s' ],
					];

					if ( 'text' === $messages['type'] ) {
						$content      = $messages['text']['body'];
						$message_type = 'text';
					} elseif ( 'image' === $messages['type'] ) {
						$media_id     = $messages['image']['id'];
						$content      = $this->downloadWhatsAppMedia( $media_id, $wa_id, 'image' );
						$message_type = 'image';
					} elseif ( 'audio' === $messages['type'] ) {
						$media_id     = $messages['audio']['id'];
						$content      = $this->downloadWhatsAppMedia( $media_id, $wa_id, 'audio' );
						$message_type = 'audio';
					} elseif ( 'video' === $messages['type'] ) {
						$media_id     = $messages['video']['id'];
						$content      = $this->downloadWhatsAppMedia( $media_id, $wa_id, 'video' );
						$message_type = 'video';
					} elseif ( 'document' === $messages['type'] ) {
						$media_id     = $messages['document']['id'];
						$file_name    = $messages['document']['filename'] ?? null;
						$content      = $this->downloadWhatsAppMedia( $media_id, $wa_id, 'document', $file_name );
						$message_type = 'document';
					} elseif ( 'reaction' === $messages['type'] ) {
						$reacted_message_id = $messages['reaction']['message_id'];
						$emoji              = $messages['reaction']['emoji'] ?? '';
						$tableMessage       = new TableMessage();
						$tableMessage->save_reaction( $reacted_message_id, $emoji );
						break;
					}

					if ( ! empty( $content ) ) {
						$message = [
							'data'   => [
								'message_id'      => $message_id,
								'conversation_id' => 0,
								'wa_id'           => $wa_id,
								'message'         => $content,
								'sender_type'     => 'user',
								'date_time'       => gmdate( 'Y-m-d H:i:s' ),
								'message_type'    => $message_type,
							],
							'format' => [ '%s', '%d', '%d', '%s', '%s', '%s', '%s' ],
						];

						$business_phone_number_id = $whatsAppReturnInterpreter->getPhoneNumberId();

						$conversation_id = $this->saveMessage(
							$user_data,
							$conversation,
							$message,
							$business_phone_number_id
						);

						if ( $conversation_id > 0 && 'text' === $message_type ) {
							$data = [
								'fields' => [
									'message'        => '',
									'waId'           => $wa_id,
									'conversationId' => $conversation_id,
								],
							];
							$this->autoReply( $data );
						}
					}
				}

				break;
			case 'error':
				$error         = $whatsAppReturnInterpreter->getError()['error_data']['details'];
				$formatted_key = ucwords(str_replace('_', ' ', $this->identifier));
				if ($this->order instanceof \WC_Order) {
					$this->order->add_order_note('Error Sending WhatsApp Message ' . $formatted_key . ': ' . $error);
					$this->addFieldStatusSendLastMessage(false);
				}
				break;
		}
	}

	/**
	 * Save_in_order.
	 *
	 * @param string $wa_id
	 * @param string $message_id
	 */
	private function saveInNewOrder( string $wa_id, string $message_id) {
		$existing_data_whatsapp_notification_message_id = $this->order->get_meta('whatsapp_notification_message_id', true);
		$existing_data_whatsapp_notification_message_id = json_decode($existing_data_whatsapp_notification_message_id, true);
		if (!is_array($existing_data_whatsapp_notification_message_id)) {
			$existing_data_whatsapp_notification_message_id = [];
		}
		$existing_data_whatsapp_notification_message_id[$this->identifier] = [
			'message_id' => $message_id
		];

		$this->order->update_meta_data('whatsapp_notification_message_id', wp_json_encode($existing_data_whatsapp_notification_message_id));
		$this->order->update_meta_data('whatsapp_notification_wa_id', $wa_id);
		$this->order->save();

	}

	/**
	 * SearchInOrderByWaId.
	 *
	 * @param string $wa_id
	 * @return false|\WC_Order
	 */
	private function searchInOrderByWaId( string $wa_id) {
		$args = array(
			'limit'        => 1,
			'return'       => 'objects',
			'orderby'      => 'date',
			'order'        => 'DESC',
			'meta_key'     => 'whatsapp_notification_wa_id',
			'meta_value'   => $wa_id,
			'meta_compare' => '='
		);

		$orders = wc_get_orders( $args );

		if ( !empty( $orders ) ) {
			return reset( $orders );
		}
		return false;
	}

	/**
	 * AddNoteInOrder.
	 *
	 * @param string $status_message
	 * @param string $message_id
	 */
	private function addNoteInOrder( string $status_message, string $message_id) {
		$whatsapp_notification_message_id = $this->order->get_meta( 'whatsapp_notification_message_id', true );
		$whatsapp_notification_message_id = json_decode($whatsapp_notification_message_id, true);

		if (is_array($whatsapp_notification_message_id)) {
			foreach ($whatsapp_notification_message_id as $key => $data) {
				if (isset($data['message_id']) && $data['message_id'] === $message_id) {
					$formatted_key = ucwords(str_replace('_', ' ', $key));
					$this->order->add_order_note('WhatsApp Message ' . $formatted_key . ': ' . $status_message);
				}
			}
		}
	}

	/**
	 * AddFieldStatusSendLastMessage whatsapp_notification_status_send_last_message.
	 *
	 * @param bool $status
	 */
	private function addFieldStatusSendLastMessage( bool $status) {
		$this->order->update_meta_data('whatsapp_notification_status_send_last_message', $status);
		$this->order->save();
	}

	/**
	 * SaveMessage.
	 *
	 * @param array $user
	 * @param array $conversation
	 * @param array $message
	 * @param string $business_phone_number_id
	 * @return bool|int
	 */
	public function saveMessage( array $user, array $conversation, array $message, string $business_phone_number_id) {
		if (! empty($user)) {
			$tableUser = new TableUser();
			$tableUser->createOrUpdate($user, []);
		}

		$tableConversation = new TableConversation();
		$conversationId    = $tableConversation->create($conversation);

		$message['data']['conversation_id'] = $conversationId;
		$tableMessage                       = new TableMessage();
		$tableMessage->create($message);

		return $conversationId;

	}

	/**
	 * Save_in_order.
	 *
	 * @param string $wa_id
	 * @param string $message_id
	 */
	public function saveResponseMessageSystem( string $wa_id, string $message_id) {
		$message         = $this->fieldsResponseMessageSystem['message'];
		$conversation_id = $this->fieldsResponseMessageSystem['conversationId'];

		$message = array(
			'data' => array(
				'message_id'=> $message_id,
				'conversation_id'=> $conversation_id,
				'wa_id' => $wa_id,
				'message' => $message,
				'sender_type' => 'system',
				'status' => 1,
				'date_time' => gmdate('Y-m-d H:i:s'),
			),
			'format' => array(
				'%s',
				'%d',
				'%d',
				'%s',
				'%s',
				'%d',
				'%s',
			)
		);

		$tableMessage = new TableMessage();
		$tableMessage->create($message);

	}

	/**
	 * Handles automatic replies based on the provided data.
	 *
	 * @param array $data An associative array containing the data necessary for auto-reply logic.
	 *
	 * @return void
	 * @throws \Exception
	 */
	private function autoReply( array $data) {
		$autoReplyOptionsManager   = new AutoReplyOptionsManager();
		$options_whatsapp_template = $autoReplyOptionsManager->get_options_whatsapp_template();

		if (empty($options_whatsapp_template['whatsapp_auto_reply'])) {
			return;
		}

		$autoReplyMessage = $options_whatsapp_template['whatsapp_auto_reply'];
		$tableMessage     = new TableMessage();
		$messages         = $tableMessage->select(
			['id', 'date_time'],
			[
				'wa_id' => $data['fields']['waId'],
				'message'         => $autoReplyMessage,
			],
			'date_time',
			'DESC'
		);

		$now                = new DateTime();
		$twentyFourHoursAgo = ( clone $now )->modify('-24 hours');

		foreach ($messages as $message) {
			$messageTime = new DateTime($message['date_time']);
			if ($messageTime >= $twentyFourHoursAgo) {
				return;
			}
		}


		$data['fields']['message']      = $autoReplyMessage;
		$optionsCredentialsManager      = new OptionsCredentialsManager();
		$responseMessageWhatsAppMessage = new ResponseMessageWhatsAppMessage($optionsCredentialsManager);
		$responseMessageWhatsAppMessage->sendMessage($data['fields']);
	}

	/**
	 * Downloads WhatsApp media by its media ID and saves it to a specific location.
	 *
	 * @param string      $media_id   The ID of the media to download.
	 * @param string      $wa_id      The WhatsApp ID related to the media.
	 * @param string      $media_type The type of media: 'image', 'audio', 'video', 'document'.
	 * @param string|null $file_name  Optional original filename (used for documents).
	 *
	 * @return string|null Returns the URL of the downloaded media file, or null if the download fails.
	 */
	private function downloadWhatsAppMedia( string $media_id, string $wa_id, string $media_type = 'image', ?string $file_name = null ): ?string {
		$optionsCredentialsManager             = new OptionsCredentialsManager();
		$options_whatsapp_settings_credentials = $optionsCredentialsManager->get_options_whatsapp_settings_credentials();

		$url = 'https://graph.facebook.com/v22.0/' . $media_id;

		$headers = array(
			'Authorization' => 'Bearer ' . $options_whatsapp_settings_credentials['whatsapp_api_token'],
			'Content-Type'  => 'application/json',
		);

		$response = wp_remote_get( $url, array( 'headers' => $headers ) );

		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $data['url'] ) ) {
			return null;
		}

		$file_response = wp_remote_get(
			$data['url'],
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $options_whatsapp_settings_credentials['whatsapp_api_token'],
				),
			)
		);

		if ( is_wp_error( $file_response ) ) {
			return null;
		}

		$image_data  = wp_remote_retrieve_body( $file_response );
		$upload_dir  = wp_upload_dir();
		$user_folder = trailingslashit( $upload_dir['basedir'] ) . 'whatsapp/' . $wa_id . '/';

		if ( ! file_exists( $user_folder ) ) {
			wp_mkdir_p( $user_folder );
		}

		$extensions = [
			'image'    => 'jpg',
			'audio'    => 'ogg',
			'video'    => 'mp4',
			'document' => 'pdf',
		];
		$extension  = $extensions[ $media_type ] ?? 'bin';

		if ( $file_name && 'document' === $media_type ) {
			$file_name = sanitize_file_name( $file_name );
			$base_name = pathinfo( $file_name, PATHINFO_FILENAME );
			$ext       = strtolower( pathinfo( $file_name, PATHINFO_EXTENSION ) );
			if ( ! in_array( $ext, [ 'pdf', 'doc', 'docx' ], true ) ) {
				return null;
			}
			$file_name = 'whatsapp_' . gmdate( 'Ymd_His' ) . '_' . $wa_id . '_' . $base_name . '.' . $ext;
		} else {
			$file_name = 'whatsapp_' . gmdate( 'Ymd_His' ) . '_' . $wa_id . '.' . $extension;
		}
		$file_path = $user_folder . $file_name;

		global $wp_filesystem;

		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		if ( ! $wp_filesystem->put_contents( $file_path, $image_data, FS_CHMOD_FILE ) ) {
			return null;
		}

		return trailingslashit( $upload_dir['baseurl'] ) . 'whatsapp/' . $wa_id . '/' . $file_name;
	}

}
