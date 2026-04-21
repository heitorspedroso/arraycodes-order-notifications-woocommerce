<?php
/**
 * Logger
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\Logger;

use ArraycodesOrderNotifications\Options\OptionsCredentialsManager;
use WC_Logger;

defined( 'ABSPATH' ) || exit;

/**
 * Class Logger
 */
class Logger implements LoggerInterface {
	/**
	 * Manages WhatsApp API credentials.
	 *
	 * @var OptionsCredentialsManager
	 */
	private $optionsCredentialsManager;

	/**
	 * WooCommerce Logger instance.
	 *
	 * @var WC_Logger
	 */
	private $logger;

	/**
	 * Constructor.
	 *
	 * @param OptionsCredentialsManager $optionsCredentialsManager Manages WhatsApp API credentials.
	 */
	public function __construct( OptionsCredentialsManager $optionsCredentialsManager ) {
		$this->optionsCredentialsManager = $optionsCredentialsManager;
		$this->logger                    = new WC_Logger();
	}

	/**
	 * Logs a message.
	 *
	 * @param string $level Log level (e.g., 'info', 'error', 'debug').
	 * @param string $message The log message.
	 * @param array  $context Optional. Additional log context.
	 *
	 * @return void
	 */
	public function log( $level, $message, array $context = [] ): void {
		$this->logger->log( $level, $message, $context );
	}

	/**
	 * Saves API logs if logging is enabled in the settings.
	 *
	 * @param string $title The log title.
	 * @param array  $return The data to log.
	 *
	 * @return void
	 */
	public function save_log_api( string $title, array $return ): void {
		$options = $this->optionsCredentialsManager->get_options_whatsapp_settings_credentials();

		if ( $options && ! empty( $options['whatsapp_api_log'] ) ) {
			$context = [ 'source' => 'plugin-arraycodes-order-notifications-woocommerce' ];
			$this->logger->info( $title, $context );
			$this->logger->debug( wp_json_encode( $return, JSON_UNESCAPED_UNICODE ), $context );
		}
	}
}
