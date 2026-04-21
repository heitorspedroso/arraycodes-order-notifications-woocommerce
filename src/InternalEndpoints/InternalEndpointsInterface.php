<?php
/**
 * InternalEndpointsInterface Interface
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\InternalEndpoints;

defined( 'ABSPATH' ) || exit;

interface InternalEndpointsInterface {
	public function new_order( $request): \WP_REST_Response;
	public function update_order( $request): \WP_REST_Response;
	public function delete_order( $request): \WP_REST_Response;
	public function get_order( $request): array;
}
