<?php
/**
 * OrderFieldsPreparator
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\WhatsAppMessageCenter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class OrderFieldsPreparator
 */
class OrderFieldsPreparator implements FieldsPreparatorInterface {

	/**
	 * Prepare Fields Order.
	 *
	 * @param \WC_Order $data The options manager instance.
	 * @param array $options_whatsapp_settings_credentials The options manager instance.
	 */
	public function prepareFields( $data, array $options_whatsapp_settings_credentials): array {
		if ( empty($options_whatsapp_settings_credentials['whatsapp_api_token']) && empty($options_whatsapp_settings_credentials['whatsapp_api_phone_number']) && empty($options_whatsapp_settings_credentials['whatsapp_api_phone_number_to']) ) {
			return array();
		}

		$transaction_id       = $data->get_id();
		$transaction_total    = $data->get_total();
		$transaction_tax      = $data->get_total_tax();
		$transaction_shipping = $data->get_shipping_total();

		$first_name = $data->get_billing_first_name();
		$last_name  = $data->get_billing_last_name();
		$name       = $first_name . ' ' . $last_name;

		$street      = $data->get_billing_address_1();
		$city        = $data->get_billing_city();
		$region      = $data->get_billing_state();
		$postal_code = $data->get_billing_postcode();

		$address = $street . ', ' . $city . ' - ' . $region . ' - ' . $postal_code;
		/**
		 * Fired before sending a message to WhatsApp
		 * This hook contains a string with the user's address that should be the return of the function with the modified value of the address field
		 * This hook contains an Order object (WC_Order) related to the message that will be fired.
		 * It can be used to change the value of the address field in a message.
		 *
		 * @param string $address String address to be changed
		 * @param object $data Order (Wc_Order)
		 *
		 * @since 3.1.0
		 *
		 * @see https://woocommerce.com/document/notifications-with-whatsapp/
		 */
		$address = apply_filters( 'notifications_with_whatsapp_field_user_address_message', $address, $data );

		$phone = $data->get_billing_phone();

		$product_data = [];
		foreach ( $data->get_items() as $item ) {
			if ( $item instanceof \WC_Order_Item_Product ) {
				$sku   = $item->get_product_id();
				$price = $data->get_item_total($item);

				$product_categories = get_the_terms($sku, 'product_cat');
				if ( ( is_array($product_categories) ) && ( count($product_categories) > 0 ) ) {
					$product_cat = array_pop($product_categories);
					$product_cat = $product_cat->name;
				} else {
					$product_cat = '';
				}

				$product_data[] = [
					'name'     => $item->get_name(),
					'id'       => $sku,
					'price'    => $price,
					'category' => $product_cat,
					'quantity' => $item->get_quantity()
				];
			}
		}

		$coupons = $data->get_coupon_codes();
		$coupon  = '';
		if ( is_array($coupons) ) {
			foreach ( $coupons as $item ) {
				$coupon = $item;
			}
		}

		$discount     = $data->get_discount_total();
		$date_created = $data->get_date_created();

		$payment_name = $data->get_payment_method_title();

		$shippings = [];
		foreach ( $data->get_items( 'shipping' ) as $shipping_item ) {
			$shippings[] = $shipping_item->get_name();
		}
		$shipping_name = $shippings[0] ?? '';


		$products = '';
		foreach ( $product_data as $index => $product ) {
			$products .= $product['quantity'] . 'x ' . $product['name'];

			if ( $index < count($product_data) - 1 ) {
				$products .= ' | ';
			}
		}

		$custom_fields = array();
		/**
		 *  Fired before sending a message to WhatsApp
		 *  This hook contains an empty array that must be the function's return with the desired value to be inserted in the custom field
		 *  This hook contains an Order object (WC_Order) related to the message that will be triggered.
		 *  Can be used to insert a non-default custom_field into the message.
		 *
		 * @param array $custom_fields Empty array to be changed
		 * @param object $data Order (Wc_Order)
		 *
		 * @since 2.7.0
		 *
		 * @see https://woocommerce.com/document/notifications-with-whatsapp/
		 */
		$custom_fields = apply_filters( 'notifications_with_whatsapp_before_send_fields_message', $custom_fields, $data );

		return [
			'transaction_id' => (string) $transaction_id,
			'date_created' => $date_created,
			'products' => $products,
			'transaction_tax' => $transaction_tax,
			'transaction_shipping' => $transaction_shipping,
			'transaction_total' => $transaction_total,
			'shipping_name' => $shipping_name,
			'payment_name' => $payment_name,
			'custom_fields' => $custom_fields,
			'order' => $data,
			'user_first_name' => $first_name,
			'user_name' => $name,
			'user_phone' => $phone,
			'user_address' => $address,
			'whatsapp_api_phone_number' =>$options_whatsapp_settings_credentials['whatsapp_api_phone_number'] ,
			'whatsapp_api_token' =>$options_whatsapp_settings_credentials['whatsapp_api_token'],
			'whatsapp_api_phone_number_to' =>$options_whatsapp_settings_credentials['whatsapp_api_phone_number_to']
		];
	}

}
