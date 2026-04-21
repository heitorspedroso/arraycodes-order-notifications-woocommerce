<?php
/**
 * BaseWhatsAppMessage
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\WhatsAppMessageCenter;

use ArraycodesOrderNotifications\Options\OptionsCredentialsManager;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'ArraycodesOrderNotifications\BaseWhatsAppMessage' ) ) :
	/**
	 * Class BaseWhatsAppMessage
	 */
	abstract class BaseWhatsAppMessage implements WhatsAppMessageInterface {

		/**
		 *  SendMessage
		 *
		 * @param array $fieldsOrder FieldsOrder.
		 * @param string $body Whatsapp_template_new_order_body.
		 */
		protected function getBodyComponents( array $fieldsOrder, string $body ): array {
			$matches = [];
			preg_match_all('/{{(.*?)}}/', $body, $matches);
			$placeholders = $matches[1];

			$associations = [
				'transactionID' => $fieldsOrder['transaction_id'],
				'dateCreated' => strval($fieldsOrder['date_created']),
				'products' => $fieldsOrder['products'],
				'firstName' => $fieldsOrder['user_first_name'] ?? '',
				'userName' => $fieldsOrder['user_name'],
				'userPhone' => $fieldsOrder['user_phone'],
				'userAddress' => $fieldsOrder['user_address'],
				'transactionTax' => $fieldsOrder['transaction_tax'],
				'transactionShipping' => $fieldsOrder['transaction_shipping'],
				'transactionTotal' => $fieldsOrder['transaction_total'],
				'shippingName' => $fieldsOrder['shipping_name'],
				'paymentName' => $fieldsOrder['payment_name'],
				'couponCode' => $fieldsOrder['coupon_code'] ?? '',
				'slugProduct' => $fieldsOrder['slug_product'] ?? '',
				'productName' => $fieldsOrder['product_name'] ?? '',
			];

			if (!empty($fieldsOrder['custom_fields']) && is_array($fieldsOrder['custom_fields'])) {
				$customFields = $fieldsOrder['custom_fields'];

				$customFieldIndex = 0;
				foreach ($placeholders as $key => $placeholder) {
					if ('customField' === $placeholder && isset($customFields[$customFieldIndex])) {
						$associations['customField_' . $customFieldIndex] = $customFields[$customFieldIndex];
						$customFieldIndex++;
					}
				}
			}

			$body_components  = [];
			$usedCustomFields = 0;
			foreach ($placeholders as $key => $placeholder) {
				if ('customField' === $placeholder && isset($associations['customField_' . $usedCustomFields])) {
					$body_components[] = [
						'type' => 'text',
						'text' => $associations['customField_' . $usedCustomFields],
					];
					$usedCustomFields++;
				} elseif (isset($associations[$placeholder])) {
					$body_components[] = [
						'type' => 'text',
						'text' => $associations[$placeholder],
					];
				}
			}

			return $body_components;
		}

		protected function getButtonComponents( array $buttons, array $fieldsOrder ): array {
			$components = [];
			$index      = 0;

			foreach ( $buttons as $button ) {

				if ( empty( $button['type'] ) || 'visit_website' !== $button['type'] ) {
					$index++;
					continue;
				}

				if ( empty( $button['url_type'] ) || 'static' === $button['url_type'] ) {
					$index++;
					continue;
				}

				/**
				 * Triggered before sending a message to WhatsApp, the value goes inside the button.
				 * This hook contains an empty string that should be the function's return value, with the desired value to be inserted into the custom field.
				 * This hook contains an Order object (WC_Order) related to the message that will be triggered.
				 *
				 * Can be used to insert a non-standard custom field into the message.
				 *
				 * @param string $custom_field Empty string to be changed
				 * @param object $data Order (Wc_Order)
				 *
				 * @since 3.8.0
				 *
				 * @see https://woocommerce.com/document/notifications-with-whatsapp/
				 */
				$value = apply_filters(
					'notifications_with_whatsapp_before_send_button_field',
					'',
					$fieldsOrder['order']
				);

				if ( ! $value ) {
					$index++;
					continue;
				}

				$components[] = [
					'type'     => 'button',
					'sub_type' => 'url',
					'index'    => (string) $index,
					'parameters' => [
						[
							'type' => 'text',
							'text' => (string) $value,
						],
					],
				];

				$index++;
			}

			return $components;
		}

		abstract public function __construct( OptionsCredentialsManager $optionsCredentialsManager);
		abstract public function sendMessage( array $fieldsOrder);
		abstract public function setStatus( string $status);

	}
endif;
