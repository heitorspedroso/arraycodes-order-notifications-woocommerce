<?php
/**
 * FacebookCommunicatorInterface Interface
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\FacebookCommunicator;

defined( 'ABSPATH' ) || exit;

interface FacebookCommunicatorInterface {
	public function get_class_type(): string;
	public function create_new_order( array $fields): array;
	public function update_new_order( array $fields): array;
	public function delete_new_order( array $fields): array;
	public function get_order(): array;
}
