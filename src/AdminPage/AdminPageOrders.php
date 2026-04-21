<?php
/**
 * AdminPageOrders Init
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\AdminPage;

defined( 'ABSPATH' ) || exit;

/**
 * Class AdminPageOrders
 */
final class AdminPageOrders {

	/**
	 * The single instance of the class.
	 *
	 * @var self|null Instance of the class.
	 */
	protected static $instance = null;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter('manage_edit-shop_order_columns', array($this, 'addWhatsAppColumn'), 10, 1);
		add_filter('manage_woocommerce_page_wc-orders_columns', array($this, 'addWhatsAppColumn'), 10, 1);
		add_action('manage_shop_order_posts_custom_column', array($this, 'populateWhatsAppColumn'), 10, 2);
		add_action('manage_woocommerce_page_wc-orders_custom_column', array($this, 'populateWhatsAppColumn'), 10, 2);
		add_action('admin_enqueue_scripts', array($this, 'injectCustomCssForOrderStatus'));
	}

	/**
	 * Adds a custom "whatsapp_column" to the WordPress admin list table for orders.
	 *
	 * @param array $columns An associative array of existing columns in the table.
	 * @return array The modified array of columns with the added "whatsapp_column".
	 */
	public function addWhatsAppColumn( array $columns): array {
		$new_columns = array();
		foreach ($columns as $key => $column) {
			$new_columns[$key] = $column;
			if ('wc_actions' === $key) {
				$new_columns['whatsapp_column'] = 'WhatsApp Message';
			}
		}
		return $new_columns;
	}

	/**
	 * Populates the custom "whatsapp_column" in the WordPress admin list table for orders.
	 *
	 * @param string $column The name of the column being processed.
	 * @param object $post_id The ID of the order post being processed.
	 * @return void Outputs a visual indicator of whether the "whatsapp_notification_status_send_last_message" meta field is set.
	 */
	public function populateWhatsAppColumn( string $column, $post_id) {
		if ('whatsapp_column' === $column) {
			$order                              = wc_get_order($post_id);
			$whatsapp_notification_fields_optin = $order->get_meta('whatsapp_notification_fields_optin');
			if ($whatsapp_notification_fields_optin) {
				$whatsapp_notification_status_send_last_message = $order->get_meta('whatsapp_notification_status_send_last_message');
				if ('0' === $whatsapp_notification_status_send_last_message) {
					printf ('<span class="whatsapp-status status-failed"><span>%s</span></span>', esc_html(__(
						'Failed',
						'arraycodes-order-notifications-woocommerce'
					)));
				}
				if ('1' === $whatsapp_notification_status_send_last_message) {
					printf ('<span class="whatsapp-status status-sent"><span>%s</span></span>', esc_html(__(
						'Sent',
						'arraycodes-order-notifications-woocommerce'
					)));
				}
			}
		}
	}

	public function injectCustomCssForOrderStatus() {
		if ( is_admin() && class_exists( 'WooCommerce' ) ) {
			$screen = get_current_screen();
			if ( $screen && 'edit-shop_order' === $screen->id || $screen && 'woocommerce_page_wc-orders' === $screen->id || $screen && 'shop_order' === $screen->id ) {
				$css = '.column-whatsapp_column { text-align: center !important; }
					.whatsapp-status { font-weight: bold; border: 2px solid; border-radius: 5px; width: 65px; text-align: center; display: inline-block; line-height: 25px; }
					.whatsapp-status.status-failed { color: red; border-color: red; }
					.whatsapp-status.status-sent { color: #558b1c; border-color: #558b1c; }';
				wp_register_style( 'arraycodes-order-notifications-admin', false );
				wp_enqueue_style( 'arraycodes-order-notifications-admin' );
				wp_add_inline_style( 'arraycodes-order-notifications-admin', $css );
			}
		}
	}

}
