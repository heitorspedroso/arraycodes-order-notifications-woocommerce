<?php
/**
 * WhatsAppReturnInterpreter
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\WhatsAppResponse;

defined( 'ABSPATH' ) || exit;

/**
 * Class WhatsAppReturnInterpreter
 */
class WhatsAppReturnInterpreter {

	/**
	 * The single instance of the class.
	 *
	 * @var array
	 */
	private $whatsAppResponseData;

	/**
	 * The type of event being handled.
	 *
	 * @var string
	 */
	protected $eventType = 'unknown';

	/**
	 * Statuses.
	 *
	 * @var array
	 */
	protected $statuses = [];

	/**
	 * Messages.
	 *
	 * @var array
	 */
	protected $messages = [];

	/**
	 * Contacts.
	 *
	 * @var array
	 */
	protected $contacts = [];

	/**
	 * MetaData.
	 *
	 * @var array
	 */
	protected $metadata = [];

	/**
	 * Phone number ID.
	 *
	 * @var mixed
	 */
	protected $phoneNumberId = null;

	/**
	 * Display phone number.
	 *
	 * @var mixed
	 */
	protected $displayPhoneNumber = null;

	/**
	 * Error.
	 *
	 * @var mixed
	 */
	protected $error;

	/**
	 * Constructs a new instance and initializes properties based on the structure
	 * of the provided WhatsApp response data.
	 *
	 * @param array $whatsAppResponseData The response data from WhatsApp to be processed.
	 *
	 * @return void
	 */
	public function __construct( array $whatsAppResponseData) {
		$this->whatsAppResponseData = $whatsAppResponseData;

		// Handle error response
		if (isset($whatsAppResponseData['error'])) {
			$this->eventType = 'error';
			$this->error     = $whatsAppResponseData['error'];
			return;
		}

		// Handle message sent response
		if (isset($whatsAppResponseData['messaging_product']) && 'whatsapp' === $whatsAppResponseData['messaging_product'] &&
			isset($whatsAppResponseData['contacts'], $whatsAppResponseData['messages'])) {
			$this->eventType = 'message_sent';
			$this->contacts  = $whatsAppResponseData['contacts'];
			$this->messages  = $whatsAppResponseData['messages'];
			return;
		}

		// Handle webhook responses (status updates or received messages)
		if (isset($whatsAppResponseData['object']) && 'whatsapp_business_account' === $whatsAppResponseData['object'] &&
			isset($whatsAppResponseData['entry'][0]['changes'][0]['value'])) {
			$value = $whatsAppResponseData['entry'][0]['changes'][0]['value'];

			$this->metadata = $value['metadata'] ?? [];
			$this->contacts = $value['contacts'] ?? [];
			$this->messages = $value['messages'] ?? [];
			$this->statuses = $value['statuses'] ?? [];

			if (!empty($this->statuses)) {
				$this->eventType = 'message_status_update';
			} elseif (!empty($this->messages)) {
				$this->eventType = 'message_received';
			}
		}
	}

	public function getEventType(): string {
		return $this->eventType;
	}

	public function getStatuses(): array {
		return $this->statuses;
	}

	public function getMessages(): array {
		return $this->messages;
	}

	public function getContacts(): array {
		return $this->contacts;
	}

	public function getPhoneNumberId(): ?string {
		return $this->metadata['phone_number_id'];
	}

	public function getDisplayPhoneNumber(): ?string {
		return $this->metadata['display_phone_number'];
	}

	public function getStatusDetail(): array {
		return $this->statuses[0] ?? [];
	}

	public function getWaId(): ?string {
		return !empty($this->contacts) ? ( $this->contacts[0]['wa_id'] ?? null ) : null;
	}
	public function getMessageId(): ?string {
		return !empty($this->messages) ? ( $this->messages[0]['id'] ?? null ) : null;
	}

	public function getError(): array {
		return $this->error;
	}
}
