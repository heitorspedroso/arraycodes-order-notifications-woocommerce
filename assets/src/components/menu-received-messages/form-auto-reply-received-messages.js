/**
 * External dependencies
 */
import {
	Button,
	Tip,
	ExternalLink,
	TextControl,
	SelectControl,
	TextareaControl,
	Spinner,
	Modal, ToggleControl,
} from '@wordpress/components';
import { SectionHeader } from '@woocommerce/components';
import { __ } from '@wordpress/i18n';
import { useCallback, useEffect, useState } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import apiFetch from '@wordpress/api-fetch';

const PageFormAutoReplyReceivedMessages = () => {
	const { createSuccessNotice, createErrorNotice } =
		useDispatch( noticesStore );

	const dispatch = useDispatch();

	const [ isSaving, setIsSaving ] = useState( false );

	const { getFields, isLoading } = useSelect(
		( select ) => ( {
			getFields: select( 'shop-arraycodes-order-notifications-woocommerce' ).getState(),
			isLoading: select(
				'shop-arraycodes-order-notifications-woocommerce'
			).hasFinishedResolution( 'getSettings' ),
		} ),
		[]
	);
	const [ fields, setFields ] = useState( {
		whatsapp_auto_reply: '',
	} );

	const handleChange = useCallback( ( newText, fieldKey ) => {
		setFields( ( prevFields ) => ( {
			...prevFields,
			[ fieldKey ]: newText,
		} ) );
	}, [] );

	const currentState = useSelect( ( select ) =>
		select( 'shop-arraycodes-order-notifications-woocommerce' ).getState()
	);

	const handleSetStorageAndReloadPage = () => {
		window.localStorage.setItem( 'activeComponent', 'item-10' );
		window.location.reload();
	};

	useEffect( () => {
		if ( getFields ) {
			const fetchedFields = getFields;
			setFields( ( prevFields ) => ( {
				...prevFields,
				...fetchedFields,
			} ) );
		}
	}, [ getFields ] );

	const handleSave = async () => {
		try {
			setIsSaving( true );
			await dispatch( 'shop-arraycodes-order-notifications-woocommerce' ).saveSettings(
				{
					whatsapp_auto_reply:
					fields.whatsapp_auto_reply,
				},
				currentState
			);

			setIsSaving( false );

			createSuccessNotice(
				__( 'Message saved!', 'arraycodes-order-notifications-woocommerce' ),
				{
					type: 'snackbar',
				}
			);
		} catch ( error ) {
			// eslint-disable-next-line no-console
			console.error( 'Error saving settings:', error );
			createErrorNotice( 'An error occurred while saving settings.', {
				type: 'snackbar',
			} );
		}
	};

	return (
		<div className="my-gutenberg-form">
			<SectionHeader
				title={__(
					'Auto-reply Message',
					'arraycodes-order-notifications-woocommerce'
				)}
			/>
			<div className="form-legend">
				<Tip>
					{__(
						'Message sent automatically when someone sends you a message.',
						'arraycodes-order-notifications-woocommerce'
					)}
				</Tip>
				<br />
				<br />
			</div>
			{!isLoading ? (
				<Spinner
					style={{
						height: 'calc(4px * 20)',
						width: 'calc(4px * 20)',
					}}
				/>
			) : (
				<>
					<div className="premium-function">
						<span>
							<ExternalLink
								href="https://woocommerce.com/products/notifications-with-whatsapp/"
								className="link-premium-function"
							>
								Unlock with Premium
							</ExternalLink>
							<div className="form-fields">
								<TextareaControl
									__nextHasNoMarginBottom
									label={__(
										'Body',
										'arraycodes-order-notifications-woocommerce'
									)}
									value={fields.whatsapp_auto_reply}
									onChange={(newValue) =>
										handleChange(
											newValue,
											'whatsapp_auto_reply'
										)
									}
									rows={10}
									help={__(
										'Enter the text for your auto-reply message',
										'arraycodes-order-notifications-woocommerce'
									)}
									disabled
								/>
								<div className="form-buttons">
									<Button
										onClick={handleSave}
										variant="primary"
										disabled
									>
										{isSaving ? (
											<>
												<Spinner />
												{__(
													'Saving',
													'arraycodes-order-notifications-woocommerce'
												)}
											</>
										) : (
											__(
												'Save',
												'arraycodes-order-notifications-woocommerce'
											)
										)}
									</Button>
								</div>
							</div>
						</span>
					</div>
				</>
			)}
		</div>
	);
};
export default PageFormAutoReplyReceivedMessages;
