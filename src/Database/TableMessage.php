<?php
/**
 * TableMessage
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TableMessage
 */
class TableMessage extends Record {

	/**
	 * Init constructor.
	 */
	public function __construct() {
		parent::__construct('notifications_with_whatsapp_messages');
	}

	/**
	 * Read_with_user_info.
	 *
	 * @param string $offset
	 * @param string $limit
	 */
	public function read_with_user_info( $offset, $limit): array {
		global $wpdb;

		$messages = $wpdb->get_results( $wpdb->prepare(
			"SELECT m.*, u.user_id, u.name AS user_name,
			   EXISTS (
					SELECT 1
					FROM {$wpdb->prefix}notifications_with_whatsapp_messages um
					WHERE um.wa_id = m.wa_id
					  AND um.sender_type = 'user'
					  AND um.status = '0'
				) AS has_unread_user_message
		 FROM {$wpdb->prefix}notifications_with_whatsapp_messages m
		 INNER JOIN {$wpdb->prefix}notifications_with_whatsapp_users u
		 ON m.wa_id = u.wa_id
		 INNER JOIN (
		     SELECT wa_id, MAX(date_time) as max_date_time
		     FROM {$wpdb->prefix}notifications_with_whatsapp_messages
		     GROUP BY wa_id
		 ) subquery
		 ON m.wa_id = subquery.wa_id AND (m.date_time = subquery.max_date_time AND m.id = (
				SELECT MAX(id)
				FROM {$wpdb->prefix}notifications_with_whatsapp_messages
				WHERE wa_id = m.wa_id AND date_time = subquery.max_date_time
			))
		 ORDER BY m.date_time DESC
		 LIMIT %d, %d", $offset, $limit
		));

		$total_count = $wpdb->get_var(
			"SELECT COUNT(DISTINCT wa_id)
		FROM {$wpdb->prefix}notifications_with_whatsapp_messages"
		);

		return array(
			'messages' => $messages,
			'total_messages' => (int) $total_count
		);
	}

	/**
	 * Read_with_user_info.
	 *
	 * @param int $wa_id
	 * @param int $offset
	 * @param null $limit
	 * @return array|object|\stdClass[]|null
	 */
	public function read_by_id_with_user_info( int $wa_id, int $offset = 0, $limit = null ) {
		global $wpdb;

		$wa_id = (int) $wa_id;
		$limit = is_null( $limit ) ? 99 : $limit;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
                m.id,
                m.wa_id,
                m.message_id,
                m.conversation_id,
                m.message,
                m.message_type,
                m.reaction,
                m.sender_type,
                m.status,
                m.date_time,
                u.user_id,
                u.name AS user_name
            FROM {$wpdb->prefix}notifications_with_whatsapp_messages m
            INNER JOIN {$wpdb->prefix}notifications_with_whatsapp_users u
            ON m.wa_id = u.wa_id
            WHERE m.wa_id = %d
            ORDER BY m.date_time ASC
            LIMIT %d, %d",
				$wa_id,
				$offset,
				$limit
			)
		);
	}

	/**
	 * Read_with_user_info.
	 *
	 * @param int $wa_id
	 */
	public function mark_as_read_database( $wa_id ) {
		global $wpdb;

		return $wpdb->update(
			"{$wpdb->prefix}notifications_with_whatsapp_messages",
			array( 'status' => 1 ),
			array( 'wa_id' => $wa_id, 'sender_type' => 'user' ),
			array( '%d' ),
			array( '%d', '%s' )
		);
	}

	/**
	 * Count_new_messages.
	 *
	 */
	public function count_new_messages(): int {
		global $wpdb;

		$total_new_messages = $wpdb->get_var( "SELECT COUNT(*) AS total_registros
									FROM (
										SELECT m.*, u.user_id, u.name AS user_name
										FROM {$wpdb->prefix}notifications_with_whatsapp_messages m
										INNER JOIN {$wpdb->prefix}notifications_with_whatsapp_users u
										ON m.wa_id = u.wa_id
										WHERE m.status = 0 AND m.sender_type = 'user'
										GROUP BY m.wa_id
									) AS subconsulta_total_registros;" );
		return (int) $total_new_messages;
	}

	/**
	 * Save reaction emoji on a message by its message_id.
	 *
	 * @param string $message_id The WhatsApp message ID that received the reaction.
	 * @param string $emoji      The emoji reaction (empty string means reaction removed).
	 * @return bool|int
	 */
	public function save_reaction( string $message_id, string $emoji ) {
		global $wpdb;

		return $wpdb->update(
			"{$wpdb->prefix}notifications_with_whatsapp_messages",
			array( 'reaction' => '' !== $emoji ? $emoji : null ),
			array( 'message_id' => $message_id ),
			array( '%s' ),
			array( '%s' )
		);
	}

	/**
	 * Read_with_user_info.
	 *
	 * @param string $message_id
	 * @param string $status_message
	 * @return bool|int|\mysqli_result|null
	 */
	public function mark_status_sender_type_system_database( string $message_id, string $status_message ) {
		global $wpdb;
		$status = 0;
		if ('delivered' === $status_message) {
			$status = 1;
		}
		if ('read' === $status_message) {
			$status = 2;
		}
		if ('failed' === $status_message) {
			$status = 3;
		}

		return $wpdb->update(
			"{$wpdb->prefix}notifications_with_whatsapp_messages",
			array( 'status' => $status ),
			array( 'message_id' => $message_id, 'sender_type' => 'system' ),
			array( '%d' ),
			array( '%s', '%s' )
		);
	}

}
