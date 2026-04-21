/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { decodeEntities } from '@wordpress/html-entities';
import { getSetting } from '@woocommerce/settings';

const settings = getSetting( 'whatsapp_data', {} );
const defaultLabel = __( 'WhatsApp Payment', 'arraycodes-order-notifications-woocommerce' );
const defaultLabelContent = __(
	'Finalize the order and send the information to our WhatsApp, there you can finish the sale by talking directly to us.',
	'arraycodes-order-notifications-woocommerce'
);

const label = decodeEntities( settings.title ) || defaultLabel;
/**
 * Content component
 */
const Content = () => {
	return decodeEntities( settings.description || defaultLabelContent );
};
/**
 * Label component
 *
 * @param {*} props Props from payment API.
 */
const Label = ( props ) => {
	const { PaymentMethodLabel } = props.components;
	return <PaymentMethodLabel text={ label } />;
};

/**
 * WhastApp payment method config object.
 */
const WhastApp = {
	name: 'whatsapp',
	label: <Label />,
	content: <Content />,
	edit: <Content />,
	canMakePayment: () => true,
	ariaLabel: label,
	supports: {
		features: settings.supports,
	},
};

registerPaymentMethod( WhastApp );
