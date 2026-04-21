<?php
/**
 * OrderCustomerFacebookCommunicator
 *
 * @version 1.0.1
 * @package 'arraycodes-order-notifications-woocommerce'
 */

namespace ArraycodesOrderNotifications\FacebookCommunicator;

use ArraycodesOrderNotifications\Options\OptionsCredentialsManager;
use ArraycodesOrderNotifications\Options\CustomerOptionsManager;

defined( 'ABSPATH' ) || exit;

/**
 * Class OrderCustomerFacebookCommunicator
 */
class OrderCustomerFacebookCommunicator extends BaseOrderFacebookCommunicator {

	/**
	 * The single instance of the class.
	 *
	 * @var OptionsCredentialsManager
	 */
	private $optionsCredentialsManager;

	/**
	 * The single instance of the class.
	 *
	 * @var CustomerOptionsManager
	 */
	private $optionsManager;

	/**
	 * The single instance of the class.
	 *
	 * @var string
	 */
	private $headerType;

	/**
	 * Constructor.
	 *
	 * @param CustomerOptionsManager $optionsManager The options manager instance.
	 */
	public function __construct( OptionsCredentialsManager $optionsCredentialsManager, CustomerOptionsManager $optionsManager) {
		$this->optionsCredentialsManager = $optionsCredentialsManager;
		$this->optionsManager            = $optionsManager;
	}

	/**
	 * Get_class_type.
	 *
	 */
	public function get_class_type(): string {
		return 'New Order Customer';
	}

	/**
	 * Create_model_new_order.
	 *
	 * @param array $fields Fields.
	 */
	public function create_new_order( $fields ): array {

		$this->headerType = $fields['whatsapp_template_new_order_customer_header_type'];

		if ('image' === $fields['whatsapp_template_new_order_customer_header_type']) {
			$returnUploadMedia = $this->upload_media($fields['whatsapp_template_new_order_customer_header']);
			if (isset($returnUploadMedia['message'])) {
				return array('message' => $returnUploadMedia['message'], 'code' => $returnUploadMedia['code']);
			}
			$fields['whatsapp_template_new_order_customer_header'] = $returnUploadMedia['id'];
		}

		$prepared_fields = $this->prepare_fields( $fields['whatsapp_template_new_order_customer_body'], $fields['whatsapp_template_new_order_customer_language'], $fields['whatsapp_template_new_order_customer_name'], $fields['whatsapp_template_new_order_customer_header'], $fields['whatsapp_template_new_order_customer_footer'] );

		$options_whatsapp_settings_credentials = $this->optionsCredentialsManager->get_options_whatsapp_settings_credentials();

		if ( $options_whatsapp_settings_credentials ) {
			$url = 'https://graph.facebook.com/v22.0/' . $options_whatsapp_settings_credentials['whatsapp_api_account_id'] . '/message_templates';

			$headers = array(
				'Authorization' => 'Bearer ' . $options_whatsapp_settings_credentials['whatsapp_api_token'],
				'Content-Type' => 'application/json'
			);

			$args = array(
				'headers' => $headers,
				'body' => wp_json_encode( $prepared_fields ),
				'timeout' => 9999,
			);

			$response = wp_remote_post($url, $args);

			if (is_wp_error($response)) {
				$error_message = $response->get_error_message();
				$code          = $response->get_error_code();

				if ( 'http_request_failed' === $code) {
					return array('message' => $error_message, 'code' => 408);
				}
				return array('message' => $error_message, 'code' => $code);
			} else {
				$response_body = wp_remote_retrieve_body($response);

				$json_response_body = json_decode($response_body);

				if ($json_response_body->error) {
					return array('message' => $json_response_body->error, 'code' => 200);
				}

				if ($json_response_body->id) {
					$this->update_status_new_order_customer( $json_response_body->id, $json_response_body->status );
				}

				return array('message' => $response_body, 'code' => 200);
			}

		}

		return array('message' => 'Fields are false', 'code' => 403);

	}

	/**
	 * Update_new_order.
	 *
	 * @param array $fields Fields.
	 */
	public function update_new_order( $fields ): array {

		$this->headerType = $fields['whatsapp_template_new_order_customer_header_type'];

		if ('image' === $fields['whatsapp_template_new_order_customer_header_type']) {
			$returnUploadMedia = $this->upload_media($fields['whatsapp_template_new_order_customer_header']);
			if (isset($returnUploadMedia['message'])) {
				return array('message' => $returnUploadMedia['message'], 'code' => $returnUploadMedia['code']);
			}
			$fields['whatsapp_template_new_order_customer_header'] = $returnUploadMedia['id'];
		}

		$prepared_fields = $this->prepare_fields( $fields['whatsapp_template_new_order_customer_body'], $fields['whatsapp_template_new_order_customer_language'], $fields['whatsapp_template_new_order_customer_name'], $fields['whatsapp_template_new_order_customer_header'], $fields['whatsapp_template_new_order_customer_footer'] );

		$options_whatsapp_settings_credentials = $this->optionsCredentialsManager->get_options_whatsapp_settings_credentials();

		if ( $options_whatsapp_settings_credentials ) {
			$options_whatsapp_settings_new_order = $this->get_options_whatsapp_settings_new_order();

			$url = 'https://graph.facebook.com/v22.0/' . $options_whatsapp_settings_new_order['whatsapp_template_new_order_customer_id'];

			$headers = array(
				'Authorization' => 'Bearer ' . $options_whatsapp_settings_credentials['whatsapp_api_token'],
				'Content-Type' => 'application/json'
			);

			$args = array(
				'headers' => $headers,
				'body' => wp_json_encode( array('components' => $prepared_fields['components']) ),
				'timeout' => 9999,
			);

			$response = wp_remote_post($url, $args);

			if (is_wp_error($response)) {
				$error_message = $response->get_error_message();
				$code          = $response->get_error_code();

				if ( 'http_request_failed' === $code) {
					return array('message' => $error_message, 'code' => 408);
				}
				return array('message' => $error_message, 'code' => $code);
			} else {
				$response_body = wp_remote_retrieve_body($response);

				$json_response_body = json_decode($response_body);

				if ($json_response_body->error) {
					return array('message' => $json_response_body->error, 'code' => 200);
				}

				return array('message' => $response_body, 'code' => 200);
			}

		}

		return array('message' => 'Fields are false', 'code' => 403);

	}

	/**
	 * Delete_new_order.
	 *
	 * @param array $fields Fields.
	 */
	public function delete_new_order( $fields ): array {

		$options_whatsapp_settings_credentials = $this->optionsCredentialsManager->get_options_whatsapp_settings_credentials();

		if ( $options_whatsapp_settings_credentials ) {
			$options_whatsapp_settings_new_order = $this->get_options_whatsapp_settings_new_order();

			$url = 'https://graph.facebook.com/v22.0/' . $options_whatsapp_settings_credentials['whatsapp_api_account_id'] . '/message_templates';

			$headers = array(
				'Authorization' => 'Bearer ' . $options_whatsapp_settings_credentials['whatsapp_api_token'],
				'Content-Type' => 'application/json'
			);

			$args = array(
				'headers' => $headers,
				'method'     => 'DELETE',
				'body' => wp_json_encode( array('hsm_id' => $options_whatsapp_settings_new_order['whatsapp_template_new_order_customer_id'], 'name' => $options_whatsapp_settings_new_order['whatsapp_template_new_order_customer_name']) ),
				'timeout' => 9999,
			);

			$response = wp_remote_post($url, $args);

			if (is_wp_error($response)) {
				$error_message = $response->get_error_message();
				$code          = $response->get_error_code();

				if ( 'http_request_failed' === $code) {
					return array('message' => $error_message, 'code' => 408);
				}
				return array('message' => $error_message, 'code' => $code);
			} else {
				$response_body = wp_remote_retrieve_body($response);

				$json_response_body = json_decode($response_body);

				if ($json_response_body->error) {
					return array('message' => $json_response_body->error, 'code' => 200);
				}

				if ($json_response_body->success) {
					$this->update_status_new_order_customer( '', '' );
				}

				return array('message' => $response_body, 'code' => 200);
			}

		}

		return array('message' => 'Fields are false', 'code' => 403);

	}

	/**
	 * Get_order.
	 *
	 */
	public function get_order(): array {
		$options_whatsapp_settings_credentials = $this->optionsCredentialsManager->get_options_whatsapp_settings_credentials();

		if ( $options_whatsapp_settings_credentials ) {

			$options_whatsapp_settings_new_order = $this->get_options_whatsapp_settings_new_order();

			if ( !empty($options_whatsapp_settings_new_order['whatsapp_template_new_order_customer_id']) ) {
				$url = 'https://graph.facebook.com/v22.0/' . $options_whatsapp_settings_new_order['whatsapp_template_new_order_customer_id'];

				$headers = array(
					'Authorization' => 'Bearer ' . $options_whatsapp_settings_credentials['whatsapp_api_token'],
					'Content-Type' => 'application/json'
				);

				$args = array(
					'headers' => $headers,
				);

				$response = wp_remote_get($url, $args);

				if ( is_wp_error( $response ) ) {
					$error_message = $response->get_error_message();
					$code          = $response->get_error_code();

					if ( 'http_request_failed' === $code) {
						return array('message' => $error_message, 'code' => 408);
					}
					return array('message' => $error_message, 'code' => $code);
				} else {
					$response_body = wp_remote_retrieve_body($response);

					$json_response_body = json_decode($response_body);

					if ($json_response_body->error) {
						$this->update_status_new_order_customer( '', '' );
						return array('message' => $json_response_body->error, 'data'=> array('whatsapp_template_new_order_customer_id' => '','whatsapp_template_new_order_customer_status'=> 'REMOVED'), 'code' => 200);
					}

					return array( 'message' => 'ok', 'data'=> array('whatsapp_template_new_order_customer_status'=> $json_response_body->status, 'whatsapp_template_new_order_customer_category'=> $json_response_body->category ?? '') );
				}
			}

			return array();
		}

		return array();
	}

	/**
	 * Update_status_new_order_customer.
	 *
	 * @param string $id Id.
	 * @param string $status Status.
	 */
	private function update_status_new_order_customer( $id, $status) {
		$options = get_option( 'arraycodes_on_fields_free' );

		$options['whatsapp_template_new_order_customer_id']     = $id;
		$options['whatsapp_template_new_order_customer_status'] = $status;

		update_option('arraycodes_on_fields_free', $options);

	}

	/**
	 * Upload_media.
	 *
	 * @param string $media Link.
	 */
	public function upload_media( string $media ): array {

		$options_whatsapp_settings_credentials = $this->optionsCredentialsManager->get_options_whatsapp_settings_credentials();

		if ( ! $options_whatsapp_settings_credentials ) {
			return array( 'message' => 'Credenciais não encontradas.', 'code' => 403 );
		}

		$url_media     = $media;
		$upload_dir    = wp_upload_dir();
		$tmp_file_path = $upload_dir['path'] . '/' . basename( $url_media );

		$response_download = wp_remote_get( $url_media );

		if ( is_wp_error( $response_download ) ) {
			$error_message = $response_download->get_error_message();
			return array( 'message' => 'Erro ao baixar o arquivo: ' . $error_message, 'code' => 500 );
		}

		$file_contents = wp_remote_retrieve_body( $response_download );

		if ( empty( $file_contents ) ) {
			return array( 'message' => 'Arquivo baixado está vazio.', 'code' => 500 );
		}

		global $wp_filesystem;

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		WP_Filesystem();

		if ( ! $wp_filesystem ) {
			return array( 'message' => 'Não foi possível inicializar o sistema de arquivos.', 'code' => 500 );
		}

		$saved = $wp_filesystem->put_contents( $tmp_file_path, $file_contents, FS_CHMOD_FILE );
		if ( ! $saved ) {
			return array( 'message' => 'Não foi possível salvar o arquivo temporariamente.', 'code' => 500 );
		}

		$filetype_info = wp_check_filetype( $tmp_file_path );
		$mime_type     = $filetype_info['type'];

		if ( empty( $mime_type ) && function_exists( 'mime_content_type' ) ) {
			$mime_type = mime_content_type( $tmp_file_path );
		}

		if ( empty( $mime_type ) ) {
			$extension = strtolower( pathinfo( $tmp_file_path, PATHINFO_EXTENSION ) );
			$mime_map  = array(
				'png'  => 'image/png',
				'jpg'  => 'image/jpeg',
				'jpeg' => 'image/jpeg',
			);
			if ( isset( $mime_map[ $extension ] ) ) {
				$mime_type = $mime_map[ $extension ];
			}
		}

		$url = 'https://graph.facebook.com/v22.0/937612081740950/uploads';

		$headers = array(
			'Authorization' => 'Bearer ' . $options_whatsapp_settings_credentials['whatsapp_api_token'],
			'Content-Type'  => 'application/json',
		);

		$prepared_fields = array(
			'file_name'    => basename( $tmp_file_path ),
			'file_length'  => $wp_filesystem->size( $tmp_file_path ),
			'file_type'    => $mime_type,
			'access_token' => $options_whatsapp_settings_credentials['whatsapp_api_token'],
		);

		$args = array(
			'headers' => $headers,
			'body'    => wp_json_encode( $prepared_fields ),
			'timeout' => 9999,
		);

		$response = wp_remote_post( $url, $args );

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			$code          = $response->get_error_code();

			if ( 'http_request_failed' === $code ) {
				return array( 'message' => $error_message, 'code' => 408 );
			}
			return array( 'message' => $error_message, 'code' => $code );
		}

		$response_body      = wp_remote_retrieve_body( $response );
		$json_response_body = json_decode( $response_body );

		if ( isset( $json_response_body->error ) ) {
			return array( 'message' => $json_response_body->error, 'code' => 500 );
		}

		if ( isset( $json_response_body->id ) ) {
			// PASSO 2: Envia o arquivo binário.
			$file_content = $wp_filesystem->get_contents( $tmp_file_path );

			$response = wp_remote_request(
				'https://graph.facebook.com/v22.0/' . $json_response_body->id,
				array(
					'method'  => 'POST',
					'headers' => array(
						'Authorization' => 'OAuth ' . $options_whatsapp_settings_credentials['whatsapp_api_token'],
						'file_offset'   => '0',
						'Content-Type'  => 'application/octet-stream',
					),
					'body'    => $file_content,
				)
			);

			$wp_filesystem->delete( $tmp_file_path );

			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				return array( 'message' => $error_message, 'code' => 500 );
			}

			$response_body      = wp_remote_retrieve_body( $response );
			$json_response_body = json_decode( $response_body );

			return isset( $json_response_body->h )
				? array( 'id' => $json_response_body->h )
				: array( 'message' => 'Erro ao enviar mídia.', 'code' => 500 );
		}

		$wp_filesystem->delete( $tmp_file_path );

		return array( 'message' => 'Erro ao realizar upload da mídia.', 'code' => 500 );
	}

	/**
	 * Retrieves the header component configuration based on the header type.
	 *
	 * @param string $header The header value used for configuration.
	 * @return array An associative array representing the header component configuration.
	 */
	protected function getHeaderComponent( string $header ): array {
		if ('image' === $this->headerType) {
			return array(
				'type'   => 'HEADER',
				'format' => 'IMAGE',
				'example' => array(
					'header_handle' => array(
						$header,
					)
				)
			);
		}
		return array(
			'type'   => 'HEADER',
			'format' => 'TEXT',
			'text'   => $header,
		);
	}

	/**
	 * Get_options_whatsapp_settings_new_order.
	 *
	 */
	public function get_options_whatsapp_settings_new_order() {
		return  $this->optionsManager->get_options_whatsapp_settings_new_order();
	}

}
