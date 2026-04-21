<?php
/**
 * InputChekedOptionsManager
 *
 * @version 1.0.0
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\Options;

defined( 'ABSPATH' ) || exit;

/**
 * Class InputCheckedOptionsManager
 */
class InputCheckedOptionsManager extends OptionsManager {
	/**
	 * Start up
	 */
	public function __construct() {
		parent::__construct(array(
			'whatsapp_api_checked_input',
		));
	}
}
