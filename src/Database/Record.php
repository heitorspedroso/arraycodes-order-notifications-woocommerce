<?php
namespace ArraycodesOrderNotifications\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Record' ) ) :
	/**
	 * Class Record
	 */
	abstract class Record {

		/**
		 * Table Name return.
		 *
		 * @var string
		 */
		protected $table_name;

		/**
		 * Global wpdb.
		 *
		 * @var \wpdb
		 */
		protected $wpdb;

		/**
		 * Init constructor.
		 *
		 * @param string $table_name Table Name.
		 */
		public function __construct( $table_name) {
			global $wpdb;
			$this->wpdb       = $wpdb;
			$this->table_name = $wpdb->prefix . $table_name;
		}

		/**
		 * Select records from the database.
		 *
		 * @param array $columns Columns to select. Default is '*'.
		 * @param array $where Conditions for the WHERE clause.
		 * @param string $order_by Order by clause.
		 * @param string $order Order direction. Default is 'ASC'.
		 * @return array|null Selected records.
		 */
		public function select( array $columns = ['*'], array $where = [], string $order_by = '', string $order = 'ASC' ): ?array {
			global $wpdb;

			$columns_sql = implode(', ', array_map(function( $col) {
				return esc_sql($col);
			}, $columns));

			$where_sql    = '';
			$where_values = [];
			if ( ! empty($where) ) {
				$where_clauses = array_map(function( $key) {
					return esc_sql($key) . ' = %s';
				}, array_keys($where));
				$where_sql     = 'WHERE ' . implode(' AND ', $where_clauses);
				$where_values  = array_values($where);
			}

			$order_sql = '';
			if ( ! empty($order_by) ) {
				$order_sql = 'ORDER BY ' . esc_sql($order_by) . ' ' . esc_sql($order);
			}

			$sql = "SELECT $columns_sql FROM {$this->table_name} $where_sql $order_sql";

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$query = $wpdb->prepare($sql, $where_values);
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			return $wpdb->get_results($query, ARRAY_A);
		}

		/**
		 * Create or Update record in the database.
		 *
		 * @param array $data Data to insert or update.
		 * @param array $where Conditions to check if record exists.
		 * @return int|\mysqli_result|bool|null
		 */
		public function createOrUpdate( $data, $where) {
			global $wpdb;

			$where_clause = implode(' AND ', array_map(function ( $key) {
				return "$key = %s";
			}, array_keys($where)));

			$sql = "SELECT * FROM {$this->table_name} WHERE {$where_clause}";
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$query = $wpdb->prepare( $sql, array_values($where));

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$exists = $wpdb->get_row($query);

			if ($exists) {
				$where_format = array_map(function () {
					return '%s';
				}, array_keys($where));

				// Perform the update
				return $wpdb->update(
					$this->table_name,
					$data['data'],
					$where,
					$data['format'],
					$where_format
				);
			} else {
				return $this->create($data);
			}
		}

		/**
		 * Create.
		 *
		 * @param array $data Data.
		 * @return int|\mysqli_result|bool|null
		 */
		public function create( $data) {
			$this->wpdb->insert($this->table_name, $data['data'], $data['format']);
			return $this->wpdb->insert_id;
		}

		/**
		 * Delete.
		 *
		 * @param array $where
		 * @return bool|int|\mysqli_result|null
		 */
		public function delete( array $where) {
			return $this->wpdb->delete($this->table_name, $where);
		}
	}
endif;
