<?php
/**
 * OptionsCredentialsManager
 *
 * @version 1.0.0
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\Options;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'OptionsCredentialsManager' ) ) :
	/**
	 * Class OptionsCredentialsManager
	 */
	class OptionsCredentialsManager {

		/**
		 * Constructor.
		 *
		 */
		public function __construct() {
		}

		/**
		 * Get_options_whatsapp_settings_credentials.
		 *
		 */
		public function get_options_whatsapp_settings_credentials() {
			$options_whatsapp_settings_credentials = get_option( 'arraycodes_on_fields_free' );
			if ( !isset( $options_whatsapp_settings_credentials['whatsapp_api_account_id'] ) ) {
				$options_whatsapp_settings_credentials = array(
					'whatsapp_api_log' => false,
					'whatsapp_api_token' => '',
					'whatsapp_api_phone_number' => '',
					'whatsapp_api_account_id' => '',
					'whatsapp_api_phone_number_to' => ''
				);
			}

			$keys_to_keep = array(
				'whatsapp_api_account_id',
				'whatsapp_api_app_secret',
				'whatsapp_api_log',
				'whatsapp_api_phone_number',
				'whatsapp_api_phone_number_to',
				'whatsapp_api_token'
			);
			return array_intersect_key($options_whatsapp_settings_credentials, array_flip($keys_to_keep));
		}

		/**
		 * Get_app_secret.
		 */
		public function get_app_secret(): string {
			$options = get_option( 'arraycodes_on_fields_free', array() );
			return isset( $options['whatsapp_api_app_secret'] ) ? (string) $options['whatsapp_api_app_secret'] : '';
		}
	}
endif;
