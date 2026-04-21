<?php
/**
 * TableManager
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\Database;

use ArraycodesOrderNotifications\Database\Migrations\Migration_2_0;
use ArraycodesOrderNotifications\Database\Migrations\Migration_2_1;
use ArraycodesOrderNotifications\Database\Migrations\Migration_2_2;
use ArraycodesOrderNotifications\Database\Migrations\Migration_2_3;
use ArraycodesOrderNotifications\Database\Migrations\Migration_2_4;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TableManager
 */
final class TableManager {

	/**
	 * Instance of this class.
	 *
	 * @var self|null Instance of the class.
	 */
	protected static $instance = null;

	/**
	 * Table Name return.
	 *
	 * @var string
	 */
	protected static $current_version = '2.4';

	/**
	 * Init constructor.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'update_database_check') );
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
	 *  Table Install
	 */
	public static function install_table() {
		global $wpdb;
		$current_version = self::$current_version;

		$table_notifications_with_whatsapp_users         = $wpdb->prefix . 'notifications_with_whatsapp_users';
		$table_notifications_with_whatsapp_messages      = $wpdb->prefix . 'notifications_with_whatsapp_messages';
		$table_notifications_with_whatsapp_conversations = $wpdb->prefix . 'notifications_with_whatsapp_conversations';
		$charset_collate                                 = $wpdb->get_charset_collate();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		if ( !get_site_option ('notifications_with_whatsapp_table_message_installed_version') ) {

			/**
			 * TABLE USERS
			 */
			$sql = "CREATE TABLE $table_notifications_with_whatsapp_users (
						id bigint PRIMARY KEY AUTO_INCREMENT,
						user_id BIGINT NOT NULL UNIQUE,
						wa_id BIGINT NOT NULL UNIQUE,
						name varchar(100) NULL,
						INDEX (wa_id)
					) $charset_collate;";
			dbDelta( $sql );

			/**
			 * TABLE CONVERSATIONS
			 */
			$sql = "CREATE TABLE $table_notifications_with_whatsapp_conversations (
						conversation_id bigint PRIMARY KEY AUTO_INCREMENT,
						user_wa_id BIGINT NOT NULL,
						started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
						FOREIGN KEY (user_wa_id) REFERENCES $table_notifications_with_whatsapp_users(wa_id)
					) $charset_collate;";
			dbDelta( $sql );

			/**
			 * TABLE MESSAGES
			 */
			$sql = "CREATE TABLE $table_notifications_with_whatsapp_messages (
						id bigint PRIMARY KEY AUTO_INCREMENT,
						message_id varchar(255) NULL,
						conversation_id bigint NOT NULL,
						wa_id BIGINT NOT NULL,
						message longtext NOT NULL,
						message_type VARCHAR(50) NULL DEFAULT 'text',
						reaction VARCHAR(10) NULL DEFAULT NULL,
						sender_type ENUM('user', 'system') NOT NULL,
						status INT DEFAULT '0',
						date_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
						related_message_id bigint DEFAULT NULL,
						FOREIGN KEY (conversation_id) REFERENCES $table_notifications_with_whatsapp_conversations(conversation_id),
						FOREIGN KEY (wa_id) REFERENCES $table_notifications_with_whatsapp_users(wa_id),
						FOREIGN KEY (related_message_id) REFERENCES $table_notifications_with_whatsapp_messages(id),
						INDEX (conversation_id),
						INDEX (date_time)
					) $charset_collate;";
			dbDelta( $sql );

			add_option( 'notifications_with_whatsapp_table_message_installed_version', $current_version );
		}

	}

	/**
	 *  Table Update Check
	 */
	public function update_database_check() {
		$installed_version = get_option('notifications_with_whatsapp_table_message_installed_version', '0.0');
		if (version_compare($installed_version, self::$current_version, '<')) {
			if ($this->run_migrations($installed_version)) {
				update_option('notifications_with_whatsapp_table_message_installed_version', self::$current_version);
			}
		}
	}

	/**
	 *  Run migrations
	 */
	private function run_migrations( $installed_version): bool {
		global $wpdb;

		$wpdb->query('START TRANSACTION');
		$success = true;

		$migrations = $this->get_migrations($installed_version);

		foreach ($migrations as $migration) {
			$result = $migration->apply();
			if (!$result) {
				$success = false;
				break;
			}
		}

		if ($success) {
			$wpdb->query('COMMIT');
			return true;
		} else {
			$wpdb->query('ROLLBACK');
			return false;
		}
	}

	/**
	 *  Get migrations
	 */
	private function get_migrations( $installed_version): array {
		$migrations = [];

		$pending_migrations = [];
		foreach ($migrations as $version => $migration_class) {
			if (version_compare($installed_version, $version, '<')) {
				$pending_migrations[] = new $migration_class();
			}
		}

		return $pending_migrations;
	}

	/**
	 *  Clear Tables
	 */
	public static function clear_tables() {
		global $wpdb;

		$wpdb->query( 'TRUNCATE TABLE woo_notifications_with_whatsapp_users' );
		$wpdb->query( 'TRUNCATE TABLE woo_notifications_with_whatsapp_messages' );
		$wpdb->query( 'TRUNCATE TABLE woo_notifications_with_whatsapp_conversations' );
	}

}
