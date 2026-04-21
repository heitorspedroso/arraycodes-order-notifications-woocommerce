<?php
/**
 * BaseOrderFacebookCommunicator
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\FacebookCommunicator;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'ArraycodesOrderNotifications\BaseOrderFacebookCommunicator' ) ) :
	/**
	 * Class BaseOrderFacebookCommunicator
	 */
	abstract class BaseOrderFacebookCommunicator implements FacebookCommunicatorInterface {

		/**
		 * Prepare_fields.
		 *
		 * @param string $body    Body text.
		 * @param string $language Language.
		 * @param string $name    Name.
		 * @param string $header  Header text.
		 * @param string $footer  Footer text.
		 * @param array $buttons  Buttons.
		 * @return array Prepared fields.
		 */
		protected function prepare_fields( string $body, string $language, string $name, string $header, string $footer, array $buttons = []): array {

			$return = array();

			$placeholders = [
				'transactionID' => '283',
				'dateCreated' => '2023-05-16T02:01:51+00:00',
				'firstName' => 'John',
				'userName' => 'John Smith',
				'userPhone' => '555-1212',
				'userAddress' => 'Street address: My Street - Apartment, suite, unit, etc.: 132 - Town / City: Kingston - State / County: New York - Postcode: 12401',
				'products' => '1x Beanie - value: 18 - cód: 18',
				'transactionTax' => '0',
				'transactionShipping' => '0',
				'transactionTotal' => '18.00',
				'couponCode' => 'DESC10',
				'customField' => '123',
				'productName' => 'Beanie',
				'shippingName' => 'Fedex',
				'paymentName' => 'Direct transfer',
				'reviewFormParams' => '?order_id=1&token=wc_order_example',
			];

			$counter         = 1;
			$fields_modified = preg_replace_callback('/{{(.*?)}}/', function ( $matches) use ( &$counter) {
				return '{{' . ( $counter++ ) . '}}';
			}, $body);

			$body_text = array();
			$pattern   = '/{{(.*?)}}/';
			preg_match_all($pattern, $body, $matches);
			foreach ($matches[1] as $placeholder) {
				$body_text[] = $placeholders[$placeholder];
			}

			$fields_modified = preg_replace("/(\n{3,})/", "\n\n", $fields_modified);

			$buttonText    = '';
			$buttonUrl     = '';
			$buttonExample = array();
			$components    = array(
				$this->getHeaderComponent( $header ),
				[
					'type' => 'FOOTER',
					'text' => $footer,
				],
				[
					'type' => 'BODY',
					'text' => $fields_modified,
					'example' => [
						'body_text' => [
							$body_text,
						],
					],
				],
			);

			if ('MARKETING' === $this->getCategory()) {
				if ('Abandoned Cart Customer' === $this->get_class_type()) {
					$buttonText    = __('Recover Cart', 'arraycodes-order-notifications-woocommerce');
					$buttonUrl     = get_bloginfo('url') . '?recover_cart={{1}}';
					$buttonExample = ['4fb60dacc2538b28c414d1f7b1577b99'];
				} elseif ('Unpaid Order Customer' === $this->get_class_type()) {
					if ($this->get_button_exist()) {
						$buttonText    = __('Pay Order', 'arraycodes-order-notifications-woocommerce');
						$buttonUrl     = get_bloginfo('url') . '/{{1}}';
						$buttonExample = ['4fb60dacc2538b28c414d1f7b1577b99'];
					}
				} elseif ('Back In Stock Customer' === $this->get_class_type()) {
					$buttonText    = __('View product', 'arraycodes-order-notifications-woocommerce');
					$buttonUrl     = get_bloginfo('url') . '/{{1}}';
					$buttonExample = ['slug-beanie'];
				}

				if ('Order Details Customer' === $this->get_class_type()) {
					$components[] = array(
						'type' => 'BUTTONS',
						'buttons' => array(
							array(
								'type' => 'ORDER_DETAILS',
								'text' => 'Copy Pix code'
							),
						),
					);

					$return['display_format'] = 'ORDER_DETAILS';
				}
			}

			if ($buttonText && $buttonUrl) {
				if ($this->get_coupon_exist()) {
					$components[] = array(
						'type' => 'BUTTONS',
						'buttons' => [
							[
								'type' => 'URL',
								'text' => $buttonText,
								'url' => $buttonUrl,
								'example' => $buttonExample,
							],
							[
								'type'=> 'COPY_CODE',
								'example'=> '250FF'
							],
						],
					);
				} else {
					$components[] = array(
						'type' => 'BUTTONS',
						'buttons' => [
							[
								'type' => 'URL',
								'text' => $buttonText,
								'url' => $buttonUrl,
								'example' => $buttonExample,
							],
						],
					);
				}
			} else {
				if ($this->get_coupon_exist()) {
					$components[] = array(
						'type' => 'BUTTONS',
						'buttons' => [
							[
								'type'=> 'COPY_CODE',
								'example'=> '250FF'
							],
						],
					);
				}
			}

			$buttonsComponent = $this->prepareButtonsComponent(
				$buttons,
				$placeholders
			);

			if ($buttonsComponent) {
				$components[] = $buttonsComponent;
			}

			$return['category']   = $this->getCategory();
			$return['language']   = $language;
			$return['name']       = $name;
			$return['components'] = $components;

			return $return;
		}

		/**
		 * Get_coupon_exist.
		 *
		 */
		protected function get_coupon_exist(): bool {
			return false;
		}

		/**
		 * Get_button_exist.
		 *
		 */
		protected function get_button_exist(): bool {
			return false;
		}

		/**
		 * GetCategory.
		 *
		 */
		protected function getCategory(): string {
			return 'UTILITY';
		}

		/**
		 * GetHeaderComponent.
		 *
		 * @param string $header Header.
		 *
		 */
		protected function getHeaderComponent( string $header ): array {
			return [
				'type'   => 'HEADER',
				'format' => 'TEXT',
				'text'   => $header,
			];
		}

		/**
		 * Prepares a buttons component by processing and formatting button configurations.
		 *
		 * @param array $buttons The array of button configurations, each containing details such as type and text.
		 * @param array $placeholders The associative array of placeholder keys and their corresponding replacement values.
		 *
		 * @return array|null Returns an associative array with the prepared buttons data if valid buttons exist.
		 *                    Returns null if no valid buttons are provided.
		 */
		protected function prepareButtonsComponent( array $buttons, array $placeholders): ?array {
			if (empty($buttons)) {
				return null;
			}

			$preparedButtons = [];

			foreach ($buttons as $button) {
				if (empty($button['type']) || empty($button['text'])) {
					continue;
				}

				if ('visit_website' !== $button['type']) {
					continue;
				}

				$rawUrl = $button['url'] ?? '';
				if (!$rawUrl) {
					continue;
				}

				/*
				 * 1. Resolve {{site_url}} primeiro (não entra como variável)
				 */
				$baseUrl = rtrim(get_bloginfo('url'), '/');
				$url     = str_replace('{{site_url}}', $baseUrl, $rawUrl);

				/*
				 * 2. Agora processa SOMENTE placeholders dinâmicos
				 */
				$example = [];
				$counter = 1;

				$url = preg_replace_callback(
					'/{{(.*?)}}/',
					function ( $matches) use ( &$counter, &$example, $placeholders) {
						$key = $matches[1];

						if (!isset($placeholders[$key])) {
							return '';
						}

						$example[] = $placeholders[$key];
						return '{{' . ( $counter++ ) . '}}';
					},
					$url
				);

				$preparedButton = [
					'type' => 'URL',
					'text' => $button['text'],
					'url'  => $url,
				];

				/*
				 * 3. Só adiciona example se for dynamic
				 */
				if (!empty($example)) {
					$preparedButton['example'] = $example;
				}

				$preparedButtons[] = $preparedButton;
			}

			if (empty($preparedButtons)) {
				return null;
			}

			return [
				'type'    => 'BUTTONS',
				'buttons' => $preparedButtons,
			];
		}

		abstract public function get_class_type(): string;
		abstract public function create_new_order( $fields ): array;
		abstract public function update_new_order( $fields ): array;
		abstract public function delete_new_order( $fields ): array;
		abstract public function get_order(): array;

	}
endif;
