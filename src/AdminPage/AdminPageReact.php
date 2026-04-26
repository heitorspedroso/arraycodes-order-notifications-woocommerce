<?php
/**
 * AdminPageReact Init
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\AdminPage;

use ArraycodesOrderNotifications\Database\TableMessage;

defined( 'ABSPATH' ) || exit;

/**
 * Class AdminPageReact
 */
class AdminPageReact {

	/**
	 * The single instance of the class.
	 *
	 * @var self|null Instance of the class.
	*/
	protected static $instance = null;

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'add_extension_register_script') );
		add_action( 'admin_menu', array( $this, 'add_extension_register_page') );
		add_action( 'admin_init', array( $this, 'register_setting'), 30);
		add_action( 'rest_api_init', array( $this, 'register_setting' ) );
		add_action( 'admin_init', array( $this, 'verify_fields_notifications_with_whatsapp_option' ) );
		new AdminPageOrders();
	}

	/**
	 * Main Extension Instance.
	 * Ensures only one instance of the extension is loaded or can be loaded.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register the JS.
	 */
	public function add_extension_register_script() {

		if ( ! method_exists( '\Automattic\WooCommerce\Admin\PageController', 'is_admin_or_embed_page' ) || ! \Automattic\WooCommerce\Admin\PageController::is_admin_or_embed_page() ) {
			return;
		}

		$script_path       = '/../../assets/build/index.js';
		$script_asset_path = dirname( __FILE__ ) . '/../../assets/build/index.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array( 'dependencies' => array('wp-i18n'), 'version' => filemtime( $script_path ) );
		$script_url        = plugins_url( $script_path, __FILE__ );

		wp_register_script(
			'arraycodes-order-notifications-woocommerce-scripts',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		wp_register_style(
			'arraycodes-order-notifications-woocommerce-styles',
			plugins_url( '/../../assets/build/index.css', __FILE__ ),
			array(),
			filemtime( dirname( __FILE__ ) . '/../../assets/build/index.css' )
		);

		wp_enqueue_script( 'arraycodes-order-notifications-woocommerce-scripts' );

		wp_localize_script('arraycodes-order-notifications-woocommerce-scripts', 'arraycodesOnVars', array(
			'security' => wp_create_nonce('arraycodes-order-notifications-woocommerce-nonce'),
		));

		wp_enqueue_style( 'arraycodes-order-notifications-woocommerce-styles' );
		wp_set_script_translations('arraycodes-order-notifications-woocommerce-scripts', 'arraycodes-order-notifications-woocommerce', plugin_dir_path(__FILE__) . '../../languages/');

		if ( ! wp_script_is( 'wp-api', 'enqueued' ) ) {
			wp_enqueue_script( 'wp-api' );
		}
	}

	/**
	 * Register a WooCommerce Admin page.
	 */
	public function add_extension_register_page() {
		if ( ! function_exists( 'wc_admin_register_page' ) ) {
			return;
		}
		$count = $this->get_new_messages( new TableMessage() );
		if ( 0 === $count) {
			$menu_title = __('ArrayCodes Order Notifications', 'arraycodes-order-notifications-woocommerce');
		} else {
			$menu_title = __('ArrayCodes Order Notifications', 'arraycodes-order-notifications-woocommerce') . '<span class="awaiting-mod update-plugins count-' . esc_attr( $count ) . '"><span class="processing-count">' . number_format_i18n( $count ) . '</span></span>';
		}

		wc_admin_register_page( array(
			'id'       => 'arraycodes-order-notifications-woocommerce',
			'title'    => $menu_title,
			'parent'   => 'woocommerce',
			'path'     => '/arraycodes-order-notifications-woocommerce',
			'nav_args' => array(
				'order'  => 10,
				'parent' => 'woocommerce',
			),
		) );
	}

	/**
	 * Sanitize settings input before saving.
	 *
	 * @param mixed $input Raw input value.
	 * @return array Sanitized settings array.
	 */
	public function sanitize_settings( $input ) {
		if ( ! is_array( $input ) ) {
			return array();
		}

		$text_fields = array(
			'whatsapp_api_token', 'whatsapp_api_phone_number', 'whatsapp_api_account_id',
			'whatsapp_api_phone_number_to', 'whatsapp_api_webhook_token', 'whatsapp_api_app_secret',
			'whatsapp_template_new_order_id', 'whatsapp_template_new_order_status',
			'whatsapp_template_new_order_category', 'whatsapp_template_new_order_name',
			'whatsapp_template_new_order_language', 'whatsapp_template_new_order_customer_id',
			'whatsapp_template_new_order_customer_status', 'whatsapp_template_new_order_customer_category',
			'whatsapp_template_new_order_customer_name', 'whatsapp_template_new_order_customer_language',
			'whatsapp_template_new_order_customer_header_type',
		);

		$textarea_fields = array(
			'whatsapp_template_new_order_header', 'whatsapp_template_new_order_footer',
			'whatsapp_template_new_order_body', 'whatsapp_template_new_order_customer_header',
			'whatsapp_template_new_order_customer_footer', 'whatsapp_template_new_order_customer_body',
			'whatsapp_auto_reply',
		);

		$sanitized = array();

		foreach ( $text_fields as $field ) {
			if ( isset( $input[ $field ] ) ) {
				$sanitized[ $field ] = sanitize_text_field( $input[ $field ] );
			}
		}

		foreach ( $textarea_fields as $field ) {
			if ( isset( $input[ $field ] ) ) {
				$sanitized[ $field ] = sanitize_textarea_field( $input[ $field ] );
			}
		}

		if ( isset( $input['whatsapp_api_webhook_callback_url'] ) ) {
			$sanitized['whatsapp_api_webhook_callback_url'] = esc_url_raw( $input['whatsapp_api_webhook_callback_url'] );
		}

		$sanitized['whatsapp_api_log']                 = isset( $input['whatsapp_api_log'] ) ? (bool) $input['whatsapp_api_log'] : false;
		$sanitized['whatsapp_api_checked_input']       = isset( $input['whatsapp_api_checked_input'] ) ? (bool) $input['whatsapp_api_checked_input'] : false;
		$sanitized['whatsapp_total_received_messages'] = isset( $input['whatsapp_total_received_messages'] ) ? absint( $input['whatsapp_total_received_messages'] ) : 0;
		$sanitized['whatsapp_received_messages']       = isset( $input['whatsapp_received_messages'] ) && is_array( $input['whatsapp_received_messages'] )
			? map_deep( $input['whatsapp_received_messages'], 'sanitize_text_field' )
			: array();
		$sanitized['whatsapp_received_messages_by_id'] = isset( $input['whatsapp_received_messages_by_id'] ) && is_array( $input['whatsapp_received_messages_by_id'] )
			? map_deep( $input['whatsapp_received_messages_by_id'], 'sanitize_text_field' )
			: array();

		return $sanitized;
	}

	/**
	 * Register and add settings
	 */
	public function register_setting() {
		register_setting(
			'arraycodes_on_settings_free',
			'arraycodes_on_fields_free',
			array(
				'type'             => 'object',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'show_in_rest' => array(
					'schema' => array(
						'type'       => 'object',
						'default'      => array(
							'whatsapp_api_log' => false,
							'whatsapp_api_token' => '',
							'whatsapp_api_phone_number' => '',
							'whatsapp_api_account_id' => '',
							'whatsapp_api_phone_number_to' => '',
							'whatsapp_api_checked_input' => false,
							'whatsapp_api_webhook_callback_url' => '',
							'whatsapp_api_webhook_token' => '',
							'whatsapp_api_app_secret' => '',
							'whatsapp_template_new_order_id' => '',
							'whatsapp_template_new_order_status' => '',
							'whatsapp_template_new_order_category' => '',
							'whatsapp_template_new_order_name' => '',
							'whatsapp_template_new_order_language' => '',
							'whatsapp_template_new_order_header' => '',
							'whatsapp_template_new_order_footer' => '',
							'whatsapp_template_new_order_body' => '',
							'whatsapp_template_new_order_customer_id' => '',
							'whatsapp_template_new_order_customer_status' => '',
							'whatsapp_template_new_order_customer_category' => '',
							'whatsapp_template_new_order_customer_name' => '',
							'whatsapp_template_new_order_customer_language' => '',
							'whatsapp_template_new_order_customer_header' => '',
							'whatsapp_template_new_order_customer_header_type' => '',
							'whatsapp_template_new_order_customer_footer' => '',
							'whatsapp_template_new_order_customer_body' => '',
							'whatsapp_received_messages' => array(),
							'whatsapp_auto_reply' => '',
							'whatsapp_total_received_messages' => 0,
							'whatsapp_received_messages_by_id' => array(),
						),
						'properties' => array(
							'whatsapp_api_log' => array(
								'type' => 'boolean',
							),
							'whatsapp_api_token' => array(
								'type' => 'string',
							),
							'whatsapp_api_phone_number' => array(
								'type' => 'string',
							),
							'whatsapp_api_account_id' => array(
								'type' => 'string',
							),
							'whatsapp_api_phone_number_to' => array(
								'type' => 'string',
							),
							'whatsapp_api_checked_input' => array(
								'type' => 'boolean',
							),
							'whatsapp_api_webhook_callback_url' => array(
								'type' => 'string',
							),
							'whatsapp_api_webhook_token' => array(
								'type' => 'string',
							),
							'whatsapp_api_app_secret' => array(
								'type' => 'string',
							),
							'whatsapp_template_new_order_id' => array(
								'type' => 'string',
							),
							'whatsapp_template_new_order_status' => array(
								'type' => 'string',
							),
							'whatsapp_template_new_order_category' => array(
								'type' => 'string',
							),
							'whatsapp_template_new_order_name' => array(
								'type' => 'string',
							),
							'whatsapp_template_new_order_language' => array(
								'type' => 'string',
							),
							'whatsapp_template_new_order_header' => array(
								'type' => 'string',
							),
							'whatsapp_template_new_order_footer' => array(
								'type' => 'string',
							),
							'whatsapp_template_new_order_body' => array(
								'type' => 'string',
							),
							'whatsapp_template_new_order_customer_id' => array(
								'type' => 'string',
							),
							'whatsapp_template_new_order_customer_status' => array(
								'type' => 'string',
							),
							'whatsapp_template_new_order_customer_category' => array(
								'type' => 'string',
							),
							'whatsapp_template_new_order_customer_name' => array(
								'type' => 'string',
							),
							'whatsapp_template_new_order_customer_language' => array(
								'type' => 'string',
							),
							'whatsapp_template_new_order_customer_header' => array(
								'type' => 'string',
							),
							'whatsapp_template_new_order_customer_header_type' => array(
								'type' => 'string',
							),
							'whatsapp_template_new_order_customer_footer' => array(
								'type' => 'string',
							),
							'whatsapp_template_new_order_customer_body' => array(
								'type' => 'string',
							),
							'whatsapp_received_messages' => array(
								'type' => 'array',
							),
							'whatsapp_auto_reply' => array(
								'type' => 'string',
							),
							'whatsapp_total_received_messages' => array(
								'type' => 'integer',
							),
							'whatsapp_received_messages_by_id' => array(
								'type' => 'array',
							),
						),
					),
				),
			)
		);

	}

	/**
	 * Migrate Options
	 */
	public function verify_fields_notifications_with_whatsapp_option() {
		$options = get_option( 'arraycodes_on_fields_free' );
		if (isset($options['whatsapp_api_webhook_callback'])) {
			unset($options['whatsapp_api_webhook_callback']);
		}
		update_option('arraycodes_on_fields_free', $options);
	}

	/**
	 * Count_new_messages.
	 *
	 * @param TableMessage $tableMessage Table Message
	 */
	private function get_new_messages( TableMessage $tableMessage): int {
		return $tableMessage->count_new_messages();
	}

}
