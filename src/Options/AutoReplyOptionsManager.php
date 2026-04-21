<?php
/**
 * AutoReplyOptionsManager
 *
 * @version 1.0.0
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\Options;

defined( 'ABSPATH' ) || exit;

/**
 * Class AutoReplyOptionsManager
 */
class AutoReplyOptionsManager extends OptionsManager {
	public function __construct() {
		parent::__construct(array(
			'whatsapp_auto_reply',
		));
	}

	/**
	 * Retrieves the options for the WhatsApp template settings for new orders.
	 *
	 * @return mixed The options for the WhatsApp template settings.
	 */
	public function get_options_whatsapp_template() {
		return parent::get_options_whatsapp_settings_new_order();
	}
}
