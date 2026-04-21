/**
 * External dependencies
 */
import { TextControl, Tip, ExternalLink, Spinner } from '@wordpress/components';
import { SectionHeader } from '@woocommerce/components';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';

const WebhookWhatsConnection = () => {
	const { webhookCallbackUrl, webhookToken, isLoadingWebhookCallbackUrl } =
		useSelect( ( select ) => {
			const store = select( 'shop-arraycodes-order-notifications-woocommerce' );

			const webhookData = store.getWebhookCallbackUrl() || {};

			return {
				webhookCallbackUrl:
					webhookData.whatsapp_api_webhook_callback_url || '',
				webhookToken: webhookData.whatsapp_api_webhook_token || '',
				isLoadingWebhookCallbackUrl: store.hasFinishedResolution(
					'getWebhookCallbackUrl'
				),
			};
		}, [] );

	return (
		<div className="my-gutenberg-form">
			<SectionHeader
				title={ __(
					'WhatsApp Connection Settings Webhook callback URL',
					'arraycodes-order-notifications-woocommerce'
				) }
			/>
			<div className="form-legend">
				<Tip>
					{ __(
						'Subscribe to Webhooks to get notifications about messages your business receives.',
						'arraycodes-order-notifications-woocommerce'
					) }{ ' ' }
					<ExternalLink href="https://woocommerce.com/document/notifications-with-whatsapp/#configure-webhook-callback">
						{ __( 'Read more', 'arraycodes-order-notifications-woocommerce' ) }
					</ExternalLink>
				</Tip>
				<br />
				<br />
			</div>
			{ ! isLoadingWebhookCallbackUrl ? (
				<Spinner
					style={ {
						height: 'calc(4px * 20)',
						width: 'calc(4px * 20)',
					} }
				/>
			) : (
				<>
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						disabled
						label={ __(
							'WebHook URL',
							'arraycodes-order-notifications-woocommerce'
						) }
						value={ webhookCallbackUrl }
					/>
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						disabled
						label={ __(
							'Verify Token',
							'arraycodes-order-notifications-woocommerce'
						) }
						value={ webhookToken }
					/>
				</>
			) }
		</div>
	);
};
export default WebhookWhatsConnection;
