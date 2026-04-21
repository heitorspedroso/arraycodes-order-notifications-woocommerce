<?php
/**
 * SellerOptionsManager
 *
 * @version 1.0.0
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\Options;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'SellerOptionsManager' ) ) :
	/**
	 * Class SellerOptionsManager
	 */
	class SellerOptionsManager extends OptionsManager {
		public function __construct() {
			parent::__construct(array(
				'whatsapp_template_new_order_id',
				'whatsapp_template_new_order_status',
				'whatsapp_template_new_order_name',
				'whatsapp_template_new_order_language',
				'whatsapp_template_new_order_header',
				'whatsapp_template_new_order_footer',
				'whatsapp_template_new_order_body'
			));
		}
	}
endif;
