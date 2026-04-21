<?php
/**
 * FieldsPreparatorInterface Interface
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\WhatsAppMessageCenter;

defined( 'ABSPATH' ) || exit;

interface FieldsPreparatorInterface {
	public function prepareFields( $data, array $options_whatsapp_settings_credentials): array;
}
