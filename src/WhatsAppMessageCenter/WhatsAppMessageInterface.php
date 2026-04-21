<?php
/**
 * WhatsAppMessageInterface Interface
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\WhatsAppMessageCenter;

use ArraycodesOrderNotifications\Options\OptionsCredentialsManager;

defined( 'ABSPATH' ) || exit;

interface WhatsAppMessageInterface {
	public function __construct( OptionsCredentialsManager $optionsCredentialsManager);
	public function sendMessage( array $fieldsOrder);
	public function setStatus( string $status);
}
