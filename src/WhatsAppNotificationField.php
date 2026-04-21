<?php
/**
 * WhatsAppNotificationField Init
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications;

use ArraycodesOrderNotifications\Options\InputCheckedOptionsManager;
use Automattic\WooCommerce\Blocks\Package;
use Automattic\WooCommerce\Blocks\Domain\Services\CheckoutFields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WhatsAppNotificationField' ) ) :
	/**
	 * Class WhatsAppNotificationField
	 */
	class WhatsAppNotificationField {

		/**
		 * Instance of this class.
		 *
		 * @var self|null Instance of the class.
		 */
		protected static $instance = null;

		/**
		 * The single instance of the class.
		 *
		 * @var InputCheckedOptionsManager|null Manages input checked options.
		 */
		private $inputCheckedOptionsManager;

		/**
		 * Start up
		 */
		private function __construct() {
			$this->inputCheckedOptionsManager = new InputCheckedOptionsManager();
			//CLASSIC CHECKOUT
			add_action( 'wp_enqueue_scripts', array( $this, 'whatsapp_notification_enqueue_script' ), 25 );
			add_action( 'woocommerce_checkout_fields', array( $this, 'whatsapp_connect_add_checkout_field' ), 10, 1 );
			add_action('woocommerce_checkout_update_order_review', array($this, 'whatsapp_connect_update_order_review'), 10, 1);
			add_filter( 'woocommerce_checkout_update_order_meta', array( $this, 'whatsapp_connect_update_order_meta' ), 10 );
			//BLOCK CHECKOUT
			add_action( 'woocommerce_init', array( $this, 'woocommerce_register_additional_checkout_field' ), 10 );
			add_action( 'woocommerce_store_api_checkout_update_order_meta', array( $this, 'whatsapp_store_api_connect_update_order_meta' ), 10, 1 );
			add_filter( 'woocommerce_get_default_value_for_notifications-with-whatsapp/whatsapp_connect_checkbox', array( $this, 'woocommerce_set_default_additional_checkout_field' ), 10, 3);
		}

		/**
		 * Whatsapp_notification_enqueue_script
		 *
		 */
		public function whatsapp_notification_enqueue_script() {
			if ( ! has_block('woocommerce/checkout') ) {
				$script = "jQuery(document).ready(function($) { $('form.checkout').on('change', 'input[name=\"billing_whatsapp_connect_checkbox\"]', function() { $(document.body).trigger('update_checkout'); }); });";
				wp_add_inline_script( 'jquery', $script );
			}
			if ( has_block('woocommerce/checkout') && WC()->version <= '9.1.4' ) {
				$script = "document.body.addEventListener('click', function(e) { var checkboxInput = e.target.closest('.wc-block-components-checkbox__input'); var checkbox = document.getElementById('billing-notifications-with-whatsapp-whatsapp_connect_checkbox'); if (checkboxInput && checkbox && !checkbox.checked) { checkbox.click(); checkbox.click(); } });";
				wp_add_inline_script( 'wc-blocks-checkout', $script );
			}
		}

		/**
		 * Return an instance of this class.
		 *
		 * @return self
		 */
		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Adds a checkout field for WhatsApp notifications.
		 *
		 * @param array $fields Fields Array.
		 *
		 */
		public function whatsapp_connect_add_checkout_field( array $fields ) {
			$input_checked_options_manager       = $this->inputCheckedOptionsManager->get_options_whatsapp_settings_new_order();
			$input_checked_options_manager_field = false;
			if (isset($input_checked_options_manager['whatsapp_api_checked_input'])) {
				$input_checked_options_manager_field = $input_checked_options_manager['whatsapp_api_checked_input'];
			}

			$fields['billing']['billing_whatsapp_connect_checkbox'] = array(
				'type'     => 'checkbox',
				'class'    => array('input-checkbox'),
				'label'    => __('I want to receive order notifications via WhatsApp.', 'arraycodes-order-notifications-woocommerce'),
				'default'  => $input_checked_options_manager_field,
				'checked'=> $input_checked_options_manager_field,
				'required'    => false,
			);

			return $fields;
		}

		/**
		 * Whatsapp_connect_update_order_review.
		 *
		 * @param string $post_data Fields Array.
		 *
		 * @return void
		 */
		public function whatsapp_connect_update_order_review( string $post_data) {
			parse_str($post_data, $post_data_array);
			if (isset($post_data_array['billing_whatsapp_connect_checkbox'])) {
				WC()->session->set('billing_whatsapp_connect_checkbox', $post_data_array['billing_whatsapp_connect_checkbox']);
			} else {
				WC()->session->set('billing_whatsapp_connect_checkbox', false);
			}
		}

		/**
		 * Update order metadata for WhatsApp notification fields opt-in.
		 *
		 * @param int $order_id The ID of the order being updated.
		 */
		public function whatsapp_connect_update_order_meta( int $order_id ) {
			$whatsapp_connect_checkbox = filter_input(INPUT_POST, 'billing_whatsapp_connect_checkbox', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
			if ( null !== $whatsapp_connect_checkbox) {
				$order = wc_get_order( $order_id );
				$order->update_meta_data( 'whatsapp_notification_fields_optin', $whatsapp_connect_checkbox );
			} else {
				$order = wc_get_order( $order_id );
				$order->update_meta_data( 'whatsapp_notification_fields_optin', false );
			}
			$order->save();
		}

		/**
		 * Woocommerce_register_additional_checkout_field.
		 *
		 * @throws \Exception
		 */
		public function woocommerce_register_additional_checkout_field() {
			global $woocommerce;
			if ( version_compare( $woocommerce->version, '7.4.9', '<=' ) ) {
				return false;
			}
			woocommerce_register_additional_checkout_field(
				array(
					'id'       => 'notifications-with-whatsapp/whatsapp_connect_checkbox',
					'label'    => __('I want to receive order notifications via WhatsApp.', 'arraycodes-order-notifications-woocommerce'),
					'location' => 'address',
					'type'     => 'checkbox',
					'sanitize_callback' => function( $field_value ) {
						return (bool) $field_value;
					},
					'validate_callback' => function( $field_value ) {
						return (bool) $field_value;
					},
				)
			);
		}

		/**
		 * Woocommerce_set_default_additional_checkout_field.
		 *
		 * @param string $value Value.
		 * @param array $group Group.
		 * @param object $wc_object Wc_Object.
		 * @throws \Exception
		 */
		public function woocommerce_set_default_additional_checkout_field( $value, $group, $wc_object) {
			$input_checked_options_manager       = $this->inputCheckedOptionsManager->get_options_whatsapp_settings_new_order();
			$input_checked_options_manager_field = false;
			if (isset($input_checked_options_manager['whatsapp_api_checked_input'])) {
				$input_checked_options_manager_field = $input_checked_options_manager['whatsapp_api_checked_input'];
			}
			return (bool) $input_checked_options_manager_field;
		}

		/**
		 * Update order metadata for WhatsApp notification fields opt-in.
		 *
		 * @param \WC_Order $order The ID of the order being updated.
		 * @return void
		 * @throws \Exception
		 */
		public function whatsapp_store_api_connect_update_order_meta( \WC_Order $order ) {
			$checkout_fields           = Package::container()->get( CheckoutFields::class );
			$whatsapp_connect_checkbox = $checkout_fields->get_field_from_object( 'notifications-with-whatsapp/whatsapp_connect_checkbox', $order, 'billing' );
			$order->update_meta_data( 'whatsapp_notification_fields_optin', $whatsapp_connect_checkbox );
			$order->save();
		}
	}
endif;