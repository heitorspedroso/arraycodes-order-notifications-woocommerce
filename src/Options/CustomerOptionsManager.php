<?php
/**
 * CustomerOptionsManager
 *
 * @version 1.0.0
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\Options;

defined( 'ABSPATH' ) || exit;

/**
 * Class CustomerOptionsManager
 */
class CustomerOptionsManager extends OptionsManager {
	public function __construct() {
		parent::__construct(array(
			'whatsapp_template_new_order_customer_id',
			'whatsapp_template_new_order_customer_status',
			'whatsapp_template_new_order_customer_name',
			'whatsapp_template_new_order_customer_language',
			'whatsapp_template_new_order_customer_header',
			'whatsapp_template_new_order_customer_header_type',
			'whatsapp_template_new_order_customer_footer',
			'whatsapp_template_new_order_customer_body'
		));
	}

	public function get_options_whatsapp_template() {
		return parent::get_options_whatsapp_settings_new_order();
	}
}
