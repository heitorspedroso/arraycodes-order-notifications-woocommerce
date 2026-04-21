<?php
/**
 * TableUser
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TableUser
 */
class TableUser extends Record {

	/**
	 * Init constructor.
	 */
	public function __construct() {
		parent::__construct('notifications_with_whatsapp_users');
	}


	/**
	 * Creates a new record or updates an existing one in the database.
	 *
	 * @param array $data Associative array containing the data to insert or update.
	 *                    Should include 'data' (the record data) and 'format' (the format of data values).
	 * @param array $where Conditions for selecting the record to update, if applicable.
	 * @return void
	 */
	public function createOrUpdate( $data, $where) {
		global $wpdb;

		$user   = $data['data'];
		$format = $data['format'];

		$wa_id       = (int) $user['wa_id'];
		$new_user_id = (int) $user['user_id'];

		// Verifica se já existe
		$existing = $this->get_by_wa_id($wa_id);

		if (! $existing) {
			// Inserir novo
			$wpdb->insert($this->table_name, $user, $format);
			return;
		}

		// Se já existe, e o user_id atual é diferente do novo (e o novo é real), atualiza
		if (
			$new_user_id > 0 &&
			(int) $existing->user_id !== $new_user_id
		) {
			$wpdb->update(
				$this->table_name,
				['user_id' => $new_user_id, 'name' => $user['name']],
				['wa_id' => $wa_id],
				['%d', '%s'],
				['%d']
			);
		}
	}


	/**
	 * Retrieves a user record by WhatsApp ID from the database.
	 *
	 * @param int $wa_id The WhatsApp ID of the user to be retrieved.
	 * @return object|null An object containing the user data if found, or null if no record exists.
	 */
	public function get_by_wa_id( int $wa_id): ?object {
		global $wpdb;

		$sql = "SELECT * FROM {$this->table_name} WHERE wa_id = %d LIMIT 1";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$query = $wpdb->prepare($sql, $wa_id);
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_row($query);
	}


}
