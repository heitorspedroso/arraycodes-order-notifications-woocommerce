/**
 * External dependencies
 */
import { Button, Tip } from '@wordpress/components';
import { SectionHeader } from '@woocommerce/components';
import { __ } from '@wordpress/i18n';

const PageFormCheckoutOptions = () => {
	return (
		<div className="my-gutenberg-form">
			<SectionHeader
				title={ __(
					'Checkout Options Settings',
					'arraycodes-order-notifications-woocommerce'
				) }
			/>
			<div className="form-buttons">
				<Tip>Enable the WhatsApp option at checkout.</Tip>
				<br />
				<br />
				<Button
					variant="primary"
					href="/wp-admin/admin.php?page=wc-settings&tab=checkout&section=whatsapp"
				>
					{ __(
						'Go to Enable WhatsApp Payment',
						'arraycodes-order-notifications-woocommerce'
					) }
				</Button>
			</div>
		</div>
	);
};
export default PageFormCheckoutOptions;
