<?php
/**
 * StatusCustomerOptionsManager
 *
 * @version 1.0.0
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\Options;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'StatusCustomerOptionsManager' ) ) :
	/**
	 * Class StatusCustomerOptionsManager
	 */
	class StatusCustomerOptionsManager extends OptionsManager {
		public function __construct() {
			parent::__construct(array(
				'whatsapp_template_update_order_status_customer',
			));
		}
	}
endif;
