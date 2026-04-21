<?php
/**
 * WhatsAppCommunicator
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\WhatsAppMessageCenter;

use ArraycodesOrderNotifications\Options\OptionsCredentialsManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WhatsAppCommunicator
 */
class WhatsAppCommunicator {
	/**
	 * OptionsCredentialsManager.
	 *
	 * @var OptionsCredentialsManager
	 */
	private $optionsCredentialsManager;

	/**
	 * OrderFieldsPreparator.
	 *
	 * @var FieldsPreparatorInterface
	 */
	private $fieldsPreparator;

	/**
	 * WhatsAppMessageInterface.
	 *
	 * @var WhatsAppMessageInterface
	 */
	private $message;

	/**
	 * Constructor.
	 *
	 * @param OptionsCredentialsManager $optionsCredentialsManager The options manager instance.
	 * @param FieldsPreparatorInterface $fieldsPreparator FieldsPreparatorInterface .
	 * @param WhatsAppMessageInterface $message WhatsAppMessageInterface.
	 */
	public function __construct( OptionsCredentialsManager $optionsCredentialsManager, FieldsPreparatorInterface $fieldsPreparator, WhatsAppMessageInterface $message) {
		$this->optionsCredentialsManager = $optionsCredentialsManager;
		$this->message                   = $message;
		$this->fieldsPreparator          = $fieldsPreparator;
	}

	/**
	 * SendMessage.
	 *
	 * @param array|object $data The options manager instance.
	 */
	public function sendMessage( $data ) {
		$options_whatsapp_settings_credentials = $this->optionsCredentialsManager->get_options_whatsapp_settings_credentials();
		$fieldsOrder                           = $this->fieldsPreparator->prepareFields($data, $options_whatsapp_settings_credentials);
		if (!empty($fieldsOrder)) {
			$this->message->sendMessage($fieldsOrder);
		}
	}

}
