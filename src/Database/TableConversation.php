<?php
/**
 * TableConversation
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TableConversation
 */
class TableConversation extends Record {

	/**
	 * Init constructor.
	 */
	public function __construct() {
		parent::__construct('notifications_with_whatsapp_conversations');
	}

}
