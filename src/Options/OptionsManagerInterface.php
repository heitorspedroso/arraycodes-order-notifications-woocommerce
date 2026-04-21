<?php
/**
 * OptionsManagerInterface Interface
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\Options;

use ArraycodesOrderNotifications\Options\OptionsCredentialsManager;
use ArraycodesOrderNotifications\Options\OptionsManager;

defined( 'ABSPATH' ) || exit;

interface OptionsManagerInterface {
	public function __construct( $options );
	public function get_options_whatsapp_settings_new_order();
}
