/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
} from '@wordpress/block-editor';
import { PanelBody } from '@wordpress/components';
import { CheckboxControl } from '@woocommerce/blocks-checkout';
import { getSetting } from '@woocommerce/settings';
/**
 * Internal dependencies
 */
import './style.scss';
const { optInDefaultText } = getSetting( 'whatsapp-notification_data', '' );

export const Edit = ( { attributes, setAttributes } ) => {
	const { text } = attributes;
	const blockProps = useBlockProps();
	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody
					title={ __( 'Block options', 'whatsapp-notification' ) }
				>
					Options
				</PanelBody>
			</InspectorControls>
			<CheckboxControl
				id="whatsapp-notification-field"
				checked={ false }
				disabled={ true }
			/>
			{ text || optInDefaultText }
		</div>
	);
};

export const Save = ( { attributes } ) => {
	const { text } = attributes;
	return (
		<div { ...useBlockProps.save() }>
			<RichText.Content value={ text } />
		</div>
	);
};
