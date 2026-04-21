<?php
/**
 * OptionsManager
 *
 * @version 1.0.0
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\Options;

defined( 'ABSPATH' ) || exit;

/**
 * Class OptionsManager
 */
class OptionsManager {
	private $options;

	/**
	 * Start up
	 */
	public function __construct( $options) {
		$this->options = $options;
	}

	/**
	 * Get_options_whatsapp_settings_new_order.
	 *
	 */
	public function get_options_whatsapp_settings_new_order() {
		$options_whatsapp_settings_new_order = get_option( 'arraycodes_on_fields_free' );
		if ( isset( $options_whatsapp_settings_new_order ) ) {
			if (is_array( $options_whatsapp_settings_new_order )) {
				$options_whatsapp_settings_new_order = array_intersect_key($options_whatsapp_settings_new_order, array_flip($this->options));
			}
		}
		return $options_whatsapp_settings_new_order;
	}
}
