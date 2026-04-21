<?php
/**
 * LoggerInterface Interface
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\Logger;

defined( 'ABSPATH' ) || exit;

interface LoggerInterface {
	public function log( $level, $message, array $context = array());
	public function save_log_api( string $title, array $return);
}
