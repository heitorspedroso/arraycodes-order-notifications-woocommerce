/**
 * External dependencies
 */
import { useEffect, useState } from '@wordpress/element';
import { CheckboxControl } from '@woocommerce/blocks-checkout';
import { getSetting } from '@woocommerce/settings';
import { useSelect, useDispatch } from '@wordpress/data';

const { optInDefaultText, whatsAppApiCheckedInput } = getSetting(
	'whatsapp-notification_data',
	''
);

const Block = ( { children, checkoutExtensionData } ) => {
	const [ checked, setChecked ] = useState( whatsAppApiCheckedInput );
	const { setExtensionData } = checkoutExtensionData;

	const { setValidationErrors, clearValidationError } = useDispatch(
		'wc/store/validation'
	);

	useEffect( () => {
		setExtensionData( 'whatsapp_notification_fields', 'optin', checked );
		clearValidationError( 'whatsapp_notification_fields' );
	}, [
		clearValidationError,
		setValidationErrors,
		checked,
		setExtensionData,
	] );

	const { validationError } = useSelect( ( select ) => {
		const store = select( 'wc/store/validation' );
		return {
			validationError: store.getValidationError(
				'whatsapp_notification_fields'
			),
		};
	} );

	return (
		<>
			<CheckboxControl
				id="whatsapp-notification-field"
				checked={ checked }
				onChange={ setChecked }
			>
				{ children || optInDefaultText }
			</CheckboxControl>

			{ validationError?.hidden === false && (
				<div>
					<span role="img" aria-label="Warning emoji">
						⚠️
					</span>
					{ validationError?.message }
				</div>
			) }
		</>
	);
};

export default Block;
